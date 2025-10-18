<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * ServiceRequest Model - Enhanced Provider Request & Approval System
 * 
 * Handles requests from travel agents to service providers for package components
 * with atomic approval processes and availability management
 * 
 * @property int $id
 * @property string $uuid
 * @property int $package_id
 * @property int $agent_id
 * @property int $provider_id
 * @property string $provider_type
 * @property int $item_id
 * @property int $requested_quantity
 * @property date $start_date
 * @property date $end_date
 * @property array $metadata
 * @property string $status
 * @property string $priority
 * @property Carbon $expires_at
 * @property int $allocated_quantity
 * @property bool $auto_approved
 */
class ServiceRequest extends Model
{
    use HasFactory;
    
    /**
     * Request statuses
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';
    
    /**
     * Provider types
     */
    public const PROVIDER_HOTEL = 'hotel';
    public const PROVIDER_FLIGHT = 'flight';
    public const PROVIDER_TRANSPORT = 'transport';
    
    /**
     * Priority levels
     */
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';
    
    /**
     * Available statuses
     */
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_EXPIRED,
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
    
    /**
     * Available priorities
     */
    public const PRIORITIES = [
        self::PRIORITY_LOW,
        self::PRIORITY_NORMAL,
        self::PRIORITY_HIGH,
        self::PRIORITY_URGENT,
    ];
    
    protected $table = 'service_requests';
    
