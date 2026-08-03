<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Browsershot (PDF export for documentation pages)
    |--------------------------------------------------------------------------
    |
    | Requires Node.js, npm/puppeteer, and Chromium/Chrome on the server.
    | On Docker/Coolify: install chromium and set BROWSERSHOT_NO_SANDBOX=true.
    | Keep puppeteer in package.json dependencies (not only devDependencies).
    |
    */

    'node_binary' => env('BROWSERSHOT_NODE_BINARY'),

    'npm_binary' => env('BROWSERSHOT_NPM_BINARY'),

    'chrome_path' => env('BROWSERSHOT_CHROME_PATH'),

    /*
    | Default true inside Docker / when unset in production-like hosts.
    | Override explicitly with BROWSERSHOT_NO_SANDBOX=false if needed.
    */
    'no_sandbox' => filter_var(
        env('BROWSERSHOT_NO_SANDBOX', file_exists('/.dockerenv') ? 'true' : 'false'),
        FILTER_VALIDATE_BOOLEAN
    ),

    'timeout' => (int) env('BROWSERSHOT_TIMEOUT', 120),

    'pdf_browser_delay_ms' => (int) env('BROWSERSHOT_PDF_BROWSER_DELAY_MS', 1500),

    /*
    | Waiting for network idle often hangs on CDN fonts/CSS in production.
    | Prefer a fixed delay unless explicitly enabled.
    */
    'pdf_wait_until_network_idle' => filter_var(
        env('BROWSERSHOT_PDF_WAIT_NETWORK_IDLE', false),
        FILTER_VALIDATE_BOOLEAN
    ),

    'pdf_signed_url_ttl_minutes' => (int) env('BROWSERSHOT_PDF_SIGNED_URL_TTL', 5),

    /*
    | Optional base URL for Puppeteer when APP_URL is not reachable from Chrome
    | (e.g. Docker: http://127.0.0.1:8000 or internal service hostname)
    */
    'pdf_internal_url' => env('BROWSERSHOT_PDF_INTERNAL_URL'),

    'pdf_browser_use_signed_url' => env('BROWSERSHOT_PDF_BROWSER_USE_SIGNED_URL', false),

    'pdf_viewport_width' => (int) env('BROWSERSHOT_PDF_VIEWPORT_WIDTH', 720),

    'pdf_viewport_height' => (int) env('BROWSERSHOT_PDF_VIEWPORT_HEIGHT', 2400),

    /*
    | Hard safety ceiling before we scale the whole page down to keep it as a
    | single continuous page. Empirically verified against the Chrome build
    | bundled with this project's puppeteer version (binary-searched via
    | Page.printToPDF): paper dimensions hard-fail above 65,535pt (~87,380px
    | at 96dpi) per axis — a 16-bit limit internal to Chromium/Skia. This
    | value must stay safely below that. Re-verify with the same binary
    | search technique after any Chrome/puppeteer upgrade.
    |
    | Below this height: output is one page sized exactly to the content
    | (unchanged from before). Above it: DocumentationPdfExportService
    | shrinks the whole page proportionally via Browsershot::scale() so it
    | still fits on ONE page — it never falls back to paginated A4 output.
    */
    'pdf_safe_single_page_height' => (int) env('BROWSERSHOT_PDF_SAFE_SINGLE_PAGE_HEIGHT', 80000),

    'pdf_branding' => [
        'organization_name' => env('DOCS_PDF_ORG_NAME', 'أكاديمية كلاودسوفت'),
        'tagline' => env('DOCS_PDF_TAGLINE', 'تعليم البرمجة وتطوير البرمجيات'),
        'website' => env('DOCS_PDF_WEBSITE', 'https://claudsoft.com'),
        'logo_path' => env('DOCS_PDF_LOGO_PATH', 'frontend2/assets/images/logo.png'),
        'address' => env('DOCS_PDF_ADDRESS'),
        'phones' => array_values(array_filter(array_map('trim', explode(',', (string) env('DOCS_PDF_PHONES', '905050580036'))))),
        'emails' => array_values(array_filter(array_map('trim', explode(',', (string) env('DOCS_PDF_EMAILS', ''))))),
    ],

];
