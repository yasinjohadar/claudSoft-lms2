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

    'whatsapp' => [
        'token' => env('WHATSAPP_TOKEN'),
        'base_url' => env('WHATSAPP_WAPI_BASE_URL', 'https://wapi.flaxxa.com/api/v1'),
        'timeout' => (int) env('WHATSAPP_WAPI_TIMEOUT', 60),
        'max_attachment_kb' => (int) env('WHATSAPP_MAX_ATTACHMENT_KB', 5120),
        'rate_limit_per_minute' => (int) env('WHATSAPP_WAPI_RATE_LIMIT', 30),
    ],

    'wasender' => [
        'base_url' => env('WASENDER_BASE_URL', 'https://www.wasenderapi.com/api'),
        'api_key' => env('WASENDER_API_KEY'),
        'mcp_url' => env('WASENDER_MCP_URL', 'https://wasenderapi.com/mcp'),
        'mcp_personal_access_token' => env('WASENDER_MCP_PERSONAL_ACCESS_TOKEN'),
    ],

    'bunny_stream' => [
        // CDN hostname for direct MP4 playback (e.g. vz-xxxxx.b-cdn.net) — unused when embed token auth is on
        'cdn_hostname' => env('BUNNY_STREAM_CDN_HOSTNAME'),
        // Optional: auto-resolve CDN hostname per library via Bunny Stream API
        'api_key' => env('BUNNY_STREAM_API_KEY'),
        // Token authentication key from Bunny Stream → Security (legacy single-library fallback only)
        'token_security_key' => env('BUNNY_STREAM_TOKEN_SECURITY_KEY'),
        // Embed view token TTL in seconds (default 2 hours)
        'embed_token_ttl' => (int) env('BUNNY_STREAM_EMBED_TOKEN_TTL', 7200),
        // null/omit = auto-enable when token_security_key is set; true/false to force
        'embed_token_enabled' => env('BUNNY_STREAM_EMBED_TOKEN_ENABLED'),
    ],

];
