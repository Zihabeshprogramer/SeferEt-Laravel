<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Database\Seeder;

class UmrahHotelRoomsSeeder extends Seeder
{
    /**
     * Umrah-specific room categories with pricing tiers
     */
    private $roomCategories = [
        'normal_view' => [
            'name' => 'Standard Room',
            'price_multiplier' => 1.0,
            'bed_type' => 'double',
            'max_occupancy' => 2,
        ],
        'window_view' => [
            'name' => 'Window View Room',
            'price_multiplier' => 1.15,
            'bed_type' => 'queen',
            'max_occupancy' => 2,
        ],
        'balcony_view' => [
            'name' => 'Balcony Room',
            'price_multiplier' => 1.30,
            'bed_type' => 'queen',
            'max_occupancy' => 3,
        ],
        'city_view' => [
            'name' => 'City View Room',
            'price_multiplier' => 1.25,
            'bed_type' => 'queen',
            'max_occupancy' => 2,
        ],
        'family_suite' => [
            'name' => 'Family Suite',
            'price_multiplier' => 1.80,
            'bed_type' => 'king',
            'max_occupancy' => 4,
        ],
        'executive_lounge' => [
            'name' => 'Executive Room',
            'price_multiplier' => 1.60,
            'bed_type' => 'king',
            'max_occupancy' => 2,
        ],
    ];

