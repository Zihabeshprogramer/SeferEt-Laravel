<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class TransportBooking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transport_service_id',
        'customer_id',
        'booking_reference',
        'transport_type',
        'route_type',
        'pickup_location',
        'dropoff_location',
        'pickup_datetime',
        'dropoff_datetime',
        'duration_minutes',
        'passenger_count',
        'adults',
        'children',
        'infants',
        'base_rate',
        'total_amount',
        'paid_amount',
        'tax_amount',
        'service_fee',
        'discount_amount',
        'currency',
        'status',
        'payment_status',
        'payment_method',
        'passenger_name',
        'passenger_email',
        'passenger_phone',
        'special_requests',
        'notes',
        'confirmation_code',
        'cancellation_reason',
        'cancellation_policy',
        'source',
        'confirmed_at',
        'started_at',
        'completed_at',
        'cancelled_at',
        'vehicle_details',
        'driver_details',
        'route_details',
        'metadata',
    ];

    protected $casts = [
        'pickup_datetime' => 'datetime',
        'dropoff_datetime' => 'datetime',
        'base_rate' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'passenger_count' => 'integer',
        'adults' => 'integer',
        'children' => 'integer',
        'infants' => 'integer',
        'duration_minutes' => 'integer',
        'confirmed_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'vehicle_details' => 'array',
        'driver_details' => 'array',
        'route_details' => 'array',
        'cancellation_policy' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Booking statuses
     */
    public const STATUSES = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'no_show' => 'No Show',
    ];

    /**
     * Payment statuses
     */
    public const PAYMENT_STATUSES = [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'partial' => 'Partially Paid',
        'refunded' => 'Refunded',
        'failed' => 'Failed',
    ];

    /**
     * Payment methods
     */
    public const PAYMENT_METHODS = [
        'card' => 'Credit/Debit Card',
        'bank_transfer' => 'Bank Transfer',
        'cash' => 'Cash',
        'paypal' => 'PayPal',
        'stripe' => 'Stripe',
        'other' => 'Other',
    ];

    /**
     * Boot method to set defaults
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (!$booking->booking_reference) {
                $booking->booking_reference = 'TB-' . strtoupper(uniqid());
            }
            
            if (!$booking->confirmation_code) {
                $booking->confirmation_code = strtoupper(uniqid());
            }
            
            // Calculate duration if pickup and dropoff times are provided
            if ($booking->pickup_datetime && $booking->dropoff_datetime) {
                $pickup = Carbon::parse($booking->pickup_datetime);
                $dropoff = Carbon::parse($booking->dropoff_datetime);
                $booking->duration_minutes = $pickup->diffInMinutes($dropoff);
            }
        });
    }

    /**
     * Get the transport service that owns the booking
     */
    public function transportService(): BelongsTo
    {
        return $this->belongsTo(TransportService::class);
    }

    /**
     * Get the customer for this booking
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the passenger for this booking (alias for customer)
     */
    public function passenger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get all payments for this booking
     */
    public function payments(): HasMany
    {
        return $this->hasMany(BookingPayment::class);
    }

    /**
     * Scope for active bookings
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['confirmed', 'in_progress']);
    }

    /**
     * Scope for pending bookings
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for completed bookings
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for cancelled bookings
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope for bookings on a specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('pickup_datetime', $date);
    }

    /**
     * Scope for bookings within date range
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('pickup_datetime', [$startDate, $endDate]);
    }

    /**
     * Check if booking can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']) && 
               $this->pickup_datetime > now();
    }

    /**
     * Check if booking is upcoming
     */
    public function isUpcoming(): bool
    {
        return $this->pickup_datetime > now() && 
               in_array($this->status, ['confirmed']);
    }

    /**
     * Check if booking is overdue
     */
    public function isOverdue(): bool
    {
        return $this->pickup_datetime < now() && 
               $this->status === 'pending';
    }

    /**
     * Get formatted pickup date
     */
    public function getFormattedPickupDateAttribute(): string
    {
        return $this->pickup_datetime ? $this->pickup_datetime->format('M j, Y g:i A') : '';
    }

    /**
     * Get formatted dropoff date
     */
    public function getFormattedDropoffDateAttribute(): string
    {
        return $this->dropoff_datetime ? $this->dropoff_datetime->format('M j, Y g:i A') : '';
    }

    /**
     * Get total passenger count
     */
    public function getTotalPassengersAttribute(): int
    {
        return $this->adults + $this->children + $this->infants;
    }

    /**
     * Get route description
     */
    public function getRouteDescriptionAttribute(): string
    {
        return $this->pickup_location . ' → ' . $this->dropoff_location;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get payment status label
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        return self::PAYMENT_STATUSES[$this->payment_status] ?? ucfirst($this->payment_status);
    }

    /**
     * Get remaining balance
     */
    public function getRemainingBalanceAttribute(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    /**
     * Check if booking is fully paid
     */
    public function isFullyPaid(): bool
    {
        return $this->paid_amount >= $this->total_amount;
    }
}
