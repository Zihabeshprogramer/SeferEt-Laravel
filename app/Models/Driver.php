<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Driver extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_AVAILABLE = 'available';
    const STATUS_ON_TRIP = 'on_trip';
    const STATUS_ON_LEAVE = 'on_leave';
    const STATUS_UNAVAILABLE = 'unavailable';

    const STATUSES = [
        self::STATUS_AVAILABLE,
        self::STATUS_ON_TRIP,
        self::STATUS_ON_LEAVE,
        self::STATUS_UNAVAILABLE,
    ];

    protected $fillable = [
        'provider_id',
        'name',
        'phone',
        'email',
        'license_number',
        'license_expiry',
        'license_type',
        'availability_status',
        'documents',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'license_expiry' => 'date',
        'documents' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the provider that owns the driver
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    /**
     * Get assigned vehicles
     */
    public function vehicles(): BelongsToMany
    {
        return $this->belongsToMany(Vehicle::class, 'vehicle_driver')
                    ->withPivot('assignment_type', 'assigned_from', 'assigned_until', 'is_active')
                    ->withTimestamps();
    }

    /**
     * Get active assigned vehicles
     */
    public function activeVehicles(): BelongsToMany
    {
        return $this->vehicles()->wherePivot('is_active', true);
    }

    /**
     * Get assignments as primary driver
     */
    public function primaryAssignments(): HasMany
    {
        return $this->hasMany(VehicleAssignment::class, 'primary_driver_id');
    }

    /**
     * Get assignments as secondary driver
     */
    public function secondaryAssignments(): HasMany
    {
        return $this->hasMany(VehicleAssignment::class, 'secondary_driver_id');
    }

    /**
     * Get all assignments (primary or secondary)
     */
    public function allAssignments()
    {
        return VehicleAssignment::where('primary_driver_id', $this->id)
                                ->orWhere('secondary_driver_id', $this->id)
                                ->orderBy('start_date');
    }

    /**
     * Get active assignments
     */
    public function activeAssignments()
    {
        return VehicleAssignment::where(function ($query) {
                    $query->where('primary_driver_id', $this->id)
                          ->orWhere('secondary_driver_id', $this->id);
                })
                ->whereIn('status', ['scheduled', 'in_progress'])
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->get();
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('availability_status', self::STATUS_AVAILABLE);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('availability_status', $status);
    }

    public function scopeLicenseExpiring($query, int $days = 30)
    {
        return $query->where('license_expiry', '<=', now()->addDays($days))
                    ->where('license_expiry', '>=', now());
    }

    /**
     * Check if driver is available for date range
     */
    public function isAvailableForDateRange($startDate, $endDate): bool
    {
        if ($this->availability_status !== self::STATUS_AVAILABLE) {
            return false;
        }

        // Check for overlapping assignments
        $hasOverlap = VehicleAssignment::where(function ($query) {
                          $query->where('primary_driver_id', $this->id)
                                ->orWhere('secondary_driver_id', $this->id);
                      })
                      ->whereIn('status', ['scheduled', 'in_progress'])
                      ->where(function ($query) use ($startDate, $endDate) {
                          $query->whereBetween('start_date', [$startDate, $endDate])
                                ->orWhereBetween('end_date', [$startDate, $endDate])
                                ->orWhere(function ($q) use ($startDate, $endDate) {
                                    $q->where('start_date', '<=', $startDate)
                                      ->where('end_date', '>=', $endDate);
                                });
                      })
                      ->exists();

        return !$hasOverlap;
    }

    /**
     * Update status automatically
     */
    public function updateStatusAutomatically(): void
    {
        // Check for active assignments
        if ($this->activeAssignments()->count() > 0) {
            $this->update(['availability_status' => self::STATUS_ON_TRIP]);
            return;
        }

        // If no active assignments, set to available
        if ($this->is_active) {
            $this->update(['availability_status' => self::STATUS_AVAILABLE]);
        }
    }

    /**
     * Check if license is expired
     */
    public function isLicenseExpired(): bool
    {
        return $this->license_expiry->isPast();
    }

    /**
     * Check if license is expiring soon
     */
    public function isLicenseExpiringSoon(int $days = 30): bool
    {
        return $this->license_expiry->between(now(), now()->addDays($days));
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->availability_status) {
            self::STATUS_AVAILABLE => 'badge-success',
            self::STATUS_ON_TRIP => 'badge-primary',
            self::STATUS_ON_LEAVE => 'badge-warning',
            self::STATUS_UNAVAILABLE => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    /**
     * Get formatted status label
     */
    public function getStatusLabelAttribute(): string
    {
        return ucwords(str_replace('_', ' ', $this->availability_status));
    }

    /**
     * Get upcoming assignments
     */
    public function getUpcomingAssignmentsAttribute()
    {
        return VehicleAssignment::where(function ($query) {
                    $query->where('primary_driver_id', $this->id)
                          ->orWhere('secondary_driver_id', $this->id);
                })
                ->where('status', 'scheduled')
                ->where('start_date', '>', now())
                ->orderBy('start_date')
                ->limit(5)
                ->get();
    }

    /**
     * Get days until license expiry
     */
    public function getDaysUntilLicenseExpiryAttribute(): int
    {
        return max(0, now()->diffInDays($this->license_expiry, false));
    }
}
