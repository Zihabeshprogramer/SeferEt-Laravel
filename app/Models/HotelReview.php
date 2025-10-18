<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HotelReview extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'hotel_id',
        'hotel_booking_id',
        'customer_id',
        'rating',
        'title',
        'comment',
        'is_verified',
        'is_published',
        'response',
        'responded_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_verified' => 'boolean',
        'is_published' => 'boolean',
        'responded_at' => 'datetime',
    ];

    /**
     * Get the hotel that owns the review
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get the booking associated with the review
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(HotelBooking::class, 'hotel_booking_id');
    }

    /**
     * Get the customer who wrote the review
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Scope for published reviews
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope for verified reviews
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope for reviews with responses
     */
    public function scopeWithResponse($query)
    {
        return $query->whereNotNull('response');
    }

    /**
     * Check if review has a response
     */
    public function hasResponse()
    {
        return !empty($this->response);
    }

    /**
     * Get star rating display
     */
    public function getStarRatingAttribute()
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }
}
