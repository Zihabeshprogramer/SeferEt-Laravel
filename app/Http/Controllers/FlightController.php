<?php

namespace App\Http\Controllers;

use App\Models\FlightBooking;
use App\Models\Offer;
use App\Services\AmadeusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FlightController extends Controller
{
    protected AmadeusService $amadeusService;

    public function __construct(AmadeusService $amadeusService)
    {
        $this->amadeusService = $amadeusService;
    }

    /**
     * Search airports by keyword (autocomplete)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function airports(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'keyword' => 'required|string|min:2',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $keyword = $request->input('keyword');
            $limit = $request->input('limit', 10);

            $result = $this->amadeusService->searchAirports($keyword, $limit);

            return response()->json([
                'success' => true,
                'data' => $result['data'] ?? [],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Airport search failed', [
                'error' => $e->getMessage(),
                'keyword' => $request->input('keyword'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to search airports',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search for flight offers
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'origin' => 'required|string|size:3',
            'destination' => 'required|string|size:3',
            'departure_date' => 'required|date|after_or_equal:today',
            'return_date' => 'nullable|date|after:departure_date',
            'adults' => 'nullable|integer|min:1|max:9',
            'max' => 'nullable|integer|min:1|max:250',
            'non_stop' => 'nullable|boolean',
            'currency_code' => 'nullable|string|size:3',
            'travel_class' => 'nullable|string|in:ECONOMY,PREMIUM_ECONOMY,BUSINESS,FIRST',
            'sort_by' => 'nullable|string|in:price,duration',
            'sort_order' => 'nullable|string|in:asc,desc',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $params = [
                'origin' => strtoupper($request->input('origin')),
                'destination' => strtoupper($request->input('destination')),
                'departure_date' => $request->input('departure_date'),
                'return_date' => $request->input('return_date'),
                'adults' => $request->input('adults', 1),
                'max' => $request->input('max', 50),
                'non_stop' => $request->input('non_stop'),
                'currency_code' => $request->input('currency_code', 'USD'),
                'travel_class' => $request->input('travel_class'),
            ];

            $result = $this->amadeusService->searchFlights($params);

            if (empty($result['data'])) {
                return response()->json([
                    'success' => true,
                    'message' => 'No flights found for the given criteria',
                    'data' => [],
                    'meta' => [],
                    'dictionaries' => [],
                ], 200);
            }

            // Apply client-side sorting if requested
            $data = $result['data'];
            $sortBy = $request->input('sort_by');
            $sortOrder = $request->input('sort_order', 'asc');

            if ($sortBy === 'price') {
                usort($data, function ($a, $b) use ($sortOrder) {
                    $priceA = floatval($a['price']['total'] ?? 0);
                    $priceB = floatval($b['price']['total'] ?? 0);
                    return $sortOrder === 'asc' ? $priceA <=> $priceB : $priceB <=> $priceA;
                });
            } elseif ($sortBy === 'duration') {
                usort($data, function ($a, $b) use ($sortOrder) {
                    $durationA = $this->calculateTotalDuration($a);
                    $durationB = $this->calculateTotalDuration($b);
                    return $sortOrder === 'asc' ? $durationA <=> $durationB : $durationB <=> $durationA;
                });
            }

            return response()->json([
                'success' => true,
                'message' => 'Flight offers retrieved successfully',
                'data' => $data,
                'meta' => $result['meta'] ?? [],
                'dictionaries' => $result['dictionaries'] ?? [],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Flight search failed', [
                'error' => $e->getMessage(),
                'params' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to search flights',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Batch search for flight offers across multiple dates
     * Returns cheapest flight info for each date
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function searchBatch(Request $request): JsonResponse
    {
        // Temporarily extend execution time limit to 5 minutes
        set_time_limit(300);
        
        $validator = Validator::make($request->all(), [
            'origin' => 'required|string|size:3',
            'destination' => 'required|string|size:3',
            'departure_dates' => 'required|string', // Comma-separated dates
            'return_date' => 'nullable|date',
            'adults' => 'nullable|integer|min:1|max:9',
            'max' => 'nullable|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $dates = explode(',', $request->input('departure_dates'));
            $dates = array_map('trim', $dates); // Remove whitespace
            $results = [];

            // Base parameters
            $baseParams = [
                'origin' => strtoupper($request->input('origin')),
                'destination' => strtoupper($request->input('destination')),
                'return_date' => $request->input('return_date'),
                'adults' => $request->input('adults', 1),
                'max' => $request->input('max', 10), // Limit results per date
                'currency_code' => 'USD',
            ];

            // Search each date
            foreach ($dates as $date) {
                try {
                    // Validate date format
                    if (!strtotime($date)) {
                        $results[$date] = [
                            'success' => false,
                            'message' => 'Invalid date format',
                            'data' => null,
                        ];
                        continue;
                    }

                    $params = array_merge($baseParams, ['departure_date' => $date]);
                    $result = $this->amadeusService->searchFlights($params);

                    if (empty($result['data'])) {
                        $results[$date] = [
                            'success' => true,
                            'message' => 'No flights found',
                            'data' => [],
                        ];
                    } else {
                        $results[$date] = [
                            'success' => true,
                            'data' => $result['data'],
                            'dictionaries' => $result['dictionaries'] ?? [],
                        ];
                    }
                } catch (\Exception $e) {
                    Log::error("Batch search failed for date {$date}", [
                        'error' => $e->getMessage(),
                    ]);
                    
                    $results[$date] = [
                        'success' => false,
                        'message' => 'Search failed for this date',
                        'data' => null,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Batch flight search completed',
                'data' => $results,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Batch flight search failed', [
                'error' => $e->getMessage(),
                'params' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to perform batch search',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create flight booking from offer
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function book(Request $request): JsonResponse
    {
        // Increase execution time for booking operations
        set_time_limit(120); // 2 minutes
        
        $validator = Validator::make($request->all(), [
            'offer' => 'required|array',
            'offer.id' => 'required|string',
            'travelers' => 'required|array|min:1',
            'travelers.*.id' => 'required|string',
            'travelers.*.dateOfBirth' => 'required|date',
            'travelers.*.name' => 'required|array',
            'travelers.*.name.firstName' => 'required|string',
            'travelers.*.name.lastName' => 'required|string',
            'travelers.*.gender' => 'required|in:MALE,FEMALE',
            'travelers.*.contact' => 'required|array',
            'travelers.*.contact.emailAddress' => 'required|email',
            'travelers.*.contact.phones' => 'required|array|min:1',
            'customer_id' => 'nullable|exists:users,id', // Made nullable for guest bookings
            'agent_id' => 'nullable|exists:users,id',
            'guest_email' => 'required_without:customer_id|email', // Required if no customer_id
            'guest_name' => 'required_without:customer_id|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $offerData = $request->input('offer');
            $travelers = $request->input('travelers');

            // Validate pricing before booking
            $pricingResult = $this->amadeusService->validatePricing($offerData);
            
            if (!$pricingResult) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Offer pricing validation failed. The offer may no longer be available.',
                ], 400);
            }

            // Create booking with Amadeus
            $bookingResult = $this->amadeusService->createBooking($offerData, $travelers);

            if (!$bookingResult || !isset($bookingResult['data'])) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create booking with Amadeus',
                ], 400);
            }

            $amadeusBooking = $bookingResult['data'];
            
            // Get or create offer in database
            $offer = $this->amadeusService->upsertOffer($offerData);

            // Extract PNR from Amadeus response
            $pnr = $amadeusBooking['associatedRecords'][0]['reference'] ?? null;

            // Extract pricing info
            $price = $offerData['price'];
            $totalAmount = $price['total'] ?? 0;
            $currency = $price['currency'] ?? 'USD';

            // Get primary traveler info
            $primaryTraveler = $travelers[0];
            $passengerName = $primaryTraveler['name']['firstName'] . ' ' . $primaryTraveler['name']['lastName'];
            $passengerEmail = $primaryTraveler['contact']['emailAddress'];
            $passengerPhone = $primaryTraveler['contact']['phones'][0]['number'] ?? null;

            // Create flight booking record
            $flightBooking = FlightBooking::create([
                'offer_id' => $offer->id,
                'flight_id' => null, // Amadeus bookings don't have a local flight_id
                'customer_id' => $request->input('customer_id'),
                'agent_id' => $request->input('agent_id'),
                'booking_reference' => $amadeusBooking['id'] ?? uniqid('AMF'),
                'confirmation_code' => $pnr,
                'pnr' => $pnr,
                'passengers' => count($travelers),
                'flight_class' => $offerData['travelerPricings'][0]['fareDetailsBySegment'][0]['cabin'] ?? 'ECONOMY',
                'seat_price' => $totalAmount / count($travelers),
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'tax_amount' => $price['taxes'][0]['amount'] ?? 0,
                'service_fee' => 0,
                'discount_amount' => 0,
                'currency' => $currency,
                'status' => FlightBooking::STATUS_CONFIRMED,
                'payment_status' => FlightBooking::PAYMENT_PENDING,
                'payment_method' => null,
                'passenger_name' => $passengerName,
                'passenger_email' => $passengerEmail,
                'passenger_phone' => $passengerPhone,
                'passenger_details' => $travelers,
                'special_requests' => $request->input('special_requests'),
                'notes' => 'Booked via Amadeus API',
                'source' => FlightBooking::SOURCE_API,
                'confirmed_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Flight booking created successfully',
                'data' => [
                    'booking' => $flightBooking,
                    'offer' => $offer,
                    'pnr' => $pnr,
                    'amadeus_booking' => $amadeusBooking,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Flight booking failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create booking',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get offer details by hash
     * 
     * @param string $hash
     * @return JsonResponse
     */
    public function getOffer(string $hash): JsonResponse
    {
        try {
            $offer = $this->amadeusService->getOfferByHash($hash);

            if (!$offer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Offer not found or expired',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $offer,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get offer failed', [
                'error' => $e->getMessage(),
                'hash' => $hash,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve offer',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Revalidate offer pricing
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function revalidate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'offer' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $offerData = $request->input('offer');
            $result = $this->amadeusService->revalidateOffer($offerData);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Offer validation failed. The offer may no longer be available.',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Offer validated successfully',
                'data' => $result,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Offer revalidation failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to revalidate offer',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get authenticated user's bookings
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserBookings(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $bookings = FlightBooking::where('customer_id', $user->id)
                ->with(['offer'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $bookings,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get user bookings failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve bookings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get booking by PNR
     * 
     * @param string $pnr
     * @return JsonResponse
     */
    public function getByPnr(string $pnr): JsonResponse
    {
        try {
            $booking = FlightBooking::where('pnr', $pnr)
                ->with(['offer', 'customer', 'agent'])
                ->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $booking,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve booking',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate total duration for an offer
     */
    private function calculateTotalDuration(array $offer): int
    {
        $totalMinutes = 0;
        foreach ($offer['itineraries'] ?? [] as $itinerary) {
            $duration = $itinerary['duration'] ?? 'PT0M';
            preg_match_all('/(\d+)([HM])/', $duration, $matches);
            for ($i = 0; $i < count($matches[1]); $i++) {
                $value = intval($matches[1][$i]);
                $unit = $matches[2][$i];
                $totalMinutes += ($unit === 'H') ? $value * 60 : $value;
            }
        }
        return $totalMinutes;
    }
}
