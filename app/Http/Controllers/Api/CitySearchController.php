<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AmadeusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CitySearchController extends Controller
{
    protected AmadeusService $amadeusService;

    public function __construct(AmadeusService $amadeusService)
    {
        $this->amadeusService = $amadeusService;
    }

    /**
     * Search cities using Amadeus API
     * 
     * GET /api/cities/search?keyword={query}
     */
    public function search(Request $request)
    {
        $keyword = $request->input('keyword', '');
        
        if (strlen($keyword) < 2) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        try {
            // Cache results for 24 hours
            $cacheKey = 'city_search_' . strtolower($keyword);
            
            $results = Cache::remember($cacheKey, 86400, function () use ($keyword) {
                $amadeusResults = $this->amadeusService->searchAirports($keyword, 20);
                
                $cities = [];
                $seenCities = [];
                
                foreach ($amadeusResults['data'] ?? [] as $location) {
                    $cityName = $location['address']['cityName'] ?? null;
                    $cityCode = $location['address']['cityCode'] ?? null;
                    $countryName = $location['address']['countryName'] ?? '';
                    $iataCode = $location['iataCode'] ?? null;
                    
                    if (!$cityName || !$cityCode) {
                        continue;
                    }
                    
                    // Avoid duplicate cities
                    $cityKey = $cityCode . '_' . $cityName;
                    if (isset($seenCities[$cityKey])) {
                        continue;
                    }
                    $seenCities[$cityKey] = true;
                    
                    $cities[] = [
                        'city_code' => $cityCode,
                        'city_name' => $cityName,
                        'country' => $countryName,
                        'iata_code' => $iataCode,
                        'display' => $cityName . ($countryName ? ', ' . $countryName : ''),
                        'value' => $cityCode, // Use city code as value
                    ];
                }
                
                return $cities;
            });

            return response()->json([
                'success' => true,
                'data' => $results
            ]);

        } catch (\Exception $e) {
            \Log::error('City search failed', [
                'keyword' => $keyword,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to search cities',
                'data' => []
            ], 500);
        }
    }
}
