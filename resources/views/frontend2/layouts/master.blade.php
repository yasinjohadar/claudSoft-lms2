<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ e($__env->yieldContent('meta_description', 'أكاديمية كلاودسوفت للخدمات والحلول البرمجية — تدريب تقني، تطوير ويب وموبايل، استشارات وحلول برمجية. دورات عملية واحترافية.')) }}">
    <title>{{ e($__env->yieldContent('title', 'أكاديمية كلاودسوفت للخدمات والحلول البرمجية | Claud Soft Academy')) }}</title>

    <link rel="icon" type="image/png" href="{{ asset('frontend2/assets/images/logo.png') }}">

    <!-- Bootstrap 5 RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('frontend2/assets/css/style.css') }}?v={{ @filemtime(public_path('frontend2/assets/css/style.css')) ?: time() }}">

    @stack('head')
    @include('partials.marketing.google.search-console')
    @include('partials.marketing.google.gtm-head')
    @include('partials.marketing.meta-pixel.base')
    @include('partials.marketing.google.datalayer-events')
</head>
<body>
    @include('partials.marketing.google.gtm-body')

    <!-- Page Loader -->
    <div id="pageLoader" aria-hidden="true">
        <div class="pageLoader-inner">
            <div class="pageLoader-logo">
                <img src="{{ asset('frontend2/assets/images/logo.png') }}" alt="" width="72" height="72">
            </div>
            <div class="pageLoader-spinner"></div>
            <p class="pageLoader-text">جاري التحميل...</p>
        </div>
    </div>

    <!-- Background Orbs -->
    <div class="bg-orbs">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    @include('frontend2.layouts.header')

    @yield('content')

    @include('frontend2.layouts.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('frontend2/assets/js/main.js') }}?v={{ @filemtime(public_path('frontend2/assets/js/main.js')) ?: time() }}"></script>
    @stack('scripts')
</body>
</html>
