<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'hotel_id',
        'category',
        'room_number',
        'room_number_start',
        'room_number_end',
        'name',
        'description',
        'size_sqm',
        'bed_type',
        'bed_count',
        'max_occupancy',
        'amenities',
        'images',
        'base_price',
        'is_available',
        'is_active',
    ];

    protected $casts = [
        'amenities' => 'array',
        'images' => 'array',
        'is_available' => 'boolean',
        'is_active' => 'boolean',
        'base_price' => 'decimal:2',
        'size_sqm' => 'decimal:2',
    ];

    /**
     * Bed types
     */
    public const BED_TYPES = [
        'single' => 'Single Bed',
        'twin' => 'Twin Beds',
        'double' => 'Double Bed',
        'queen' => 'Queen Bed',
        'king' => 'King Bed',
        'sofa_bed' => 'Sofa Bed',
        'bunk_bed' => 'Bunk Bed',
    ];
    
    /**
     * Feature-based room type categories
     */
    public const ROOM_TYPE_CATEGORIES = [
        'normal_view' => 'Normal Room',
        'window_view' => 'Window View',
        'balcony_view' => 'Balcony View',
        'sea_view' => 'Sea View',
        'mountain_view' => 'Mountain View',
        'city_view' => 'City View',
        'garden_view' => 'Garden View',
        'vip_access' => 'VIP Access',
        'executive_lounge' => 'Executive Lounge Access',
        'family_suite' => 'Family Suite',
        'connecting_rooms' => 'Connecting Rooms Available',
        'accessible' => 'Wheelchair Accessible',
        'pet_friendly' => 'Pet Friendly',
        'kitchenette' => 'Kitchenette Included',
        'jacuzzi' => 'Private Jacuzzi',
        'fireplace' => 'Fireplace',
        'business_center' => 'Business Center Access',
        'conference_ready' => 'Conference Ready',
        'spa_access' => 'Spa Access Included',
        'pool_access' => 'Private Pool Access',
        'gym_access' => 'Gym Access Included',
        'concierge_service' => 'Personal Concierge',
        'room_service_24h' => '24/7 Room Service',
        'soundproof' => 'Soundproof Room',
        'smoking_allowed' => 'Smoking Allowed',
        'non_smoking' => 'Non-Smoking',
    ];

    /**
     * Get the hotel that owns the room
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }


    /**
     * Get all bookings for this room
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(HotelBooking::class);
    }

    /**
     * Get all rates for this room
     */
    public function rates(): HasMany
    {
        return $this->hasMany(RoomRate::class);
    }

    /**
     * Scope to filter available rooms
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)->where('is_active', true);
    }

    /**
     * Scope to filter by hotel
     */
    public function scopeForHotel($query, $hotelId)
    {
        return $query->where('hotel_id', $hotelId);
    }

    /**
     * Check if room is available for given dates
     */
    public function isAvailableForDates($checkIn, $checkOut)
    {
        if (!$this->is_available || !$this->is_active) {
            return false;
        }

        // Check for conflicting bookings
        $conflictingBookings = $this->bookings()
            ->where('status', 'confirmed')
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->where(function ($q) use ($checkIn, $checkOut) {
                    $q->where('check_in_date', '<', $checkOut)
                      ->where('check_out_date', '>', $checkIn);
                });
            })
            ->exists();

        return !$conflictingBookings;
    }

    /**
     * Get the main room image
     */
    public function getMainImageAttribute()
    {
        return $this->images && count($this->images) > 0 ? $this->images[0] : null;
    }

    /**
     * Get formatted bed information
     */
    public function getBedInfoAttribute()
    {
        $bedType = self::BED_TYPES[$this->bed_type] ?? 'Unknown';
        $count = $this->bed_count > 1 ? " ({$this->bed_count})" : '';
        return $bedType . $count;
    }

    /**
     * Get current rate for the room
     */
    public function getCurrentRate($date = null)
    {
        $date = $date ?? now()->toDateString();
        
        return $this->rates()
            ->where('date', $date)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get the effective price for a date (rate or base price)
     */
    public function getEffectivePrice($date = null)
    {
        $rate = $this->getCurrentRate($date);
        return $rate ? $rate->price : $this->base_price;
    }
    
    /**
     * Parse room number input (single number or range)
     */
    public static function parseRoomNumberInput($input)
    {
        $input = trim($input);
        
        // Check if it's a range (e.g., "125-130" or "125 - 130")
        if (preg_match('/^(\d+)\s*-\s*(\d+)$/', $input, $matches)) {
            $start = (int)$matches[1];
            $end = (int)$matches[2];
            
            if ($start >= $end) {
                throw new \InvalidArgumentException('End room number must be greater than start room number.');
            }
            
            if ($end - $start > 50) {
                throw new \InvalidArgumentException('Room number range cannot exceed 50 rooms.');
            }
            
            return [
                'type' => 'range',
                'start' => $start,
                'end' => $end,
                'numbers' => range($start, $end)
            ];
        }
        
        // Single room number
        if (preg_match('/^\d+$/', $input)) {
            $number = (int)$input;
            return [
                'type' => 'single',
                'start' => $number,
                'end' => $number,
                'numbers' => [$number]
            ];
        }
        
        throw new \InvalidArgumentException('Invalid room number format. Use single number (e.g., "105") or range (e.g., "125-130").');
    }
    
    /**
     * Get formatted room number display
     */
    public function getFormattedRoomNumberAttribute()
    {
        if ($this->room_number_start && $this->room_number_end && $this->room_number_start !== $this->room_number_end) {
            return $this->room_number_start . '-' . $this->room_number_end;
        }
        
        return $this->room_number;
    }
    
    /**
     * Check if room is part of a range
     */
    public function isPartOfRange()
    {
        return $this->room_number_start && $this->room_number_end && $this->room_number_start !== $this->room_number_end;
    }
    
    /**
     * Get all room type categories for dropdowns
     */
    public static function getRoomTypeCategories()
    {
        return self::ROOM_TYPE_CATEGORIES;
    }
    
    /**
     * Get the formatted category name
     */
    public function getCategoryNameAttribute()
    {
        return self::ROOM_TYPE_CATEGORIES[$this->category] ?? 'Unknown Category';
    }
    
    /**
     * Scope to filter by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
    
    /**
     * Get room type (alias for category_name for compatibility)
     */
    public function getRoomTypeAttribute()
    {
        return $this->category_name;
    }
}
