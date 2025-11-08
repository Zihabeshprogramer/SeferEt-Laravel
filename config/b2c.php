<?php

return [
    /*
    |--------------------------------------------------------------------------
    | B2C Platform Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings for the B2C (customer-facing) platform.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Asset Configuration
    |--------------------------------------------------------------------------
    */
    'assets' => [
        'base_url' => env('B2C_ASSET_BASE_URL', '/storage'),
        'default_image' => '/images/defaults/package-placeholder.jpg',
        'image_sizes' => [
            'thumbnail' => ['width' => 150, 'height' => 100],
            'small' => ['width' => 300, 'height' => 200],
            'medium' => ['width' => 600, 'height' => 400],
            'large' => ['width' => 1200, 'height' => 800],
            'hero' => ['width' => 1920, 'height' => 1080],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination Configuration
    |--------------------------------------------------------------------------
    */
    'pagination' => [
        'packages_per_page' => 12,
        'search_results_per_page' => 15,
        'reviews_per_page' => 10,
        'bookings_per_page' => 20,
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => env('B2C_CACHE_ENABLED', true),
        'ttl' => [
            'packages' => 3600, // 1 hour
            'featured_packages' => 7200, // 2 hours
            'package_details' => 1800, // 30 minutes
            'search_filters' => 86400, // 24 hours
            'statistics' => 14400, // 4 hours
        ],
        'tags' => [
            'packages' => 'b2c_packages',
            'search' => 'b2c_search',
            'statistics' => 'b2c_stats',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Configuration
    |--------------------------------------------------------------------------
    */
    'search' => [
        'min_query_length' => 2,
        'max_query_length' => 100,
        'search_fields' => [
            'name',
            'description', 
            'detailed_description',
            'destinations',
            'highlights',
            'tags'
        ],
        'weight_factors' => [
            'name' => 3,
            'destinations' => 2,
            'description' => 1.5,
            'highlights' => 1.2,
            'tags' => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Filter Configuration
    |--------------------------------------------------------------------------
    */
    'filters' => [
        'price_ranges' => [
            '0-1000' => '$0 - $1,000',
            '1000-2500' => '$1,000 - $2,500',
            '2500-5000' => '$2,500 - $5,000',
            '5000-10000' => '$5,000 - $10,000',
            '10000+' => '$10,000+'
        ],
        'durations' => [
            '1-3' => '1-3 days',
            '4-7' => '4-7 days',
            '8-14' => '8-14 days',
            '15-21' => '15-21 days',
            '22-30' => '22-30 days',
            '30+' => '30+ days'
        ],
        'ratings' => [
            '4.5' => '4.5+ Stars',
            '4.0' => '4.0+ Stars',
            '3.5' => '3.5+ Stars',
            '3.0' => '3.0+ Stars',
        ],
        'popular_destinations' => [
            'Makkah',
            'Madinah',
            'Makkah & Madinah',
            'Jeddah',
            'Taif',
            'Al-Ula'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Booking Configuration
    |--------------------------------------------------------------------------
    */
    'booking' => [
        'min_advance_days' => 7,
        'max_advance_days' => 365,
        'default_travelers' => 2,
        'max_travelers_per_booking' => 10,
        'cancellation_policy_hours' => 48,
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO Configuration
    |--------------------------------------------------------------------------
    */
    'seo' => [
        'default_meta' => [
            'title' => 'SeferEt - Premium Umrah & Travel Packages',
            'description' => 'Discover premium Umrah and travel packages with SeferEt. Book your spiritual journey with trusted partners and exceptional service.',
            'keywords' => 'umrah, hajj, travel, packages, makkah, madinah, spiritual journey, pilgrimage',
        ],
        'og_image' => '/images/seo/og-default.jpg',
        'twitter_handle' => '@SeferEt',
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    */
    'features' => [
        'lazy_loading' => true,
        'infinite_scroll' => false,
        'advanced_search' => true,
        'package_comparison' => true,
        'wishlist' => true,
        'reviews_enabled' => true,
        'social_sharing' => true,
        'price_alerts' => false,
        'multi_language' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    */
    'api' => [
        'rate_limit' => 100, // requests per minute
        'timeout' => 30, // seconds
        'enable_external_apis' => env('B2C_ENABLE_EXTERNAL_APIS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    */
    'performance' => [
        'eager_load_relations' => [
            'creator',
            'packageActivities',
            'hotels',
            'flights',
            'transportServices'
        ],
        'image_optimization' => true,
        'cdn_enabled' => env('CDN_ENABLED', false),
        'cdn_url' => env('CDN_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Configuration
    |--------------------------------------------------------------------------
    */
    'emails' => [
        'contact_recipient' => env('B2C_CONTACT_EMAIL', 'support@seferet.com'),
        'booking_notifications' => env('B2C_BOOKING_EMAIL', 'bookings@seferet.com'),
        'support_email' => env('B2C_SUPPORT_EMAIL', 'support@seferet.com'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Social Media Links
    |--------------------------------------------------------------------------
    */
    'social' => [
        'facebook' => env('SOCIAL_FACEBOOK'),
        'twitter' => env('SOCIAL_TWITTER'),
        'instagram' => env('SOCIAL_INSTAGRAM'),
        'youtube' => env('SOCIAL_YOUTUBE'),
        'linkedin' => env('SOCIAL_LINKEDIN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact Information
    |--------------------------------------------------------------------------
    */
    'contact' => [
        'phone' => env('CONTACT_PHONE', '+1 (234) 567-8900'),
        'email' => env('CONTACT_EMAIL', 'support@seferet.com'),
        'address' => env('CONTACT_ADDRESS', 'SeferEt Headquarters'),
        'hours' => env('CONTACT_HOURS', '24/7'),
        'support_available' => true,
    ],
];