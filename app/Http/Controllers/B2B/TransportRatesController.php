<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use App\Models\TransportRate;
use App\Models\TransportService;
use App\Models\TransportPricingRule;
use App\Services\PricingRuleEngine;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TransportRatesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:transport_provider']);
    }

    /**
     * Display the rates management page
     */
    public function index()
    {
        $provider = Auth::user();
        
        // Ensure user is a transport provider
        if (!$provider || !$provider->isTransportProvider()) {
            abort(403, 'Unauthorized. This page is for transport providers only.');
        }
        
        $services = TransportService::where('provider_id', $provider->id)
                                  ->with(['transportRates'])
                                  ->get();
        
        // Also get pricing rules for the provider
        $pricingRules = TransportPricingRule::where('provider_id', $provider->id)
                                           ->where('is_active', true)
                                           ->orderBy('priority', 'desc')
                                           ->get();
        
        return view('b2b.transport-provider.rates', compact('services', 'pricingRules'));
    }

    /**
     * Get rates for a transport service within date range
     */
    public function getRates(Request $request)
    {
        $provider = Auth::user();
        
        if (!$provider || !$provider->isTransportProvider()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'service_id' => 'required|exists:transport_services,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        
        $service = TransportService::where('id', $request->service_id)
                                 ->where('provider_id', $provider->id)
                                 ->first();
        
        if (!$service) {
            return response()->json(['error' => 'Service not found'], 404);
        }
        
        $rates = TransportRate::where('transport_service_id', $service->id)
                             ->whereBetween('date', [$request->start_date, $request->end_date])
                             ->orderBy('date')
                             ->get();
        
        return response()->json([
            'success' => true,
            'rates' => $rates,
            'service' => $service
        ]);
    }
    
    /**
     * Store individual transport service rate
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:transport_services,id',
            'route_from' => 'required|string|max:255',
            'route_to' => 'required|string|max:255',
            'passenger_type' => 'required|in:adult,child,infant',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'base_rate' => 'required|numeric|min:0|max:99999',
            'currency' => 'required|string|size:3',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $provider = Auth::user();
            $service = TransportService::where('id', $request->service_id)
                                     ->where('provider_id', $provider->id)
                                     ->firstOrFail();
            
            // Generate date range
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $dates = [];
            
            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate)) {
                $dates[] = $currentDate->format('Y-m-d');
                $currentDate->addDay();
            }

            // Delete existing rates for these dates
            TransportRate::where('transport_service_id', $service->id)
                ->where('route_from', $request->route_from)
                ->where('route_to', $request->route_to)
                ->where('passenger_type', $request->passenger_type)
                ->whereIn('date', $dates)
                ->delete();

            // Insert new rates
            $rateData = [];
            foreach ($dates as $date) {
                $rateData[] = [
                    'transport_service_id' => $service->id,
                    'provider_id' => $provider->id,
                    'date' => $date,
                    'route_from' => $request->route_from,
                    'route_to' => $request->route_to,
                    'passenger_type' => $request->passenger_type,
                    'base_rate' => $request->base_rate,
                    'currency' => $request->currency,
                    'notes' => $request->notes,
                    'is_available' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            TransportRate::insert($rateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transport rate updated successfully for ' . count($dates) . ' days',
                'data' => [
                    'service_id' => $service->id,
                    'dates_count' => count($dates),
                    'base_rate' => $request->base_rate
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update transport rate: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store group rates for multiple routes/services
     */
    public function storeGroupRate(Request $request)
    {
        // Create base validation rules
        $rules = [
            'group_key' => 'required|string',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'rate_type' => 'required|in:fixed,base_plus,base_percentage',
            'currency' => 'required|string|size:3',
            'notes' => 'nullable|string|max:500',
            'apply_to_all' => 'nullable|in:0,1,true,false',
            'override_existing' => 'nullable|in:0,1,true,false',
            'adjustment_direction' => 'nullable|in:increase,decrease',
            'selected_routes' => 'nullable|array',
        ];
        
        // Add conditional base_rate validation based on rate_type
        $rateType = $request->input('rate_type');
        if ($rateType === 'fixed') {
            // Fixed rates must be positive
            $rules['base_rate'] = 'required|numeric|min:0|max:99999';
        } else {
            // base_plus and base_percentage can be negative for discounts
            $rules['base_rate'] = 'required|numeric|min:-99999|max:99999';
        }
        
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $provider = Auth::user();
            
            // Convert checkbox values to proper booleans with defaults
            $applyToAll = $request->has('apply_to_all') ? in_array($request->apply_to_all, [1, '1', true, 'true'], true) : false;
            $overrideExisting = $request->has('override_existing') ? in_array($request->override_existing, [1, '1', true, 'true'], true) : false;

            // Parse group key to get service and route criteria
            $groupCriteria = $this->parseGroupKey($request->group_key);
            
            // Get routes for this group
            $service = TransportService::where('id', $groupCriteria['service_id'])
                                     ->where('provider_id', $provider->id)
                                     ->firstOrFail();
            
            $availableRoutes = $service->routes ?? [];
            $targetRoutes = [];
            
            if (!$applyToAll && !empty($request->selected_routes)) {
                foreach ($request->selected_routes as $selectedRoute) {
                    foreach ($availableRoutes as $route) {
                        if (isset($route['from']) && isset($route['to']) && 
                            $route['from'] . '|' . $route['to'] === $selectedRoute) {
                            $targetRoutes[] = $route;
                        }
                    }
                }
            } else {
                $targetRoutes = $availableRoutes;
            }

            if (empty($targetRoutes)) {
                throw new \Exception('No routes found for the selected criteria');
            }

            // Create dates array
            $dates = [];
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            
            while ($startDate <= $endDate) {
                $dates[] = $startDate->format('Y-m-d');
                $startDate->addDay();
            }

            // Process each route and date combination
            $totalRatesCreated = 0;
            $passengerTypes = ['adult', 'child', 'infant'];
            
            foreach ($targetRoutes as $route) {
                foreach ($dates as $date) {
                    foreach ($passengerTypes as $passengerType) {
                        // Check if rate exists and whether to override
                        if (!$overrideExisting) {
                            $existingRate = TransportRate::where([
                                'transport_service_id' => $service->id,
                                'route_from' => $route['from'],
                                'route_to' => $route['to'],
                                'date' => $date,
                                'passenger_type' => $passengerType
                            ])->first();

                            if ($existingRate) {
                                continue; // Skip if rate exists and override is disabled
                            }
                        }

                        // Calculate final rate based on rate type
                        $finalRate = $this->calculateFinalRate(
                            $service->price ?? 0, 
                            $request->base_rate, 
                            $request->rate_type,
                            $request->adjustment_direction ?? 'increase'
                        );
                        
                        // Create or update rate
                        TransportRate::updateOrCreate(
                            [
                                'transport_service_id' => $service->id,
                                'provider_id' => $provider->id,
                                'route_from' => $route['from'],
                                'route_to' => $route['to'],
                                'date' => $date,
                                'passenger_type' => $passengerType
                            ],
                            [
                                'base_rate' => $finalRate,
                                'currency' => $request->currency,
                                'notes' => $request->notes,
                                'is_available' => true,
                                'updated_at' => now()
                            ]
                        );
                        
                        $totalRatesCreated++;
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Group rates updated successfully for {$totalRatesCreated} rate records across " . count($targetRoutes) . " routes and " . count($dates) . " days",
                'data' => [
                    'service_id' => $service->id,
                    'routes_affected' => count($targetRoutes),
                    'dates_count' => count($dates),
                    'total_rates_created' => $totalRatesCreated,
                    'base_rate' => $request->base_rate
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update group rates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get routes for a specific group key
     */
    public function getGroupRoutes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_key' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $provider = Auth::user();
            $groupCriteria = $this->parseGroupKey($request->group_key);
            
            $service = TransportService::where('id', $groupCriteria['service_id'])
                                     ->where('provider_id', $provider->id)
                                     ->firstOrFail();
            
            $routes = $service->routes ?? [];
            $routeData = [];
            
            foreach ($routes as $index => $route) {
                if (isset($route['from']) && isset($route['to'])) {
                    $routeData[] = [
                        'key' => $route['from'] . '|' . $route['to'],
                        'from' => $route['from'],
                        'to' => $route['to'],
                        'duration' => $route['duration'] ?? null,
                        'distance' => $route['distance'] ?? null,
                        'service_name' => $service->service_name,
                        'current_rate' => $this->getCurrentRateForRoute($service->id, $route['from'], $route['to'])
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $routeData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch routes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get rate history for a service group
     */
    public function getGroupHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:transport_services,id',
            'group_key' => 'required|string',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $provider = Auth::user();
            $service = TransportService::where('id', $request->service_id)
                                     ->where('provider_id', $provider->id)
                                     ->firstOrFail();
            
            $limit = $request->limit ?? 50;
            
            // Get all rates for this service (group history shows all routes)
            $rateHistory = TransportRate::where('transport_service_id', $service->id)
                ->where('date', '<=', now()->format('Y-m-d')) // Only past and current rates for history
                ->orderBy('date', 'desc')
                ->orderBy('updated_at', 'desc')
                ->limit($limit)
                ->get(['date', 'route_from', 'route_to', 'passenger_type', 'base_rate', 'currency', 'notes', 'is_available', 'created_at', 'updated_at']);

            return response()->json([
                'success' => true,
                'data' => [
                    'service' => [
                        'id' => $service->id,
                        'service_name' => $service->service_name,
                        'transport_type' => $service->transport_type,
                        'route' => 'All Routes',
                        'passenger_type' => 'All Types'
                    ],
                    'history' => $rateHistory->map(function($rate) {
                        return [
                            'date' => $rate->date,
                            'route' => $rate->route_from . ' → ' . $rate->route_to . ' (' . ucfirst($rate->passenger_type) . ')',
                            'base_rate' => $rate->base_rate,
                            'currency' => $rate->currency,
                            'notes' => $rate->notes,
                            'is_available' => $rate->is_available,
                            'created_at' => $rate->created_at->format('Y-m-d H:i:s'),
                            'updated_at' => $rate->updated_at->format('Y-m-d H:i:s')
                        ];
                    })
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch group history: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get rate history for a route
     */
    public function getRateHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:transport_services,id',
            'route_from' => 'required|string',
            'route_to' => 'required|string',
            'passenger_type' => 'required|in:adult,child,infant',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $provider = Auth::user();
            $service = TransportService::where('id', $request->service_id)
                                     ->where('provider_id', $provider->id)
                                     ->firstOrFail();
            
            $limit = $request->limit ?? 50;

            $rateHistory = TransportRate::where('transport_service_id', $service->id)
                ->where('route_from', $request->route_from)
                ->where('route_to', $request->route_to)
                ->where('passenger_type', $request->passenger_type)
                ->orderBy('date', 'desc')
                ->limit($limit)
                ->get(['date', 'base_rate', 'currency', 'notes', 'is_available', 'created_at', 'updated_at']);

            return response()->json([
                'success' => true,
                'data' => [
                    'service' => [
                        'id' => $service->id,
                        'service_name' => $service->service_name,
                        'transport_type' => $service->transport_type,
                        'route' => $request->route_from . ' → ' . $request->route_to,
                        'passenger_type' => $request->passenger_type
                    ],
                    'history' => $rateHistory->map(function($rate) {
                        return [
                            'date' => $rate->date,
                            'base_rate' => $rate->base_rate,
                            'currency' => $rate->currency,
                            'notes' => $rate->notes,
                            'is_available' => $rate->is_available,
                            'created_at' => $rate->created_at->format('Y-m-d H:i:s'),
                            'updated_at' => $rate->updated_at->format('Y-m-d H:i:s')
                        ];
                    })
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch rate history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get calendar rates for a specific route
     */
    public function getCalendarRates(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:transport_services,id',
            'route_from' => 'nullable|string',
            'route_to' => 'nullable|string',
            'passenger_type' => 'nullable|in:adult,child,infant',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $provider = Auth::user();
            
            // Get and validate the service
            $service = TransportService::where('id', $request->service_id)
                                     ->where('provider_id', $provider->id)
                                     ->firstOrFail();
            
            // Start building the query for the specific service
            $query = TransportRate::where('transport_service_id', $service->id)
                                 ->where('is_available', true);
            
            // Filter by route if specified
            if ($request->route_from) {
                $query->where('route_from', $request->route_from);
            }
            
            if ($request->route_to) {
                $query->where('route_to', $request->route_to);
            }
            
            // Filter by passenger type if specified
            if ($request->passenger_type) {
                $query->where('passenger_type', $request->passenger_type);
            }
            
            // Filter by date range
            if ($request->start_date) {
                $query->where('date', '>=', $request->start_date);
            }

            if ($request->end_date) {
                $query->where('date', '<=', $request->end_date);
            }

            // Get existing rates from database
            $existingRates = $query->orderBy('date', 'asc')
                          ->get(['date', 'base_rate', 'currency', 'notes', 'transport_service_id', 'route_from', 'route_to', 'passenger_type']);
            
            // Start with all existing database rates
            $allRates = collect();
            
            // Add all existing rates first (these have priority)
            foreach ($existingRates as $rate) {
                $allRates->push([
                    'date' => $rate->date,
                    'base_rate' => $rate->base_rate,
                    'currency' => $rate->currency,
                    'notes' => $rate->notes,
                    'service_id' => $rate->transport_service_id,
                    'route_from' => $rate->route_from,
                    'route_to' => $rate->route_to,
                    'passenger_type' => $rate->passenger_type,
                    'route_display' => $rate->route_from . ' → ' . $rate->route_to,
                    'rate_source' => 'database'
                ]);
            }
            
            // Now add pricing rule rates for dates/routes where no database rate exists
            $routes = $service->routes ?? [];
            $passengerTypes = ['adult', 'child', 'infant'];
            
            // Generate date range for pricing rule calculation
            $startDate = Carbon::parse($request->start_date ?? now()->subDays(30));
            $endDate = Carbon::parse($request->end_date ?? now()->addDays(90));
            
            foreach ($routes as $route) {
                if (!isset($route['from'], $route['to'])) continue;
                
                // Apply route filter if specified
                if ($request->route_from && $route['from'] !== $request->route_from) continue;
                if ($request->route_to && $route['to'] !== $request->route_to) continue;
                
                foreach ($passengerTypes as $passengerType) {
                    // Apply passenger type filter if specified
                    if ($request->passenger_type && $passengerType !== $request->passenger_type) continue;
                    
                    $currentDate = $startDate->copy();
                    while ($currentDate->lte($endDate)) {
                        $dateStr = $currentDate->format('Y-m-d');
                        
                        // Check if we already have a database rate for this date/route/passenger combination
                        $hasExistingRate = $allRates->first(function($rate) use ($dateStr, $route, $passengerType) {
                            return $rate['date'] === $dateStr && 
                                   $rate['route_from'] === $route['from'] && 
                                   $rate['route_to'] === $route['to'] && 
                                   $rate['passenger_type'] === $passengerType &&
                                   $rate['rate_source'] === 'database';
                        });
                        
                        // Only add pricing rule rate if no database rate exists
                        if (!$hasExistingRate) {
                            // Check if pricing rules apply for this date/route
                            $applicableRules = $this->getApplicablePricingRules($service, $currentDate, $route['from'], $route['to']);
                            
                            if ($applicableRules->isNotEmpty()) {
                                // Calculate rate with pricing rules
                                $basePrice = $service->price ?? 100;
                                $adjustedPrice = $this->calculatePriceWithRules($basePrice, $applicableRules);
                                
                                $allRates->push([
                                    'date' => $dateStr,
                                    'base_rate' => $adjustedPrice,
                                    'currency' => 'SAR', // Default currency
                                    'notes' => $this->generatePricingRuleNotes($applicableRules),
                                    'service_id' => $service->id,
                                    'route_from' => $route['from'],
                                    'route_to' => $route['to'],
                                    'passenger_type' => $passengerType,
                                    'route_display' => $route['from'] . ' → ' . $route['to'],
                                    'rate_source' => 'pricing_rules'
                                ]);
                            }
                        }
                        
                        $currentDate->addDay();
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $allRates->sortBy('date')->values()->all()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch calendar rates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear rates for a route group
     */
    public function clearGroupRates(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_key' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $provider = Auth::user();
            $groupCriteria = $this->parseGroupKey($request->group_key);
            
            $service = TransportService::where('id', $groupCriteria['service_id'])
                                     ->where('provider_id', $provider->id)
                                     ->firstOrFail();

            $query = TransportRate::where('transport_service_id', $service->id);

            if ($request->start_date) {
                $query->where('date', '>=', $request->start_date);
            }

            if ($request->end_date) {
                $query->where('date', '<=', $request->end_date);
            }

            $deletedCount = $query->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Cleared {$deletedCount} rate entries from service routes",
                'data' => [
                    'deleted_rates' => $deletedCount
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear group rates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Copy rates from one date range to another
     */
    public function copyRates(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'source_group_key' => 'required|string',
            'copy_start_date' => 'required|date|after_or_equal:today',
            'copy_end_date' => 'required|date|after_or_equal:copy_start_date',
            'overwrite_existing' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $provider = Auth::user();
            $groupCriteria = $this->parseGroupKey($request->source_group_key);
            $overwriteExisting = $request->boolean('overwrite_existing', false);
            
            // Get the source service
            $service = TransportService::where('id', $groupCriteria['service_id'])
                                     ->where('provider_id', $provider->id)
                                     ->firstOrFail();
            
            // Get current rates for this service to copy from
            $sourceRates = TransportRate::where('transport_service_id', $service->id)
                ->where('date', '>=', now()->format('Y-m-d'))
                ->where('is_available', true)
                ->orderBy('date')
                ->get();
                
            if ($sourceRates->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No source rates found to copy from'
                ], 404);
            }
            
            // Generate target date range
            $targetDates = [];
            $startDate = Carbon::parse($request->copy_start_date);
            $endDate = Carbon::parse($request->copy_end_date);
            
            while ($startDate <= $endDate) {
                $targetDates[] = $startDate->format('Y-m-d');
                $startDate->addDay();
            }
            
            $copiedRatesCount = 0;
            $skippedRatesCount = 0;
            
            // Get unique routes from source rates
            $uniqueRoutes = $sourceRates->groupBy(function($rate) {
                return $rate->route_from . '|' . $rate->route_to . '|' . $rate->passenger_type;
            });
            
            foreach ($uniqueRoutes as $routeKey => $routeRates) {
                // Use the most recent rate as the template
                $templateRate = $routeRates->first();
                
                foreach ($targetDates as $targetDate) {
                    // Check if rate already exists
                    if (!$overwriteExisting) {
                        $existingRate = TransportRate::where([
                            'transport_service_id' => $service->id,
                            'route_from' => $templateRate->route_from,
                            'route_to' => $templateRate->route_to,
                            'passenger_type' => $templateRate->passenger_type,
                            'date' => $targetDate
                        ])->first();
                        
                        if ($existingRate) {
                            $skippedRatesCount++;
                            continue; // Skip if rate exists and overwrite is disabled
                        }
                    }
                    
                    // Create or update the rate
                    TransportRate::updateOrCreate(
                        [
                            'transport_service_id' => $service->id,
                            'provider_id' => $provider->id,
                            'route_from' => $templateRate->route_from,
                            'route_to' => $templateRate->route_to,
                            'passenger_type' => $templateRate->passenger_type,
                            'date' => $targetDate
                        ],
                        [
                            'base_rate' => $templateRate->base_rate,
                            'currency' => $templateRate->currency,
                            'notes' => 'Copied from ' . $templateRate->date . ($templateRate->notes ? ' - ' . $templateRate->notes : ''),
                            'is_available' => true,
                            'updated_at' => now()
                        ]
                    );
                    
                    $copiedRatesCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully copied {$copiedRatesCount} rates to the new date range" . 
                           ($skippedRatesCount > 0 ? " ({$skippedRatesCount} rates were skipped as they already exist)" : ''),
                'data' => [
                    'service_id' => $service->id,
                    'service_name' => $service->service_name,
                    'copied_rates' => $copiedRatesCount,
                    'skipped_rates' => $skippedRatesCount,
                    'target_date_range' => $request->copy_start_date . ' to ' . $request->copy_end_date
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to copy rates: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Apply pricing rules to transport rates automatically
     */
    public function applyPricingRules(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'nullable|exists:transport_services,id',
            'route_from' => 'nullable|string',
            'route_to' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'dry_run' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $provider = Auth::user();
            $dryRun = $request->boolean('dry_run', false);
            
            // Get services based on filters
            $servicesQuery = TransportService::where('provider_id', $provider->id);
            
            if ($request->service_id) {
                $servicesQuery->where('id', $request->service_id);
            }
            
            $services = $servicesQuery->get();
            
            if ($services->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No services found matching the criteria'
                ], 404);
            }

            $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now();
            $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->addDays(30);
            
            $affectedServices = [];
            $totalRatesCreated = 0;
            $totalRatesUpdated = 0;
            
            if (!$dryRun) {
                DB::beginTransaction();
            }

            foreach ($services as $service) {
                $serviceAffected = [
                    'id' => $service->id,
                    'service_name' => $service->service_name,
                    'transport_type' => $service->transport_type,
                    'base_price' => $service->price,
                    'rates_applied' => []
                ];
                
                $routes = $service->routes ?? [];
                $passengerTypes = ['adult', 'child', 'infant'];
                
                foreach ($routes as $route) {
                    if (!isset($route['from'], $route['to'])) continue;
                    
                    // Apply route filter if specified
                    if ($request->route_from && $route['from'] !== $request->route_from) continue;
                    if ($request->route_to && $route['to'] !== $request->route_to) continue;
                    
                    foreach ($passengerTypes as $passengerType) {
                        $currentDate = $startDate->copy();
                        
                        while ($currentDate->lte($endDate)) {
                            // Get applicable pricing rules for this service and date
                            $applicableRules = $this->getApplicablePricingRules($service, $currentDate, $route['from'], $route['to']);
                            
                            if ($applicableRules->isNotEmpty()) {
                                // Calculate adjusted price using pricing rules
                                $basePrice = $service->price ?? 100;
                                $adjustedPrice = $this->calculatePriceWithRules($basePrice, $applicableRules);
                                
                                if ($adjustedPrice != $basePrice) {
                                    $dateStr = $currentDate->format('Y-m-d');
                                    
                                    if (!$dryRun) {
                                        // Check if rate already exists
                                        $existingRate = TransportRate::where('transport_service_id', $service->id)
                                            ->where('route_from', $route['from'])
                                            ->where('route_to', $route['to'])
                                            ->where('passenger_type', $passengerType)
                                            ->where('date', $dateStr)
                                            ->first();
                                        
                                        if ($existingRate) {
                                            $existingRate->update([
                                                'base_rate' => $adjustedPrice,
                                                'notes' => $this->generatePricingRuleNotes($applicableRules),
                                                'updated_at' => now()
                                            ]);
                                            $totalRatesUpdated++;
                                        } else {
                                            TransportRate::create([
                                                'transport_service_id' => $service->id,
                                                'provider_id' => $provider->id,
                                                'date' => $dateStr,
                                                'route_from' => $route['from'],
                                                'route_to' => $route['to'],
                                                'passenger_type' => $passengerType,
                                                'base_rate' => $adjustedPrice,
                                                'currency' => 'USD',
                                                'notes' => $this->generatePricingRuleNotes($applicableRules),
                                                'is_available' => true
                                            ]);
                                            $totalRatesCreated++;
                                        }
                                    }
                                    
                                    $serviceAffected['rates_applied'][] = [
                                        'date' => $dateStr,
                                        'route' => $route['from'] . ' → ' . $route['to'],
                                        'passenger_type' => $passengerType,
                                        'original_price' => $basePrice,
                                        'adjusted_price' => $adjustedPrice,
                                        'rules_applied' => $applicableRules->pluck('rule_name')->toArray()
                                    ];
                                }
                            }
                            
                            $currentDate->addDay();
                        }
                    }
                }
                
                if (!empty($serviceAffected['rates_applied'])) {
                    $affectedServices[] = $serviceAffected;
                }
            }
            
            if (!$dryRun) {
                DB::commit();
            }

            return response()->json([
                'success' => true,
                'message' => ($dryRun ? 'Dry run completed' : 'Pricing rules applied successfully') . ". Created {$totalRatesCreated} new rates, updated {$totalRatesUpdated} existing rates.",
                'data' => [
                    'affected_services' => $affectedServices,
                    'total_rates_created' => $totalRatesCreated,
                    'total_rates_updated' => $totalRatesUpdated,
                    'dry_run' => $dryRun
                ]
            ]);

        } catch (\Exception $e) {
            if (!$dryRun) {
                DB::rollback();
            }
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply pricing rules: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper Methods
     */

    /**
     * Parse group key into criteria
     */
    private function parseGroupKey($groupKey)
    {
        $parts = explode('|', $groupKey);
        
        return [
            'service_id' => intval($parts[0] ?? 0),
            'transport_type' => $parts[1] ?? '',
            'base_price' => floatval($parts[2] ?? 0)
        ];
    }

    /**
     * Calculate final price based on rate type
     */
    private function calculateFinalPrice($basePrice, $inputPrice, $rateType)
    {
        switch ($rateType) {
            case 'fixed':
                return $inputPrice;
            case 'base_plus':
                return $basePrice + $inputPrice;
            case 'base_percentage':
                return $basePrice + ($basePrice * $inputPrice / 100);
            default:
                return $inputPrice;
        }
    }
    
    /**
     * Calculate final rate based on rate type and direction
     */
    private function calculateFinalRate($basePrice, $inputValue, $rateType, $direction = 'increase')
    {
        switch ($rateType) {
            case 'fixed':
                return abs($inputValue); // Always positive for fixed rates
                
            case 'base_plus':
                // For base_plus, inputValue can be negative (already handled in frontend)
                return $basePrice + $inputValue;
                
            case 'base_percentage':
                // For base_percentage, inputValue can be negative (already handled in frontend)
                return $basePrice + ($basePrice * $inputValue / 100);
                
            default:
                return abs($inputValue);
        }
    }

    /**
     * Get current rate for a route
     */
    private function getCurrentRateForRoute($serviceId, $routeFrom, $routeTo)
    {
        return TransportRate::where('transport_service_id', $serviceId)
            ->where('route_from', $routeFrom)
            ->where('route_to', $routeTo)
            ->where('date', '>=', now()->format('Y-m-d'))
            ->where('is_available', true)
            ->orderBy('date', 'asc')
            ->first();
    }

    /**
     * Get applicable pricing rules
     */
    private function getApplicablePricingRules($service, $date, $routeFrom = null, $routeTo = null)
    {
        return TransportPricingRule::where('transport_service_id', $service->id)
            ->where('is_active', true)
            ->get()
            ->filter(function($rule) use ($date, $routeFrom, $routeTo) {
                return $rule->isApplicable($date->format('Y-m-d'), 1, $routeFrom, $routeTo);
            });
    }

    /**
     * Calculate price with rules applied
     */
    private function calculatePriceWithRules($basePrice, $rules)
    {
        $finalPrice = $basePrice;
        
        foreach ($rules->sortBy('priority') as $rule) {
            $adjustment = $rule->calculateAdjustment($finalPrice);
            $finalPrice += $adjustment;
        }
        
        return $finalPrice;
    }

    /**
     * Generate pricing rule notes
     */
    private function generatePricingRuleNotes($rules)
    {
        $notes = [];
        foreach ($rules as $rule) {
            $notes[] = $rule->rule_name;
        }
        return 'Applied: ' . implode(', ', $notes);
    }
}
