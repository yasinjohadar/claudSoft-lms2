<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application AI engine (parallel stack)
    |--------------------------------------------------------------------------
    |
    | legacy      — existing Http providers under App\Services\Ai (default).
    | laravel_ai  — Laravel AI SDK + laravel_ai_models / App\Services\AiNew.
    | Use only in new integration points; do not change legacy services blindly.
    |
    */
    'application' => [
        'engine' => env('AI_APPLICATION_ENGINE', 'legacy'),
        /*
         | Blog AI wizard: which stack to use when global engine is legacy.
         | null / empty = auto: use Laravel AI SDK if at least one active laravel_ai_models row exists.
         | legacy | laravel_ai = force that stack for blog generate only.
         */
        'blog_engine' => env('AI_BLOG_ENGINE'),
        /*
         | Documentation AI wizard (create page): same semantics as blog_engine.
         */
        'docs_engine' => env('AI_DOCS_ENGINE'),
        /*
         | Admin question generation (AI question bank): same semantics as blog_engine.
         */
        'questions_engine' => env('AI_QUESTIONS_ENGINE'),
        /*
         | Student progress AI reports (admin batch): same semantics as blog_engine.
         */
        'reports_engine' => env('AI_REPORTS_ENGINE'),
        /*
         | Optional hard ceiling for max completion tokens (per laravel_ai_models.max_tokens after DB value).
         | Leave unset for no extra cap (provider limits still apply).
         */
        'completion_tokens_ceiling' => env('AI_COMPLETION_TOKENS_CEILING') !== null && env('AI_COMPLETION_TOKENS_CEILING') !== ''
            ? (int) env('AI_COMPLETION_TOKENS_CEILING')
            : null,
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP client (Laravel AI / Prism uses Illuminate Http / Guzzle)
    |--------------------------------------------------------------------------
    |
    | Windows dev often hits cURL error 60 (unable to get local issuer certificate).
    | Easiest: download https://curl.se/ca/cacert.pem and save as storage/cacert.pem
    | (auto-detected). Or set AI_HTTP_VERIFY to an absolute path, or curl.cainfo in php.ini.
    | For local debugging only you may use AI_HTTP_VERIFY=false (never in production).
    |
    | null = use storage/cacert.pem if the file exists; else PHP/cURL defaults.
    |
    */
    'http' => [
        'verify' => env('AI_HTTP_VERIFY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default AI Provider Names
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the AI providers below should be the
    | default for AI operations when no explicit provider is provided
    | for the operation. This should be any provider defined below.
    |
    */

    'default' => 'openai',
    'default_for_images' => 'gemini',
    'default_for_audio' => 'openai',
    'default_for_transcription' => 'openai',
    'default_for_embeddings' => 'openai',
    'default_for_reranking' => 'cohere',

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Below you may configure caching strategies for AI related operations
    | such as embedding generation. You are free to adjust these values
    | based on your application's available caching stores and needs.
    |
    */

    'caching' => [
        'embeddings' => [
            'cache' => false,
            'store' => env('CACHE_STORE', 'database'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Providers
    |--------------------------------------------------------------------------
    |
    | Below are each of your AI providers defined for this application. Each
    | represents an AI provider and API key combination which can be used
    | to perform tasks like text, image, and audio creation via agents.
    | LaravelAiProviderManager may override key/url per request from DB; restore
    | config in finally to avoid leaking credentials (important under Octane).
    |
    */

    'providers' => [
        'anthropic' => [
            'driver' => 'anthropic',
            'key' => env('ANTHROPIC_API_KEY'),
        ],

        'azure' => [
            'driver' => 'azure',
            'key' => env('AZURE_OPENAI_API_KEY'),
            'url' => env('AZURE_OPENAI_URL'),
            'api_version' => env('AZURE_OPENAI_API_VERSION', '2024-10-21'),
            'deployment' => env('AZURE_OPENAI_DEPLOYMENT', 'gpt-4o'),
            'embedding_deployment' => env('AZURE_OPENAI_EMBEDDING_DEPLOYMENT', 'text-embedding-3-small'),
        ],

        'cohere' => [
            'driver' => 'cohere',
            'key' => env('COHERE_API_KEY'),
        ],

        'deepseek' => [
            'driver' => 'deepseek',
            'key' => env('DEEPSEEK_API_KEY'),
        ],

        'eleven' => [
            'driver' => 'eleven',
            'key' => env('ELEVENLABS_API_KEY'),
        ],

        'gemini' => [
            'driver' => 'gemini',
            'key' => env('GEMINI_API_KEY'),
        ],

        'groq' => [
            'driver' => 'groq',
            'key' => env('GROQ_API_KEY'),
        ],

        'jina' => [
            'driver' => 'jina',
            'key' => env('JINA_API_KEY'),
        ],

        'mistral' => [
            'driver' => 'mistral',
            'key' => env('MISTRAL_API_KEY'),
        ],

        'ollama' => [
            'driver' => 'ollama',
            'key' => env('OLLAMA_API_KEY', ''),
            'url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
        ],

        'openai' => [
            'driver' => 'openai',
            'key' => env('OPENAI_API_KEY'),
            'url' => env('OPENAI_URL', 'https://api.openai.com/v1'),
        ],

        'openrouter' => [
            'driver' => 'openrouter',
            'key' => env('OPENROUTER_API_KEY'),
        ],

        'voyageai' => [
            'driver' => 'voyageai',
            'key' => env('VOYAGEAI_API_KEY'),
        ],

        'xai' => [
            'driver' => 'xai',
            'key' => env('XAI_API_KEY'),
        ],
    ],

];
