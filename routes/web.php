<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Auth\B2BAuthController;
use App\Http\Controllers\Auth\B2BRegisterController;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\Auth\CustomerRegisterController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserModerationController;
use App\Http\Controllers\Admin\PartnerManagementController;
use App\Http\Controllers\B2B\DashboardController as B2BDashboardController;
use App\Http\Controllers\B2B\HotelProviderController;
use App\Http\Controllers\B2B\TransportProviderController;
use App\Http\Controllers\B2B\ServiceOfferController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;

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

// Welcome page
Route::get('/', function () {
    return view('welcome');
});

Route::get('/get/media/{media}', [HomeController::class, 'GetMedia'])->name('get.media');


/*
|--------------------------------------------------------------------------
| Admin Authentication Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    // Admin Login Routes
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login']);
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
    
    // Admin Dashboard Routes - Protected by auth middleware
    Route::middleware(['auth', 'role.redirect'])->group(function () {
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('users', function() {
            return redirect()->route('admin.users.moderation');
        })->name('users');
        Route::get('partners', function() {
            return redirect()->route('admin.partners.management');
        })->name('partners');
        Route::get('packages', [AdminDashboardController::class, 'packages'])->name('packages');
        Route::get('packages/{id}', [AdminDashboardController::class, 'viewPackage'])->name('packages.show');
        Route::post('packages/{id}/approve', [AdminDashboardController::class, 'approvePackage'])->name('packages.approve');
        Route::post('packages/{id}/reject', [AdminDashboardController::class, 'rejectPackage'])->name('packages.reject');
        Route::get('bookings', [AdminDashboardController::class, 'bookings'])->name('bookings');
        Route::get('analytics', [AdminDashboardController::class, 'analytics'])->name('analytics');
        Route::get('settings', [AdminDashboardController::class, 'settings'])->name('settings');
        Route::get('profile/{id}', [AdminDashboardController::class, 'profile'])->name('profile');
        Route::post('profile/{id}', [AdminDashboardController::class, 'updateProfile'])->name('profile.update');
        
        // User Moderation Routes (Admin Panel Users Only)
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('moderation', [UserModerationController::class, 'index'])->name('moderation');
            Route::post('{user}/status', [UserModerationController::class, 'setStatus'])->name('set-status');
            Route::get('create-admin', [UserModerationController::class, 'createAdmin'])->name('create-admin');
            Route::post('create-admin', [UserModerationController::class, 'storeAdmin'])->name('store-admin');
            Route::get('{user}/edit', [UserModerationController::class, 'edit'])->name('edit');
            Route::put('{user}', [UserModerationController::class, 'update'])->name('update');
            Route::delete('{user}', [UserModerationController::class, 'destroy'])->name('destroy');
        });
        
        
        // Ad Management Routes
        Route::prefix('ads')->name('ads.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AdManagementController::class, 'index'])->name('index');
            Route::get('/pending', [\App\Http\Controllers\Admin\AdManagementController::class, 'pending'])->name('pending');
            Route::get('/{id}', [\App\Http\Controllers\Admin\AdManagementController::class, 'show'])->name('show');
            Route::post('/{id}/approve', [\App\Http\Controllers\Admin\AdManagementController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [\App\Http\Controllers\Admin\AdManagementController::class, 'reject'])->name('reject');
            Route::put('/{id}/scheduling', [\App\Http\Controllers\Admin\AdManagementController::class, 'updateScheduling'])->name('scheduling.update');
            Route::put('/{id}/priority', [\App\Http\Controllers\Admin\AdManagementController::class, 'updatePriority'])->name('priority.update');
            Route::post('/{id}/toggle-active', [\App\Http\Controllers\Admin\AdManagementController::class, 'toggleActive'])->name('toggle-active');
            Route::post('/bulk-approve', [\App\Http\Controllers\Admin\AdManagementController::class, 'bulkApprove'])->name('bulk-approve');
            Route::post('/bulk-reject', [\App\Http\Controllers\Admin\AdManagementController::class, 'bulkReject'])->name('bulk-reject');
            Route::delete('/{id}', [\App\Http\Controllers\Admin\AdManagementController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/analytics', [\App\Http\Controllers\Admin\AdManagementController::class, 'analytics'])->name('analytics');
            
            // Ad Analytics Routes
            Route::prefix('analytics')->name('analytics.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\AdAnalyticsController::class, 'index'])->name('index');
                Route::get('/export', [\App\Http\Controllers\Admin\AdAnalyticsController::class, 'export'])->name('export');
                Route::get('/realtime', [\App\Http\Controllers\Admin\AdAnalyticsController::class, 'realtime'])->name('realtime');
                Route::get('/{ad}', [\App\Http\Controllers\Admin\AdAnalyticsController::class, 'show'])->name('show');
            });
        });
        
        // Partner Management Routes (B2B Business Management)
        Route::prefix('partners')->name('partners.')->group(function () {
            // Specific routes MUST come before parameterized routes
            Route::get('management', [PartnerManagementController::class, 'index'])->name('management');
            Route::get('business/overview', [PartnerManagementController::class, 'businessOverview'])->name('business-overview');
            Route::get('export', [PartnerManagementController::class, 'export'])->name('export');
            
            
            // Hotel Service Review Routes
            
            Route::get('hotel-services', [PartnerManagementController::class, 'hotelServices'])->name('hotel-services');
            Route::get('hotel-services/{service}', [PartnerManagementController::class, 'getHotelService'])->name('hotel-services.show');
            Route::post('hotel-services/{service}/approve', [PartnerManagementController::class, 'approveHotelService'])->name('hotel-services.approve');
            Route::post('hotel-services/{service}/reject', [PartnerManagementController::class, 'rejectHotelService'])->name('hotel-services.reject');
            Route::post('hotel-services/{service}/suspend', [PartnerManagementController::class, 'suspendHotelService'])->name('hotel-services.suspend');
            Route::post('hotel-services/{service}/reactivate', [PartnerManagementController::class, 'reactivateHotelService'])->name('hotel-services.reactivate');
            
            // Parameterized routes MUST come last
            Route::get('{partner}', [PartnerManagementController::class, 'show'])->name('show');
            Route::post('{partner}/approve', [PartnerManagementController::class, 'approve'])->name('approve');
            Route::post('{partner}/reject', [PartnerManagementController::class, 'reject'])->name('reject');
            Route::post('{partner}/suspend', [PartnerManagementController::class, 'suspend'])->name('suspend');
            Route::post('{partner}/reactivate', [PartnerManagementController::class, 'reactivate'])->name('reactivate');
        });
    });
});

/*
|--------------------------------------------------------------------------
| B2B Routes - Load from separate file
|--------------------------------------------------------------------------
*/
// Load B2B routes from dedicated file
require __DIR__.'/b2b.php';

