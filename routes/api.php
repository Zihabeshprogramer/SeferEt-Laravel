<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ServiceDiscoveryController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\ServiceRequestController;
use App\Http\Controllers\Api\PriceLookupController;
use App\Http\Controllers\Api\FavoritesController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no authentication required)
Route::prefix('v1')->group(function () {
    // Authentication routes
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    
    // Health check
    Route::get('/health', function () {
        return response()->json([
            'success' => true,
            'message' => 'SeferEt API is running',
            'version' => 'v1',
            'timestamp' => now()->toISOString(),
        ]);
    });
    
    // Home screen endpoints (public)
    Route::get('/featured/products', 'App\Http\Controllers\Api\V1\HomeController@featuredProducts');
    Route::get('/recommendations', 'App\Http\Controllers\Api\V1\HomeController@recommendations');
    Route::get('/popular', 'App\Http\Controllers\Api\V1\HomeController@popular');
    
    // Public Package Routes (accessible without authentication)
    Route::prefix('packages')->group(function () {
        Route::get('/', 'App\\Http\\Controllers\\Api\\V1\\PackageController@index');
        Route::get('/search', 'App\\Http\\Controllers\\Api\\V1\\PackageController@search');
        Route::get('/featured', 'App\\Http\\Controllers\\Api\\V1\\PackageController@featured');
        Route::get('/categories', 'App\\Http\\Controllers\\Api\\V1\\PackageController@categories');
        Route::get('/{package}', 'App\\Http\\Controllers\\Api\\V1\\PackageController@show');
    });
    
    // Public Ad Serving Routes (accessible without authentication)
    Route::prefix('ads')->group(function () {
        // Serve ads based on context
        Route::get('/serve', [\App\Http\Controllers\Api\AdServingController::class, 'serveAds']);
        Route::get('/serve/{id}', [\App\Http\Controllers\Api\AdServingController::class, 'getAd']);
        
        // Track impressions and clicks (no auth required for tracking)
        Route::post('/{id}/track/impression', [\App\Http\Controllers\Api\AdTrackingController::class, 'trackImpression'])->name('api.ads.track.impression');
        Route::post('/{id}/track/click', [\App\Http\Controllers\Api\AdTrackingController::class, 'trackClick'])->name('api.ads.track.click');
        Route::post('/{adId}/track/conversion/{clickId}', [\App\Http\Controllers\Api\AdTrackingController::class, 'trackConversion']);
        
        // Batch tracking for performance
        Route::post('/track/impressions/batch', [\App\Http\Controllers\Api\AdTrackingController::class, 'batchTrackImpressions']);
        
        // Legacy route
        Route::get('/active', 'App\\Http\\Controllers\\Api\\AdController@active');
    });
});

