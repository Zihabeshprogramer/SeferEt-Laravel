<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class UmrahHotelsSeeder extends Seeder
{
    /**
     * Authentic Makkah hotels near Haram
     */
    private $makkahHotels = [
        ['name' => 'Al Safwah Tower Makkah', 'star' => 5, 'distance' => 0.2],
        ['name' => 'Dar Al Eiman Royal Hotel', 'star' => 5, 'distance' => 0.3],
        ['name' => 'Swissotel Makkah', 'star' => 5, 'distance' => 0.4],
        ['name' => 'Raffles Makkah Palace', 'star' => 5, 'distance' => 0.5],
        ['name' => 'Hilton Makkah Convention Hotel', 'star' => 5, 'distance' => 0.6],
        ['name' => 'Makkah Clock Royal Tower', 'star' => 5, 'distance' => 0.1],
        ['name' => 'Al Marwa Rayhaan by Rotana', 'star' => 4, 'distance' => 0.5],
        ['name' => 'Elaf Kinda Hotel', 'star' => 4, 'distance' => 0.7],
        ['name' => 'Anjum Hotel Makkah', 'star' => 4, 'distance' => 0.8],
        ['name' => 'Makkah Hotel', 'star' => 3, 'distance' => 1.0],
        ['name' => 'Al Kiswah Towers Hotel', 'star' => 3, 'distance' => 1.2],
        ['name' => 'Emaar Grand Hotel', 'star' => 4, 'distance' => 0.9],
        ['name' => 'Jabal Omar Marriott Hotel', 'star' => 5, 'distance' => 0.4],
        ['name' => 'Conrad Makkah', 'star' => 5, 'distance' => 0.3],
        ['name' => 'Le Meridien Towers Makkah', 'star' => 5, 'distance' => 0.5],
    ];

    /**
     * Authentic Madinah hotels near Masjid Nabawi
     */
    private $madinahHotels = [
        ['name' => 'Pullman Zamzam Madinah', 'star' => 5, 'distance' => 0.3],
        ['name' => 'Anwar Al Madinah Movenpick Hotel', 'star' => 5, 'distance' => 0.4],
        ['name' => 'Shaza Al Madina', 'star' => 5, 'distance' => 0.5],
        ['name' => 'Crowne Plaza Madinah', 'star' => 5, 'distance' => 0.6],
        ['name' => 'Al Aqeeq Hotel', 'star' => 4, 'distance' => 0.7],
        ['name' => 'Taiba Madinah Hotel', 'star' => 4, 'distance' => 0.8],
        ['name' => 'Elaf Taiba Hotel', 'star' => 4, 'distance' => 0.9],
        ['name' => 'Dar Al Taqwa Hotel', 'star' => 3, 'distance' => 1.0],
        ['name' => 'Madinah Hilton Hotel', 'star' => 5, 'distance' => 0.5],
        ['name' => 'Dار Al Eiman Umm Al Qura', 'star' => 4, 'distance' => 0.6],
        ['name' => 'Al Haram Hotel', 'star' => 3, 'distance' => 1.1],
        ['name' => 'Millennium Al Aqeeq Madinah', 'star' => 5, 'distance' => 0.7],
        ['name' => 'Oberoi Madinah', 'star' => 5, 'distance' => 0.4],
        ['name' => 'Madinah Marriott Hotel', 'star' => 5, 'distance' => 0.8],
        ['name' => 'Dar Al Tawfiq Hotel', 'star' => 3, 'distance' => 1.3],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get hotel providers
        $providers = User::where('role', User::ROLE_HOTEL_PROVIDER)->get();
        
        if ($providers->isEmpty()) {
            $this->command->error('❌ No hotel providers found. Please seed hotel providers first.');
            return;
        }

        $this->command->info('🏨 Starting Umrah Hotels Seeder...');
        $this->command->info('📊 Found ' . $providers->count() . ' hotel providers');
        $this->command->newLine();

        // Generate hotels
        $hotelsData = $this->generateHotelsData($providers);
        
        $this->command->info('💾 Inserting ' . count($hotelsData) . ' hotels into database...');
        
        foreach ($hotelsData as $index => $hotelData) {
            Hotel::create($hotelData);
            $this->command->info(sprintf(
                '  ✓ [%d/%d] %s (%s) - %s star - %.1fkm from Haram',
                $index + 1,
                count($hotelsData),
                $hotelData['name'],
                $hotelData['city'],
                $hotelData['star_rating'],
                $hotelData['distance_to_haram']
            ));
        }

        $this->command->newLine();
        $this->command->info('✅ Successfully seeded ' . count($hotelsData) . ' Umrah hotels!');
        $this->command->info('📈 Total hotels in database: ' . Hotel::count());
    }

    /**
     * Generate realistic Umrah hotels data
     */
    private function generateHotelsData($providers): array
    {
        $hotels = [];
        
        // Add Makkah hotels
        foreach ($this->makkahHotels as $hotelInfo) {
            $hotels[] = $this->createHotelData($hotelInfo, 'Makkah', $providers->random());
        }
        
        // Add Madinah hotels
        foreach ($this->madinahHotels as $hotelInfo) {
            $hotels[] = $this->createHotelData($hotelInfo, 'Madinah', $providers->random());
        }
        
        // Shuffle to mix cities
        shuffle($hotels);
        
        return $hotels;
    }

    /**
     * Create individual hotel data
     */
    private function createHotelData(array $hotelInfo, string $city, $provider): array
    {
        $isMakkah = $city === 'Makkah';
        $starRating = $hotelInfo['star'];
        $distanceToHaram = $hotelInfo['distance'];
        
        return [
            'provider_id' => $provider->id,
            'name' => $hotelInfo['name'],
            'description' => $this->generateDescription($hotelInfo['name'], $city, $starRating, $distanceToHaram),
            'type' => $this->getHotelType($starRating),
            'star_rating' => $starRating,
            
            // Location
            'address' => $this->generateAddress($city, $isMakkah),
            'city' => $city,
            'country' => 'Saudi Arabia',
            'postal_code' => $isMakkah ? rand(21000, 21999) : rand(41000, 41999),
            'latitude' => $this->getLatitude($city),
            'longitude' => $this->getLongitude($city),
            
            // Contact
            'phone' => '+966 ' . rand(12, 14) . ' ' . rand(100, 999) . ' ' . rand(1000, 9999),
            'email' => strtolower(str_replace(' ', '', $hotelInfo['name'])) . '@hotel.sa',
            'website' => 'https://www.' . strtolower(str_replace(' ', '', $hotelInfo['name'])) . '.com',
            
            // Policies
            'check_in_time' => '15:00',
            'check_out_time' => '12:00',
            'distance_to_haram' => $distanceToHaram,
            'distance_to_airport' => $isMakkah ? rand(15, 25) : rand(10, 20),
            
            // Amenities
            'amenities' => $this->getAmenities($starRating),
            
            // Images - placeholder paths
            'images' => $this->generateImagePaths($hotelInfo['name'], $starRating),
            
            // Policies
            'policy_cancellation' => $this->getCancellationPolicy($starRating),
            'policy_children' => 'Children under 6 years stay free when using existing bedding. Extra bed charges apply for older children.',
            'policy_pets' => 'Pets are not allowed as per Islamic guidelines.',
            
            // Status
            'status' => 'active',
            'is_active' => true,
            
            // Timestamps
            'created_at' => now()->subDays(rand(30, 120)),
            'updated_at' => now()->subDays(rand(1, 29)),
        ];
    }

    /**
     * Generate hotel description
     */
    private function generateDescription(string $name, string $city, int $stars, float $distance): string
    {
        $distanceText = $distance < 0.5 ? 'just steps away from' : ($distance < 1 ? 'walking distance to' : 'close proximity to');
        $hotelType = $stars >= 5 ? 'luxurious' : ($stars >= 4 ? 'comfortable' : 'affordable');
        
        $masjidName = $city === 'Makkah' ? 'Masjid al-Haram' : 'Masjid an-Nabawi';
        
        $descriptions = [
            "{$name} offers {$hotelType} accommodations in {$city}, {$distanceText} {$masjidName}. Perfect for Umrah pilgrims seeking comfort and convenience.",
            "Experience authentic Saudi hospitality at {$name}, located {$distanceText} {$masjidName}. Ideal for individual travelers and group bookings.",
            "{$name} provides exceptional service for Umrah guests in the heart of {$city}, only {$distance}km from {$masjidName}.",
            "Stay at {$name}, a {$hotelType} hotel in {$city} offering easy access to {$masjidName} and modern amenities for pilgrims.",
        ];
        
        return $descriptions[array_rand($descriptions)];
    }

    /**
     * Get hotel type based on star rating
     */
    private function getHotelType(int $stars): string
    {
        if ($stars >= 5) return 'luxury';
        if ($stars >= 4) return 'business';
        return 'budget';
    }

    /**
     * Generate realistic address
     */
    private function generateAddress(string $city, bool $isMakkah): string
    {
        if ($isMakkah) {
            $areas = [
                'Ibrahim Al Khalil Street',
                'Ajyad Street',
                'King Abdul Aziz Road',
                'Makkah Al Mukarramah Road',
                'Al Aziziyah District',
                'Jabal Omar District',
                'Al Ghassalah District',
            ];
        } else {
            $areas = [
                'King Faisal Road',
                'Al Madinah Al Munawwarah Road',
                'King Abdul Aziz Road',
                'Al Haram District',
                'Quba Road',
                'Central Area',
                'King Abdullah Road',
            ];
        }
        
        return $areas[array_rand($areas)] . ', ' . $city;
    }

    /**
     * Get approximate latitude based on city
     */
    private function getLatitude(string $city): float
    {
        // Makkah: ~21.4225, Madinah: ~24.4672
        if ($city === 'Makkah') {
            return 21.4225 + (rand(-100, 100) / 10000);
        }
        return 24.4672 + (rand(-100, 100) / 10000);
    }

    /**
     * Get approximate longitude based on city
     */
    private function getLongitude(string $city): float
    {
        // Makkah: ~39.8262, Madinah: ~39.6117
        if ($city === 'Makkah') {
            return 39.8262 + (rand(-100, 100) / 10000);
        }
        return 39.6117 + (rand(-100, 100) / 10000);
    }

    /**
     * Get amenities based on star rating
     */
    private function getAmenities(int $stars): array
    {
        $baseAmenities = [
            'Free WiFi',
            '24-hour Front Desk',
            'Air Conditioning',
            'Prayer Area',
            'Shuttle to Haram',
            'Currency Exchange',
            'Luggage Storage',
            'Daily Housekeeping',
        ];

        if ($stars >= 4) {
            $baseAmenities = array_merge($baseAmenities, [
                'Restaurant',
                'Room Service',
                'Elevator',
                'Breakfast Included',
                'Coffee Maker',
                'Mini Refrigerator',
            ]);
        }

        if ($stars >= 5) {
            $baseAmenities = array_merge($baseAmenities, [
                'Haram View Rooms',
                'Premium Bedding',
                'Concierge Service',
                'Valet Parking',
                'Business Center',
                'Meeting Rooms',
                'Spa & Wellness',
                'Fitness Center',
                'Multiple Restaurants',
                'Executive Lounge',
            ]);
        }

        return $baseAmenities;
    }

    /**
     * Generate image paths (placeholder)
     */
    private function generateImagePaths(string $hotelName, int $stars): array
    {
        $slug = strtolower(str_replace(' ', '-', $hotelName));
        
        return [
            "hotels/{$slug}/exterior.jpg",
            "hotels/{$slug}/lobby.jpg",
            "hotels/{$slug}/room-1.jpg",
            "hotels/{$slug}/room-2.jpg",
            $stars >= 5 ? "hotels/{$slug}/suite.jpg" : "hotels/{$slug}/room-3.jpg",
        ];
    }

    /**
     * Get cancellation policy based on star rating
     */
    private function getCancellationPolicy(int $stars): string
    {
        if ($stars >= 5) {
            return 'Free cancellation up to 7 days before check-in. Cancellations within 7 days subject to one night charge. No-show charges apply for full stay.';
        }
        
        if ($stars >= 4) {
            return 'Free cancellation up to 3 days before check-in. Cancellations within 3 days incur 50% charge. No-show charges full amount.';
        }
        
        return 'Free cancellation up to 24 hours before check-in. Late cancellations and no-shows are charged full amount. Ramadan bookings non-refundable.';
    }
}
