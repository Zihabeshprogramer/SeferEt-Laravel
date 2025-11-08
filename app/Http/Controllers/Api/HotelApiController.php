<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\CityCodeMapper;
use App\Models\Hotel;
use App\Models\HotelBooking;
use App\Models\Room;
use App\Services\AmadeusHotelService;
use App\Services\RoomAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class HotelApiController extends Controller
{
    protected AmadeusHotelService $amadeusHotelService;
    protected RoomAvailabilityService $availabilityService;

    public function __construct(
        AmadeusHotelService $amadeusHotelService,
        RoomAvailabilityService $availabilityService
    ) {
        $this->amadeusHotelService = $amadeusHotelService;
        $this->availabilityService = $availabilityService;
    }

    /**
     * Unified hotel search - combines local and Amadeus results
     * 
     * GET /api/hotels/search
     */
    public function search(Request $request)
    {
        try {
            // Accept both Flutter params (city_code, adults) and legacy params (location, guests)
            $validator = Validator::make($request->all(), [
                'location' => 'required_without:city_code|string',
                'city_code' => 'required_without:location|string',
                'check_in' => 'required|date|after_or_equal:today',
                'check_out' => 'required|date|after:check_in',
                'guests' => 'nullable|integer|min:1|max:10',
                'adults' => 'nullable|integer|min:1|max:10',
                'rooms' => 'nullable|integer|min:1|max:10',
                'star_rating' => 'nullable|integer|min:1|max:5',
                'max_price' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Accept both parameter names
            $location = $request->input('location') ?? $request->input('city_code');
            
            // Convert city name to IATA code for Amadeus
            $iataCode = CityCodeMapper::cityToIata($location);
            
            $checkIn = $request->input('check_in');
            $checkOut = $request->input('check_out');
            $guests = $request->input('guests') ?? $request->input('adults', 1);
            $rooms = $request->input('rooms', 1);
            $starRating = $request->input('star_rating');
            $maxPrice = $request->input('max_price');
            $skipAmadeus = $request->boolean('skip_amadeus', false); // Allow skipping Amadeus for faster results

            // Step 1: Search local hotels
            $localHotels = $this->searchLocalHotels(
                $location,
                $checkIn,
                $checkOut,
                $guests,
                $starRating,
                $maxPrice
            );

            // Step 2: Search Amadeus hotels (with timeout protection)
            $amadeusHotels = [];
            $amadeusError = false;
            
            // Only search Amadeus if not explicitly skipped
            if (!$skipAmadeus) {
            try {
                // Set max execution time for Amadeus search only
                set_time_limit(120); // Allow 120 seconds (2 minutes) for Amadeus
                
                $amadeusResults = $this->amadeusHotelService->searchHotels(
                    $iataCode,  // Use IATA code for Amadeus
                    $checkIn,
                    $checkOut,
                    $guests,
                    $rooms
                );

                if (!empty($amadeusResults['data'])) {
                    foreach ($amadeusResults['data'] as $hotelData) {
                        $transformed = $this->amadeusHotelService->transformToUnifiedFormat($hotelData);
                        
                        // Apply filters
                        if ($starRating && $transformed['star_rating'] != $starRating) {
                            continue;
                        }
                        
                        if ($maxPrice && $transformed['price']['amount'] > $maxPrice) {
                            continue;
                        }
                        
                        $amadeusHotels[] = $transformed;
                    }
                }
            } catch (\Exception $e) {
                $amadeusError = true;
                Log::channel('daily')->error('Amadeus search failed in unified endpoint', [
                    'error' => $e->getMessage(),
                    'location' => $location,
                ]);
                    // Continue with local results only - don't fail the entire search
                }
            } else {
                Log::channel('daily')->info('Skipping Amadeus search as requested', [
                    'location' => $location,
                ]);
            }

            // Step 3: Merge results - local hotels first, then Amadeus
            $allHotels = array_merge($localHotels, $amadeusHotels);

            return response()->json([
                'success' => true,
                'data' => [
                    'hotels' => $allHotels,
                    'total' => count($allHotels),
                    'local_count' => count($localHotels),
                    'amadeus_count' => count($amadeusHotels),
                ],
                'search_params' => [
                    'location' => $location,
                    'location_code' => $iataCode,
                    'location_display' => CityCodeMapper::iataToFriendlyName($iataCode),
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'guests' => $guests,
                    'rooms' => $rooms,
                ],
                'meta' => [
                    'is_mecca' => CityCodeMapper::isMecca($location),
                    'code_explanation' => CityCodeMapper::isMecca($location) 
                        ? CityCodeMapper::getMeccaExplanation() 
                        : null,
                ]
            ]);
        } catch (\Exception $e) {
            Log::channel('daily')->error('Hotel search failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to search hotels. Please try again.'
            ], 500);
        }
    }

    /**
     * Get hotel details by ID
     * 
     * GET /api/hotels/{id}
     */
    public function show(Request $request, string $id)
    {
        try {
            // Check if it's an Amadeus hotel (prefix: amadeus_)
            if (str_starts_with($id, 'amadeus_')) {
                $amadeusId = str_replace('amadeus_', '', $id);
                
                $hotelData = $this->amadeusHotelService->getHotelDetails($amadeusId);
                
                if (!$hotelData) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Hotel not found'
                    ], 404);
                }

                $hotel = $this->amadeusHotelService->transformToUnifiedFormat($hotelData);

                return response()->json([
                    'success' => true,
                    'data' => $hotel
                ]);
            }

            // Local hotel
            $hotel = Hotel::with(['rooms', 'reviews', 'provider'])
                ->where('id', $id)
                ->where('status', 'active')
                ->where('is_active', true)
                ->first();

            if (!$hotel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hotel not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->transformLocalHotelToUnified($hotel)
            ]);
        } catch (\Exception $e) {
            Log::channel('daily')->error('Failed to fetch hotel details', [
                'hotel_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch hotel details'
            ], 500);
        }
    }

    /**
     * Book a hotel
     * 
     * POST /api/hotels/book
     */
    public function book(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'hotel_id' => 'required|string',
                'source' => 'required|in:local,amadeus',
                'check_in' => 'required|date|after_or_equal:today',
                'check_out' => 'required|date|after:check_in',
                'adults' => 'required|integer|min:1',
                'children' => 'nullable|integer|min:0',
                'guest_name' => 'required|string|max:255',
                'guest_email' => 'required|email',
                'guest_phone' => 'required|string|max:20',
                'special_requests' => 'nullable|string',
                'room_id' => 'required_if:source,local',
                'offer_id' => 'required_if:source,amadeus',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $source = $request->input('source');

            DB::beginTransaction();

            try {
                if ($source === 'amadeus') {
                    $booking = $this->bookAmadeusHotel($request);
                } else {
                    $booking = $this->bookLocalHotel($request);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Booking created successfully',
                    'data' => $booking
                ], 201);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::channel('daily')->error('Hotel booking failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Booking failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search local hotels
     */
    private function searchLocalHotels(
        string $location,
        string $checkIn,
        string $checkOut,
        int $guests,
        ?int $starRating = null,
        ?float $maxPrice = null
    ): array {
        // Map IATA codes to city names for local search
        $cityMap = [
            'MKK' => 'Makkah',
            'MED' => 'Madinah',
            'JED' => 'Jeddah',
            'RUH' => 'Riyadh',
            'DXB' => 'Dubai',
            'AUH' => 'Abu Dhabi',
        ];
        
        // Convert code to city name if it's a known code
        $searchLocation = $cityMap[$location] ?? $location;
        
        $query = Hotel::query()
            ->with(['rooms'])
            ->where('status', 'active')
            ->where('is_active', true)
            ->where(function ($q) use ($searchLocation, $location) {
                $q->where('city', 'like', "%{$searchLocation}%")
                    ->orWhere('city', 'like', "%{$location}%") // Also search by code
                    ->orWhere('country', 'like', "%{$searchLocation}%")
                    ->orWhere('address', 'like', "%{$searchLocation}%");
            });

        if ($starRating) {
            $query->where('star_rating', $starRating);
        }

        $hotels = $query->get();

        $results = [];
        foreach ($hotels as $hotel) {
            // Check availability
            $availableRooms = $hotel->rooms()
                ->where('is_available', true)
                ->where('max_occupancy', '>=', $guests)
                ->get();

            if ($availableRooms->isEmpty()) {
                continue;
            }

            // Get minimum price
            $minPrice = $availableRooms->min('base_price');

            if ($maxPrice && $minPrice > $maxPrice) {
                continue;
            }

            $hotelData = $this->transformLocalHotelToUnified($hotel);
            $hotelData['price'] = [
                'amount' => $minPrice,
                'currency' => 'USD',
            ];
            $hotelData['available_rooms'] = $availableRooms->count();

            $results[] = $hotelData;
        }

        return $results;
    }

    /**
     * Book local hotel
     */
    private function bookLocalHotel(Request $request): array
    {
        $hotelId = $request->input('hotel_id');
        $roomId = $request->input('room_id');

        $hotel = Hotel::findOrFail($hotelId);
        $room = Room::where('hotel_id', $hotelId)
            ->where('id', $roomId)
            ->where('is_available', true)
            ->firstOrFail();

        $checkIn = Carbon::parse($request->input('check_in'));
        $checkOut = Carbon::parse($request->input('check_out'));
        $nights = $checkIn->diffInDays($checkOut);

        $totalAmount = $room->base_price * $nights;

        $booking = HotelBooking::create([
            'hotel_id' => $hotel->id,
            'room_id' => $room->id,
            'customer_id' => Auth::id(),
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'nights' => $nights,
            'adults' => $request->input('adults'),
            'children' => $request->input('children', 0),
            'room_rate' => $room->base_price,
            'total_amount' => $totalAmount,
            'currency' => 'USD',
            'status' => 'pending',
            'payment_status' => 'pending',
            'guest_name' => $request->input('guest_name'),
            'guest_email' => $request->input('guest_email'),
            'guest_phone' => $request->input('guest_phone'),
            'special_requests' => $request->input('special_requests'),
            'source' => 'local',
        ]);

        Log::channel('daily')->info('Local hotel booking created', [
            'booking_id' => $booking->id,
            'hotel_id' => $hotel->id,
            'room_id' => $room->id,
        ]);

        return [
            'booking_id' => $booking->id,
            'booking_reference' => $booking->booking_reference,
            'hotel_name' => $hotel->name,
            'check_in' => $booking->check_in_date->format('Y-m-d'),
            'check_out' => $booking->check_out_date->format('Y-m-d'),
            'total_amount' => $booking->total_amount,
            'currency' => $booking->currency,
            'status' => $booking->status,
            'source' => 'local',
        ];
    }

    /**
     * Book Amadeus hotel
     */
    private function bookAmadeusHotel(Request $request): array
    {
        $offerId = $request->input('offer_id');

        $travelerInfo = [
            'name' => [
                'firstName' => explode(' ', $request->input('guest_name'))[0],
                'lastName' => explode(' ', $request->input('guest_name'))[1] ?? '',
            ],
            'contact' => [
                'email' => $request->input('guest_email'),
                'phone' => $request->input('guest_phone'),
            ],
        ];

        $amadeusBooking = $this->amadeusHotelService->bookHotel($offerId, $travelerInfo);

        if (!$amadeusBooking) {
            throw new \Exception('Amadeus booking failed');
        }

        $checkIn = Carbon::parse($request->input('check_in'));
        $checkOut = Carbon::parse($request->input('check_out'));
        $nights = $checkIn->diffInDays($checkOut);

        // Store booking in local database
        $booking = HotelBooking::create([
            'hotel_id' => null, // No local hotel
            'room_id' => null,
            'customer_id' => Auth::id(),
            'booking_reference' => $amadeusBooking['data']['id'] ?? 'AMD-' . strtoupper(uniqid()),
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'nights' => $nights,
            'adults' => $request->input('adults'),
            'children' => $request->input('children', 0),
            'total_amount' => $amadeusBooking['data']['price']['total'] ?? 0,
            'currency' => $amadeusBooking['data']['price']['currency'] ?? 'USD',
            'status' => 'confirmed',
            'payment_status' => 'pending',
            'guest_name' => $request->input('guest_name'),
            'guest_email' => $request->input('guest_email'),
            'guest_phone' => $request->input('guest_phone'),
            'special_requests' => $request->input('special_requests'),
            'source' => 'amadeus',
            'notes' => json_encode($amadeusBooking),
        ]);

        Log::channel('daily')->info('Amadeus hotel booking created', [
            'booking_id' => $booking->id,
            'amadeus_booking_id' => $amadeusBooking['data']['id'] ?? null,
        ]);

        return [
            'booking_id' => $booking->id,
            'booking_reference' => $booking->booking_reference,
            'amadeus_booking_id' => $amadeusBooking['data']['id'] ?? null,
            'check_in' => $booking->check_in_date->format('Y-m-d'),
            'check_out' => $booking->check_out_date->format('Y-m-d'),
            'total_amount' => $booking->total_amount,
            'currency' => $booking->currency,
            'status' => $booking->status,
            'source' => 'amadeus',
        ];
    }

    /**
     * Transform local hotel to unified format
     */
    private function transformLocalHotelToUnified(Hotel $hotel): array
    {
        return [
            'id' => (string) $hotel->id,
            'source' => 'local',
            'name' => $hotel->name,
            'description' => $hotel->description,
            'type' => $hotel->type,
            'star_rating' => $hotel->star_rating,
            'address' => $hotel->address,
            'city' => $hotel->city,
            'country' => $hotel->country,
            'postal_code' => $hotel->postal_code,
            'phone' => $hotel->phone,
            'email' => $hotel->email,
            'website' => $hotel->website,
            'latitude' => $hotel->latitude,
            'longitude' => $hotel->longitude,
            'amenities' => $hotel->amenities ?? [],
            'images' => $hotel->images ?? [],
            'check_in_time' => $hotel->check_in_time ? $hotel->check_in_time->format('H:i') : null,
            'check_out_time' => $hotel->check_out_time ? $hotel->check_out_time->format('H:i') : null,
            'average_rating' => $hotel->average_rating ?? 0,
            'total_reviews' => $hotel->total_reviews ?? 0,
            'is_available' => $hotel->isAvailable(),
        ];
    }
}