/*
|--------------------------------------------------------------------------
| Public Customer Routes (E-commerce Style)
|--------------------------------------------------------------------------
*/
// Public pages - no login required
Route::get('/', [CustomerDashboardController::class, 'home'])->name('home');
Route::get('/packages', [CustomerDashboardController::class, 'packages'])->name('packages');
Route::get('/packages/{identifier}', [CustomerDashboardController::class, 'packageDetails'])->name('packages.details')
    ->where('identifier', '[0-9]+|[a-zA-Z0-9\-]+');
// API route for search
Route::get('/api/packages/search', [CustomerDashboardController::class, 'searchPackages'])->name('api.packages.search');
Route::get('/api/packages/departure-cities', [CustomerDashboardController::class, 'getDepartureCities'])->name('api.packages.departure-cities');
Route::get('/api/packages/destinations', [CustomerDashboardController::class, 'getDestinations'])->name('api.packages.destinations');
Route::get('/flights', [CustomerDashboardController::class, 'flights'])->name('flights');
Route::get('/flights/{id}', [CustomerDashboardController::class, 'flightDetails'])->name('flights.details');
Route::get('/hotels', [CustomerDashboardController::class, 'hotels'])->name('hotels');
Route::get('/hotels/{id}', [CustomerDashboardController::class, 'hotelDetails'])->name('hotels.details');
Route::get('/explore', [CustomerDashboardController::class, 'explore'])->name('explore');
Route::get('/about', [CustomerDashboardController::class, 'about'])->name('about');
Route::get('/contact', [CustomerDashboardController::class, 'contact'])->name('contact');
Route::post('/contact', [CustomerDashboardController::class, 'submitContact'])->name('contact.submit');

