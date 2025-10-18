<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * PackageActivity Model
 * 
 * Represents detailed activities within a package itinerary
 * 
 * @property int $id
 * @property int $package_id
 * @property int $day_number
 * @property string $activity_name
 * @property string $description
 * @property string $category
 * @property bool $is_included
 * @property bool $is_optional
 */
class PackageActivity extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * Activity categories
     */
    public const CATEGORY_RELIGIOUS = 'religious';
    public const CATEGORY_CULTURAL = 'cultural';
    public const CATEGORY_EDUCATIONAL = 'educational';
    public const CATEGORY_RECREATIONAL = 'recreational';
    public const CATEGORY_SHOPPING = 'shopping';
    public const CATEGORY_DINING = 'dining';
    public const CATEGORY_TRANSPORT = 'transport';
    public const CATEGORY_ACCOMMODATION = 'accommodation';
    public const CATEGORY_FREE_TIME = 'free_time';
    public const CATEGORY_OPTIONAL = 'optional';
    public const CATEGORY_GROUP = 'group';
    public const CATEGORY_INDIVIDUAL = 'individual';
    
    /**
     * Difficulty levels
     */
    public const DIFFICULTY_EASY = 'easy';
    public const DIFFICULTY_MODERATE = 'moderate';
    public const DIFFICULTY_CHALLENGING = 'challenging';
    public const DIFFICULTY_EXPERT = 'expert';
    
    /**
     * Time types
     */
    public const TIME_FIXED = 'fixed';
    public const TIME_FLEXIBLE = 'flexible';
    public const TIME_APPROXIMATE = 'approximate';
    
    /**
     * Booking statuses
     */
    public const BOOKING_NOT_REQUIRED = 'not_required';
    public const BOOKING_PENDING = 'pending';
    public const BOOKING_CONFIRMED = 'confirmed';
    public const BOOKING_CANCELLED = 'cancelled';
    
    /**
     * Availability statuses
     */
    public const AVAILABILITY_AVAILABLE = 'available';
    public const AVAILABILITY_LIMITED = 'limited';
    public const AVAILABILITY_SOLD_OUT = 'sold_out';
    public const AVAILABILITY_SUSPENDED = 'suspended';
    
    /**
     * Available categories
     */
    public const CATEGORIES = [
        self::CATEGORY_RELIGIOUS,
        self::CATEGORY_CULTURAL,
        self::CATEGORY_EDUCATIONAL,
        self::CATEGORY_RECREATIONAL,
        self::CATEGORY_SHOPPING,
        self::CATEGORY_DINING,
        self::CATEGORY_TRANSPORT,
        self::CATEGORY_ACCOMMODATION,
        self::CATEGORY_FREE_TIME,
        self::CATEGORY_OPTIONAL,
        self::CATEGORY_GROUP,
        self::CATEGORY_INDIVIDUAL,
    ];
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'package_id',
        'day_number',
        'activity_name',
        'description',
        'detailed_description',
        'highlights',
        'start_time',
        'end_time',
        'duration_minutes',
        'time_type',
        'alternative_times',
        'location',
        'address',
        'latitude',
        'longitude',
        'location_details',
        'directions',
        'category',
        'difficulty_level',
        'age_restrictions',
        'physical_requirements',
        'is_included',
        'additional_cost',
        'currency',
        'is_optional',
        'requires_booking',
        'booking_details',
        'min_participants',
        'max_participants',
        'optimal_group_size',
        'allows_individual_booking',
        'group_requirements',
        'required_items',
        'recommended_items',
        'dress_code',
        'weather_considerations',
        'preparation_notes',
        'guide_details',
        'provider_details',
        'guide_included',
        'guide_cost',
        'contact_details',
        'images',
        'videos',
        'documents',
        'external_link',
        'amenities',
        'accessibility_features',
        'dietary_accommodations',
        'photo_opportunities',
        'shopping_available',
        'seasonal_availability',
        'weather_dependency',
        'alternative_activities',
        'display_order',
        'is_featured',
        'is_highlight',
        'marketing_tags',
        'marketing_description',
        'booking_status',
        'booking_reference',
        'confirmation_details',
        'booking_deadline',
        'average_rating',
        'reviews_count',
        'feedback_summary',
        'requires_transport',
        'transport_details',
        'meals_included',
        'meal_details',
        'safety_information',
        'emergency_procedures',
        'is_active',
        'availability_status',
        'availability_calendar',
        'status_notes',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'day_number' => 'integer',
        'highlights' => 'array',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'duration_minutes' => 'integer',
        'alternative_times' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'location_details' => 'array',
        'age_restrictions' => 'array',
        'physical_requirements' => 'array',
        'is_included' => 'boolean',
        'additional_cost' => 'decimal:2',
        'is_optional' => 'boolean',
        'requires_booking' => 'boolean',
        'booking_details' => 'array',
        'min_participants' => 'integer',
        'max_participants' => 'integer',
        'optimal_group_size' => 'integer',
        'allows_individual_booking' => 'boolean',
        'group_requirements' => 'array',
        'required_items' => 'array',
        'recommended_items' => 'array',
        'dress_code' => 'array',
        'weather_considerations' => 'array',
        'guide_details' => 'array',
        'provider_details' => 'array',
        'guide_included' => 'boolean',
        'guide_cost' => 'decimal:2',
        'contact_details' => 'array',
        'images' => 'array',
        'videos' => 'array',
        'documents' => 'array',
        'amenities' => 'array',
        'accessibility_features' => 'array',
        'dietary_accommodations' => 'array',
        'photo_opportunities' => 'boolean',
        'shopping_available' => 'boolean',
        'seasonal_availability' => 'array',
        'weather_dependency' => 'array',
        'alternative_activities' => 'array',
        'display_order' => 'integer',
        'is_featured' => 'boolean',
        'is_highlight' => 'boolean',
        'marketing_tags' => 'array',
        'booking_deadline' => 'datetime',
        'average_rating' => 'decimal:2',
        'reviews_count' => 'integer',
        'feedback_summary' => 'array',
        'requires_transport' => 'boolean',
        'transport_details' => 'array',
        'meals_included' => 'boolean',
        'meal_details' => 'array',
        'safety_information' => 'array',
        'emergency_procedures' => 'array',
        'is_active' => 'boolean',
        'availability_calendar' => 'array',
        'confirmation_details' => 'array',
        'deleted_at' => 'datetime',
    ];
    
    /**
     * Get the package this activity belongs to
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
    
    /**
     * Scope for active activities
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope for activities by day
     */
    public function scopeForDay($query, int $day)
    {
        return $query->where('day_number', $day);
    }
    
    /**
     * Scope for activities by category
     */
    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
    
    /**
     * Scope for included activities
     */
    public function scopeIncluded($query)
    {
        return $query->where('is_included', true);
    }
    
    /**
     * Scope for optional activities
     */
    public function scopeOptional($query)
    {
        return $query->where('is_optional', true);
    }
    
    /**
     * Scope for highlighted activities
     */
    public function scopeHighlighted($query)
    {
        return $query->where('is_highlight', true);
    }
    
    /**
     * Get the formatted duration
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration_minutes) {
            return 'Duration not specified';
        }
        
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes}m";
        }
    }
    
    /**
     * Get the main image
     */
    public function getMainImageAttribute(): ?string
    {
        return $this->images && count($this->images) > 0 ? $this->images[0] : null;
    }
    
    /**
     * Check if activity requires additional payment
     */
    public function requiresPayment(): bool
    {
        return !$this->is_included && $this->additional_cost > 0;
    }
    
    /**
     * Check if activity is bookable
     */
    public function isBookable(): bool
    {
        return $this->is_active && 
               $this->availability_status === self::AVAILABILITY_AVAILABLE &&
               ($this->booking_deadline === null || $this->booking_deadline->isFuture());
    }
    
    /**
     * Check if activity has capacity available
     */
    public function hasCapacityFor(int $participants): bool
    {
        if (!$this->max_participants) {
            return true;
        }
        
        return $participants <= $this->max_participants;
    }
    
    /**
     * Get the display name with day number
     */
    public function getDisplayNameAttribute(): string
    {
        return "Day {$this->day_number}: {$this->activity_name}";
    }
    
    /**
     * Get location coordinates as array
     */
    public function getCoordinatesAttribute(): ?array
    {
        if ($this->latitude && $this->longitude) {
            return [
                'lat' => (float) $this->latitude,
                'lng' => (float) $this->longitude
            ];
        }
        
        return null;
    }
}