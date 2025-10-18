<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use App\Models\TransportService;
use App\Models\TransportBooking;
use App\Models\ServiceOffer;
use App\Models\TransportPricingRule;
use App\Models\TransportRate;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * TransportProviderController
 * 
 * Handles transport provider operations for B2B users
 */
class TransportProviderController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role.redirect']);
    }
    
    /**
     * Display transport provider dashboard
     */
    public function index(): View
    {
        $provider = Auth::user();
        
        // Ensure user is a transport provider
        if (!$provider->isTransportProvider()) {
            abort(403, 'Access denied. Transport provider access required.');
        }
        
        $services = TransportService::where('provider_id', $provider->id)
                                  ->with('offers')
                                  ->latest()
                                  ->paginate(10);
        
        $stats = [
            'total_services' => TransportService::where('provider_id', $provider->id)->count(),
            'active_services' => TransportService::where('provider_id', $provider->id)->where('is_active', true)->count(),
            'total_offers' => ServiceOffer::where('provider_id', $provider->id)->where('service_type', 'transport')->count(),
            'active_offers' => ServiceOffer::where('provider_id', $provider->id)
                                         ->where('service_type', 'transport')
                                         ->where('status', 'active')
                                         ->count(),
        ];
        
        return view('b2b.transport-provider.dashboard.index', compact('services', 'stats'));
    }

    /**
     * Display all transport services for the provider
     */
    public function servicesIndex(): View
    {
        $provider = Auth::user();
        
        if (!$provider->isTransportProvider()) {
            abort(403, 'Access denied. Transport provider access required.');
        }
        
        $services = TransportService::where('provider_id', $provider->id)
                                  ->withCount('transportRates')
                                  ->latest()
                                  ->get();
        
        return view('b2b.transport-provider.services.index', compact('services'));
    }
    
    /**
     * Show the form for creating a new transport service
     */
    public function create(): View
    {
        $provider = Auth::user();
        
        if (!$provider->isTransportProvider()) {
            abort(403, 'Access denied. Transport provider access required.');
        }
        
        return view('b2b.transport-provider.services.create', [
            'transportTypes' => TransportService::TRANSPORT_TYPES,
            'routeTypes' => TransportService::ROUTE_TYPES,
        ]);
    }

    /**
     * Store a newly created transport service
     */
    public function store(Request $request): RedirectResponse
    {
        $provider = Auth::user();
        
        if (!$provider->isTransportProvider()) {
            abort(403, 'Access denied. Transport provider access required.');
        }
        
        $validated = $request->validate([
            'service_name' => 'required|string|max:255',
            'transport_type' => ['required', Rule::in(TransportService::TRANSPORT_TYPES)],
            'route_type' => ['nullable', Rule::in(TransportService::ROUTE_TYPES)], // Made optional since routes are defined below
            'price' => 'required|numeric|min:0', // Added price field
            'routes' => 'required|array|min:1', // At least one route is required
            'routes.*.from' => 'required|string|max:255',
            'routes.*.to' => 'required|string|max:255',
            'routes.*.duration' => 'required|integer|min:1',
            'specifications' => 'nullable|string', // This will be JSON string
            'max_passengers' => 'required|integer|min:1',
            'pickup_locations' => 'nullable|string', // This will be JSON string
            'dropoff_locations' => 'nullable|string', // This will be JSON string
            'operating_hours' => 'nullable|array',
            'operating_hours.start' => 'nullable|date_format:H:i',
            'operating_hours.end' => 'nullable|date_format:H:i',
            'operating_hours.is_24_7' => 'nullable|string',
            'policies' => 'nullable|string', // This will be JSON string
            'contact_info' => 'nullable|array',
            'contact_info.phone' => 'nullable|string|max:20',
            'contact_info.email' => 'nullable|email|max:255',
            'images' => 'nullable|array',
        ]);
        
        // Process JSON string fields
        if (!empty($validated['specifications'])) {
            $validated['specifications'] = json_decode($validated['specifications'], true) ?: [];
        }
        if (!empty($validated['pickup_locations'])) {
            $validated['pickup_locations'] = json_decode($validated['pickup_locations'], true) ?: [];
        }
        if (!empty($validated['dropoff_locations'])) {
            $validated['dropoff_locations'] = json_decode($validated['dropoff_locations'], true) ?: [];
        }
        if (!empty($validated['policies'])) {
            $validated['policies'] = json_decode($validated['policies'], true) ?: [];
        }
        
        $validated['provider_id'] = $provider->id;
        $validated['is_active'] = true; // New services are active by default
        
        $service = TransportService::create($validated);
        
        return redirect()->route('b2b.transport-provider.show', $service->id)
                        ->with('success', 'Transport service created successfully!');
    }

    /**
     * Display the specified transport service
     */
    public function show(TransportService $transportService): View
    {
        $provider = Auth::user();
        
        // Ensure the service belongs to the current provider
        if ($transportService->provider_id !== $provider->id) {
            abort(404);
        }
        
        $transportService->load('offers');
        
        return view('b2b.transport-provider.services.show', compact('transportService'));
    }

    /**
     * Show the form for editing the transport service
     */
    public function edit(TransportService $transportService): View
    {
        $provider = Auth::user();
        
        // Ensure the service belongs to the current provider
        if ($transportService->provider_id !== $provider->id) {
            abort(404);
        }
        
        return view('b2b.transport-provider.services.edit', [
            'transportService' => $transportService,
            'transportTypes' => TransportService::TRANSPORT_TYPES,
            'routeTypes' => TransportService::ROUTE_TYPES,
        ]);
    }

    /**
     * Update the transport service
     */
    public function update(Request $request, TransportService $transportService): RedirectResponse
    {
        $provider = Auth::user();
        
        // Ensure the service belongs to the current provider
        if ($transportService->provider_id !== $provider->id) {
            abort(404);
        }
        
        $validated = $request->validate([
            'service_name' => 'required|string|max:255',
            'transport_type' => ['required', Rule::in(TransportService::TRANSPORT_TYPES)],
            'route_type' => ['nullable', Rule::in(TransportService::ROUTE_TYPES)], // Made optional since routes are defined below
            'price' => 'required|numeric|min:0', // Added price field
            'routes' => 'required|array|min:1', // At least one route is required
            'routes.*.from' => 'required|string|max:255',
            'routes.*.to' => 'required|string|max:255',
            'routes.*.duration' => 'required|integer|min:1',
            'specifications' => 'nullable|string', // This will be JSON string
            'max_passengers' => 'required|integer|min:1',
            'pickup_locations' => 'nullable|string', // This will be JSON string
            'dropoff_locations' => 'nullable|string', // This will be JSON string
            'operating_hours' => 'nullable|array',
            'operating_hours.start' => 'nullable|date_format:H:i',
            'operating_hours.end' => 'nullable|date_format:H:i',
            'operating_hours.is_24_7' => 'nullable|string',
            'policies' => 'nullable|string', // This will be JSON string
            'contact_info' => 'nullable|array',
            'contact_info.phone' => 'nullable|string|max:20',
            'contact_info.email' => 'nullable|email|max:255',
            'images' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);
        
        // Process JSON string fields
        if (!empty($validated['specifications'])) {
            $validated['specifications'] = json_decode($validated['specifications'], true) ?: [];
        }
        if (!empty($validated['pickup_locations'])) {
            $validated['pickup_locations'] = json_decode($validated['pickup_locations'], true) ?: [];
        }
        if (!empty($validated['dropoff_locations'])) {
            $validated['dropoff_locations'] = json_decode($validated['dropoff_locations'], true) ?: [];
        }
        if (!empty($validated['policies'])) {
            $validated['policies'] = json_decode($validated['policies'], true) ?: [];
        }
        
        // Convert is_active to boolean if present
        if (isset($validated['is_active'])) {
            $validated['is_active'] = (bool) $validated['is_active'];
        }
        
        $transportService->update($validated);
        
        return redirect()->route('b2b.transport-provider.show', $transportService->id)
                        ->with('success', 'Transport service updated successfully!');
    }

    /**
     * Display pricing management for transport services
     */
    public function pricing(): View
    {
        $provider = Auth::user();
        
        if (!$provider->isTransportProvider()) {
            abort(403, 'Access denied. Transport provider access required.');
        }
        
        // Get services with their pricing rules and rates
        $services = TransportService::where('provider_id', $provider->id)
                                  ->with(['transportPricingRules', 'transportRates'])
                                  ->get();
        
        // Get pricing rules
        $pricingRules = TransportPricingRule::where('provider_id', $provider->id)
                                          ->with('transportService')
                                          ->latest()
                                          ->get();
        
        // Get current rates for next 30 days
        $rates = TransportRate::where('transport_service_id', $provider->id)
                             ->whereBetween('date', [now(), now()->addDays(30)])
                             ->with('transportService')
                             ->orderBy('date')
                             ->get();
        
        $pricingStats = [
            'total_services' => $services->count(),
            'active_rules' => $pricingRules->where('is_active', true)->count(),
            'total_routes' => $services->sum(function ($services) {
                return is_array($services->routes) ? count($services->routes) : 0;
            }),
            'upcoming_rates' => $rates->count(),
            'average_base_rate' => $rates->avg('base_rate') ?? 0,
        ];
        
        return view('b2b.transport-provider.rates.index', compact('services', 'pricingRules', 'rates', 'pricingStats'));
    }
    
    /**
     * Display pricing rules management page
     */
    public function pricingRules(): View
    {
        $provider = Auth::user();
        
        if (!$provider->isTransportProvider()) {
            abort(403, 'Access denied. Transport provider access required.');
        }
        
        $services = TransportService::where('provider_id', $provider->id)
                                  ->select(['id', 'service_name', 'transport_type'])
                                  ->get();
        
        return view('b2b.transport-provider.pricing-rules.index', compact('services'));
    }

    /**
     * Update route-specific pricing
     */
    public function updateRoutePricing(Request $request, TransportService $transportService): RedirectResponse
    {
        $provider = Auth::user();
        
        // Ensure the service belongs to the current provider
        if ($transportService->provider_id !== $provider->id) {
            abort(403, 'Unauthorized access to transport service.');
        }
        
        $validated = $request->validate([
            'route_pricing' => 'required|array',
            'route_pricing.*.route_index' => 'required|integer|min:0',
            'route_pricing.*.price' => 'required|numeric|min:0',
            'route_pricing.*.currency' => 'required|string|max:3',
        ]);
        
        $routes = $transportService->routes ?? [];
        
        foreach ($validated['route_pricing'] as $pricing) {
            $routeIndex = $pricing['route_index'];
            if (isset($routes[$routeIndex])) {
                $routes[$routeIndex]['price'] = $pricing['price'];
                $routes[$routeIndex]['currency'] = $pricing['currency'];
            }
        }
        
        $transportService->update(['routes' => $routes]);
        
        return redirect()->back()
            ->with('success', 'Route pricing updated successfully!');
    }

    /**
     * Create seasonal pricing rule
     */
    public function createSeasonalPricing(Request $request, TransportService $transportService): RedirectResponse
    {
        $provider = Auth::user();
        
        // Ensure the service belongs to the current provider
        if ($transportService->provider_id !== $provider->id) {
            abort(403, 'Unauthorized access to transport service.');
        }
        
        $validated = $request->validate([
            'season_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'price_adjustment_type' => 'required|in:percentage,fixed,amount',
            'price_adjustment_value' => 'required|numeric',
            'applies_to_routes' => 'required|array',
            'applies_to_routes.*' => 'integer|min:0',
        ]);
        
        // Get existing pricing rules
        $pricingRules = $transportService->pricing_rules ?? [];
        
        // Add new seasonal rule
        $pricingRules[] = [
            'type' => 'seasonal',
            'name' => $validated['season_name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'adjustment_type' => $validated['price_adjustment_type'],
            'adjustment_value' => $validated['price_adjustment_value'],
            'applies_to_routes' => $validated['applies_to_routes'],
            'created_at' => now()->toISOString(),
        ];
        
        $transportService->update(['pricing_rules' => $pricingRules]);
        
        return redirect()->back()
            ->with('success', 'Seasonal pricing rule created successfully!');
    }

    /**
     * Calculate dynamic price for a route
     */
    public function calculateDynamicPrice(TransportService $service, int $routeIndex, \Carbon\Carbon $date = null): float
    {
        $date = $date ?? now();
        $routes = $service->routes ?? [];
        
        if (!isset($routes[$routeIndex])) {
            return 0;
        }
        
        $basePrice = $routes[$routeIndex]['price'] ?? 0;
        $pricingRules = $service->pricing_rules ?? [];
        
        foreach ($pricingRules as $rule) {
            if ($rule['type'] === 'seasonal' && 
                in_array($routeIndex, $rule['applies_to_routes']) &&
                $date->between($rule['start_date'], $rule['end_date'])) {
                
                switch ($rule['adjustment_type']) {
                    case 'percentage':
                        $basePrice += ($basePrice * ($rule['adjustment_value'] / 100));
                        break;
                    case 'amount':
                        $basePrice += $rule['adjustment_value'];
                        break;
                    case 'fixed':
                        $basePrice = $rule['adjustment_value'];
                        break;
                }
            }
        }
        
        return $basePrice;
    }

    /**
     * Get pricing analytics for transport services
     */
    public function pricingAnalytics(): \Illuminate\Http\JsonResponse
    {
        $provider = Auth::user();
        
        if (!$provider->isTransportProvider()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $services = TransportService::where('provider_id', $provider->id)->get();
        
        $analytics = [
            'revenue_by_service' => [],
            'popular_routes' => [],
            'price_trends' => [],
        ];
        
        foreach ($services as $service) {
            $routes = $service->routes ?? [];
            foreach ($routes as $index => $route) {
                $analytics['popular_routes'][] = [
                    'service_name' => $service->service_name,
                    'route' => $route['from'] . ' → ' . $route['to'],
                    'price' => $route['price'] ?? 0,
                    'bookings' => 0, // TODO: Implement booking tracking
                ];
            }
        }
        
        return response()->json($analytics);
    }

    /**
     * Display transport bookings management page
     */
    public function bookings(Request $request): View
    {
        $provider = Auth::user();
        
        if (!$provider->isTransportProvider()) {
            abort(403, 'Access denied. Transport provider access required.');
        }
        
        // Get bookings for this provider's transport services
        $bookingsQuery = TransportBooking::whereHas('transportService', function ($query) use ($provider) {
            $query->where('provider_id', $provider->id);
        })->with(['transportService', 'customer']);
        
        // Apply filters
        if ($request->has('status') && $request->status !== '') {
            $bookingsQuery->where('status', $request->status);
        }
        
        if ($request->has('payment_status') && $request->payment_status !== '') {
            $bookingsQuery->where('payment_status', $request->payment_status);
        }
        
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $bookingsQuery->where(function ($query) use ($search) {
                $query->where('booking_reference', 'like', "%{$search}%")
                      ->orWhere('passenger_name', 'like', "%{$search}%")
                      ->orWhere('pickup_location', 'like', "%{$search}%")
                      ->orWhere('dropoff_location', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('date_from') && $request->date_from !== '') {
            $bookingsQuery->whereDate('pickup_datetime', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to !== '') {
            $bookingsQuery->whereDate('pickup_datetime', '<=', $request->date_to);
        }
        
        $bookings = $bookingsQuery->orderBy('pickup_datetime', 'asc')->paginate(15);
        
        // Get statistics
        $stats = [
            'total_bookings' => TransportBooking::whereHas('transportService', function ($query) use ($provider) {
                $query->where('provider_id', $provider->id);
            })->count(),
            
            'confirmed_bookings' => TransportBooking::whereHas('transportService', function ($query) use ($provider) {
                $query->where('provider_id', $provider->id);
            })->where('status', 'confirmed')->count(),
            
            'pending_bookings' => TransportBooking::whereHas('transportService', function ($query) use ($provider) {
                $query->where('provider_id', $provider->id);
            })->where('status', 'pending')->count(),
            
            'total_revenue' => TransportBooking::whereHas('transportService', function ($query) use ($provider) {
                $query->where('provider_id', $provider->id);
            })->sum('total_amount'),
            
            'paid_amount' => TransportBooking::whereHas('transportService', function ($query) use ($provider) {
                $query->where('provider_id', $provider->id);
            })->sum('paid_amount'),
        ];
        
        return view('b2b.transport-provider.operations.bookings', compact('bookings', 'stats'));
    }
    
    /**
     * Show specific transport booking details
     */
    public function showBooking(TransportBooking $booking): View
    {
        $provider = Auth::user();
        
        // Ensure the booking belongs to this provider's transport service
        if ($booking->transportService->provider_id !== $provider->id) {
            abort(404);
        }
        
        $booking->load(['transportService', 'customer', 'payments']);
        
        return view('b2b.transport-provider.operations.booking-details', compact('booking'));
    }
    
    /**
     * Confirm a transport booking
     */
    public function confirmBooking(Request $request, TransportBooking $booking)
    {
        $provider = Auth::user();
        
        // Ensure the booking belongs to this provider's transport service
        if ($booking->transportService->provider_id !== $provider->id) {
            abort(404);
        }
        
        $validated = $request->validate([
            'vehicle_details' => 'nullable|array',
            'driver_details' => 'nullable|array',
            'pickup_notes' => 'nullable|string|max:500',
        ]);
        
        $booking->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'vehicle_details' => $validated['vehicle_details'] ?? null,
            'driver_details' => $validated['driver_details'] ?? null,
            'notes' => ($booking->notes ? $booking->notes . "\n\n" : '') . ($validated['pickup_notes'] ?? ''),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Booking confirmed successfully!',
            'booking' => $booking->fresh(['transportService', 'customer'])
        ]);
    }
    
    /**
     * Start a transport booking (mark as in progress)
     */
    public function startBooking(TransportBooking $booking)
    {
        $provider = Auth::user();
        
        // Ensure the booking belongs to this provider's transport service
        if ($booking->transportService->provider_id !== $provider->id) {
            abort(404);
        }
        
        if ($booking->status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Only confirmed bookings can be started.'
            ], 422);
        }
        
        $booking->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Trip started successfully!',
            'booking' => $booking->fresh(['transportService', 'customer'])
        ]);
    }
    
    /**
     * Complete a transport booking
     */
    public function completeBooking(TransportBooking $booking)
    {
        $provider = Auth::user();
        
        // Ensure the booking belongs to this provider's transport service
        if ($booking->transportService->provider_id !== $provider->id) {
            abort(404);
        }
        
        if ($booking->status !== 'in_progress') {
            return response()->json([
                'success' => false,
                'message' => 'Only in-progress bookings can be completed.'
            ], 422);
        }
        
        $booking->update([
            'status' => 'completed',
            'completed_at' => now(),
            'dropoff_datetime' => now(), // Set actual dropoff time
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Trip completed successfully!',
            'booking' => $booking->fresh(['transportService', 'customer'])
        ]);
    }
    
    /**
     * Cancel a transport booking
     */
    public function cancelBooking(Request $request, TransportBooking $booking)
    {
        $provider = Auth::user();
        
        // Ensure the booking belongs to this provider's transport service
        if ($booking->transportService->provider_id !== $provider->id) {
            abort(404);
        }
        
        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);
        
        if (in_array($booking->status, ['completed', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'This booking cannot be cancelled.'
            ], 422);
        }
        
        $booking->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $validated['cancellation_reason'],
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully!',
            'booking' => $booking->fresh(['transportService', 'customer'])
        ]);
    }
    
    /**
     * Get bookings data for DataTables (AJAX)
     */
    public function bookingsData(Request $request)
    {
        $provider = Auth::user();
        
        if (!$provider->isTransportProvider()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $bookings = TransportBooking::whereHas('transportService', function ($query) use ($provider) {
            $query->where('provider_id', $provider->id);
        })->with(['transportService', 'customer']);
        
        // Apply DataTables filtering
        if ($request->has('search') && $request->search['value'] !== '') {
            $search = $request->search['value'];
            $bookings->where(function ($query) use ($search) {
                $query->where('booking_reference', 'like', "%{$search}%")
                      ->orWhere('passenger_name', 'like', "%{$search}%")
                      ->orWhere('pickup_location', 'like', "%{$search}%")
                      ->orWhere('dropoff_location', 'like', "%{$search}%");
            });
        }
        
        $totalRecords = $bookings->count();
        
        // Apply DataTables pagination
        if ($request->has('start') && $request->has('length')) {
            $bookings->skip($request->start)->take($request->length);
        }
        
        // Apply DataTables ordering
        if ($request->has('order')) {
            $columns = ['id', 'booking_reference', 'passenger_name', 'pickup_datetime', 'status', 'total_amount'];
            $orderColumn = $columns[$request->order[0]['column']] ?? 'pickup_datetime';
            $orderDir = $request->order[0]['dir'] ?? 'asc';
            $bookings->orderBy($orderColumn, $orderDir);
        }
        
        $bookings = $bookings->get();
        
        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $bookings->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'service_name' => $booking->transportService->service_name ?? 'N/A',
                    'passenger_name' => $booking->passenger_name,
                    'route' => $booking->pickup_location . ' → ' . $booking->dropoff_location,
                    'pickup_datetime' => $booking->pickup_datetime->format('M j, Y g:i A'),
                    'passengers' => $booking->total_passengers,
                    'status' => $booking->status,
                    'status_label' => $booking->status_label,
                    'payment_status' => $booking->payment_status_label,
                    'total_amount' => $booking->currency . ' ' . number_format($booking->total_amount, 2),
                    'actions' => [
                        'view' => route('b2b.transport-provider.bookings.show', $booking),
                        'confirm' => $booking->status === 'pending' ? route('b2b.transport-provider.bookings.confirm', $booking) : null,
                        'start' => $booking->status === 'confirmed' ? route('b2b.transport-provider.bookings.start', $booking) : null,
                        'complete' => $booking->status === 'in_progress' ? route('b2b.transport-provider.bookings.complete', $booking) : null,
                        'cancel' => !in_array($booking->status, ['completed', 'cancelled']) ? route('b2b.transport-provider.bookings.cancel', $booking) : null,
                    ]
                ];
            })
        ]);
    }

    /**
     * Remove the transport service
     */
    public function destroy(TransportService $transportService): RedirectResponse
    {
        $provider = Auth::user();
        
        // Ensure the service belongs to the current provider
        if ($transportService->provider_id !== $provider->id) {
            abort(404);
        }
        
        $transportService->delete();
        
        return redirect()->route('b2b.transport-provider.dashboard')
                        ->with('success', 'Transport service deleted successfully!');
    }
    
    /**
     * Toggle service active status
     */
    public function toggleStatus(TransportService $transportService): RedirectResponse
    {
        $provider = Auth::user();
        
        // Ensure the service belongs to the current provider
        if ($transportService->provider_id !== $provider->id) {
            abort(404);
        }
        
        $transportService->update([
            'is_active' => !$transportService->is_active
        ]);
        
        $status = $transportService->is_active ? 'activated' : 'deactivated';
        
        return back()->with('success', "Transport service {$status} successfully!");
    }
}
