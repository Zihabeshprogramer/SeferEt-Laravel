<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

/**
 * Ad Model
 * 
 * Manages promotional advertisements with approval workflow, scheduling, and analytics.
 * Supports polymorphic relationships for owners and products.
 * 
 * @property int $id
 * @property int $owner_id
 * @property string $owner_type
 * @property int|null $product_id
 * @property string|null $product_type
 * @property string $title
 * @property string|null $description
 * @property string|null $image_path
 * @property array|null $image_variants
 * @property string|null $cta_text
 * @property string|null $cta_action
 * @property float $cta_position
 * @property string $cta_style
 * @property string $status
 * @property int|null $approved_by
 * @property \Carbon\Carbon|null $approved_at
 * @property string|null $rejection_reason
 * @property \Carbon\Carbon|null $start_at
 * @property \Carbon\Carbon|null $end_at
 * @property int $priority
 * @property bool $is_active
 * @property array|null $analytics_meta
 */
class Ad extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Status constants
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Available statuses
     */
    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
    ];

    /**
     * Product type constants
     */
    public const PRODUCT_TYPE_HOTEL = 'hotel';
    public const PRODUCT_TYPE_PACKAGE = 'package';
    public const PRODUCT_TYPE_FLIGHT = 'flight';
    public const PRODUCT_TYPE_OFFER = 'offer';
    public const PRODUCT_TYPE_VEHICLE = 'vehicle';

    /**
     * Available product types
     */
    public const PRODUCT_TYPES = [
        self::PRODUCT_TYPE_HOTEL,
        self::PRODUCT_TYPE_PACKAGE,
        self::PRODUCT_TYPE_FLIGHT,
        self::PRODUCT_TYPE_OFFER,
        self::PRODUCT_TYPE_VEHICLE,
    ];

    /**
     * CTA style constants
     */
    public const CTA_STYLE_PRIMARY = 'primary';
    public const CTA_STYLE_SECONDARY = 'secondary';
    public const CTA_STYLE_SUCCESS = 'success';
    public const CTA_STYLE_DANGER = 'danger';
    public const CTA_STYLE_WARNING = 'warning';
    public const CTA_STYLE_INFO = 'info';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'owner_id',
        'owner_type',
        'product_id',
        'product_type',
        'title',
        'description',
        'image_path',
        'image_variants',
        'cta_text',
        'cta_action',
        'cta_position',
        'cta_position_x',
        'cta_position_y',
        'cta_style',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'admin_notes',
        'start_at',
        'end_at',
        'priority',
        'is_active',
        'is_local_owner',
        'device_type',
        'placement',
        'regions',
        'analytics_meta',
        'impressions_count',
        'clicks_count',
        'ctr',
        'max_impressions',
        'max_clicks',
        'budget',
        'spent',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'image_variants' => 'array',
        'regions' => 'array',
        'analytics_meta' => 'array',
        'approved_at' => 'datetime',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'priority' => 'integer',
        'is_active' => 'boolean',
        'is_local_owner' => 'boolean',
        'cta_position' => 'decimal:2',
        'cta_position_x' => 'decimal:2',
        'cta_position_y' => 'decimal:2',
        'impressions_count' => 'integer',
        'clicks_count' => 'integer',
        'ctr' => 'decimal:2',
        'max_impressions' => 'integer',
        'max_clicks' => 'integer',
        'budget' => 'decimal:2',
        'spent' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created(function ($ad) {
            $ad->logAudit('created', null, [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        static::updated(function ($ad) {
            if ($ad->wasChanged(['status', 'approved_by', 'approved_at', 'rejection_reason'])) {
                $eventType = match($ad->status) {
                    self::STATUS_PENDING => 'submitted',
                    self::STATUS_APPROVED => 'approved',
                    self::STATUS_REJECTED => 'rejected',
                    default => 'updated'
                };
                
                $ad->logAudit($eventType, $ad->getChanges(), [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        });

        static::deleted(function ($ad) {
            $ad->logAudit('deleted', null, [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });
    }

    // ===========================================
    // RELATIONSHIPS
    // ===========================================

    /**
     * Get the owner (polymorphic)
     */
    public function owner(): MorphTo
    {
        return $this->morphTo('owner', 'owner_type', 'owner_id');
    }

    /**
     * Get the product being advertised (polymorphic)
     */
    public function product(): MorphTo
    {
        return $this->morphTo('product', 'product_type', 'product_id');
    }

    /**
     * Get the admin who approved/rejected the ad
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get audit logs for this ad
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AdAuditLog::class);
    }

    /**
     * Get impressions for this ad
     */
    public function impressions(): HasMany
    {
        return $this->hasMany(AdImpression::class);
    }

    /**
     * Get clicks for this ad
     */
    public function clicks(): HasMany
    {
        return $this->hasMany(AdClick::class);
    }

    // ===========================================
    // SCOPES
    // ===========================================

    /**
     * Scope for draft ads
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope for pending ads
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for approved ads
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope for rejected ads
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Scope for active ads (approved, active toggle on, and within date range)
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED)
            ->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('start_at')
                  ->orWhere('start_at', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('end_at')
                  ->orWhere('end_at', '>=', now());
            });
    }

    /**
     * Scope for ads by owner
     */
    public function scopeByOwner(Builder $query, int $ownerId, ?string $ownerType = null): Builder
    {
        $query->where('owner_id', $ownerId);
        
        if ($ownerType) {
            $query->where('owner_type', $ownerType);
        }
        
        return $query;
    }

    /**
     * Scope for ads by product
     */
    public function scopeByProduct(Builder $query, int $productId, string $productType): Builder
    {
        return $query->where('product_id', $productId)
                    ->where('product_type', $productType);
    }

    /**
     * Scope for ads by product type
     */
    public function scopeOfProductType(Builder $query, string $productType): Builder
    {
        return $query->where('product_type', $productType);
    }

    /**
     * Scope for scheduled ads (has start_at or end_at)
     */
    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where(function($q) {
            $q->whereNotNull('start_at')
              ->orWhereNotNull('end_at');
        });
    }

    /**
     * Scope for expired ads
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED)
            ->whereNotNull('end_at')
            ->where('end_at', '<', now());
    }

    /**
     * Scope to order by priority
     */
    public function scopeByPriority(Builder $query, string $direction = 'desc'): Builder
    {
        return $query->orderBy('priority', $direction);
    }

    /**
     * Scope for device type targeting
     */
    public function scopeForDevice(Builder $query, string $deviceType): Builder
    {
        return $query->where(function($q) use ($deviceType) {
            $q->where('device_type', 'all')
              ->orWhere('device_type', $deviceType);
        });
    }

    /**
     * Scope for placement targeting
     */
    public function scopeForPlacement(Builder $query, string $placement): Builder
    {
        return $query->where(function($q) use ($placement) {
            $q->whereNull('placement')
              ->orWhere('placement', $placement);
        });
    }

    /**
     * Scope for region targeting
     */
    public function scopeForRegion(Builder $query, ?string $region): Builder
    {
        if (!$region) {
            return $query;
        }

        return $query->where(function($q) use ($region) {
            $q->whereNull('regions')
              ->orWhereJsonContains('regions', $region);
        });
    }

    /**
     * Scope to prioritize local owners first, then by priority
     */
    public function scopePrioritized(Builder $query): Builder
    {
        return $query->orderByDesc('is_local_owner')
                    ->orderByDesc('priority')
                    ->orderBy('created_at');
    }

    // ===========================================
    // STATUS CHECK METHODS
    // ===========================================

    /**
     * Check if ad is draft
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if ad is pending approval
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if ad is approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if ad is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check if ad is currently active (approved + in date range + toggle on)
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->isApproved() || !$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->start_at && $this->start_at->isFuture()) {
            return false;
        }

        if ($this->end_at && $this->end_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if ad has expired
     */
    public function isExpired(): bool
    {
        return $this->isApproved() && 
               $this->end_at && 
               $this->end_at->isPast();
    }

    // ===========================================
    // WORKFLOW METHODS
    // ===========================================

    /**
     * Submit ad for approval
     */
    public function submitForApproval(): bool
    {
        if (!$this->isDraft()) {
            return false;
        }

        $this->status = self::STATUS_PENDING;
        return $this->save();
    }

    /**
     * Approve the ad
     */
    public function approve(User $approver): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        $this->status = self::STATUS_APPROVED;
        $this->approved_by = $approver->id;
        $this->approved_at = now();
        $this->rejection_reason = null;
        
        return $this->save();
    }

    /**
     * Reject the ad
     */
    public function reject(User $approver, string $reason = null): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        $this->status = self::STATUS_REJECTED;
        $this->approved_by = $approver->id;
        $this->approved_at = now();
        $this->rejection_reason = $reason;
        
        return $this->save();
    }

    /**
     * Withdraw ad from approval (back to draft)
     */
    public function withdraw(): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        $this->status = self::STATUS_DRAFT;
        return $this->save();
    }

    /**
     * Activate the ad
     */
    public function activate(): bool
    {
        $this->is_active = true;
        return $this->save();
    }

    /**
     * Deactivate the ad
     */
    public function deactivate(): bool
    {
        $this->is_active = false;
        return $this->save();
    }

    // ===========================================
    // HELPER METHODS
    // ===========================================

    /**
     * Get status badge color for display
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'secondary',
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get formatted date range
     */
    public function getDateRangeAttribute(): string
    {
        if (!$this->start_at && !$this->end_at) {
            return 'Indefinite';
        }

        $start = $this->start_at ? $this->start_at->format('M d, Y') : 'Now';
        $end = $this->end_at ? $this->end_at->format('M d, Y') : 'Indefinite';

        return "{$start} - {$end}";
    }

    /**
     * Get main image URL
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        return asset('storage/' . $this->image_path);
    }

    /**
     * Get responsive image variants URLs
     */
    public function getImageVariantUrlsAttribute(): ?array
    {
        if (!$this->image_variants) {
            return null;
        }

        $urls = [];
        foreach ($this->image_variants as $size => $path) {
            $urls[$size] = asset('storage/' . $path);
        }

        return $urls;
    }

    /**
     * Check if ad has image
     */
    public function hasImage(): bool
    {
        return !empty($this->image_path);
    }

    /**
     * Log audit event
     */
    public function logAudit(string $eventType, ?array $changes = null, ?array $metadata = null): void
    {
        AdAuditLog::create([
            'ad_id' => $this->id,
            'event_type' => $eventType,
            'user_id' => auth()->id(),
            'changes' => $changes,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get the actual product model instance
     */
    public function getProductModel()
    {
        if (!$this->product_id || !$this->product_type) {
            return null;
        }

        return match($this->product_type) {
            self::PRODUCT_TYPE_HOTEL => Hotel::find($this->product_id),
            self::PRODUCT_TYPE_PACKAGE => Package::find($this->product_id),
            self::PRODUCT_TYPE_FLIGHT => Flight::find($this->product_id),
            self::PRODUCT_TYPE_OFFER => Offer::find($this->product_id),
            self::PRODUCT_TYPE_VEHICLE => Vehicle::find($this->product_id),
            default => null
        };
    }

    /**
     * Record impression and update counter
     */
    public function recordImpression(): void
    {
        $this->increment('impressions_count');
        $this->updateCtr();
    }

    /**
     * Record click and update counter
     */
    public function recordClick(): void
    {
        $this->increment('clicks_count');
        $this->updateCtr();
    }

    /**
     * Update CTR based on current counts
     */
    protected function updateCtr(): void
    {
        $this->refresh(); // Get latest counts
        if ($this->impressions_count > 0) {
            $this->ctr = round(($this->clicks_count / $this->impressions_count) * 100, 2);
            $this->saveQuietly();
        }
    }

    /**
     * Check if ad has reached impression limit
     */
    public function hasReachedImpressionLimit(): bool
    {
        return $this->max_impressions && $this->impressions_count >= $this->max_impressions;
    }

    /**
     * Check if ad has reached click limit
     */
    public function hasReachedClickLimit(): bool
    {
        return $this->max_clicks && $this->clicks_count >= $this->max_clicks;
    }
}
