<?php

return [
    'channels' => [
        'database' => env('NOTIFY_CHANNEL_DATABASE', true),
        'realtime' => env('NOTIFY_CHANNEL_REALTIME', true),
        'fcm' => env('NOTIFY_CHANNEL_FCM', false),
        'mail' => env('NOTIFY_CHANNEL_MAIL', true),
        'whatsapp' => env('NOTIFY_CHANNEL_WHATSAPP', true),
    ],

    'fcm' => [
        'enabled' => env('FCM_ENABLED', false),
        'server_key' => env('FCM_SERVER_KEY'),
        'endpoint' => env('FCM_ENDPOINT', 'https://fcm.googleapis.com/fcm/send'),
        'timeout' => env('FCM_TIMEOUT', 20),
    ],

    'defaults' => [
        'locale' => env('NOTIFY_DEFAULT_LOCALE', 'ar'),
    ],
];
