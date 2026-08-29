<!DOCTYPE html>
<html lang="en" dir="rtl" data-nav-layout="vertical" data-theme-mode="" data-header-styles=""
    data-menu-styles="" data-toggled="close">

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

    <!-- Apply dark mode immediately to prevent flash of white -->
    <script>
        (function() {
            // Apply dark mode from localStorage immediately
            if (localStorage.getItem("valexdarktheme")) {
                document.documentElement.setAttribute("data-theme-mode", "dark");
                document.documentElement.setAttribute("data-menu-styles", "dark");
                document.documentElement.setAttribute("data-header-styles", "dark");
            }
            // Apply mixed mode (dark sidebar + light header/content) immediately
            if (localStorage.getItem("valexMixedTheme")) {
                document.documentElement.setAttribute("data-theme-mode", "light");
                document.documentElement.setAttribute("data-menu-styles", "dark");
                document.documentElement.setAttribute("data-header-styles", "light");
            }
            // Set loader background color based on theme
            const isDark = localStorage.getItem("valexdarktheme");
            document.documentElement.style.setProperty('--loader-bg', isDark ? '#0d0d0d' : '#fff');
            // Mark theme as applied
            if (document.body) {
                document.body.setAttribute("data-theme-applied", "true");
            } else {
                document.addEventListener('DOMContentLoaded', function() {
                    if (document.body) {
                        document.body.setAttribute("data-theme-applied", "true");
                    }
                });
            }
        })();
    </script>

    @include('admin.layouts.head')
    
    <!-- Code blocks LTR styling (after all CSS files) -->
    <style>
        /* Code blocks should be LTR for proper display */
        pre,
        pre code,
        code,
        .code-block,
        .code-block pre,
        .code-block code,
        [class*="language-"],
        [class*="language-"] code {
            direction: ltr !important;
            text-align: left !important;
            unicode-bidi: embed !important;
        }
        
        /* Ensure code inside pre is also LTR */
        pre > code,
        pre code {
            direction: ltr !important;
            text-align: left !important;
            display: block !important;
        }
    </style>
    
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
            background: var(--loader-bg, #fff);
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
        
        /* Hide body until theme is applied */
        body:not([data-theme-applied]) {
            visibility: hidden;
        }
        body[data-theme-applied] {
            visibility: visible;
        }
    </style>
</head>

<body class="admin-portal">


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

        @auth
            @if(auth()->user()->hasRole('admin'))
                <x-admin.impersonate-modal />
            @endif
        @endauth

        @include('admin.layouts.footer')

    </div>
    @include('admin.layouts.footer-scripts')

    @auth
    <!-- Session Tracking Script -->
    <script>
        (function() {
            'use strict';
            
            let sessionId = null;
            let heartbeatInterval = null;
            let idleTimer = null;
            let isIdle = false;
            let lastActivity = Date.now();
            const IDLE_THRESHOLD = 300000; // 5 minutes
            const HEARTBEAT_INTERVAL = 30000; // 30 seconds
            
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            // Track activity function
            function trackActivity(activityType, data = {}) {
                if (!csrfToken) return;
                
                const payload = {
                    activity_type: activityType,
                    page_url: window.location.href,
                    activity_details: {
                        ...data,
                        screen_width: window.screen.width,
                        screen_height: window.screen.height,
                        viewport_width: window.innerWidth,
                        viewport_height: window.innerHeight,
                        user_agent: navigator.userAgent,
                        timestamp: new Date().toISOString(),
                    }
                };
                
                fetch('{{ route("session.track") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                    credentials: 'same-origin'
                }).catch(err => {
                    console.error('Failed to track activity:', err);
                });
            }
            
            // Heartbeat function
            function sendHeartbeat() {
                if (!csrfToken) return;
                
                fetch('{{ route("session.heartbeat") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin'
                }).catch(err => {
                    console.error('Failed to send heartbeat:', err);
                });
            }
            
            // Update last activity
            function updateActivity() {
                lastActivity = Date.now();
                
                if (isIdle) {
                    isIdle = false;
                    trackActivity('idle_end');
                }
                
                // Reset idle timer
                clearTimeout(idleTimer);
                idleTimer = setTimeout(() => {
                    if (!isIdle) {
                        isIdle = true;
                        trackActivity('idle_start');
                    }
                }, IDLE_THRESHOLD);
            }

            function sendDisconnectBeacon() {
                if (!csrfToken) return;

                if (navigator.sendBeacon) {
                    const formData = new FormData();
                    formData.append('activity_type', 'disconnect');
                    formData.append('page_url', window.location.href);
                    formData.append('_token', csrfToken);
                    navigator.sendBeacon('{{ route("session.track") }}', formData);
                    return;
                }

                trackActivity('disconnect', {
                    duration: Date.now() - lastActivity,
                });
            }
            
            // page_view is recorded server-side by SessionTrackingMiddleware only.
            
            // Single focus source to avoid duplicate focus_lost/focus_gained
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    trackActivity('focus_lost');
                } else {
                    trackActivity('focus_gained');
                    trackActivity('reconnect');
                    updateActivity();
                }
            });
            
            // Track user activity
            ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(event => {
                document.addEventListener(event, updateActivity, { passive: true });
            });
            
            // Closing the tab/app reports disconnect only — never session_end
            window.addEventListener('pagehide', sendDisconnectBeacon);
            window.addEventListener('beforeunload', sendDisconnectBeacon);
            
            // Start heartbeat
            heartbeatInterval = setInterval(sendHeartbeat, HEARTBEAT_INTERVAL);
            
            // Initialize idle timer
            updateActivity();
        })();
    </script>
    @endauth

</body>

</html>
