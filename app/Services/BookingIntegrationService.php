<?php

namespace App\Services;

use App\Models\ServiceRequest;
use App\Models\HotelBooking;
use App\Models\TransportBooking;
use App\Models\FlightBooking;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\Flight;
use App\Models\TransportService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * BookingIntegrationService
 * 
 * Handles the conversion of approved service requests into actual bookings
 * for different provider types (hotels, flights, transport)
 */
class BookingIntegrationService
{
    /**
     * Convert an approved service request to a booking
     * 
     * @param ServiceRequest $serviceRequest
     * @return array
     */
    public function convertToBooking(ServiceRequest $serviceRequest): array
    {
        try {
            // Validate that the request can be converted
            if (!$this->canConvertToBooking($serviceRequest)) {
                return [
                    'success' => false,
                    'message' => 'Service request cannot be converted to booking',
                    'error_code' => 'INVALID_STATE'
                ];
            }

            DB::beginTransaction();

            $booking = null;

            switch ($serviceRequest->provider_type) {
                case ServiceRequest::PROVIDER_HOTEL:
                    $booking = $this->createHotelBooking($serviceRequest);
                    break;
                    
                case ServiceRequest::PROVIDER_FLIGHT:
                    $booking = $this->createFlightBooking($serviceRequest);
                    break;
                    
                case ServiceRequest::PROVIDER_TRANSPORT:
                    $booking = $this->createTransportBooking($serviceRequest);
                    break;
                    
                default:
                    return [
                        'success' => false,
                        'message' => 'Unknown provider type: ' . $serviceRequest->provider_type,
                        'error_code' => 'INVALID_PROVIDER_TYPE'
                    ];
            }

            if (!$booking) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Failed to create booking',
                    'error_code' => 'BOOKING_CREATION_FAILED'
                ];
            }

            // Handle multiple bookings vs single booking
            $bookings = is_array($booking) ? $booking : [$booking];
            $primaryBooking = is_array($booking) ? $booking[0] : $booking;
            
            // Update the service request to link to the booking(s)
            $this->linkServiceRequestToBookings($serviceRequest, $bookings);

            DB::commit();

            $bookingCount = count($bookings);
            Log::info('Service request converted to booking(s) successfully', [
                'service_request_id' => $serviceRequest->id,
                'booking_count' => $bookingCount,
                'primary_booking_id' => $primaryBooking->id,
                'provider_type' => $serviceRequest->provider_type
            ]);

            $message = $bookingCount > 1 ? 
                "Service request successfully converted to {$bookingCount} bookings" :
                'Service request successfully converted to booking';

