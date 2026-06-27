
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield("title")</title>

    {{-- Favicon and Site Icons (for Google Search Results) --}}
    {{-- Google uses favicon.ico from root or the icon specified in <link rel="icon"> --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('frontend/assets/images/logo.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('frontend/assets/images/logo.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('frontend/assets/images/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    {{-- For better Google Search Results icon, ensure favicon.ico exists in public root --}}

    {{-- Basic Meta Tags --}}
    @if(View::hasSection('meta_description'))
        <meta name="description" content="@yield('meta_description')">
    @endif
    
    @if(View::hasSection('meta_keywords'))
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
    @include('partials.marketing.google.search-console')
    @include('partials.marketing.google.gtm-head')
    
    {{-- Organization Schema (for all pages) --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Organization",
        "name": "{{ config('app.name') }}",
        "url": "{{ url('/') }}",
        "logo": "{{ asset('frontend/assets/images/logo.png') }}",
        "description": "{{ config('app.description', 'منصة تعليمية متخصصة في تقديم الدورات التدريبية والكورسات التعليمية') }}",
        "address": {
            "@@type": "PostalAddress",
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

    @include('partials.marketing.meta-pixel.base')
    @include('partials.marketing.google.datalayer-events')
