<?php

namespace App\Services;

use App\Models\Offer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AmadeusService
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
     * Search airports by keyword (for autocomplete)
     */
    public function searchAirports(string $keyword, int $limit = 10): array
    {
        $cacheKey = 'amadeus_airports_' . strtolower($keyword);

        return Cache::remember($cacheKey, 86400, function () use ($keyword, $limit) {
            try {
                $token = $this->getAccessToken();

                $response = Http::withOptions(['verify' => false])
                    ->withToken($token)
                    ->get("{$this->baseUrl}/v1/reference-data/locations", [
                        'subType' => 'AIRPORT',
                        'keyword' => $keyword,
                        'page[limit]' => $limit,
                    ]);

                if ($response->failed()) {
                    Log::channel('daily')->error('Amadeus airport search failed', [
                        'keyword' => $keyword,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    return ['data' => []];
                }

                return $response->json();
            } catch (\Exception $e) {
                Log::channel('daily')->error('Amadeus airport search exception', [
                    'keyword' => $keyword,
                    'error' => $e->getMessage(),
                ]);
                return ['data' => []];
            }
        });
    }

    /**
     * Get offer by hash from database
     */
    public function getOfferByHash(string $hash): ?Offer
    {
        return Offer::where('offer_hash', $hash)->first();
    }

    /**
     * Search for flight offers
     */
    public function searchFlights(array $params): array
    {
        $cacheKey = 'amadeus_search_' . md5(json_encode($params));

        return Cache::remember($cacheKey, $this->searchTtl, function () use ($params) {
            try {
                $token = $this->getAccessToken();

                $queryParams = [
                    'originLocationCode' => $params['origin'],
                    'destinationLocationCode' => $params['destination'],
                    'departureDate' => $params['departure_date'],
                    'adults' => $params['adults'] ?? 1,
                ];

                if (!empty($params['return_date'])) {
                    $queryParams['returnDate'] = $params['return_date'];
                }

                // Add optional parameters for pagination and sorting
                if (!empty($params['max'])) {
                    $queryParams['max'] = min($params['max'], 250);
                }

                if (!empty($params['currency_code'])) {
                    $queryParams['currencyCode'] = $params['currency_code'];
                }

                if (!empty($params['non_stop'])) {
                    $queryParams['nonStop'] = $params['non_stop'];
                }

                if (!empty($params['travel_class'])) {
                    $queryParams['travelClass'] = $params['travel_class'];
                }

                $response = Http::withOptions(['verify' => false])
                    ->withToken($token)
                    ->get("{$this->baseUrl}/v2/shopping/flight-offers", $queryParams);

                if ($response->failed()) {
                    Log::channel('daily')->error('Amadeus flight search failed', [
                        'params' => $params,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    return ['data' => []];
                }

                $result = $response->json();
                
                // Store offers in database
                if (!empty($result['data'])) {
                    foreach ($result['data'] as $offerData) {
                        $this->upsertOffer($offerData);
                    }
                }

                return $result;
            } catch (\Exception $e) {
                Log::channel('daily')->error('Amadeus search exception', [
                    'params' => $params,
                    'error' => $e->getMessage(),
                ]);
                return ['data' => []];
            }
        });
    }

    /**
     * Revalidate offer (pricing check)
     */
    public function revalidateOffer(array $offerData): ?array
    {
        return $this->validatePricing($offerData);
    }

    /**
     * Validate flight offer pricing
     */
    public function validatePricing(array $offerData): ?array
    {
        try {
            $token = $this->getAccessToken();

            $response = Http::withOptions([
                    'verify' => false,
                    'timeout' => 60, // 60 seconds timeout
                ])
                ->withToken($token)
                ->post("{$this->baseUrl}/v1/shopping/flight-offers/pricing", [
                    'data' => [
                        'type' => 'flight-offers-pricing',
                        'flightOffers' => [$offerData],
                    ],
                ]);

            if ($response->failed()) {
                Log::channel('daily')->error('Amadeus pricing validation failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::channel('daily')->error('Amadeus pricing exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Create flight booking
     */
    public function createBooking(array $offerData, array $travelers): ?array
    {
        try {
            $token = $this->getAccessToken();

            $payload = [
                'data' => [
                    'type' => 'flight-order',
                    'flightOffers' => [$offerData],
                    'travelers' => $travelers,
                ],
            ];

            $response = Http::withOptions([
                    'verify' => false,
                    'timeout' => 60, // 60 seconds timeout
                ])
                ->withToken($token)
                ->post("{$this->baseUrl}/v1/booking/flight-orders", $payload);

            if ($response->failed()) {
                Log::channel('daily')->error('Amadeus booking failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $result = $response->json();
            
            Log::channel('daily')->info('Amadeus booking successful', [
                'pnr' => $result['data']['associatedRecords'][0]['reference'] ?? 'N/A',
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::channel('daily')->error('Amadeus booking exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Compute deterministic offer hash
     */
    public function computeOfferHash(array $offerData): string
    {
        // Extract key identifying fields
        $itineraries = $offerData['itineraries'] ?? [];
        $segments = [];
        
        foreach ($itineraries as $itinerary) {
            foreach ($itinerary['segments'] ?? [] as $segment) {
                $segments[] = [
                    'departure' => $segment['departure']['iataCode'] ?? '',
                    'arrival' => $segment['arrival']['iataCode'] ?? '',
                    'departure_at' => $segment['departure']['at'] ?? '',
                    'carrier' => $segment['carrierCode'] ?? '',
                    'number' => $segment['number'] ?? '',
                ];
            }
        }

        $price = $offerData['price']['total'] ?? '0';
        $currency = $offerData['price']['currency'] ?? 'USD';

        $hashData = [
            'segments' => $segments,
            'price' => $price,
            'currency' => $currency,
        ];

        return hash('sha256', json_encode($hashData));
    }

    /**
     * Upsert offer to database
     */
    public function upsertOffer(array $offerData): Offer
    {
        $offerHash = $this->computeOfferHash($offerData);

        // Extract first and last segments for origin/destination
        $itineraries = $offerData['itineraries'] ?? [];
        $firstItinerary = $itineraries[0] ?? null;
        $lastItinerary = end($itineraries);
        
        $firstSegment = $firstItinerary['segments'][0] ?? null;
        $lastSegmentArray = $lastItinerary['segments'] ?? [];
        $lastSegment = end($lastSegmentArray);

        $origin = $firstSegment['departure']['iataCode'] ?? 'XXX';
        $destination = $lastSegment['arrival']['iataCode'] ?? 'XXX';
        $departureDate = isset($firstSegment['departure']['at']) 
            ? date('Y-m-d', strtotime($firstSegment['departure']['at'])) 
            : now()->format('Y-m-d');
        $returnDate = count($itineraries) > 1 && isset($lastSegment['departure']['at'])
            ? date('Y-m-d', strtotime($lastSegment['departure']['at']))
            : null;

        $price = $offerData['price']['total'] ?? 0;
        $currency = $offerData['price']['currency'] ?? 'USD';

        $offerAttributes = [
            'offer_source' => 'amadeus',
            'external_offer_id' => $offerData['id'] ?? null,
            'origin' => $origin,
            'destination' => $destination,
            'departure_date' => $departureDate,
            'return_date' => $returnDate,
            'price_amount' => $price,
            'price_currency' => $currency,
            'segments' => $offerData['itineraries'] ?? [],
        ];

        return Offer::firstOrCreate(
            ['offer_hash' => $offerHash],
            $offerAttributes
        );
    }
}
