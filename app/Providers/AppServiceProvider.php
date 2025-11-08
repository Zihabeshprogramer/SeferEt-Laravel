<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the BookingIntegrationService
        $this->app->singleton(\App\Services\BookingIntegrationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use Bootstrap 5 pagination views
        Paginator::useBootstrapFive();
        
        // Configure morph map for polymorphic relationships
        Relation::morphMap([
            'package' => \App\Models\Package::class,
            'hotel' => \App\Models\Hotel::class,
            'flight' => \App\Models\Flight::class,
            'offer' => \App\Models\Offer::class,
            'vehicle' => \App\Models\Vehicle::class,
            'user' => \App\Models\User::class,
        ]);
    }
}
