<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;

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
    
    // Common authenticated routes (all roles)
    Route::get('/profile', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ]);
    });
});

// Fallback route for API
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found',
    ], 404);
});
