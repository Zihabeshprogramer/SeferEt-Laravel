<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'default_amenities',
        'is_active',
    ];

    protected $casts = [
        'default_amenities' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Boot method to set slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($roomType) {
            if (!$roomType->slug) {
                $roomType->slug = \Str::slug($roomType->name);
            }
        });

        static::updating(function ($roomType) {
            if ($roomType->isDirty('name')) {
                $roomType->slug = \Str::slug($roomType->name);
            }
        });
    }

    /**
     * Get all rooms of this type
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Get pricing rules for this room type
     */
    public function pricingRules(): HasMany
    {
        return $this->hasMany(PricingRule::class);
    }

    /**
     * Scope for active room types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
