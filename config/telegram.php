<?php

return [
    'api_base' => env('TELEGRAM_API_BASE', 'https://api.telegram.org'),

    'webhook_path' => env('TELEGRAM_WEBHOOK_PATH', '/api/webhooks/telegram'),

    'link_token_ttl_minutes' => (int) env('TELEGRAM_LINK_TOKEN_TTL', 30),

    'bridge' => [
        'base_url' => env('TELEGRAM_BRIDGE_BASE_URL', ''),
        'api_key' => env('TELEGRAM_BRIDGE_API_KEY', ''),
        'timeout' => (int) env('TELEGRAM_BRIDGE_TIMEOUT', 30),
    ],
];
