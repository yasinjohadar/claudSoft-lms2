
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



