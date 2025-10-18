<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ServiceOffer Model
 * 
 * Polymorphic model to handle different types of service offers
 * (hotel offers, transport offers, etc.)
 * 
 * @property int $id
 * @property int $provider_id
 * @property string $service_type
 * @property string $name
 * @property string $description
 * @property array $specifications
 * @property decimal $base_price
 * @property string $currency
 * @property array $pricing_rules
 * @property int $max_capacity
 * @property array $availability
 * @property string $status
 * @property array $terms_conditions
 * @property array $cancellation_policy
 * @property boolean $is_api_integrated
 * @property array $api_mapping
 */
class ServiceOffer extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * Service types
     */
    public const TYPE_HOTEL = 'hotel';
    public const TYPE_TRANSPORT = 'transport';
    
    /**
     * Service statuses
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUSPENDED = 'suspended';
    
    /**
     * Available service types
     */
    public const TYPES = [
        self::TYPE_HOTEL,
        self::TYPE_TRANSPORT,
    ];
    
    /**
     * Available statuses
     */
    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
        self::STATUS_DRAFT,
        self::STATUS_SUSPENDED,
    ];
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'provider_id',
        'name',
        'description',
        'specifications',
        'base_price',
        'currency',
        'pricing_rules',
        'max_capacity',
        'availability',
        'status',
        'terms_conditions',
        'cancellation_policy',
        'is_api_integrated',
        'api_mapping',
        'service_type',
        'service_id',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'specifications' => 'array',
        'base_price' => 'decimal:2',
        'pricing_rules' => 'array',
        'availability' => 'array',
        'terms_conditions' => 'array',
        'cancellation_policy' => 'array',
        'is_api_integrated' => 'boolean',
        'api_mapping' => 'array',
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
     * Get the specific service model (HotelService, TransportService, etc.)
     */
    public function service(): MorphTo
    {
        return $this->morphTo();
    }
    
    /**
     * Scope for active offers
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
    
    /**
     * Scope for specific service type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('service_type', $type);
    }
    
    /**
     * Check if the offer is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
    
    /**
     * Activate the offer
     */
    public function activate(): bool
    {
        $this->status = self::STATUS_ACTIVE;
        return $this->save();
    }
    
    /**
     * Deactivate the offer
     */
    public function deactivate(): bool
    {
        $this->status = self::STATUS_INACTIVE;
        return $this->save();
    }
}
