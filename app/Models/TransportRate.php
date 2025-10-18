<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User;
use App\Models\TransportPricingRule;
use Carbon\Carbon;

class TransportRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'transport_service_id',
        'provider_id',
        'date',
        'route_from',
        'route_to',
        'passenger_type',
        'base_rate',
        'currency',
        'notes',
        'is_available'
    ];

    protected $casts = [
        'date' => 'date',
        'base_rate' => 'decimal:2',
        'is_available' => 'boolean'
    ];

    // Relationships
    public function transportService(): BelongsTo
    {
        return $this->belongsTo(TransportService::class, 'transport_service_id');
    }
    
    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_available', true);
    }
    
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeForService($query, $serviceId)
    {
        return $query->where('transport_service_id', $serviceId);
    }

    public function scopeForRoute($query, $from, $to)
    {
        return $query->where('route_from', $from)
                    ->where('route_to', $to);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->where('date', '>=', $startDate)
                    ->where('date', '<=', $endDate);
    }

    public function scopeByDate($query, $direction = 'asc')
    {
        return $query->orderBy('date', $direction);
    }

    // Helper methods
    public function getFormattedRateAttribute()
    {
        return $this->currency . ' ' . number_format($this->base_rate, 2);
    }
    
    public function getCurrentRate(): ?self
    {
        return static::where('transport_service_id', $this->transport_service_id)
                    ->where('route_from', $this->route_from)
                    ->where('route_to', $this->route_to)
                    ->where('passenger_type', $this->passenger_type)
                    ->where('date', '>=', now()->format('Y-m-d'))
                    ->where('is_available', true)
                    ->orderBy('date', 'asc')
                    ->first();
    }
    
    public function isFutureRate(): bool
    {
        return Carbon::parse($this->date)->isFuture();
    }
    
    public function isPastRate(): bool
    {
        return Carbon::parse($this->date)->isPast();
    }
    
    public function getFormattedDateAttribute(): string
    {
        return Carbon::parse($this->date)->format('M d, Y');
    }

    public function getRouteDisplayAttribute()
    {
        return $this->route_from . ' → ' . $this->route_to;
    }

    public function isPastDate()
    {
        return $this->date->lt(Carbon::today());
    }

    public function isFutureDate()
    {
        return $this->date->gt(Carbon::today());
    }

    public function isToday()
    {
        return $this->date->isToday();
    }

    /**
     * Get rate with pricing rules applied
     */
    public function getRateWithPricingRules(int $passengerCount = 1): float
    {
        $baseRate = $this->base_rate;
        $service = $this->transportService;
        
        if (!$service) {
            return $baseRate;
        }
        
        $pricingRules = TransportPricingRule::where('transport_service_id', $service->id)
                                          ->where('is_active', true)
                                          ->orderBy('priority', 'asc')
                                          ->get();
        
        $finalRate = $baseRate;
        
        foreach ($pricingRules as $rule) {
            if ($rule->isApplicable($this->date, $passengerCount, $this->route_from, $this->route_to)) {
                $adjustment = $rule->calculateAdjustment($finalRate);
                $finalRate += $adjustment;
            }
        }
        
        return $finalRate;
    }
    
    // Static helper methods
    public static function getRateForDate(int $serviceId, string $routeFrom, string $routeTo, string $passengerType, string $date): ?self
    {
        return static::where('transport_service_id', $serviceId)
                    ->where('route_from', $routeFrom)
                    ->where('route_to', $routeTo)
                    ->where('passenger_type', $passengerType)
                    ->where('date', $date)
                    ->where('is_available', true)
                    ->first();
    }
    
    public static function getRatesForRange(int $serviceId, string $routeFrom, string $routeTo, string $passengerType, string $startDate, string $endDate): Collection
    {
        return static::where('transport_service_id', $serviceId)
                    ->where('route_from', $routeFrom)
                    ->where('route_to', $routeTo)
                    ->where('passenger_type', $passengerType)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->where('is_available', true)
                    ->orderBy('date')
                    ->get();
    }
    
    public static function getRatesByRoute(int $serviceId, string $routeFrom, string $routeTo): Collection
    {
        return static::where('transport_service_id', $serviceId)
                    ->where('route_from', $routeFrom)
                    ->where('route_to', $routeTo)
                    ->where('date', '>=', now()->format('Y-m-d'))
                    ->where('is_available', true)
                    ->orderBy('date')
                    ->orderBy('passenger_type')
                    ->get();
    }
    
    public static function getLatestRateForRoute(int $serviceId, string $routeFrom, string $routeTo, string $passengerType): ?self
    {
        return static::where('transport_service_id', $serviceId)
                    ->where('route_from', $routeFrom)
                    ->where('route_to', $routeTo)
                    ->where('passenger_type', $passengerType)
                    ->where('is_available', true)
                    ->orderBy('date', 'desc')
                    ->first();
    }
    
    public static function getAverageRateForRange(int $serviceId, string $routeFrom, string $routeTo, string $passengerType, string $startDate, string $endDate): float
    {
        return static::where('transport_service_id', $serviceId)
                    ->where('route_from', $routeFrom)
                    ->where('route_to', $routeTo)
                    ->where('passenger_type', $passengerType)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->where('is_available', true)
                    ->avg('base_rate') ?: 0;
    }
    
    public static function getAvailableRoutes($serviceId)
    {
        return static::where('transport_service_id', $serviceId)
                    ->where('is_available', true)
                    ->select('route_from', 'route_to')
                    ->distinct()
                    ->get()
                    ->map(function ($rate) {
                        return [
                            'from' => $rate->route_from,
                            'to' => $rate->route_to,
                            'display' => $rate->route_display
                        ];
                    });
    }
}
