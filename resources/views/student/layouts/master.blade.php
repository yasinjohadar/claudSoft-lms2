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
    <style>
        .profile-completion-banner {
            background: linear-gradient(135deg, #fff7df 0%, #fff2c7 100%);
            border: 1px solid #f2d377;
            border-radius: 14px;
            padding: 0.875rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .profile-completion-main {
            flex: 1 1 auto;
            min-width: 0;
        }

        .profile-completion-title-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.375rem;
        }

        .profile-completion-title {
            color: #7a5b00;
            font-weight: 700;
        }

        .profile-completion-percentage {
            background: #ffc107;
            color: #2d2200;
            border-radius: 999px;
            padding: 0.15rem 0.6rem;
            font-size: 0.8rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .profile-completion-subtitle {
            color: #7d6a2b;
            font-size: 0.88rem;
        }

        .profile-completion-progress {
            height: 8px;
            background: #f4e7b8;
        }

        .profile-completion-progress .progress-bar {
            background: #f0ad00;
        }

        .profile-completion-cta {
            white-space: nowrap;
            font-weight: 600;
        }

        @media (min-width: 992px) {
            .profile-completion-wrapper.app-content {
                min-height: auto;
                margin-block-start: 0.5rem;
                margin-block-end: 0;
            }
        }

        @media (max-width: 768px) {
            .profile-completion-banner {
                flex-direction: column;
                align-items: stretch;
            }

            .profile-completion-cta {
                width: 100%;
            }
        }
    </style>
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

        @php
            $profileCompletion = auth()->check() ? auth()->user()->profile_completion_data : null;
        @endphp
        @if($profileCompletion && $profileCompletion['percentage'] < 100)
            <div class="main-content app-content profile-completion-wrapper">
                <div class="container-fluid mt-2">
                    <div class="profile-completion-banner shadow-sm">
                        <div class="profile-completion-main">
                            <div class="profile-completion-title-row">
                                <h6 class="profile-completion-title mb-0">اكتمال الملف الشخصي</h6>
                                <span class="profile-completion-percentage">{{ $profileCompletion['percentage'] }}%</span>
                            </div>
                            <p class="profile-completion-subtitle mb-2">
                                متبقي {{ $profileCompletion['missing_count'] }} حقول لإكمال ملفك الشخصي.
                            </p>
                            <div class="progress profile-completion-progress" aria-label="نسبة اكتمال الملف الشخصي">
                                <div class="progress-bar"
                                     role="progressbar"
                                     style="width: {{ $profileCompletion['percentage'] }}%;"
                                     aria-valuenow="{{ $profileCompletion['percentage'] }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100"></div>
                            </div>
                        </div>
                        <a href="{{ route('student.profile.edit') }}" class="btn btn-warning btn-sm profile-completion-cta">
                            إكمال الملف الشخصي
                        </a>
                    </div>
                </div>
            </div>
        @endif

        @yield('content')


        @include('student.layouts.footer')

    </div>
    @include('student.layouts.footer-scripts')


</body>

</html>
