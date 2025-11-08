<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AdAuditLog Model
 * 
 * Tracks all changes and events for ads throughout their lifecycle.
 * 
 * @property int $id
 * @property int $ad_id
 * @property string $event_type
 * @property int|null $user_id
 * @property array|null $changes
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 */
class AdAuditLog extends Model
{
    use HasFactory;

    /**
     * Disable updated_at timestamp
     */
    public const UPDATED_AT = null;

    /**
     * Event type constants
     */
    public const EVENT_CREATED = 'created';
    public const EVENT_UPDATED = 'updated';
    public const EVENT_SUBMITTED = 'submitted';
    public const EVENT_APPROVED = 'approved';
    public const EVENT_REJECTED = 'rejected';
    public const EVENT_DELETED = 'deleted';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'ad_id',
        'event_type',
        'user_id',
        'changes',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'changes' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the ad this log belongs to
     */
    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    /**
     * Get the user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted event type for display
     */
    public function getEventTypeDisplayAttribute(): string
    {
        return match($this->event_type) {
            self::EVENT_CREATED => 'Created',
            self::EVENT_UPDATED => 'Updated',
            self::EVENT_SUBMITTED => 'Submitted for Approval',
            self::EVENT_APPROVED => 'Approved',
            self::EVENT_REJECTED => 'Rejected',
            self::EVENT_DELETED => 'Deleted',
            default => ucfirst($this->event_type)
        };
    }
}
