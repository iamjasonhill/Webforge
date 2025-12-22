<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Domain Monitor Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the connection to Domain Monitor API for fetching domain
    | information when scaffolding projects.
    |
    */
    'domain_monitor' => [
        'url' => env('DOMAIN_MONITOR_URL'),
        'api_key' => env('DOMAIN_MONITOR_API_KEY'),
        'timeout' => env('DOMAIN_MONITOR_TIMEOUT', 30),
    ],
];
