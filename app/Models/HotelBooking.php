<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class HotelBooking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'hotel_id',
        'room_id',
        'customer_id',
        'booking_reference',
        'check_in_date',
        'check_out_date',
        'nights',
        'adults',
        'children',
        'room_rate',
        'total_amount',
        'paid_amount',
        'tax_amount',
        'service_fee',
        'discount_amount',
        'currency',
        'status',
        'payment_status',
        'payment_method',
        'special_requests',
        'guest_name',
        'guest_email',
        'guest_phone',
        'notes',
        'confirmation_code',
        'cancellation_reason',
        'cancellation_policy',
        'source',
        'cancelled_at',
        'confirmed_at',
        'checked_in_at',
        'checked_out_at',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'room_rate' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'cancelled_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
    ];

    /**
     * Booking statuses
     */
    public const STATUSES = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'checked_in' => 'Checked In',
        'checked_out' => 'Checked Out',
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
                $booking->booking_reference = 'HB-' . strtoupper(uniqid());
            }
            
            if ($booking->check_in_date && $booking->check_out_date) {
                $booking->nights = Carbon::parse($booking->check_in_date)
                    ->diffInDays(Carbon::parse($booking->check_out_date));
            }
        });
    }

    /**
     * Get the hotel that owns the booking
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get the room for this booking
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the customer for this booking
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the guest for this booking (alias for customer)
     */
    public function guest(): BelongsTo
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
     * Get all reviews for this booking
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(HotelReview::class);
    }

    /**
     * Scope for active bookings
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['confirmed', 'checked_in']);
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
        return $query->where('status', 'checked_out');
    }

    /**
     * Scope for cancelled bookings
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope for current year bookings
     */
    public function scopeCurrentYear($query)
    {
        return $query->whereYear('created_at', now()->year);
    }

    /**
     * Scope for current month bookings
     */
    public function scopeCurrentMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    /**
     * Scope for bookings by hotel provider
     */
    public function scopeForProvider($query, $providerId)
    {
        return $query->whereHas('hotel', function ($q) use ($providerId) {
            $q->where('provider_id', $providerId);
        });
    }

    /**
     * Check if booking can be cancelled
     */
    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'confirmed']) && 
               $this->check_in_date->isFuture();
    }

    /**
     * Check if booking can be confirmed
     */
    public function canBeConfirmed()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if customer can check in
     */
    public function canCheckIn()
    {
        return $this->status === 'confirmed' && 
               $this->check_in_date->isToday() || $this->check_in_date->isPast();
    }

    /**
     * Check if customer can check out
     */
    public function canCheckOut()
    {
        return $this->status === 'checked_in';
    }

    /**
     * Confirm the booking
     */
    public function confirm()
    {
        if ($this->canBeConfirmed()) {
            $this->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
            ]);
            return true;
        }
        return false;
    }

    /**
     * Cancel the booking
     */
    public function cancel($reason = null)
    {
        if ($this->canBeCancelled()) {
            $this->update([
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
                'cancelled_at' => now(),
            ]);
            return true;
        }
        return false;
    }

    /**
     * Check in the booking
     */
    public function checkIn()
    {
        if ($this->canCheckIn()) {
            $this->update(['status' => 'checked_in']);
            return true;
        }
        return false;
    }

    /**
     * Check out the booking
     */
    public function checkOut()
    {
        if ($this->canCheckOut()) {
            $this->update(['status' => 'checked_out']);
            return true;
        }
        return false;
    }

    /**
     * Get the total guests count
     */
    public function getTotalGuestsAttribute()
    {
        return $this->adults + $this->children;
    }

    /**
     * Get formatted guest information
     */
    public function getGuestInfoAttribute()
    {
        $info = "{$this->adults} Adult" . ($this->adults > 1 ? 's' : '');
        if ($this->children > 0) {
            $info .= ", {$this->children} Child" . ($this->children > 1 ? 'ren' : '');
        }
        return $info;
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'confirmed' => 'info',
            'checked_in' => 'primary',
            'checked_out' => 'success',
            'cancelled' => 'danger',
            'no_show' => 'secondary',
            default => 'secondary'
        };
    }

    /**
     * Get payment status badge class
     */
    public function getPaymentStatusBadgeClassAttribute()
    {
        return match($this->payment_status) {
            'paid' => 'success',
            'partial' => 'warning',
            'pending' => 'info',
            'refunded' => 'secondary',
            'failed' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Calculate the total amount based on rates
     */
    public function calculateTotal()
    {
        if ($this->room && $this->nights > 0) {
            $totalAmount = $this->room_rate * $this->nights;
            $this->update(['total_amount' => $totalAmount]);
            return $totalAmount;
        }
        return $this->total_amount;
    }
}
