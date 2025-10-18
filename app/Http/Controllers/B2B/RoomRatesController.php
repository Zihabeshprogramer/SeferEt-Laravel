<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomRate;
use App\Models\Hotel;
use App\Models\PricingRule;
use App\Services\PricingRuleEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RoomRatesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:hotel_provider']);
    }

    /**
     * Display the rates management page
     */
    public function index()
    {
        $hotels = Hotel::with(['rooms' => function($query) {
            $query->with('rates');
        }])->get();
        
        $roomTypeCategories = Room::getRoomTypeCategories();

        return view('b2b.hotel-provider.rates', compact('hotels', 'roomTypeCategories'));
    }

    /**
     * Store individual room rate
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|exists:rooms,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'price' => 'required|numeric|min:0|max:99999',
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

            $room = Room::findOrFail($request->room_id);
            
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
            RoomRate::where('room_id', $room->id)
                ->whereIn('date', $dates)
                ->delete();

            // Insert new rates
            $rateData = [];
            foreach ($dates as $date) {
                $rateData[] = [
                    'room_id' => $room->id,
                    'date' => $date,
                    'price' => $request->price,
                    'notes' => $request->notes,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            RoomRate::insert($rateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Room rate updated successfully for ' . count($dates) . ' days',
                'data' => [
                    'room_id' => $room->id,
                    'dates_count' => count($dates),
                    'price' => $request->price
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update room rate: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store group rates for multiple rooms
     */
    public function storeGroupRate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_key' => 'required|string',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'price' => 'required|numeric|min:0|max:99999',
            'rate_type' => 'required|in:fixed,base_plus,base_percentage',
            'notes' => 'nullable|string|max:500',
            'apply_to_all' => 'required|in:0,1,true,false',
            'override_existing' => 'required|in:0,1,true,false',
            'selected_rooms' => 'nullable|array',
            'selected_rooms.*' => 'exists:rooms,id',
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

            // Convert checkbox values to proper booleans
            $applyToAll = in_array($request->apply_to_all, [1, '1', true, 'true'], true);
            $overrideExisting = in_array($request->override_existing, [1, '1', true, 'true'], true);

            // Parse group key to get room criteria
            $groupCriteria = $this->parseGroupKey($request->group_key);
            
            // Get rooms for this group
            $roomsQuery = Room::where('category', $groupCriteria['category'])
                ->where('max_occupancy', $groupCriteria['occupancy'])
                ->where('base_price', $groupCriteria['base_price']);

            if (!$applyToAll && !empty($request->selected_rooms)) {
                $roomsQuery->whereIn('id', $request->selected_rooms);
            }

            $rooms = $roomsQuery->get();

            if ($rooms->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No rooms found matching the criteria'
                ], 404);
            }

            // Generate date range
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $dates = [];
            
            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate)) {
                $dates[] = $currentDate->format('Y-m-d');
                $currentDate->addDay();
            }

            $totalRatesCreated = 0;
            $affectedRooms = [];

            foreach ($rooms as $room) {
                // Calculate actual price based on rate type
                $finalPrice = $this->calculateFinalPrice($room->base_price, $request->price, $request->rate_type);

                // Delete existing rates if override is enabled
                if ($overrideExisting) {
                    RoomRate::where('room_id', $room->id)
                        ->whereIn('date', $dates)
                        ->delete();
                } else {
                    // Only delete dates that don't have existing rates
                    $existingDates = RoomRate::where('room_id', $room->id)
                        ->whereIn('date', $dates)
                        ->pluck('date')
                        ->toArray();
                    
                    $dates = array_diff($dates, $existingDates);
                }

                if (!empty($dates)) {
                    // Insert new rates
                    $rateData = [];
                    foreach ($dates as $date) {
                        $rateData[] = [
                            'room_id' => $room->id,
                            'date' => $date,
                            'price' => $finalPrice,
                            'notes' => $request->notes,
                            'is_active' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    RoomRate::insert($rateData);
                    $totalRatesCreated += count($rateData);

                    $affectedRooms[] = [
                        'id' => $room->id,
                        'room_number' => $room->room_number,
                        'dates_updated' => count($dates),
                        'final_price' => $finalPrice
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Group rate applied successfully to " . count($affectedRooms) . " rooms with " . $totalRatesCreated . " rate entries",
                'data' => [
                    'affected_rooms' => $affectedRooms,
                    'total_rates_created' => $totalRatesCreated,
                    'date_range' => [
                        'start' => $request->start_date,
                        'end' => $request->end_date,
                        'days' => count($dates)
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply group rate: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get rooms for a specific group key
     */
    public function getGroupRooms(Request $request)
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
            $groupCriteria = $this->parseGroupKey($request->group_key);
            
            $rooms = Room::where('category', $groupCriteria['category'])
                ->where('max_occupancy', $groupCriteria['occupancy'])
                ->where('base_price', $groupCriteria['base_price'])
                ->with('hotel:id,name')
                ->get(['id', 'room_number', 'name', 'hotel_id', 'base_price', 'is_active', 'is_available']);

            return response()->json([
                'success' => true,
                'data' => $rooms->map(function($room) {
                    return [
                        'id' => $room->id,
                        'room_number' => $room->room_number,
                        'name' => $room->name,
                        'hotel_name' => $room->hotel->name,
                        'base_price' => $room->base_price,
                        'is_active' => $room->is_active,
                        'is_available' => $room->is_available,
                        'current_rate' => $room->getCurrentRate()
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch rooms: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get rate history for a room
     */
    public function getRateHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|exists:rooms,id',
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
            $room = Room::findOrFail($request->room_id);
            $limit = $request->limit ?? 50;

            $rateHistory = RoomRate::where('room_id', $room->id)
                ->orderBy('date', 'desc')
                ->limit($limit)
                ->get(['date', 'price', 'notes', 'is_active', 'created_at', 'updated_at']);

            return response()->json([
                'success' => true,
                'data' => [
                    'room' => [
                        'id' => $room->id,
                        'room_number' => $room->room_number,
                        'name' => $room->name,
                        'base_price' => $room->base_price
                    ],
                    'history' => $rateHistory->map(function($rate) {
                        return [
                            'date' => $rate->date,
                            'price' => $rate->price,
                            'notes' => $rate->notes,
                            'is_active' => $rate->is_active,
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
     * Get calendar rates for a specific room
     */
    public function getCalendarRates(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|exists:rooms,id',
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
            $room = Room::findOrFail($request->room_id);
            
            $query = RoomRate::where('room_id', $room->id)
                ->where('is_active', true)
                ->orderBy('date', 'asc');

            if ($request->start_date) {
                $query->where('date', '>=', $request->start_date);
            }

            if ($request->end_date) {
                $query->where('date', '<=', $request->end_date);
            }

            $rates = $query->get(['date', 'price', 'notes']);

            return response()->json([
                'success' => true,
                'data' => $rates->map(function($rate) {
                    return [
                        'date' => $rate->date,
                        'price' => $rate->price,
                        'notes' => $rate->notes
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch calendar rates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear rates for a room group
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

            $groupCriteria = $this->parseGroupKey($request->group_key);
            
            $rooms = Room::where('category', $groupCriteria['category'])
                ->where('max_occupancy', $groupCriteria['occupancy'])
                ->where('base_price', $groupCriteria['base_price'])
                ->get(['id']);

            $roomIds = $rooms->pluck('id');

            $query = RoomRate::whereIn('room_id', $roomIds);

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
                'message' => "Cleared {$deletedCount} rate entries from " . count($roomIds) . " rooms",
                'data' => [
                    'deleted_rates' => $deletedCount,
                    'affected_rooms' => count($roomIds)
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
     * Parse group key into criteria
     */
    private function parseGroupKey($groupKey)
    {
        $parts = explode('|', $groupKey);
        
        return [
            'category' => $parts[0] ?? '',
            'occupancy' => intval($parts[1] ?? 2),
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
     * Apply pricing rules to room rates automatically
     */
    public function applyPricingRules(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hotel_id' => 'nullable|exists:hotels,id',
            'room_category' => 'nullable|string',
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
            $dryRun = $request->boolean('dry_run', false);
            
            // Get rooms based on filters
            $roomsQuery = Room::query();
            
            if ($request->hotel_id) {
                $roomsQuery->where('hotel_id', $request->hotel_id);
            }
            
            if ($request->room_category) {
                $roomsQuery->where('category', $request->room_category);
            }
            
            $rooms = $roomsQuery->with('hotel')->get();
            
            if ($rooms->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No rooms found matching the criteria'
                ], 404);
            }

            $pricingEngine = new PricingRuleEngine();
            $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now();
            $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->addDays(30);
            
            $affectedRooms = [];
            $totalRatesCreated = 0;
            $totalRatesUpdated = 0;
            
            if (!$dryRun) {
                DB::beginTransaction();
            }

            foreach ($rooms as $room) {
                $roomAffected = [
                    'id' => $room->id,
                    'room_number' => $room->room_number,
                    'hotel_name' => $room->hotel->name,
                    'base_price' => $room->base_price,
                    'rates_applied' => []
                ];
                
                $currentDate = $startDate->copy();
                while ($currentDate->lte($endDate)) {
                    // Get applicable pricing rules for this room and date
                    $applicableRules = $this->getApplicablePricingRules($room, $currentDate);
                    
                    if ($applicableRules->isNotEmpty()) {
                        // Calculate adjusted price using pricing engine
                        $adjustedPrice = $this->calculatePriceWithRules($room->base_price, $applicableRules);
                        
                        if ($adjustedPrice != $room->base_price) {
                            $dateStr = $currentDate->format('Y-m-d');
                            
                            if (!$dryRun) {
                                // Check if rate already exists
                                $existingRate = RoomRate::where('room_id', $room->id)
                                    ->where('date', $dateStr)
                                    ->first();
                                
                                if ($existingRate) {
                                    $existingRate->update([
                                        'price' => $adjustedPrice,
                                        'notes' => $this->generatePricingRuleNotes($applicableRules),
                                        'updated_at' => now()
                                    ]);
                                    $totalRatesUpdated++;
                                } else {
                                    RoomRate::create([
                                        'room_id' => $room->id,
                                        'date' => $dateStr,
                                        'price' => $adjustedPrice,
                                        'notes' => $this->generatePricingRuleNotes($applicableRules),
                                        'is_active' => true
                                    ]);
                                    $totalRatesCreated++;
                                }
                            }
                            
                            $roomAffected['rates_applied'][] = [
                                'date' => $dateStr,
                                'original_price' => $room->base_price,
                                'adjusted_price' => $adjustedPrice,
                                'adjustment' => $adjustedPrice - $room->base_price,
                                'rules_applied' => $applicableRules->pluck('name')->toArray()
                            ];
                        }
                    }
                    
                    $currentDate->addDay();
                }
                
                if (!empty($roomAffected['rates_applied'])) {
                    $affectedRooms[] = $roomAffected;
                }
            }

            if (!$dryRun) {
                DB::commit();
            }

            $message = $dryRun 
                ? "Preview: Would affect " . count($affectedRooms) . " rooms with " . ($totalRatesCreated + $totalRatesUpdated) . " rate adjustments"
                : "Successfully applied pricing rules to " . count($affectedRooms) . " rooms. Created {$totalRatesCreated} new rates, updated {$totalRatesUpdated} existing rates";

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'affected_rooms' => $affectedRooms,
                    'summary' => [
                        'rooms_affected' => count($affectedRooms),
                        'rates_created' => $totalRatesCreated,
                        'rates_updated' => $totalRatesUpdated,
                        'date_range' => [
                            'start' => $startDate->format('Y-m-d'),
                            'end' => $endDate->format('Y-m-d')
                        ],
                        'is_dry_run' => $dryRun
                    ]
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
     * Get room rates with pricing rules applied
     */
    public function getRatesWithPricingRules(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|exists:rooms,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $room = Room::with('hotel')->findOrFail($request->room_id);
            $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now();
            $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->addDays(30);
            
            $ratesData = [];
            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate)) {
                $dateStr = $currentDate->format('Y-m-d');
                
                // Get existing rate
                $existingRate = RoomRate::where('room_id', $room->id)
                    ->where('date', $dateStr)
                    ->first();
                
                // Get applicable pricing rules
                $applicableRules = $this->getApplicablePricingRules($room, $currentDate);
                
                // Calculate price with rules
                $basePrice = $existingRate ? $existingRate->price : $room->base_price;
                $adjustedPrice = $this->calculatePriceWithRules($basePrice, $applicableRules);
                
                $ratesData[] = [
                    'date' => $dateStr,
                    'base_price' => $room->base_price,
                    'current_rate' => $existingRate ? $existingRate->price : null,
                    'rules_adjusted_price' => $adjustedPrice,
                    'final_price' => $adjustedPrice,
                    'has_existing_rate' => (bool) $existingRate,
                    'existing_rate_notes' => $existingRate ? $existingRate->notes : null,
                    'applicable_rules' => $applicableRules->map(function($rule) {
                        return [
                            'id' => $rule->id,
                            'name' => $rule->name,
                            'rule_type' => $rule->rule_type,
                            'adjustment_type' => $rule->adjustment_type,
                            'adjustment_value' => $rule->adjustment_value,
                            'priority' => $rule->priority
                        ];
                    })
                ];
                
                $currentDate->addDay();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'room' => [
                        'id' => $room->id,
                        'room_number' => $room->room_number,
                        'name' => $room->name,
                        'hotel_name' => $room->hotel->name,
                        'base_price' => $room->base_price
                    ],
                    'rates' => $ratesData
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get rates with pricing rules: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get applicable pricing rules for a room on a specific date
     */
    private function getApplicablePricingRules(Room $room, Carbon $date)
    {
        return PricingRule::active()
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->where(function ($query) use ($room) {
                $query->whereNull('hotel_id')
                      ->orWhere('hotel_id', $room->hotel_id);
            })
            ->where(function ($query) use ($room) {
                $query->whereNull('room_category')
                      ->orWhere('room_category', $room->category);
            })
            ->orderBy('priority', 'desc')
            ->get();
    }
    
    /**
     * Calculate price with applicable pricing rules
     */
    private function calculatePriceWithRules($basePrice, $rules)
    {
        $finalPrice = $basePrice;
        
        foreach ($rules as $rule) {
            $adjustment = $rule->calculateAdjustment($finalPrice);
            $finalPrice += $adjustment;
        }
        
        return max(0, $finalPrice); // Ensure price doesn't go negative
    }
    
    /**
     * Generate notes for pricing rule applications
     */
    private function generatePricingRuleNotes($rules)
    {
        if ($rules->isEmpty()) {
            return null;
        }
        
        $ruleNames = $rules->pluck('name')->toArray();
        return 'Applied pricing rules: ' . implode(', ', $ruleNames);
    }
    
    /**
     * Bulk apply pricing rules to multiple rooms
     */
    public function bulkApplyPricingRules(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_ids' => 'required|array',
            'room_ids.*' => 'exists:rooms,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'override_existing' => 'nullable|boolean'
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
            
            $rooms = Room::with('hotel')->whereIn('id', $request->room_ids)->get();
            $overrideExisting = $request->boolean('override_existing', false);
            
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            
            $affectedRooms = [];
            $totalRatesProcessed = 0;
            
            foreach ($rooms as $room) {
                $roomResult = $this->applyPricingRulesToRoom($room, $startDate, $endDate, $overrideExisting);
                if ($roomResult['rates_affected'] > 0) {
                    $affectedRooms[] = $roomResult;
                    $totalRatesProcessed += $roomResult['rates_affected'];
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Successfully applied pricing rules to " . count($affectedRooms) . " rooms with {$totalRatesProcessed} rate adjustments",
                'data' => [
                    'affected_rooms' => $affectedRooms,
                    'total_rates_processed' => $totalRatesProcessed
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk apply pricing rules: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Apply pricing rules to a single room
     */
    private function applyPricingRulesToRoom(Room $room, Carbon $startDate, Carbon $endDate, bool $overrideExisting = false)
    {
        $ratesAffected = 0;
        $adjustments = [];
        
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dateStr = $currentDate->format('Y-m-d');
            
            // Get existing rate
            $existingRate = RoomRate::where('room_id', $room->id)
                ->where('date', $dateStr)
                ->first();
            
            if ($existingRate && !$overrideExisting) {
                $currentDate->addDay();
                continue; // Skip if rate exists and we're not overriding
            }
            
            // Get applicable rules
            $applicableRules = $this->getApplicablePricingRules($room, $currentDate);
            
            if ($applicableRules->isNotEmpty()) {
                $adjustedPrice = $this->calculatePriceWithRules($room->base_price, $applicableRules);
                
                if ($adjustedPrice != $room->base_price) {
                    if ($existingRate) {
                        $existingRate->update([
                            'price' => $adjustedPrice,
                            'notes' => $this->generatePricingRuleNotes($applicableRules)
                        ]);
                    } else {
                        RoomRate::create([
                            'room_id' => $room->id,
                            'date' => $dateStr,
                            'price' => $adjustedPrice,
                            'notes' => $this->generatePricingRuleNotes($applicableRules),
                            'is_active' => true
                        ]);
                    }
                    
                    $adjustments[] = [
                        'date' => $dateStr,
                        'original_price' => $room->base_price,
                        'adjusted_price' => $adjustedPrice,
                        'rules_count' => $applicableRules->count()
                    ];
                    
                    $ratesAffected++;
                }
            }
            
            $currentDate->addDay();
        }
        
        return [
            'room_id' => $room->id,
            'room_number' => $room->room_number,
            'hotel_name' => $room->hotel->name,
            'rates_affected' => $ratesAffected,
            'adjustments' => $adjustments
        ];
    }
}
