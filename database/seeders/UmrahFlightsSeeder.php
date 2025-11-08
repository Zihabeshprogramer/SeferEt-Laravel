<?php

namespace Database\Seeders;

use App\Models\Flight;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class UmrahFlightsSeeder extends Seeder
{
    /**
     * Realistic Umrah airlines serving pilgrims
     */
    private $airlines = [
        'Saudia',
        'Flynas',
        'Qatar Airways',
        'Emirates',
        'Etihad Airways',
        'Turkish Airlines',
        'Pakistan International Airlines',
        'IndiGo',
        'Air Arabia',
        'Gulf Air',
    ];

    /**
     * Major departure cities for Umrah pilgrims
     */
    private $departureAirports = [
        ['code' => 'KHI', 'city' => 'Karachi', 'country' => 'Pakistan'],
        ['code' => 'LHE', 'city' => 'Lahore', 'country' => 'Pakistan'],
        ['code' => 'ISB', 'city' => 'Islamabad', 'country' => 'Pakistan'],
        ['code' => 'DEL', 'city' => 'New Delhi', 'country' => 'India'],
        ['code' => 'BOM', 'city' => 'Mumbai', 'country' => 'India'],
        ['code' => 'CGK', 'city' => 'Jakarta', 'country' => 'Indonesia'],
        ['code' => 'CAI', 'city' => 'Cairo', 'country' => 'Egypt'],
        ['code' => 'IST', 'city' => 'Istanbul', 'country' => 'Turkey'],
        ['code' => 'DAC', 'city' => 'Dhaka', 'country' => 'Bangladesh'],
        ['code' => 'KUL', 'city' => 'Kuala Lumpur', 'country' => 'Malaysia'],
    ];

    /**
     * Saudi Arabia destinations (Jeddah & Madinah)
     */
    private $arrivalAirports = [
        ['code' => 'JED', 'city' => 'Jeddah', 'name' => 'King Abdulaziz International Airport'],
        ['code' => 'MED', 'city' => 'Madinah', 'name' => 'Prince Mohammad Bin Abdulaziz Airport'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get travel agents (providers)
        $agents = User::where('role', User::ROLE_TRAVEL_AGENT)->get();
        
        if ($agents->isEmpty()) {
            $this->command->error('❌ No travel agents found. Please seed travel agents first.');
            return;
        }

        $this->command->info('✈️  Starting Umrah Flights Seeder...');
        $this->command->info('📊 Found ' . $agents->count() . ' travel agents');
        $this->command->newLine();

        $flightsData = $this->generateFlightsData($agents);
        
        $this->command->info('💾 Inserting ' . count($flightsData) . ' flights into database...');
        
        foreach ($flightsData as $index => $flightData) {
            Flight::create($flightData);
            $this->command->info(sprintf(
                '  ✓ [%d/%d] %s - %s → %s (Dep: %s)',
                $index + 1,
                count($flightsData),
                $flightData['airline'],
                $flightData['departure_airport'],
                $flightData['arrival_airport'],
                Carbon::parse($flightData['departure_datetime'])->format('M d, Y')
            ));
        }

        $this->command->newLine();
        $this->command->info('✅ Successfully seeded ' . count($flightsData) . ' Umrah flights!');
        $this->command->info('📈 Total flights in database: ' . Flight::count());
    }

    /**
     * Generate realistic Umrah flight data
     */
    private function generateFlightsData($agents): array
    {
        $flights = [];
        $totalFlights = rand(22, 25);

        for ($i = 0; $i < $totalFlights; $i++) {
            $agent = $agents->random();
            $airline = $this->airlines[array_rand($this->airlines)];
            $departure = $this->departureAirports[array_rand($this->departureAirports)];
            $arrival = $this->arrivalAirports[array_rand($this->arrivalAirports)];
            
            // Departure within next 3 months
            $daysAhead = rand(5, 90);
            $departureDate = Carbon::now()->addDays($daysAhead);
            
            // Flight duration (4-8 hours)
            $flightDuration = rand(240, 480); // minutes
            $arrivalDate = (clone $departureDate)->addMinutes($flightDuration);
            
            // Round trip duration (7-14 days)
            $tripDays = rand(7, 14);
            $returnDepartureDate = (clone $departureDate)->addDays($tripDays);
            $returnArrivalDate = (clone $returnDepartureDate)->addMinutes($flightDuration);
            
            // Pricing (realistic Umrah flight prices)
            $economyPrice = rand(350, 800);
            $groupEconomyPrice = $economyPrice * 0.85; // 15% group discount
            $businessPrice = $economyPrice * 2.5;
            $groupBusinessPrice = $businessPrice * 0.90; // 10% group discount
            
            // Flight number format
            $flightNumber = $this->generateFlightNumber($airline);
            $returnFlightNumber = $this->generateFlightNumber($airline);
            
            // Booking deadline (3-10 days before departure)
            $bookingDeadline = (clone $departureDate)->subDays(rand(3, 10));
            
            $flights[] = [
                'provider_id' => $agent->id,
                'airline' => $airline,
                'flight_number' => $flightNumber,
                'trip_type' => 'round_trip',
                'return_flight_number' => $returnFlightNumber,
                
                // Departure flight
                'departure_airport' => $departure['code'] . ' - ' . $departure['city'] . ', ' . $departure['country'],
                'arrival_airport' => $arrival['code'] . ' - ' . $arrival['city'] . ', Saudi Arabia',
                'departure_datetime' => $departureDate,
                'arrival_datetime' => $arrivalDate,
                
                // Return flight
                'return_departure_datetime' => $returnDepartureDate,
                'return_arrival_datetime' => $returnArrivalDate,
                
                // Capacity
                'total_seats' => rand(100, 200),
                'available_seats' => rand(30, 100),
                
                // Pricing
                'economy_price' => $economyPrice,
                'group_economy_price' => round($groupEconomyPrice, 2),
                'business_price' => round($businessPrice, 2),
                'group_business_price' => round($groupBusinessPrice, 2),
                'first_class_price' => null,
                'group_first_class_price' => null,
                'currency' => 'USD',
                
                // Aircraft details
                'aircraft_type' => $this->getAircraftType($airline),
                
                // Amenities
                'amenities' => $this->getAmenities($airline),
                
                // Description
                'description' => $this->generateDescription($departure, $arrival, $tripDays, $airline),
                
                // Group booking settings
                'is_active' => true,
                'is_group_booking' => true,
                'min_group_size' => rand(10, 15),
                'max_group_size' => rand(40, 60),
                'group_discount_percentage' => rand(10, 20),
                'booking_deadline' => $bookingDeadline,
                
                // Collaboration
                'allows_agent_collaboration' => rand(0, 1) === 1,
                'collaboration_commission_percentage' => rand(3, 8),
                'collaboration_terms' => 'Standard agent collaboration terms apply. Commission paid after passenger confirmation.',
                
                // Status
                'status' => 'scheduled',
                
                // Baggage
                'baggage_allowance' => $this->getBaggageAllowance($airline),
                
                // Meal service
                'meal_service' => [
                    'meals_included' => true,
                    'halal_certified' => true,
                    'special_meals' => ['Vegetarian', 'Diabetic', 'Child meal'],
                    'note' => 'All meals are 100% Halal certified'
                ],
                
                // Included services
                'included_services' => $this->getIncludedServices($airline),
                
                // Special requirements
                'special_requirements' => $this->getSpecialRequirements(),
                
                // Payment terms
                'payment_terms' => ['full_upfront', '50_percent_deposit', '30_percent_deposit'][array_rand(['full_upfront', '50_percent_deposit', '30_percent_deposit'])],
                
                // Timestamps
                'created_at' => now()->subDays(rand(10, 45)),
                'updated_at' => now()->subDays(rand(1, 9)),
            ];
        }

        return $flights;
    }

    /**
     * Generate flight number
     */
    private function generateFlightNumber($airline): string
    {
        $prefixes = [
            'Saudia' => 'SV',
            'Flynas' => 'XY',
            'Qatar Airways' => 'QR',
            'Emirates' => 'EK',
            'Etihad Airways' => 'EY',
            'Turkish Airlines' => 'TK',
            'Pakistan International Airlines' => 'PK',
            'IndiGo' => '6E',
            'Air Arabia' => 'G9',
            'Gulf Air' => 'GF',
        ];

        $prefix = $prefixes[$airline] ?? 'XX';
        return $prefix . rand(100, 999);
    }

    /**
     * Get aircraft type based on airline
     */
    private function getAircraftType($airline): string
    {
        $aircraftTypes = [
            'Boeing 777-300ER',
            'Airbus A330-300',
            'Boeing 787 Dreamliner',
            'Airbus A320',
            'Boeing 737-800',
            'Airbus A350-900',
        ];

        return $aircraftTypes[array_rand($aircraftTypes)];
    }

    /**
     * Get amenities based on airline
     */
    private function getAmenities($airline): array
    {
        $baseAmenities = [
            'In-flight Entertainment',
            'USB Charging Ports',
            'Wi-Fi Available',
            'Halal Meals',
            'Prayer Time Announcements',
            'Adjustable Headrests',
            'Reading Lights',
        ];

        $premiumAmenities = [
            'Lie-flat Seats (Business)',
            'Priority Boarding',
            'Extra Legroom',
            'Premium Blankets & Pillows',
            'Noise-canceling Headphones',
        ];

        $isPremium = in_array($airline, ['Qatar Airways', 'Emirates', 'Etihad Airways', 'Turkish Airlines']);

        return $isPremium 
            ? array_merge($baseAmenities, $premiumAmenities)
            : $baseAmenities;
    }

    /**
     * Generate flight description
     */
    private function generateDescription($departure, $arrival, $tripDays, $airline): string
    {
        $descriptions = [
            "Direct Umrah group flight from {$departure['city']} to {$arrival['city']}. Perfect for {$tripDays}-day Umrah packages with {$airline}.",
            "Convenient round-trip Umrah flight connecting {$departure['city']} with {$arrival['city']}. Ideal for group bookings and pilgrimage tours.",
            "Umrah charter flight operated by {$airline}. Comfortable {$tripDays}-day journey from {$departure['city']} to the holy cities.",
            "Group Umrah flight service from {$departure['city']} to {$arrival['city']}. Special rates for travel agents and tour operators.",
            "Exclusive Umrah flight package with {$airline}. Round-trip service spanning {$tripDays} days for your sacred journey.",
        ];

        return $descriptions[array_rand($descriptions)];
    }

    /**
     * Get baggage allowance
     */
    private function getBaggageAllowance($airline): array
    {
        return [
            'checked_baggage' => '30kg (2 pieces of 15kg each)',
            'cabin_baggage' => '7kg hand carry',
            'zamzam_allowance' => '5 liters Zamzam water (special container)',
            'extra_notes' => 'Additional baggage can be purchased. Zamzam containers provided at Jeddah airport.',
        ];
    }

    /**
     * Get included services
     */
    private function getIncludedServices($airline): array
    {
        $services = [
            'Complimentary meals and beverages',
            'In-flight entertainment system',
            'Travel insurance coverage',
            '30kg checked baggage allowance',
            'Zamzam water transportation (5L)',
            'Prayer time notifications',
            'Multilingual cabin crew',
        ];

        if (in_array($airline, ['Qatar Airways', 'Emirates', 'Etihad Airways', 'Turkish Airlines'])) {
            $services[] = 'Premium lounge access (Business class)';
            $services[] = 'Priority check-in and boarding';
            $services[] = 'Extra comfort amenities kit';
        }

        return $services;
    }

    /**
     * Get special requirements
     */
    private function getSpecialRequirements(): string
    {
        $requirements = [
            'Valid passport with minimum 6 months validity. Umrah visa must be obtained before travel. Women under 45 traveling without mahram require NOC.',
            'Passengers must have valid Umrah visa before departure. COVID-19 vaccination certificate may be required. Check latest travel advisories.',
            'Valid passport and Umrah visa mandatory. Meningitis vaccination required. Please arrive at airport 4 hours before departure.',
            'Umrah visa processing assistance available. All passengers must have travel insurance. Group coordinator required for 15+ passengers.',
        ];

        return $requirements[array_rand($requirements)];
    }
}
