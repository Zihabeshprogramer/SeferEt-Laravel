<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\B2C\HomeController as B2CHomeController;
use App\Http\Controllers\Web\B2B\DashboardController as B2BDashboardController;
use App\Http\Controllers\Web\Admin\DashboardController as AdminDashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// B2C Customer Website Routes
Route::prefix('/')->name('b2c.')->group(function () {
    // Public routes
    Route::get('/', [B2CHomeController::class, 'index'])->name('home');
    Route::get('/packages', [B2CHomeController::class, 'packages'])->name('packages');
    Route::get('/packages/{id}', [B2CHomeController::class, 'packageDetails'])->name('package.details');
    Route::get('/about', [B2CHomeController::class, 'about'])->name('about');
    Route::get('/contact', [B2CHomeController::class, 'contact'])->name('contact');
    
    // Authentication routes for web
    Route::get('/login', [B2CHomeController::class, 'login'])->name('login');
    Route::get('/register', [B2CHomeController::class, 'register'])->name('register');
    
    // Protected customer routes
    Route::middleware(['auth:web', 'role:customer'])->group(function () {
        Route::get('/dashboard', [B2CHomeController::class, 'dashboard'])->name('dashboard');
        Route::get('/bookings', [B2CHomeController::class, 'bookings'])->name('bookings');
        Route::get('/profile', [B2CHomeController::class, 'profile'])->name('profile');
    });
});

// B2B Partner Portal Routes
Route::prefix('partner')->name('b2b.')->group(function () {
    // Partner authentication
    Route::get('/login', [B2BDashboardController::class, 'login'])->name('login');
    
    // Protected partner routes
    Route::middleware(['auth:web', 'role:partner'])->group(function () {
        Route::get('/dashboard', [B2BDashboardController::class, 'index'])->name('dashboard');
        Route::get('/packages', [B2BDashboardController::class, 'packages'])->name('packages');
        Route::get('/packages/create', [B2BDashboardController::class, 'createPackage'])->name('packages.create');
        Route::get('/packages/{id}/edit', [B2BDashboardController::class, 'editPackage'])->name('packages.edit');
        Route::get('/bookings', [B2BDashboardController::class, 'bookings'])->name('bookings');
        Route::get('/analytics', [B2BDashboardController::class, 'analytics'])->name('analytics');
        Route::get('/profile', [B2BDashboardController::class, 'profile'])->name('profile');
    });
});

// Admin Dashboard Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Admin authentication
    Route::get('/login', [AdminDashboardController::class, 'login'])->name('login');
    
    // Protected admin routes
    Route::middleware(['auth:web', 'role:admin'])->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/users', [AdminDashboardController::class, 'users'])->name('users');
        Route::get('/partners', [AdminDashboardController::class, 'partners'])->name('partners');
        Route::get('/packages', [AdminDashboardController::class, 'packages'])->name('packages');
        Route::get('/bookings', [AdminDashboardController::class, 'bookings'])->name('bookings');
        Route::get('/analytics', [AdminDashboardController::class, 'analytics'])->name('analytics');
        Route::get('/settings', [AdminDashboardController::class, 'settings'])->name('settings');
    });
});

// Fallback route
Route::fallback(function () {
    return view('errors.404');
});
