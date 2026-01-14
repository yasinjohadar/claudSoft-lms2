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
            
            // Track page view on load
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    trackActivity('page_view', {
                        referrer: document.referrer,
                    });
                });
            } else {
                trackActivity('page_view', {
                    referrer: document.referrer,
                });
            }
            
            // Track visibility changes (focus/blur)
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    trackActivity('focus_lost');
                } else {
                    trackActivity('focus_gained');
                    updateActivity();
                }
            });
            
            // Track window focus/blur
            window.addEventListener('blur', () => {
                trackActivity('focus_lost');
            });
            
            window.addEventListener('focus', () => {
                trackActivity('focus_gained');
                updateActivity();
            });
            
            // Track user activity
            ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(event => {
                document.addEventListener(event, updateActivity, { passive: true });
            });
            
            // Track page unload
            window.addEventListener('beforeunload', () => {
                trackActivity('session_end', {
                    duration: Date.now() - lastActivity,
                });
                
                // Send synchronously
                if (navigator.sendBeacon && csrfToken) {
                    const formData = new FormData();
                    formData.append('activity_type', 'session_end');
                    formData.append('_token', csrfToken);
                    navigator.sendBeacon('{{ route("session.track") }}', formData);
                }
            });
            
            // Track history changes (SPA navigation)
            let lastUrl = location.href;
            new MutationObserver(() => {
                const url = location.href;
                if (url !== lastUrl) {
                    lastUrl = url;
                    trackActivity('page_view', {
                        referrer: document.referrer,
                    });
                }
            }).observe(document, { subtree: true, childList: true });
            
            // Track popstate (browser back/forward)
            window.addEventListener('popstate', () => {
                trackActivity('page_view', {
                    referrer: document.referrer,
                });
            });
            
            // Start heartbeat
            heartbeatInterval = setInterval(sendHeartbeat, HEARTBEAT_INTERVAL);
            
            // Initialize idle timer
            updateActivity();
        })();
    </script>
    @endauth

</body>

</html>
