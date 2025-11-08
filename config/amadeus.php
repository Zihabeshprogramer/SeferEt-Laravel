<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Amadeus API Credentials
    |--------------------------------------------------------------------------
    |
    | These credentials are used to authenticate with the Amadeus API.
    | You can obtain these from your Amadeus Self-Service account.
    |
    */
    'api_key' => env('AMADEUS_API_KEY'),
    'api_secret' => env('AMADEUS_API_SECRET'),
    
    /*
    |--------------------------------------------------------------------------
    | Amadeus Environment
    |--------------------------------------------------------------------------
    |
    | Supported: "test", "production"
    |
    */
    'environment' => env('AMADEUS_ENV', 'test'),
    
    /*
    |--------------------------------------------------------------------------
    | API Base URLs
    |--------------------------------------------------------------------------
    */
    'base_urls' => [
        'test' => 'https://test.api.amadeus.com',
        'production' => 'https://api.amadeus.com',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'token_ttl' => 1800, // 30 minutes in seconds
        'search_ttl' => 600, // 10 minutes in seconds
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */
    'log_channel' => env('AMADEUS_LOG_CHANNEL', 'daily'),
];
