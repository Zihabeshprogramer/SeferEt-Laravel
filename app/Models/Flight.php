<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

/**
 * Flight Model - Group Booking & Agent Collaboration
 * 
 * Represents round-trip group flight bookings offered by travel agents
 * with support for agent-to-agent collaboration
 * 
 * @property int $id
 * @property int $provider_id
 * @property string $airline
 * @property string $flight_number
 * @property string $trip_type
 * @property string $return_flight_number
 * @property string $departure_airport
 * @property string $arrival_airport
 * @property datetime $departure_datetime
 * @property datetime $arrival_datetime
 * @property datetime $return_departure_datetime
 * @property datetime $return_arrival_datetime
 * @property int $total_seats
 * @property int $available_seats
 * @property decimal $economy_price
 * @property decimal $group_economy_price
 * @property decimal $business_price
 * @property decimal $group_business_price
 * @property decimal $first_class_price
 * @property decimal $group_first_class_price
 * @property string $currency
 * @property string $aircraft_type
 * @property array $amenities
 * @property string $description
 * @property boolean $is_active
 * @property boolean $is_group_booking
 * @property int $min_group_size
 * @property int $max_group_size
 * @property decimal $group_discount_percentage
 * @property date $booking_deadline
 * @property boolean $allows_agent_collaboration
 * @property decimal $collaboration_commission_percentage
 * @property string $collaboration_terms
 * @property string $status
 * @property array $baggage_allowance
 * @property array $meal_service
 * @property array $included_services
 * @property string $special_requirements
 * @property string $payment_terms
 */
