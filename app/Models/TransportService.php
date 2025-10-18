<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * TransportService Model
 * 
 * Represents transportation services offered by transport providers
 * 
 * @property int $id
 * @property int $provider_id
 * @property string $service_name
 * @property string $transport_type
 * @property string $route_type
 * @property array $routes
 * @property array $specifications
 * @property int $max_passengers
 * @property array $pickup_locations
 * @property array $dropoff_locations
 * @property array $operating_hours
 * @property array $policies
 * @property array $contact_info
 * @property array $images
 * @property boolean $is_active
 */
class TransportService extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * Transport types
     */
    public const TYPE_BUS = 'bus';
    public const TYPE_CAR = 'car';
    public const TYPE_VAN = 'van';
    public const TYPE_TAXI = 'taxi';
    public const TYPE_SHUTTLE = 'shuttle';
    public const TYPE_FLIGHT = 'flight';
    
    /**
     * Route types
     */
    public const ROUTE_AIRPORT_TRANSFER = 'airport_transfer';
    public const ROUTE_CITY_TRANSPORT = 'city_transport';
    public const ROUTE_INTERCITY = 'intercity';
    public const ROUTE_PILGRIMAGE_SITES = 'pilgrimage_sites';
    
    /**
     * Available transport types
     */
    public const TRANSPORT_TYPES = [
        self::TYPE_BUS,
        self::TYPE_CAR,
        self::TYPE_VAN,
        self::TYPE_TAXI,
        self::TYPE_SHUTTLE,
        self::TYPE_FLIGHT,
    ];
    
    /**
     * Available route types
     */
    public const ROUTE_TYPES = [
        self::ROUTE_AIRPORT_TRANSFER,
        self::ROUTE_CITY_TRANSPORT,
        self::ROUTE_INTERCITY,
        self::ROUTE_PILGRIMAGE_SITES,
    ];
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'provider_id',
        'service_name',
        'transport_type',
        'route_type',
        'price', // Added price field
        'routes',
        'specifications',
        'max_passengers',
        'pickup_locations',
        'dropoff_locations',
        'operating_hours',
        'policies',
        'contact_info',
        'images',
        'is_active',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'price' => 'decimal:2', // Added price casting
        'routes' => 'array',
        'specifications' => 'array',
        'max_passengers' => 'integer',
        'pickup_locations' => 'array',
        'dropoff_locations' => 'array',
        'operating_hours' => 'array',
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
     * Get transport pricing rules for this service
     */
    public function transportPricingRules(): HasMany
    {
        return $this->hasMany(TransportPricingRule::class);
    }
    
    /**
     * Get transport rates for this service
     */
    public function transportRates(): HasMany
    {
        return $this->hasMany(TransportRate::class);
    }
    
    /**
     * Get transport bookings for this service
     */
    public function transportBookings(): HasMany
    {
        return $this->hasMany(TransportBooking::class);
    }
    
    /**
     * Scope for active transport services
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope for specific transport type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('transport_type', $type);
    }
    
    /**
     * Scope for specific route type
     */
    public function scopeForRoute($query, string $routeType)
    {
        return $query->where('route_type', $routeType);
    }
    
    /**
     * Check if service operates on specific route
     */
    public function operatesRoute(string $from, string $to): bool
    {
        $routes = $this->routes ?? [];
        foreach ($routes as $route) {
            if (isset($route['from']) && isset($route['to']) && 
                strtolower($route['from']) === strtolower($from) && 
                strtolower($route['to']) === strtolower($to)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Get active offers
     */
    public function getActiveOffersAttribute()
    {
        return $this->offers()->active()->get();
    }
    
    /**
     * Check if service can accommodate passenger count
     */
    public function canAccommodate(int $passengerCount): bool
    {
        return $passengerCount <= $this->max_passengers;
    }
}
