<?php

use App\Http\Controllers\Api\FeatureRequestController;
use App\Http\Controllers\Api\Admin\AdminFeatureController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Featured Products API Routes
|--------------------------------------------------------------------------
|
| Routes for managing featured product requests and admin approval
|
*/

// Provider/Agent Feature Request Routes
Route::middleware(['auth:sanctum'])->prefix('feature')->name('feature.')->group(function () {
    // List user's feature requests
    Route::get('/requests', [FeatureRequestController::class, 'index'])->name('requests.index');
    
    // Create new feature request
    Route::post('/request', [FeatureRequestController::class, 'store'])->name('requests.store');
    
    // View specific feature request
    Route::get('/requests/{featuredRequest}', [FeatureRequestController::class, 'show'])->name('requests.show');
    
    // Cancel pending feature request
    Route::delete('/requests/{featuredRequest}', [FeatureRequestController::class, 'destroy'])->name('requests.destroy');
    
    // Get feature status for a product
    Route::get('/status/{product_type}/{product_id}', [FeatureRequestController::class, 'status'])->name('status');
});

// Admin Feature Management Routes
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin/feature')->name('admin.feature.')->group(function () {
    // View all feature requests
    Route::get('/requests', [AdminFeatureController::class, 'requests'])->name('requests');
    
    // Approve feature request
    Route::post('/approve/{featuredRequest}', [AdminFeatureController::class, 'approve'])->name('approve');
    
    // Reject feature request
    Route::post('/reject/{featuredRequest}', [AdminFeatureController::class, 'reject'])->name('reject');
    
    // Manually feature a product
    Route::post('/manual', [AdminFeatureController::class, 'manualFeature'])->name('manual');
    
    // Unfeature a product
    Route::post('/unfeature', [AdminFeatureController::class, 'unfeature'])->name('unfeature');
    
    // Get all featured products
    Route::get('/featured', [AdminFeatureController::class, 'featured'])->name('featured');
    
    // Get statistics
    Route::get('/statistics', [AdminFeatureController::class, 'statistics'])->name('statistics');
    
    // Update priority
    Route::patch('/priority/{featuredRequest}', [AdminFeatureController::class, 'updatePriority'])->name('priority');
});
