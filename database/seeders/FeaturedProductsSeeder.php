<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;
use App\Models\Flight;
use App\Models\Hotel;
use App\Models\User;
use App\Models\FeaturedRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FeaturedProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌟 Starting Featured Products Seeder...');
        
        // Get admin user for approvals
        $admin = User::where('role', 'admin')->first();
        
        if (!$admin) {
            $this->command->error('❌ No admin user found. Please create an admin user first.');
            return;
        }
        
        $this->command->info("✓ Using admin: {$admin->name} (ID: {$admin->id})");
        
        DB::beginTransaction();
        
        try {
            // Clear existing featured data
            $this->clearExistingFeatured();
            
            // Feature diverse products
            $featuredPackages = $this->featurePackages($admin);
            $featuredFlights = $this->featureFlights($admin);
            $featuredHotels = $this->featureHotels($admin);
            
            DB::commit();
            
            // Summary
            $this->command->newLine();
            $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->command->info('✅ Featured Products Seeder Complete!');
            $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->command->table(
                ['Product Type', 'Featured Count'],
                [
                    ['Packages', $featuredPackages],
                    ['Flights', $featuredFlights],
                    ['Hotels', $featuredHotels],
                    ['TOTAL', $featuredPackages + $featuredFlights + $featuredHotels],
                ]
            );
            $this->command->newLine();
            $this->command->info('🔗 View at: /admin/featured/requests');
            $this->command->info('🏠 Check home page: /');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error: ' . $e->getMessage());
            Log::error('Featured Products Seeder Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    /**
     * Clear existing featured data
     */
    protected function clearExistingFeatured(): void
    {
        $this->command->warn('🧹 Clearing existing featured data...');
        
        // Unfeature all products
        Package::where('is_featured', true)->update([
            'is_featured' => false,
            'featured_at' => null,
            'featured_expires_at' => null,
        ]);
        
        Flight::where('is_featured', true)->update([
            'is_featured' => false,
            'featured_at' => null,
            'featured_expires_at' => null,
        ]);
        
        Hotel::where('is_featured', true)->update([
            'is_featured' => false,
            'featured_at' => null,
            'featured_expires_at' => null,
        ]);
        
        // Clear featured requests (optional - keeps history if commented)
        // FeaturedRequest::truncate();
        
        $this->command->info('✓ Cleared existing featured data');
    }
    
    /**
     * Feature selected packages
     */
    protected function featurePackages(User $admin): int
    {
        $this->command->info('📦 Featuring Umrah Packages...');
        
        // Get approved, active packages
        $packages = Package::where('status', 'active')
            ->where('approval_status', 'approved')
            ->whereNotNull('base_price')
            ->where('base_price', '>', 0)
            ->get();
        
        if ($packages->isEmpty()) {
            $this->command->warn('⚠️  No packages found to feature');
            return 0;
        }
        
        // Select diverse packages (different types, prices)
        $selectedPackages = $this->selectDiversePackages($packages, 6);
        
        $count = 0;
        foreach ($selectedPackages as $index => $package) {
            $priorityLevel = 10 - $index; // Higher priority for first items
            $daysToFeature = [30, 60, 90, 120, null, null][$index] ?? 30; // Some with no expiry
            
            $this->featureProduct(
                $package,
                'package',
                $admin,
                $priorityLevel,
                $daysToFeature
            );
            
            $count++;
            $this->command->info("  ✓ Featured: {$package->name} (Priority: {$priorityLevel})");
        }
        
        return $count;
    }
    
    /**
     * Feature selected flights
     */
    protected function featureFlights(User $admin): int
    {
        $this->command->info('✈️  Featuring Umrah Flights...');
        
        // Get active flights with available seats
        $flights = Flight::where('is_active', true)
            ->where('status', 'scheduled')
            ->where('available_seats', '>', 0)
            ->where('departure_datetime', '>', now())
            ->get();
        
        if ($flights->isEmpty()) {
            $this->command->warn('⚠️  No flights found to feature');
            return 0;
        }
        
        // Prioritize flights to Jeddah (JED) - gateway to Makkah
        $umrahFlights = $flights->filter(function($flight) {
            return stripos($flight->arrival_airport, 'JED') !== false ||
                   stripos($flight->arrival_airport, 'Jeddah') !== false ||
                   stripos($flight->arrival_airport, 'MED') !== false ||
                   stripos($flight->arrival_airport, 'Madinah') !== false;
        });
        
        // If no specific Umrah flights, use general flights
        $selectedFlights = $umrahFlights->take(4);
        if ($selectedFlights->count() < 4) {
            $selectedFlights = $flights->take(4);
        }
        
        $count = 0;
        foreach ($selectedFlights as $index => $flight) {
            $priorityLevel = 8 - $index;
            $daysToFeature = [45, 60, 75, null][$index] ?? 45;
            
            $this->featureProduct(
                $flight,
                'flight',
                $admin,
                $priorityLevel,
                $daysToFeature
            );
            
            $count++;
            $route = $flight->departure_airport . ' → ' . $flight->arrival_airport;
            $this->command->info("  ✓ Featured: {$route} ({$flight->airline})");
        }
        
        return $count;
    }
    
    /**
     * Feature selected hotels
     */
    protected function featureHotels(User $admin): int
    {
        $this->command->info('🏨 Featuring Umrah Hotels...');
        
        // Get active hotels
        $hotels = Hotel::where('status', 'active')
            ->where('is_active', true)
            ->get();
        
        if ($hotels->isEmpty()) {
            $this->command->warn('⚠️  No hotels found to feature');
            return 0;
        }
        
        // Prioritize hotels near Haram
        $umrahHotels = $hotels->filter(function($hotel) {
            $city = strtolower($hotel->city ?? '');
            return stripos($city, 'makkah') !== false ||
                   stripos($city, 'mecca') !== false ||
                   stripos($city, 'madinah') !== false ||
                   stripos($city, 'medina') !== false ||
                   ($hotel->distance_to_haram !== null && $hotel->distance_to_haram < 2000);
        });
        
        // Select diverse hotels by star rating
        $selectedHotels = $this->selectDiverseHotels($umrahHotels->count() > 0 ? $umrahHotels : $hotels, 5);
        
        $count = 0;
        foreach ($selectedHotels as $index => $hotel) {
            $priorityLevel = 7 - $index;
            $daysToFeature = [60, 90, 120, null, null][$index] ?? 60;
            
            $this->featureProduct(
                $hotel,
                'hotel',
                $admin,
                $priorityLevel,
                $daysToFeature
            );
            
            $count++;
            $stars = str_repeat('⭐', $hotel->star_rating ?? 3);
            $this->command->info("  ✓ Featured: {$hotel->name} {$stars}");
        }
        
        return $count;
    }
    
    /**
     * Feature a product with featured request
     */
    protected function featureProduct($product, string $productType, User $admin, int $priority, ?int $daysToFeature): void
    {
        $now = Carbon::now();
        $expiresAt = $daysToFeature ? $now->copy()->addDays($daysToFeature) : null;
        
        // Get product owner for the request
        $requestedBy = $this->getProductOwner($product, $productType) ?? $admin->id;
        
        // Create featured request (approved)
        FeaturedRequest::create([
            'product_id' => $product->id,
            'product_type' => $productType,
            'requested_by' => $requestedBy,
            'approved_by' => $admin->id,
            'status' => FeaturedRequest::STATUS_APPROVED,
            'approved_at' => $now,
            'priority_level' => $priority,
            'start_date' => $now->toDateString(),
            'end_date' => $expiresAt?->toDateString(),
            'notes' => 'Featured via seeder - Premium Umrah offering',
        ]);
        
        // Update product
        $product->update([
            'is_featured' => true,
            'featured_at' => $now,
            'featured_expires_at' => $expiresAt,
        ]);
    }
    
    /**
     * Get product owner ID
     */
    protected function getProductOwner($product, string $productType): ?int
    {
        return match($productType) {
            'flight' => $product->provider_id ?? null,
            'hotel' => $product->provider_id ?? null,
            'package' => $product->creator_id ?? null,
            default => null,
        };
    }
    
    /**
     * Select diverse packages by type and price
     */
    protected function selectDiversePackages($packages, int $limit)
    {
        $selected = collect();
        
        // Try to get different types
        $types = ['religious', 'luxury', 'family', 'budget', 'premium'];
        
        foreach ($types as $type) {
            $package = $packages->where('type', $type)->first();
            if ($package && !$selected->contains($package)) {
                $selected->push($package);
                if ($selected->count() >= $limit) break;
            }
        }
        
        // Fill remaining with highest rated/most booked
        if ($selected->count() < $limit) {
            $remaining = $packages->diff($selected)
                ->sortByDesc(function($p) {
                    return ($p->average_rating ?? 0) * 100 + ($p->bookings_count ?? 0);
                })
                ->take($limit - $selected->count());
            
            $selected = $selected->merge($remaining);
        }
        
        return $selected->take($limit);
    }
    
    /**
     * Select diverse hotels by star rating
     */
    protected function selectDiverseHotels($hotels, int $limit)
    {
        $selected = collect();
        
        // Get one of each star rating (5 to 3)
        for ($stars = 5; $stars >= 3; $stars--) {
            $hotel = $hotels->where('star_rating', $stars)->first();
            if ($hotel && !$selected->contains($hotel)) {
                $selected->push($hotel);
                if ($selected->count() >= $limit) break;
            }
        }
        
        // Fill remaining with best rated
        if ($selected->count() < $limit) {
            $remaining = $hotels->diff($selected)
                ->sortByDesc('star_rating')
                ->take($limit - $selected->count());
            
            $selected = $selected->merge($remaining);
        }
        
        return $selected->take($limit);
    }
}
