<?php

use App\Http\Controllers\FlightController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Flight API Routes
|--------------------------------------------------------------------------
|
| These routes handle Amadeus flight search and booking.
| Add these to your main routes/api.php file or include this file.
|
*/

// Flight search and booking endpoints
Route::prefix('flights')->group(function () {
    // Public routes - Search for flights and airports (guests can access)
    Route::get('/search', [FlightController::class, 'search']);
    Route::get('/search-batch', [FlightController::class, 'searchBatch']);
    Route::get('/airports', [FlightController::class, 'airports']);
    Route::get('/offer/{hash}', [FlightController::class, 'getOffer']);
    
    // Revalidate offer pricing (guests can access)
    Route::post('/revalidate', [FlightController::class, 'revalidate']);
    
    // Book a flight (guests can access - no authentication required)
    Route::post('/book', [FlightController::class, 'book']);
    
    // Protected routes - require authentication
    Route::middleware(['auth:sanctum'])->group(function () {
        // Get user's bookings
        Route::get('/bookings', [FlightController::class, 'getUserBookings']);
        
        // Get booking by PNR
        Route::get('/booking/{pnr}', [FlightController::class, 'getByPnr']);
    });
});
