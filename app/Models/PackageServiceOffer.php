<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PackageServiceOffer Model
 * 
 * Pivot model for Package and ServiceOffer relationships
 * Handles pricing, markup, and integration details
 * 
 * @property int $id
 * @property int $package_id
 * @property int $service_offer_id
 * @property boolean $is_required
 * @property float $markup_percentage
 * @property decimal $custom_price
 * @property array $integration_config
 */
class PackageServiceOffer extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'package_id',
        'service_offer_id',
        'is_required',
        'markup_percentage',
        'custom_price',
        'integration_config',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_required' => 'boolean',
        'markup_percentage' => 'decimal:2',
        'custom_price' => 'decimal:2',
        'integration_config' => 'array',
    ];
    
    /**
     * Get the package
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
    
    /**
     * Get the service offer
     */
    public function serviceOffer(): BelongsTo
    {
        return $this->belongsTo(ServiceOffer::class);
    }
    
    /**
     * Calculate the final price for this service in the package
     */
    public function calculatePrice(): float
    {
        if ($this->custom_price) {
            return $this->custom_price;
        }
        
        $basePrice = $this->serviceOffer->base_price;
        $markup = $basePrice * ($this->markup_percentage / 100);
        
        return $basePrice + $markup;
    }
    
    /**
     * Check if this service is required for the package
     */
    public function isRequired(): bool
    {
        return $this->is_required;
    }
}
