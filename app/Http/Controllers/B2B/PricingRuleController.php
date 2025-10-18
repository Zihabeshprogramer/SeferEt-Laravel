<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use App\Models\PricingRule;
use App\Models\Hotel;
use App\Models\RoomType;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PricingRuleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:hotel_provider']);
    }

    /**
     * Display a listing of pricing rules (AJAX only)
     */
    public function index(Request $request)
    {
        $pricingRules = PricingRule::with(['hotel'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $pricingRules->map(function ($rule) {
                return [
                    'id' => $rule->id,
                    'name' => $rule->name,
                    'rule_type' => $rule->rule_type,
                    'hotel_id' => $rule->hotel_id,
                    'hotel_name' => $rule->hotel->name ?? 'All Hotels',
                    'room_category' => $rule->room_category,
                    'room_category_display' => ucfirst(str_replace('_', ' ', $rule->room_category ?? 'All Categories')),
                    'start_date' => $rule->start_date,
                    'end_date' => $rule->end_date,
                    'adjustment_type' => $rule->adjustment_type,
                    'adjustment_value' => $rule->adjustment_value,
                    'min_nights' => $rule->min_nights,
                    'max_nights' => $rule->max_nights,
                    'days_of_week' => $rule->days_of_week,
                    'priority' => $rule->priority,
                    'is_active' => $rule->is_active,
                    'conditions' => $rule->conditions,
                    'created_at' => $rule->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $rule->updated_at->format('Y-m-d H:i:s'),
                ];
            })
        ]);
    }


    /**
     * Store a newly created pricing rule
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'rule_type' => 'required|in:seasonal,advance_booking,length_of_stay,day_of_week,occupancy,promotional,blackout,minimum_stay',
            'hotel_id' => 'nullable|exists:hotels,id',
            'room_category' => 'nullable|string|in:window_view,sea_view,balcony,kitchenette,family_suite,executive_lounge',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'adjustment_type' => 'required|in:percentage,fixed,multiply',
            'adjustment_value' => 'required|numeric',
            'min_nights' => 'nullable|integer|min:1',
            'max_nights' => 'nullable|integer|min:1',
            'days_of_week' => 'nullable|array',
            'days_of_week.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'priority' => 'nullable|integer|between:1,10',
            'is_active' => 'boolean',
            'conditions' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $pricingRule = PricingRule::create([
            'name' => $request->name,
            'rule_type' => $request->rule_type,
            'hotel_id' => $request->hotel_id,
            'room_category' => $request->room_category,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'adjustment_type' => $request->adjustment_type,
            'adjustment_value' => $request->adjustment_value,
            'min_nights' => $request->min_nights,
            'max_nights' => $request->max_nights,
            'days_of_week' => $request->days_of_week ?? [],
            'priority' => $request->priority ?? 5,
            'is_active' => $request->has('is_active') ? true : false,
            'conditions' => $request->conditions ?? []
        ]);
        
        // Automatically apply pricing rule if it's active
        if ($pricingRule->is_active) {
            try {
                $this->autoApplyPricingRule($pricingRule);
            } catch (\Exception $e) {
                // Log the error but don't fail the creation
                \Log::warning('Failed to auto-apply pricing rule: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Pricing rule created successfully',
            'data' => $pricingRule->load(['hotel'])
        ]);
    }



    /**
     * Update the specified pricing rule
     */
    public function update(Request $request, PricingRule $pricingRule)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'rule_type' => 'required|in:seasonal,advance_booking,length_of_stay,day_of_week,occupancy,promotional,blackout,minimum_stay',
            'hotel_id' => 'nullable|exists:hotels,id',
            'room_category' => 'nullable|string|in:window_view,sea_view,balcony,kitchenette,family_suite,executive_lounge',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'adjustment_type' => 'required|in:percentage,fixed,multiply',
            'adjustment_value' => 'required|numeric',
            'min_nights' => 'nullable|integer|min:1',
            'max_nights' => 'nullable|integer|min:1',
            'days_of_week' => 'nullable|array',
            'days_of_week.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'priority' => 'nullable|integer|between:1,10',
            'is_active' => 'boolean',
            'conditions' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $pricingRule->update([
            'name' => $request->name,
            'rule_type' => $request->rule_type,
            'hotel_id' => $request->hotel_id,
            'room_category' => $request->room_category,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'adjustment_type' => $request->adjustment_type,
            'adjustment_value' => $request->adjustment_value,
            'min_nights' => $request->min_nights,
            'max_nights' => $request->max_nights,
            'days_of_week' => $request->days_of_week ?? [],
            'priority' => $request->priority ?? 5,
            'is_active' => $request->has('is_active') ? true : false,
            'conditions' => $request->conditions ?? []
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pricing rule updated successfully',
            'data' => $pricingRule->load(['hotel'])
        ]);
    }

    /**
     * Remove the specified pricing rule
     */
    public function destroy(PricingRule $pricingRule)
    {
        $pricingRule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pricing rule deleted successfully'
        ]);
    }

    /**
     * Toggle pricing rule status
     */
    public function toggleStatus(PricingRule $pricingRule)
    {
        $pricingRule->is_active = !$pricingRule->is_active;
        $pricingRule->save();

        $status = $pricingRule->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'success' => true,
            'message' => "Pricing rule has been {$status} successfully",
            'is_active' => $pricingRule->is_active
        ]);
    }

    /**
     * Bulk operations
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:activate,deactivate,delete,clone,update_priority,update_dates,export,import',
            'pricing_rule_ids' => 'required|array',
            'pricing_rule_ids.*' => 'exists:pricing_rules,id',
            'new_priority' => 'nullable|integer|between:1,10',
            'new_start_date' => 'nullable|date',
            'new_end_date' => 'nullable|date|after_or_equal:new_start_date',
            'clone_name_prefix' => 'nullable|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $pricingRules = PricingRule::whereIn('id', $request->pricing_rule_ids);
        $count = $pricingRules->count();
        
        try {
            DB::beginTransaction();

            switch ($request->action) {
                case 'activate':
                    $pricingRules->update(['is_active' => true]);
                    $message = "Successfully activated {$count} pricing rules";
                    break;

                case 'deactivate':
                    $pricingRules->update(['is_active' => false]);
                    $message = "Successfully deactivated {$count} pricing rules";
                    break;

                case 'delete':
                    $pricingRules->delete();
                    $message = "Successfully deleted {$count} pricing rules";
                    break;

                case 'clone':
                    $originalRules = $pricingRules->get();
                    $cloned = 0;
                    $prefix = $request->clone_name_prefix ?: 'Copy of';
                    
                    foreach ($originalRules as $rule) {
                        $newRule = $rule->replicate();
                        $newRule->name = $prefix . ' ' . $rule->name;
                        $newRule->is_active = false;
                        $newRule->save();
                        $cloned++;
                    }
                    $message = "Successfully cloned {$cloned} pricing rules";
                    break;
                    
                case 'update_priority':
                    if (!$request->new_priority) {
                        return response()->json([
                            'success' => false,
                            'message' => 'New priority is required for this action'
                        ], 422);
                    }
                    
                    $pricingRules->update(['priority' => $request->new_priority]);
                    $message = "Successfully updated priority to {$request->new_priority} for {$count} pricing rules";
                    break;
                    
                case 'update_dates':
                    if (!$request->new_start_date || !$request->new_end_date) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Both start date and end date are required for this action'
                        ], 422);
                    }
                    
                    $pricingRules->update([
                        'start_date' => $request->new_start_date,
                        'end_date' => $request->new_end_date
                    ]);
                    $message = "Successfully updated dates for {$count} pricing rules";
                    break;
                    
                case 'export':
                    return $this->exportPricingRules($request->pricing_rule_ids);
                    
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid action specified'
                    ], 422);
            }
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'affected_count' => $count
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error performing bulk action: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pricing rules for a specific date range and room
     */
    public function getApplicableRules(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hotel_id' => 'nullable|exists:hotels,id',
            'room_category' => 'nullable|string',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'nights' => 'nullable|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = PricingRule::active()
            ->where('start_date', '<=', $request->check_out)
            ->where('end_date', '>=', $request->check_in)
            ->orderBy('priority', 'desc');

        if ($request->hotel_id) {
            $query->where(function ($q) use ($request) {
                $q->whereNull('hotel_id')
                  ->orWhere('hotel_id', $request->hotel_id);
            });
        }

        if ($request->room_category) {
            $query->where(function ($q) use ($request) {
                $q->whereNull('room_category')
                  ->orWhere('room_category', $request->room_category);
            });
        }

        $rules = $query->get();

        // Filter by nights if provided
        if ($request->nights) {
            $rules = $rules->filter(function ($rule) use ($request) {
                return ($rule->min_nights === null || $request->nights >= $rule->min_nights) &&
                       ($rule->max_nights === null || $request->nights <= $rule->max_nights);
            });
        }

        return response()->json([
            'success' => true,
            'data' => $rules->values(),
            'count' => $rules->count()
        ]);
    }

    /**
     * Calculate price with applicable rules
     */
    public function calculatePrice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'base_price' => 'required|numeric|min:0',
            'hotel_id' => 'nullable|exists:hotels,id',
            'room_category' => 'nullable|string',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $basePrice = $request->base_price;
        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);
        $nights = $checkIn->diffInDays($checkOut);

        // Get applicable rules
        $rulesResponse = $this->getApplicableRules($request);
        $rules = $rulesResponse->getData()->data ?? collect([]);

        $finalPrice = $basePrice;
        $appliedRules = [];

        foreach ($rules as $rule) {
            $adjustment = 0;
            
            switch ($rule->adjustment_type) {
                case 'percentage':
                    $adjustment = ($finalPrice * $rule->adjustment_value) / 100;
                    break;
                case 'fixed':
                    $adjustment = $rule->adjustment_value;
                    break;
                case 'multiply':
                    $adjustment = $finalPrice * ($rule->adjustment_value - 1);
                    break;
            }

            $finalPrice += $adjustment;
            $appliedRules[] = [
                'rule_id' => $rule->id,
                'rule_name' => $rule->name,
                'rule_type' => $rule->rule_type,
                'adjustment_type' => $rule->adjustment_type,
                'adjustment_value' => $rule->adjustment_value,
                'price_adjustment' => $adjustment
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'base_price' => $basePrice,
                'final_price' => max(0, $finalPrice), // Ensure price doesn't go negative
                'total_adjustment' => $finalPrice - $basePrice,
                'nights' => $nights,
                'applied_rules' => $appliedRules,
                'total_cost' => max(0, $finalPrice) * $nights
            ]
        ]);
    }
    
    /**
     * Bulk create pricing rules
     */
    public function bulkCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rules' => 'required|array|min:1',
            'rules.*.name' => 'required|string|max:255',
            'rules.*.rule_type' => 'required|in:seasonal,advance_booking,length_of_stay,day_of_week,occupancy,promotional,blackout,minimum_stay',
            'rules.*.hotel_id' => 'nullable|exists:hotels,id',
            'rules.*.room_category' => 'nullable|string|in:window_view,sea_view,balcony,kitchenette,family_suite,executive_lounge',
            'rules.*.start_date' => 'required|date',
            'rules.*.end_date' => 'required|date|after:start_date',
            'rules.*.adjustment_type' => 'required|in:percentage,fixed,multiply',
            'rules.*.adjustment_value' => 'required|numeric',
            'rules.*.min_nights' => 'nullable|integer|min:1',
            'rules.*.max_nights' => 'nullable|integer|min:1',
            'rules.*.priority' => 'nullable|integer|between:1,10',
            'rules.*.is_active' => 'nullable|boolean'
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
            
            $createdRules = [];
            $errors = [];
            
            foreach ($request->rules as $index => $ruleData) {
                try {
                    $rule = PricingRule::create([
                        'name' => $ruleData['name'],
                        'rule_type' => $ruleData['rule_type'],
                        'hotel_id' => $ruleData['hotel_id'] ?? null,
                        'room_category' => $ruleData['room_category'] ?? null,
                        'start_date' => $ruleData['start_date'],
                        'end_date' => $ruleData['end_date'],
                        'adjustment_type' => $ruleData['adjustment_type'],
                        'adjustment_value' => $ruleData['adjustment_value'],
                        'min_nights' => $ruleData['min_nights'] ?? null,
                        'max_nights' => $ruleData['max_nights'] ?? null,
                        'days_of_week' => $ruleData['days_of_week'] ?? [],
                        'priority' => $ruleData['priority'] ?? 5,
                        'is_active' => $ruleData['is_active'] ?? true,
                        'conditions' => $ruleData['conditions'] ?? []
                    ]);
                    
                    $createdRules[] = $rule;
                } catch (\Exception $e) {
                    $errors[] = "Rule #{$index}: " . $e->getMessage();
                }
            }
            
            if (count($errors) > 0) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Some rules failed to create',
                    'errors' => $errors
                ], 422);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Successfully created ' . count($createdRules) . ' pricing rules',
                'data' => collect($createdRules)->map(function($rule) {
                    return [
                        'id' => $rule->id,
                        'name' => $rule->name,
                        'rule_type' => $rule->rule_type,
                        'is_active' => $rule->is_active
                    ];
                })
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create pricing rules: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Export pricing rules
     */
    public function exportPricingRules($ruleIds)
    {
        try {
            $rules = PricingRule::whereIn('id', $ruleIds)
                ->with('hotel:id,name')
                ->get();
            
            $exportData = $rules->map(function($rule) {
                return [
                    'id' => $rule->id,
                    'name' => $rule->name,
                    'rule_type' => $rule->rule_type,
                    'rule_type_display' => $rule->rule_type_display,
                    'hotel_id' => $rule->hotel_id,
                    'hotel_name' => $rule->hotel ? $rule->hotel->name : null,
                    'room_category' => $rule->room_category,
                    'start_date' => $rule->start_date->format('Y-m-d'),
                    'end_date' => $rule->end_date->format('Y-m-d'),
                    'adjustment_type' => $rule->adjustment_type,
                    'adjustment_value' => $rule->adjustment_value,
                    'formatted_adjustment' => $rule->formatted_adjustment,
                    'min_nights' => $rule->min_nights,
                    'max_nights' => $rule->max_nights,
                    'days_of_week' => $rule->days_of_week,
                    'priority' => $rule->priority,
                    'is_active' => $rule->is_active,
                    'conditions' => $rule->conditions,
                    'created_at' => $rule->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $rule->updated_at->format('Y-m-d H:i:s')
                ];
            });
            
            $filename = 'pricing_rules_export_' . date('Y-m-d_H-i-s') . '.json';
            
            return response()->json([
                'success' => true,
                'message' => 'Pricing rules exported successfully',
                'data' => [
                    'filename' => $filename,
                    'export_data' => $exportData,
                    'count' => $exportData->count(),
                    'exported_at' => now()->format('Y-m-d H:i:s')
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export pricing rules: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Import pricing rules
     */
    public function importPricingRules(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'import_data' => 'required|array',
            'import_mode' => 'required|in:create_new,replace_existing,merge',
            'name_prefix' => 'nullable|string|max:50'
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
            
            $importData = $request->import_data;
            $importMode = $request->import_mode;
            $namePrefix = $request->name_prefix;
            
            $created = 0;
            $updated = 0;
            $errors = [];
            
            foreach ($importData as $index => $ruleData) {
                try {
                    // Validate required fields
                    if (!isset($ruleData['name']) || !isset($ruleData['rule_type']) || 
                        !isset($ruleData['start_date']) || !isset($ruleData['end_date']) ||
                        !isset($ruleData['adjustment_type']) || !isset($ruleData['adjustment_value'])) {
                        $errors[] = "Rule #{$index}: Missing required fields";
                        continue;
                    }
                    
                    $ruleName = $namePrefix ? $namePrefix . ' ' . $ruleData['name'] : $ruleData['name'];
                    
                    if ($importMode === 'replace_existing' && isset($ruleData['id'])) {
                        // Try to update existing rule
                        $existingRule = PricingRule::find($ruleData['id']);
                        if ($existingRule) {
                            $existingRule->update([
                                'name' => $ruleName,
                                'rule_type' => $ruleData['rule_type'],
                                'hotel_id' => $ruleData['hotel_id'] ?? null,
                                'room_category' => $ruleData['room_category'] ?? null,
                                'start_date' => $ruleData['start_date'],
                                'end_date' => $ruleData['end_date'],
                                'adjustment_type' => $ruleData['adjustment_type'],
                                'adjustment_value' => $ruleData['adjustment_value'],
                                'min_nights' => $ruleData['min_nights'] ?? null,
                                'max_nights' => $ruleData['max_nights'] ?? null,
                                'days_of_week' => $ruleData['days_of_week'] ?? [],
                                'priority' => $ruleData['priority'] ?? 5,
                                'is_active' => $ruleData['is_active'] ?? true,
                                'conditions' => $ruleData['conditions'] ?? []
                            ]);
                            $updated++;
                            continue;
                        }
                    }
                    
                    // Create new rule
                    PricingRule::create([
                        'name' => $ruleName,
                        'rule_type' => $ruleData['rule_type'],
                        'hotel_id' => $ruleData['hotel_id'] ?? null,
                        'room_category' => $ruleData['room_category'] ?? null,
                        'start_date' => $ruleData['start_date'],
                        'end_date' => $ruleData['end_date'],
                        'adjustment_type' => $ruleData['adjustment_type'],
                        'adjustment_value' => $ruleData['adjustment_value'],
                        'min_nights' => $ruleData['min_nights'] ?? null,
                        'max_nights' => $ruleData['max_nights'] ?? null,
                        'days_of_week' => $ruleData['days_of_week'] ?? [],
                        'priority' => $ruleData['priority'] ?? 5,
                        'is_active' => $ruleData['is_active'] ?? true,
                        'conditions' => $ruleData['conditions'] ?? []
                    ]);
                    $created++;
                    
                } catch (\Exception $e) {
                    $errors[] = "Rule #{$index} ({$ruleData['name']}): " . $e->getMessage();
                }
            }
            
            DB::commit();
            
            $message = "Import completed: {$created} created, {$updated} updated";
            if (count($errors) > 0) {
                $message .= ", " . count($errors) . " errors";
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'created' => $created,
                    'updated' => $updated,
                    'errors' => $errors,
                    'total_processed' => count($importData)
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to import pricing rules: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Toggle pricing rule active status (alternative method name)
     */
    public function toggle(PricingRule $pricingRule)
    {
        return $this->toggleStatus($pricingRule);
    }
    
    /**
     * Automatically apply a pricing rule to relevant room rates
     */
    private function autoApplyPricingRule(PricingRule $rule)
    {
        // Get the RoomRatesController instance
        $roomRatesController = new \App\Http\Controllers\B2B\RoomRatesController();
        
        // Create a fake request with appropriate filters
        $fakeRequest = new Request([
            'hotel_id' => $rule->hotel_id,
            'room_category' => $rule->room_category,
            'start_date' => $rule->start_date->format('Y-m-d'),
            'end_date' => $rule->end_date->format('Y-m-d'),
            'dry_run' => false
        ]);
        
        // Apply the pricing rules
        $response = $roomRatesController->applyPricingRules($fakeRequest);
        
        return $response;
    }
    
    /**
     * Get analytics data for pricing rules
     */
    public function analytics(Request $request)
    {
        try {
            $totalRules = PricingRule::count();
            $activeRules = PricingRule::where('is_active', true)->count();
            $seasonalRules = PricingRule::where('rule_type', 'seasonal')->count();
            $promotionalRules = PricingRule::where('rule_type', 'promotional')->count();
            
            // Calculate some basic analytics
            $avgPriceImpact = PricingRule::active()
                ->where('adjustment_type', 'percentage')
                ->avg('adjustment_value') ?? 0;
                
            $roomsAffected = 0; // This would need actual room rate calculations
            $revenueImpact = 0; // This would need booking data integration
            $rulesAppliedToday = 0; // This would need application logs
            
            // Get top performing rules (placeholder data)
            $topRules = PricingRule::active()
                ->with(['hotel'])
                ->orderBy('priority', 'desc')
                ->take(10)
                ->get()
                ->map(function($rule) {
                    return [
                        'id' => $rule->id,
                        'name' => $rule->name,
                        'rule_type' => $rule->rule_type,
                        'hotel_name' => $rule->hotel->name ?? 'All Hotels',
                        'is_active' => $rule->is_active,
                        'rooms_affected' => 0, // Placeholder
                        'avg_impact' => $rule->adjustment_value,
                        'estimated_revenue' => 0, // Placeholder
                        'applications_count' => 0 // Placeholder
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => [
                        'total_rules' => $totalRules,
                        'active_rules' => $activeRules,
                        'rooms_impacted' => $roomsAffected,
                        'avg_impact' => round($avgPriceImpact, 1)
                    ],
                    'analytics' => [
                        'avg_price_impact' => round($avgPriceImpact, 1),
                        'rules_applied_today' => $rulesAppliedToday,
                        'rooms_affected' => $roomsAffected,
                        'estimated_revenue_impact' => $revenueImpact
                    ],
                    'stats' => [
                        'total' => $totalRules,
                        'active' => $activeRules,
                        'seasonal' => $seasonalRules,
                        'promotional' => $promotionalRules
                    ],
                    'top_rules' => $topRules
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving analytics: ' . $e->getMessage()
            ], 500);
        }
    }
}
