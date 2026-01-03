
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield("title")</title>

    {{-- Basic Meta Tags --}}
    @hasSection('meta_description')
    <meta name="description" content="@yield('meta_description')">
    @endif
    
    @hasSection('meta_keywords')
    <meta name="keywords" content="@yield('meta_keywords')">
    @endif

    {{-- Additional SEO Meta Tags (for course pages) --}}
    @yield('seo_meta')
    
    {{-- Push head section for additional meta tags --}}
    @stack('head')

    {{-- SEO: Language and Region --}}
    <meta name="language" content="Arabic">
    <meta name="geo.region" content="SA">
    <meta name="geo.placename" content="Saudi Arabia">
    
    {{-- Additional SEO Meta Tags --}}
    <meta name="author" content="{{ config('app.name') }}">
    <meta name="publisher" content="{{ config('app.name') }}">
    <meta name="copyright" content="{{ config('app.name') }}">
    <meta name="revisit-after" content="7 days">
    <meta name="rating" content="general">
    <meta name="distribution" content="global">
    <meta name="coverage" content="worldwide">
    <meta name="target" content="all">
    <meta name="audience" content="all">
    
    {{-- hreflang for Arabic --}}
    <link rel="alternate" hreflang="ar" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="x-default" href="{{ url()->current() }}">
    
    {{-- Google Settings (GTM & Search Console) --}}
    @php
        $googleSettings = \App\Models\GoogleSetting::getSettings();
    @endphp
    
    @if($googleSettings->gtm_enabled && $googleSettings->gtm_container_id)
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','{{ $googleSettings->gtm_container_id }}');</script>
    <!-- End Google Tag Manager -->
    @endif
    
    @if($googleSettings->search_console_enabled && $googleSettings->search_console_verification)
    <!-- Google Search Console Verification -->
    <meta name="google-site-verification" content="{{ $googleSettings->search_console_verification }}" />
    @endif
    
    {{-- Organization Schema (for all pages) --}}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "{{ config('app.name') }}",
        "url": "{{ url('/') }}",
        "logo": "{{ asset('frontend/assets/images/logo.png') }}",
        "description": "{{ config('app.description', 'منصة تعليمية متخصصة في تقديم الدورات التدريبية والكورسات التعليمية') }}",
        "address": {
            "@type": "PostalAddress",
            "addressCountry": "SA",
            "addressLocality": "Riyadh"
        },
        "sameAs": [
            @if(config('services.facebook.url'))
            "{{ config('services.facebook.url') }}",
            @endif
            @if(config('services.twitter.url'))
            "{{ config('services.twitter.url') }}",
            @endif
            @if(config('services.instagram.url'))
            "{{ config('services.instagram.url') }}",
            @endif
            @if(config('services.youtube.url'))
            "{{ config('services.youtube.url') }}"
            @endif
        ]
    }
    </script>
    
    {{-- Preconnect to external resources for faster loading --}}
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    @if($googleSettings->gtm_enabled && $googleSettings->gtm_container_id)
    <link rel="preconnect" href="https://www.googletagmanager.com" crossorigin>
    @endif

    @php
        $cssVersion = config('app.version', '1.0.0');
        $bootstrapVersion = file_exists(public_path('frontend/assets/css/bootstrap.css')) ? filemtime(public_path('frontend/assets/css/bootstrap.css')) : $cssVersion;
        $styleVersion = file_exists(public_path('frontend/assets/css/style.css')) ? filemtime(public_path('frontend/assets/css/style.css')) : $cssVersion;
    @endphp
    <link rel="stylesheet" href="{{ asset("frontend/assets/css/bootstrap.css") }}?v={{ $bootstrapVersion }}">
    <link rel="stylesheet" href="{{ asset("frontend/assets/css/style.css") }}?v={{ $styleVersion }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    {{-- Force remove all nav borders and outlines --}}
    <style>
        .buttom-header *, header nav *, .navbar-nav *, .nav-item, .nav-link {
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
        }
        .nav-item:focus, .nav-item:focus-visible, .nav-item:focus-within,
        .nav-link:focus, .nav-link:focus-visible, .nav-link:focus-within,
        .nav-item:active, .nav-link:active,
        .nav-item.active, .nav-link.active {
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            background: transparent !important;
        }
        *:focus { outline: none !important; }
    </style>



