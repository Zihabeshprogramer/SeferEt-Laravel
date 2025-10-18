<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_booking_id',
        'payment_reference',
        'amount',
        'payment_method',
        'status',
        'gateway_response',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the booking for this payment
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(HotelBooking::class, 'hotel_booking_id');
    }

    /**
     * Scope for successful payments
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'completed');
    }
}
