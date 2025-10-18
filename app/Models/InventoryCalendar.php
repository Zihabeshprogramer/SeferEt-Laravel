<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * InventoryCalendar Model - Real-time Availability Tracking
 * 
 * Manages daily inventory capacity and availability for atomic allocation operations
 * 
 * @property int $id
 * @property string $provider_type
 * @property int $item_id
 * @property date $date
 * @property int $total_capacity
 * @property int $allocated_capacity
 * @property int $available_capacity
 * @property int $blocked_capacity
 * @property decimal $base_price
 * @property string $currency
 * @property array $pricing_tiers
 * @property bool $is_available
 * @property bool $is_bookable
 * @property int $version
 */
class InventoryCalendar extends Model
{
    use HasFactory;
    
    /**
     * Provider types
     */
    public const PROVIDER_HOTEL = 'hotel';
    public const PROVIDER_FLIGHT = 'flight';
    public const PROVIDER_TRANSPORT = 'transport';
    
    /**
     * Available provider types
     */
    public const PROVIDER_TYPES = [
        self::PROVIDER_HOTEL,
        self::PROVIDER_FLIGHT,
        self::PROVIDER_TRANSPORT,
    ];
    
    protected $table = 'inventory_calendar';
    
    protected $fillable = [
        'provider_type',
        'item_id',
        'date',
        'total_capacity',
        'allocated_capacity',
        'available_capacity',
        'blocked_capacity',
        'base_price',
        'currency',
        'pricing_tiers',
        'is_available',
        'is_bookable',
        'restriction_notes',
        'version',
        'last_updated_at',
        'metadata',
    ];
    
    protected $casts = [
        'date' => 'date',
        'total_capacity' => 'integer',
        'allocated_capacity' => 'integer',
        'available_capacity' => 'integer',
        'blocked_capacity' => 'integer',
        'base_price' => 'decimal:2',
        'pricing_tiers' => 'array',
        'metadata' => 'array',
        'is_available' => 'boolean',
        'is_bookable' => 'boolean',
        'version' => 'integer',
        'last_updated_at' => 'datetime',
    ];
    
    /**
     * Scope for specific provider type
     */
    public function scopeByProviderType($query, string $providerType)
    {
        return $query->where('provider_type', $providerType);
    }
    
    /**
     * Scope for specific item
     */
    public function scopeForItem($query, int $itemId)
    {
        return $query->where('item_id', $itemId);
    }
    
    /**
     * Scope for specific date
     */
    public function scopeForDate($query, Carbon $date)
    {
        return $query->where('date', $date->format('Y-m-d'));
    }
    
