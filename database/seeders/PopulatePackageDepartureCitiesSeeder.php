<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PopulatePackageDepartureCitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // International departure cities including Turkey, Africa, Middle East, Europe, and more
        $departureCities = [
            ['Istanbul', 'Ankara'],
            ['Istanbul', 'Addis Ababa'],
            ['Ankara', 'Izmir'],
            ['Istanbul', 'Antalya', 'Dubai'],
            ['Izmir', 'Cairo'],
            ['Antalya', 'Riyadh'],
            ['Istanbul', 'Ankara', 'Izmir'],
            ['Bursa', 'Istanbul', 'London'],
            ['Gaziantep', 'Jeddah'],
            ['Kayseri', 'Ankara', 'Addis Ababa'],
            ['Dubai', 'Abu Dhabi'],
            ['Addis Ababa', 'Nairobi'],
            ['Cairo', 'Istanbul'],
            ['Doha', 'Kuwait City'],
            ['London', 'Paris'],
        ];

        $packages = Package::all();
        
        if ($packages->isEmpty()) {
            $this->command->info('No packages found to update.');
            return;
        }

        $this->command->info("Updating departure cities for {$packages->count()} packages...");
        
        $updated = 0;
        foreach ($packages as $index => $package) {
            // Assign departure cities in a round-robin fashion (force update all)
            $citySet = $departureCities[$index % count($departureCities)];
            
            $package->departure_cities = $citySet;
            $package->save();
            
            $updated++;
            $this->command->info("Updated Package #{$package->id} ({$package->name}) with departure cities: " . implode(', ', $citySet));
        }

        $this->command->info("\n✅ Successfully updated {$updated} packages with departure cities!");
    }
}