            return [
                'success' => true,
                'message' => $message,
                'booking' => $primaryBooking, // For backward compatibility
                'bookings' => $bookings, // All bookings
                'booking_id' => $primaryBooking->id, // For backward compatibility
                'booking_count' => $bookingCount,
                'booking_type' => $serviceRequest->provider_type
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error converting service request to booking', [
                'service_request_id' => $serviceRequest->id,
                'provider_type' => $serviceRequest->provider_type,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
                'service_request_data' => [
                    'item_id' => $serviceRequest->item_id,
                    'start_date' => $serviceRequest->start_date?->format('Y-m-d'),
                    'end_date' => $serviceRequest->end_date?->format('Y-m-d'),
                    'requested_quantity' => $serviceRequest->requested_quantity,
                    'metadata' => $serviceRequest->metadata
                ]
            ]);

            return [
                'success' => false,
                'message' => 'Failed to convert service request to booking: ' . $e->getMessage(),
                'error_code' => 'CONVERSION_ERROR',
                'debug_info' => [
                    'exception_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    /**
     * Create hotel booking(s) from service request
     * 
     * @param ServiceRequest $serviceRequest
     * @return HotelBooking|array|null
     */
    private function createHotelBooking(ServiceRequest $serviceRequest)
    {
        try {
            // Start a transaction with row locking to prevent race conditions
            DB::beginTransaction();
            
            // Get hotel and room information
            $hotel = Hotel::find($serviceRequest->item_id);
            if (!$hotel) {
                throw new \Exception('Hotel not found with ID: ' . $serviceRequest->item_id);
            }

            $requestedQuantity = $serviceRequest->requested_quantity ?? 1;
            
            // Check if we need multiple rooms
            if ($requestedQuantity > 1) {
                return $this->createMultipleHotelBookings($serviceRequest, $hotel, $requestedQuantity);
            }
            
            // Single room booking (original logic)
            // Determine room - either from metadata or get the first available room
            $room = $this->determineHotelRoom($serviceRequest, $hotel);
            if (!$room) {
                throw new \Exception('No suitable room found for the booking');
            }
            
            // Lock the room row to prevent race conditions
            $lockedRoom = Room::where('id', $room->id)->lockForUpdate()->first();
            if (!$lockedRoom) {
                throw new \Exception('Room not found or could not be locked for booking');
            }
            
            // Final availability check with locked room (prevents race conditions)
            $roomAvailabilityService = new \App\Services\RoomAvailabilityService();
            if (!$roomAvailabilityService->isRoomAvailableForDates($lockedRoom, $serviceRequest->start_date, $serviceRequest->end_date)) {
                throw new \Exception("Room {$lockedRoom->formatted_room_number} is no longer available for the requested dates. Another booking may have been created simultaneously.");
            }
            
            // Use the locked room for booking creation
            $room = $lockedRoom;

            // Get customer information (travel agent as customer for now)
            $customer = $serviceRequest->agent;

            // Extract guest information from metadata or use agent info
            $guestInfo = $this->extractGuestInformation($serviceRequest);

            // Calculate pricing
            $pricing = $this->calculateHotelBookingPricing($serviceRequest, $room);

            // Create the hotel booking
            $booking = HotelBooking::create([
                'hotel_id' => $hotel->id,
                'room_id' => $room->id,
                'customer_id' => $customer->id,
                'booking_reference' => $this->generateBookingReference('HB'),
                'check_in_date' => $serviceRequest->start_date,
                'check_out_date' => $serviceRequest->end_date,
                'nights' => $serviceRequest->start_date->diffInDays($serviceRequest->end_date),
                'adults' => $guestInfo['adults'],
                'children' => $guestInfo['children'],
                'room_rate' => $pricing['room_rate'],
                'total_amount' => $pricing['total_amount'],
                'paid_amount' => 0, // Initially unpaid
                'tax_amount' => $pricing['tax_amount'],
                'service_fee' => $pricing['service_fee'],
                'discount_amount' => $pricing['discount_amount'],
                'currency' => $serviceRequest->currency ?? 'USD',
                'status' => 'confirmed', // Auto-confirm approved requests
                'payment_status' => 'pending',
                'special_requests' => $serviceRequest->metadata['special_requirements'] ?? null,
                'guest_name' => $guestInfo['name'],
                'guest_email' => $guestInfo['email'],
                'guest_phone' => $guestInfo['phone'],
                'notes' => $this->buildBookingNotes($serviceRequest),
                'confirmation_code' => strtoupper(uniqid()),
                'source' => 'service_request',
                'confirmed_at' => now(),
            ]);
            
            // Commit the transaction to release locks
            DB::commit();

            return $booking;

        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            
            Log::error('Error creating hotel booking', [
                'service_request_id' => $serviceRequest->id,
                'hotel_id' => $serviceRequest->item_id,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
                'service_request_details' => [
                    'start_date' => $serviceRequest->start_date?->format('Y-m-d'),
                    'end_date' => $serviceRequest->end_date?->format('Y-m-d'),
                    'requested_quantity' => $serviceRequest->requested_quantity,
                    'metadata' => $serviceRequest->metadata,
                    'agent_id' => $serviceRequest->agent_id,
                    'provider_id' => $serviceRequest->provider_id
                ]
            ]);
            
            // Re-throw with more context
            throw new \Exception(
                'Hotel booking creation failed: ' . $e->getMessage() . 
                ' (File: ' . basename($e->getFile()) . ':' . $e->getLine() . ')',
                $e->getCode(),
                $e
            );
        }
    }
    
    /**
     * Create multiple hotel bookings for a service request
     * 
     * @param ServiceRequest $serviceRequest
     * @param Hotel $hotel
     * @param int $requestedQuantity
     * @return array
     */
    private function createMultipleHotelBookings(ServiceRequest $serviceRequest, Hotel $hotel, int $requestedQuantity): array
    {
        try {
            // Get all available rooms for the dates
            $roomAvailabilityService = new \App\Services\RoomAvailabilityService();
            $availableRooms = $roomAvailabilityService->getAvailableRooms(
                $hotel->id,
                $serviceRequest->start_date,
                $serviceRequest->end_date,
                1, // Guest count for filtering
                []
            );
            
            if ($availableRooms->count() < $requestedQuantity) {
                throw new \Exception("Insufficient rooms available. Requested: {$requestedQuantity}, Available: {$availableRooms->count()}");
            }
            
            // Get customer information
            $customer = $serviceRequest->agent;
            $guestInfo = $this->extractGuestInformation($serviceRequest);
            
            $bookings = [];
            $roomsToUse = $availableRooms->take($requestedQuantity);
            
            foreach ($roomsToUse as $room) {
                // Lock the room
                $lockedRoom = Room::where('id', $room->id)->lockForUpdate()->first();
                if (!$lockedRoom) {
                    throw new \Exception("Room {$room->id} could not be locked for booking");
                }
                
                // Final availability check
                if (!$roomAvailabilityService->isRoomAvailableForDates($lockedRoom, $serviceRequest->start_date, $serviceRequest->end_date)) {
                    throw new \Exception("Room {$lockedRoom->formatted_room_number} is no longer available for the requested dates.");
                }
                
                // Calculate pricing for this room
                $pricing = $this->calculateHotelBookingPricing($serviceRequest, $lockedRoom);
                
                // Create individual booking
                $booking = HotelBooking::create([
                    'hotel_id' => $hotel->id,
                    'room_id' => $lockedRoom->id,
                    'customer_id' => $customer->id,
                    'booking_reference' => $this->generateBookingReference('HB'),
                    'check_in_date' => $serviceRequest->start_date,
                    'check_out_date' => $serviceRequest->end_date,
                    'nights' => $serviceRequest->start_date->diffInDays($serviceRequest->end_date),
                    'adults' => $guestInfo['adults'],
                    'children' => $guestInfo['children'],
                    'room_rate' => $pricing['room_rate'],
                    'total_amount' => $pricing['total_amount'],
                    'paid_amount' => 0,
                    'tax_amount' => $pricing['tax_amount'],
                    'service_fee' => $pricing['service_fee'],
                    'discount_amount' => $pricing['discount_amount'],
                    'currency' => $serviceRequest->currency ?? 'USD',
                    'status' => 'confirmed',
                    'payment_status' => 'pending',
                    'special_requests' => $serviceRequest->metadata['special_requirements'] ?? null,
                    'guest_name' => $guestInfo['name'],
                    'guest_email' => $guestInfo['email'],
                    'guest_phone' => $guestInfo['phone'],
                    'notes' => $this->buildBookingNotes($serviceRequest) . " (Room " . (count($bookings) + 1) . " of {$requestedQuantity})",
                    'confirmation_code' => strtoupper(uniqid()),
                    'source' => 'service_request',
                    'confirmed_at' => now(),
                ]);
                
                $bookings[] = $booking;
            }
            
            // Commit the transaction
            DB::commit();
            
            Log::info('Multiple hotel bookings created successfully', [
                'service_request_id' => $serviceRequest->id,
                'hotel_id' => $hotel->id,
                'bookings_created' => count($bookings),
                'requested_quantity' => $requestedQuantity
            ]);
            
            return $bookings;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error creating multiple hotel bookings', [
                'service_request_id' => $serviceRequest->id,
                'hotel_id' => $hotel->id,
                'requested_quantity' => $requestedQuantity,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            throw new \Exception(
                'Multiple hotel bookings creation failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create flight booking from service request
     * 
     * @param ServiceRequest $serviceRequest
     * @return FlightBooking|null
     */
    private function createFlightBooking(ServiceRequest $serviceRequest): ?FlightBooking
    {
        try {
            DB::beginTransaction();
            
            // Get flight information
            $flight = Flight::find($serviceRequest->item_id);
            if (!$flight) {
                throw new \Exception('Flight not found with ID: ' . $serviceRequest->item_id);
            }

            $requestedSeats = $serviceRequest->requested_quantity ?? 1;
            
            // Get customer information (travel agent as customer for now)
            $customer = $serviceRequest->agent;

            // Extract passenger information from metadata or use agent info
            $passengerInfo = $this->extractFlightPassengerInformation($serviceRequest);

            // Get flight class from metadata or default to economy
            $flightClass = $serviceRequest->metadata['flight_class'] ?? 'economy';

            // Calculate pricing
            $pricing = $this->calculateFlightBookingPricing($serviceRequest, $flight, $flightClass);

            // Create the flight booking
            $booking = FlightBooking::create([
                'flight_id' => $flight->id,
                'customer_id' => $customer->id,
                'booking_reference' => $this->generateBookingReference('FB'),
                'confirmation_code' => strtoupper(uniqid()),
                'passengers' => $requestedSeats,
                'flight_class' => $flightClass,
                'seat_price' => $pricing['seat_price'],
                'total_amount' => $pricing['total_amount'],
                'paid_amount' => 0, // Initially unpaid
                'tax_amount' => $pricing['tax_amount'],
                'service_fee' => $pricing['service_fee'],
                'discount_amount' => $pricing['discount_amount'],
                'currency' => $serviceRequest->currency ?? 'USD',
                'status' => FlightBooking::STATUS_CONFIRMED, // Auto-confirm approved requests
                'payment_status' => FlightBooking::PAYMENT_PENDING,
                'passenger_name' => $passengerInfo['name'],
                'passenger_email' => $passengerInfo['email'],
                'passenger_phone' => $passengerInfo['phone'],
                'passenger_details' => $passengerInfo['details'] ?? null,
                'special_requests' => $serviceRequest->metadata['special_requirements'] ?? null,
                'notes' => $this->buildFlightBookingNotes($serviceRequest),
                'source' => FlightBooking::SOURCE_SERVICE_REQUEST,
                'confirmed_at' => now(),
            ]);
            
            DB::commit();

            Log::info('Flight booking created successfully', [
                'service_request_id' => $serviceRequest->id,
                'flight_id' => $flight->id,
                'booking_id' => $booking->id,
                'passengers' => $requestedSeats,
                'flight_class' => $flightClass
            ]);

            return $booking;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error creating flight booking', [
                'service_request_id' => $serviceRequest->id,
                'flight_id' => $serviceRequest->item_id,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
                'service_request_details' => [
                    'start_date' => $serviceRequest->start_date?->format('Y-m-d'),
                    'end_date' => $serviceRequest->end_date?->format('Y-m-d'),
                    'requested_quantity' => $serviceRequest->requested_quantity,
                    'metadata' => $serviceRequest->metadata,
                    'agent_id' => $serviceRequest->agent_id,
                    'provider_id' => $serviceRequest->provider_id
                ]
            ]);
            
            throw new \Exception(
                'Flight booking creation failed: ' . $e->getMessage() . 
                ' (File: ' . basename($e->getFile()) . ':' . $e->getLine() . ')',
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create a transport booking from service request
     * 
     * @param ServiceRequest $serviceRequest
     * @return TransportBooking|null
     */
    private function createTransportBooking(ServiceRequest $serviceRequest): ?TransportBooking
    {
        try {
            // Get transport service information
            $transportService = TransportService::find($serviceRequest->item_id);
            if (!$transportService) {
                throw new \Exception('Transport service not found with ID: ' . $serviceRequest->item_id);
            }

            // Get customer information (travel agent as customer for now)
            $customer = $serviceRequest->agent;

            // Extract passenger information from metadata or use agent info
            $passengerInfo = $this->extractTransportPassengerInformation($serviceRequest);

            // Calculate pricing
            $pricing = $this->calculateTransportBookingPricing($serviceRequest, $transportService);

            // Extract trip details from metadata
            $tripDetails = $this->extractTransportTripDetails($serviceRequest);

            // Create the transport booking
            $booking = TransportBooking::create([
                'transport_service_id' => $transportService->id,
                'customer_id' => $customer->id,
                'booking_reference' => $this->generateBookingReference('TB'),
                'transport_type' => $transportService->transport_type ?? 'bus',
                'route_type' => $transportService->route_type ?? 'airport_transfer',
                'pickup_location' => $tripDetails['pickup_location'],
                'dropoff_location' => $tripDetails['dropoff_location'],
                'pickup_datetime' => $tripDetails['pickup_datetime'],
                'dropoff_datetime' => $tripDetails['dropoff_datetime'],
                'passenger_count' => $passengerInfo['total_passengers'],
                'adults' => $passengerInfo['adults'],
                'children' => $passengerInfo['children'],
                'infants' => $passengerInfo['infants'],
                'base_rate' => $pricing['base_rate'],
                'total_amount' => $pricing['total_amount'],
                'paid_amount' => 0, // Initially unpaid
                'tax_amount' => $pricing['tax_amount'],
                'service_fee' => $pricing['service_fee'],
                'discount_amount' => $pricing['discount_amount'],
                'currency' => $serviceRequest->currency ?? 'USD',
                'status' => 'confirmed', // Auto-confirm approved requests
                'payment_status' => 'pending',
                'passenger_name' => $passengerInfo['name'],
                'passenger_email' => $passengerInfo['email'],
                'passenger_phone' => $passengerInfo['phone'] ?: 'Not provided',
                'special_requests' => $serviceRequest->metadata['special_requirements'] ?? null,
                'notes' => $this->buildTransportBookingNotes($serviceRequest),
                'confirmation_code' => strtoupper(uniqid()),
                'source' => 'service_request',
                'confirmed_at' => now(),
                'vehicle_details' => $tripDetails['vehicle_details'] ?? null,
                'driver_details' => $tripDetails['driver_details'] ?? null,
                'route_details' => $tripDetails['route_details'] ?? null,
                'metadata' => [
                    'service_request_id' => $serviceRequest->id,
                    'approved_price' => $serviceRequest->offered_price,
                    'provider_notes' => $serviceRequest->provider_notes,
                ]
            ]);

            return $booking;

        } catch (\Exception $e) {
            Log::error('Error creating transport booking', [
                'service_request_id' => $serviceRequest->id,
                'transport_service_id' => $serviceRequest->item_id,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Determine which room to assign for the booking
     * 
     * @param ServiceRequest $serviceRequest
     * @param Hotel $hotel
     * @return Room|null
     */
    private function determineHotelRoom(ServiceRequest $serviceRequest, Hotel $hotel): ?Room
    {
        // First priority: Check if a room was specifically assigned during approval
        if (isset($serviceRequest->metadata['assigned_room_id'])) {
            $room = Room::where('id', $serviceRequest->metadata['assigned_room_id'])
                ->where('hotel_id', $hotel->id)
                ->where('is_active', true)
                ->first();
            
            if ($room) {
                // Verify the room is still available for the booking dates
                $roomAvailabilityService = new \App\Services\RoomAvailabilityService();
                if ($roomAvailabilityService->isRoomAvailableForDates($room, $serviceRequest->start_date, $serviceRequest->end_date)) {
                    return $room;
                } else {
                    Log::warning('Assigned room is no longer available for booking', [
                        'service_request_id' => $serviceRequest->id,
                        'assigned_room_id' => $serviceRequest->metadata['assigned_room_id'],
                        'room_number' => $room->formatted_room_number
                    ]);
                    // Continue to fallback logic below
                }
            }
        }
        
        // Fallback: If specific room is mentioned in metadata, try to get that
        if (isset($serviceRequest->metadata['room_id'])) {
            $room = Room::where('id', $serviceRequest->metadata['room_id'])
                ->where('hotel_id', $hotel->id)
                ->where('is_active', true)
                ->where('is_available', true)
                ->first();
            
            if ($room) {
                return $room;
            }
        }

        // Fallback: If room type is specified, find a room of that type
        if (isset($serviceRequest->metadata['room_type'])) {
            $room = $hotel->rooms()
                ->where('category', $serviceRequest->metadata['room_type'])
                ->where('is_active', true)
                ->where('is_available', true)
                ->first();
                
            if ($room) {
                return $room;
            }
        }

        // Final fallback: Get the first available room that can accommodate the guests
        $guestCount = ($serviceRequest->metadata['adults'] ?? 1) + ($serviceRequest->metadata['children'] ?? 0);
        
        return $hotel->rooms()
            ->where('is_active', true)
            ->where('is_available', true)
            ->where('max_occupancy', '>=', $guestCount)
            ->orderBy('base_price') // Get the cheapest suitable room
            ->first();
    }

    /**
     * Extract guest information from service request
     * 
     * @param ServiceRequest $serviceRequest
     * @return array
     */
    private function extractGuestInformation(ServiceRequest $serviceRequest): array
    {
        $metadata = $serviceRequest->metadata ?? [];
        
        return [
            'name' => $metadata['guest_name'] ?? $serviceRequest->agent->name,
            'email' => $metadata['guest_email'] ?? $serviceRequest->agent->email,
            'phone' => $metadata['guest_phone'] ?? $serviceRequest->agent->phone ?? null,
            'adults' => $metadata['adults'] ?? $metadata['guest_count'] ?? 1,
            'children' => $metadata['children'] ?? 0,
        ];
    }

    /**
     * Calculate pricing for hotel booking
     * 
     * @param ServiceRequest $serviceRequest
     * @param Room $room
     * @return array
     */
    private function calculateHotelBookingPricing(ServiceRequest $serviceRequest, Room $room): array
    {
        $nights = $serviceRequest->start_date->diffInDays($serviceRequest->end_date);
        
        // Use offered price if available, otherwise use room base price
        $roomRate = $serviceRequest->offered_price ?? $room->base_price;
        
        $subtotal = $roomRate * $nights;
        
        // Calculate tax (assume 10% tax rate)
        $taxRate = 0.10;
        $taxAmount = $subtotal * $taxRate;
        
        // Calculate service fee (assume 5% service fee)
        $serviceFeeRate = 0.05;
        $serviceFee = $subtotal * $serviceFeeRate;
        
        // Apply discount if any
        $discountAmount = 0;
        if (isset($serviceRequest->metadata['discount_percentage'])) {
            $discountAmount = $subtotal * ($serviceRequest->metadata['discount_percentage'] / 100);
        }
        
        $totalAmount = $subtotal + $taxAmount + $serviceFee - $discountAmount;
        
        return [
            'room_rate' => $roomRate,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'service_fee' => $serviceFee,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
        ];
    }

    /**
     * Build booking notes from service request
     * 
     * @param ServiceRequest $serviceRequest
     * @return string
     */
    private function buildBookingNotes(ServiceRequest $serviceRequest): string
    {
        $notes = [];
        
        $notes[] = "Created from approved service request #{$serviceRequest->uuid}";
        
        if ($serviceRequest->agent_notes) {
            $notes[] = "Agent Notes: " . $serviceRequest->agent_notes;
        }
        
        if ($serviceRequest->provider_notes) {
            $notes[] = "Provider Notes: " . $serviceRequest->provider_notes;
        }
        
        if (isset($serviceRequest->metadata['special_requirements'])) {
            $notes[] = "Special Requirements: " . $serviceRequest->metadata['special_requirements'];
        }
        
        return implode("\n\n", $notes);
    }

    /**
     * Link service request to the created booking
     * 
     * @param ServiceRequest $serviceRequest
     * @param mixed $booking
     * @return void
     */
    private function linkServiceRequestToBooking(ServiceRequest $serviceRequest, $booking): void
    {
        // Add booking information to service request metadata
        $metadata = $serviceRequest->metadata ?? [];
        $metadata['booking_created'] = true;
        $metadata['booking_id'] = $booking->id;
        $metadata['booking_type'] = $serviceRequest->provider_type;
        $metadata['booking_reference'] = $booking->booking_reference ?? $booking->id;
        $metadata['booking_created_at'] = now()->toDateTimeString();
        
        $serviceRequest->update([
            'metadata' => $metadata
        ]);
    }
    
    /**
     * Link service request to multiple created bookings
     * 
     * @param ServiceRequest $serviceRequest
     * @param array $bookings
     * @return void
     */
    private function linkServiceRequestToBookings(ServiceRequest $serviceRequest, array $bookings): void
    {
        // Add booking information to service request metadata
        $metadata = $serviceRequest->metadata ?? [];
        $metadata['booking_created'] = true;
        $metadata['booking_count'] = count($bookings);
        $metadata['booking_type'] = $serviceRequest->provider_type;
        $metadata['booking_created_at'] = now()->toDateTimeString();
        
        // Store all booking details
        $bookingDetails = [];
        foreach ($bookings as $booking) {
            $bookingDetails[] = [
                'id' => $booking->id,
                'reference' => $booking->booking_reference ?? $booking->id,
                'room_id' => $booking->room_id ?? null,
                'room_number' => $booking->room?->formatted_room_number ?? null,
                'total_amount' => $booking->total_amount ?? null
            ];
        }
        
        $metadata['bookings'] = $bookingDetails;
        
        // Keep primary booking info for backward compatibility
        $primaryBooking = $bookings[0];
        $metadata['booking_id'] = $primaryBooking->id;
        $metadata['booking_reference'] = $primaryBooking->booking_reference ?? $primaryBooking->id;
        
        $serviceRequest->update([
            'metadata' => $metadata
        ]);
    }

    /**
     * Generate booking reference
     * 
     * @param string $prefix
     * @return string
     */
    private function generateBookingReference(string $prefix): string
    {
        return $prefix . '-' . strtoupper(uniqid()) . '-' . now()->format('ymd');
    }

    /**
     * Check if service request can be converted to booking
     * 
     * @param ServiceRequest $serviceRequest
     * @return bool
     */
    private function canConvertToBooking(ServiceRequest $serviceRequest): bool
    {
        // Must be approved
        if ($serviceRequest->status !== ServiceRequest::STATUS_APPROVED) {
            return false;
        }

        // Must not already have a booking
        if (isset($serviceRequest->metadata['booking_created']) && $serviceRequest->metadata['booking_created']) {
            return false;
        }

        // Must have valid dates
        if (!$serviceRequest->start_date || !$serviceRequest->end_date) {
            return false;
        }

        // Start date must be before or equal to end date
        // (Transport services can be same-day, hotels require at least 1 night)
        if ($serviceRequest->provider_type === ServiceRequest::PROVIDER_HOTEL) {
            // Hotels require start_date < end_date (at least 1 night)
            if ($serviceRequest->start_date->gte($serviceRequest->end_date)) {
                return false;
            }
        } else {
            // Transport and flights can be same-day services
            if ($serviceRequest->start_date->gt($serviceRequest->end_date)) {
                return false;
            }
        }

        // Must have valid item_id (hotel, flight, transport service)
        if (!$serviceRequest->item_id) {
            return false;
        }

        return true;
    }

    /**
     * Check if booking was created from service request
     * 
     * @param mixed $booking
     * @return bool
     */
    public function isFromServiceRequest($booking): bool
    {
        if ($booking instanceof HotelBooking) {
            return $booking->source === 'service_request';
        }
        
        if ($booking instanceof TransportBooking) {
            return $booking->source === 'service_request';
        }
        
        return false;
    }

    /**
     * Get service request that created this booking
     * 
     * @param mixed $booking
     * @return ServiceRequest|null
     */
    public function getSourceServiceRequest($booking): ?ServiceRequest
    {
        if (!$this->isFromServiceRequest($booking)) {
            return null;
        }

        // Look for service request that has this booking in its metadata
        $bookingType = 'unknown';
        if ($booking instanceof HotelBooking) {
            $bookingType = 'hotel';
        } elseif ($booking instanceof TransportBooking) {
            $bookingType = 'transport';
        }
        
        return ServiceRequest::where('metadata->booking_id', $booking->id)
            ->where('metadata->booking_type', $bookingType)
            ->first();
    }

    /**
     * Extract passenger information from transport service request
     * 
     * @param ServiceRequest $serviceRequest
     * @return array
     */
    private function extractTransportPassengerInformation(ServiceRequest $serviceRequest): array
    {
        $metadata = $serviceRequest->metadata ?? [];
        
        // Try to get passenger info from metadata
        $adults = $metadata['adults'] ?? $metadata['passenger_count'] ?? 1;
        $children = $metadata['children'] ?? 0;
        $infants = $metadata['infants'] ?? 0;
        
        // Get passenger contact info
        $passengerName = $metadata['passenger_name'] ?? $serviceRequest->agent->name;
        $passengerEmail = $metadata['passenger_email'] ?? $serviceRequest->agent->email;
        $passengerPhone = $metadata['passenger_phone'] ?? $serviceRequest->agent->phone ?? null;
        
        return [
            'adults' => $adults,
            'children' => $children,
            'infants' => $infants,
            'total_passengers' => $adults + $children + $infants,
            'name' => $passengerName,
            'email' => $passengerEmail,
            'phone' => $passengerPhone,
        ];
    }

    /**
     * Calculate transport booking pricing
     * 
     * @param ServiceRequest $serviceRequest
     * @param TransportService $transportService
     * @return array
     */
    private function calculateTransportBookingPricing(ServiceRequest $serviceRequest, TransportService $transportService): array
    {
        // For approved service requests, use the exact approved price as total
        // The provider has already agreed to this price, so no additional fees
        if ($serviceRequest->offered_price) {
            $totalAmount = $serviceRequest->offered_price;
            $baseRate = $totalAmount; // Use approved price as base rate
            $taxAmount = 0; // No additional tax on approved price
            $serviceFee = 0; // No additional service fee on approved price
        } else {
            // Fallback for requests without offered price
            $baseRate = $transportService->price ?? 100.00;
            
            // Calculate tax (10% default)
            $taxRate = 0.10;
            $taxAmount = $baseRate * $taxRate;
            
            // Service fee (5% default)
            $serviceFeeRate = 0.05;
            $serviceFee = $baseRate * $serviceFeeRate;
            
            // Total calculation
            $totalAmount = $baseRate + $taxAmount + $serviceFee;
        }
        
        // Discount (from metadata or 0)
        $discountAmount = $serviceRequest->metadata['discount_amount'] ?? 0;
        $totalAmount -= $discountAmount;
        
        return [
            'base_rate' => round($baseRate, 2),
            'tax_amount' => round($taxAmount, 2),
            'service_fee' => round($serviceFee, 2),
            'discount_amount' => round($discountAmount, 2),
            'total_amount' => round($totalAmount, 2),
        ];
    }

    /**
     * Extract trip details from transport service request
     * 
     * @param ServiceRequest $serviceRequest
     * @return array
     */
    private function extractTransportTripDetails(ServiceRequest $serviceRequest): array
    {
        $metadata = $serviceRequest->metadata ?? [];
        
        // Extract pickup and dropoff locations
        $pickupLocation = $metadata['pickup_location'] ?? 'TBD';
        $dropoffLocation = $metadata['dropoff_location'] ?? 'TBD';
        
        // Extract datetime information
        $pickupDatetime = isset($metadata['pickup_datetime']) 
            ? Carbon::parse($metadata['pickup_datetime']) 
            : $serviceRequest->start_date;
            
        $dropoffDatetime = isset($metadata['dropoff_datetime']) 
            ? Carbon::parse($metadata['dropoff_datetime']) 
            : $serviceRequest->end_date;
        
        return [
            'pickup_location' => $pickupLocation,
            'dropoff_location' => $dropoffLocation,
            'pickup_datetime' => $pickupDatetime,
            'dropoff_datetime' => $dropoffDatetime,
            'vehicle_details' => $metadata['vehicle_details'] ?? null,
            'driver_details' => $metadata['driver_details'] ?? null,
            'route_details' => $metadata['route_details'] ?? null,
        ];
    }

    /**
     * Build notes for transport booking
     * 
     * @param ServiceRequest $serviceRequest
     * @return string
     */
    private function buildTransportBookingNotes(ServiceRequest $serviceRequest): string
    {
        $notes = [];
        
        $notes[] = "Transport booking created from service request #{$serviceRequest->id}";
        $notes[] = "Requested by: {$serviceRequest->agent->name} ({$serviceRequest->agent->email})";
        
        if ($serviceRequest->agent_notes) {
            $notes[] = "Agent Notes: " . $serviceRequest->agent_notes;
        }
        
        if ($serviceRequest->provider_notes) {
            $notes[] = "Provider Notes: " . $serviceRequest->provider_notes;
        }
        
        if (isset($serviceRequest->metadata['special_requirements'])) {
            $notes[] = "Special Requirements: " . $serviceRequest->metadata['special_requirements'];
        }
        
        if ($serviceRequest->offered_price) {
            $notes[] = "Approved Price: {$serviceRequest->currency} {$serviceRequest->offered_price}";
        }
        
        return implode("\n\n", $notes);
    }

    /**
     * Extract flight passenger information from service request
     * 
     * @param ServiceRequest $serviceRequest
     * @return array
     */
    private function extractFlightPassengerInformation(ServiceRequest $serviceRequest): array
    {
        $metadata = $serviceRequest->metadata ?? [];
        
        // Get passenger contact info from metadata or agent info
        $passengerName = $metadata['passenger_name'] ?? $serviceRequest->agent->name;
        $passengerEmail = $metadata['passenger_email'] ?? $serviceRequest->agent->email;
        $passengerPhone = $metadata['passenger_phone'] ?? $serviceRequest->agent->phone ?? null;
        
        // Extract detailed passenger information if available
        $passengerDetails = $metadata['passenger_details'] ?? [];
        
        return [
            'name' => $passengerName,
            'email' => $passengerEmail,
            'phone' => $passengerPhone,
            'details' => $passengerDetails
        ];
    }

    /**
     * Calculate flight booking pricing
     * 
     * @param ServiceRequest $serviceRequest
     * @param Flight $flight
     * @param string $flightClass
     * @return array
     */
    private function calculateFlightBookingPricing(ServiceRequest $serviceRequest, Flight $flight, string $flightClass = 'economy'): array
    {
        $requestedSeats = $serviceRequest->requested_quantity ?? 1;
        
        // For approved service requests, use the offered price per seat
        if ($serviceRequest->offered_price) {
            $seatPrice = $serviceRequest->offered_price / $requestedSeats;
        } else {
            // Get price based on flight class
            $seatPrice = $this->getFlightPriceForClass($flight, $flightClass);
        }
        
        $subtotal = $seatPrice * $requestedSeats;
        
        // Calculate tax (assume 12% tax rate for flights)
        $taxRate = 0.12;
        $taxAmount = $subtotal * $taxRate;
        
        // Calculate service fee (assume 3% service fee for flights)
        $serviceFeeRate = 0.03;
        $serviceFee = $subtotal * $serviceFeeRate;
        
        // Apply discount if any
        $discountAmount = $serviceRequest->metadata['discount_amount'] ?? 0;
        
        $totalAmount = $subtotal + $taxAmount + $serviceFee - $discountAmount;
        
        return [
            'seat_price' => round($seatPrice, 2),
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($taxAmount, 2),
            'service_fee' => round($serviceFee, 2),
            'discount_amount' => round($discountAmount, 2),
            'total_amount' => round($totalAmount, 2),
        ];
    }

    /**
     * Get flight price for specific class
     * 
     * @param Flight $flight
     * @param string $flightClass
     * @return float
     */
    private function getFlightPriceForClass(Flight $flight, string $flightClass): float
    {
        switch (strtolower($flightClass)) {
            case 'economy':
                return $flight->economy_price ?? 300.00;
            case 'business':
                return $flight->business_price ?? $flight->economy_price ?? 800.00;
            case 'first':
            case 'first_class':
                return $flight->first_class_price ?? $flight->business_price ?? $flight->economy_price ?? 1500.00;
            default:
                return $flight->economy_price ?? 300.00;
        }
    }

    /**
     * Build notes for flight booking
     * 
     * @param ServiceRequest $serviceRequest
     * @return string
     */
    private function buildFlightBookingNotes(ServiceRequest $serviceRequest): string
    {
        $notes = [];
        
        $notes[] = "Flight booking created from approved service request #{$serviceRequest->uuid}";
        $notes[] = "Requested by: {$serviceRequest->agent->name} ({$serviceRequest->agent->email})";
        
        if ($serviceRequest->agent_notes) {
            $notes[] = "Agent Notes: " . $serviceRequest->agent_notes;
        }
        
        if ($serviceRequest->provider_notes) {
            $notes[] = "Provider Notes: " . $serviceRequest->provider_notes;
        }
        
        if (isset($serviceRequest->metadata['special_requirements'])) {
            $notes[] = "Special Requirements: " . $serviceRequest->metadata['special_requirements'];
        }
        
        if ($serviceRequest->offered_price) {
            $currency = $serviceRequest->currency ?? 'USD';
            $notes[] = "Approved Price: {$currency} {$serviceRequest->offered_price}";
        }
        
        return implode("\n\n", $notes);
    }
}
