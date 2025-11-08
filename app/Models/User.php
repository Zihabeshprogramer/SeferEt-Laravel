<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Class User - Sophisticated STI User Model for Umrah Travel Platform
 * 
 * This model implements Single Table Inheritance (STI) to handle multiple user types:
 * - Admin: Platform administrators with full system access
 * - Partner: Travel companies/agents who create and manage Umrah packages
 * - Customer: End users who book Umrah packages
 * 
 * @property string $role
 * @property string $status
 * @property string|null $company_name
 * @property string|null $company_registration_number
 * @property string|null $contact_phone
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles {
        HasRoles::hasRole as spatieHasRole;
    }

    /**
     * User roles constants for better code maintainability
     */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_PARTNER = 'partner';
    public const ROLE_TRAVEL_AGENT = 'travel_agent';
    public const ROLE_CUSTOMER = 'customer';
    public const ROLE_HOTEL_PROVIDER = 'hotel_provider';
    public const ROLE_TRANSPORT_PROVIDER = 'transport_provider';

    /**
     * User status constants
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_PENDING = 'pending';

    /**
     * Available roles array for validation
     */
    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_PARTNER,
        self::ROLE_TRAVEL_AGENT,
        self::ROLE_CUSTOMER,
        self::ROLE_HOTEL_PROVIDER,
        self::ROLE_TRANSPORT_PROVIDER,
    ];

    /**
     * Available statuses array for validation
     */
    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_SUSPENDED,
        self::STATUS_PENDING,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // Standard Laravel fields
        'name',
        'email',
        'password',
        'email_verified_at',
        
        // Role and status management
        'role',
        'status',
        'suspend_reason',
        
        // Contact information
        'phone',
        
        // Partner-specific fields
        'company_name',
        'company_registration_number',
        'contact_phone',
        
        // Profile fields
        'avatar',
        'date_of_birth',
        'gender',
        'nationality',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        
        // Business/Partner additional fields
        'company_description',
        'business_license',
        'tax_number',
        'website',
        'social_links',
        
        // Service provider specific fields
        'service_type', // hotel, transport, etc.
        'service_categories', // JSON array of service categories
        'coverage_areas', // JSON array of coverage areas
        'certification_number',
        'api_credentials', // JSON for third-party integration credentials
        'commission_rate',
        'is_api_enabled',
        
        // Umrah-specific fields
        'has_umrah_experience',
        'completed_umrah_count',
        'special_requirements',
        'emergency_contact_name',
        'emergency_contact_phone',
        
        // System fields
        'last_login_at',
        'last_login_ip',
        'preferences',
        'notes',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
        'has_umrah_experience' => 'boolean',
        'completed_umrah_count' => 'integer',
        'two_factor_enabled' => 'boolean',
        'social_links' => 'array',
        'preferences' => 'array',
        'service_categories' => 'array',
        'coverage_areas' => 'array',
        'api_credentials' => 'array',
        'commission_rate' => 'decimal:2',
        'is_api_enabled' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
        'email_verified_at',
        'last_login_at',
        'date_of_birth',
    ];

    // ===========================================
    // SCOPES FOR FILTERING USERS BY ROLE
    // ===========================================

    /**
     * Scope to filter only customer users
     */
    public function scopeCustomers(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_CUSTOMER);
    }

    /**
     * Scope to filter only partner users (includes all B2B partner types)
     */
    public function scopePartners(Builder $query): Builder
    {
        return $query->whereIn('role', [self::ROLE_PARTNER, self::ROLE_TRAVEL_AGENT, self::ROLE_HOTEL_PROVIDER, self::ROLE_TRANSPORT_PROVIDER]);
    }

    /**
     * Scope to filter only admin users
     */
    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_ADMIN);
    }
    
    /**
     * Scope to filter only hotel provider users
     */
    public function scopeHotelProviders(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_HOTEL_PROVIDER);
    }
    
    /**
     * Scope to filter only transport provider users
     */
    public function scopeTransportProviders(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_TRANSPORT_PROVIDER);
    }
    
    /**
     * Scope to filter all service providers (hotel + transport)
     */
    public function scopeServiceProviders(Builder $query): Builder
    {
        return $query->whereIn('role', [self::ROLE_HOTEL_PROVIDER, self::ROLE_TRANSPORT_PROVIDER]);
    }

    /**
     * Scope to filter active users
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to filter suspended users
     */
    public function scopeSuspended(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SUSPENDED);
    }

    /**
     * Scope to filter pending users
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    // ===========================================
    // ROLE CHECK HELPER METHODS
    // ===========================================

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is a partner
     */
    public function isPartner(): bool
    {
        return $this->role === self::ROLE_PARTNER;
    }
    
    /**
     * Check if user is a travel agent
     */
    public function isTravelAgent(): bool
    {
        return $this->role === self::ROLE_TRAVEL_AGENT;
    }

    /**
     * Check if user is a customer
     */
    public function isCustomer(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }
    
    /**
     * Check if user is a hotel provider
     */
    public function isHotelProvider(): bool
    {
        return $this->role === self::ROLE_HOTEL_PROVIDER;
    }
    
    /**
     * Check if user is a transport provider
     */
    public function isTransportProvider(): bool
    {
        return $this->role === self::ROLE_TRANSPORT_PROVIDER;
    }
    
    /**
     * Check if user is any type of service provider
     */
    public function isServiceProvider(): bool
    {
        return in_array($this->role, [self::ROLE_HOTEL_PROVIDER, self::ROLE_TRANSPORT_PROVIDER]);
    }
    
    /**
     * Check if user is any type of B2B user (partner, travel agent, or service provider)
     */
    public function isB2BUser(): bool
    {
        return in_array($this->role, [self::ROLE_PARTNER, self::ROLE_TRAVEL_AGENT, self::ROLE_HOTEL_PROVIDER, self::ROLE_TRANSPORT_PROVIDER]);
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if user is suspended
     */
    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    /**
     * Check if user is pending approval
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    // ===========================================
    // SMART ACCESSORS FOR PARTNER-SPECIFIC FIELDS
    // ===========================================

    /**
     * Get company name for B2B users (partners and service providers)
     */
    public function getCompanyNameAttribute($value): ?string
    {
        return $this->isB2BUser() ? $value : null;
    }

    /**
     * Get company registration number for B2B users (partners and service providers)
     */
    public function getCompanyRegistrationNumberAttribute($value): ?string
    {
        return $this->isB2BUser() ? $value : null;
    }

    /**
     * Get contact phone for B2B users (partners and service providers)
     */
    public function getContactPhoneAttribute($value): ?string
    {
        return $this->isB2BUser() ? $value : null;
    }

    /**
     * Get company description for B2B users (partners and service providers)
     */
    public function getCompanyDescriptionAttribute($value): ?string
    {
        return $this->isB2BUser() ? $value : null;
    }

    /**
     * Get business license for B2B users (partners and service providers)
     */
    public function getBusinessLicenseAttribute($value): ?string
    {
        return $this->isB2BUser() ? $value : null;
    }

    /**
     * Get tax number for B2B users (partners and service providers)
     */
    public function getTaxNumberAttribute($value): ?string
    {
        return $this->isB2BUser() ? $value : null;
    }

    /**
     * Get website for B2B users (partners and service providers)
     */
    public function getWebsiteAttribute($value): ?string
    {
        return $this->isB2BUser() ? $value : null;
    }

    // ===========================================
    // SMART MUTATORS FOR PARTNER-SPECIFIC FIELDS
    // ===========================================

    /**
     * Set company name for B2B users (partners and service providers)
     */
    public function setCompanyNameAttribute($value): void
    {
        $this->attributes['company_name'] = $this->isB2BUser() ? $value : null;
    }

    /**
     * Set company registration number for B2B users (partners and service providers)
     */
    public function setCompanyRegistrationNumberAttribute($value): void
    {
        $this->attributes['company_registration_number'] = $this->isB2BUser() ? $value : null;
    }

    /**
     * Set contact phone for B2B users (partners and service providers)
     */
    public function setContactPhoneAttribute($value): void
    {
        $this->attributes['contact_phone'] = $this->isB2BUser() ? $value : null;
    }

    /**
     * Set company description for B2B users (partners and service providers)
     */
    public function setCompanyDescriptionAttribute($value): void
    {
        $this->attributes['company_description'] = $this->isB2BUser() ? $value : null;
    }

    /**
     * Set business license for B2B users (partners and service providers)
     */
    public function setBusinessLicenseAttribute($value): void
    {
        $this->attributes['business_license'] = $this->isB2BUser() ? $value : null;
    }

    /**
     * Set tax number for B2B users (partners and service providers)
     */
    public function setTaxNumberAttribute($value): void
    {
        $this->attributes['tax_number'] = $this->isB2BUser() ? $value : null;
    }

    /**
     * Set website for B2B users (partners and service providers)
     */
    public function setWebsiteAttribute($value): void
    {
        $this->attributes['website'] = $this->isB2BUser() ? $value : null;
    }

    // ===========================================
    // DYNAMIC PROFILE METHOD
    // ===========================================

    /**
     * Get user profile based on role
     * Returns role-specific profile information
     */
    public function getProfileAttribute(): array
    {
        $baseProfile = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'status' => $this->status,
            'avatar' => $this->avatar,
        ];

        switch ($this->role) {
            case self::ROLE_PARTNER:
                return array_merge($baseProfile, [
                    'company_info' => [
                        'company_name' => $this->getRawOriginal('company_name'),
                        'registration_number' => $this->getRawOriginal('company_registration_number'),
                        'contact_phone' => $this->getRawOriginal('contact_phone'),
                        'description' => $this->getRawOriginal('company_description'),
                        'business_license' => $this->getRawOriginal('business_license'),
                        'tax_number' => $this->getRawOriginal('tax_number'),
                        'website' => $this->getRawOriginal('website'),
                    ],
                    'address_info' => [
                        'address' => $this->address,
                        'city' => $this->city,
                        'state' => $this->state,
                        'postal_code' => $this->postal_code,
                        'country' => $this->country,
                    ],
                ]);

            case self::ROLE_CUSTOMER:
                return array_merge($baseProfile, [
                    'personal_info' => [
                        'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
                        'gender' => $this->gender,
                        'nationality' => $this->nationality,
                        'has_umrah_experience' => $this->has_umrah_experience,
                        'completed_umrah_count' => $this->completed_umrah_count,
                    ],
                    'emergency_contact' => [
                        'name' => $this->emergency_contact_name,
                        'phone' => $this->emergency_contact_phone,
                    ],
                    'special_requirements' => $this->special_requirements,
                ]);

            case self::ROLE_ADMIN:
                return array_merge($baseProfile, [
                    'admin_info' => [
                        'last_login' => $this->last_login_at?->format('Y-m-d H:i:s'),
                        'two_factor_enabled' => $this->two_factor_enabled,
                        'notes' => $this->notes,
                    ],
                ]);

            default:
                return $baseProfile;
        }
    }

    // ===========================================
    // UTILITY METHODS
    // ===========================================

    /**
     * Get the user's full name with title based on role
     */
    public function getFullNameWithTitleAttribute(): string
    {
        $title = match($this->role) {
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_PARTNER => 'Partner',
            self::ROLE_CUSTOMER => 'Customer',
            default => ''
        };

        return $title ? "{$title}: {$this->name}" : $this->name;
    }

    /**
     * Get status badge color for AdminLTE
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'success',
            self::STATUS_SUSPENDED => 'danger',
            self::STATUS_PENDING => 'warning',
            default => 'secondary'
        };
    }

    /**
     * Get role badge color for AdminLTE
     */
    public function getRoleBadgeAttribute(): string
    {
        return match($this->role) {
            self::ROLE_ADMIN => 'primary',
            self::ROLE_PARTNER => 'info',
            self::ROLE_CUSTOMER => 'secondary',
            default => 'light'
        };
    }

    /**
     * Suspend a user with reason
     */
    public function suspend(string $reason = null): bool
    {
        $this->status = self::STATUS_SUSPENDED;
        $this->suspend_reason = $reason;
        return $this->save();
    }

    /**
     * Activate a user
     */
    public function activate(): bool
    {
        $this->status = self::STATUS_ACTIVE;
        $this->suspend_reason = null;
        return $this->save();
    }

    /**
     * Update last login information
     */
    public function updateLastLogin(string $ip = null): bool
    {
        $this->last_login_at = now();
        $this->last_login_ip = $ip ?? request()->ip();
        return $this->save();
    }

    /**
     * Check if user can perform admin actions
     */
    public function canAdministrate(): bool
    {
        return $this->isAdmin() && $this->isActive();
    }

    /**
     * Check if user can create packages (for partners)
     */
    public function canCreatePackages(): bool
    {
        return $this->isPartner() && $this->isActive();
    }

    /**
     * Check if user can book packages (for customers)
     */
    public function canBookPackages(): bool
    {
        return $this->isCustomer() && $this->isActive();
    }
    
    // ===========================================
    // SERVICE PROVIDER RELATIONSHIPS
    // ===========================================
    
    /**
     * Get hotel services for hotel providers
     */
    public function hotelServices(): HasMany
    {
        return $this->hasMany(HotelService::class, 'provider_id');
    }
    
    /**
     * Get transport services for transport providers
     */
    public function transportServices(): HasMany
    {
        return $this->hasMany(TransportService::class, 'provider_id');
    }
    
    /**
     * Get all service offers for service providers
     */
    public function serviceOffers(): HasMany
    {
        return $this->hasMany(ServiceOffer::class, 'provider_id');
    }
    
    /**
     * Get packages created by partners
     */
    public function packages(): HasMany
    {
        return $this->hasMany(Package::class, 'creator_id');
    }
    
    /**
     * Get user's favorites
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(UserFavorite::class);
    }
    
    /**
     * Get user's favorites by type
     */
    public function favoritesByType(string $type): HasMany
    {
        return $this->hasMany(UserFavorite::class)->where('type', $type);
    }

    /**
     * Get all available roles - Legacy method for compatibility
     *
     * @return array
     */
    public static function getAllRoles(): array
    {
        return self::ROLES;
    }

    /**
     * Check if user has a specific role - Updated to use Spatie roles primarily
     * Falls back to old role column for backward compatibility
     *
     * @param string|array $roles
     * @param string|null $guard
     * @return bool
     */
    public function hasRole($roles = null, $guard = null): bool
    {
        // Use the HasRoles trait implementation if user has any Spatie roles
        if ($this->roles()->exists()) {
            // Call the trait's hasRole method using the alias
            return $this->spatieHasRole($roles, $guard);
        }
        
        // Fall back to old role column system for backward compatibility
        if (is_string($roles)) {
            return $this->role === $roles;
        }
        
        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }
        
        return false;
    }

    /**
     * Scope query to only include users of a given role - Legacy method for compatibility
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $role
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }
}
