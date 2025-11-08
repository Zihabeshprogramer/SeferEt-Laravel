<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomRate;
use App\Models\Flight;
use App\Models\Hotel;
use App\Models\InventoryCalendar;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class UmrahInventorySeeder extends Seeder
{
    /**
     * Run the database seeds - Populate Room Rates and Inventory Calendar
     */
    public function run(): void
    {
        $this->command->info('📅 Starting Umrah Inventory Seeder...');
        $this->command->info('   This will populate Room Rates and Inventory Calendar');
        $this->command->newLine();

        // Seed room rates and hotel inventory
        $this->seedHotelInventory();
        
        // Seed flight inventory
        $this->seedFlightInventory();

        $this->command->newLine();
        $this->command->info('✅ Inventory seeding complete!');
        $this->command->info('📊 Summary:');
        $this->command->info('   - Room Rates: ' . RoomRate::count());
        $this->command->info('   - Inventory Calendar: ' . InventoryCalendar::count());
    }

    /**
     * Seed hotel inventory (room rates + inventory calendar)
     */
    private function seedHotelInventory(): void
    {
        $this->command->info('🏨 Seeding Hotel Inventory...');
        
        $rooms = Room::with('hotel')->where('is_active', true)->get();
        
        if ($rooms->isEmpty()) {
            $this->command->error('❌ No rooms found. Please run UmrahHotelRoomsSeeder first.');
            return;
        }

        $this->command->info("📊 Found {$rooms->count()} rooms");
        
        // Date range for inventory (next 4 months)
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addMonths(4);
        
        $roomRatesCreated = 0;
        $inventoryCreated = 0;
        $hotelsProcessed = [];

        foreach ($rooms as $room) {
            // Create room rates for next 4 months
            $rates = $this->createRoomRates($room, $startDate, $endDate);
            $roomRatesCreated += $rates;
            
            // Track hotels to create inventory calendar
            if (!in_array($room->hotel_id, $hotelsProcessed)) {
                $hotelsProcessed[] = $room->hotel_id;
            }
        }

        // Create inventory calendar for each hotel
        foreach ($hotelsProcessed as $hotelId) {
            $hotel = Hotel::find($hotelId);
            $totalRooms = Room::where('hotel_id', $hotelId)->where('is_active', true)->count();
            
            $created = InventoryCalendar::initializeInventoryForDateRange(
                InventoryCalendar::PROVIDER_HOTEL,
                $hotelId,
                $startDate,
                $endDate,
                $totalRooms,
                null, // Base price will be calculated from rooms
                ['hotel_name' => $hotel->name, 'city' => $hotel->city]
            );
            
            $inventoryCreated += $created;
        }

        $this->command->info("   ✓ Created {$roomRatesCreated} room rates");
        $this->command->info("   ✓ Created {$inventoryCreated} hotel inventory calendar entries");
    }

    /**
     * Seed flight inventory calendar
     */
    private function seedFlightInventory(): void
    {
        $this->command->info('✈️  Seeding Flight Inventory...');
        
        $flights = Flight::where('is_active', true)
                        ->where('status', 'scheduled')
                        ->get();
        
        if ($flights->isEmpty()) {
            $this->command->warn('⚠️  No active flights found.');
            return;
        }

        $this->command->info("📊 Found {$flights->count()} flights");
        
        $inventoryCreated = 0;

        foreach ($flights as $flight) {
            // Create inventory for departure date
            $created = $this->createFlightInventory($flight);
            $inventoryCreated += $created;
        }

        $this->command->info("   ✓ Created {$inventoryCreated} flight inventory calendar entries");
    }

    /**
     * Create room rates for a room
     */
    private function createRoomRates(Room $room, Carbon $startDate, Carbon $endDate): int
    {
        $currentDate = $startDate->copy();
        $rates = [];
        $basePrice = $room->base_price;
        $now = now();

        while ($currentDate <= $endDate) {
            // Apply seasonal pricing
            $price = $this->calculateRoomPrice($basePrice, $currentDate, $room->hotel);
            $dateString = $currentDate->toDateString();
            
            $rates[] = [
                'room_id' => $room->id,
                'date' => $dateString,
                'price' => $price,
                'is_active' => true,
                'notes' => $this->getRateNotes($currentDate),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $currentDate->addDay();
        }

        // Batch insert rates
        if (!empty($rates)) {
            // Delete existing rates for this room in the date range
            RoomRate::where('room_id', $room->id)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->delete();
            
            // Insert in chunks to avoid memory issues
            foreach (array_chunk($rates, 100) as $chunk) {
                RoomRate::insert($chunk);
            }
        }

        return count($rates);
    }

    /**
     * Calculate room price with seasonal adjustments
     */
    private function calculateRoomPrice(float $basePrice, Carbon $date, Hotel $hotel): float
    {
        $price = $basePrice;
        
        // Ramadan pricing (30-50% increase)
        if ($this->isRamadan($date)) {
            $price *= rand(130, 150) / 100;
        }
        
        // Hajj season pricing (50-80% increase)
        elseif ($this->isHajjSeason($date)) {
            $price *= rand(150, 180) / 100;
        }
        
        // Peak Umrah season (November-February)
        elseif (in_array($date->month, [11, 12, 1, 2])) {
            $price *= rand(115, 135) / 100;
        }
        
        // Weekend pricing (Friday-Saturday in Saudi)
        elseif (in_array($date->dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY])) {
            $price *= rand(110, 120) / 100;
        }
        
        // Off-season discount (summer months)
        elseif (in_array($date->month, [6, 7, 8])) {
            $price *= rand(85, 95) / 100;
        }

        return round($price, 2);
    }

    /**
     * Check if date is during Ramadan (approximate)
     */
    private function isRamadan(Carbon $date): bool
    {
        // This is a simplified check - in production, use Islamic calendar
        // Ramadan 2026 is approximately March 2026
        return $date->year === 2026 && $date->month === 3;
    }

    /**
     * Check if date is during Hajj season (approximate)
     */
    private function isHajjSeason(Carbon $date): bool
    {
        // Hajj 2026 is approximately June 2026
        return $date->year === 2026 && $date->month === 6;
    }

    /**
     * Get rate notes based on date
     */
    private function getRateNotes(Carbon $date): ?string
    {
        if ($this->isRamadan($date)) {
            return 'Ramadan Special Rate - High Demand Period';
        }
        
        if ($this->isHajjSeason($date)) {
            return 'Hajj Season Rate - Premium Pricing';
        }
        
        if (in_array($date->month, [11, 12, 1, 2])) {
            return 'Peak Umrah Season';
        }
        
        if (in_array($date->dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY])) {
            return 'Weekend Rate';
        }
        
        return null;
    }

    /**
     * Create flight inventory calendar entries
     */
    private function createFlightInventory(Flight $flight): int
    {
        $created = 0;
        
        // Create inventory for departure date
        $departureDate = $flight->departure_datetime;
        
        InventoryCalendar::updateOrCreate(
            [
                'provider_type' => InventoryCalendar::PROVIDER_FLIGHT,
                'item_id' => $flight->id,
                'date' => $departureDate->toDateString(),
            ],
            [
            'total_capacity' => $flight->total_seats,
            'allocated_capacity' => $flight->total_seats - $flight->available_seats,
            'available_capacity' => $flight->available_seats,
            'blocked_capacity' => 0,
            'base_price' => $flight->economy_price,
            'currency' => $flight->currency,
            'pricing_tiers' => [
                'economy' => [
                    'price' => $flight->economy_price,
                    'group_price' => $flight->group_economy_price,
                ],
                'business' => [
                    'price' => $flight->business_price,
                    'group_price' => $flight->group_business_price,
                ],
            ],
            'is_available' => $flight->is_active && $flight->available_seats > 0,
            'is_bookable' => $flight->is_active && !$flight->isBookingDeadlinePassed(),
            'restriction_notes' => $flight->special_requirements,
            'version' => 1,
            'last_updated_at' => now(),
            'metadata' => [
                'airline' => $flight->airline,
                'flight_number' => $flight->flight_number,
                'departure_airport' => $flight->departure_airport,
                'arrival_airport' => $flight->arrival_airport,
                'trip_type' => $flight->trip_type,
                'is_group_booking' => $flight->is_group_booking,
                'min_group_size' => $flight->min_group_size,
            ],
            ]
        );
        $created++;

        // Create inventory for return date if round trip
        if ($flight->trip_type === 'round_trip' && $flight->return_departure_datetime) {
            $returnDate = $flight->return_departure_datetime;
            
            InventoryCalendar::updateOrCreate(
                [
                    'provider_type' => InventoryCalendar::PROVIDER_FLIGHT,
                    'item_id' => $flight->id,
                    'date' => $returnDate->toDateString(),
                ],
                [
                'total_capacity' => $flight->total_seats,
                'allocated_capacity' => $flight->total_seats - $flight->available_seats,
                'available_capacity' => $flight->available_seats,
                'blocked_capacity' => 0,
                'base_price' => $flight->economy_price,
                'currency' => $flight->currency,
                'pricing_tiers' => [
                    'economy' => [
                        'price' => $flight->economy_price,
                        'group_price' => $flight->group_economy_price,
                    ],
                    'business' => [
                        'price' => $flight->business_price,
                        'group_price' => $flight->group_business_price,
                    ],
                ],
                'is_available' => $flight->is_active && $flight->available_seats > 0,
                'is_bookable' => $flight->is_active && !$flight->isBookingDeadlinePassed(),
                'restriction_notes' => $flight->special_requirements,
                'version' => 1,
                'last_updated_at' => now(),
                'metadata' => [
                    'airline' => $flight->airline,
                    'flight_number' => $flight->return_flight_number,
                    'departure_airport' => $flight->arrival_airport, // Return is reverse
                    'arrival_airport' => $flight->departure_airport,
                    'trip_type' => 'return_leg',
                    'is_group_booking' => $flight->is_group_booking,
                ],
                ]
            );
            $created++;
        }

        return $created;
    }
}