    protected $fillable = [
        'uuid',
        'package_id',
        'agent_id',
        'provider_id',
        'provider_type',
        'item_id',
        'requested_quantity',
        'start_date',
        'end_date',
        'metadata',
        'status',
        'agent_notes',
        'provider_notes',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'rejected_at',
        'allocated_quantity',
        'approval_conditions',
        'requested_price',
        'offered_price',
        'currency',
        'commission_percentage',
        'terms_and_conditions',
        'expires_at',
        'responded_at',
        'reminder_count',
        'last_reminder_sent',
        'communication_log',
        'priority',
        'is_rush_request',
        'requires_advance_payment',
        'advance_payment_percentage',
        'external_provider_details',
        'is_external_provider',
        'auto_approved',
        'version',
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'metadata' => 'array',
        'approval_conditions' => 'array',
        'terms_and_conditions' => 'array',
        'external_provider_details' => 'array',
        'communication_log' => 'array',
        'expires_at' => 'datetime',
        'responded_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'last_reminder_sent' => 'datetime',
        'requested_price' => 'decimal:2',
        'offered_price' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'advance_payment_percentage' => 'decimal:2',
        'requested_quantity' => 'integer',
        'allocated_quantity' => 'integer',
        'reminder_count' => 'integer',
        'version' => 'integer',
        'requires_advance_payment' => 'boolean',
        'is_external_provider' => 'boolean',
        'is_rush_request' => 'boolean',
        'auto_approved' => 'boolean',
    ];
    
    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($serviceRequest) {
            if (empty($serviceRequest->uuid)) {
                $serviceRequest->uuid = Str::uuid()->toString();
            }
        });
    }
    
    /**
     * Get the package this request belongs to
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
    
    /**
     * Get the travel agent who made the request
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
    
    /**
     * Get the provider receiving the request
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
    
    /**
     * Get the user who approved this request
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    /**
     * Get the specific service (hotel, flight, or transport) - polymorphic
     */
    public function service(): MorphTo
    {
        return $this->morphTo('service', 'provider_type', 'item_id');
    }
    
    /**
     * Get all allocations created from this request
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(Allocation::class);
    }
    
    /**
     * Get the active allocation for this request
     */
    public function activeAllocation(): HasOne
    {
        return $this->hasOne(Allocation::class)->where('status', Allocation::STATUS_ACTIVE);
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
     * Scope for expired requests
     */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED)
                    ->orWhere(function ($q) {
                        $q->where('status', self::STATUS_PENDING)
                          ->where('expires_at', '<', now());
                    });
    }
    
    /**
     * Scope for requests by provider type
     */
    public function scopeByProviderType($query, string $providerType)
    {
        return $query->where('provider_type', $providerType);
    }
    
    /**
     * Scope for requests by priority
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }
    
    /**
     * Scope for rush requests
     */
    public function scopeRush($query)
    {
        return $query->where('is_rush_request', true);
    }
    
    /**
     * Scope for requests awaiting response (pending and not expired)
     */
    public function scopeAwaitingResponse($query)
    {
        return $query->where('status', self::STATUS_PENDING)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>=', now());
                    });
    }
    
    /**
     * Scope for auto-approved requests (own services)
     */
    public function scopeAutoApproved($query)
    {
        return $query->where('auto_approved', true);
    }
    
    /**
     * Check if request is expired
     */
    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED || 
               ($this->expires_at && $this->expires_at->isPast() && $this->status === self::STATUS_PENDING);
    }
    
    /**
     * Check if request is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING && !$this->isExpired();
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
     * Check if request can be approved
     */
    public function canBeApproved(): bool
    {
        return $this->isPending() && !$this->isExpired();
    }
    
    /**
     * Approve the request - NOTE: This should be called within a transaction
     */
    public function approve(array $conditions = [], int $allocatedQuantity = null, User $approvedBy = null): bool
    {
        if (!$this->canBeApproved()) {
            return false;
        }
        
        return $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_at' => now(),
            'responded_at' => now(),
            'approved_by' => $approvedBy ? $approvedBy->id : auth()->id(),
            'allocated_quantity' => $allocatedQuantity ?? $this->requested_quantity,
            'approval_conditions' => $conditions,
            'version' => $this->version + 1,
        ]);
    }
    
    /**
     * Reject the request
     */
    public function reject(string $reason = '', User $rejectedBy = null): bool
    {
        if (!$this->isPending()) {
            return false;
        }
        
        return $this->update([
            'status' => self::STATUS_REJECTED,
            'rejected_at' => now(),
            'responded_at' => now(),
            'rejection_reason' => $reason,
            'approved_by' => $rejectedBy ? $rejectedBy->id : auth()->id(),
            'version' => $this->version + 1,
        ]);
    }
    
    /**
     * Cancel the request
     */
    public function cancel(string $reason = ''): bool
    {
        if (!$this->isPending()) {
            return false;
        }
        
        return $this->update([
            'status' => self::STATUS_CANCELLED,
            'rejection_reason' => $reason,
            'version' => $this->version + 1,
        ]);
    }
    
    /**
     * Mark as expired
     */
    public function markExpired(): bool
    {
        return $this->update([
            'status' => self::STATUS_EXPIRED,
            'version' => $this->version + 1,
        ]);
    }
    
    /**
     * Send reminder
     */
    public function sendReminder(): bool
    {
        return $this->update([
            'reminder_count' => $this->reminder_count + 1,
            'last_reminder_sent' => now(),
        ]);
    }
    
    /**
     * Add to communication log
     */
    public function addToCommunicationLog(string $message, string $type = 'note', User $user = null): bool
    {
        $log = $this->communication_log ?? [];
        $log[] = [
            'timestamp' => now()->toISOString(),
            'type' => $type,
            'message' => $message,
            'user_id' => $user ? $user->id : auth()->id(),
            'user_name' => $user ? $user->name : auth()->user()?->name,
        ];
        
        return $this->update([
            'communication_log' => $log,
        ]);
    }
    
    /**
     * Get days until deadline
     */
    public function getDaysUntilDeadlineAttribute(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }
        
        return max(0, now()->diffInDays($this->expires_at, false));
    }
    
    /**
     * Get hours until deadline
     */
    public function getHoursUntilDeadlineAttribute(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }
        
        return max(0, now()->diffInHours($this->expires_at, false));
    }
    
    /**
     * Get priority badge class for UI
     */
    public function getPriorityBadgeClassAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'badge-secondary',
            self::PRIORITY_NORMAL => 'badge-primary',
            self::PRIORITY_HIGH => 'badge-warning',
            self::PRIORITY_URGENT => 'badge-danger',
            default => 'badge-secondary',
        };
    }
    
    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_APPROVED => 'badge-success',
            self::STATUS_REJECTED => 'badge-danger',
            self::STATUS_EXPIRED => 'badge-dark',
            self::STATUS_CANCELLED => 'badge-secondary',
            default => 'badge-secondary',
        };
    }
    
    /**
     * Calculate allocation percentage
     */
    public function getAllocationPercentageAttribute(): float
    {
        if (!$this->requested_quantity || $this->requested_quantity === 0) {
            return 0;
        }
        
        $allocated = $this->allocated_quantity ?? 0;
        return ($allocated / $this->requested_quantity) * 100;
    }
    
    /**
     * Auto-expire old requests (static method for scheduled jobs)
     */
    public static function expireOldRequests(): int
    {
        return static::where('status', self::STATUS_PENDING)
                     ->where('expires_at', '<', now())
                     ->update([
                         'status' => self::STATUS_EXPIRED,
                         'version' => \DB::raw('version + 1'),
                     ]);
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
    
    /**
     * Check if request requires immediate attention (high priority or near expiry)
     */
    public function requiresImmediateAttention(): bool
    {
        if ($this->priority === self::PRIORITY_URGENT) {
            return true;
        }
        
        if ($this->expires_at && $this->expires_at->diffInHours(now()) <= 2) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if this request is for a draft package
     */
    public function isDraftPackage(): bool
    {
        return $this->package_id === null && 
               isset($this->metadata['is_draft_package']) && 
               $this->metadata['is_draft_package'] === true;
    }
    
    /**
     * Get the draft package ID if this is a draft package request
     */
    public function getDraftPackageId(): ?int
    {
        if ($this->isDraftPackage()) {
            return $this->metadata['draft_id'] ?? null;
        }
        return null;
    }
    
    /**
     * Get the package name (from package or draft metadata)
     */
    public function getPackageNameAttribute(): string
    {
        if ($this->package) {
            return $this->package->name;
        }
        
        if ($this->isDraftPackage()) {
            return $this->metadata['package_name'] ?? 'Draft Package';
        }
        
        return 'Unknown Package';
    }
    
    /**
     * Get the guest count from metadata
     */
    public function getGuestCountAttribute(): ?int
    {
        return $this->metadata['guest_count'] ?? null;
    }
    
    /**
     * Get special requirements from metadata
     */
    public function getSpecialRequirementsAttribute(): ?string
    {
        return $this->metadata['special_requirements'] ?? null;
    }
}
