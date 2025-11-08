<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceRecord extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    const TYPE_ROUTINE = 'routine';
    const TYPE_REPAIR = 'repair';
    const TYPE_INSPECTION = 'inspection';
    const TYPE_EMERGENCY = 'emergency';
    const TYPE_OTHER = 'other';

    const STATUSES = [
        self::STATUS_SCHEDULED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    const TYPES = [
        self::TYPE_ROUTINE,
        self::TYPE_REPAIR,
        self::TYPE_INSPECTION,
        self::TYPE_EMERGENCY,
        self::TYPE_OTHER,
    ];

    protected $fillable = [
        'vehicle_id',
        'provider_id',
        'maintenance_type',
        'maintenance_date',
        'next_due_date',
        'description',
        'cost',
        'currency',
        'service_provider',
        'status',
        'notes',
        'documents',
    ];

    protected $casts = [
        'maintenance_date' => 'date',
        'next_due_date' => 'date',
        'cost' => 'decimal:2',
        'documents' => 'array',
    ];

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // Update vehicle status when maintenance is created
        static::created(function ($maintenance) {
            if (in_array($maintenance->status, [self::STATUS_SCHEDULED, self::STATUS_IN_PROGRESS])) {
                $maintenance->vehicle->updateStatusAutomatically();
            }
        });

        // Update vehicle status when maintenance status changes
        static::updated(function ($maintenance) {
            $maintenance->vehicle->updateStatusAutomatically();
        });

        // Update vehicle status when maintenance is deleted
        static::deleted(function ($maintenance) {
            $maintenance->vehicle->updateStatusAutomatically();
        });
    }

    /**
     * Get the vehicle
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the provider
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    /**
     * Scopes
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('maintenance_type', $type);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeUpcoming($query, int $days = 30)
    {
        return $query->where('status', self::STATUS_SCHEDULED)
                    ->where('maintenance_date', '>=', now())
                    ->where('maintenance_date', '<=', now()->addDays($days));
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED)
                    ->where('maintenance_date', '<', now());
    }

    /**
     * Mark as in progress
     */
    public function markInProgress(): bool
    {
        if ($this->status === self::STATUS_SCHEDULED) {
            return $this->update(['status' => self::STATUS_IN_PROGRESS]);
        }
        return false;
    }

    /**
     * Mark as completed
     */
    public function markCompleted(): bool
    {
        if (in_array($this->status, [self::STATUS_SCHEDULED, self::STATUS_IN_PROGRESS])) {
            return $this->update(['status' => self::STATUS_COMPLETED]);
        }
        return false;
    }

    /**
     * Mark as cancelled
     */
    public function markCancelled(): bool
    {
        if (in_array($this->status, [self::STATUS_SCHEDULED, self::STATUS_IN_PROGRESS])) {
            return $this->update(['status' => self::STATUS_CANCELLED]);
        }
        return false;
    }

    /**
     * Check if maintenance is active (scheduled or in progress)
     */
    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_SCHEDULED, self::STATUS_IN_PROGRESS]);
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_SCHEDULED => 'badge-info',
            self::STATUS_IN_PROGRESS => 'badge-warning',
            self::STATUS_COMPLETED => 'badge-success',
            self::STATUS_CANCELLED => 'badge-secondary',
            default => 'badge-secondary',
        };
    }

    /**
     * Get type badge class for UI
     */
    public function getTypeBadgeClassAttribute(): string
    {
        return match($this->maintenance_type) {
            self::TYPE_ROUTINE => 'badge-primary',
            self::TYPE_REPAIR => 'badge-warning',
            self::TYPE_INSPECTION => 'badge-info',
            self::TYPE_EMERGENCY => 'badge-danger',
            self::TYPE_OTHER => 'badge-secondary',
            default => 'badge-secondary',
        };
    }

    /**
     * Get formatted status label
     */
    public function getStatusLabelAttribute(): string
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }

    /**
     * Get formatted type label
     */
    public function getTypeLabelAttribute(): string
    {
        return ucwords(str_replace('_', ' ', $this->maintenance_type));
    }

    /**
     * Check if maintenance is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_SCHEDULED && $this->maintenance_date->isPast();
    }

    /**
     * Get days until maintenance
     */
    public function getDaysUntilMaintenanceAttribute(): ?int
    {
        if ($this->status !== self::STATUS_SCHEDULED) {
            return null;
        }
        return now()->diffInDays($this->maintenance_date, false);
    }
}
