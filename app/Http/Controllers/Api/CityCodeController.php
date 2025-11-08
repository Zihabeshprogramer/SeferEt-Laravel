<?php

namespace App\Http\Controllers\Api;

use App\Controllers\Controller;
use App\Helpers\CityCodeMapper;
use Illuminate\Http\Request;

class CityCodeController extends Controller
{
    /**
     * Search for cities and their codes
     * 
     * GET /api/cities/search?query=mecca
     */
    public function search(Request $request)
    {
        $query = $request->input('query', '');
        
        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Query parameter is required'
            ], 400);
        }

        $results = CityCodeMapper::searchCities($query);

        return response()->json([
            'success' => true,
            'data' => $results,
            'query' => $query
        ]);
    }

    /**
     * Get IATA code for a city
     * 
     * GET /api/cities/code?city=mecca
     */
    public function getCode(Request $request)
    {
        $city = $request->input('city', '');
        
        if (empty($city)) {
            return response()->json([
                'success' => false,
                'message' => 'City parameter is required'
            ], 400);
        }

        $code = CityCodeMapper::cityToIata($city);
        $isMecca = CityCodeMapper::isMecca($city);
        $isMedina = CityCodeMapper::isMedina($city);

        return response()->json([
            'success' => true,
            'data' => [
                'city' => $city,
                'code' => $code,
                'display_name' => CityCodeMapper::iataToFriendlyName($code),
                'is_mecca' => $isMecca,
                'is_medina' => $isMedina,
                'explanation' => $isMecca ? CityCodeMapper::getMeccaExplanation() : null,
            ]
        ]);
    }

    /**
     * Get all supported cities
     * 
     * GET /api/cities/supported
     */
    public function getSupportedCities()
    {
        $cities = CityCodeMapper::getSupportedCities();

        return response()->json([
            'success' => true,
            'data' => $cities,
            'total' => count($cities)
        ]);
    }

    /**
     * Get explanation for Mecca-JED mapping
     * 
     * GET /api/cities/mecca-explanation
     */
    public function getMeccaExplanation()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'question' => 'Why does Mecca show JED code?',
                'answer' => CityCodeMapper::getMeccaExplanation(),
                'mapping' => [
                    'input' => ['Mecca', 'Makkah', 'Makka'],
                    'code' => 'JED',
                    'airport' => 'King Abdulaziz International Airport',
                    'city' => 'Jeddah',
                    'distance_from_mecca' => '80km (approximately 1 hour drive)',
                ]
            ]
        ]);
    }
}
