<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * HotelService Model
 * 
 * Represents hotel services offered by hotel providers
 * 
 * @property int $id
 * @property int $provider_id
 * @property string $hotel_name
 * @property string $address
 * @property string $city
 * @property string $country
 * @property float $latitude
 * @property float $longitude
 * @property int $star_rating
 * @property array $amenities
 * @property array $room_types
 * @property string $check_in_time
 * @property string $check_out_time
 * @property array $policies
 * @property array $contact_info
 * @property array $images
 * @property boolean $is_active
 */
class HotelService extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'provider_id',
        'hotel_name',
        'address',
        'city',
        'country',
        'latitude',
        'longitude',
        'star_rating',
        'amenities',
        'room_types',
        'check_in_time',
        'check_out_time',
        'policies',
        'contact_info',
        'images',
        'is_active',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'star_rating' => 'integer',
        'amenities' => 'array',
        'room_types' => 'array',
        'policies' => 'array',
        'contact_info' => 'array',
        'images' => 'array',
        'is_active' => 'boolean',
        'deleted_at' => 'datetime',
    ];
    
    /**
     * Get the service provider (User)
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
    
    /**
     * Get service offers (polymorphic relationship)
     */
    public function offers(): MorphMany
    {
        return $this->morphMany(ServiceOffer::class, 'service');
    }
    
    /**
     * Scope for active hotels
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope for hotels in specific city
     */
    public function scopeInCity($query, string $city)
    {
        return $query->where('city', 'like', "%{$city}%");
    }
    
    /**
     * Scope for hotels by star rating
     */
    public function scopeByStarRating($query, int $rating)
    {
        return $query->where('star_rating', $rating);
    }
    
    /**
     * Get the full address
     */
    public function getFullAddressAttribute(): string
    {
        return "{$this->address}, {$this->city}, {$this->country}";
    }
    
    /**
     * Check if hotel has specific amenity
     */
    public function hasAmenity(string $amenity): bool
    {
        return in_array($amenity, $this->amenities ?? []);
    }
    
    /**
     * Get active offers
     */
    public function getActiveOffersAttribute()
    {
        return $this->offers()->active()->get();
    }
}
