<!DOCTYPE html>
<html lang="en" dir="rtl" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="light" data-toggled="close">

<head>

    <!-- Meta Data -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title> @yield('page-title')</title>
    <meta name="Description" content="منصة تعليمية متكاملة تقدم كورسات ودورات تدريبية احترافية مع شهادات معتمدة. تعلم من أفضل المدربين في مختلف المجالات">
    <meta name="Author" content="claudSoft">
    <meta name="keywords" content="منصة تعليمية, كورسات, دورات تدريبية, تعلم إلكتروني, شهادات معتمدة, لوحة التحكم">

    @include('admin.layouts.head')
    
    <!-- Inline Critical CSS to prevent FOUC -->
    <style>
        /* Hide page until CSS loads */
        html:not(.loaded) .page {
            opacity: 0;
            visibility: hidden;
        }
        html.loaded .page {
            opacity: 1;
            visibility: visible;
            transition: opacity 0.3s ease-in-out;
        }
        /* Ensure loader is visible initially */
        #loader {
            display: flex;
            align-items: center;
            justify-content: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #fff;
            z-index: 9999;
        }
        html.loaded #loader {
            display: none;
        }
        
        /* Fix excessive spacing before footer - Global fix */
        .page {
            min-height: auto !important;
            justify-content: flex-start !important;
        }
        .app-content {
            min-height: auto !important;
            margin-block-end: 0 !important;
        }
        .app-content > .container-fluid {
            padding-bottom: 0 !important;
            margin-bottom: 0 !important;
        }
        .footer {
            margin-top: 0 !important;
        }
        
        /* Code blocks should be LTR for proper display */
        pre {
            direction: ltr !important;
            text-align: left !important;
        }
        code {
            direction: ltr !important;
            text-align: left !important;
        }
    </style>
</head>

<body>


    @include('admin.layouts.switcher')


    <!-- Loader -->
    <div id="loader">
        <img src="{{asset('assets/images/media/loader.svg')}}" alt="">
    </div>
    <!-- Loader -->

    <div class="page">


        @include('admin.layouts.main-header')



        {{-- @include('admin.layouts.offcanvas-sidebar') --}}



        @include('admin.layouts.main-sidebar')


        @yield('content')


        @include('admin.layouts.footer')

    </div>
    @include('admin.layouts.footer-scripts')


</body>

</html>
