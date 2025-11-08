<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\B2BAuthController;
use App\Http\Controllers\Auth\B2BRegisterController;
use App\Http\Controllers\B2B\DashboardController as B2BDashboardController;
use App\Http\Controllers\B2B\HotelController;
use App\Http\Controllers\B2B\RoomTypeController;
use App\Http\Controllers\B2B\PricingRuleController;
use App\Http\Controllers\B2B\RoomController;
use App\Http\Controllers\B2B\RoomRatesController;
// Note: These controllers need to be created
// use App\Http\Controllers\B2B\TravelAgentController;
// use App\Http\Controllers\B2B\TransportController;
// use App\Http\Controllers\B2B\DashboardController;

/*
|--------------------------------------------------------------------------
| B2B Partner Routes
|--------------------------------------------------------------------------
|
| Here are the routes for B2B partner functionalities including
| authentication, registration, and partner-specific features for
| travel agents, hotel providers, and transport providers.
|
*/

Route::prefix('b2b')->name('b2b.')->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | B2B Authentication & Registration Routes (Public Access)
    |--------------------------------------------------------------------------
    */
    // B2B Login Routes
    Route::get('login', [B2BAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [B2BAuthController::class, 'login']);
    Route::post('logout', [B2BAuthController::class, 'logout'])->name('logout');
    
    // B2B Registration Routes
    Route::get('register', [B2BRegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [B2BRegisterController::class, 'register']);
    Route::get('pending', [B2BRegisterController::class, 'pending'])->name('pending');

    /*
    |--------------------------------------------------------------------------
    | B2B Protected Routes (Authentication Required)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth', 'role.redirect'])->group(function () {
        
        // Main B2B Dashboard (role-based redirect)
        Route::get('dashboard', [B2BDashboardController::class, 'index'])->name('dashboard');
        
        /*
        |--------------------------------------------------------------------------
        | Travel Agent Routes (TODO: Create TravelAgentController)
        |--------------------------------------------------------------------------
        */
        Route::middleware(['role:travel_agent'])->prefix('travel-agent')->name('travel-agent.')->group(function () {
            Route::get('dashboard', [\App\Http\Controllers\B2B\TravelAgentDashboardController::class, 'index'])->name('dashboard');
            
            // Draft management routes
            Route::get('drafts', [\App\Http\Controllers\B2B\TravelAgentDashboardController::class, 'drafts'])->name('drafts');
            Route::delete('drafts/{draftId}', [\App\Http\Controllers\B2B\TravelAgentDashboardController::class, 'deleteDraft'])->name('drafts.delete');
            Route::post('drafts/cleanup-expired', [\App\Http\Controllers\B2B\TravelAgentDashboardController::class, 'cleanupExpired'])->name('drafts.cleanup-expired');
            
            // Enhanced Package Management (Multi-step Creation)
            Route::resource('packages', \App\Http\Controllers\B2B\PackageController::class);
            
            // Multi-step package creation wizard
            Route::get('packages/create/step-2', [\App\Http\Controllers\B2B\PackageController::class, 'createStep2'])->name('packages.create-step2')->middleware('validate.package.step:step-2');
            Route::get('packages/create/step-3', [\App\Http\Controllers\B2B\PackageController::class, 'createStep3'])->name('packages.create-step3')->middleware('validate.package.step:step-3');
            Route::get('packages/create/step-4', [\App\Http\Controllers\B2B\PackageController::class, 'createStep4'])->name('packages.create-step4')->middleware('validate.package.step:step-4');
            Route::get('packages/create/step-5', [\App\Http\Controllers\B2B\PackageController::class, 'createStep5'])->name('packages.create-step5')->middleware('validate.package.step:step-5');
            
            // Package management actions
            Route::patch('packages/{package}/toggle-status', [\App\Http\Controllers\B2B\PackageController::class, 'toggleStatus'])->name('packages.toggle-status');
            
            // Draft management
            Route::post('packages/save-draft', [\App\Http\Controllers\B2B\PackageController::class, 'saveDraft'])->name('packages.save-draft');
            Route::get('packages/load-draft/{draftId}', [\App\Http\Controllers\B2B\PackageController::class, 'loadDraft'])->name('packages.load-draft');
            Route::get('packages/continue-draft/{draftId}', [\App\Http\Controllers\B2B\PackageController::class, 'continueDraft'])->name('packages.continue-draft');
            
            // Step validation
            Route::post('packages/validate-step', [\App\Http\Controllers\B2B\PackageController::class, 'validateStep'])->name('packages.validate-step');
            
            // Provider details for Step 5 review
            Route::get('packages/drafts/{draftId}/provider-details', [\App\Http\Controllers\B2B\PackageController::class, 'getProviderDetails'])->name('packages.provider-details');
            
            // Debug route (temporary)
            Route::post('packages/debug-form', [\App\Http\Controllers\B2B\PackageController::class, 'debugFormData'])->name('packages.debug-form');
            
            
            // Package Image Upload API Routes
            Route::prefix('packages/images')->name('packages.images.')->group(function () {
                Route::post('upload-draft', [\App\Http\Controllers\B2B\PackageController::class, 'uploadDraftImage'])->name('upload-draft');
                Route::delete('delete-draft/{imageId}', [\App\Http\Controllers\B2B\PackageController::class, 'deleteDraftImage'])->name('delete-draft');
                Route::post('reorder-draft', [\App\Http\Controllers\B2B\PackageController::class, 'reorderDraftImages'])->name('reorder-draft');
                Route::post('set-main-draft', [\App\Http\Controllers\B2B\PackageController::class, 'setMainDraftImage'])->name('set-main-draft');
                Route::post('upload/{package}', [\App\Http\Controllers\B2B\PackageController::class, 'uploadPackageImage'])->name('upload');
                Route::delete('delete/{package}/{imageId}', [\App\Http\Controllers\B2B\PackageController::class, 'deletePackageImage'])->name('delete');
                Route::post('reorder/{package}', [\App\Http\Controllers\B2B\PackageController::class, 'reorderPackageImages'])->name('reorder');
                Route::post('set-main/{package}', [\App\Http\Controllers\B2B\PackageController::class, 'setMainPackageImage'])->name('set-main');
            });
            
            // Package duplication
            Route::post('packages/{package}/duplicate', [\App\Http\Controllers\B2B\PackageController::class, 'duplicate'])->name('packages.duplicate');
            
            // Analytics
            Route::get('packages/analytics', [\App\Http\Controllers\B2B\PackageController::class, 'analytics'])->name('packages.analytics');
            
            // Legacy flight management (keeping for backward compatibility)
            Route::post('packages/{package}/add-flight', [\App\Http\Controllers\B2B\PackageController::class, 'addFlight'])->name('packages.add-flight');
            Route::delete('packages/{package}/flights/{flight}', [\App\Http\Controllers\B2B\PackageController::class, 'removeFlight'])->name('packages.remove-flight');
            Route::get('packages-flights/available', [\App\Http\Controllers\B2B\PackageController::class, 'getAvailableFlights'])->name('packages.available-flights');
            
            /*
            |--------------------------------------------------------------------------
            | API Routes for AJAX functionality
            |--------------------------------------------------------------------------
            */
            Route::prefix('api')->name('api.')->group(function () {
                // Enhanced Provider search and management
                Route::post('providers/search-hotels', [\App\Http\Controllers\B2B\API\ProviderSearchController::class, 'searchHotels'])->name('providers.search-hotels');
                Route::post('providers/search-flights', [\App\Http\Controllers\B2B\API\ProviderSearchController::class, 'searchFlights'])->name('providers.search-flights');
                Route::post('providers/search-transport', [\App\Http\Controllers\B2B\API\ProviderSearchController::class, 'searchTransport'])->name('providers.search-transport');
                Route::get('providers/own-flights', [\App\Http\Controllers\B2B\API\ProviderSearchController::class, 'getOwnFlights'])->name('providers.own-flights');
                Route::post('providers/request-approval', [\App\Http\Controllers\B2B\API\ProviderSearchController::class, 'requestProviderApproval'])->name('providers.request-approval');
                Route::get('providers/check-approval-status', [\App\Http\Controllers\B2B\API\ProviderSearchController::class, 'checkApprovalStatus'])->name('providers.check-approval-status');
                
// Service Request Management (B2B session-based auth)
                Route::prefix('service-requests')->name('service-requests.')->group(function () {
                    Route::post('/', [\App\Http\Controllers\Api\ServiceRequestController::class, 'store'])->name('store');
                    Route::get('/package/{packageId}/status', [\App\Http\Controllers\Api\ServiceRequestController::class, 'packageStatus'])->name('package-status');
                    Route::post('/batch-status', [\App\Http\Controllers\Api\ServiceRequestController::class, 'batchStatus'])->name('batch-status');
                });
                
                // Legacy provider routes (keeping for backward compatibility)
                Route::post('providers/search', [\App\Http\Controllers\B2B\API\ProviderController::class, 'search'])->name('providers.search');
                Route::post('providers/batch-fetch', [\App\Http\Controllers\B2B\API\ProviderController::class, 'batchFetch'])->name('providers.batch-fetch');
                Route::post('providers/check-availability', [\App\Http\Controllers\B2B\API\ProviderController::class, 'checkAvailability'])->name('providers.check-availability');
                Route::post('providers/calculate-pricing', [\App\Http\Controllers\B2B\API\ProviderController::class, 'calculatePricing'])->name('providers.calculate-pricing');
                
                // Activity management for itinerary builder
                Route::prefix('packages/{package}/activities')->name('activities.')->group(function () {
                    Route::post('/', [\App\Http\Controllers\B2B\API\ActivityController::class, 'store'])->name('store');
                    Route::put('{activity}', [\App\Http\Controllers\B2B\API\ActivityController::class, 'update'])->name('update');
                    Route::delete('{activity}', [\App\Http\Controllers\B2B\API\ActivityController::class, 'destroy'])->name('destroy');
                    Route::post('reorder', [\App\Http\Controllers\B2B\API\ActivityController::class, 'reorder'])->name('reorder');
                    Route::get('day/{day}', [\App\Http\Controllers\B2B\API\ActivityController::class, 'getByDay'])->name('by-day');
                    Route::post('{activity}/duplicate', [\App\Http\Controllers\B2B\API\ActivityController::class, 'duplicate'])->name('duplicate');
                });
            });
            
            // Flight Management (RESTful Resource)
            Route::resource('flights', \App\Http\Controllers\B2B\FlightController::class);
            Route::patch('flights/{flight}/toggle-status', [\App\Http\Controllers\B2B\FlightController::class, 'toggleStatus'])->name('flights.toggle-status');
            Route::patch('flights/{flight}/update-status', [\App\Http\Controllers\B2B\FlightController::class, 'updateStatus'])->name('flights.update-status');
            Route::post('flights/{flight}/duplicate', [\App\Http\Controllers\B2B\FlightController::class, 'duplicate'])->name('flights.duplicate');
            Route::get('flights-schedule/data', [\App\Http\Controllers\B2B\FlightController::class, 'getScheduleData'])->name('flights.schedule-data');
            Route::post('flights/bulk-update-seats', [\App\Http\Controllers\B2B\FlightController::class, 'bulkUpdateSeats'])->name('flights.bulk-update-seats');
            
            // Request Management (for when travel agents receive requests for their flights)
            Route::get('requests', [\App\Http\Controllers\B2B\ProviderRequestController::class, 'index'])->name('requests');
            Route::get('requests/data', [\App\Http\Controllers\B2B\ProviderRequestController::class, 'getData'])->name('requests.data');
            Route::get('requests/stats', [\App\Http\Controllers\B2B\ProviderRequestController::class, 'getStats'])->name('requests.stats');
            Route::get('requests/{id}', [\App\Http\Controllers\B2B\ProviderRequestController::class, 'show'])->name('requests.show');
            Route::get('requests/{id}/booking', [\App\Http\Controllers\B2B\ProviderRequestController::class, 'getBookingDetails'])->name('requests.booking-details');
            Route::post('requests/{id}/approve', [\App\Http\Controllers\B2B\ProviderRequestController::class, 'approve'])->name('requests.approve');
            Route::post('requests/{id}/reject', [\App\Http\Controllers\B2B\ProviderRequestController::class, 'reject'])->name('requests.reject');
            Route::post('requests/batch-action', [\App\Http\Controllers\B2B\ProviderRequestController::class, 'batchAction'])->name('requests.batch-action');
            
            // Booking Management (Package Bookings Only)
            Route::get('bookings', [\App\Http\Controllers\B2B\TravelAgentBookingController::class, 'index'])->name('bookings');
            Route::get('bookings/data', [\App\Http\Controllers\B2B\TravelAgentBookingController::class, 'getData'])->name('bookings.data');
            Route::get('bookings/stats', [\App\Http\Controllers\B2B\TravelAgentBookingController::class, 'getStats'])->name('bookings.stats');
            Route::get('bookings/export', [\App\Http\Controllers\B2B\TravelAgentBookingController::class, 'export'])->name('bookings.export');
            
            // All Bookings Management (Unified: Package, Hotel, Flight, Transport) - Must come before {id} routes
            
            Route::get('bookings/all', [\App\Http\Controllers\B2B\AllBookingsController::class, 'index'])->name('bookings.all');
            Route::get('bookings/all/export', [\App\Http\Controllers\B2B\AllBookingsController::class, 'export'])->name('bookings.export-all');
            
            // Package booking specific routes (with ID parameter - must come after specific routes)
            Route::get('bookings/{id}', [\App\Http\Controllers\B2B\TravelAgentBookingController::class, 'show'])->name('bookings.show');
            Route::post('bookings/{id}/confirm', [\App\Http\Controllers\B2B\TravelAgentBookingController::class, 'confirm'])->name('bookings.confirm');
            Route::post('bookings/{id}/cancel', [\App\Http\Controllers\B2B\TravelAgentBookingController::class, 'cancel'])->name('bookings.cancel');
            Route::post('bookings/{id}/update-payment', [\App\Http\Controllers\B2B\TravelAgentBookingController::class, 'updatePaymentStatus'])->name('bookings.update-payment');
            
            // Universal booking actions (works across all booking types)
            Route::get('bookings/{type}/{id}', [\App\Http\Controllers\B2B\AllBookingsController::class, 'show'])->name('bookings.show-universal')
                ->where('type', 'package|hotel|flight|transport');
            Route::post('bookings/{type}/{id}/cancel', [\App\Http\Controllers\B2B\AllBookingsController::class, 'cancel'])->name('bookings.cancel-universal')
                ->where('type', 'package|hotel|flight|transport');
            Route::post('bookings/{type}/{id}/update-payment', [\App\Http\Controllers\B2B\AllBookingsController::class, 'updatePayment'])->name('bookings.update-payment-universal')
                ->where('type', 'package|hotel|flight|transport');
            
            Route::get('customers', function () {
                return view('b2b.travel-agent.customers');
            })->name('customers');
            
            Route::get('commissions', function () {
                return view('b2b.travel-agent.commissions');
            })->name('commissions');
            
            Route::get('reports', function () {
                return view('b2b.travel-agent.reports');
            })->name('reports');
            
            Route::get('profile', function () {
                return view('b2b.travel-agent.profile');
            })->name('profile');
            
            // Route::put('profile', [TravelAgentController::class, 'updateProfile'])->name('profile.update');
        });

        /*
        |--------------------------------------------------------------------------
        | Hotel Provider Routes
        |--------------------------------------------------------------------------
        */
        Route::middleware(['role:hotel_provider'])->prefix('hotel-provider')->name('hotel-provider.')->group(function () {
            Route::get('dashboard', [HotelController::class, 'index'])->name('dashboard');
            
            // Hotel Management (RESTful Resource)
            Route::resource('hotels', HotelController::class);
            Route::patch('hotels/{hotel}/toggle-status', [HotelController::class, 'toggleStatus'])->name('hotels.toggle-status');
            
            // Pricing Rules Management
            Route::resource('pricing-rules', PricingRuleController::class);
            Route::patch('pricing-rules/{pricingRule}/toggle-status', [PricingRuleController::class, 'toggleStatus'])->name('pricing-rules.toggle-status');
            Route::post('pricing-rules/bulk-action', [PricingRuleController::class, 'bulkAction'])->name('pricing-rules.bulk-action');
            Route::get('pricing-rules/applicable', [PricingRuleController::class, 'getApplicableRules'])->name('pricing-rules.applicable');
            Route::post('pricing-rules/calculate', [PricingRuleController::class, 'calculatePrice'])->name('pricing-rules.calculate');
            
            // Bulk Pricing Rules Operations
            Route::post('pricing-rules/bulk-create', [PricingRuleController::class, 'bulkCreate'])->name('pricing-rules.bulk-create');
            Route::post('pricing-rules/import', [PricingRuleController::class, 'importPricingRules'])->name('pricing-rules.import');
            Route::get('pricing-rules/export/{ids}', [PricingRuleController::class, 'exportPricingRules'])->name('pricing-rules.export');
            Route::get('pricing-rules/analytics', [PricingRuleController::class, 'analytics'])->name('pricing-rules.analytics');
            Route::post('pricing-rules/{pricingRule}/toggle', [PricingRuleController::class, 'toggle'])->name('pricing-rules.toggle');
            
            // Room Management
            Route::resource('rooms', RoomController::class);
            Route::patch('rooms/{room}/toggle-status', [RoomController::class, 'toggleStatus'])->name('rooms.toggle-status');
            Route::patch('rooms/{room}/toggle-availability', [RoomController::class, 'toggleAvailability'])->name('rooms.toggle-availability');
            Route::post('rooms/bulk-action', [RoomController::class, 'bulkAction'])->name('rooms.bulk-action');
            Route::get('hotels/{hotel}/rooms', [RoomController::class, 'getByHotel'])->name('hotels.rooms.api');
            Route::get('hotels/{hotel}/room-categories', [HotelController::class, 'getRoomCategories'])->name('hotels.room-categories.api');
            Route::get('rooms/available', [RoomController::class, 'getAvailable'])->name('rooms.available');
            
            // Hotel-specific sub-features
            Route::prefix('hotels/{hotel}')->name('hotels.')->group(function () {
                Route::get('rooms', [RoomController::class, 'index'])->name('rooms');
                Route::get('reviews', [HotelController::class, 'reviews'])->name('reviews');
                Route::get('analytics', [HotelController::class, 'analytics'])->name('analytics');
            });
            
            // ========== BOOKING MANAGEMENT ROUTES ==========
            
            // Main booking management routes
            Route::get('bookings', [HotelController::class, 'bookings'])->name('bookings.index');
            
            // Legacy route name alias for backward compatibility
            Route::get('bookings-legacy', function() {
                return redirect()->route('b2b.hotel-provider.bookings.index');
            })->name('bookings');
            Route::get('bookings/data', [HotelController::class, 'bookingsData'])->name('bookings.data');
            Route::get('bookings/export', [HotelController::class, 'exportBookings'])->name('bookings.export');
            
            // Individual booking management
            Route::get('bookings/{booking}', [HotelController::class, 'showBooking'])->name('bookings.show');
            Route::post('bookings/{booking}/confirm', [HotelController::class, 'confirmBooking'])->name('bookings.confirm');
            Route::post('bookings/{booking}/cancel', [HotelController::class, 'cancelBooking'])->name('bookings.cancel');
            Route::post('bookings/{booking}/check-in', [HotelController::class, 'checkInBooking'])->name('bookings.check-in');
            Route::post('bookings/{booking}/check-out', [HotelController::class, 'checkOutBooking'])->name('bookings.check-out');
            Route::post('bookings/{booking}/payment', [HotelController::class, 'recordPayment'])->name('bookings.payment');
            
            // Hotel-specific booking routes
            Route::get('hotels/{hotel}/bookings', [HotelController::class, 'hotelBookings'])->name('bookings.hotel');
            Route::get('hotels/{hotel}/bookings/data', [HotelController::class, 'hotelBookingsData'])->name('bookings.hotel.data');
            Route::get('hotels/{hotel}/bookings/export', [HotelController::class, 'exportHotelBookings'])->name('bookings.hotel.export');
            
            // Booking calendar routes
            Route::get('hotels/{hotel}/bookings/calendar', [HotelController::class, 'bookingCalendar'])->name('bookings.calendar');
            Route::get('hotels/{hotel}/bookings/calendar/data', [HotelController::class, 'bookingCalendarData'])->name('bookings.calendar.data');
            Route::get('hotels/{hotel}/bookings/calendar/export', [HotelController::class, 'exportCalendarData'])->name('bookings.calendar.export');
            
            // Booking Statistics Dashboard
            Route::get('bookings/dashboard', [HotelController::class, 'bookingDashboard'])->name('bookings.dashboard');
            Route::get('bookings/dashboard/data', [HotelController::class, 'bookingDashboardData'])->name('bookings.dashboard.data');
            Route::get('bookings/dashboard/export', [HotelController::class, 'bookingDashboardExport'])->name('bookings.dashboard.export');
            
            // Legacy route for backward compatibility
            Route::get('all-bookings', [HotelController::class, 'allBookings'])->name('all-bookings');
            
            // Provider Requests Management
            Route::get('requests', [\App\Http\Controllers\B2B\ProviderRequestController::class, 'index'])->name('requests');
            Route::get('requests/data', [\App\Http\Controllers\B2B\ProviderRequestController::class, 'getData'])->name('requests.data');
            Route::get('requests/stats', [\App\Http\Controllers\B2B\ProviderRequestController::class, 'getStats'])->name('requests.stats');
            Route::get('requests/{id}', [\App\Http\Controllers\B2B\ProviderRequestController::class, 'show'])->name('requests.show');
            Route::get('requests/{id}/booking', [\App\Http\Controllers\B2B\ProviderRequestController::class, 'getBookingDetails'])->name('requests.booking-details');
            
            // Room availability and assignment routes
            Route::get('requests/{id}/available-rooms', [\App\Http\Controllers\B2B\ProviderRequestController::class, 'getAvailableRooms'])->name('requests.available-rooms');
            Route::post('requests/{id}/assign-room', [\App\Http\Controllers\B2B\ProviderRequestController::class, 'assignRoom'])->name('requests.assign-room');
            Route::get('requests/{id}/room-assignment', [\App\Http\Controllers\B2B\ProviderRequestController::class, 'getRoomAssignmentStatus'])->name('requests.room-assignment');
            Route::get('hotels/occupancy-stats', [\App\Http\Controllers\B2B\ProviderRequestController::class, 'getHotelOccupancyStats'])->name('hotels.occupancy-stats');
            
            Route::post('requests/{id}/approve', [\App\Http\Controllers\B2B\ProviderRequestController::class, 'approve'])->name('requests.approve');
            Route::post('requests/{id}/reject', [\App\Http\Controllers\B2B\ProviderRequestController::class, 'reject'])->name('requests.reject');
            Route::post('requests/batch-action', [\App\Http\Controllers\B2B\ProviderRequestController::class, 'batchAction'])->name('requests.batch-action');
            
            // Room Rates Management (New Grouped System)
            Route::get('rates', [RoomRatesController::class, 'index'])->name('rates');
            Route::post('rates', [RoomRatesController::class, 'store'])->name('rates.store');
            Route::post('rates/group-store', [RoomRatesController::class, 'storeGroupRate'])->name('rates.group-store');
            Route::get('rates/group-rooms', [RoomRatesController::class, 'getGroupRooms'])->name('rates.group-rooms');
            Route::get('rates/history', [RoomRatesController::class, 'getRateHistory'])->name('rates.history');
            Route::get('rates/calendar', [RoomRatesController::class, 'getCalendarRates'])->name('rates.calendar');
            Route::delete('rates/group-clear', [RoomRatesController::class, 'clearGroupRates'])->name('rates.group-clear');
            
            // Pricing Rules Integration with Room Rates
            Route::post('room-rates/apply-pricing-rules', [RoomRatesController::class, 'applyPricingRules'])->name('room-rates.apply-pricing-rules');
            Route::post('room-rates/preview-pricing-rules', [RoomRatesController::class, 'previewPricingRules'])->name('room-rates.preview-pricing-rules');
            Route::get('room-rates/with-pricing-rules', [RoomRatesController::class, 'getRatesWithPricingRules'])->name('room-rates.with-pricing-rules');
            Route::post('room-rates/bulk-apply-pricing-rules', [RoomRatesController::class, 'bulkApplyPricingRules'])->name('room-rates.bulk-apply-pricing-rules');
            Route::get('availability', [HotelController::class, 'availability'])->name('availability');
            Route::get('reports', [HotelController::class, 'reports'])->name('reports');
            Route::get('profile', [HotelController::class, 'profile'])->name('profile');
            Route::put('profile', [HotelController::class, 'updateProfile'])->name('profile.update');
        });

        /*
        |--------------------------------------------------------------------------
        | Transport Provider Routes (TODO: Create TransportController)
        |--------------------------------------------------------------------------
        */
        Route::middleware(['role:transport_provider'])->prefix('transport-provider')->name('transport-provider.')->group(function () {
            // Dashboard
            Route::get('dashboard', [\App\Http\Controllers\B2B\TransportProviderController::class, 'index'])->name('dashboard');
            
            // Services Management
            Route::get('services', [\App\Http\Controllers\B2B\TransportProviderController::class, 'servicesIndex'])->name('services.index');
            Route::resource('services', \App\Http\Controllers\B2B\TransportProviderController::class, [
                'names' => [
                    'create' => 'create',
                    'store' => 'store',
                    'show' => 'show',
                    'edit' => 'edit',
                    'update' => 'update',
                    'destroy' => 'destroy',
                ],
                'parameters' => ['services' => 'transportService']
            ])->except(['index']);
            Route::patch('services/{transportService}/toggle-status', [\App\Http\Controllers\B2B\TransportProviderController::class, 'toggleStatus'])->name('toggle-status');
            
            // Rates Management
            Route::get('rates', [\App\Http\Controllers\B2B\TransportProviderController::class, 'pricing'])->name('rates');
            
            // Pricing Rules Management
            Route::get('pricing-rules', [\App\Http\Controllers\B2B\TransportProviderController::class, 'pricingRules'])->name('pricing-rules.index');
            
            // Fleet Management
            Route::prefix('fleet')->name('fleet.')->group(function () {
                // Vehicles
                Route::get('vehicles', [\App\Http\Controllers\B2B\FleetController::class, 'vehiclesIndex'])->name('vehicles');
                Route::get('vehicles/{vehicle}', [\App\Http\Controllers\B2B\FleetController::class, 'vehiclesShow'])->name('vehicles.show');
                Route::post('vehicles', [\App\Http\Controllers\B2B\FleetController::class, 'vehiclesStore'])->name('vehicles.store');
                Route::put('vehicles/{vehicle}', [\App\Http\Controllers\B2B\FleetController::class, 'vehiclesUpdate'])->name('vehicles.update');
                Route::delete('vehicles/{vehicle}', [\App\Http\Controllers\B2B\FleetController::class, 'vehiclesDestroy'])->name('vehicles.destroy');
                
                // Drivers
                Route::get('drivers', [\App\Http\Controllers\B2B\FleetController::class, 'driversIndex'])->name('drivers');
                Route::get('drivers/{driver}', [\App\Http\Controllers\B2B\FleetController::class, 'driversShow'])->name('drivers.show');
                Route::post('drivers', [\App\Http\Controllers\B2B\FleetController::class, 'driversStore'])->name('drivers.store');
                Route::put('drivers/{driver}', [\App\Http\Controllers\B2B\FleetController::class, 'driversUpdate'])->name('drivers.update');
                Route::delete('drivers/{driver}', [\App\Http\Controllers\B2B\FleetController::class, 'driversDestroy'])->name('drivers.destroy');
                Route::post('drivers/assign', [\App\Http\Controllers\B2B\FleetController::class, 'assignDriverToVehicle'])->name('drivers.assign');
                
                // Maintenance
                Route::get('maintenance', [\App\Http\Controllers\B2B\FleetController::class, 'maintenanceIndex'])->name('maintenance');
                Route::post('maintenance', [\App\Http\Controllers\B2B\FleetController::class, 'maintenanceStore'])->name('maintenance.store');
                Route::put('maintenance/{maintenance}/status', [\App\Http\Controllers\B2B\FleetController::class, 'maintenanceUpdateStatus'])->name('maintenance.status');
                Route::delete('maintenance/{maintenance}', [\App\Http\Controllers\B2B\FleetController::class, 'maintenanceDestroy'])->name('maintenance.destroy');
                
                // Availability & Assignment
                Route::post('check-availability', [\App\Http\Controllers\B2B\FleetController::class, 'checkAvailability'])->name('check-availability');
                Route::post('assign-service-request', [\App\Http\Controllers\B2B\FleetController::class, 'assignToServiceRequest'])->name('assign-service-request');
                
                // Calendar
                Route::get('calendar', function() {
                    return view('b2b.transport-provider.fleet.calendar');
                })->name('calendar');
                Route::get('calendar/data', [\App\Http\Controllers\B2B\FleetController::class, 'calendarData'])->name('calendar.data');
            });
            
            // Operations Management - Booking Management
            Route::get('operations/bookings', [\App\Http\Controllers\B2B\TransportProviderController::class, 'bookings'])->name('operations.bookings');
            Route::get('operations/bookings/data', [\App\Http\Controllers\B2B\TransportProviderController::class, 'bookingsData'])->name('operations.bookings.data');
            
            Route::get('bookings/{booking}', [\App\Http\Controllers\B2B\TransportProviderController::class, 'showBooking'])->name('bookings.show');
            Route::post('bookings/{booking}/confirm', [\App\Http\Controllers\B2B\TransportProviderController::class, 'confirmBooking'])->name('bookings.confirm');
            Route::post('bookings/{booking}/start', [\App\Http\Controllers\B2B\TransportProviderController::class, 'startBooking'])->name('bookings.start');
            Route::post('bookings/{booking}/complete', [\App\Http\Controllers\B2B\TransportProviderController::class, 'completeBooking'])->name('bookings.complete');
            Route::post('bookings/{booking}/cancel', [\App\Http\Controllers\B2B\TransportProviderController::class, 'cancelBooking'])->name('bookings.cancel');
            Route::get('operations/routes', function () {
                return view('b2b.transport-provider.operations.routes');
            })->name('operations.routes');
            
            // Reports
            Route::get('reports', function () {
                return view('b2b.transport-provider.reports.index');
            })->name('reports.index');
            
            // Profile
            Route::get('profile', [\App\Http\Controllers\B2B\TransportProviderController::class, 'profile'])->name('profile.index');
            Route::put('profile', [\App\Http\Controllers\B2B\TransportProviderController::class, 'updateProfile'])->name('profile.update');
            
            // Legacy bookings route for compatibility
            Route::get('bookings', function () {
                return redirect()->route('b2b.transport-provider.operations.bookings');
            })->name('bookings');
            
            // Legacy reports route for compatibility  
            Route::get('reports-legacy', function () {
                return redirect()->route('b2b.transport-provider.reports.index');
            });
            
            // Legacy profile route for compatibility
            Route::get('profile-legacy', function () {
                return redirect()->route('b2b.transport-provider.profile.index');
            });
            
            // Provider Requests Management
            Route::get('requests', [\App\Http\Controllers\B2B\ProviderRequestController::class, 'index'])->name('requests');
            Route::get('requests/data', [\App\Http\Controllers\B2B\ProviderRequestController::class, 'getData'])->name('requests.data');
            Route::get('requests/stats', [\App\Http\Controllers\B2B\ProviderRequestController::class, 'getStats'])->name('requests.stats');
            Route::get('requests/{id}', [\App\Http\Controllers\B2B\ProviderRequestController::class, 'show'])->name('requests.show');
            Route::get('requests/{id}/booking', [\App\Http\Controllers\B2B\ProviderRequestController::class, 'getBookingDetails'])->name('requests.booking-details');
            Route::post('requests/{id}/approve', [\App\Http\Controllers\B2B\ProviderRequestController::class, 'approve'])->name('requests.approve');
            Route::post('requests/{id}/reject', [\App\Http\Controllers\B2B\ProviderRequestController::class, 'reject'])->name('requests.reject');
            Route::post('requests/batch-action', [\App\Http\Controllers\B2B\ProviderRequestController::class, 'batchAction'])->name('requests.batch-action');
        });

        /*
        |--------------------------------------------------------------------------
        | Shared B2B Routes (All Partner Types)
        |--------------------------------------------------------------------------
        */
        // Ad Management (All B2B Users)
        Route::prefix('ads')->name('ads.')->middleware(['role:travel_agent,hotel_provider,transport_provider'])->group(function () {
            Route::get('/', [\App\Http\Controllers\B2B\AdController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\B2B\AdController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\B2B\AdController::class, 'store'])->name('store');
            Route::get('/data/list', [\App\Http\Controllers\B2B\AdController::class, 'getData'])->name('data');
            Route::get('/stats/summary', [\App\Http\Controllers\B2B\AdController::class, 'getStats'])->name('stats');
            Route::get('/{ad}', [\App\Http\Controllers\B2B\AdController::class, 'show'])->name('show');
            Route::get('/{ad}/edit', [\App\Http\Controllers\B2B\AdController::class, 'edit'])->name('edit');
            Route::put('/{ad}', [\App\Http\Controllers\B2B\AdController::class, 'update'])->name('update');
            Route::delete('/{ad}', [\App\Http\Controllers\B2B\AdController::class, 'destroy'])->name('destroy');
            Route::post('/{ad}/submit-for-approval', [\App\Http\Controllers\B2B\AdController::class, 'submitForApproval'])->name('submit-for-approval');
            Route::post('/{ad}/toggle-active', [\App\Http\Controllers\B2B\AdController::class, 'toggleActive'])->name('toggle-active');
        });
        
        Route::get('notifications', function () {
            return view('b2b.common.notifications.index');
        })->name('notifications');
        
        Route::get('help', function () {
            return view('b2b.common.help.index');
        })->name('help');
        
        Route::get('settings', function () {
            return view('b2b.common.settings.index');
        })->name('settings');
        
        // General B2B routes (role-agnostic)
        Route::get('bookings', [B2BDashboardController::class, 'bookings'])->name('bookings');
        Route::get('profile', [B2BDashboardController::class, 'profile'])->name('profile');
        Route::put('profile', [B2BDashboardController::class, 'updateProfile'])->name('profile.update');
        
        // Service Offer Management (All Provider Types)
        Route::middleware(['role:hotel_provider,transport_provider'])->group(function () {
            Route::resource('service-offers', \App\Http\Controllers\B2B\ServiceOfferController::class);
            Route::patch('service-offers/{serviceOffer}/toggle-status', [\App\Http\Controllers\B2B\ServiceOfferController::class, 'toggleStatus'])->name('service-offers.toggle-status');
        });
        
        // Transport Pricing Routes
        Route::middleware(['role:transport_provider'])->prefix('transport-provider')->name('transport-provider.')->group(function () {
            Route::get('rates', [\App\Http\Controllers\B2B\TransportProviderController::class, 'pricing'])->name('rates');
            Route::post('services/{transportService}/pricing/routes', [\App\Http\Controllers\B2B\TransportProviderController::class, 'updateRoutePricing'])->name('pricing.routes');
            Route::post('services/{transportService}/pricing/seasonal', [\App\Http\Controllers\B2B\TransportProviderController::class, 'createSeasonalPricing'])->name('pricing.seasonal');
            Route::get('analytics/pricing', [\App\Http\Controllers\B2B\TransportProviderController::class, 'pricingAnalytics'])->name('analytics.pricing');
            
            // Transport Rates Management
            Route::get('transport-rates', [\App\Http\Controllers\B2B\TransportRatesController::class, 'index'])->name('transport-rates.index');
            Route::post('transport-rates', [\App\Http\Controllers\B2B\TransportRatesController::class, 'store'])->name('transport-rates.store');
            Route::post('transport-rates/group-store', [\App\Http\Controllers\B2B\TransportRatesController::class, 'storeGroupRate'])->name('transport-rates.group-store');
            Route::get('transport-rates/group-routes', [\App\Http\Controllers\B2B\TransportRatesController::class, 'getGroupRoutes'])->name('transport-rates.group-routes');
            Route::get('transport-rates/history', [\App\Http\Controllers\B2B\TransportRatesController::class, 'getRateHistory'])->name('transport-rates.history');
            Route::get('transport-rates/group-history', [\App\Http\Controllers\B2B\TransportRatesController::class, 'getGroupHistory'])->name('transport-rates.group-history');
            Route::get('transport-rates/calendar', [\App\Http\Controllers\B2B\TransportRatesController::class, 'getCalendarRates'])->name('transport-rates.calendar');
            Route::delete('transport-rates/group-clear', [\App\Http\Controllers\B2B\TransportRatesController::class, 'clearGroupRates'])->name('transport-rates.group-clear');
            Route::post('transport-rates/copy-rates', [\App\Http\Controllers\B2B\TransportRatesController::class, 'copyRates'])->name('transport-rates.copy-rates');
            Route::post('transport-rates/apply-pricing-rules', [\App\Http\Controllers\B2B\TransportRatesController::class, 'applyPricingRules'])->name('transport-rates.apply-pricing-rules');
            
            // Transport Pricing Rules Management
            Route::get('transport-pricing-rules', [\App\Http\Controllers\B2B\TransportPricingRuleController::class, 'index'])->name('transport-pricing-rules.index');
            Route::post('transport-pricing-rules', [\App\Http\Controllers\B2B\TransportPricingRuleController::class, 'store'])->name('transport-pricing-rules.store');
            Route::get('transport-pricing-rules/{transportPricingRule}', [\App\Http\Controllers\B2B\TransportPricingRuleController::class, 'show'])->name('transport-pricing-rules.show');
            Route::put('transport-pricing-rules/{transportPricingRule}', [\App\Http\Controllers\B2B\TransportPricingRuleController::class, 'update'])->name('transport-pricing-rules.update');
            Route::delete('transport-pricing-rules/{transportPricingRule}', [\App\Http\Controllers\B2B\TransportPricingRuleController::class, 'destroy'])->name('transport-pricing-rules.destroy');
            Route::patch('transport-pricing-rules/{transportPricingRule}/toggle-status', [\App\Http\Controllers\B2B\TransportPricingRuleController::class, 'toggleStatus'])->name('transport-pricing-rules.toggle-status');
            Route::post('transport-pricing-rules/update-priority', [\App\Http\Controllers\B2B\TransportPricingRuleController::class, 'updatePriority'])->name('transport-pricing-rules.update-priority');
            Route::post('transport-pricing-rules/preview', [\App\Http\Controllers\B2B\TransportPricingRuleController::class, 'previewRule'])->name('transport-pricing-rules.preview');
            Route::get('transport-pricing-rules/templates', [\App\Http\Controllers\B2B\TransportPricingRuleController::class, 'getTemplates'])->name('transport-pricing-rules.templates');
        });
        
        // Legacy package routes (if needed for backward compatibility)
        Route::get('packages', [B2BDashboardController::class, 'packages'])->name('packages');
        Route::get('packages/create', [B2BDashboardController::class, 'createPackage'])->name('packages.create');
        Route::get('packages/{id}/edit', [B2BDashboardController::class, 'editPackage'])->name('packages.edit');
        Route::get('analytics', [B2BDashboardController::class, 'analytics'])->name('analytics');
    });
});

