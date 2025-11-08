<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hotel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'provider_id',
        'name',
        'description',
        'type',
        'star_rating',
        'address',
        'city',
        'country',
        'postal_code',
        'phone',
        'email',
        'website',
        'check_in_time',
        'check_out_time',
        'distance_to_haram',
        'distance_to_airport',
        'amenities',
        'images',
        'policy_cancellation',
        'policy_children',
        'policy_pets',
        'status',
        'is_active',
        'latitude',
        'longitude',
        'is_featured',
        'featured_at',
        'featured_expires_at',
    ];

    protected $casts = [
        'amenities' => 'array',
        'images' => 'array',
        'is_active' => 'boolean',
        'star_rating' => 'integer',
        'distance_to_haram' => 'decimal:2',
        'distance_to_airport' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'check_in_time' => 'datetime:H:i',
        'check_out_time' => 'datetime:H:i',
        'is_featured' => 'boolean',
        'featured_at' => 'datetime',
        'featured_expires_at' => 'datetime',
    ];

    /**
     * Hotel types
     */
    public const TYPES = [
        'luxury' => 'Luxury Hotel',
        'boutique' => 'Boutique Hotel', 
        'business' => 'Business Hotel',
        'resort' => 'Resort',
        'budget' => 'Budget Hotel',
        'apartment' => 'Serviced Apartment'
    ];

    /**
     * Hotel statuses
     */
    public const STATUSES = [
        'pending' => 'Pending Approval',
        'active' => 'Active',
        'suspended' => 'Suspended',
        'rejected' => 'Rejected'
    ];

    /**
     * Get the hotel provider (hotel_provider user)
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    /**
     * Get all rooms for this hotel
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Get all bookings for this hotel
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(HotelBooking::class);
    }

    /**
     * Get all reviews for this hotel
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(HotelReview::class);
    }

    /**
     * Get deals and promotions for this hotel
     */
    public function deals(): HasMany
    {
        return $this->hasMany(HotelDeal::class);
    }

    /**
     * Get pricing rules for this hotel
     */
    public function pricingRules(): HasMany
    {
        return $this->hasMany(PricingRule::class);
    }
    
    /**
     * Get featured requests for this hotel
     */
    public function featuredRequests(): HasMany
    {
        return $this->hasMany(FeaturedRequest::class, 'product_id')
                    ->where('product_type', FeaturedRequest::PRODUCT_TYPE_HOTEL);
    }
    
    /**
     * Get active featured request for this hotel
     */
    public function activeFeaturedRequest()
    {
        return $this->featuredRequests()
                    ->where('status', FeaturedRequest::STATUS_APPROVED)
                    ->active()
                    ->first();
    }

    /**
     * Scope to filter active hotels
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_active', true);
    }

    /**
     * Scope to filter hotels by provider
     */
    public function scopeByProvider($query, $providerId)
    {
        return $query->where('provider_id', $providerId);
    }

    /**
     * Scope to filter hotels by city
     */
    public function scopeInCity($query, $city)
    {
        return $query->where('city', 'like', "%{$city}%");
    }

    /**
     * Scope to filter hotels by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter hotels by star rating
     */
    public function scopeWithStarRating($query, $rating)
    {
        return $query->where('star_rating', $rating);
    }
    
    /**
     * Scope for featured hotels
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)
                    ->where(function($q) {
                        $q->whereNull('featured_expires_at')
                          ->orWhere('featured_expires_at', '>', now());
                    });
    }

    /**
     * Get the hotel's main image
     */
    public function getMainImageAttribute()
    {
        return $this->images && count($this->images) > 0 ? $this->images[0] : null;
    }

    /**
     * Get formatted address
     */
    public function getFullAddressAttribute()
    {
        $address = $this->address;
        if ($this->city) {
            $address .= ', ' . $this->city;
        }
        if ($this->country) {
            $address .= ', ' . $this->country;
        }
        return $address;
    }

    /**
     * Get formatted star rating for display
     */
    public function getStarDisplayAttribute()
    {
        return str_repeat('★', $this->star_rating) . str_repeat('☆', 5 - $this->star_rating);
    }

    /**
     * Check if hotel is available
     */
    public function isAvailable()
    {
        return $this->status === 'active' && $this->is_active;
    }

    /**
     * Get hotel's average rating
     */
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    /**
     * Get hotel's total reviews count
     */
    public function getTotalReviewsAttribute()
    {
        return $this->reviews()->count();
    }

    /**
     * Get hotel's occupancy rate for current month
     */
    public function getCurrentOccupancyRateAttribute()
    {
        $totalRooms = $this->rooms->count();
        if ($totalRooms === 0) return 0;

        $currentMonth = now()->month;
        $daysInMonth = now()->daysInMonth;
        $totalRoomNights = $totalRooms * $daysInMonth;

        $bookedRoomNights = $this->bookings()
            ->whereMonth('check_in_date', $currentMonth)
            ->where('status', 'confirmed')
            ->sum('nights');

        return $totalRoomNights > 0 ? round(($bookedRoomNights / $totalRoomNights) * 100, 1) : 0;
    }
}
