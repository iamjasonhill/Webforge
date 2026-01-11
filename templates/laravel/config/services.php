<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => (string) env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => (string) env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => (string) env('AWS_ACCESS_KEY_ID'),
        'secret' => (string) env('AWS_SECRET_ACCESS_KEY'),
        'region' => (string) env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => (string) env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => (string) env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'serpapi' => [
        'key' => (string) env('SERP_API_KEY'),
    ],

];
