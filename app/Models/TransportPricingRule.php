<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportPricingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'rule_name',
        'description',
        'rule_type',
        'provider_id',
        'transport_service_id',
        'start_date',
        'end_date',
        'adjustment_type',
        'adjustment_value',
        'min_passengers',
        'max_passengers',
        'days_of_week',
        'priority',
        'is_active',
        'conditions',
        'applicable_routes'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'adjustment_value' => 'decimal:2',
        'is_active' => 'boolean',
        'days_of_week' => 'array',
        'conditions' => 'array',
        'applicable_routes' => 'array'
    ];

    // Relationships
    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function transportService(): BelongsTo
    {
        return $this->belongsTo(TransportService::class, 'transport_service_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForProvider($query, $providerId)
    {
        return $query->where(function ($q) use ($providerId) {
            $q->whereNull('provider_id')
              ->orWhere('provider_id', $providerId);
        });
    }

    public function scopeForService($query, $serviceId)
    {
        return $query->where(function ($q) use ($serviceId) {
            $q->whereNull('transport_service_id')
              ->orWhere('transport_service_id', $serviceId);
        });
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate);
    }

    public function scopeByPriority($query, $direction = 'desc')
    {
        return $query->orderBy('priority', $direction);
    }

    public function scopeForTransportType($query, $type)
    {
        return $query->where(function ($q) use ($type) {
            $q->whereNull('transport_type')
              ->orWhere('transport_type', $type);
        });
    }

    public function scopeForRouteType($query, $routeType)
    {
        return $query->where(function ($q) use ($routeType) {
            $q->whereNull('route_type')
              ->orWhere('route_type', $routeType);
        });
    }

    // Helper methods
    public function isApplicableForPassengers($passengerCount)
    {
        if ($this->min_passengers && $passengerCount < $this->min_passengers) {
            return false;
        }

        if ($this->max_passengers && $passengerCount > $this->max_passengers) {
            return false;
        }

        return true;
    }

    public function isApplicableForDistance($distance)
    {
        if ($this->min_distance && $distance < $this->min_distance) {
            return false;
        }

        if ($this->max_distance && $distance > $this->max_distance) {
            return false;
        }

        return true;
    }

    public function isApplicableForRoute($from, $to)
    {
        if (!$this->applicable_routes || empty($this->applicable_routes)) {
            return true; // Apply to all routes if none specified
        }

        foreach ($this->applicable_routes as $route) {
            if (isset($route['from']) && isset($route['to']) &&
                strtolower($route['from']) === strtolower($from) &&
                strtolower($route['to']) === strtolower($to)) {
                return true;
            }
        }

        return false;
    }

    public function isApplicableForDate($date)
    {
        $dateObj = is_string($date) ? \Carbon\Carbon::parse($date) : $date;
        
        if ($dateObj->lt($this->start_date) || $dateObj->gt($this->end_date)) {
            return false;
        }

        // Check day of week if specified
        if ($this->days_of_week && !empty($this->days_of_week)) {
            $dayOfWeek = strtolower($dateObj->format('l'));
            if (!in_array($dayOfWeek, $this->days_of_week)) {
                return false;
            }
        }

        return true;
    }

    public function calculateAdjustment($basePrice)
    {
        switch ($this->adjustment_type) {
            case 'percentage':
                return ($basePrice * $this->adjustment_value) / 100;
            
            case 'fixed':
                return $this->adjustment_value;
            
            case 'multiply':
                return $basePrice * ($this->adjustment_value - 1);
            
            default:
                return 0;
        }
    }

    public function getFormattedAdjustmentAttribute()
    {
        switch ($this->adjustment_type) {
            case 'percentage':
                return $this->adjustment_value . '%';
            
            case 'fixed':
                return 'SAR ' . number_format($this->adjustment_value, 2);
            
            case 'multiply':
                return 'x' . $this->adjustment_value;
            
            default:
                return $this->adjustment_value;
        }
    }

    public function getRuleTypeDisplayAttribute()
    {
        $types = [
            'seasonal' => 'Seasonal Pricing',
            'advance_booking' => 'Advance Booking Discount',
            'route_based' => 'Route Based Pricing',
            'day_of_week' => 'Day of Week Adjustment',
            'demand_based' => 'Demand Based Pricing',
            'promotional' => 'Promotional Pricing',
            'distance_based' => 'Distance Based Pricing',
            'passenger_count' => 'Passenger Count Pricing'
        ];

        return $types[$this->rule_type] ?? $this->rule_type;
    }
    
    /**
     * Check if the rule is applicable for given parameters
     */
    public function isApplicable($date, $passengerCount = 1, $routeFrom = null, $routeTo = null)
    {
        // Check date range
        if (!$this->isApplicableForDate($date)) {
            return false;
        }
        
        // Check passenger count
        if (!$this->isApplicableForPassengers($passengerCount)) {
            return false;
        }
        
        // Check route
        if ($routeFrom && $routeTo && !$this->isApplicableForRoute($routeFrom, $routeTo)) {
            return false;
        }
        
        return true;
    }
}
