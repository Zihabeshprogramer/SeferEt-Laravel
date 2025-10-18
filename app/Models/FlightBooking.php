<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * FlightBooking Model
 * 
 * Represents flight bookings made from approved service requests
 * 
 * @property int $id
 * @property int $flight_id
 * @property int $customer_id
 * @property string $booking_reference
 * @property string $confirmation_code
 * @property int $passengers
 * @property string $flight_class
 * @property decimal $seat_price
 * @property decimal $total_amount
 * @property decimal $paid_amount
 * @property decimal $tax_amount
 * @property decimal $service_fee
 * @property decimal $discount_amount
 * @property string $currency
 * @property string $status
 * @property string $payment_status
 * @property string $payment_method
 * @property string $passenger_name
 * @property string $passenger_email
 * @property string $passenger_phone
 * @property array $passenger_details
 * @property string $special_requests
 * @property string $notes
 * @property string $cancellation_reason
 * @property string $source
 * @property datetime $confirmed_at
 * @property datetime $cancelled_at
 * @property datetime $check_in_opened_at
 */
class FlightBooking extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Booking statuses
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CHECKED_IN = 'checked_in';
    public const STATUS_BOARDED = 'boarded';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_NO_SHOW = 'no_show';

    /**
     * Payment statuses
     */
    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_PARTIAL = 'partial';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_REFUNDED = 'refunded';

    /**
     * Booking sources
     */
    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_SERVICE_REQUEST = 'service_request';
    public const SOURCE_API = 'api';
    public const SOURCE_AGENT_PORTAL = 'agent_portal';

    /**
     * Available statuses
     */
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_CHECKED_IN,
        self::STATUS_BOARDED,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
        self::STATUS_NO_SHOW,
    ];

    /**
     * Available payment statuses
     */
    public const PAYMENT_STATUSES = [
        self::PAYMENT_PENDING,
        self::PAYMENT_PARTIAL,
        self::PAYMENT_PAID,
        self::PAYMENT_REFUNDED,
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'flight_id',
        'customer_id',
        'booking_reference',
        'confirmation_code',
        'passengers',
        'flight_class',
        'seat_price',
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
        'passenger_details',
        'special_requests',
        'notes',
        'cancellation_reason',
        'source',
        'confirmed_at',
        'cancelled_at',
        'check_in_opened_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'passengers' => 'integer',
        'seat_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'passenger_details' => 'array',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'check_in_opened_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the flight associated with this booking
     */
    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }

    /**
     * Get the customer (User) associated with this booking
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Scope for active bookings
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::STATUS_CANCELLED]);
    }

    /**
     * Scope for confirmed bookings
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    /**
     * Scope for bookings by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for bookings by payment status
     */
    public function scopeByPaymentStatus($query, string $paymentStatus)
    {
        return $query->where('payment_status', $paymentStatus);
    }

    /**
     * Scope for bookings from service requests
     */
    public function scopeFromServiceRequest($query)
    {
        return $query->where('source', self::SOURCE_SERVICE_REQUEST);
    }

    /**
     * Check if booking is confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * Check if booking is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if booking is fully paid
     */
    public function isFullyPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    /**
     * Get remaining balance
     */
    public function getRemainingBalance(): float
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }

    /**
     * Get payment percentage
     */
    public function getPaymentPercentage(): float
    {
        if ($this->total_amount <= 0) {
            return 0;
        }

        return round(($this->paid_amount / $this->total_amount) * 100, 1);
    }

    /**
     * Check if check-in is available
     */
    public function canCheckIn(): bool
    {
        if ($this->status !== self::STATUS_CONFIRMED) {
            return false;
        }

        // Check-in typically opens 24 hours before departure
        $checkInOpenTime = $this->flight->departure_datetime->subHours(24);
        return now()->gte($checkInOpenTime);
    }

    /**
     * Get booking age in hours
     */
    public function getBookingAgeHours(): int
    {
        return $this->created_at->diffInHours(now());
    }

    /**
     * Get time until departure in hours
     */
    public function getTimeUntilDepartureHours(): int
    {
        if (!$this->flight || !$this->flight->departure_datetime) {
            return 0;
        }

        return max(0, now()->diffInHours($this->flight->departure_datetime, false));
    }

    /**
     * Check if booking can be cancelled
     */
    public function canBeCancelled(): bool
    {
        if ($this->isCancelled()) {
            return false;
        }

        // Allow cancellation up to 2 hours before departure
        $cancellationDeadline = $this->flight->departure_datetime->subHours(2);
        return now()->lt($cancellationDeadline);
    }

    /**
     * Get formatted passenger count
     */
    public function getFormattedPassengerCountAttribute(): string
    {
        $count = $this->passengers;
        return $count === 1 ? '1 passenger' : "{$count} passengers";
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedTotalAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->total_amount, 2);
    }

    /**
     * Get flight route from associated flight
     */
    public function getRouteAttribute(): string
    {
        return $this->flight ? $this->flight->route : 'N/A';
    }
}