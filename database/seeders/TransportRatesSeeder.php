<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TransportService;
use App\Models\TransportRate;
use App\Models\TransportPricingRule;
use App\Models\User;
use Carbon\Carbon;

class TransportRatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find or create a transport provider user
        $provider = User::where('role', 'transport_provider')->first();
        
        if (!$provider) {
            $provider = User::create([
                'name' => 'Test Transport Provider',
                'email' => 'transport@test.com',
                'password' => bcrypt('password'),
                'role' => 'transport_provider',
                'status' => 'active',
                'company_name' => 'Test Transport Company',
            ]);
        }

        // Create sample transport services
        $busService = TransportService::create([
            'provider_id' => $provider->id,
            'service_name' => 'City Bus Service',
            'transport_type' => 'bus',
            'price' => 50.00,
            'routes' => [
                [
                    'from' => 'Riyadh',
                    'to' => 'Jeddah',
                    'duration' => '8 hours',
                    'distance' => '950 km'
                ],
                [
                    'from' => 'Riyadh',
                    'to' => 'Dammam',
                    'duration' => '4 hours',
                    'distance' => '400 km'
                ],
                [
                    'from' => 'Jeddah',
                    'to' => 'Mecca',
                    'duration' => '1.5 hours',
                    'distance' => '80 km'
                ]
            ],
            'max_passengers' => 50,
            'is_active' => true,
        ]);

        $taxiService = TransportService::create([
            'provider_id' => $provider->id,
            'service_name' => 'Premium Taxi Service',
            'transport_type' => 'taxi',
            'price' => 25.00,
            'routes' => [
                [
                    'from' => 'Riyadh Airport',
                    'to' => 'Downtown Riyadh',
                    'duration' => '45 minutes',
                    'distance' => '35 km'
                ],
                [
                    'from' => 'Riyadh Airport',
                    'to' => 'King Saud University',
                    'duration' => '30 minutes',
                    'distance' => '25 km'
                ]
            ],
            'max_passengers' => 4,
            'is_active' => true,
        ]);

        // Create sample rates for the next 30 days
        $passengerTypes = ['adult', 'child', 'infant'];
        $currencies = ['USD', 'SAR'];
        
        // Bus service rates
        foreach ($busService->routes as $route) {
            foreach ($passengerTypes as $passengerType) {
                for ($i = 0; $i < 30; $i++) {
                    $date = Carbon::now()->addDays($i)->format('Y-m-d');
                    
                    // Vary rates based on passenger type and day
                    $baseRate = $busService->price;
                    if ($passengerType === 'child') {
                        $baseRate *= 0.7; // 30% discount for children
                    } elseif ($passengerType === 'infant') {
                        $baseRate *= 0.5; // 50% discount for infants
                    }
                    
                    // Weekend premium
                    $dayOfWeek = Carbon::parse($date)->dayOfWeek;
                    if (in_array($dayOfWeek, [5, 6])) { // Friday, Saturday
                        $baseRate *= 1.2; // 20% weekend premium
                    }
                    
                    TransportRate::create([
                        'transport_service_id' => $busService->id,
                        'provider_id' => $provider->id,
                        'date' => $date,
                        'route_from' => $route['from'],
                        'route_to' => $route['to'],
                        'passenger_type' => $passengerType,
                        'base_rate' => round($baseRate, 2),
                        'currency' => 'SAR',
                        'notes' => $i < 7 ? 'Early booking rate' : null,
                        'is_available' => true,
                    ]);
                }
            }
        }

        // Taxi service rates
        foreach ($taxiService->routes as $route) {
            foreach ($passengerTypes as $passengerType) {
                for ($i = 0; $i < 30; $i++) {
                    $date = Carbon::now()->addDays($i)->format('Y-m-d');
                    
                    // Vary rates based on passenger type
                    $baseRate = $taxiService->price;
                    if ($passengerType === 'child') {
                        $baseRate *= 0.8; // 20% discount for children
                    } elseif ($passengerType === 'infant') {
                        $baseRate *= 0.3; // 70% discount for infants
                    }
                    
                    // Peak hour rates (simulate higher rates on certain days)
                    if ($i % 3 === 0) {
                        $baseRate *= 1.5; // Peak hour premium
                    }
                    
                    TransportRate::create([
                        'transport_service_id' => $taxiService->id,
                        'provider_id' => $provider->id,
                        'date' => $date,
                        'route_from' => $route['from'],
                        'route_to' => $route['to'],
                        'passenger_type' => $passengerType,
                        'base_rate' => round($baseRate, 2),
                        'currency' => 'SAR',
                        'notes' => $i % 3 === 0 ? 'Peak hour rate' : null,
                        'is_available' => true,
                    ]);
                }
            }
        }

        // Create sample pricing rules
        TransportPricingRule::create([
            'transport_service_id' => $busService->id,
            'provider_id' => $provider->id,
            'rule_name' => 'Weekend Premium',
            'description' => 'Higher rates on weekends',
            'rule_type' => 'day_of_week',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addYear()->format('Y-m-d'),
            'adjustment_type' => 'percentage',
            'adjustment_value' => 25.0,
            'days_of_week' => ['friday', 'saturday'],
            'priority' => 10,
            'is_active' => true,
        ]);

        TransportPricingRule::create([
            'transport_service_id' => $busService->id,
            'provider_id' => $provider->id,
            'rule_name' => 'Group Discount',
            'description' => 'Discount for groups of 5 or more passengers',
            'rule_type' => 'passenger_count',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addYear()->format('Y-m-d'),
            'adjustment_type' => 'percentage',
            'adjustment_value' => -15.0,
            'min_passengers' => 5,
            'priority' => 15,
            'is_active' => true,
        ]);

        TransportPricingRule::create([
            'transport_service_id' => $taxiService->id,
            'provider_id' => $provider->id,
            'rule_name' => 'Airport Premium',
            'description' => 'Premium for airport routes',
            'rule_type' => 'route_specific',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addYear()->format('Y-m-d'),
            'adjustment_type' => 'fixed',
            'adjustment_value' => 10.0,
            'applicable_routes' => [
                ['from' => 'Riyadh Airport', 'to' => 'Downtown Riyadh'],
                ['from' => 'Riyadh Airport', 'to' => 'King Saud University']
            ],
            'priority' => 5,
            'is_active' => true,
        ]);

        $this->command->info('Transport rates and pricing rules seeded successfully!');
        $this->command->info('Provider email: ' . $provider->email);
        $this->command->info('Password: password');
    }
}
