<?php

namespace App\Services;

use App\Models\Room;
use App\Models\Hotel;
use App\Models\HotelBooking;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * RoomAvailabilityService
 * 
 * Handles room availability checking, room selection, and booking assignment
 * to prevent overbooking and ensure proper room management
 */
class RoomAvailabilityService
{
    /**
     * Check which rooms are available for the given hotel and date range
     * 
     * @param int $hotelId
     * @param Carbon $checkInDate
     * @param Carbon $checkOutDate
     * @param int $guestCount
     * @param array $preferences Optional room preferences (category, amenities, etc.)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableRooms(int $hotelId, Carbon $checkInDate, Carbon $checkOutDate, int $guestCount = 1, array $preferences = [])
    {
        try {
            // Get all active rooms for the hotel
            $query = Room::where('hotel_id', $hotelId)
                ->where('is_active', true)
                ->where('is_available', true)
                ->where('max_occupancy', '>=', $guestCount);

            // Apply room preferences if specified
            if (!empty($preferences['category'])) {
                $query->where('category', $preferences['category']);
            }

            if (!empty($preferences['bed_type'])) {
                $query->where('bed_type', $preferences['bed_type']);
            }

            if (!empty($preferences['min_size'])) {
                $query->where('size_sqm', '>=', $preferences['min_size']);
            }

            // Get rooms and filter out those with conflicting bookings
            $rooms = $query->get();
            
            return $rooms->filter(function (Room $room) use ($checkInDate, $checkOutDate) {
                return $this->isRoomAvailableForDates($room, $checkInDate, $checkOutDate);
            })->sortBy(['base_price', 'room_number']); // Sort by price then room number

        } catch (\Exception $e) {
            Log::error('Error checking room availability', [
                'hotel_id' => $hotelId,
                'check_in' => $checkInDate->format('Y-m-d'),
                'check_out' => $checkOutDate->format('Y-m-d'),
                'guest_count' => $guestCount,
                'error' => $e->getMessage()
            ]);
            
            return collect();
        }
    }

    /**
     * Check if a specific room is available for the given date range
     * 
     * @param Room $room
     * @param Carbon $checkInDate
     * @param Carbon $checkOutDate
     * @return bool
     */
    public function isRoomAvailableForDates(Room $room, Carbon $checkInDate, Carbon $checkOutDate): bool
    {
        if (!$room->is_available || !$room->is_active) {
            return false;
        }

        // Check for overlapping bookings
        $conflictingBookings = HotelBooking::where('room_id', $room->id)
            ->whereIn('status', ['confirmed', 'checked_in', 'pending']) // Include pending bookings
            ->where(function ($query) use ($checkInDate, $checkOutDate) {
                // Check for date overlap: booking conflicts if:
                // 1. Booking check-in is before our check-out AND
                // 2. Booking check-out is after our check-in
                $query->where('check_in_date', '<', $checkOutDate)
                      ->where('check_out_date', '>', $checkInDate);
            })
            ->exists();

        return !$conflictingBookings;
    }

