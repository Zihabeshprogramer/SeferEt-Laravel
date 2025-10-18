<?php

namespace App\Http\Controllers\B2B\API;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Flight;
use App\Models\TransportService;
use App\Models\ProviderRequest;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

/**
 * ProviderSearchController
 * 
 * Handles AJAX requests for searching and managing providers in package creation
 */
class ProviderSearchController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:travel_agent']);
    }
    
    /**
     * Search hotels based on dates, location, and other criteria
     */
    public function searchHotels(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'destination' => 'nullable|string|max:255',
                'check_in_date' => 'nullable|date|after_or_equal:today',
                'check_out_date' => 'nullable|date|after:check_in_date',
                'star_rating' => 'nullable|integer|min:1|max:5',
                'hotel_type' => 'nullable|string',
                'max_distance_haram' => 'nullable|numeric|min:0',
                'amenities' => 'nullable|array',
                'price_range_min' => 'nullable|numeric|min:0',
                'price_range_max' => 'nullable|numeric|min:0',
                'rooms_needed' => 'nullable|integer|min:1|max:50',
                'package_dates' => 'nullable|array',
                'source' => 'required|in:platform,external,mixed',
                'limit' => 'nullable|integer|min:1|max:50'
            ]);
            
            $query = Hotel::query()->active()->with(['provider', 'rooms']);
            
            // Filter by destination/city
            if ($request->filled('destination')) {
                $query->inCity($request->destination);
            }
            
            // Filter by star rating
            if ($request->filled('star_rating')) {
                $query->withStarRating($request->star_rating);
            }
            
            // Filter by hotel type
            if ($request->filled('hotel_type')) {
                $query->ofType($request->hotel_type);
            }
            
            // Filter by distance to Haram (for Hajj/Umrah packages)
            if ($request->filled('max_distance_haram')) {
                $query->where('distance_to_haram', '<=', $request->max_distance_haram);
            }
            
            // Filter by amenities
            if ($request->filled('amenities')) {
                foreach ($request->amenities as $amenity) {
                    $query->whereJsonContains('amenities', $amenity);
                }
            }
            
            // Only show hotels that have availability for the given dates
            if ($request->filled(['check_in_date', 'check_out_date'])) {
                $query = $this->filterHotelAvailability($query, $request->check_in_date, $request->check_out_date, $request->rooms_needed ?? 1);
            }
            
            $hotels = $query->limit($request->get('limit', 20))->get();
            
            // Transform hotels for response
            $transformedHotels = $hotels->map(function ($hotel) use ($request) {
                $basePrice = $this->calculateHotelPrice($hotel, $request);
                
                return [
                    'id' => $hotel->id,
                    'name' => $hotel->name,
                    'provider_name' => $hotel->provider->company_name ?? $hotel->provider->name,
                    'provider_id' => $hotel->provider_id,
                    'star_rating' => $hotel->star_rating,
                    'type' => $hotel->type,
                    'address' => $hotel->full_address,
                    'city' => $hotel->city,
                    'country' => $hotel->country,
                    'distance_to_haram' => $hotel->distance_to_haram,
                    'main_image' => $hotel->main_image,
                    'amenities' => $hotel->amenities ?? [],
                    'description' => $hotel->description,
                    'estimated_price' => $basePrice,
                    'currency' => 'USD',
                    'available_rooms' => $hotel->rooms->count(),
                    'requires_approval' => true
                ];
            });
            
            return response()->json([
                'success' => true,
                'hotels' => $transformedHotels,
                'total' => $transformedHotels->count(),
                'search_criteria' => $request->only([
                    'destination', 'check_in_date', 'check_out_date', 
                    'star_rating', 'hotel_type', 'amenities'
                ])
            ]);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid search criteria',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Search flights based on dates, routes, and other criteria
     */
    public function searchFlights(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'departure_airport' => 'nullable|string|max:10',
                'arrival_airport' => 'nullable|string|max:10',
                'departure_date' => 'nullable|date|after_or_equal:today',
                'return_date' => 'nullable|date|after:departure_date',
                'passengers' => 'nullable|integer|min:1|max:100',
                'class' => 'nullable|in:economy,business,first_class',
                'airline' => 'nullable|string|max:255',
                'max_price' => 'nullable|numeric|min:0',
                'direct_only' => 'nullable|boolean',
                'source' => 'required|in:own,platform,external,mixed',
                'limit' => 'nullable|integer|min:1|max:50'
            ]);
            
            $query = Flight::query()->active()->with('provider');
            
            // Filter by departure airport
            if ($request->filled('departure_airport')) {
                $query->where('departure_airport', 'like', '%' . $request->departure_airport . '%');
            }
            
            // Filter by arrival airport  
            if ($request->filled('arrival_airport')) {
                $query->where('arrival_airport', 'like', '%' . $request->arrival_airport . '%');
            }
            
            // Filter by departure date
            if ($request->filled('departure_date')) {
                $startDate = Carbon::parse($request->departure_date)->startOfDay();
                $endDate = Carbon::parse($request->departure_date)->endOfDay();
                $query->whereBetween('departure_datetime', [$startDate, $endDate]);
            }
            
            // Filter by airline
            if ($request->filled('airline')) {
                $query->where('airline', 'like', '%' . $request->airline . '%');
            }
            
            // Filter by available seats
            $passengers = $request->get('passengers', 1);
            $query->where('available_seats', '>=', $passengers);
            
            // Filter by price range
            if ($request->filled('max_price')) {
                $class = $request->get('class', 'economy');
                $priceField = $class . '_price';
                $query->where($priceField, '<=', $request->max_price);
            }
            
            // Filter by source (own flights vs platform flights)
            if ($request->source === 'own') {
                $query->where('provider_id', Auth::id());
            } elseif ($request->source === 'platform') {
                // Exclude flights from the same provider when browsing platform flights
                $query->where('provider_id', '!=', Auth::id());
            }
            
            $flights = $query->limit($request->get('limit', 20))->get();
            
            // Transform flights for response
            $transformedFlights = $flights->map(function ($flight) use ($request) {
                $class = $request->get('class', 'economy');
                $price = $flight->{$class . '_price'};
                
                return [
                    'id' => $flight->id,
                    'airline' => $flight->airline,
                    'flight_number' => $flight->flight_number,
                    'provider_name' => $flight->provider->company_name ?? $flight->provider->name,
                    'provider_id' => $flight->provider_id,
                    'departure_airport' => $flight->departure_airport,
                    'arrival_airport' => $flight->arrival_airport,
                    'departure_datetime' => $flight->departure_datetime->format('Y-m-d H:i'),
                    'arrival_datetime' => $flight->arrival_datetime->format('Y-m-d H:i'),
                    'duration' => $flight->duration,
                    'aircraft_type' => $flight->aircraft_type,
                    'available_seats' => $flight->available_seats,
                    'price' => $price,
                    'currency' => $flight->currency,
                    'amenities' => $flight->amenities ?? [],
                    'baggage_allowance' => $flight->baggage_allowance ?? [],
                    'meal_service' => $flight->meal_service ?? [],
                    'requires_approval' => $flight->provider_id !== Auth::id()
                ];
            });
            
            return response()->json([
                'success' => true,
                'flights' => $transformedFlights,
                'total' => $transformedFlights->count(),
                'search_criteria' => $request->only([
                    'departure_airport', 'arrival_airport', 'departure_date', 
                    'passengers', 'class', 'airline'
                ])
            ]);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid search criteria',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get flights owned by the authenticated user/provider
     */
    public function getOwnFlights(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'limit' => 'nullable|integer|min:1|max:50',
                'status' => 'nullable|in:scheduled,boarding,departed,arrived,cancelled,delayed',
                'active_only' => 'nullable|boolean'
            ]);
            
            $query = Flight::query()
                ->where('provider_id', Auth::id())
                ->with('provider');
            
            // Filter by status if provided
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            // Filter by active status if specified
            if ($request->get('active_only', true)) {
                $query->where('is_active', true);
            }
            
            // Order by departure date
            $query->orderBy('departure_datetime', 'asc');
            
            $flights = $query->limit($request->get('limit', 20))->get();
            
            // Transform flights for response
            $transformedFlights = $flights->map(function ($flight) {
                return [
                    'id' => $flight->id,
                    'airline' => $flight->airline,
                    'flight_number' => $flight->flight_number,
                    'provider_name' => $flight->provider->company_name ?? $flight->provider->name,
                    'provider_id' => $flight->provider_id,
                    'departure_airport' => $flight->departure_airport,
                    'arrival_airport' => $flight->arrival_airport,
                    'departure_datetime' => $flight->departure_datetime->format('Y-m-d H:i'),
                    'arrival_datetime' => $flight->arrival_datetime->format('Y-m-d H:i'),
                    'duration' => $flight->duration,
                    'aircraft_type' => $flight->aircraft_type,
                    'total_seats' => $flight->total_seats,
                    'available_seats' => $flight->available_seats,
                    'economy_price' => $flight->economy_price,
                    'business_price' => $flight->business_price,
                    'first_class_price' => $flight->first_class_price,
                    'currency' => $flight->currency,
                    'status' => $flight->status,
                    'is_active' => $flight->is_active,
                    'amenities' => $flight->amenities ?? [],
                    'baggage_allowance' => $flight->baggage_allowance ?? [],
                    'meal_service' => $flight->meal_service ?? [],
                    'trip_type' => $flight->trip_type,
                    'return_flight_number' => $flight->return_flight_number,
                    'return_departure_datetime' => $flight->return_departure_datetime?->format('Y-m-d H:i'),
                    'return_arrival_datetime' => $flight->return_arrival_datetime?->format('Y-m-d H:i'),
                    'requires_approval' => false // Own flights don't require approval
                ];
            });
            
            return response()->json([
                'success' => true,
                'flights' => $transformedFlights,
                'total' => $transformedFlights->count(),
                'message' => 'Your flights retrieved successfully'
            ]);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request parameters',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve flights: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Search transport services
     */
    public function searchTransport(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'transport_type' => 'nullable|in:bus,car,van,taxi,shuttle',
                'route_type' => 'nullable|in:airport_transfer,city_transport,intercity,pilgrimage_sites',
                'pickup_location' => 'nullable|string|max:255',
                'dropoff_location' => 'nullable|string|max:255',
                'service_date' => 'nullable|date|after_or_equal:today',
                'passengers' => 'nullable|integer|min:1|max:100',
                'max_price' => 'nullable|numeric|min:0',
                'source' => 'required|in:platform,external,mixed',
                'limit' => 'nullable|integer|min:1|max:50'
            ]);
            
            $query = TransportService::query()->active()->with('provider');
            
            // Filter by transport type
            if ($request->filled('transport_type')) {
                $query->ofType($request->transport_type);
            }
            
            // Filter by route type
            if ($request->filled('route_type')) {
                $query->forRoute($request->route_type);
            }
            
            // Filter by passenger capacity
            if ($request->filled('passengers')) {
                $query->where('max_passengers', '>=', $request->passengers);
            }
            
            // Filter by route (if pickup and dropoff specified)
            if ($request->filled(['pickup_location', 'dropoff_location'])) {
                $query->where(function ($q) use ($request) {
                    $q->whereJsonContains('routes', [
                        'from' => $request->pickup_location,
                        'to' => $request->dropoff_location
                    ]);
                });
            }
            
            // Filter by price
            if ($request->filled('max_price')) {
                $query->where('price', '<=', $request->max_price);
            }
            
            $transportServices = $query->limit($request->get('limit', 20))->get();
            
            // Transform transport services for response
            $transformedServices = $transportServices->map(function ($service) {
                return [
                    'id' => $service->id,
                    'service_name' => $service->service_name,
                    'provider_name' => $service->provider->company_name ?? $service->provider->name,
                    'provider_id' => $service->provider_id,
                    'transport_type' => $service->transport_type,
                    'route_type' => $service->route_type,
                    'max_passengers' => $service->max_passengers,
                    'price' => $service->price,
                    'routes' => $service->routes ?? [],
                    'pickup_locations' => $service->pickup_locations ?? [],
                    'dropoff_locations' => $service->dropoff_locations ?? [],
                    'operating_hours' => $service->operating_hours ?? [],
                    'specifications' => $service->specifications ?? [],
                    'images' => $service->images ?? [],
                    'requires_approval' => true
                ];
            });
            
            return response()->json([
                'success' => true,
                'transport_services' => $transformedServices,
                'total' => $transformedServices->count(),
                'search_criteria' => $request->only([
                    'transport_type', 'route_type', 'pickup_location', 
                    'dropoff_location', 'passengers'
                ])
            ]);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid search criteria',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Request provider approval for a service
     */
    public function requestProviderApproval(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'package_id' => 'required|exists:packages,id',
                'service_type' => 'required|in:hotel,flight,transport',
                'service_id' => 'nullable|integer',
                'provider_id' => 'nullable|exists:users,id',
                'external_provider' => 'nullable|boolean',
                'request_details' => 'required|array',
                'requested_price' => 'nullable|numeric|min:0',
                'service_start_date' => 'required|date|after_or_equal:today',
                'service_end_date' => 'nullable|date|after:service_start_date',
                'travel_agent_notes' => 'nullable|string|max:1000',
                'priority' => 'nullable|in:low,normal,high,urgent',
                'is_rush_request' => 'nullable|boolean'
            ]);
            
            // Handle external providers
            $isExternal = $request->get('external_provider', false);
            $providerId = null;
            
            if (!$isExternal) {
                // For platform providers, we need to find the provider based on service
                if ($request->service_type === 'hotel' && $request->service_id) {
                    $hotel = Hotel::find($request->service_id);
                    $providerId = $hotel?->provider_id;
                } elseif ($request->service_type === 'flight' && $request->service_id) {
                    $flight = Flight::find($request->service_id);
                    $providerId = $flight?->provider_id;
                } elseif ($request->service_type === 'transport' && $request->service_id) {
                    $transport = TransportService::find($request->service_id);
                    $providerId = $transport?->provider_id;
                }
            }
            
            // Check for existing request
            $existingRequestQuery = ProviderRequest::where([
                'package_id' => $request->package_id,
                'service_type' => $request->service_type,
                'travel_agent_id' => Auth::id()
            ])->where('status', ProviderRequest::STATUS_PENDING);
            
            if (!$isExternal && $request->service_id) {
                $existingRequestQuery->where('service_id', $request->service_id);
            }
            
            $existingRequest = $existingRequestQuery->first();
            
            if ($existingRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'A pending request already exists for this service.'
                ], 409);
            }
            
            // Create provider request
            $providerRequest = ProviderRequest::create([
                'package_id' => $request->package_id,
                'travel_agent_id' => Auth::id(),
                'provider_id' => $providerId,
                'service_type' => $request->service_type,
                'service_id' => $request->service_id,
                'request_details' => $request->request_details,
                'requested_price' => $request->requested_price,
                'service_start_date' => $request->service_start_date,
                'service_end_date' => $request->service_end_date,
                'travel_agent_notes' => $request->travel_agent_notes,
                'priority' => $request->get('priority', ProviderRequest::PRIORITY_NORMAL),
                'is_rush_request' => $request->get('is_rush_request', false),
                'response_deadline' => now()->addDays($request->is_rush_request ? 1 : 3),
                'currency' => 'USD',
                'is_external_provider' => $isExternal
            ]);
            
            // TODO: Send notification to provider (if not external)
            
            $message = $isExternal ? 
                'External provider request created. You can track the status manually.' :
                'Provider approval request sent successfully.';
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'request_id' => $providerRequest->id,
                'response_deadline' => $providerRequest->response_deadline->format('Y-m-d H:i'),
                'is_external' => $isExternal
            ]);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Request failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Check approval status for package providers
     */
    public function checkApprovalStatus(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'package_id' => 'required|exists:packages,id'
            ]);
            
            $requests = ProviderRequest::where('package_id', $request->package_id)
                ->where('travel_agent_id', Auth::id())
                ->with(['provider', 'package'])
                ->get();
            
            $statusSummary = [
                'total_requests' => $requests->count(),
                'pending' => $requests->where('status', ProviderRequest::STATUS_PENDING)->count(),
                'approved' => $requests->where('status', ProviderRequest::STATUS_APPROVED)->count(),
                'rejected' => $requests->where('status', ProviderRequest::STATUS_REJECTED)->count(),
                'expired' => $requests->where('status', ProviderRequest::STATUS_EXPIRED)->count(),
                'can_proceed' => true, // Will be calculated below
                'requests' => []
            ];
            
            foreach ($requests as $request_item) {
                $statusSummary['requests'][] = [
                    'id' => $request_item->id,
                    'service_type' => $request_item->service_type,
                    'service_id' => $request_item->service_id,
                    'provider_name' => $request_item->provider->company_name ?? $request_item->provider->name,
                    'status' => $request_item->status,
                    'status_badge_class' => $request_item->status_badge_class,
                    'priority' => $request_item->priority,
                    'priority_badge_class' => $request_item->priority_badge_class,
                    'response_deadline' => $request_item->response_deadline?->format('Y-m-d H:i'),
                    'days_until_deadline' => $request_item->days_until_deadline,
                    'provider_notes' => $request_item->provider_notes,
                    'is_expired' => $request_item->isExpired()
                ];
            }
            
            // Determine if user can proceed to next step
            $statusSummary['can_proceed'] = $statusSummary['pending'] === 0 && $statusSummary['approved'] > 0;
            
            return response()->json([
                'success' => true,
                'status_summary' => $statusSummary
            ]);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Status check failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Helper method to filter hotel availability
     */
    private function filterHotelAvailability($query, $checkIn, $checkOut, $roomsNeeded)
    {
        // This is a simplified availability check
        // In a real system, you'd check against booking records, room availability, etc.
        return $query->whereHas('rooms', function ($roomQuery) use ($roomsNeeded) {
            $roomQuery->where('is_active', true);
            // Additional availability logic would go here
        });
    }
    
    /**
     * Helper method to calculate hotel pricing
     */
    private function calculateHotelPrice($hotel, $request)
    {
        // Simplified pricing calculation
        // In a real system, you'd factor in dates, room types, seasonal pricing, etc.
        $basePrice = 100; // Default base price
        
        // Factor in star rating
        $basePrice *= (1 + ($hotel->star_rating * 0.2));
        
        // Factor in number of nights
        if ($request->filled(['check_in_date', 'check_out_date'])) {
            $nights = Carbon::parse($request->check_in_date)
                           ->diffInDays(Carbon::parse($request->check_out_date));
            $basePrice *= max(1, $nights);
        }
        
        return round($basePrice, 2);
    }
}
