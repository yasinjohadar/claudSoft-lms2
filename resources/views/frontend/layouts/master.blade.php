<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>

    @include("frontend.layouts.head")
    
    <!-- Code blocks LTR styling (after all CSS files) -->
    <style>
        /* Code blocks should be LTR for proper display */
        pre,
        pre code,
        code,
        .code-block,
        .code-block pre,
        .code-block code,
        .article-content pre,
        .article-content pre code,
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
</head>
<body data-bs-theme="LIGHT">
    @include('partials.marketing.google.gtm-body')
    <!-- Page Loader -->
    <div id="page-loader" class="page-loader">
        <div class="loader-content">
            <div class="spinner">
                <div class="spinner-circle"></div>
                <div class="spinner-circle"></div>
                <div class="spinner-circle"></div>
            </div>
            <p class="loader-text">جاري التحميل...</p>
        </div>
    </div>

    @include("frontend.layouts.main-header")


    @yield("content")


    @include("frontend.layouts.footer")

    <!-- Page Loader Styles -->
    <style>
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 1;
            visibility: visible;
            transition: opacity 0.2s ease, visibility 0.2s ease;
        }

        .page-loader.hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .loader-content {
            text-align: center;
        }

        .spinner {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .spinner-circle {
            width: 18px;
            height: 18px;
            background: var(--main-Color);
            border-radius: 50%;
            animation: bounce 1.4s infinite ease-in-out both;
        }

        .spinner-circle:nth-child(1) {
            animation-delay: -0.32s;
        }

        .spinner-circle:nth-child(2) {
            animation-delay: -0.16s;
            background: var(--secondary-Color);
        }

        .spinner-circle:nth-child(3) {
            background: var(--main-Color);
        }

        @keyframes bounce {
            0%, 80%, 100% {
                transform: scale(0);
                opacity: 0.5;
            }
            40% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .loader-text {
            color: var(--secondary-Color);
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.6;
            }
        }

        /* Hide body scroll while loading */
        body.loading {
            overflow: hidden;
        }
    </style>

    <!-- Page Loader Script -->
    <script>
        (function() {
            const loader = document.getElementById('page-loader');

            // Function to hide loader
            function hideLoader() {
                if (loader) {
                    loader.classList.add('hidden');
                    document.body.classList.remove('loading');
                }
            }

            // Hide loader as soon as DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(hideLoader, 100);
                });
            } else {
                // DOM is already ready
                setTimeout(hideLoader, 100);
            }

            // Setup navigation loader after DOM is ready
            document.addEventListener('DOMContentLoaded', function() {
                // Show loader on link clicks
                const links = document.querySelectorAll('a:not([target="_blank"]):not([href^="#"]):not([href^="javascript:"]):not([href^="mailto:"]):not([href^="tel:"])');

                links.forEach(link => {
                    link.addEventListener('click', function(e) {
                        const href = this.getAttribute('href');

                        // Check if it's an internal link
                        if (href && !href.startsWith('#') && !href.startsWith('javascript:') && !href.startsWith('mailto:') && !href.startsWith('tel:')) {
                            loader.classList.remove('hidden');
                            document.body.classList.add('loading');
                        }
                    });
                });

                // Show loader on form submissions
                const forms = document.querySelectorAll('form');
                forms.forEach(form => {
                    form.addEventListener('submit', function() {
                        loader.classList.remove('hidden');
                        document.body.classList.add('loading');
                    });
                });

                // Handle browser back/forward buttons
                window.addEventListener('pageshow', function(event) {
                    if (event.persisted) {
                        hideLoader();
                    }
                });
            });
        })();
    </script>

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
