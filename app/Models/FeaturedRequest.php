<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * FeaturedRequest Model
 * 
 * Manages feature requests for products (flights, hotels, packages)
 * 
 * @property int $id
 * @property int $product_id
 * @property string $product_type
 * @property int $requested_by
 * @property string $status
 * @property int|null $approved_by
 * @property \Carbon\Carbon|null $approved_at
 * @property int $priority_level
 * @property \Carbon\Carbon|null $start_date
 * @property \Carbon\Carbon|null $end_date
 * @property string|null $notes
 * @property string|null $rejection_reason
 */
class FeaturedRequest extends Model
{
    use HasFactory;

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Product type constants
     */
    public const PRODUCT_TYPE_FLIGHT = 'flight';
    public const PRODUCT_TYPE_HOTEL = 'hotel';
    public const PRODUCT_TYPE_PACKAGE = 'package';

    /**
     * Available statuses
     */
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
    ];

    /**
     * Available product types
     */
    public const PRODUCT_TYPES = [
        self::PRODUCT_TYPE_FLIGHT,
        self::PRODUCT_TYPE_HOTEL,
        self::PRODUCT_TYPE_PACKAGE,
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_id',
        'product_type',
        'requested_by',
        'status',
        'approved_by',
        'approved_at',
        'priority_level',
        'start_date',
        'end_date',
        'notes',
        'rejection_reason',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'approved_at' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
        'priority_level' => 'integer',
        'product_id' => 'integer',
        'requested_by' => 'integer',
        'approved_by' => 'integer',
    ];

    /**
     * Get the product (polymorphic relation)
     */
    public function product(): MorphTo
    {
        return $this->morphTo('product', 'product_type', 'product_id');
    }

    /**
     * Get the user who requested the feature
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the admin who approved/rejected the request
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope for pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope for rejected requests
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Scope for requests by product type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('product_type', $type);
    }

    /**
     * Scope for requests by requester
     */
    public function scopeByRequester($query, int $userId)
    {
        return $query->where('requested_by', $userId);
    }

    /**
     * Scope for active featured items (approved and within date range)
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_APPROVED)
            ->where(function($q) {
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            });
    }

    /**
     * Scope for expired featured items
     */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_APPROVED)
            ->whereNotNull('end_date')
            ->where('end_date', '<', now());
    }

    /**
     * Scope to order by priority
     */
    public function scopeByPriority($query, string $direction = 'desc')
    {
        return $query->orderBy('priority_level', $direction);
    }

    /**
     * Check if request is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if request is approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if request is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check if the featured period is active
     */
    public function isActive(): bool
    {
        if (!$this->isApproved()) {
            return false;
        }

        $now = now();

        if ($this->start_date && $this->start_date->isFuture()) {
            return false;
        }

        if ($this->end_date && $this->end_date->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if the featured period has expired
     */
    public function isExpired(): bool
    {
        return $this->isApproved() && 
               $this->end_date && 
               $this->end_date->isPast();
    }

    /**
     * Get status badge color for display
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get product type display name
     */
    public function getProductTypeDisplayAttribute(): string
    {
        return match($this->product_type) {
            self::PRODUCT_TYPE_FLIGHT => 'Flight',
            self::PRODUCT_TYPE_HOTEL => 'Hotel',
            self::PRODUCT_TYPE_PACKAGE => 'Package',
            default => ucfirst($this->product_type)
        };
    }

    /**
     * Get the actual product model instance
     */
    public function getProductModel()
    {
        return match($this->product_type) {
            self::PRODUCT_TYPE_FLIGHT => Flight::find($this->product_id),
            self::PRODUCT_TYPE_HOTEL => Hotel::find($this->product_id),
            self::PRODUCT_TYPE_PACKAGE => Package::find($this->product_id),
            default => null
        };
    }

    /**
     * Get formatted date range
     */
    public function getDateRangeAttribute(): string
    {
        if (!$this->start_date && !$this->end_date) {
            return 'Indefinite';
        }

        $start = $this->start_date ? $this->start_date->format('M d, Y') : 'Now';
        $end = $this->end_date ? $this->end_date->format('M d, Y') : 'Indefinite';

        return "{$start} - {$end}";
    }
}
