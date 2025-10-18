<?php

namespace App\Services;

use App\Models\ServiceOffer;
use App\Models\TransportService;
use App\Models\RoomRate;
use Carbon\Carbon;

/**
 * Dynamic Pricing Rule Engine
 * 
 * Handles complex pricing rules including seasonal pricing,
 * demand-based pricing, and promotional rates
 */
class PricingRuleEngine
{
    /**
     * Calculate dynamic price for a service offer
     */
    public function calculatePrice(ServiceOffer $offer, array $context = []): float
    {
        $basePrice = $offer->base_price;
        $pricingRules = $offer->pricing_rules ?? [];
        
        foreach ($pricingRules as $rule) {
            $basePrice = $this->applyRule($basePrice, $rule, $context);
        }
        
        return max($basePrice, 0); // Ensure price is never negative
    }
    
    /**
     * Calculate dynamic price for room rates
     */
    public function calculateRoomPrice(RoomRate $roomRate, array $context = []): float
    {
        $basePrice = $roomRate->price;
        $room = $roomRate->room;
        
        // Apply hotel-level pricing rules if any
        $hotel = $room->hotel;
        $pricingRules = $hotel->pricing_rules ?? [];
        
        foreach ($pricingRules as $rule) {
            $basePrice = $this->applyRule($basePrice, $rule, array_merge($context, [
                'room_type' => $room->roomType->name ?? 'standard',
                'room_id' => $room->id,
                'date' => $roomRate->date
            ]));
        }
        
        return max($basePrice, 0);
    }
    
    /**
     * Calculate dynamic price for transport routes
     */
    public function calculateTransportPrice(TransportService $service, int $routeIndex, array $context = []): float
    {
        $routes = $service->routes ?? [];
        
        if (!isset($routes[$routeIndex])) {
            return 0;
        }
        
        $route = $routes[$routeIndex];
        $basePrice = $route['price'] ?? 0;
        
        $pricingRules = $service->pricing_rules ?? [];
        
        foreach ($pricingRules as $rule) {
            $basePrice = $this->applyRule($basePrice, $rule, array_merge($context, [
                'route_index' => $routeIndex,
                'from' => $route['from'],
                'to' => $route['to'],
                'distance' => $route['distance'] ?? 0,
                'duration' => $route['duration'] ?? 0
            ]));
        }
        
        return max($basePrice, 0);
    }
    
    /**
     * Apply a single pricing rule
     */
    private function applyRule(float $currentPrice, array $rule, array $context): float
    {
        if (!$this->shouldApplyRule($rule, $context)) {
            return $currentPrice;
        }
        
        switch ($rule['type']) {
            case 'seasonal':
                return $this->applySeasonalRule($currentPrice, $rule, $context);
            case 'demand':
                return $this->applyDemandRule($currentPrice, $rule, $context);
            case 'promotional':
                return $this->applyPromotionalRule($currentPrice, $rule, $context);
            case 'occupancy':
                return $this->applyOccupancyRule($currentPrice, $rule, $context);
            case 'advance_booking':
                return $this->applyAdvanceBookingRule($currentPrice, $rule, $context);
            case 'length_of_stay':
                return $this->applyLengthOfStayRule($currentPrice, $rule, $context);
            default:
                return $currentPrice;
        }
    }
    
