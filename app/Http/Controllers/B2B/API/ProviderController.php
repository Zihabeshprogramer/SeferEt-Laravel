<?php

namespace App\Http\Controllers\B2B\API;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\TransportService;
use App\Models\Flight;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Provider API Controller - AJAX Provider Integration
 * 
 * Handles real-time provider search, availability checking,
 * pricing calculations, and commission management.
 */
class ProviderController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:travel_agent']);
    }
    
    /**
     * Batch fetch providers by IDs for draft loading
     */
    public function batchFetch(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'provider_ids' => 'required|array',
                'provider_ids.*' => 'required|integer',
                'service_type' => 'required|in:hotel,flight,transport'
            ]);
            
            $providerIds = $request->provider_ids;
            $serviceType = $request->service_type;
            
            $providers = [];
            
            switch ($serviceType) {
                case 'hotel':
                    $providers = Hotel::whereIn('id', $providerIds)
                        ->with(['provider', 'rooms'])
                        ->get()
                        ->map(function($hotel) {
                            return $this->formatHotelForAPI($hotel);
                        });
                    break;
                    
                case 'flight':
                    $providers = Flight::whereIn('id', $providerIds)
                        ->with('provider')
                        ->get()
                        ->map(function($flight) {
                            return $this->formatFlightForAPI($flight);
                        });
                    break;
                    
                case 'transport':
                    $providers = TransportService::whereIn('id', $providerIds)
                        ->with('provider')
                        ->get()
                        ->map(function($transport) {
                            return $this->formatTransportForAPI($transport);
                        });
                    break;
            }
            
            return response()->json([
                'success' => true,
                'providers' => $providers,
                'message' => "Fetched {$providers->count()} {$serviceType} providers"
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            \Log::error('Batch fetch error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'provider_ids' => $request->provider_ids ?? null,
                'service_type' => $request->service_type ?? null
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch providers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generic search method for all provider types
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $type = $request->get('type', 'hotels'); // Default to hotels
            
            switch ($type) {
                case 'hotels':
                    return $this->searchHotels($request);
                case 'transport':
                    return $this->searchTransport($request);
                case 'flights':
                    return $this->searchFlights($request);
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid provider type specified'
                    ], 400);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search available hotels with advanced filtering
     */
    public function searchHotels(Request $request): JsonResponse
    {
        try {
            // Use explicit where clauses to ensure proper filtering
            $query = Hotel::where('status', 'active')
                          ->where('is_active', true)
                          ->with(['provider', 'rooms']);
            
            // Location filters
            if ($request->filled('city')) {
                $query->inCity($request->city);
            }
            
            if ($request->filled('country')) {
                $query->where('country', $request->country);
            }
            
            // Property filters
            if ($request->filled('star_rating')) {
                $query->withStarRating($request->star_rating);
            }
            
            // Note: 'type' parameter is used for service type (hotels/flights/transport)
            // For hotel property type filtering, use 'hotel_type' parameter instead
            if ($request->filled('hotel_type')) {
                $query->ofType($request->hotel_type);
            }
            
            // Amenity filters
            if ($request->filled('amenities')) {
                $amenities = is_array($request->amenities) ? $request->amenities : [$request->amenities];
                foreach ($amenities as $amenity) {
                    $query->whereJsonContains('amenities', $amenity);
                }
            }
            
            // Distance filters
            if ($request->filled('max_distance_to_haram')) {
                $query->where('distance_to_haram', '<=', $request->max_distance_to_haram);
            }
            
            // Search by name
            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }
            
            // Price range (if rooms are available)
            if ($request->filled('min_price') || $request->filled('max_price')) {
                $query->whereHas('rooms', function($roomQuery) use ($request) {
                    if ($request->filled('min_price')) {
                        $roomQuery->where('base_price', '>=', $request->min_price);
                    }
                    if ($request->filled('max_price')) {
                        $roomQuery->where('base_price', '<=', $request->max_price);
                    }
                });
            }
            
            // Availability check
            if ($request->filled('check_in') && $request->filled('check_out')) {
                $query = $this->filterHotelsByAvailability(
                    $query, 
                    $request->check_in, 
                    $request->check_out,
                    $request->get('rooms', 1),
                    $request->get('guests', 2)
                );
            }
            
            // Sorting
            $sortField = $request->get('sort', 'star_rating');
            $sortDirection = $request->get('sort_direction', 'desc');
            
            if ($sortField === 'distance_to_haram') {
                $query->orderBy('distance_to_haram', 'asc');
            } elseif ($sortField === 'price') {
                $query->select('hotels.*')
                      ->leftJoin('rooms', 'hotels.id', '=', 'rooms.hotel_id')
                      ->orderBy('rooms.base_price', $sortDirection);
            } else {
                $query->orderBy($sortField, $sortDirection);
            }
            


            $hotels = $query->take($request->get('limit', 20))->get();

            $results = $hotels->map(function ($hotel) {
                $rooms = $hotel->rooms->map(function ($room) {
                    $effectivePrice = $room->getEffectivePrice();
                    
                    return [
                        'id' => $room->id,
                        'name' => $room->name,
                        'room_number' => $room->room_number,
                        'category' => $room->category,
                        'category_name' => $room->category_name,
                        'base_price' => (float) $room->base_price,
                        'effective_price' => (float) $effectivePrice,
                        'max_occupancy' => $room->max_occupancy,
                        'bed_type' => $room->bed_type,
                        'bed_info' => $room->bed_info,
                        'bed_count' => $room->bed_count,
                        'size_sqm' => $room->size_sqm,
                        'amenities' => $room->amenities ?? [],
                        'images' => $room->images ?? [],
                        'description' => $room->description,
                        'is_available' => $room->is_available,
                        'is_active' => $room->is_active,
                    ];
                });
                
                // Calculate room pricing summary
                $availableRooms = $rooms->where('is_available', true);
                $roomPrices = $rooms->pluck('effective_price')->filter()->toArray();
                $priceRange = !empty($roomPrices) ? [
                    'min' => min($roomPrices),
                    'max' => max($roomPrices),
                    'currency' => 'USD'
                ] : null;
                    
                return [
                    'id' => $hotel->id,
                    'hotel_name' => $hotel->name,
                    'name' => $hotel->name,
                    'star_rating' => $hotel->star_rating,
                    'star_display' => $hotel->star_display,
                    'type' => $hotel->type,
                    'address' => $hotel->full_address,
                    'city' => $hotel->city,
                    'country' => $hotel->country,
                    'distance_to_haram' => $hotel->distance_to_haram,
                    'distance_to_airport' => $hotel->distance_to_airport,
                    'main_image' => $hotel->main_image,
                    'images' => $hotel->images ?? [],
                    'amenities' => $hotel->amenities ?? [],
                    'provider' => [
                        'id' => $hotel->provider->id,
                        'name' => $hotel->provider->name,
                        'company_name' => $hotel->provider->company_name,
                    ],
                    // Room data
                    'rooms' => $rooms,
                    'total_rooms' => $rooms->count(),
                    'available_rooms' => $availableRooms->count(),
                    'room_types_count' => $rooms->pluck('category')->unique()->count(),
                    // Pricing information
                    'price_range' => $priceRange,
                    'base_price' => !empty($roomPrices) ? min($roomPrices) : null,
                    'estimated_price' => !empty($roomPrices) ? min($roomPrices) : null,
                    // Legacy room_types for backward compatibility
                    'room_types' => $rooms->take(3), // Limit to first 3 rooms for legacy compatibility
                    'average_rating' => $hotel->average_rating,
                    'total_reviews' => $hotel->total_reviews,
                    'policies' => [
                        'cancellation' => $hotel->policy_cancellation,
                        'children' => $hotel->policy_children,
                        'pets' => $hotel->policy_pets
                    ]
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $results,
                'total' => $results->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search hotels: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search available transport services
     */
    public function searchTransport(Request $request): JsonResponse
    {
        try {
            $query = TransportService::active()->with('provider');
            
            // Transport type filter
            if ($request->filled('transport_type')) {
                $query->ofType($request->transport_type);
            }
            
            // Route type filter
            if ($request->filled('route_type')) {
                $query->forRoute($request->route_type);
            }
            
            // Capacity filter
            if ($request->filled('min_passengers')) {
                $query->where('max_passengers', '>=', $request->min_passengers);
            }
            
            // Route search
            if ($request->filled('from') && $request->filled('to')) {
                $query->where(function ($q) use ($request) {
                    $q->whereJsonContains('routes', [
                        'from' => $request->from,
                        'to' => $request->to
                    ])->orWhere(function ($subQ) use ($request) {
                        $subQ->whereJsonContains('pickup_locations', $request->from)
                             ->whereJsonContains('dropoff_locations', $request->to);
                    });
                });
            }
            
            // Search by service name
            if ($request->filled('search')) {
                $query->where('service_name', 'like', '%' . $request->search . '%');
            }
            
            // Price filter (if available)
            if ($request->filled('max_price')) {
                $query->where('price', '<=', $request->max_price);
            }
            
            $transports = $query->take($request->get('limit', 20))->get();

            $results = $transports->map(function ($transport) {
                return [
                    'id' => $transport->id,
                    'service_name' => $transport->service_name,
                    'transport_type' => $transport->transport_type,
                    'route_type' => $transport->route_type,
                    'max_passengers' => $transport->max_passengers,
                    'base_price' => $transport->price,
                    'routes' => $transport->routes ?? [],
                    'pickup_locations' => $transport->pickup_locations ?? [],
                    'dropoff_locations' => $transport->dropoff_locations ?? [],
                    'specifications' => $transport->specifications ?? [],
                    'operating_hours' => $transport->operating_hours ?? [],
                    'images' => $transport->images ?? [],
                    'provider' => [
                        'id' => $transport->provider->id,
                        'name' => $transport->provider->name,
                        'company_name' => $transport->provider->company_name,
                    ]
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $results,
                'total' => $results->count()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search transport services: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search available flights
     */
    public function searchFlights(Request $request): JsonResponse
    {
        try {
            $query = Flight::active()->available()->with('provider');
            
            // Route filters
            if ($request->filled('departure_airport')) {
                $query->departingFrom($request->departure_airport);
            }
            
            if ($request->filled('arrival_airport')) {
                $query->arrivingAt($request->arrival_airport);
            }
            
            // Date filters
            if ($request->filled('departure_date')) {
                $query->whereDate('departure_datetime', $request->departure_date);
            }
            
            if ($request->filled('return_date')) {
                // For return flights, search within a reasonable range
                $query->orWhereDate('departure_datetime', $request->return_date);
            }
            
            // Time preferences
            if ($request->filled('preferred_time')) {
                $preferredTime = $request->preferred_time;
                if ($preferredTime === 'morning') {
                    $query->whereTime('departure_datetime', '>=', '06:00')
                          ->whereTime('departure_datetime', '<=', '12:00');
                } elseif ($preferredTime === 'afternoon') {
                    $query->whereTime('departure_datetime', '>=', '12:00')
                          ->whereTime('departure_datetime', '<=', '18:00');
                } elseif ($preferredTime === 'evening') {
                    $query->whereTime('departure_datetime', '>=', '18:00')
                          ->whereTime('departure_datetime', '<=', '23:59');
                }
            }
            
            // Capacity requirements
            if ($request->filled('required_seats')) {
                $query->where('available_seats', '>=', $request->required_seats);
            }
            
            // Price range
            if ($request->filled('max_price')) {
                $query->where('economy_price', '<=', $request->max_price);
            }
            
            // Airline filter
            if ($request->filled('airline')) {
                $query->where('airline', $request->airline);
            }
            
            // Flight source filter (own flights vs platform flights)
            $flightSource = $request->get('flight_source', 'all');
            if ($flightSource === 'own') {
                $query->where('provider_id', Auth::id());
            } elseif ($flightSource === 'platform') {
                $query->where('provider_id', '!=', Auth::id());
            }
            
            // Sorting
            $sortField = $request->get('sort', 'departure_datetime');
            $sortDirection = $request->get('sort_direction', 'asc');
            $query->orderBy($sortField, $sortDirection);
            
            $flights = $query->take($request->get('limit', 20))->get();
            
            $results = $flights->map(function ($flight) {
                return [
                    'id' => $flight->id,
                    'flight_number' => $flight->flight_number,
                    'airline' => $flight->airline,
                    'departure_airport' => $flight->departure_airport,
                    'arrival_airport' => $flight->arrival_airport,
                    'route' => $flight->route,
                    'departure_datetime' => $flight->departure_datetime->format('Y-m-d H:i'),
                    'arrival_datetime' => $flight->arrival_datetime->format('Y-m-d H:i'),
                    'duration' => $flight->formatted_duration,
                    'total_seats' => $flight->total_seats,
                    'available_seats' => $flight->available_seats,
                    'occupancy_rate' => $flight->occupancy_rate,
                    'pricing' => [
                        'economy' => $flight->economy_price,
                        'business' => $flight->business_price,
                        'first_class' => $flight->first_class_price,
                        'currency' => $flight->currency
                    ],
                    'aircraft_type' => $flight->aircraft_type,
                    'amenities' => $flight->amenities ?? [],
                    'baggage_allowance' => $flight->baggage_allowance ?? [],
                    'meal_service' => $flight->meal_service ?? [],
                    'provider' => [
                        'id' => $flight->provider->id,
                        'name' => $flight->provider->name,
                        'is_own' => $flight->provider_id === Auth::id()
                    ]
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $results,
                'total' => $results->count()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search flights: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate package pricing with selected providers
     */
    public function calculatePricing(Request $request): JsonResponse
    {
        try {
            $basePrice = $request->get('base_price', 0);
            $totalPrice = $basePrice;
            
            $breakdown = [
                'base_price' => $basePrice,
                'hotels' => [],
                'transport' => [],
                'flights' => [],
                'activities' => [],
                'commissions' => [],
                'total_commission' => 0,
                'final_total' => 0
            ];
            
            // Calculate hotel costs
            if ($request->filled('selected_hotels')) {
                foreach ($request->selected_hotels as $hotelData) {
                    $hotel = Hotel::find($hotelData['hotel_id']);
                    if ($hotel) {
                        $nights = $hotelData['nights'] ?? 1;
                        $rooms = $hotelData['rooms_needed'] ?? 1;
                        $roomPrice = $hotelData['room_price'] ?? 100; // Default or from room type
                        $markup = $hotelData['markup_percentage'] ?? 0;
                        
                        $totalRoomCost = $roomPrice * $nights * $rooms;
                        $markupAmount = $totalRoomCost * ($markup / 100);
                        $finalHotelCost = $totalRoomCost + $markupAmount;
                        
                        $hotelBreakdown = [
                            'hotel_id' => $hotel->id,
                            'hotel_name' => $hotel->name,
                            'nights' => $nights,
                            'rooms' => $rooms,
                            'base_cost' => $totalRoomCost,
                            'markup_percentage' => $markup,
                            'markup_amount' => $markupAmount,
                            'final_cost' => $finalHotelCost
                        ];
                        
                        $breakdown['hotels'][] = $hotelBreakdown;
                        $totalPrice += $finalHotelCost;
                    }
                }
            }
            
            // Calculate transport costs
            if ($request->filled('selected_transport')) {
                foreach ($request->selected_transport as $transportData) {
                    $transport = TransportService::find($transportData['transport_id']);
                    if ($transport) {
                        $passengers = $transportData['passengers'] ?? 1;
                        $baseTransportCost = $transport->price * $passengers;
                        $markup = $transportData['markup_percentage'] ?? 0;
                        $markupAmount = $baseTransportCost * ($markup / 100);
                        $finalTransportCost = $baseTransportCost + $markupAmount;
                        
                        $transportBreakdown = [
                            'transport_id' => $transport->id,
                            'service_name' => $transport->service_name,
                            'passengers' => $passengers,
                            'base_cost' => $baseTransportCost,
                            'markup_percentage' => $markup,
                            'markup_amount' => $markupAmount,
                            'final_cost' => $finalTransportCost
                        ];
                        
                        $breakdown['transport'][] = $transportBreakdown;
                        $totalPrice += $finalTransportCost;
                    }
                }
            }
            
            // Calculate flight costs
            if ($request->filled('selected_flights')) {
                foreach ($request->selected_flights as $flightData) {
                    $flight = Flight::find($flightData['flight_id']);
                    if ($flight) {
                        $seats = $flightData['seats_allocated'] ?? 1;
                        $flightClass = $flightData['class'] ?? 'economy';
                        $flightPrice = $flight->getPriceForClass($flightClass);
                        $baseFlightCost = $flightPrice * $seats;
                        $markup = $flightData['markup_percentage'] ?? 0;
                        $markupAmount = $baseFlightCost * ($markup / 100);
                        $finalFlightCost = $baseFlightCost + $markupAmount;
                        
                        $flightBreakdown = [
                            'flight_id' => $flight->id,
                            'flight_number' => $flight->flight_number,
                            'airline' => $flight->airline,
                            'seats' => $seats,
                            'class' => $flightClass,
                            'base_cost' => $baseFlightCost,
                            'markup_percentage' => $markup,
                            'markup_amount' => $markupAmount,
                            'final_cost' => $finalFlightCost
                        ];
                        
                        $breakdown['flights'][] = $flightBreakdown;
                        $totalPrice += $finalFlightCost;
                    }
                }
            }
            
            // Calculate activity costs
            if ($request->filled('activities')) {
                foreach ($request->activities as $activity) {
                    if (!($activity['is_included'] ?? true) && isset($activity['additional_cost'])) {
                        $activityCost = $activity['additional_cost'];
                        $breakdown['activities'][] = [
                            'name' => $activity['activity_name'],
                            'cost' => $activityCost
                        ];
                        $totalPrice += $activityCost;
                    }
                }
            }
            
            // Calculate platform commission
            $platformCommission = $request->get('platform_commission', 10);
            $commissionAmount = $totalPrice * ($platformCommission / 100);
            
            $breakdown['commissions'] = [
                'platform_commission_percentage' => $platformCommission,
                'platform_commission_amount' => $commissionAmount
            ];
            
            $breakdown['total_commission'] = $commissionAmount;
            $breakdown['final_total'] = $totalPrice;
            
            return response()->json([
                'success' => true,
                'breakdown' => $breakdown,
                'summary' => [
                    'subtotal' => $totalPrice,
                    'commission' => $commissionAmount,
                    'net_revenue' => $totalPrice - $commissionAmount,
                    'total' => $totalPrice
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate pricing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check real-time availability for selected providers
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        try {
            $results = [
                'available' => true,
                'hotels' => [],
                'transport' => [],
                'flights' => [],
                'conflicts' => []
            ];
            
            // Check hotel availability
            if ($request->filled('hotels')) {
                foreach ($request->hotels as $hotelData) {
                    $hotel = Hotel::find($hotelData['hotel_id']);
                    if ($hotel) {
                        $available = $this->checkHotelAvailability(
                            $hotel,
                            $hotelData['check_in'],
                            $hotelData['check_out'],
                            $hotelData['rooms_needed'] ?? 1
                        );
                        
                        $results['hotels'][] = [
                            'hotel_id' => $hotel->id,
                            'hotel_name' => $hotel->name,
                            'available' => $available['available'],
                            'available_rooms' => $available['available_rooms'],
                            'message' => $available['message']
                        ];
                        
                        if (!$available['available']) {
                            $results['available'] = false;
                            $results['conflicts'][] = "Hotel '{$hotel->name}' is not available for selected dates";
                        }
                    }
                }
            }
            
            // Check transport availability
            if ($request->filled('transport')) {
                foreach ($request->transport as $transportData) {
                    $transport = TransportService::find($transportData['transport_id']);
                    if ($transport) {
                        $available = $this->checkTransportAvailability(
                            $transport,
                            $transportData['date'],
                            $transportData['passengers'] ?? 1
                        );
                        
                        $results['transport'][] = [
                            'transport_id' => $transport->id,
                            'service_name' => $transport->service_name,
                            'available' => $available['available'],
                            'message' => $available['message']
                        ];
                        
                        if (!$available['available']) {
                            $results['available'] = false;
                            $results['conflicts'][] = "Transport '{$transport->service_name}' is not available";
                        }
                    }
                }
            }
            
            // Check flight availability
            if ($request->filled('flights')) {
                foreach ($request->flights as $flightData) {
                    $flight = Flight::find($flightData['flight_id']);
                    if ($flight) {
                        $available = $flight->hasAvailableSeats($flightData['seats_needed'] ?? 1);
                        
                        $results['flights'][] = [
                            'flight_id' => $flight->id,
                            'flight_number' => $flight->flight_number,
                            'available' => $available,
                            'available_seats' => $flight->available_seats,
                            'message' => $available ? 'Available' : 'Not enough seats available'
                        ];
                        
                        if (!$available) {
                            $results['available'] = false;
                            $results['conflicts'][] = "Flight {$flight->flight_number} doesn't have enough seats";
                        }
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => $results
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check availability: ' . $e->getMessage()
            ], 500);
        }
    }

    // Private helper methods

    private function filterHotelsByAvailability($query, $checkIn, $checkOut, $rooms, $guests)
    {
        // This is a simplified availability check
        // In a real system, you'd check against actual bookings
        return $query->whereHas('rooms', function($roomQuery) use ($rooms, $guests) {
            $roomQuery->where('capacity', '>=', $guests)
                     ->where('available_rooms', '>=', $rooms);
        });
    }

    private function checkHotelAvailability($hotel, $checkIn, $checkOut, $roomsNeeded)
    {
        // Simplified availability check
        $availableRooms = $hotel->rooms->where('available_rooms', '>=', $roomsNeeded)->count();
        
        return [
            'available' => $availableRooms > 0,
            'available_rooms' => $availableRooms,
            'message' => $availableRooms > 0 ? 'Available' : 'No rooms available for selected dates'
        ];
    }

    private function checkTransportAvailability($transport, $date, $passengers)
    {
        // Simplified availability check
        $available = $transport->canAccommodate($passengers);
        
        return [
            'available' => $available,
            'message' => $available ? 'Available' : 'Cannot accommodate required passengers'
        ];
    }
    
    /**
     * Format hotel data for API response
     */
    private function formatHotelForAPI($hotel)
    {
        $rooms = $hotel->rooms->map(function ($room) {
            return [
                'id' => $room->id,
                'name' => $room->name,
                'category' => $room->category,
                'base_price' => (float) $room->base_price,
                'effective_price' => (float) $room->getEffectivePrice(),
                'max_occupancy' => $room->max_occupancy,
                'amenities' => $room->amenities ?? [],
                'is_available' => $room->is_available
            ];
        });
        
        // Service requests will be handled separately for now
        $serviceRequest = null;
        
        return [
            'id' => $hotel->id,
            'name' => $hotel->name,
            'hotel_name' => $hotel->name,
            'type' => 'hotel',
            'provider_type' => 'platform',
            'status' => $hotel->status,
            'star_rating' => $hotel->star_rating,
            'address' => $hotel->full_address,
            'city' => $hotel->city,
            'country' => $hotel->country,
            'location' => $hotel->city . ($hotel->country ? ', ' . $hotel->country : ''),
            'phone' => $hotel->phone,
            'email' => $hotel->email,
            'website' => $hotel->website,
            'distance_to_haram' => $hotel->distance_to_haram,
            'main_image' => $hotel->main_image,
            'images' => $hotel->images ?? [],
            'amenities' => $hotel->amenities ?? [],
            'rooms' => $rooms,
            'estimated_price' => $rooms->min('effective_price'),
            'currency' => 'USD',
            'provider' => [
                'id' => $hotel->provider->id ?? null,
                'name' => $hotel->provider->name ?? 'Platform Provider',
                'company_name' => $hotel->provider->company_name ?? null
            ],
            'service_request' => $serviceRequest ? [
                'id' => $serviceRequest->id,
                'status' => $serviceRequest->status
            ] : null,
            'created_at' => $hotel->created_at
        ];
    }
    
    /**
     * Format flight data for API response
     */
    private function formatFlightForAPI($flight)
    {
        $serviceRequest = null; // Service requests will be handled separately
        
        return [
            'id' => $flight->id,
            'name' => $flight->airline . ' ' . $flight->flight_number,
            'airline' => $flight->airline,
            'flight_number' => $flight->flight_number,
            'type' => 'flight',
            'provider_type' => 'platform',
            'status' => $flight->status,
            'departure_airport' => $flight->departure_airport,
            'arrival_airport' => $flight->arrival_airport,
            'departure_datetime' => $flight->departure_datetime,
            'arrival_datetime' => $flight->arrival_datetime,
            'duration' => $flight->duration,
            'aircraft_type' => $flight->aircraft_type,
            'total_seats' => $flight->total_seats,
            'available_seats' => $flight->available_seats,
            'economy_price' => (float) $flight->economy_price,
            'business_price' => (float) $flight->business_price,
            'first_class_price' => (float) $flight->first_class_price,
            'currency' => $flight->currency ?? 'USD',
            'provider' => [
                'id' => $flight->provider->id ?? null,
                'name' => $flight->provider->name ?? 'Platform Provider',
                'company_name' => $flight->provider->company_name ?? null
            ],
            'service_request' => $serviceRequest ? [
                'id' => $serviceRequest->id,
                'status' => $serviceRequest->status
            ] : null,
            'created_at' => $flight->created_at
        ];
    }
    
    /**
     * Format transport service data for API response
     */
    private function formatTransportForAPI($transport)
    {
        $serviceRequest = null; // Service requests will be handled separately
        
        return [
            'id' => $transport->id,
            'name' => $transport->service_name,
            'service_name' => $transport->service_name,
            'company' => $transport->company_name,
            'type' => 'transport',
            'provider_type' => 'platform',
            'status' => $transport->status,
            'transport_type' => $transport->transport_type,
            'vehicle_type' => $transport->vehicle_type,
            'max_passengers' => $transport->max_passengers,
            'vehicle_capacity' => $transport->vehicle_capacity,
            'price' => $transport->price,
            'currency' => $transport->currency ?? 'USD',
            'phone' => $transport->phone,
            'email' => $transport->email,
            'website' => $transport->website,
            'location' => $transport->service_area,
            'features' => $transport->features ?? [],
            'provider' => [
                'id' => $transport->provider->id ?? null,
                'name' => $transport->provider->name ?? 'Platform Provider',
                'company_name' => $transport->provider->company_name ?? null
            ],
            'service_request' => $serviceRequest ? [
                'id' => $serviceRequest->id,
                'status' => $serviceRequest->status
            ] : null,
            'created_at' => $transport->created_at
        ];
    }
}