    /**
     * Scope for date range
     */
    public function scopeForDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
    }
    
    /**
     * Scope for available inventory
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)
                    ->where('is_bookable', true)
                    ->where('available_capacity', '>', 0);
    }
    
    /**
     * Scope for bookable inventory
     */
    public function scopeBookable($query)
    {
        return $query->where('is_bookable', true);
    }
    
    /**
     * Check if there's sufficient capacity for allocation
     */
    public function hasSufficientCapacity(int $requestedQuantity): bool
    {
        return $this->is_available && 
               $this->is_bookable && 
               $this->available_capacity >= $requestedQuantity;
    }
    
    /**
     * Atomically allocate capacity - CRITICAL for concurrency safety
     */
    public function allocateCapacity(int $quantity): bool
    {
        // Use optimistic locking to prevent race conditions
        $result = static::where('id', $this->id)
                        ->where('version', $this->version)
                        ->where('available_capacity', '>=', $quantity)
                        ->where('is_available', true)
                        ->where('is_bookable', true)
                        ->update([
                            'allocated_capacity' => DB::raw("allocated_capacity + {$quantity}"),
                            'available_capacity' => DB::raw("available_capacity - {$quantity}"),
                            'version' => DB::raw('version + 1'),
                            'last_updated_at' => now(),
                        ]);
        
        if ($result) {
            // Refresh the model with updated values
            $this->refresh();
            return true;
        }
        
        return false; // Allocation failed (insufficient capacity or version conflict)
    }
    
    /**
     * Atomically release allocated capacity
     */
    public function releaseCapacity(int $quantity): bool
    {
        // Ensure we don't release more than allocated
        $maxReleasable = min($quantity, $this->allocated_capacity);
        
        if ($maxReleasable <= 0) {
            return false;
        }
        
        $result = static::where('id', $this->id)
                        ->where('version', $this->version)
                        ->where('allocated_capacity', '>=', $maxReleasable)
                        ->update([
                            'allocated_capacity' => DB::raw("allocated_capacity - {$maxReleasable}"),
                            'available_capacity' => DB::raw("available_capacity + {$maxReleasable}"),
                            'version' => DB::raw('version + 1'),
                            'last_updated_at' => now(),
                        ]);
        
        if ($result) {
            $this->refresh();
            return true;
        }
        
        return false;
    }
    
    /**
     * Block capacity (for maintenance, etc.)
     */
    public function blockCapacity(int $quantity, string $reason = ''): bool
    {
        $result = static::where('id', $this->id)
                        ->where('version', $this->version)
                        ->where('available_capacity', '>=', $quantity)
                        ->update([
                            'blocked_capacity' => DB::raw("blocked_capacity + {$quantity}"),
                            'available_capacity' => DB::raw("available_capacity - {$quantity}"),
                            'version' => DB::raw('version + 1'),
                            'restriction_notes' => $reason,
                            'last_updated_at' => now(),
                        ]);
        
        if ($result) {
            $this->refresh();
            return true;
        }
        
        return false;
    }
    
    /**
     * Unblock capacity
     */
    public function unblockCapacity(int $quantity): bool
    {
        $maxUnblockable = min($quantity, $this->blocked_capacity);
        
        if ($maxUnblockable <= 0) {
            return false;
        }
        
        $result = static::where('id', $this->id)
                        ->where('version', $this->version)
                        ->where('blocked_capacity', '>=', $maxUnblockable)
                        ->update([
                            'blocked_capacity' => DB::raw("blocked_capacity - {$maxUnblockable}"),
                            'available_capacity' => DB::raw("available_capacity + {$maxUnblockable}"),
                            'version' => DB::raw('version + 1'),
                            'last_updated_at' => now(),
                        ]);
        
        if ($result) {
            $this->refresh();
            return true;
        }
        
        return false;
    }
    
    /**
     * Update total capacity
     */
    public function updateTotalCapacity(int $newCapacity): bool
    {
        $difference = $newCapacity - $this->total_capacity;
        
        $result = static::where('id', $this->id)
                        ->where('version', $this->version)
                        ->update([
                            'total_capacity' => $newCapacity,
                            'available_capacity' => DB::raw("available_capacity + {$difference}"),
                            'version' => DB::raw('version + 1'),
                            'last_updated_at' => now(),
                        ]);
        
        if ($result) {
            $this->refresh();
            return true;
        }
        
        return false;
    }
    
    /**
     * Set availability status
     */
    public function setAvailability(bool $isAvailable, string $reason = ''): bool
    {
        return $this->update([
            'is_available' => $isAvailable,
            'restriction_notes' => $reason,
            'version' => $this->version + 1,
            'last_updated_at' => now(),
        ]);
    }
    
    /**
     * Set bookable status
     */
    public function setBookable(bool $isBookable, string $reason = ''): bool
    {
        return $this->update([
            'is_bookable' => $isBookable,
            'restriction_notes' => $reason,
            'version' => $this->version + 1,
            'last_updated_at' => now(),
        ]);
    }
    
    /**
     * Get capacity utilization percentage
     */
    public function getUtilizationPercentageAttribute(): float
    {
        if ($this->total_capacity === 0) {
            return 0;
        }
        
        $utilized = $this->allocated_capacity + $this->blocked_capacity;
        return ($utilized / $this->total_capacity) * 100;
    }
    
    /**
     * Get availability percentage
     */
    public function getAvailabilityPercentageAttribute(): float
    {
        if ($this->total_capacity === 0) {
            return 0;
        }
        
        return ($this->available_capacity / $this->total_capacity) * 100;
    }
    
    /**
     * Check if inventory is fully booked
     */
    public function isFullyBookedAttribute(): bool
    {
        return $this->available_capacity === 0 && $this->total_capacity > 0;
    }
    
    /**
     * Check if inventory is overbooked
     */
    public function isOverbookedAttribute(): bool
    {
        return ($this->allocated_capacity + $this->blocked_capacity) > $this->total_capacity;
    }
    
    /**
     * Get capacity status for display
     */
    public function getCapacityStatusAttribute(): string
    {
        if (!$this->is_available) {
            return 'Not Available';
        }
        
        if (!$this->is_bookable) {
            return 'Not Bookable';
        }
        
        if ($this->is_fully_booked) {
            return 'Fully Booked';
        }
        
        if ($this->is_overbooked) {
            return 'Overbooked';
        }
        
        if ($this->available_capacity <= ($this->total_capacity * 0.1)) {
            return 'Low Availability';
        }
        
        return 'Available';
    }
    
    /**
     * Get capacity status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        if (!$this->is_available || !$this->is_bookable) {
            return 'badge-danger';
        }
        
        if ($this->is_overbooked) {
            return 'badge-danger';
        }
        
        if ($this->is_fully_booked) {
            return 'badge-warning';
        }
        
        $availabilityPercentage = $this->availability_percentage;
        
        if ($availabilityPercentage <= 10) {
            return 'badge-warning';
        } elseif ($availabilityPercentage <= 30) {
            return 'badge-info';
        } else {
            return 'badge-success';
        }
    }
    
    /**
     * Static method to initialize or update inventory for date range
     */
    public static function initializeInventoryForDateRange(
        string $providerType,
        int $itemId,
        Carbon $startDate,
        Carbon $endDate,
        int $totalCapacity,
        float $basePrice = null,
        array $metadata = []
    ): int {
        $created = 0;
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            static::updateOrCreate(
                [
                    'provider_type' => $providerType,
                    'item_id' => $itemId,
                    'date' => $currentDate->format('Y-m-d'),
                ],
                [
                    'total_capacity' => $totalCapacity,
                    'available_capacity' => $totalCapacity,
                    'allocated_capacity' => 0,
                    'blocked_capacity' => 0,
                    'base_price' => $basePrice,
                    'currency' => 'USD',
                    'is_available' => true,
                    'is_bookable' => true,
                    'metadata' => $metadata,
                    'version' => 1,
                    'last_updated_at' => now(),
                ]
            );
            
            $created++;
            $currentDate->addDay();
        }
        
        return $created;
    }
    
    /**
     * Get inventory summary for date range
     */
    public static function getInventorySummaryForDateRange(
        string $providerType,
        int $itemId,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $inventory = static::byProviderType($providerType)
                          ->forItem($itemId)
                          ->forDateRange($startDate, $endDate)
                          ->get();
        
        return [
            'total_days' => $inventory->count(),
            'available_days' => $inventory->where('is_available', true)->count(),
            'total_capacity' => $inventory->sum('total_capacity'),
            'allocated_capacity' => $inventory->sum('allocated_capacity'),
            'available_capacity' => $inventory->sum('available_capacity'),
            'blocked_capacity' => $inventory->sum('blocked_capacity'),
            'utilization_percentage' => $inventory->count() > 0 ? 
                ($inventory->sum('allocated_capacity') + $inventory->sum('blocked_capacity')) / $inventory->sum('total_capacity') * 100 : 0,
            'availability_percentage' => $inventory->count() > 0 ? 
                $inventory->sum('available_capacity') / $inventory->sum('total_capacity') * 100 : 0,
        ];
    }
    
    /**
     * Check availability for date range and quantity
     */
    public static function checkAvailabilityForDateRange(
        string $providerType,
        int $itemId,
        Carbon $startDate,
        Carbon $endDate,
        int $requestedQuantity
    ): array {
        $inventory = static::byProviderType($providerType)
                          ->forItem($itemId)
                          ->forDateRange($startDate, $endDate)
                          ->available()
                          ->get();
        
        $unavailableDates = [];
        $insufficientDates = [];
        
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dayInventory = $inventory->where('date', $currentDate->format('Y-m-d'))->first();
            
            if (!$dayInventory) {
                $unavailableDates[] = $currentDate->format('Y-m-d');
            } elseif (!$dayInventory->hasSufficientCapacity($requestedQuantity)) {
                $insufficientDates[] = [
                    'date' => $currentDate->format('Y-m-d'),
                    'available' => $dayInventory->available_capacity,
                    'requested' => $requestedQuantity,
                ];
            }
            
            $currentDate->addDay();
        }
        
        return [
            'available' => empty($unavailableDates) && empty($insufficientDates),
            'unavailable_dates' => $unavailableDates,
            'insufficient_capacity_dates' => $insufficientDates,
            'total_available_capacity' => $inventory->sum('available_capacity'),
        ];
    }
}