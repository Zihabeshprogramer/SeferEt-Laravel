<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use App\Models\TransportPricingRule;
use App\Models\TransportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class TransportPricingRuleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role.redirect']);
    }

    /**
     * Get all pricing rules for the provider
     */
    public function index(Request $request): JsonResponse
    {
        $provider = Auth::user();
        
        if (!$provider->isTransportProvider()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $query = TransportPricingRule::where('provider_id', $provider->id)
                                   ->with('transportService');
        
        // Filter by service if specified
        if ($request->has('service_id')) {
            $query->where('transport_service_id', $request->service_id);
        }
        
        // Filter by rule type if specified
        if ($request->has('rule_type')) {
            $query->where('rule_type', $request->rule_type);
        }
        
        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }
        
        $rules = $query->orderBy('priority', 'asc')
                      ->orderBy('created_at', 'desc')
                      ->get();
        
        return response()->json([
            'success' => true,
            'rules' => $rules
        ]);
    }
    
    /**
     * Store a new pricing rule
     */
    public function store(Request $request): JsonResponse
    {
        $provider = Auth::user();
        
        if (!$provider->isTransportProvider()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'transport_service_id' => 'required|exists:transport_services,id',
            'rule_name' => 'required|string|max:255',
            'rule_type' => 'required|in:seasonal,distance,passenger_count,route_specific,day_of_week,advance_booking',
            'description' => 'nullable|string|max:500',
            'adjustment_type' => 'required|in:percentage,fixed,multiplier',
            'adjustment_value' => 'required|numeric',
            'conditions' => 'nullable|array',
            'applicable_routes' => 'nullable|array',
            'applicable_routes.*.from' => 'string|max:255',
            'applicable_routes.*.to' => 'string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'min_passengers' => 'nullable|integer|min:1',
            'max_passengers' => 'nullable|integer|gte:min_passengers',
            'min_distance' => 'nullable|numeric|min:0',
            'max_distance' => 'nullable|numeric|gte:min_distance',
            'days_of_week' => 'nullable|array',
            'days_of_week.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'min_advance_hours' => 'nullable|integer|min:0',
            'max_advance_hours' => 'nullable|integer|gte:min_advance_hours',
            'priority' => 'nullable|integer|min:1|max:100',
            'is_active' => 'boolean',
        ]);
        
        // Verify service ownership
        $service = TransportService::where('id', $request->transport_service_id)
                                 ->where('provider_id', $provider->id)
                                 ->first();
        
        if (!$service) {
            return response()->json(['error' => 'Service not found'], 404);
        }
        
        // Manually build the rule data array
        $ruleData = [
            'transport_service_id' => $request->transport_service_id,
            'rule_name' => $request->rule_name,
            'rule_type' => $request->rule_type,
            'description' => $request->description,
            'adjustment_type' => $request->adjustment_type,
            'adjustment_value' => $request->adjustment_value,
            'conditions' => $request->conditions,
            'applicable_routes' => $request->applicable_routes,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'min_passengers' => $request->min_passengers,
            'max_passengers' => $request->max_passengers,
            'min_distance' => $request->min_distance,
            'max_distance' => $request->max_distance,
            'days_of_week' => $request->days_of_week,
            'min_advance_hours' => $request->min_advance_hours,
            'max_advance_hours' => $request->max_advance_hours,
            'priority' => $request->priority ?? 10,
            'provider_id' => $provider->id,
            'is_active' => $request->boolean('is_active', true),
        ];
        
        $rule = TransportPricingRule::create($ruleData);
        
        // Apply rule automatically if it's active and the service allows it
        if ($rule->is_active && $service->auto_apply_pricing_rules) {
            $this->applyRuleToExistingRates($rule);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Pricing rule created successfully',
            'rule' => $rule->load('transportService')
        ], 201);
    }
    
    /**
     * Show a specific pricing rule
     */
    public function show(TransportPricingRule $transportPricingRule): JsonResponse
    {
        $provider = Auth::user();
        
        if (!$provider->isTransportProvider() || $transportPricingRule->provider_id !== $provider->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        return response()->json([
            'success' => true,
            'rule' => $transportPricingRule->load('transportService')
        ]);
    }
    
    /**
     * Update an existing pricing rule
     */
    public function update(Request $request, TransportPricingRule $transportPricingRule): JsonResponse
    {
        $provider = Auth::user();
        
        if (!$provider->isTransportProvider() || $transportPricingRule->provider_id !== $provider->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'rule_name' => 'required|string|max:255',
            'rule_type' => 'required|in:seasonal,distance,passenger_count,route_specific,day_of_week,advance_booking',
            'description' => 'nullable|string|max:500',
            'adjustment_type' => 'required|in:percentage,fixed,multiplier',
            'adjustment_value' => 'required|numeric',
            'conditions' => 'nullable|array',
            'applicable_routes' => 'nullable|array',
            'applicable_routes.*.from' => 'string|max:255',
            'applicable_routes.*.to' => 'string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'min_passengers' => 'nullable|integer|min:1',
            'max_passengers' => 'nullable|integer|gte:min_passengers',
            'min_distance' => 'nullable|numeric|min:0',
            'max_distance' => 'nullable|numeric|gte:min_distance',
            'days_of_week' => 'nullable|array',
            'days_of_week.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'min_advance_hours' => 'nullable|integer|min:0',
            'max_advance_hours' => 'nullable|integer|gte:min_advance_hours',
            'priority' => 'nullable|integer|min:1|max:100',
            'is_active' => 'boolean',
        ]);
        
        // Manually build the update data array
        $updateData = [
            'rule_name' => $request->rule_name,
            'rule_type' => $request->rule_type,
            'description' => $request->description,
            'adjustment_type' => $request->adjustment_type,
            'adjustment_value' => $request->adjustment_value,
            'conditions' => $request->conditions,
            'applicable_routes' => $request->applicable_routes,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'min_passengers' => $request->min_passengers,
            'max_passengers' => $request->max_passengers,
            'min_distance' => $request->min_distance,
            'max_distance' => $request->max_distance,
            'days_of_week' => $request->days_of_week,
            'min_advance_hours' => $request->min_advance_hours,
            'max_advance_hours' => $request->max_advance_hours,
            'priority' => $request->priority,
            'is_active' => $request->boolean('is_active', true),
        ];
        
        $transportPricingRule->update($updateData);
        
        return response()->json([
            'success' => true,
            'message' => 'Pricing rule updated successfully',
            'rule' => $transportPricingRule->fresh(['transportService'])
        ]);
    }
    
    /**
     * Delete a pricing rule
     */
    public function destroy(TransportPricingRule $transportPricingRule): JsonResponse
    {
        $provider = Auth::user();
        
        if (!$provider->isTransportProvider() || $transportPricingRule->provider_id !== $provider->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $transportPricingRule->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Pricing rule deleted successfully'
        ]);
    }
    
    /**
     * Toggle rule active status
     */
    public function toggleStatus(TransportPricingRule $transportPricingRule): JsonResponse
    {
        $provider = Auth::user();
        
        if (!$provider->isTransportProvider() || $transportPricingRule->provider_id !== $provider->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $transportPricingRule->update([
            'is_active' => !$transportPricingRule->is_active
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Pricing rule status updated',
            'is_active' => $transportPricingRule->is_active
        ]);
    }
    
    /**
     * Update rule priority
     */
    public function updatePriority(Request $request): JsonResponse
    {
        $provider = Auth::user();
        
        if (!$provider->isTransportProvider()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'rules' => 'required|array|min:1',
            'rules.*.id' => 'required|exists:transport_pricing_rules,id',
            'rules.*.priority' => 'required|integer|min:1|max:100',
        ]);
        
        foreach ($request->rules as $ruleData) {
            TransportPricingRule::where('id', $ruleData['id'])
                              ->where('provider_id', $provider->id)
                              ->update(['priority' => $ruleData['priority']]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Rule priorities updated successfully'
        ]);
    }
    
    /**
     * Preview rule application
     */
    public function previewRule(Request $request): JsonResponse
    {
        $provider = Auth::user();
        
        if (!$provider->isTransportProvider()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'service_id' => 'required|exists:transport_services,id',
            'rule_type' => 'required|in:seasonal,distance,passenger_count,route_specific,day_of_week,advance_booking',
            'adjustment_type' => 'required|in:percentage,fixed,multiplier',
            'adjustment_value' => 'required|numeric',
            'conditions' => 'nullable|array',
            'test_scenarios' => 'required|array|min:1',
            'test_scenarios.*.route_from' => 'required|string',
            'test_scenarios.*.route_to' => 'required|string',
            'test_scenarios.*.date' => 'required|date',
            'test_scenarios.*.passenger_count' => 'required|integer|min:1',
        ]);
        
        $service = TransportService::where('id', $request->service_id)
                                 ->where('provider_id', $provider->id)
                                 ->first();
        
        if (!$service) {
            return response()->json(['error' => 'Service not found'], 404);
        }
        
        // Create a temporary rule object (don't save it)
        $tempRule = new TransportPricingRule($request->only([
            'rule_type', 'adjustment_type', 'adjustment_value', 'conditions',
            'start_date', 'end_date', 'min_passengers', 'max_passengers',
            'min_distance', 'max_distance', 'days_of_week',
            'min_advance_hours', 'max_advance_hours'
        ]));
        $tempRule->transport_service_id = $service->id;
        $tempRule->provider_id = $provider->id;
        
        $results = [];
        
        foreach ($request->test_scenarios as $scenario) {
            $isApplicable = $tempRule->isApplicable(
                $scenario['date'],
                $scenario['passenger_count'],
                $scenario['route_from'],
                $scenario['route_to']
            );
            
            $baseRate = 100; // Use a default base rate for preview
            $adjustment = $isApplicable ? $tempRule->calculateAdjustment($baseRate) : 0;
            $finalRate = $baseRate + $adjustment;
            
            $results[] = [
                'scenario' => $scenario,
                'is_applicable' => $isApplicable,
                'base_rate' => $baseRate,
                'adjustment' => $adjustment,
                'final_rate' => $finalRate,
                'percentage_change' => $baseRate > 0 ? round((($finalRate - $baseRate) / $baseRate) * 100, 2) : 0,
            ];
        }
        
        return response()->json([
            'success' => true,
            'preview_results' => $results
        ]);
    }
    
    /**
     * Get rule templates for quick creation
     */
    public function getTemplates(): JsonResponse
    {
        $templates = [
            [
                'name' => 'Weekend Premium',
                'rule_type' => 'day_of_week',
                'description' => 'Higher rates on weekends',
                'adjustment_type' => 'percentage',
                'adjustment_value' => 25,
                'days_of_week' => ['saturday', 'sunday'],
                'priority' => 10,
            ],
            [
                'name' => 'Peak Season',
                'rule_type' => 'seasonal',
                'description' => 'Higher rates during peak travel season',
                'adjustment_type' => 'percentage',
                'adjustment_value' => 30,
                'priority' => 20,
            ],
            [
                'name' => 'Group Discount',
                'rule_type' => 'passenger_count',
                'description' => 'Discount for larger groups',
                'adjustment_type' => 'percentage',
                'adjustment_value' => -15,
                'min_passengers' => 6,
                'priority' => 15,
            ],
            [
                'name' => 'Long Distance Premium',
                'rule_type' => 'distance',
                'description' => 'Premium for longer routes',
                'adjustment_type' => 'percentage',
                'adjustment_value' => 20,
                'min_distance' => 100,
                'priority' => 12,
            ],
            [
                'name' => 'Last Minute Booking',
                'rule_type' => 'advance_booking',
                'description' => 'Premium for bookings made within 24 hours',
                'adjustment_type' => 'percentage',
                'adjustment_value' => 35,
                'max_advance_hours' => 24,
                'priority' => 5,
            ],
        ];
        
        return response()->json([
            'success' => true,
            'templates' => $templates
        ]);
    }
    
    /**
     * Apply a rule to existing rates (private helper method)
     */
    private function applyRuleToExistingRates(TransportPricingRule $rule): void
    {
        // This would apply the rule to existing rates
        // Implementation depends on business logic
        // For now, we'll skip this to avoid complexity
    }
}
