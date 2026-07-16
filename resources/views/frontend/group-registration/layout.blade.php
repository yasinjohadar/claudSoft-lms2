<!DOCTYPE html>
<html lang="ar" dir="rtl" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="color-scheme" content="light only">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">
    <title>@yield('title', 'التسجيل — كلاودسوفت')</title>

    @php
        $grAssetVersion = static function (string $publicPath, string $fallback = '1'): string {
            $fullPath = public_path($publicPath);

            return file_exists($fullPath) ? (string) filemtime($fullPath) : $fallback;
        };
    @endphp

    <link rel="icon" type="image/png" href="/frontend/assets/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;800&family=Alexandria:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/frontend/assets/css/bootstrap.css?v={{ $grAssetVersion('frontend/assets/css/bootstrap.css') }}">
    <link rel="stylesheet" href="/assets/icon-fonts/fontawesome/css/all.min.css?v={{ $grAssetVersion('assets/icon-fonts/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="/assets/css/group-registration-form.css?v={{ $grAssetVersion('assets/css/group-registration-form.css') }}">

    @stack('head')
    @include('partials.marketing.google.search-console')
    @include('partials.marketing.google.gtm-head')
    @include('partials.marketing.meta-pixel.base')
    @include('partials.marketing.google.datalayer-events')
</head>
<body class="gr-body">
    @include('partials.marketing.google.gtm-body')
    @yield('content')

    <script src="/frontend/assets/bootstrap.js?v={{ $grAssetVersion('frontend/assets/bootstrap.js') }}"></script>
    @stack('scripts')
</body>
</html>
