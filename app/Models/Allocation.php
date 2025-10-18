<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Allocation Model - Inventory Reservation Management
 * 
 * Records the actual inventory allocations/holds when service requests are approved
 * 
 * @property int $id
 * @property string $uuid
 * @property int $service_request_id
 * @property int $provider_id
 * @property string $provider_type
 * @property int $item_id
 * @property int $quantity
 * @property date $start_date
 * @property date $end_date
 * @property string $status
 * @property Carbon $allocated_at
 * @property Carbon $expires_at
 * @property array $metadata
 * @property decimal $allocated_price
 * @property int $version
 */
class Allocation extends Model
{
    use HasFactory;
    
    /**
     * Allocation statuses
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_RELEASED = 'released';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_USED = 'used';
    public const STATUS_CANCELLED = 'cancelled';
    
    /**
     * Provider types
     */
    public const PROVIDER_HOTEL = 'hotel';
    public const PROVIDER_FLIGHT = 'flight';
    public const PROVIDER_TRANSPORT = 'transport';
    
    /**
     * Available statuses
     */
    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_RELEASED,
        self::STATUS_EXPIRED,
        self::STATUS_USED,
        self::STATUS_CANCELLED,
    ];
    
    /**
     * Available provider types
     */
    public const PROVIDER_TYPES = [
        self::PROVIDER_HOTEL,
        self::PROVIDER_FLIGHT,
        self::PROVIDER_TRANSPORT,
    ];
    
    protected $fillable = [
        'uuid',
        'service_request_id',
        'provider_id',
        'provider_type',
        'item_id',
        'quantity',
        'start_date',
        'end_date',
        'status',
        'allocated_at',
        'expires_at',
        'released_at',
        'used_at',
        'metadata',
        'allocation_reference',
        'notes',
        'allocated_price',
        'currency',
        'commission_amount',
        'version',
        'created_by',
        'released_by',
        'release_reason',
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'allocated_at' => 'datetime',
        'expires_at' => 'datetime',
        'released_at' => 'datetime',
        'used_at' => 'datetime',
        'metadata' => 'array',
        'allocated_price' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'quantity' => 'integer',
        'version' => 'integer',
    ];
    
    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($allocation) {
            if (empty($allocation->uuid)) {
                $allocation->uuid = Str::uuid()->toString();
            }
            
            if (empty($allocation->allocated_at)) {
                $allocation->allocated_at = now();
            }
        });
    }
    
    /**
     * Get the service request this allocation belongs to
     */
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }
    
    /**
     * Get the provider
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
    
    /**
     * Get the user who created this allocation
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Get the user who released this allocation
     */
    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }
    
    /**
     * Scope for active allocations
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
    
    /**
     * Scope for expired allocations
     */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED)
                    ->orWhere(function ($q) {
                        $q->where('status', self::STATUS_ACTIVE)
                          ->where('expires_at', '<', now());
                    });
    }
    
    /**
     * Scope for allocations by provider type
     */
    public function scopeByProviderType($query, string $providerType)
    {
        return $query->where('provider_type', $providerType);
    }
    
    /**
     * Scope for allocations by date range
     */
    public function scopeByDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhere(function ($subQ) use ($startDate, $endDate) {
                  $subQ->where('start_date', '<=', $startDate)
                       ->where('end_date', '>=', $endDate);
              });
        });
    }
    
    /**
     * Check if allocation is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE && 
               (!$this->expires_at || $this->expires_at->isFuture());
    }
    
    /**
     * Check if allocation is expired
     */
    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED || 
               ($this->expires_at && $this->expires_at->isPast() && $this->status === self::STATUS_ACTIVE);
    }
    
    /**
     * Check if allocation can be released
     */
    public function canBeReleased(): bool
    {
        return $this->status === self::STATUS_ACTIVE && !$this->isExpired();
    }
    
    /**
     * Release the allocation
     */
    public function release(string $reason = '', User $releasedBy = null): bool
    {
        if (!$this->canBeReleased()) {
            return false;
        }
        
        return $this->update([
            'status' => self::STATUS_RELEASED,
            'released_at' => now(),
            'released_by' => $releasedBy ? $releasedBy->id : auth()->id(),
            'release_reason' => $reason,
            'version' => $this->version + 1,
        ]);
    }
    
    /**
     * Mark allocation as used/consumed
     */
    public function markAsUsed(User $usedBy = null): bool
    {
        if (!$this->isActive()) {
            return false;
        }
        
        return $this->update([
            'status' => self::STATUS_USED,
            'used_at' => now(),
            'version' => $this->version + 1,
        ]);
    }
    
    /**
     * Mark allocation as expired
     */
    public function markExpired(): bool
    {
        return $this->update([
            'status' => self::STATUS_EXPIRED,
            'version' => $this->version + 1,
        ]);
    }
    
    /**
     * Cancel the allocation
     */
    public function cancel(string $reason = ''): bool
    {
        if ($this->status === self::STATUS_USED) {
            return false; // Cannot cancel used allocations
        }
        
        return $this->update([
            'status' => self::STATUS_CANCELLED,
            'released_at' => now(),
            'release_reason' => $reason,
            'version' => $this->version + 1,
        ]);
    }
    
    /**
     * Get days until expiration
     */
    public function getDaysUntilExpirationAttribute(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }
        
        return max(0, now()->diffInDays($this->expires_at, false));
    }
    
    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'badge-success',
            self::STATUS_RELEASED => 'badge-secondary',
            self::STATUS_EXPIRED => 'badge-dark',
            self::STATUS_USED => 'badge-info',
            self::STATUS_CANCELLED => 'badge-danger',
            default => 'badge-secondary',
        };
    }
    
    /**
     * Get allocation duration in days
     */
    public function getDurationDaysAttribute(): int
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }
        
        return $this->start_date->diffInDays($this->end_date) + 1; // Include both start and end dates
    }
    
    /**
     * Calculate total value of allocation
     */
    public function getTotalValueAttribute(): float
    {
        if (!$this->allocated_price) {
            return 0;
        }
        
        return $this->allocated_price * $this->quantity * $this->duration_days;
    }
    
    /**
     * Auto-expire old allocations (static method for scheduled jobs)
     */
    public static function expireOldAllocations(): int
    {
        return static::where('status', self::STATUS_ACTIVE)
                     ->where('expires_at', '<', now())
                     ->update([
                         'status' => self::STATUS_EXPIRED,
                         'version' => \DB::raw('version + 1'),
                     ]);
    }
    
    /**
     * Get allocation summary for display
     */
    public function getSummaryAttribute(): string
    {
        $summary = "{$this->quantity} ";
        
        switch ($this->provider_type) {
            case self::PROVIDER_HOTEL:
                $summary .= $this->quantity === 1 ? 'room' : 'rooms';
                break;
            case self::PROVIDER_FLIGHT:
                $summary .= $this->quantity === 1 ? 'seat' : 'seats';
                break;
            case self::PROVIDER_TRANSPORT:
                $summary .= $this->quantity === 1 ? 'passenger' : 'passengers';
                break;
            default:
                $summary .= $this->quantity === 1 ? 'unit' : 'units';
        }
        
        if ($this->start_date && $this->end_date) {
            $summary .= " from {$this->start_date->format('M j')} to {$this->end_date->format('M j, Y')}";
        }
        
        return $summary;
    }
    
    /**
     * Check if allocation overlaps with given date range
     */
    public function overlapsWithDates(Carbon $startDate, Carbon $endDate): bool
    {
        if (!$this->start_date || !$this->end_date) {
            return false;
        }
        
        return $this->start_date <= $endDate && $this->end_date >= $startDate;
    }
    
    /**
     * Get provider service name for display
     */
    public function getProviderServiceNameAttribute(): string
    {
        switch ($this->provider_type) {
            case self::PROVIDER_HOTEL:
                return 'Hotel Service';
            case self::PROVIDER_FLIGHT:
                return 'Flight Service';
            case self::PROVIDER_TRANSPORT:
                return 'Transport Service';
            default:
                return 'Service';
        }
    }
}