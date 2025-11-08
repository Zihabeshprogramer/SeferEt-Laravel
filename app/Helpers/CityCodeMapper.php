<?php

namespace App\Helpers;

class CityCodeMapper
{
    /**
     * Map city names to IATA codes for Amadeus Hotel API
     * 
     * Since Mecca/Makkah doesn't have its own IATA code,
     * we map it to JED (Jeddah) which is the nearest major airport
     * and is used by Amadeus to represent the Mecca/Jeddah area
     */
    private static array $cityToIataMap = [
        // Mecca variations
        'mecca' => 'JED',
        'makkah' => 'JED',
        'makkah al mukarramah' => 'JED',
        'makka' => 'JED',
        
        // Medina variations
        'medina' => 'MED',
        'madinah' => 'MED',
        'al madinah' => 'MED',
        'madinah al munawwarah' => 'MED',
        
        // Jeddah
        'jeddah' => 'JED',
        'jiddah' => 'JED',
        
        // Riyadh
        'riyadh' => 'RUH',
        'riyad' => 'RUH',
        
        // Other Saudi cities
        'dammam' => 'DMM',
        'dhahran' => 'DMM',
        'khobar' => 'DMM',
        'al khobar' => 'DMM',
        'taif' => 'TIF',
        'abha' => 'AHB',
        'tabuk' => 'TUU',
        'yanbu' => 'YNB',
        
        // Popular Muslim destinations
        'dubai' => 'DXB',
        'abu dhabi' => 'AUH',
        'doha' => 'DOH',
        'kuwait' => 'KWI',
        'muscat' => 'MCT',
        'cairo' => 'CAI',
        'istanbul' => 'IST',
        'kuala lumpur' => 'KUL',
        'jakarta' => 'CGK',
        'karachi' => 'KHI',
        'lahore' => 'LHE',
        'islamabad' => 'ISB',
    ];

    /**
     * Convert city name to IATA code
     * 
     * @param string $cityName City name (e.g., "Mecca", "Makkah")
     * @return string IATA code (e.g., "JED")
     */
    public static function cityToIata(string $cityName): string
    {
        $normalized = strtolower(trim($cityName));
        
        // If already an IATA code (3 letters, uppercase), return as-is
        if (preg_match('/^[A-Z]{3}$/', $cityName)) {
            return $cityName;
        }
        
        // Check if city exists in map
        if (isset(self::$cityToIataMap[$normalized])) {
            return self::$cityToIataMap[$normalized];
        }
        
        // If not found, return original (might already be a code)
        return strtoupper($cityName);
    }

    /**
     * Get user-friendly city name from IATA code
     * 
     * @param string $iataCode IATA code (e.g., "JED")
     * @return string City name (e.g., "Jeddah (for Mecca area)")
     */
    public static function iataToFriendlyName(string $iataCode): string
    {
        $friendlyNames = [
            'JED' => 'Jeddah / Mecca area',
            'MED' => 'Medina',
            'RUH' => 'Riyadh',
            'DMM' => 'Dammam / Khobar',
            'TIF' => 'Taif',
            'AHB' => 'Abha',
            'TUU' => 'Tabuk',
            'YNB' => 'Yanbu',
        ];

        return $friendlyNames[$iataCode] ?? $iataCode;
    }

    /**
     * Check if a city name is Mecca/Makkah
     * 
     * @param string $cityName
     * @return bool
     */
    public static function isMecca(string $cityName): bool
    {
        $normalized = strtolower(trim($cityName));
        return in_array($normalized, ['mecca', 'makkah', 'makka', 'makkah al mukarramah']);
    }

    /**
     * Check if a city name is Medina/Madinah
     * 
     * @param string $cityName
     * @return bool
     */
    public static function isMedina(string $cityName): bool
    {
        $normalized = strtolower(trim($cityName));
        return in_array($normalized, ['medina', 'madinah', 'al madinah', 'madinah al munawwarah']);
    }

    /**
     * Get explanation for why JED is used for Mecca
     * 
     * @return string
     */
    public static function getMeccaExplanation(): string
    {
        return 'Hotels in Mecca are searched using JED (Jeddah) code because Mecca does not have an international airport. Jeddah\'s King Abdulaziz International Airport (JED) is the nearest major airport, located approximately 80km from Mecca.';
    }

    /**
     * Get all supported cities
     * 
     * @return array
     */
    public static function getSupportedCities(): array
    {
        return array_unique(array_values(self::$cityToIataMap));
    }

    /**
     * Search for matching cities
     * 
     * @param string $query Search query
     * @return array Matching cities with their codes
     */
    public static function searchCities(string $query): array
    {
        $query = strtolower(trim($query));
        $results = [];

        foreach (self::$cityToIataMap as $cityName => $code) {
            if (str_contains($cityName, $query)) {
                $results[] = [
                    'city' => ucwords($cityName),
                    'code' => $code,
                    'display' => ucwords($cityName) . ' (' . $code . ')',
                ];
            }
        }

        return $results;
    }
}
