<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Carbon\Carbon;

/**
 * ProviderRequest Model
 * 
 * Handles requests from travel agents to service providers for package components
 */
class ProviderRequest extends Model
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
     * Service types
     */
    public const SERVICE_HOTEL = 'hotel';
    public const SERVICE_FLIGHT = 'flight';
    public const SERVICE_TRANSPORT = 'transport';
    
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
     * Available service types
     */
    public const SERVICE_TYPES = [
        self::SERVICE_HOTEL,
        self::SERVICE_FLIGHT,
        self::SERVICE_TRANSPORT,
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
    
    protected $fillable = [
        'package_id',
        'travel_agent_id',
        'provider_id',
        'service_type',
        'service_id',
        'request_details',
        'status',
        'provider_notes',
        'travel_agent_notes',
        'requested_price',
        'offered_price',
        'currency',
        'commission_percentage',
        'terms_and_conditions',
        'service_start_date',
        'service_end_date',
        'response_deadline',
        'responded_at',
        'approved_at',
        'rejected_at',
        'approval_conditions',
        'requires_advance_payment',
        'advance_payment_percentage',
        'external_provider_details',
        'is_external_provider',
        'reminder_count',
        'last_reminder_sent',
        'communication_log',
        'priority',
        'is_rush_request',
    ];
    
    protected $casts = [
        'request_details' => 'array',
        'terms_and_conditions' => 'array',
        'approval_conditions' => 'array',
        'external_provider_details' => 'array',
        'communication_log' => 'array',
        'service_start_date' => 'datetime',
        'service_end_date' => 'datetime',
        'response_deadline' => 'datetime',
        'responded_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'last_reminder_sent' => 'datetime',
        'requested_price' => 'decimal:2',
        'offered_price' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'advance_payment_percentage' => 'decimal:2',
        'requires_advance_payment' => 'boolean',
        'is_external_provider' => 'boolean',
        'is_rush_request' => 'boolean',
        'reminder_count' => 'integer',
    ];
    
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
    public function travelAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'travel_agent_id');
    }
    
    /**
     * Get the provider receiving the request
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
    
    /**
     * Get the specific service (hotel, flight, or transport)
     */
    public function service(): MorphTo
    {
        return $this->morphTo('service', 'service_type', 'service_id');
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
                    ->orWhere('response_deadline', '<', now());
    }
    
    /**
     * Scope for requests by service type
     */
    public function scopeByServiceType($query, string $serviceType)
    {
        return $query->where('service_type', $serviceType);
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
     * Scope for requests needing response
     */
    public function scopeAwaitingResponse($query)
    {
        return $query->where('status', self::STATUS_PENDING)
                    ->where('response_deadline', '>=', now());
    }
    
    /**
     * Check if request is expired
     */
    public function isExpired(): bool
    {
        return $this->response_deadline && $this->response_deadline->isPast();
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
     * Approve the request
     */
    public function approve(array $conditions = [], string $notes = ''): bool
    {
        return $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_at' => now(),
            'responded_at' => now(),
            'approval_conditions' => $conditions,
            'provider_notes' => $notes,
        ]);
    }
    
    /**
     * Reject the request
     */
    public function reject(string $reason = ''): bool
    {
        return $this->update([
            'status' => self::STATUS_REJECTED,
            'rejected_at' => now(),
            'responded_at' => now(),
            'provider_notes' => $reason,
        ]);
    }
    
    /**
     * Cancel the request
     */
    public function cancel(): bool
    {
        return $this->update([
            'status' => self::STATUS_CANCELLED,
        ]);
    }
    
    /**
     * Mark as expired
     */
    public function markExpired(): bool
    {
        return $this->update([
            'status' => self::STATUS_EXPIRED,
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
    public function addToCommunicationLog(string $message, string $type = 'note'): bool
    {
        $log = $this->communication_log ?? [];
        $log[] = [
            'timestamp' => now()->toISOString(),
            'type' => $type,
            'message' => $message,
            'user_id' => auth()->id(),
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
        if (!$this->response_deadline) {
            return null;
        }
        
        return max(0, now()->diffInDays($this->response_deadline, false));
    }
    
    /**
     * Get priority badge class
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
     * Get status badge class
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
     * Auto-expire old requests
     */
    public static function expireOldRequests(): int
    {
        return static::where('status', self::STATUS_PENDING)
                     ->where('response_deadline', '<', now())
                     ->update(['status' => self::STATUS_EXPIRED]);
    }
}
