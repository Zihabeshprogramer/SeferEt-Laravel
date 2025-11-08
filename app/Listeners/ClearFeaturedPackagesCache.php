<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ClearFeaturedPackagesCache
{
    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        try {
            // Clear all featured packages cache keys
            $cacheKeys = [
                'b2c_featured_packages_4',
                'b2c_featured_packages_8',
                'b2c_featured_packages_12',
                'b2c_package_statistics',
            ];

            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }

            Log::info('Featured packages cache cleared successfully');
        } catch (\Exception $e) {
            Log::error('Failed to clear featured packages cache: ' . $e->getMessage());
        }
    }
}