/*
|--------------------------------------------------------------------------
| B2B API Routes (AJAX & DataTables)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'web'])->prefix('api/b2b')->name('api.b2b.')->group(function () {
    
    // Hotel Provider API Routes
    Route::middleware(['role:hotel_provider'])->prefix('hotel-provider')->group(function () {
        Route::get('hotels/datatable', [HotelController::class, 'datatable'])->name('hotels.datatable');
        Route::patch('hotels/{hotel}/quick-toggle', [HotelController::class, 'quickToggle'])->name('hotels.quick-toggle');
        
        // Pricing Rules API Routes
        Route::get('pricing-rules/ajax', [PricingRuleController::class, 'index'])->name('pricing-rules.ajax');
        Route::post('pricing-rules/ajax', [PricingRuleController::class, 'store'])->name('pricing-rules.ajax.store');
        Route::put('pricing-rules/{pricingRule}/ajax', [PricingRuleController::class, 'update'])->name('pricing-rules.ajax.update');
        Route::delete('pricing-rules/{pricingRule}/ajax', [PricingRuleController::class, 'destroy'])->name('pricing-rules.ajax.destroy');
        
        // Room Management API Routes
        Route::get('rooms/ajax', [RoomController::class, 'index'])->name('rooms.ajax');
        Route::post('rooms/ajax', [RoomController::class, 'store'])->name('rooms.ajax.store');
        Route::put('rooms/{room}/ajax', [RoomController::class, 'update'])->name('rooms.ajax.update');
        Route::delete('rooms/{room}/ajax', [RoomController::class, 'destroy'])->name('rooms.ajax.destroy');
    });
    
    // Travel Agent API Routes - Enhanced Package Creation
    Route::middleware(['role:travel_agent'])->prefix('travel-agent')->group(function () {
        // Provider search and integration APIs
        Route::get('providers/search-hotels', [\App\Http\Controllers\B2B\API\ProviderController::class, 'searchHotels'])->name('providers.search-hotels');
        Route::get('providers/search-transport', [\App\Http\Controllers\B2B\API\ProviderController::class, 'searchTransport'])->name('providers.search-transport');
        Route::get('providers/search-flights', [\App\Http\Controllers\B2B\API\ProviderController::class, 'searchFlights'])->name('providers.search-flights');
        
        // Pricing and availability APIs
        Route::post('providers/calculate-pricing', [\App\Http\Controllers\B2B\API\ProviderController::class, 'calculatePricing'])->name('providers.calculate-pricing');
        Route::post('providers/check-availability', [\App\Http\Controllers\B2B\API\ProviderController::class, 'checkAvailability'])->name('providers.check-availability');
        
        // Package management APIs
        Route::get('packages/datatable', [\App\Http\Controllers\B2B\PackageController::class, 'datatable'])->name('packages.datatable');
        Route::post('packages/duplicate/{package}', [\App\Http\Controllers\B2B\PackageController::class, 'duplicate'])->name('packages.duplicate');
        Route::get('packages/{package}/analytics', [\App\Http\Controllers\B2B\PackageController::class, 'analytics'])->name('packages.analytics');
        
        // Activity management APIs  
        Route::post('packages/{package}/activities', [\App\Http\Controllers\B2B\API\ActivityController::class, 'store'])->name('packages.activities.store');
        Route::put('packages/{package}/activities/{activity}', [\App\Http\Controllers\B2B\API\ActivityController::class, 'update'])->name('packages.activities.update');
        Route::delete('packages/{package}/activities/{activity}', [\App\Http\Controllers\B2B\API\ActivityController::class, 'destroy'])->name('packages.activities.destroy');
        Route::post('packages/{package}/activities/reorder', [\App\Http\Controllers\B2B\API\ActivityController::class, 'reorder'])->name('packages.activities.reorder');
        
        // Legacy datatable routes
        // Route::get('bookings/datatable', [TravelAgentController::class, 'bookingsDatatable'])->name('bookings.datatable');
        // Route::get('customers/datatable', [TravelAgentController::class, 'customersDatatable'])->name('customers.datatable');
    });
    
    // Transport Provider API Routes (TODO: Create TransportController)
    // Route::middleware(['role:transport_provider'])->prefix('transport-provider')->group(function () {
    //     Route::get('vehicles/datatable', [TransportController::class, 'vehiclesDatatable'])->name('vehicles.datatable');
    //     Route::get('bookings/datatable', [TransportController::class, 'bookingsDatatable'])->name('bookings.datatable');
    // });
});
