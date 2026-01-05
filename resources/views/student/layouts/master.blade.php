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

    @include('student.layouts.head')
</head>

<body>


    @include('student.layouts.switcher')


    <!-- Loader -->
    <div id="loader">
        <img src="{{asset('assets/images/media/loader.svg')}}" alt="">
    </div>
    <!-- Loader -->

    <div class="page">

        {{-- Impersonation Banner --}}
        @include('components.impersonation-banner')

        @include('student.layouts.main-header')



        {{-- @include('student.layouts.offcanvas-sidebar') --}}



        @include('student.layouts.main-sidebar')

        {{-- Flash Messages --}}
        <div class="container-fluid mt-3">
            @include('student.components.alerts')
        </div>

        @yield('content')


        @include('student.layouts.footer')

    </div>
    @include('student.layouts.footer-scripts')


</body>

</html>