    /**
     * Check if a rule should be applied based on conditions
     */
    private function shouldApplyRule(array $rule, array $context): bool
    {
        $now = Carbon::now();
        
        // Check date validity
        if (isset($rule['start_date']) && $now->lt(Carbon::parse($rule['start_date']))) {
            return false;
        }
        
        if (isset($rule['end_date']) && $now->gt(Carbon::parse($rule['end_date']))) {
            return false;
        }
        
        // Check day of week conditions
        if (isset($rule['days_of_week']) && !in_array($now->dayOfWeek, $rule['days_of_week'])) {
            return false;
        }
        
        // Check minimum/maximum advance booking
        if (isset($rule['min_advance_days'], $context['booking_date'])) {
            $bookingDate = Carbon::parse($context['booking_date']);
            if ($now->diffInDays($bookingDate) < $rule['min_advance_days']) {
                return false;
            }
        }
        
        // Check service-specific conditions
        if (isset($rule['applies_to']) && !empty($rule['applies_to'])) {
            $applies = false;
            foreach ($rule['applies_to'] as $condition) {
                if ($this->checkCondition($condition, $context)) {
                    $applies = true;
                    break;
                }
            }
            if (!$applies) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Apply seasonal pricing rule
     */
    private function applySeasonalRule(float $currentPrice, array $rule, array $context): float
    {
        return $this->applyPriceAdjustment($currentPrice, $rule);
    }
    
    /**
     * Apply demand-based pricing rule
     */
    private function applyDemandRule(float $currentPrice, array $rule, array $context): float
    {
        $demandLevel = $context['demand_level'] ?? 'normal';
        $demandMultiplier = $rule['demand_multipliers'][$demandLevel] ?? 1.0;
        
        return $currentPrice * $demandMultiplier;
    }
    
    /**
     * Apply promotional pricing rule
     */
    private function applyPromotionalRule(float $currentPrice, array $rule, array $context): float
    {
        // Check if promo code is required and valid
        if (isset($rule['promo_code'], $context['promo_code'])) {
            if ($rule['promo_code'] !== $context['promo_code']) {
                return $currentPrice;
            }
        }
        
        return $this->applyPriceAdjustment($currentPrice, $rule);
    }
    
    /**
     * Apply occupancy-based pricing rule (for hotels)
     */
    private function applyOccupancyRule(float $currentPrice, array $rule, array $context): float
    {
        $occupancyRate = $context['occupancy_rate'] ?? 50;
        
        foreach ($rule['occupancy_brackets'] as $bracket) {
            if ($occupancyRate >= $bracket['min'] && $occupancyRate <= $bracket['max']) {
                return $this->applyPriceAdjustment($currentPrice, $bracket);
            }
        }
        
        return $currentPrice;
    }
    
    /**
     * Apply advance booking pricing rule
     */
    private function applyAdvanceBookingRule(float $currentPrice, array $rule, array $context): float
    {
        if (!isset($context['booking_date'], $context['service_date'])) {
            return $currentPrice;
        }
        
        $bookingDate = Carbon::parse($context['booking_date']);
        $serviceDate = Carbon::parse($context['service_date']);
        $daysInAdvance = $bookingDate->diffInDays($serviceDate);
        
        foreach ($rule['advance_brackets'] as $bracket) {
            if ($daysInAdvance >= $bracket['min_days']) {
                return $this->applyPriceAdjustment($currentPrice, $bracket);
            }
        }
        
        return $currentPrice;
    }
    
    /**
     * Apply length of stay pricing rule (for hotels)
     */
    private function applyLengthOfStayRule(float $currentPrice, array $rule, array $context): float
    {
        $nights = $context['nights'] ?? 1;
        
        foreach ($rule['stay_brackets'] as $bracket) {
            if ($nights >= $bracket['min_nights']) {
                return $this->applyPriceAdjustment($currentPrice, $bracket);
            }
        }
        
        return $currentPrice;
    }
    
    /**
     * Apply price adjustment based on adjustment type
     */
    private function applyPriceAdjustment(float $currentPrice, array $adjustment): float
    {
        switch ($adjustment['adjustment_type']) {
            case 'percentage':
                return $currentPrice * (1 + ($adjustment['adjustment_value'] / 100));
            case 'amount':
                return $currentPrice + $adjustment['adjustment_value'];
            case 'fixed':
                return $adjustment['adjustment_value'];
            case 'discount_percentage':
                return $currentPrice * (1 - ($adjustment['adjustment_value'] / 100));
            case 'discount_amount':
                return $currentPrice - $adjustment['adjustment_value'];
            default:
                return $currentPrice;
        }
    }
    
    /**
     * Check if a specific condition matches the context
     */
    private function checkCondition(array $condition, array $context): bool
    {
        $field = $condition['field'];
        $operator = $condition['operator'];
        $value = $condition['value'];
        $contextValue = $context[$field] ?? null;
        
        switch ($operator) {
            case 'equals':
                return $contextValue == $value;
            case 'in':
                return in_array($contextValue, (array) $value);
            case 'greater_than':
                return $contextValue > $value;
            case 'less_than':
                return $contextValue < $value;
            case 'contains':
                return strpos($contextValue, $value) !== false;
            default:
                return false;
        }
    }
    
    /**
     * Create a seasonal pricing rule
     */
    public static function createSeasonalRule(string $name, Carbon $startDate, Carbon $endDate, string $adjustmentType, float $adjustmentValue, array $appliesTo = []): array
    {
        return [
            'type' => 'seasonal',
            'name' => $name,
            'start_date' => $startDate->toISOString(),
            'end_date' => $endDate->toISOString(),
            'adjustment_type' => $adjustmentType,
            'adjustment_value' => $adjustmentValue,
            'applies_to' => $appliesTo,
            'created_at' => now()->toISOString(),
        ];
    }
    
    /**
     * Create a demand-based pricing rule
     */
    public static function createDemandRule(string $name, array $demandMultipliers, array $appliesTo = []): array
    {
        return [
            'type' => 'demand',
            'name' => $name,
            'demand_multipliers' => $demandMultipliers, // e.g., ['low' => 0.8, 'normal' => 1.0, 'high' => 1.5]
            'applies_to' => $appliesTo,
            'created_at' => now()->toISOString(),
        ];
    }
    
    /**
     * Create a promotional pricing rule
     */
    public static function createPromotionalRule(string $name, Carbon $startDate, Carbon $endDate, string $adjustmentType, float $adjustmentValue, string $promoCode = null): array
    {
        $rule = [
            'type' => 'promotional',
            'name' => $name,
            'start_date' => $startDate->toISOString(),
            'end_date' => $endDate->toISOString(),
            'adjustment_type' => $adjustmentType,
            'adjustment_value' => $adjustmentValue,
            'created_at' => now()->toISOString(),
        ];
        
        if ($promoCode) {
            $rule['promo_code'] = $promoCode;
        }
        
        return $rule;
    }
    
    /**
     * Create an occupancy-based pricing rule
     */
    public static function createOccupancyRule(string $name, array $occupancyBrackets): array
    {
        return [
            'type' => 'occupancy',
            'name' => $name,
            'occupancy_brackets' => $occupancyBrackets,
            'created_at' => now()->toISOString(),
        ];
    }
    
    /**
     * Create an advance booking pricing rule
     */
    public static function createAdvanceBookingRule(string $name, array $advanceBrackets): array
    {
        return [
            'type' => 'advance_booking',
            'name' => $name,
            'advance_brackets' => $advanceBrackets,
            'created_at' => now()->toISOString(),
        ];
    }
}