    /**
     * Get available rooms formatted for selection UI
     * 
     * @param ServiceRequest $serviceRequest
     * @return array
     */
    public function getAvailableRoomsForServiceRequest(ServiceRequest $serviceRequest): array
    {
        if (!$serviceRequest->start_date || !$serviceRequest->end_date || !$serviceRequest->item_id) {
            return [
                'success' => false,
                'message' => 'Invalid service request data for room availability check',
                'rooms' => []
            ];
        }

        try {
            $hotel = Hotel::find($serviceRequest->item_id);
            if (!$hotel) {
                return [
                    'success' => false,
                    'message' => 'Hotel not found',
                    'rooms' => []
                ];
            }

            $guestCount = ($serviceRequest->metadata['adults'] ?? 1) + ($serviceRequest->metadata['children'] ?? 0);
            $preferences = [
                'category' => $serviceRequest->metadata['room_type'] ?? null,
                'bed_type' => $serviceRequest->metadata['bed_type'] ?? null,
            ];
            
            // Remove null values from preferences to avoid filtering by them
            $preferences = array_filter($preferences, fn($value) => !is_null($value));

            $availableRooms = $this->getAvailableRooms(
                $hotel->id,
                $serviceRequest->start_date,
                $serviceRequest->end_date,
                $guestCount,
                $preferences
            );

            $formattedRooms = $availableRooms->map(function (Room $room) use ($serviceRequest) {
                $nights = $serviceRequest->start_date->diffInDays($serviceRequest->end_date);
                $totalPrice = $room->base_price * $nights;

                return [
                    'id' => $room->id,
                    'room_number' => $room->formatted_room_number,
                    'name' => $room->name,
                    'category' => $room->category,
                    'category_name' => $room->category_name,
                    'bed_info' => $room->bed_info,
                    'max_occupancy' => $room->max_occupancy,
                    'size_sqm' => $room->size_sqm,
                    'base_price' => $room->base_price,
                    'total_price' => $totalPrice,
                    'amenities' => $room->amenities ?? [],
                    'description' => $room->description,
                    'main_image' => $room->main_image,
                    'is_recommended' => $this->isRoomRecommended($room, $serviceRequest)
                ];
            })->values();

            return [
                'success' => true,
                'message' => count($formattedRooms) . ' rooms available',
                'rooms' => $formattedRooms,
                'hotel' => [
                    'id' => $hotel->id,
                    'name' => $hotel->name,
                    'check_in_time' => $hotel->check_in_time?->format('H:i'),
                    'check_out_time' => $hotel->check_out_time?->format('H:i'),
                ],
                'booking_details' => [
                    'check_in_date' => $serviceRequest->start_date->format('Y-m-d'),
                    'check_out_date' => $serviceRequest->end_date->format('Y-m-d'),
                    'nights' => $serviceRequest->start_date->diffInDays($serviceRequest->end_date),
                    'guest_count' => $guestCount
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Error getting available rooms for service request', [
                'service_request_id' => $serviceRequest->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error checking room availability: ' . $e->getMessage(),
                'rooms' => []
            ];
        }
    }

    /**
     * Reserve/assign a specific room for a service request approval
     * 
     * @param ServiceRequest $serviceRequest
     * @param int $roomId
     * @return array
     */
    public function assignRoomToServiceRequest(ServiceRequest $serviceRequest, int $roomId): array
    {
        try {
            DB::beginTransaction();

            $room = Room::find($roomId);
            if (!$room) {
                return [
                    'success' => false,
                    'message' => 'Room not found',
                    'error_code' => 'ROOM_NOT_FOUND'
                ];
            }

            // Verify room belongs to the correct hotel
            if ($room->hotel_id != $serviceRequest->item_id) {
                return [
                    'success' => false,
                    'message' => 'Room does not belong to the requested hotel',
                    'error_code' => 'ROOM_HOTEL_MISMATCH'
                ];
            }

            // Check if room is still available
            if (!$this->isRoomAvailableForDates($room, $serviceRequest->start_date, $serviceRequest->end_date)) {
                return [
                    'success' => false,
                    'message' => 'Room is no longer available for the requested dates',
                    'error_code' => 'ROOM_NOT_AVAILABLE'
                ];
            }

            // Update service request metadata with assigned room
            $metadata = $serviceRequest->metadata ?? [];
            $metadata['assigned_room_id'] = $room->id;
            $metadata['assigned_room_number'] = $room->formatted_room_number;
            $metadata['assigned_room_name'] = $room->name;
            $metadata['assigned_room_category'] = $room->category;
            $metadata['room_assignment_timestamp'] = now()->toDateTimeString();

            $serviceRequest->update(['metadata' => $metadata]);

            DB::commit();

            Log::info('Room assigned to service request', [
                'service_request_id' => $serviceRequest->id,
                'room_id' => $room->id,
                'room_number' => $room->formatted_room_number
            ]);

            return [
                'success' => true,
                'message' => 'Room successfully assigned',
                'room' => [
                    'id' => $room->id,
                    'room_number' => $room->formatted_room_number,
                    'name' => $room->name,
                    'category_name' => $room->category_name
                ]
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error assigning room to service request', [
                'service_request_id' => $serviceRequest->id,
                'room_id' => $roomId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to assign room: ' . $e->getMessage(),
                'error_code' => 'ASSIGNMENT_ERROR'
            ];
        }
    }

    /**
     * Get room assignment status for a service request
     * 
     * @param ServiceRequest $serviceRequest
     * @return array
     */
    public function getRoomAssignmentStatus(ServiceRequest $serviceRequest): array
    {
        $metadata = $serviceRequest->metadata ?? [];
        
        if (!isset($metadata['assigned_room_id'])) {
            return [
                'has_assignment' => false,
                'status' => 'not_assigned',
                'message' => 'No room assigned'
            ];
        }

        $room = Room::find($metadata['assigned_room_id']);
        if (!$room) {
            return [
                'has_assignment' => true,
                'status' => 'invalid_assignment',
                'message' => 'Assigned room no longer exists',
                'assignment_data' => $metadata
            ];
        }

        // Check if room is still available (in case of conflicts)
        $isStillAvailable = $this->isRoomAvailableForDates(
            $room, 
            $serviceRequest->start_date, 
            $serviceRequest->end_date
        );

        return [
            'has_assignment' => true,
            'status' => $isStillAvailable ? 'valid_assignment' : 'conflict_detected',
            'message' => $isStillAvailable ? 'Room assignment valid' : 'Room assignment conflict detected',
            'room' => [
                'id' => $room->id,
                'room_number' => $room->formatted_room_number,
                'name' => $room->name,
                'category_name' => $room->category_name,
                'is_available' => $isStillAvailable
            ],
            'assignment_timestamp' => $metadata['room_assignment_timestamp'] ?? null
        ];
    }

    /**
     * Determine if a room is recommended for the service request
     * 
     * @param Room $room
     * @param ServiceRequest $serviceRequest
     * @return bool
     */
    private function isRoomRecommended(Room $room, ServiceRequest $serviceRequest): bool
    {
        $metadata = $serviceRequest->metadata ?? [];
        
        // Exact room type match
        if (!empty($metadata['room_type']) && $room->category === $metadata['room_type']) {
            return true;
        }

        // Exact bed type match
        if (!empty($metadata['bed_type']) && $room->bed_type === $metadata['bed_type']) {
            return true;
        }

        // Price match (within 10% of requested price)
        if (!empty($serviceRequest->requested_price)) {
            $nights = $serviceRequest->start_date->diffInDays($serviceRequest->end_date);
            $roomTotalPrice = $room->base_price * $nights;
            $priceDifference = abs($roomTotalPrice - $serviceRequest->requested_price) / $serviceRequest->requested_price;
            
            if ($priceDifference <= 0.1) { // Within 10%
                return true;
            }
        }

        return false;
    }

    /**
     * Get room occupancy statistics for a hotel
     * 
     * @param int $hotelId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return array
     */
    public function getHotelOccupancyStats(int $hotelId, Carbon $startDate = null, Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now();
        $endDate = $endDate ?? now()->addDays(30);

        try {
            $hotel = Hotel::find($hotelId);
            if (!$hotel) {
                return [
                    'success' => false,
                    'message' => 'Hotel not found'
                ];
            }

            $totalRooms = $hotel->rooms()->where('is_active', true)->count();
            if ($totalRooms === 0) {
                return [
                    'success' => true,
                    'total_rooms' => 0,
                    'occupancy_rate' => 0,
                    'available_rooms' => 0,
                    'booked_rooms' => 0
                ];
            }

            // Count unique rooms with bookings in the date range
            $bookedRoomsCount = HotelBooking::where('hotel_id', $hotelId)
                ->whereIn('status', ['confirmed', 'checked_in', 'pending'])
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->where('check_in_date', '<', $endDate)
                          ->where('check_out_date', '>', $startDate);
                })
                ->distinct('room_id')
                ->count('room_id');

            $availableRooms = $totalRooms - $bookedRoomsCount;
            $occupancyRate = $totalRooms > 0 ? round(($bookedRoomsCount / $totalRooms) * 100, 1) : 0;

            return [
                'success' => true,
                'hotel_id' => $hotelId,
                'hotel_name' => $hotel->name,
                'date_range' => [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d')
                ],
                'total_rooms' => $totalRooms,
                'available_rooms' => $availableRooms,
                'booked_rooms' => $bookedRoomsCount,
                'occupancy_rate' => $occupancyRate
            ];

        } catch (\Exception $e) {
            Log::error('Error getting hotel occupancy stats', [
                'hotel_id' => $hotelId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error calculating occupancy stats: ' . $e->getMessage()
            ];
        }
    }
}