<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default AI provider that will be used when
    | no specific provider is requested.
    |
    */

    'default_provider' => env('AI_DEFAULT_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | AI Providers Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for each AI provider type.
    |
    */

    'providers' => [
        'openai' => [
            'default_model' => env('OPENAI_MODEL', 'gpt-4'),
            'api_url' => env('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions'),
            'timeout' => 120,
            'max_tokens' => 4000,
            'temperature' => 0.7,
        ],

        'gemini' => [
            'default_model' => env('GEMINI_MODEL', 'gemini-pro'),
            'api_url' => env('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models'),
            'timeout' => 120,
            'max_tokens' => 4000,
            'temperature' => 0.7,
        ],

        'glm' => [
            'default_model' => env('GLM_MODEL', 'glm-4'),
            'api_url' => env('GLM_API_URL', 'https://open.bigmodel.cn/api/paas/v4/chat/completions'),
            'timeout' => 120,
            'max_tokens' => 4000,
            'temperature' => 0.7,
        ],

        'openrouter' => [
            'default_model' => env('OPENROUTER_MODEL', 'openai/gpt-4'),
            'api_url' => env('OPENROUTER_API_URL', 'https://openrouter.ai/api/v1/chat/completions'),
            'timeout' => 120,
            'max_tokens' => 4000,
            'temperature' => 0.7,
            'http_referer' => env('APP_URL'),
            'app_name' => env('APP_NAME', 'Claudsoft Academy'),
        ],

        'custom' => [
            'timeout' => 120,
            'max_tokens' => 4000,
            'temperature' => 0.7,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limiting configuration for AI requests.
    |
    */

    'rate_limiting' => [
        'enabled' => env('AI_RATE_LIMITING_ENABLED', true),
        'max_requests_per_minute' => env('AI_MAX_REQUESTS_PER_MINUTE', 60),
        'max_requests_per_hour' => env('AI_MAX_REQUESTS_PER_HOUR', 1000),
        'max_requests_per_day' => env('AI_MAX_REQUESTS_PER_DAY', 10000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cost Tracking
    |--------------------------------------------------------------------------
    |
    | Enable cost tracking for AI requests.
    |
    */

    'cost_tracking' => [
        'enabled' => env('AI_COST_TRACKING_ENABLED', true),
        'alert_threshold' => env('AI_COST_ALERT_THRESHOLD', 1000), // USD
    ],

    /*
    |--------------------------------------------------------------------------
    | Prompt Templates Path
    |--------------------------------------------------------------------------
    |
    | Path to prompt templates directory.
    |
    */

    'prompts_path' => resource_path('prompts'),

    /*
    |--------------------------------------------------------------------------
    | Default Prompts
    |--------------------------------------------------------------------------
    |
    | Default prompt templates for different use cases.
    |
    */

    'prompts' => [
        'question_generation' => 'question_generation.txt',
        'quiz_generation' => 'quiz_generation.txt',
        'essay_grading' => 'essay_grading.txt',
        'content_creation' => 'content_creation.txt',
        'translation' => 'translation.txt',
    ],
];

