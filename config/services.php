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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'dropbox' => [
        'app_key'       => env('DROPBOX_APP_KEY'),
        'secret_key'    => env('DROPBOX_SECRET_KEY'),
        'refresh_token' => env('DROPBOX_REFRESH_TOKEN'),
    ],
    'snapgate' => [
        'max_files'   => (int) env('SNAPGATE_MAX_FILES', 10),
        'max_mb'      => (int) env('SNAPGATE_MAX_MB', 20),
        'session_ttl' => (int) env('SNAPGATE_SESSION_TTL', 30), // 分
    ],

];
