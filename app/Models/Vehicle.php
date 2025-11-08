<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_AVAILABLE = 'available';
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_UNDER_MAINTENANCE = 'under_maintenance';
    const STATUS_UNAVAILABLE = 'unavailable';

    const VEHICLE_TYPES = ['bus', 'van', 'car', 'minibus', 'coach', 'suv', 'sedan', 'other'];
    const STATUSES = [
        self::STATUS_AVAILABLE,
        self::STATUS_ASSIGNED,
        self::STATUS_UNDER_MAINTENANCE,
        self::STATUS_UNAVAILABLE,
    ];

    protected $fillable = [
        'provider_id',
        'vehicle_name',
        'vehicle_type',
        'plate_number',
        'capacity',
        'brand',
        'model',
        'year',
        'status',
        'images',
        'documents',
        'specifications',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'images' => 'array',
        'documents' => 'array',
        'specifications' => 'array',
        'capacity' => 'integer',
        'year' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the provider that owns the vehicle
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    /**
     * Get assigned drivers
     */
    public function drivers(): BelongsToMany
    {
        return $this->belongsToMany(Driver::class, 'vehicle_driver')
                    ->withPivot('assignment_type', 'assigned_from', 'assigned_until', 'is_active')
                    ->withTimestamps();
    }

    /**
     * Get active assigned drivers
     */
    public function activeDrivers(): BelongsToMany
    {
        return $this->drivers()->wherePivot('is_active', true);
    }

    /**
     * Get primary driver
     */
    public function primaryDriver()
    {
        return $this->drivers()
                    ->wherePivot('assignment_type', 'primary')
                    ->wherePivot('is_active', true)
                    ->first();
    }

    /**
     * Get secondary driver
     */
    public function secondaryDriver()
    {
        return $this->drivers()
                    ->wherePivot('assignment_type', 'secondary')
                    ->wherePivot('is_active', true)
                    ->first();
    }

    /**
     * Get maintenance records
     */
    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class);
    }

    /**
     * Get active/scheduled maintenance
     */
    public function activeMaintenance(): HasMany
    {
        return $this->maintenanceRecords()
                    ->whereIn('status', ['scheduled', 'in_progress']);
    }

    /**
     * Get vehicle assignments
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(VehicleAssignment::class);
    }

    /**
     * Get active assignments
     */
    public function activeAssignments(): HasMany
    {
        return $this->assignments()
                    ->whereIn('status', ['scheduled', 'in_progress'])
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
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
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('vehicle_type', $type);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if vehicle is available for date range
     */
    public function isAvailableForDateRange($startDate, $endDate): bool
    {
        if ($this->status !== self::STATUS_AVAILABLE) {
            return false;
        }

        // Check for overlapping assignments
        $hasOverlap = $this->assignments()
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

        if ($hasOverlap) {
            return false;
        }

        // Check for maintenance during period
        $hasMaintenance = $this->maintenanceRecords()
                              ->whereIn('status', ['scheduled', 'in_progress'])
                              ->where(function ($query) use ($startDate, $endDate) {
                                  $query->whereBetween('maintenance_date', [$startDate, $endDate]);
                              })
                              ->exists();

        return !$hasMaintenance;
    }

    /**
     * Update status automatically
     */
    public function updateStatusAutomatically(): void
    {
        // Check for active maintenance
        if ($this->activeMaintenance()->exists()) {
            $this->update(['status' => self::STATUS_UNDER_MAINTENANCE]);
            return;
        }

        // Check for active assignments
        if ($this->activeAssignments()->exists()) {
            $this->update(['status' => self::STATUS_ASSIGNED]);
            return;
        }

        // If no active maintenance or assignments, set to available
        if ($this->is_active) {
            $this->update(['status' => self::STATUS_AVAILABLE]);
        }
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_AVAILABLE => 'badge-success',
            self::STATUS_ASSIGNED => 'badge-primary',
            self::STATUS_UNDER_MAINTENANCE => 'badge-warning',
            self::STATUS_UNAVAILABLE => 'badge-danger',
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
     * Get vehicle full name
     */
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->vehicle_name,
            $this->brand,
            $this->model,
            $this->plate_number,
        ]);

        return implode(' - ', $parts);
    }

    /**
     * Get upcoming assignments
     */
    public function getUpcomingAssignmentsAttribute()
    {
        return $this->assignments()
                    ->where('status', 'scheduled')
                    ->where('start_date', '>', now())
                    ->orderBy('start_date')
                    ->limit(5)
                    ->get();
    }
}
