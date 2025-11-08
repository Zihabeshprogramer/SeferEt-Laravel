<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AmadeusHotelService
{
    private string $baseUrl;
    private string $apiKey;
    private string $apiSecret;
    private int $tokenTtl;
    private int $searchTtl;

    public function __construct()
    {
        $env = config('amadeus.environment', 'test');
        $this->baseUrl = config("amadeus.base_urls.{$env}");
        $this->apiKey = config('amadeus.api_key');
        $this->apiSecret = config('amadeus.api_secret');
        $this->tokenTtl = config('amadeus.cache.token_ttl', 1800);
        $this->searchTtl = config('amadeus.cache.search_ttl', 600);
    }

    /**
     * Get OAuth access token (cached)
     */
    private function getAccessToken(): string
    {
        return Cache::remember('amadeus_token', $this->tokenTtl, function () {
            try {
                $response = Http::withOptions(['verify' => false])
                    ->asForm()
                    ->post("{$this->baseUrl}/v1/security/oauth2/token", [
                        'grant_type' => 'client_credentials',
                        'client_id' => $this->apiKey,
                        'client_secret' => $this->apiSecret,
                    ]);

                if ($response->failed()) {
                    Log::channel('daily')->error('Amadeus OAuth failed', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    throw new \Exception('Failed to obtain Amadeus access token');
                }

                $data = $response->json();
                return $data['access_token'];
            } catch (\Exception $e) {
                Log::channel('daily')->error('Amadeus token exception', ['error' => $e->getMessage()]);
                throw $e;
            }
        });
    }

    /**
     * Search hotels by location
     * 
     * @param string $location City code or name
     * @param string $checkIn Check-in date (YYYY-MM-DD)
     * @param string $checkOut Check-out date (YYYY-MM-DD)
     * @param int $guests Number of guests
     * @param int $rooms Number of rooms
     * @return array
     */
    public function searchHotels(
        string $location,
        string $checkIn,
        string $checkOut,
        int $guests = 1,
        int $rooms = 1
    ): array {
        $cacheKey = 'amadeus_hotel_search_' . md5($location . $checkIn . $checkOut . $guests . $rooms);

        return Cache::remember($cacheKey, $this->searchTtl, function () use ($location, $checkIn, $checkOut, $guests, $rooms) {
            try {
                $startTime = microtime(true);
                
                // Step 1: Get hotel IDs by location
                $hotelIds = $this->getHotelIdsByLocation($location);
                
                if (empty($hotelIds)) {
                    Log::channel('daily')->warning('No hotels found for location', ['location' => $location]);
                    return ['data' => []];
                }

                // Step 2: Search for offers
                $token = $this->getAccessToken();
                
                $queryParams = [
                    'hotelIds' => implode(',', array_slice($hotelIds, 0, 100)), // Max 100 hotels
                    'checkInDate' => $checkIn,
                    'checkOutDate' => $checkOut,
                    'adults' => $guests,
                    'roomQuantity' => $rooms,
                ];

                $response = Http::timeout(90) // 90 second timeout (temporary for debugging)
                    ->withOptions(['verify' => false])
                    ->withToken($token)
                    ->get("{$this->baseUrl}/v3/shopping/hotel-offers", $queryParams);

                $duration = microtime(true) - $startTime;

                if ($response->failed()) {
                    Log::channel('daily')->error('Amadeus hotel search failed', [
                        'location' => $location,
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'duration' => $duration,
                    ]);
                    return ['data' => []];
                }

                $result = $response->json();
                
                // Cache hotel offers
                if (!empty($result['data'])) {
                    foreach ($result['data'] as $hotelData) {
                        $this->cacheHotelOffer($hotelData);
                    }
                }

                Log::channel('daily')->info('Amadeus hotel search successful', [
                    'location' => $location,
                    'results_count' => count($result['data'] ?? []),
                    'duration' => $duration,
                ]);

                return $result;
            } catch (\Exception $e) {
                Log::channel('daily')->error('Amadeus hotel search exception', [
                    'location' => $location,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return ['data' => []];
            }
        });
    }

    /**
     * Get hotel IDs by city/location
     */
    private function getHotelIdsByLocation(string $location): array
    {
        try {
            $token = $this->getAccessToken();

            $response = Http::timeout(30) // 30 second timeout (temporary for debugging)
                ->withOptions(['verify' => false])
                ->withToken($token)
                ->get("{$this->baseUrl}/v1/reference-data/locations/hotels/by-city", [
                    'cityCode' => $location,
                ]);

            if ($response->failed()) {
                Log::channel('daily')->error('Failed to get hotel IDs by location', [
                    'location' => $location,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [];
            }

            $data = $response->json();
            return array_column($data['data'] ?? [], 'hotelId');
        } catch (\Exception $e) {
            Log::channel('daily')->error('Exception getting hotel IDs', [
                'location' => $location,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Get hotel details by hotel ID
     * 
     * @param string $hotelId Amadeus hotel ID
     * @return array|null
     */
    public function getHotelDetails(string $hotelId): ?array
    {
        $cacheKey = 'amadeus_hotel_details_' . $hotelId;

        return Cache::remember($cacheKey, 3600, function () use ($hotelId) {
            try {
                $startTime = microtime(true);
                $token = $this->getAccessToken();

                $response = Http::timeout(60) // 60 second timeout (temporary for debugging)
                    ->withOptions(['verify' => false])
                    ->withToken($token)
                    ->get("{$this->baseUrl}/v3/shopping/hotel-offers", [
                        'hotelIds' => $hotelId,
                    ]);

                $duration = microtime(true) - $startTime;

                if ($response->failed()) {
                    Log::channel('daily')->error('Amadeus hotel details failed', [
                        'hotel_id' => $hotelId,
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'duration' => $duration,
                    ]);
                    return null;
                }

                $result = $response->json();

                Log::channel('daily')->info('Amadeus hotel details retrieved', [
                    'hotel_id' => $hotelId,
                    'duration' => $duration,
                ]);

                return $result['data'][0] ?? null;
            } catch (\Exception $e) {
                Log::channel('daily')->error('Amadeus hotel details exception', [
                    'hotel_id' => $hotelId,
                    'error' => $e->getMessage(),
                ]);
                return null;
            }
        });
    }

    /**
     * Book a hotel through Amadeus
     * 
     * @param string $offerId The offer ID from Amadeus
     * @param array $travelerInfo Guest information
     * @return array|null
     */
    public function bookHotel(string $offerId, array $travelerInfo): ?array
    {
        try {
            $startTime = microtime(true);
            $token = $this->getAccessToken();

            // First, get the offer details to ensure it's still valid
            $offerData = $this->getOfferFromCache($offerId);
            
            if (!$offerData) {
                Log::channel('daily')->error('Offer not found in cache', ['offer_id' => $offerId]);
                return null;
            }

            $payload = [
                'data' => [
                    'offerId' => $offerId,
                    'guests' => [$travelerInfo],
                    'payments' => [
                        [
                            'method' => 'creditCard',
                            'card' => $travelerInfo['payment'] ?? [],
                        ],
                    ],
                ],
            ];

            $response = Http::withOptions([
                    'verify' => false,
                    'timeout' => 60,
                ])
                ->withToken($token)
                ->post("{$this->baseUrl}/v2/booking/hotel-bookings", $payload);

            $duration = microtime(true) - $startTime;

            if ($response->failed()) {
                Log::channel('daily')->error('Amadeus hotel booking failed', [
                    'offer_id' => $offerId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'duration' => $duration,
                ]);
                return null;
            }

            $result = $response->json();

            Log::channel('daily')->info('Amadeus hotel booking successful', [
                'offer_id' => $offerId,
                'confirmation' => $result['data']['id'] ?? 'N/A',
                'duration' => $duration,
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::channel('daily')->error('Amadeus hotel booking exception', [
                'offer_id' => $offerId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Cache hotel offer in database
     */
    private function cacheHotelOffer(array $hotelData): void
    {
        try {
            $hotelId = $hotelData['hotel']['hotelId'] ?? null;
            
            if (!$hotelId) {
                return;
            }

            $hotel = $hotelData['hotel'] ?? [];
            $offers = $hotelData['offers'] ?? [];
            
            if (empty($offers)) {
                return;
            }

            $firstOffer = $offers[0];
            $price = $firstOffer['price']['total'] ?? 0;
            $currency = $firstOffer['price']['currency'] ?? 'USD';
            $offerId = $firstOffer['id'] ?? null;

            // Store in cache table
            DB::table('amadeus_hotels_cache')->updateOrInsert(
                ['hotel_id' => $hotelId],
                [
                    'name' => $hotel['name'] ?? 'Unknown Hotel',
                    'city_code' => $hotel['cityCode'] ?? '',
                    'price' => $price,
                    'currency' => $currency,
                    'rating' => $hotel['rating'] ?? null,
                    'offer_id' => $offerId,
                    'offer_data' => json_encode($hotelData),
                    'expires_at' => now()->addMinutes($this->searchTtl),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        } catch (\Exception $e) {
            Log::channel('daily')->error('Failed to cache hotel offer', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get offer from cache by offer ID
     */
    private function getOfferFromCache(string $offerId): ?array
    {
        try {
            $cached = DB::table('amadeus_hotels_cache')
                ->where('offer_id', $offerId)
                ->where('expires_at', '>', now())
                ->first();

            if ($cached && $cached->offer_data) {
                return json_decode($cached->offer_data, true);
            }

            return null;
        } catch (\Exception $e) {
            Log::channel('daily')->error('Failed to retrieve offer from cache', [
                'offer_id' => $offerId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Transform Amadeus hotel data to unified format
     */
    public function transformToUnifiedFormat(array $hotelData): array
    {
        $hotel = $hotelData['hotel'] ?? [];
        $offers = $hotelData['offers'] ?? [];
        $firstOffer = $offers[0] ?? null;

        return [
            'id' => 'amadeus_' . ($hotel['hotelId'] ?? 'unknown'),
            'source' => 'amadeus',
            'name' => $hotel['name'] ?? 'Unknown Hotel',
            'description' => $hotel['description']['text'] ?? null,
            'type' => $hotel['type'] ?? 'hotel',
            'star_rating' => (int) ($hotel['rating'] ?? 0),
            'address' => $hotel['address']['lines'][0] ?? null,
            'city' => $hotel['address']['cityName'] ?? null,
            'country' => $hotel['address']['countryCode'] ?? null,
            'postal_code' => $hotel['address']['postalCode'] ?? null,
            'phone' => $hotel['contact']['phone'] ?? null,
            'email' => $hotel['contact']['email'] ?? null,
            'latitude' => $hotel['latitude'] ?? null,
            'longitude' => $hotel['longitude'] ?? null,
            'amenities' => $hotel['amenities'] ?? [],
            'images' => $hotel['media'] ?? [],
            'price' => [
                'amount' => $firstOffer['price']['total'] ?? 0,
                'currency' => $firstOffer['price']['currency'] ?? 'USD',
            ],
            'offers' => $offers,
            'is_available' => !empty($offers),
        ];
    }
}
