<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class VehicleAssignment extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_SCHEDULED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'service_request_id',
        'allocation_id',
        'vehicle_id',
        'primary_driver_id',
        'secondary_driver_id',
        'provider_id',
        'start_date',
        'end_date',
        'status',
        'notes',
        'metadata',
        'assigned_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'metadata' => 'array',
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // Update vehicle and driver statuses when assignment is created
        static::created(function ($assignment) {
            $assignment->vehicle->updateStatusAutomatically();
            if ($assignment->primaryDriver) {
                $assignment->primaryDriver->updateStatusAutomatically();
            }
            if ($assignment->secondaryDriver) {
                $assignment->secondaryDriver->updateStatusAutomatically();
            }

            // Set assigned_at if not set
            if (!$assignment->assigned_at) {
                $assignment->update(['assigned_at' => now()]);
            }
        });

        // Update statuses when assignment is updated
        static::updated(function ($assignment) {
            $assignment->vehicle->updateStatusAutomatically();
            if ($assignment->primaryDriver) {
                $assignment->primaryDriver->updateStatusAutomatically();
            }
            if ($assignment->secondaryDriver) {
                $assignment->secondaryDriver->updateStatusAutomatically();
            }
        });

        // Update statuses when assignment is deleted
        static::deleted(function ($assignment) {
            $assignment->vehicle->updateStatusAutomatically();
            if ($assignment->primaryDriver) {
                $assignment->primaryDriver->updateStatusAutomatically();
            }
            if ($assignment->secondaryDriver) {
                $assignment->secondaryDriver->updateStatusAutomatically();
            }
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
     * Get the primary driver
     */
    public function primaryDriver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'primary_driver_id');
    }

    /**
     * Get the secondary driver
     */
    public function secondaryDriver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'secondary_driver_id');
    }

    /**
     * Get the provider
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    /**
     * Get the service request
     */
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    /**
     * Get the allocation
     */
    public function allocation(): BelongsTo
    {
        return $this->belongsTo(Allocation::class);
    }

    /**
     * Scopes
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
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

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_SCHEDULED, self::STATUS_IN_PROGRESS]);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
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

    public function scopeUpcoming($query, int $days = 7)
    {
        return $query->where('status', self::STATUS_SCHEDULED)
                    ->where('start_date', '>=', now())
                    ->where('start_date', '<=', now()->addDays($days));
    }

    /**
     * Mark assignment as started
     */
    public function markStarted(): bool
    {
        if ($this->status === self::STATUS_SCHEDULED) {
            return $this->update([
                'status' => self::STATUS_IN_PROGRESS,
                'started_at' => now(),
            ]);
        }
        return false;
    }

    /**
     * Mark assignment as completed
     */
    public function markCompleted(): bool
    {
        if (in_array($this->status, [self::STATUS_SCHEDULED, self::STATUS_IN_PROGRESS])) {
            return $this->update([
                'status' => self::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);
        }
        return false;
    }

    /**
     * Mark assignment as cancelled
     */
    public function markCancelled(): bool
    {
        if (in_array($this->status, [self::STATUS_SCHEDULED, self::STATUS_IN_PROGRESS])) {
            return $this->update(['status' => self::STATUS_CANCELLED]);
        }
        return false;
    }

    /**
     * Check if assignment is active
     */
    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_SCHEDULED, self::STATUS_IN_PROGRESS]);
    }

    /**
     * Check if assignment overlaps with date range
     */
    public function overlapsWithDates(Carbon $startDate, Carbon $endDate): bool
    {
        return $this->start_date <= $endDate && $this->end_date >= $startDate;
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_SCHEDULED => 'badge-info',
            self::STATUS_IN_PROGRESS => 'badge-primary',
            self::STATUS_COMPLETED => 'badge-success',
            self::STATUS_CANCELLED => 'badge-secondary',
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
     * Get assignment duration in days
     */
    public function getDurationDaysAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Get days until assignment starts
     */
    public function getDaysUntilStartAttribute(): ?int
    {
        if ($this->status !== self::STATUS_SCHEDULED) {
            return null;
        }
        return max(0, now()->diffInDays($this->start_date, false));
    }

    /**
     * Get formatted route information
     */
    public function getRouteInfoAttribute(): string
    {
        if ($this->metadata && isset($this->metadata['route'])) {
            return $this->metadata['route'];
        }
        return 'N/A';
    }
}
