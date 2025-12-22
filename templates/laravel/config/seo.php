<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default SEO Settings
    |--------------------------------------------------------------------------
    */

    'default_description' => env('SEO_DEFAULT_DESCRIPTION', 'Welcome to our website'),
    
    'default_image' => env('SEO_DEFAULT_IMAGE', ''),
    
    'twitter_handle' => env('SEO_TWITTER_HANDLE', ''),
    
    'logo' => env('SEO_LOGO', ''),

    /*
    |--------------------------------------------------------------------------
    | Sitemap Settings
    |--------------------------------------------------------------------------
    */

    'sitemap' => [
        'enabled' => true,
        'cache_duration' => 60, // minutes
    ],
];
