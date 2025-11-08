<?php

use App\Http\Controllers\Api\HotelApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Hotel API Routes
|--------------------------------------------------------------------------
|
| These routes handle unified hotel search, details, and booking
| combining local provider hotels with Amadeus hotel inventory.
|
*/

// Wrap all hotel routes in v1 prefix to match the API structure
Route::prefix('v1')->group(function () {
    // Public hotel search and details
    Route::prefix('hotels')->group(function () {
        // Unified hotel search (local + Amadeus)
        Route::get('/search', [HotelApiController::class, 'search'])
            ->name('api.hotels.search');
        
        // Get hotel details by ID (local or Amadeus)
        Route::get('/{id}', [HotelApiController::class, 'show'])
            ->name('api.hotels.show');
    });

    // Protected routes - require authentication
    Route::middleware('auth:sanctum')->prefix('hotels')->group(function () {
        // Book a hotel (local or Amadeus)
        Route::post('/book', [HotelApiController::class, 'book'])
            ->name('api.hotels.book');
    });
});
