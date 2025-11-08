<?php

namespace App\Console\Commands;

use App\Models\Flight;
use App\Models\Hotel;
use App\Models\Package;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UnfeatureExpiredProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'featured:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unfeature products that have expired featured periods';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting featured products cleanup...');

        $totalUnfeatured = 0;

        // Unfeature expired flights
        $flightsUnfeatured = $this->unfeatureExpiredFlights();
        $totalUnfeatured += $flightsUnfeatured;
        $this->info("Unfeatured {$flightsUnfeatured} expired flights.");

        // Unfeature expired hotels
        $hotelsUnfeatured = $this->unfeatureExpiredHotels();
        $totalUnfeatured += $hotelsUnfeatured;
        $this->info("Unfeatured {$hotelsUnfeatured} expired hotels.");

        // Unfeature expired packages
        $packagesUnfeatured = $this->unfeatureExpiredPackages();
        $totalUnfeatured += $packagesUnfeatured;
        $this->info("Unfeatured {$packagesUnfeatured} expired packages.");

        $this->info("Cleanup complete. Total products unfeatured: {$totalUnfeatured}");

        Log::info('Featured products cleanup completed', [
            'flights' => $flightsUnfeatured,
            'hotels' => $hotelsUnfeatured,
            'packages' => $packagesUnfeatured,
            'total' => $totalUnfeatured,
        ]);

        return 0;
    }

    /**
     * Unfeature expired flights.
     */
    private function unfeatureExpiredFlights(): int
    {
        return Flight::where('is_featured', true)
            ->whereNotNull('featured_expires_at')
            ->where('featured_expires_at', '<', now())
            ->update([
                'is_featured' => false,
                'featured_at' => null,
                'featured_expires_at' => null,
            ]);
    }

    /**
     * Unfeature expired hotels.
     */
    private function unfeatureExpiredHotels(): int
    {
        return Hotel::where('is_featured', true)
            ->whereNotNull('featured_expires_at')
            ->where('featured_expires_at', '<', now())
            ->update([
                'is_featured' => false,
                'featured_at' => null,
                'featured_expires_at' => null,
            ]);
    }

    /**
     * Unfeature expired packages.
     */
    private function unfeatureExpiredPackages(): int
    {
        return Package::where('is_featured', true)
            ->whereNotNull('featured_expires_at')
            ->where('featured_expires_at', '<', now())
            ->update([
                'is_featured' => false,
                'featured_at' => null,
                'featured_expires_at' => null,
            ]);
    }
}
