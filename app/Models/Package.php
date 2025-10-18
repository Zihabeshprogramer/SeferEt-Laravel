<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Package Model
 * 
 * Represents Umrah packages that can optionally integrate with B2B service providers
 * 
 * @property int $id
 * @property int $creator_id
 * @property string $name
 * @property string $description
 * @property string $type
 * @property int $duration
 * @property decimal $base_price
 * @property string $currency
 * @property array $inclusions
 * @property array $exclusions
 * @property array $itinerary
 * @property string $status
 * @property boolean $uses_b2b_services
 * @property array $service_preferences
 */
class Package extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * Package types
     */
    public const TYPE_CULTURAL = 'cultural';
    public const TYPE_ADVENTURE = 'adventure';
    public const TYPE_LEISURE = 'leisure';
    public const TYPE_BUSINESS = 'business';
    public const TYPE_FAMILY = 'family';
    public const TYPE_LUXURY = 'luxury';
    public const TYPE_BUDGET = 'budget';
    public const TYPE_HONEYMOON = 'honeymoon';
    public const TYPE_RELIGIOUS = 'religious';
    public const TYPE_WELLNESS = 'wellness';
    
    /**
     * Package statuses
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';
    
    /**
     * Approval statuses
     */
    public const APPROVAL_PENDING = 'pending';
    public const APPROVAL_APPROVED = 'approved';
    public const APPROVAL_REJECTED = 'rejected';
    public const APPROVAL_NEEDS_REVISION = 'needs_revision';
    
    /**
     * Provider source types
     */
    public const SOURCE_PLATFORM = 'platform';
    public const SOURCE_EXTERNAL = 'external';
    public const SOURCE_MIXED = 'mixed';
    public const SOURCE_OWN = 'own';
    
    /**
     * Approval statuses array
     */
    public const APPROVAL_STATUSES = [
        self::APPROVAL_PENDING,
        self::APPROVAL_APPROVED,
        self::APPROVAL_REJECTED,
        self::APPROVAL_NEEDS_REVISION,
    ];
    
    /**
     * Provider sources array
     */
    public const PROVIDER_SOURCES = [
        self::SOURCE_PLATFORM,
        self::SOURCE_EXTERNAL,
        self::SOURCE_MIXED,
    ];
    
    /**
     * Flight sources array
     */
    public const FLIGHT_SOURCES = [
        self::SOURCE_OWN,
        self::SOURCE_PLATFORM,
        self::SOURCE_EXTERNAL,
        self::SOURCE_MIXED,
    ];
    
    /**
     * Available package types
     */
    public const TYPES = [
        self::TYPE_CULTURAL,
        self::TYPE_ADVENTURE,
        self::TYPE_LEISURE,
        self::TYPE_BUSINESS,
        self::TYPE_FAMILY,
        self::TYPE_LUXURY,
        self::TYPE_BUDGET,
        self::TYPE_HONEYMOON,
        self::TYPE_RELIGIOUS,
        self::TYPE_WELLNESS,
    ];
    
    /**
     * Available statuses
     */
    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
        self::STATUS_SUSPENDED,
    ];
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        // Basic package information
        'creator_id', 'name', 'slug', 'description', 'short_description', 'detailed_description',
        'type', 'duration', 'start_date', 'end_date',
        
        // Destination and categorization
        'destinations', 'categories', 'difficulty_level',
        
        // Pricing and financial
        'base_price', 'child_price', 'child_discount_percent', 'infant_price', 'single_supplement',
        'child_price_disabled', 'child_discount_percent_disabled',
        'pricing_breakdown', 'optional_addons', 'total_price',
        'payment_terms', 'deposit_percentage', 'cancellation_policy', 'currency',
        'commission_rate', 'terms_accepted',
        
        // Inclusions
        'includes_meals', 'includes_accommodation', 'includes_transport', 'includes_guide',
        'includes_flights', 'includes_activities', 'free_cancellation', 'instant_confirmation',
        
        // Content and marketing
        'inclusions', 'exclusions', 'features', 'activities', 'highlights',
        'special_offers', 'images', 'seo_meta', 'tags',
        
        // Capacity and availability
        'min_participants', 'max_participants', 'current_bookings', 
        'available_from', 'available_until', 'booking_deadlines',
        'min_booking_days', 'requires_deposit',
        
        // Provider management
        'uses_b2b_services', 'hotel_source', 'transport_source', 'flight_source',
        'external_providers', 'service_preferences',
        
        // Commission and revenue
        'commission_structure', 'platform_commission', 'revenue_sharing',
        
        // Status and approval
        'status', 'approval_status', 'rejection_reason', 'approved_at', 'approved_by',
        'version', 'draft_data', 'requires_approval',
        
        // Location and logistics
        'departure_cities', 'meeting_points', 'pickup_locations',
        'accommodation_preferences', 'transport_preferences',
        
        // Requirements and documentation
        'required_documents', 'visa_requirements', 'health_requirements', 'age_restrictions',
        
        // Features and options
        'is_featured', 'is_premium', 'allow_customization', 'instant_booking',
        'customization_options', 'group_discounts', 'seasonal_pricing', 'multi_language',
        
        // Analytics and tracking
        'views_count', 'bookings_count', 'average_rating', 'reviews_count',
        'analytics_data', 'last_updated_pricing', 'last_availability_check',
        
        // Legacy fields
        'itinerary',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        // Basic fields
        'duration' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        
        // Destination and categorization
        'destinations' => 'array',
        'categories' => 'array',
        
        // Pricing and financial
        'base_price' => 'decimal:2',
        'child_price' => 'decimal:2',
        'child_discount_percent' => 'decimal:2',
        'infant_price' => 'decimal:2',
        'single_supplement' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'total_price' => 'decimal:2',
        'deposit_percentage' => 'decimal:2',
        'platform_commission' => 'decimal:2',
        'pricing_breakdown' => 'array',
        'optional_addons' => 'array',
        'payment_terms' => 'array',
        'cancellation_policy' => 'array',
        'commission_structure' => 'array',
        'revenue_sharing' => 'array',
        'group_discounts' => 'array',
        'seasonal_pricing' => 'array',
        'terms_accepted' => 'array',
        
        // Content arrays
        'inclusions' => 'array',
        'exclusions' => 'array',
        'features' => 'array',
        'activities' => 'array',
        'highlights' => 'array',
        'special_offers' => 'array',
        'images' => 'array',
        'tags' => 'array',
        'seo_meta' => 'array',
        'multi_language' => 'array',
        
        // Location and logistics
        'departure_cities' => 'array',
        'meeting_points' => 'array',
        'pickup_locations' => 'array',
        'accommodation_preferences' => 'array',
        'transport_preferences' => 'array',
        'external_providers' => 'array',
        
        // Requirements and documentation
        'required_documents' => 'array',
        'visa_requirements' => 'array',
        'health_requirements' => 'array',
        'age_restrictions' => 'array',
        
        // Features and options
        'customization_options' => 'array',
        'analytics_data' => 'array',
        'service_preferences' => 'array',
        'booking_deadlines' => 'array',
        'draft_data' => 'array',
        
        // Boolean fields
        'uses_b2b_services' => 'boolean',
        'includes_meals' => 'boolean',
        'includes_accommodation' => 'boolean',
        'includes_transport' => 'boolean',
        'includes_guide' => 'boolean',
        'includes_flights' => 'boolean',
        'includes_activities' => 'boolean',
        'free_cancellation' => 'boolean',
        'instant_confirmation' => 'boolean',
        'child_price_disabled' => 'boolean',
        'child_discount_percent_disabled' => 'boolean',
        'requires_deposit' => 'boolean',
        'is_featured' => 'boolean',
        'is_premium' => 'boolean',
        'allow_customization' => 'boolean',
        'instant_booking' => 'boolean',
        'requires_approval' => 'boolean',
        
        // Integer fields
        'min_participants' => 'integer',
        'max_participants' => 'integer',
        'min_booking_days' => 'integer',
        'current_bookings' => 'integer',
        'views_count' => 'integer',
        'bookings_count' => 'integer',
        'reviews_count' => 'integer',
        'version' => 'integer',
        
        // Decimal fields
        'average_rating' => 'decimal:2',
        
        // Date/datetime fields
        'available_from' => 'date',
        'available_until' => 'date',
        'approved_at' => 'datetime',
        'last_updated_pricing' => 'datetime',
        'last_availability_check' => 'datetime',
        'deleted_at' => 'datetime',
        
        // Legacy
        'itinerary' => 'array',
    ];
    
    /**
     * Get the package creator (Partner user)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
    
    /**
     * Get all B2B service offers attached to this package
     */
    public function serviceOffers(): BelongsToMany
    {
        return $this->belongsToMany(ServiceOffer::class, 'package_service_offers')
                    ->withPivot('is_required', 'markup_percentage', 'custom_price')
                    ->withTimestamps();
    }
    
    /**
     * Get package service offer pivot records
     */
    public function packageServiceOffers(): HasMany
    {
        return $this->hasMany(PackageServiceOffer::class);
    }
    
    /**
     * Scope for active packages
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
    
    /**
     * Scope for packages by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
    
    /**
     * Scope for packages that use B2B services
     */
    public function scopeUsingB2BServices($query)
    {
        return $query->where('uses_b2b_services', true);
    }
    
    /**
     * Check if package uses B2B services
     */
    public function usesB2BServices(): bool
    {
        return $this->uses_b2b_services;
    }
    
    /**
     * Get hotel services attached to this package
     */
    public function hotelServices()
    {
        return $this->serviceOffers()->where('service_type', 'hotel');
    }
    
    
    /**
     * Get flights attached to this package
     */
    public function flights(): BelongsToMany
    {
        return $this->belongsToMany(Flight::class, 'package_flight')
                    ->withPivot(['flight_type', 'is_required', 'markup_percentage', 'custom_price', 'seats_allocated'])
                    ->withTimestamps();
    }
    
    /**
     * Get hotels attached to this package with detailed pivot data
     */
    public function hotels(): BelongsToMany
    {
        return $this->belongsToMany(Hotel::class, 'package_hotel')
                    ->withPivot([
                        'source_type', 'is_primary', 'is_required', 'nights',
                        'check_in_date', 'check_out_date', 'room_type', 'rooms_needed',
                        'room_configuration', 'original_price', 'markup_percentage',
                        'custom_price', 'final_price', 'currency', 'meal_plans',
                        'additional_services', 'special_requests', 'commission_percentage',
                        'commission_amount', 'commission_type', 'commission_shared',
                        'revenue_sharing_details', 'external_hotel_details',
                        'confirmation_status', 'booking_reference', 'confirmation_notes',
                        'display_order', 'marketing_description', 'featured_in_package'
                    ])
                    ->withTimestamps();
    }
    
    /**
     * Get transport services attached to this package with detailed pivot data
     */
    public function transportServices(): BelongsToMany
    {
        return $this->belongsToMany(TransportService::class, 'package_transport')
                    ->withPivot([
                        'source_type', 'transport_category', 'is_required', 'day_of_itinerary',
                        'pickup_location', 'dropoff_location', 'route_details',
                        'scheduled_pickup_time', 'scheduled_dropoff_time', 'estimated_duration_minutes',
                        'distance_km', 'passengers_count', 'luggage_pieces', 'special_requirements',
                        'passenger_details', 'original_price', 'markup_percentage', 'custom_price',
                        'final_price', 'currency', 'pricing_type', 'commission_percentage',
                        'commission_amount', 'commission_type', 'commission_shared',
                        'revenue_sharing_details', 'external_transport_details',
                        'confirmation_status', 'booking_reference', 'confirmation_notes',
                        'driver_details', 'vehicle_details', 'display_order'
                    ])
                    ->withTimestamps();
    }
    
    /**
     * Get package activities (detailed itinerary)
     */
    public function packageActivities(): HasMany
    {
        return $this->hasMany(PackageActivity::class)->orderBy('day_number')->orderBy('display_order');
    }
    
    /**
     * Get all service requests for this package
     */
    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }
    
    /**
     * Get pending service requests for this package
     */
    public function pendingServiceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class)->where('status', ServiceRequest::STATUS_PENDING);
    }
    
    /**
     * Get approved service requests for this package
     */
    public function approvedServiceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class)->where('status', ServiceRequest::STATUS_APPROVED);
    }
    
    /**
     * Get the user who approved this package
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    /**
     * Check if package can proceed (all service requests approved)
     */
    public function canProceed(): bool
    {
        // If package doesn't use B2B services, it can always proceed
        if (!$this->usesB2BServices()) {
            return true;
        }
        
        // Check if there are any service requests
        $totalRequests = $this->serviceRequests()->count();
        
        // If no requests exist yet, package cannot proceed
        if ($totalRequests === 0) {
            return false;
        }
        
        // Check if all requests are either approved or auto-approved
        $approvedCount = $this->serviceRequests()
            ->whereIn('status', [ServiceRequest::STATUS_APPROVED])
            ->count();
            
        $autoApprovedCount = $this->serviceRequests()
            ->where('auto_approved', true)
            ->count();
        
        return ($approvedCount + $autoApprovedCount) === $totalRequests;
    }
    
    /**
     * Check if all service requests are approved
     */
    public function allServiceRequestsApproved(): bool
    {
        return $this->canProceed();
    }
    
    /**
     * Get service request approval status summary
     */
    public function getServiceRequestApprovalStatus(): array
    {
        $totalRequests = $this->serviceRequests()->count();
        $approvedRequests = $this->serviceRequests()->where('status', ServiceRequest::STATUS_APPROVED)->count();
        $pendingRequests = $this->serviceRequests()->where('status', ServiceRequest::STATUS_PENDING)->count();
        $rejectedRequests = $this->serviceRequests()->where('status', ServiceRequest::STATUS_REJECTED)->count();
        $expiredRequests = $this->serviceRequests()->where('status', ServiceRequest::STATUS_EXPIRED)->count();
        $autoApprovedRequests = $this->serviceRequests()->where('auto_approved', true)->count();
        
        return [
            'total' => $totalRequests,
            'approved' => $approvedRequests,
            'pending' => $pendingRequests,
            'rejected' => $rejectedRequests,
            'expired' => $expiredRequests,
            'auto_approved' => $autoApprovedRequests,
            'can_proceed' => $this->canProceed(),
            'completion_percentage' => $totalRequests > 0 ? 
                round((($approvedRequests + $autoApprovedRequests) / $totalRequests) * 100, 2) : 0
        ];
    }
    
    /**
     * Get blocking service requests (non-approved)
     */
    public function getBlockingServiceRequests()
    {
        return $this->serviceRequests()
            ->whereNotIn('status', [ServiceRequest::STATUS_APPROVED])
            ->where('auto_approved', false)
            ->with(['provider:id,name,email', 'agent:id,name,email'])
            ->get();
    }
    
    /**
     * Get outbound flights
     */
    public function outboundFlights()
    {
        return $this->flights()->wherePivot('flight_type', 'outbound');
    }
    
    /**
     * Get return flights
     */
    public function returnFlights()
    {
        return $this->flights()->wherePivot('flight_type', 'return');
    }
    
    /**
     * Get connecting flights
     */
    public function connectingFlights()
    {
        return $this->flights()->wherePivot('flight_type', 'connecting');
    }
    
    /**
     * Calculate total package price including B2B services and flights
     */
    public function calculateTotalPrice(): float
    {
        $totalPrice = $this->base_price;
        
        if ($this->usesB2BServices()) {
            foreach ($this->packageServiceOffers as $serviceOffer) {
                if ($serviceOffer->custom_price) {
                    $totalPrice += $serviceOffer->custom_price;
                } else {
                    $servicePrice = $serviceOffer->serviceOffer->base_price;
                    $markup = $servicePrice * ($serviceOffer->markup_percentage / 100);
                    $totalPrice += $servicePrice + $markup;
                }
            }
        }
        
        // Add flight costs
        foreach ($this->flights as $flight) {
            if ($flight->pivot->custom_price) {
                $totalPrice += $flight->pivot->custom_price;
            } else {
                $flightPrice = $flight->economy_price; // Default to economy price
                $markup = $flightPrice * ($flight->pivot->markup_percentage / 100);
                $totalPrice += $flightPrice + $markup;
            }
        }
        
        return $totalPrice;
    }
    
    /**
     * Enhanced scopes for package filtering
     */
    
    /**
     * Scope for approved packages
     */
    public function scopeApproved($query)
    {
        return $query->where('approval_status', self::APPROVAL_APPROVED);
    }
    
    /**
     * Scope for pending approval packages
     */
    public function scopePendingApproval($query)
    {
        return $query->where('approval_status', self::APPROVAL_PENDING);
    }
    
    /**
     * Scope for premium packages
     */
    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }
    
    /**
     * Scope for packages within date range
     */
    public function scopeWithinDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate]);
    }
    
    /**
     * Scope for packages by provider source
     */
    public function scopeByProviderSource($query, $type, $source)
    {
        switch ($type) {
            case 'hotel':
                return $query->where('hotel_source', $source);
            case 'transport':
                return $query->where('transport_source', $source);
            case 'flight':
                return $query->where('flight_source', $source);
            default:
                return $query;
        }
    }
    
    /**
     * Scope for packages with minimum rating
     */
    public function scopeWithRating($query, float $minRating)
    {
        return $query->where('average_rating', '>=', $minRating);
    }
    
    /**
     * Scope for searchable packages
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('detailed_description', 'like', "%{$search}%")
              ->orWhereJsonContains('tags', $search)
              ->orWhereJsonContains('highlights', $search);
        });
    }
    
    /**
     * Get package types with display labels
     */
    public static function getPackageTypes(): array
    {
        return [
            self::TYPE_CULTURAL => '🏛️ Cultural & Historical Tours',
            self::TYPE_ADVENTURE => '🏔️ Adventure & Outdoor', 
            self::TYPE_LEISURE => '🏖️ Leisure & Relaxation',
            self::TYPE_BUSINESS => '💼 Business Travel',
            self::TYPE_FAMILY => '👨‍👩‍👧‍👦 Family Vacation',
            self::TYPE_LUXURY => '💎 Luxury Experience',
            self::TYPE_BUDGET => '💰 Budget Travel',
            self::TYPE_HONEYMOON => '💕 Honeymoon & Romance',
            self::TYPE_RELIGIOUS => '🕌 Religious & Pilgrimage',
            self::TYPE_WELLNESS => '🧘‍♀️ Wellness & Spa',
        ];
    }
    
    /**
     * Enhanced business logic methods
     */
    
    /**
     * Check if package is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
    
    /**
     * Check if package is approved
     */
    public function isApproved(): bool
    {
        return $this->approval_status === self::APPROVAL_APPROVED;
    }
    
    /**
     * Check if package is available for booking
     */
    public function isAvailableForBooking(): bool
    {
        return $this->isActive() && 
               $this->isApproved() &&
               ($this->available_from === null || $this->available_from <= now()) &&
               ($this->available_until === null || $this->available_until >= now()) &&
               ($this->max_participants === null || $this->current_bookings < $this->max_participants);
    }
    
    /**
     * Check if package has capacity for given number of participants
     */
    public function hasCapacityFor(int $participants): bool
    {
        if (!$this->max_participants) {
            return true;
        }
        
        $availableSpots = $this->max_participants - $this->current_bookings;
        return $availableSpots >= $participants;
    }
    
    /**
     * Check if package meets minimum participants requirement
     */
    public function meetsMinimumRequirement(int $participants): bool
    {
        return !$this->min_participants || $participants >= $this->min_participants;
    }
    
    /**
     * Get availability percentage
     */
    public function getAvailabilityPercentage(): int
    {
        if (!$this->max_participants) {
            return 100;
        }
        
        $availableSpots = $this->max_participants - $this->current_bookings;
        return max(0, round(($availableSpots / $this->max_participants) * 100));
    }
    
    /**
     * Calculate comprehensive package price including all services
     */
    public function calculateComprehensivePrice(): float
    {
        $totalPrice = $this->total_price ?? $this->base_price;
        
        // Add hotel costs
        foreach ($this->hotels as $hotel) {
            if ($hotel->pivot->final_price) {
                $totalPrice += $hotel->pivot->final_price;
            } elseif ($hotel->pivot->custom_price) {
                $totalPrice += $hotel->pivot->custom_price;
            } elseif ($hotel->pivot->original_price) {
                $markup = $hotel->pivot->original_price * ($hotel->pivot->markup_percentage / 100);
                $totalPrice += $hotel->pivot->original_price + $markup;
            }
        }
        
        // Add transport costs
        foreach ($this->transportServices as $transport) {
            if ($transport->pivot->final_price) {
                $totalPrice += $transport->pivot->final_price;
            } elseif ($transport->pivot->custom_price) {
                $totalPrice += $transport->pivot->custom_price;
            } elseif ($transport->pivot->original_price) {
                $markup = $transport->pivot->original_price * ($transport->pivot->markup_percentage / 100);
                $totalPrice += $transport->pivot->original_price + $markup;
            }
        }
        
        // Add flight costs (existing logic)
        foreach ($this->flights as $flight) {
            if ($flight->pivot->custom_price) {
                $totalPrice += $flight->pivot->custom_price;
            } else {
                $flightPrice = $flight->economy_price;
                $markup = $flightPrice * ($flight->pivot->markup_percentage / 100);
                $totalPrice += $flightPrice + $markup;
            }
        }
        
        // Add optional activities costs
        foreach ($this->packageActivities as $activity) {
            if (!$activity->is_included && $activity->additional_cost) {
                $totalPrice += $activity->additional_cost;
            }
        }
        
        return $totalPrice;
    }
    
    /**
     * Get primary hotel for this package
     */
    public function getPrimaryHotel(): ?Hotel
    {
        return $this->hotels()->wherePivot('is_primary', true)->first();
    }
    
    /**
     * Get activities by day
     */
    public function getActivitiesByDay(int $day): \Illuminate\Database\Eloquent\Collection
    {
        return $this->packageActivities()->forDay($day)->get();
    }
    
    /**
     * Get highlight activities
     */
    public function getHighlightActivities(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->packageActivities()->highlighted()->get();
    }
    
    /**
     * Generate SEO-friendly slug
     */
    public function generateSlug(): string
    {
        $baseSlug = Str::slug($this->name);
        $slug = $baseSlug;
        $counter = 1;
        
        while (static::where('slug', $slug)->where('id', '!=', $this->id ?? 0)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
     * Update pricing based on current configurations
     */
    public function updatePricing(): bool
    {
        $this->total_price = $this->calculateComprehensivePrice();
        $this->last_updated_pricing = now();
        
        return $this->save();
    }
    
    /**
     * Check if package uses platform providers
     */
    public function usesPlatformProviders(): bool
    {
        return $this->hotel_source === self::SOURCE_PLATFORM ||
               $this->transport_source === self::SOURCE_PLATFORM ||
               $this->flight_source === self::SOURCE_PLATFORM;
    }
    
    /**
     * Get main package image (updated for new structure)
     */
    public function getMainImageAttribute(): ?array
    {
        return $this->getMainImage();
    }
    
    /**
     * Get the main package image from images array
     */
    public function getMainImage(): ?array
    {
        if (empty($this->images)) {
            return null;
        }
        
        // Find the main image
        foreach ($this->images as $image) {
            if (isset($image['is_main']) && $image['is_main']) {
                return $image;
            }
        }
        
        // If no main image is explicitly set, return the first image
        return $this->images[0] ?? null;
    }
    
    /**
     * Get the main image URL for a specific size
     */
    public function getMainImageUrl(string $size = 'medium'): ?string
    {
        $mainImage = $this->getMainImage();
        
        if (!$mainImage || !isset($mainImage['sizes'][$size])) {
            return null;
        }
        
        return asset('storage/' . $mainImage['sizes'][$size]);
    }
    
    /**
     * Get all images with URLs
     */
    public function getImagesWithUrls(): array
    {
        if (empty($this->images)) {
            return [];
        }
        
        return array_map(function ($image) {
            $imageWithUrls = $image;
            $imageWithUrls['urls'] = [];
            
            foreach ($image['sizes'] ?? [] as $size => $path) {
                $imageWithUrls['urls'][$size] = asset('storage/' . $path);
            }
            
            return $imageWithUrls;
        }, $this->images);
    }
    
    /**
     * Check if package has images
     */
    public function hasImages(): bool
    {
        return !empty($this->images);
    }
    
    /**
     * Get image count
     */
    public function getImageCount(): int
    {
        return count($this->images ?? []);
    }
    
    /**
     * Get package duration in human readable format
     */
    public function getFormattedDurationAttribute(): string
    {
        if ($this->duration === 1) {
            return '1 day';
        }
        
        return $this->duration . ' days';
    }
    
    /**
     * Get package status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'badge-success',
            self::STATUS_DRAFT => 'badge-warning',
            self::STATUS_INACTIVE => 'badge-secondary',
            self::STATUS_SUSPENDED => 'badge-danger',
            default => 'badge-secondary'
        };
    }
    
    /**
     * Get approval status badge class
     */
    public function getApprovalBadgeClassAttribute(): string
    {
        return match ($this->approval_status) {
            self::APPROVAL_APPROVED => 'badge-success',
            self::APPROVAL_PENDING => 'badge-warning',
            self::APPROVAL_NEEDS_REVISION => 'badge-info',
            self::APPROVAL_REJECTED => 'badge-danger',
            default => 'badge-secondary'
        };
    }
    
    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($package) {
            if (empty($package->slug)) {
                $package->slug = $package->generateSlug();
            }
        });
        
        static::updating(function ($package) {
            if ($package->isDirty('name') && empty($package->slug)) {
                $package->slug = $package->generateSlug();
            }
        });
        
        static::deleting(function ($package) {
            // Clean up associated images when package is deleted
            try {
                $imageService = app(\App\Services\PackageImageService::class);
                $imageService->cleanupPackageImages($package);
            } catch (\Exception $e) {
                \Log::warning('Failed to cleanup images for package ' . $package->id . ': ' . $e->getMessage());
            }
        });
    }
}
