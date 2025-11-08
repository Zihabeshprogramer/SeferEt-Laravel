<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Offer Model
 * 
 * Canonical table for storing flight offers from any source
 * (local, Amadeus, manual, etc.)
 * 
 * @property int $id
 * @property string $offer_source
 * @property string $external_offer_id
 * @property string $offer_hash
 * @property string $origin
 * @property string $destination
 * @property date $departure_date
 * @property date $return_date
 * @property decimal $price_amount
 * @property string $price_currency
 * @property array $segments
 * @property int $owner_agent_id
 */
class Offer extends Model
{
    use HasFactory;

    /**
     * Offer sources
     */
    public const SOURCE_LOCAL = 'local';
    public const SOURCE_AMADEUS = 'amadeus';
    public const SOURCE_MANUAL = 'manual';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'offer_source',
        'external_offer_id',
        'offer_hash',
        'origin',
        'destination',
        'departure_date',
        'return_date',
        'price_amount',
        'price_currency',
        'segments',
        'owner_agent_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
        'price_amount' => 'decimal:2',
        'segments' => 'array',
    ];

    /**
     * Get all bookings associated with this offer
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(FlightBooking::class, 'offer_id');
    }

    /**
     * Get the agent who owns this offer (if applicable)
     */
    public function ownerAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_agent_id');
    }

    /**
     * Scope for Amadeus offers
     */
    public function scopeFromAmadeus($query)
    {
        return $query->where('offer_source', self::SOURCE_AMADEUS);
    }

    /**
     * Scope for local offers
     */
    public function scopeLocal($query)
    {
        return $query->where('offer_source', self::SOURCE_LOCAL);
    }

    /**
     * Scope for manual offers
     */
    public function scopeManual($query)
    {
        return $query->where('offer_source', self::SOURCE_MANUAL);
    }

    /**
     * Check if this is a round trip
     */
    public function isRoundTrip(): bool
    {
        return !is_null($this->return_date);
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return $this->price_currency . ' ' . number_format($this->price_amount, 2);
    }

    /**
     * Get route string
     */
    public function getRouteAttribute(): string
    {
        return $this->origin . ' → ' . $this->destination;
    }
}