class Flight extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * Flight statuses
     */
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_BOARDING = 'boarding';
    public const STATUS_DEPARTED = 'departed';
    public const STATUS_ARRIVED = 'arrived';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_DELAYED = 'delayed';
    
    /**
     * Trip types
     */
    public const TRIP_ONE_WAY = 'one_way';
    public const TRIP_ROUND_TRIP = 'round_trip';
    
    /**
     * Payment terms
     */
    public const PAYMENT_FULL_UPFRONT = 'full_upfront';
    public const PAYMENT_50_PERCENT_DEPOSIT = '50_percent_deposit';
    public const PAYMENT_30_PERCENT_DEPOSIT = '30_percent_deposit';
    
    /**
     * Flight class types
     */
    public const CLASS_ECONOMY = 'economy';
    public const CLASS_BUSINESS = 'business';
    public const CLASS_FIRST = 'first_class';
    
    /**
     * Available flight statuses
     */
    public const STATUSES = [
        self::STATUS_SCHEDULED,
        self::STATUS_BOARDING,
        self::STATUS_DEPARTED,
        self::STATUS_ARRIVED,
        self::STATUS_CANCELLED,
        self::STATUS_DELAYED,
    ];
    
    /**
     * Available trip types
     */
    public const TRIP_TYPES = [
        self::TRIP_ONE_WAY,
        self::TRIP_ROUND_TRIP,
    ];
    
    /**
     * Available payment terms
     */
    public const PAYMENT_TERMS = [
        self::PAYMENT_FULL_UPFRONT,
        self::PAYMENT_50_PERCENT_DEPOSIT,
        self::PAYMENT_30_PERCENT_DEPOSIT,
    ];
    
    /**
     * Available flight classes
     */
    public const CLASSES = [
        self::CLASS_ECONOMY,
        self::CLASS_BUSINESS,
        self::CLASS_FIRST,
    ];
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'provider_id',
        'airline',
        'flight_number',
        'trip_type',
        'return_flight_number',
        'departure_airport',
        'arrival_airport',
        'departure_datetime',
        'arrival_datetime',
        'return_departure_datetime',
        'return_arrival_datetime',
        'total_seats',
        'available_seats',
        'economy_price',
        'group_economy_price',
        'business_price',
        'group_business_price',
        'first_class_price',
        'group_first_class_price',
        'currency',
        'aircraft_type',
        'amenities',
        'description',
        'is_active',
        'is_group_booking',
        'min_group_size',
        'max_group_size',
        'group_discount_percentage',
        'booking_deadline',
        'allows_agent_collaboration',
        'collaboration_commission_percentage',
        'collaboration_terms',
        'status',
        'baggage_allowance',
        'meal_service',
        'included_services',
        'special_requirements',
        'payment_terms',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'departure_datetime' => 'datetime',
        'arrival_datetime' => 'datetime',
        'return_departure_datetime' => 'datetime',
        'return_arrival_datetime' => 'datetime',
        'booking_deadline' => 'date',
        'total_seats' => 'integer',
        'available_seats' => 'integer',
        'min_group_size' => 'integer',
        'max_group_size' => 'integer',
        'economy_price' => 'decimal:2',
        'group_economy_price' => 'decimal:2',
        'business_price' => 'decimal:2',
        'group_business_price' => 'decimal:2',
        'first_class_price' => 'decimal:2',
        'group_first_class_price' => 'decimal:2',
        'group_discount_percentage' => 'decimal:2',
        'collaboration_commission_percentage' => 'decimal:2',
        'amenities' => 'array',
        'included_services' => 'array',
        'is_active' => 'boolean',
        'is_group_booking' => 'boolean',
        'allows_agent_collaboration' => 'boolean',
        'baggage_allowance' => 'array',
        'meal_service' => 'array',
        'deleted_at' => 'datetime',
    ];
    
    /**
     * Get the flight provider (User)
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
    
    /**
     * Get packages that include this flight
     */
    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'package_flight')
                    ->withPivot(['flight_type', 'is_required', 'markup_percentage', 'custom_price', 'seats_allocated'])
                    ->withTimestamps();
    }
    
    /**
     * Get flight collaborations
     */
    public function collaborations(): HasMany
    {
        return $this->hasMany(FlightCollaboration::class);
    }
    
    /**
     * Get active collaborations
     */
    public function activeCollaborations(): HasMany
    {
        return $this->hasMany(FlightCollaboration::class)->where('status', 'active');
    }
    
    /**
     * Get collaborating agents
     */
    public function collaboratingAgents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'flight_collaborations', 'flight_id', 'collaborator_agent_id')
                    ->withPivot(['status', 'commission_percentage', 'allocated_seats', 'booked_seats'])
                    ->withTimestamps();
    }
    
    /**
     * Scope for active flights
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope for flights by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
    
    /**
     * Scope for available flights (with available seats)
     */
    public function scopeAvailable($query)
    {
        return $query->where('available_seats', '>', 0);
    }
    
    /**
     * Scope for flights departing from specific airport
     */
    public function scopeDepartingFrom($query, string $airport)
    {
        return $query->where('departure_airport', 'like', "%{$airport}%");
    }
    
    /**
     * Scope for flights arriving at specific airport
     */
    public function scopeArrivingAt($query, string $airport)
    {
        return $query->where('arrival_airport', 'like', "%{$airport}%");
    }
    
    /**
     * Scope for flights within date range
     */
    public function scopeWithinDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('departure_datetime', [$startDate, $endDate]);
    }
    
    /**
     * Scope for group booking flights
     */
    public function scopeGroupBookings($query)
    {
        return $query->where('is_group_booking', true);
    }
    
    /**
     * Scope for round-trip flights
     */
    public function scopeRoundTrip($query)
    {
        return $query->where('trip_type', 'round_trip');
    }
    
    /**
     * Scope for flights allowing collaboration
     */
    public function scopeAllowsCollaboration($query)
    {
        return $query->where('allows_agent_collaboration', true);
    }
    
    /**
     * Scope for flights with available booking deadline
     */
    public function scopeWithinBookingDeadline($query)
    {
        return $query->where(function($q) {
            $q->whereNull('booking_deadline')
              ->orWhere('booking_deadline', '>=', now()->toDateString());
        });
    }
    
    /**
     * Get flight duration in minutes
     */
    public function getDurationAttribute(): int
    {
        return $this->departure_datetime->diffInMinutes($this->arrival_datetime);
    }
    
    /**
     * Check if flight has available seats
     */
    public function hasAvailableSeats(int $requiredSeats = 1): bool
    {
        return $this->available_seats >= $requiredSeats;
    }
    
    /**
     * Get price for specific class
     */
    public function getPriceForClass(string $class): ?float
    {
        switch ($class) {
            case self::CLASS_ECONOMY:
                return $this->economy_price;
            case self::CLASS_BUSINESS:
                return $this->business_price;
            case self::CLASS_FIRST:
                return $this->first_class_price;
            default:
                return null;
        }
    }
    
    /**
     * Check if flight is bookable
     */
    public function isBookable(): bool
    {
        return $this->is_active && 
               $this->status === self::STATUS_SCHEDULED && 
               $this->available_seats > 0 && 
               $this->departure_datetime->isFuture();
    }
    
    /**
     * Get route display string
     */
    public function getRouteAttribute(): string
    {
        return $this->departure_airport . ' → ' . $this->arrival_airport;
    }
    
    /**
     * Get occupancy rate
     */
    public function getOccupancyRateAttribute(): float
    {
        if ($this->total_seats === 0) {
            return 0;
        }
        
        $occupiedSeats = $this->total_seats - $this->available_seats;
        return round(($occupiedSeats / $this->total_seats) * 100, 1);
    }
    
    /**
     * Check if flight is a round trip
     */
    public function isRoundTrip(): bool
    {
        return $this->trip_type === self::TRIP_ROUND_TRIP;
    }
    
    /**
     * Get round trip duration in days
     */
    public function getRoundTripDuration(): int
    {
        if (!$this->isRoundTrip() || !$this->return_departure_datetime) {
            return 0;
        }
        
        return $this->departure_datetime->diffInDays($this->return_departure_datetime);
    }
    
    /**
     * Get formatted round trip duration
     */
    public function getFormattedRoundTripDuration(): string
    {
        $days = $this->getRoundTripDuration();
        return $days > 0 ? "{$days} days" : 'Same day return';
    }
    
    /**
     * Check if flight meets minimum group size
     */
    public function meetsMinGroupSize(int $requestedSeats): bool
    {
        if (!$this->is_group_booking) {
            return true;
        }
        
        return $requestedSeats >= $this->min_group_size;
    }
    
    /**
     * Check if flight has space for group size
     */
    public function hasSpaceForGroup(int $requestedSeats): bool
    {
        return $requestedSeats <= $this->available_seats && $requestedSeats <= $this->max_group_size;
    }
    
    /**
     * Get group price for specific class
     */
    public function getGroupPriceForClass(string $class): ?float
    {
        if (!$this->is_group_booking) {
            return $this->getPriceForClass($class);
        }
        
        switch ($class) {
            case self::CLASS_ECONOMY:
                return $this->group_economy_price ?? $this->economy_price;
            case self::CLASS_BUSINESS:
                return $this->group_business_price ?? $this->business_price;
            case self::CLASS_FIRST:
                return $this->group_first_class_price ?? $this->first_class_price;
            default:
                return null;
        }
    }
    
    /**
     * Calculate total price for group booking
     */
    public function calculateGroupTotal(int $passengers, string $class = 'economy'): float
    {
        $pricePerPerson = $this->getGroupPriceForClass($class);
        
        if (!$pricePerPerson) {
            return 0;
        }
        
        $total = $pricePerPerson * $passengers;
        
        // Apply group discount if applicable
        if ($this->is_group_booking && $this->group_discount_percentage > 0) {
            $discount = ($total * $this->group_discount_percentage) / 100;
            $total -= $discount;
        }
        
        return round($total, 2);
    }
    
    /**
     * Check if booking deadline has passed
     */
    public function isBookingDeadlinePassed(): bool
    {
        if (!$this->booking_deadline) {
            return false;
        }
        
        return now()->toDateString() > $this->booking_deadline->toDateString();
    }
    
    /**
     * Get available seats for collaboration
     */
    public function getAvailableSeatsForCollaboration(): int
    {
        if (!$this->allows_agent_collaboration) {
            return 0;
        }
        
        $allocatedToCollaborators = $this->activeCollaborations()->sum('allocated_seats');
        return $this->available_seats - $allocatedToCollaborators;
    }
    
    /**
     * Check if agent can collaborate on this flight
     */
    public function canAgentCollaborate(int $agentId): bool
    {
        if (!$this->allows_agent_collaboration || $this->provider_id === $agentId) {
            return false;
        }
        
        // Check if already collaborating
        return !$this->collaborations()
                    ->where('collaborator_agent_id', $agentId)
                    ->whereIn('status', ['pending', 'accepted', 'active'])
                    ->exists();
    }
    
    /**
     * Get full route string for round trip
     */
    public function getFullRouteAttribute(): string
    {
        if ($this->isRoundTrip()) {
            return $this->departure_airport . ' ⇄ ' . $this->arrival_airport;
        }
        
        return $this->departure_airport . ' → ' . $this->arrival_airport;
    }
    
    /**
     * Get return flight route string
     */
    public function getReturnRouteAttribute(): string
    {
        if (!$this->isRoundTrip()) {
            return '';
        }
        
        return $this->arrival_airport . ' → ' . $this->departure_airport;
    }
    
    /**
     * Get formatted flight duration
     */
    public function getFormattedDurationAttribute(): string
    {
        $departureTime = $this->departure_datetime;
        $arrivalTime = $this->arrival_datetime;
        
        if (!$departureTime || !$arrivalTime) {
            return 'N/A';
        }
        
        $diffInMinutes = $arrivalTime->diffInMinutes($departureTime);
        $hours = intval($diffInMinutes / 60);
        $minutes = $diffInMinutes % 60;
        
        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        } else {
            return "{$minutes}m";
        }
    }
}