// Protected routes (authentication required)
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    // Authentication routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    
    // Customer routes
    Route::middleware(['role:customer'])->prefix('customer')->group(function () {
        // Customer-specific routes will go here
        Route::get('/dashboard', function () {
            return response()->json([
                'success' => true,
                'message' => 'Customer dashboard data',
                'data' => [
                    'user' => auth()->user(),
                    'bookings' => [], // Placeholder
                    'packages' => [], // Placeholder
                ],
            ]);
        });
    });
    
    // Partner routes
    Route::middleware(['role:partner'])->prefix('partner')->group(function () {
        // Partner-specific routes will go here
        Route::get('/dashboard', function () {
            return response()->json([
                'success' => true,
                'message' => 'Partner dashboard data',
                'data' => [
                    'user' => auth()->user(),
                    'packages' => [], // Placeholder
                    'bookings' => [], // Placeholder
                    'revenue' => [], // Placeholder
                ],
            ]);
        });
    });
    
    // Admin routes
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        // Admin-specific routes will go here
        Route::get('/dashboard', function () {
            return response()->json([
                'success' => true,
                'message' => 'Admin dashboard data',
                'data' => [
                    'user' => auth()->user(),
                    'users' => [], // Placeholder
                    'partners' => [], // Placeholder
                    'bookings' => [], // Placeholder
                    'revenue' => [], // Placeholder
                    'analytics' => [], // Placeholder
                ],
            ]);
        });
    });
    
    // B2B Service Discovery Routes (accessible to partners and service providers)
    Route::prefix('b2b')->group(function () {
        Route::get('/service-offers', [ServiceDiscoveryController::class, 'getServiceOffers']);
        Route::get('/service-offers/{serviceOffer}', [ServiceDiscoveryController::class, 'getServiceOffer']);
        Route::get('/hotel-services', [ServiceDiscoveryController::class, 'getHotelServices']);
        Route::get('/transport-services', [ServiceDiscoveryController::class, 'getTransportServices']);
        Route::get('/service-providers', [ServiceDiscoveryController::class, 'getServiceProviders']);
    });
    
    // Service Request Management
    Route::prefix('service-requests')->group(function () {
        // Create service request (agents only)
        Route::post('/', [ServiceRequestController::class, 'store']);
        
        // Get service requests for current user
        Route::get('/agent', [ServiceRequestController::class, 'agentIndex']);
        Route::get('/provider', [ServiceRequestController::class, 'providerIndex']);
        
        // Individual service request operations
        Route::get('/{id}', [ServiceRequestController::class, 'show']);
        Route::put('/{id}/approve', [ServiceRequestController::class, 'approve']);
        Route::put('/{id}/reject', [ServiceRequestController::class, 'reject']);
        Route::put('/{id}/cancel', [ServiceRequestController::class, 'cancel']);
        
        // Package service request status
        Route::get('/package/{packageId}/status', [ServiceRequestController::class, 'packageStatus']);
    });
    
    // Package approval status (legacy)
    Route::get('/packages/{id}/approval-status', [ServiceRequestController::class, 'packageApprovalStatus']);
    
    // Price Lookup API Routes
    Route::prefix('pricing')->group(function () {
        // Get base price for a service
        Route::post('/base-price', [PriceLookupController::class, 'getBasePrice']);
        
        // Get price for a specific service request
        Route::get('/service-request/{serviceRequestId}', [PriceLookupController::class, 'getServiceRequestPrice']);
        
        // Get price calculation for date range
        Route::post('/date-range', [PriceLookupController::class, 'getPriceForDateRange']);
        
        // Batch price lookup
        Route::post('/batch', [PriceLookupController::class, 'getBatchPrices']);
    });
    
    // Favorites routes (all authenticated users)
    Route::prefix('favorites')->group(function () {
        Route::get('/', [FavoritesController::class, 'index']);
        Route::post('/', [FavoritesController::class, 'store']);
        Route::get('/counts', [FavoritesController::class, 'counts']);
        Route::post('/check', [FavoritesController::class, 'check']);
        Route::delete('/multiple', [FavoritesController::class, 'destroyMultiple']);
        Route::get('/{id}', [FavoritesController::class, 'show']);
        Route::put('/{id}', [FavoritesController::class, 'update']);
        Route::delete('/{id}', [FavoritesController::class, 'destroy']);
    });
    
    // Ads Management Routes (B2B users and admins)
    Route::prefix('ads')->group(function () {
        // List and retrieve ads
        Route::get('/', 'App\\Http\\Controllers\\Api\\AdController@index');
        Route::get('/{ad}', 'App\\Http\\Controllers\\Api\\AdController@show');
        
        // Create and manage ads (B2B users)
        Route::post('/', 'App\\Http\\Controllers\\Api\\AdController@store');
        Route::put('/{ad}', 'App\\Http\\Controllers\\Api\\AdController@update');
        Route::delete('/{ad}', 'App\\Http\\Controllers\\Api\\AdController@destroy');
        
        // Image upload
        Route::post('/{ad}/upload-image', 'App\\Http\\Controllers\\Api\\AdController@uploadImage');
        
        // Workflow actions
        Route::post('/{ad}/submit', 'App\\Http\\Controllers\\Api\\AdController@submit');
        Route::post('/{ad}/withdraw', 'App\\Http\\Controllers\\Api\\AdController@withdraw');
        Route::post('/{ad}/toggle-active', 'App\\Http\\Controllers\\Api\\AdController@toggleActive');
        
        // Admin approval actions
        Route::post('/{ad}/approve', 'App\\Http\\Controllers\\Api\\AdController@approve');
        Route::post('/{ad}/reject', 'App\\Http\\Controllers\\Api\\AdController@reject');
        
        // Audit logs
        Route::get('/{ad}/audit-logs', 'App\\Http\\Controllers\\Api\\AdController@auditLogs');
    });
    
    // Common authenticated routes (all roles)
    Route::get('/profile', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ]);
    });
});


/*
|--------------------------------------------------------------------------
| Amadeus Routes - Load from separate files
|--------------------------------------------------------------------------
*/
// Load api flights routes from dedicated file
require __DIR__.'/api_flights.php';

// Load api hotels routes from dedicated file
require __DIR__.'/api_hotels.php';

// City search autocomplete (public)
Route::get('/cities/search', [App\Http\Controllers\Api\CitySearchController::class, 'search'])
    ->name('api.cities.search');

// Fallback route for API
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found',
    ], 404);
});
