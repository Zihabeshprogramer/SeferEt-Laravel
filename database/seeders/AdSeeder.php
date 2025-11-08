<?php

namespace Database\Seeders;

use App\Models\Ad;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have B2B users and an admin
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $admin = User::factory()->create([
                'role' => 'admin',
                'name' => 'Admin User',
                'email' => 'admin@seferet.com',
            ]);
        }

        // Get or create partner users (B2B users)
        $b2bUsers = User::where('role', 'partner')->limit(5)->get();
        if ($b2bUsers->count() < 5) {
            $b2bUsers = User::factory()
                ->partner()
                ->count(5 - $b2bUsers->count())
                ->create();
        }

        // Create diverse set of ads for testing different scenarios
        
        // 1. Pending ads awaiting approval (8 ads)
        echo "Creating pending ads...\n";
        foreach ($b2bUsers as $index => $user) {
            Ad::factory()
                ->pending()
                ->for($user, 'owner')
                ->create([
                    'title' => "Pending Ad #{$index} - {$user->name}",
                    'placement' => ['home_banner', 'product_list', 'product_detail'][$index % 3],
                ]);
        }

        Ad::factory()
            ->count(3)
            ->pending()
            ->for($b2bUsers->random(), 'owner')
            ->create();

        // 2. Approved and active ads (15 ads)
        echo "Creating active ads...\n";
        Ad::factory()
            ->count(10)
            ->active()
            ->for($b2bUsers->random(), 'owner')
            ->create();

        // High-performance ads
        Ad::factory()
            ->count(3)
            ->highPerformance()
            ->for($b2bUsers->random(), 'owner')
            ->create();

        // Local owner ads with priority
        Ad::factory()
            ->count(2)
            ->active()
            ->localOwner()
            ->for($b2bUsers->random(), 'owner')
            ->create();

        // 3. Draft ads (5 ads)
        echo "Creating draft ads...\n";
        Ad::factory()
            ->count(5)
            ->draft()
            ->for($b2bUsers->random(), 'owner')
            ->create();

        // 4. Rejected ads (4 ads)
        echo "Creating rejected ads...\n";
        Ad::factory()
            ->count(4)
            ->rejected()
            ->for($b2bUsers->random(), 'owner')
            ->create();

        // 5. Inactive approved ads (3 ads)
        echo "Creating inactive ads...\n";
        Ad::factory()
            ->count(3)
            ->inactive()
            ->for($b2bUsers->random(), 'owner')
            ->create();

        // 6. Scheduled ads (future start date) (4 ads)
        echo "Creating scheduled ads...\n";
        Ad::factory()
            ->count(4)
            ->scheduled()
            ->approved()
            ->for($b2bUsers->random(), 'owner')
            ->create();

        // 7. Expired ads (3 ads)
        echo "Creating expired ads...\n";
        Ad::factory()
            ->count(3)
            ->expired()
            ->for($b2bUsers->random(), 'owner')
            ->create();

        // 8. Device-specific ads
        echo "Creating device-specific ads...\n";
        Ad::factory()
            ->count(2)
            ->active()
            ->mobile()
            ->for($b2bUsers->random(), 'owner')
            ->create(['title' => 'Mobile-Only Ad']);

        Ad::factory()
            ->count(2)
            ->active()
            ->desktop()
            ->for($b2bUsers->random(), 'owner')
            ->create(['title' => 'Desktop-Only Ad']);

        // 9. Placement-specific ads
        echo "Creating placement-specific ads...\n";
        Ad::factory()
            ->active()
            ->homeBanner()
            ->for($b2bUsers->random(), 'owner')
            ->create(['title' => 'Premium Home Banner Ad']);

        Ad::factory()
            ->active()
            ->for($b2bUsers->random(), 'owner')
            ->create([
                'title' => 'Product Detail Page Ad',
                'placement' => 'product_detail',
            ]);

        Ad::factory()
            ->active()
            ->for($b2bUsers->random(), 'owner')
            ->create([
                'title' => 'Search Results Ad',
                'placement' => 'search_results',
            ]);

        echo "\nAd seeding completed!\n";
        echo "Summary:\n";
        echo "- Total ads: " . Ad::count() . "\n";
        echo "- Pending: " . Ad::pending()->count() . "\n";
        echo "- Approved: " . Ad::approved()->count() . "\n";
        echo "- Active: " . Ad::active()->count() . "\n";
        echo "- Draft: " . Ad::where('status', 'draft')->count() . "\n";
        echo "- Rejected: " . Ad::rejected()->count() . "\n";
    }
}