    /**
     * Special Haram/Masjid view category for premium hotels
     */
    private $haramViewCategories = [
        'haram_view' => [
            'name' => 'Haram View Room',
            'price_multiplier' => 2.20,
            'bed_type' => 'king',
            'max_occupancy' => 2,
        ],
        'haram_view_suite' => [
            'name' => 'Haram View Suite',
            'price_multiplier' => 3.00,
            'bed_type' => 'king',
            'max_occupancy' => 4,
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🛏️  Starting Umrah Hotel Rooms Seeder...');
        
        $hotels = Hotel::where('status', 'active')->get();
        
        if ($hotels->isEmpty()) {
            $this->command->error('❌ No active hotels found. Please run UmrahHotelsSeeder first.');
            return;
        }

        $this->command->info('📊 Found ' . $hotels->count() . ' active hotels');
        $this->command->newLine();

        $totalRooms = 0;

        foreach ($hotels as $hotel) {
            $roomsCreated = $this->createRoomsForHotel($hotel);
            $totalRooms += $roomsCreated;
            
            $this->command->info(sprintf(
                '  ✓ %s: Created %d rooms',
                $hotel->name,
                $roomsCreated
            ));
        }

        $this->command->newLine();
        $this->command->info("✅ Successfully seeded {$totalRooms} rooms across {$hotels->count()} hotels!");
        $this->command->info('📈 Total rooms in database: ' . Room::count());
    }

    /**
     * Create rooms for a specific hotel
     */
    private function createRoomsForHotel(Hotel $hotel): int
    {
        $starRating = $hotel->star_rating;
        $isMakkah = $hotel->city === 'Makkah';
        $isClose = $hotel->distance_to_haram < 0.5; // Close to Haram/Masjid
        
        // Determine room count based on hotel rating
        $roomCount = match($starRating) {
            5 => rand(80, 150),
            4 => rand(50, 90),
            3 => rand(30, 60),
            default => rand(20, 40),
        };

        // Determine available categories
        $categories = $this->getAvailableCategoriesForHotel($starRating, $isClose);
        
        // Base price per night based on hotel rating and location
        $basePrice = $this->calculateBasePrice($hotel);
        
        $roomsCreated = 0;
        $currentRoomNumber = 101; // Start from room 101

        foreach ($categories as $categoryKey => $categoryInfo) {
            // Number of rooms per category (distribute based on category popularity)
            $roomsInCategory = $this->getRoomsPerCategory($categoryKey, $roomCount, $starRating);
            
            for ($i = 0; $i < $roomsInCategory; $i++) {
                $floor = (int)($currentRoomNumber / 100);
                
                Room::create([
                    'hotel_id' => $hotel->id,
                    'category' => $categoryKey,
                    'room_number' => (string)$currentRoomNumber,
                    'room_number_start' => $currentRoomNumber,
                    'room_number_end' => $currentRoomNumber,
                    'name' => $categoryInfo['name'] . ' ' . $currentRoomNumber,
                    'description' => $this->generateRoomDescription($categoryInfo, $hotel->city, $floor),
                    'size_sqm' => $this->getRoomSize($categoryKey, $starRating),
                    'bed_type' => $categoryInfo['bed_type'],
                    'bed_count' => $this->getBedCount($categoryInfo['bed_type']),
                    'max_occupancy' => $categoryInfo['max_occupancy'],
                    'amenities' => $this->getRoomAmenities($starRating, $categoryKey),
                    'images' => $this->generateRoomImages($hotel->name, $categoryKey),
                    'base_price' => round($basePrice * $categoryInfo['price_multiplier'], 2),
                    'is_available' => true,
                    'is_active' => true,
                    'created_at' => now()->subDays(rand(30, 120)),
                    'updated_at' => now()->subDays(rand(1, 29)),
                ]);

                $roomsCreated++;
                $currentRoomNumber++;
                
                // Skip to next floor when appropriate
                if ($currentRoomNumber % 100 > 50 && rand(0, 1) === 1) {
                    $currentRoomNumber = (floor($currentRoomNumber / 100) + 1) * 100 + 1;
                }
            }
        }

        return $roomsCreated;
    }

    /**
     * Get available room categories for hotel
     */
    private function getAvailableCategoriesForHotel(int $starRating, bool $isClose): array
    {
        $categories = [];

        if ($starRating >= 5 && $isClose) {
            // Premium hotels close to Haram get special views
            $categories = array_merge($this->roomCategories, $this->haramViewCategories);
        } elseif ($starRating >= 4) {
            // 4-star hotels get most categories except special views
            $categories = $this->roomCategories;
        } else {
            // 3-star hotels get basic categories only
            $categories = array_intersect_key($this->roomCategories, array_flip([
                'normal_view', 'window_view', 'city_view'
            ]));
        }

        return $categories;
    }

    /**
     * Calculate base price for hotel
     */
    private function calculateBasePrice(Hotel $hotel): float
    {
        $starRating = $hotel->star_rating;
        $distance = $hotel->distance_to_haram;
        $isMakkah = $hotel->city === 'Makkah';

        // Base prices by star rating
        $baseByStars = match($starRating) {
            5 => rand(200, 350),
            4 => rand(120, 200),
            3 => rand(80, 120),
            default => rand(60, 90),
        };

        // Distance multiplier (closer = more expensive)
        if ($distance < 0.3) {
            $distanceMultiplier = 1.4;
        } elseif ($distance < 0.6) {
            $distanceMultiplier = 1.2;
        } elseif ($distance < 1.0) {
            $distanceMultiplier = 1.0;
        } else {
            $distanceMultiplier = 0.85;
        }

        // Makkah is generally more expensive than Madinah
        $cityMultiplier = $isMakkah ? 1.15 : 1.0;

        return round($baseByStars * $distanceMultiplier * $cityMultiplier, 2);
    }

    /**
     * Get number of rooms per category
     */
    private function getRoomsPerCategory(string $category, int $totalRooms, int $starRating): int
    {
        $distribution = match($category) {
            'normal_view' => 0.35, // 35% standard rooms
            'window_view' => 0.25, // 25% window view
            'city_view' => 0.15,   // 15% city view
            'balcony_view' => 0.10, // 10% balcony
            'family_suite' => 0.08, // 8% family suites
            'executive_lounge' => 0.07, // 7% executive
            'haram_view' => 0.05,   // 5% Haram view (if available)
            'haram_view_suite' => 0.03, // 3% Haram view suites
            default => 0.10,
        };

        return max(1, (int)($totalRooms * $distribution));
    }

    /**
     * Get room size based on category and rating
     */
    private function getRoomSize(string $category, int $starRating): float
    {
        $baseSizes = [
            'normal_view' => 25,
            'window_view' => 28,
            'city_view' => 30,
            'balcony_view' => 32,
            'family_suite' => 45,
            'executive_lounge' => 38,
            'haram_view' => 35,
            'haram_view_suite' => 55,
        ];

        $baseSize = $baseSizes[$category] ?? 28;
        $starMultiplier = 1 + (($starRating - 3) * 0.1); // +10% per star above 3

        return round($baseSize * $starMultiplier + rand(-2, 3), 2);
    }

    /**
     * Get bed count based on bed type
     */
    private function getBedCount(string $bedType): int
    {
        return match($bedType) {
            'twin' => 2,
            'king', 'queen', 'double' => 1,
            default => 1,
        };
    }

    /**
     * Generate room description
     */
    private function generateRoomDescription(array $categoryInfo, string $city, int $floor): string
    {
        $masjidName = $city === 'Makkah' ? 'Masjid al-Haram' : 'Masjid an-Nabawi';
        $floorText = $floor > 10 ? "high floor" : "mid-level floor";
        
        $descriptions = [
            "Comfortable {$categoryInfo['name']} on {$floorText}, perfect for Umrah pilgrims. Close to {$masjidName}.",
            "Well-appointed {$categoryInfo['name']} with modern amenities. Ideal for rest after worship at {$masjidName}.",
            "Spacious {$categoryInfo['name']} designed for pilgrim comfort. Walking distance to holy sites.",
            "{$categoryInfo['name']} featuring contemporary furnishings and Islamic decor. Perfect for your spiritual journey.",
        ];

        return $descriptions[array_rand($descriptions)];
    }

    /**
     * Get room amenities based on rating and category
     */
    private function getRoomAmenities(int $starRating, string $category): array
    {
        $baseAmenities = [
            'Air Conditioning',
            'Private Bathroom',
            'Free WiFi',
            'Flat-screen TV',
            'Prayer Mat & Qibla Direction',
            'Safe Deposit Box',
            'Wardrobe',
            'Desk',
            'Telephone',
        ];

        if ($starRating >= 4) {
            $baseAmenities = array_merge($baseAmenities, [
                'Mini Refrigerator',
                'Electric Kettle',
                'Complimentary Water',
                'Toiletries',
                'Hair Dryer',
                'Iron & Ironing Board',
                'Slippers',
            ]);
        }

        if ($starRating >= 5) {
            $baseAmenities = array_merge($baseAmenities, [
                'Coffee/Tea Maker',
                'Bathrobe',
                'Premium Bedding',
                'Blackout Curtains',
                'Soundproofing',
                'Daily Newspaper',
                'Room Service',
            ]);
        }

        if (str_contains($category, 'suite')) {
            $baseAmenities[] = 'Separate Living Area';
            $baseAmenities[] = 'Sofa';
            $baseAmenities[] = 'Dining Table';
        }

        if (str_contains($category, 'haram_view')) {
            $baseAmenities[] = 'Haram View';
            $baseAmenities[] = 'Binoculars';
        }

        return $baseAmenities;
    }

    /**
     * Generate room image paths
     */
    private function generateRoomImages(string $hotelName, string $category): array
    {
        $slug = strtolower(str_replace(' ', '-', $hotelName));
        $categorySlug = str_replace('_', '-', $category);
        
        return [
            "hotels/{$slug}/rooms/{$categorySlug}-1.jpg",
            "hotels/{$slug}/rooms/{$categorySlug}-2.jpg",
            "hotels/{$slug}/rooms/{$categorySlug}-bathroom.jpg",
        ];
    }
}
