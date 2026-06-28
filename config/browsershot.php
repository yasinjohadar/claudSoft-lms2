<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Browsershot (PDF export for documentation pages)
    |--------------------------------------------------------------------------
    |
    | Requires Node.js, npm, and Chromium/Chrome on the server.
    | On Docker/Coolify: install chromium and set BROWSERSHOT_NO_SANDBOX=true.
    |
    */

    'node_binary' => env('BROWSERSHOT_NODE_BINARY'),

    'npm_binary' => env('BROWSERSHOT_NPM_BINARY'),

    'chrome_path' => env('BROWSERSHOT_CHROME_PATH'),

    'no_sandbox' => env('BROWSERSHOT_NO_SANDBOX', false),

    'timeout' => (int) env('BROWSERSHOT_TIMEOUT', 120),

    'pdf_browser_delay_ms' => (int) env('BROWSERSHOT_PDF_BROWSER_DELAY_MS', 2000),

    'pdf_signed_url_ttl_minutes' => (int) env('BROWSERSHOT_PDF_SIGNED_URL_TTL', 5),

    /*
    | Optional base URL for Puppeteer when APP_URL is not reachable from Chrome
    | (e.g. Docker: http://127.0.0.1:8000 or internal service hostname)
    */
    'pdf_internal_url' => env('BROWSERSHOT_PDF_INTERNAL_URL'),

    'pdf_browser_use_signed_url' => env('BROWSERSHOT_PDF_BROWSER_USE_SIGNED_URL', false),

    'pdf_viewport_width' => (int) env('BROWSERSHOT_PDF_VIEWPORT_WIDTH', 720),

    'pdf_viewport_height' => (int) env('BROWSERSHOT_PDF_VIEWPORT_HEIGHT', 2400),

    'pdf_branding' => [
        'organization_name' => env('DOCS_PDF_ORG_NAME', 'أكاديمية كلاودسوفت'),
        'tagline' => env('DOCS_PDF_TAGLINE', 'تعليم البرمجة وتطوير البرمجيات'),
    ],

];