/*
|--------------------------------------------------------------------------
| Customer Authentication & Account Routes
|--------------------------------------------------------------------------
*/
Route::prefix('customer')->name('customer.')->group(function () {
    // Customer Login Routes
    Route::get('login', [CustomerAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [CustomerAuthController::class, 'login']);
    Route::post('logout', [CustomerAuthController::class, 'logout'])->name('logout');
    
    // Customer Registration Routes
    Route::get('register', [CustomerRegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [CustomerRegisterController::class, 'register']);
    
    // Customer Account Routes - Protected by auth middleware (login required)
    Route::middleware(['auth', 'role.redirect'])->group(function () {
        Route::get('dashboard', [CustomerDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('bookings', [CustomerDashboardController::class, 'bookings'])->name('bookings');
        Route::get('bookings/{id}', [CustomerDashboardController::class, 'bookingDetails'])->name('bookings.show');
        Route::get('profile', [CustomerDashboardController::class, 'profile'])->name('profile');
        Route::post('profile', [CustomerDashboardController::class, 'updateProfile'])->name('profile.update');
        
        // My Flight Bookings (requires authentication)
        Route::get('my-flight-bookings', [CustomerDashboardController::class, 'myFlightBookings'])->name('flights.my-bookings');
    });
    
    // Flight booking route (public - guests can book)
    Route::get('flights/book/{hash}', [CustomerDashboardController::class, 'flightBooking'])->name('flights.booking');
    
    // Booking Routes - Require authentication
    Route::middleware(['auth', 'role.redirect'])->group(function () {
        // Package Booking
        Route::post('packages/{id}/book', [CustomerDashboardController::class, 'startPackageBooking'])->name('packages.book');
        Route::get('packages/{id}/checkout', [CustomerDashboardController::class, 'packageCheckout'])->name('packages.checkout');
        Route::post('packages/{id}/checkout', [CustomerDashboardController::class, 'processPackageCheckout'])->name('packages.checkout.process');
        
        // Flight Booking
        Route::post('flights/{id}/book', [CustomerDashboardController::class, 'startFlightBooking'])->name('flights.book');
        Route::get('flights/{id}/checkout', [CustomerDashboardController::class, 'flightCheckout'])->name('flights.checkout');
        Route::post('flights/{id}/checkout', [CustomerDashboardController::class, 'processFlightCheckout'])->name('flights.checkout.process');
        
        // Hotel Booking
        Route::post('hotels/{id}/book', [CustomerDashboardController::class, 'startHotelBooking'])->name('hotels.book');
        Route::get('hotels/{id}/checkout', [CustomerDashboardController::class, 'hotelCheckout'])->name('hotels.checkout');
        Route::post('hotels/{id}/checkout', [CustomerDashboardController::class, 'processHotelCheckout'])->name('hotels.checkout.process');
        
        // Legacy checkout route for backward compatibility
        Route::get('checkout/{bookingId}', [CustomerDashboardController::class, 'checkout'])->name('checkout');
        Route::post('checkout/{bookingId}', [CustomerDashboardController::class, 'processCheckout'])->name('checkout.process');
    });
});

/*
|--------------------------------------------------------------------------
| Legacy Routes (Redirects)
|--------------------------------------------------------------------------
*/
// Redirect legacy routes to customer routes
Route::get('/login', function () {
    return redirect()->route('customer.login');
})->name('login');
Route::get('/register', function () {
    return redirect()->route('customer.register');
});
Route::get('/home', function () {
    return redirect()->route('customer.dashboard');
});
Route::get('/dashboard', function () {
    if (auth()->check()) {
        $user = auth()->user();
        return match($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'partner' => redirect()->route('b2b.dashboard'),
            'customer' => redirect()->route('customer.dashboard'),
            default => redirect()->route('customer.dashboard')
        };
    }
    return redirect()->route('customer.login');
})->name('dashboard');

// Keep Laravel's password reset routes
Auth::routes(['login' => false, 'register' => false]);
