<!-- Scroll To Top -->
<button type="button" class="scrollToTop student-scroll-to-top" aria-label="العودة للأعلى">
    <span class="student-scroll-to-top__ring" aria-hidden="true"></span>
    <span class="student-scroll-to-top__icon" aria-hidden="true"><i class="ri-arrow-up-line"></i></span>
</button>
<div id="responsive-overlay"></div>
{{-- لا يمنع النقر عندما تكون الطبقة غير مفعّلة (كانت تُغطي المحتوى أحياناً مع visibility:hidden --}}
<style>
    #responsive-overlay:not(.active) {
        pointer-events: none !important;
    }

    #responsive-overlay.active {
        pointer-events: auto;
    }
</style>
<!-- Scroll To Top -->

<!-- jQuery -->
<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>

<!-- SweetAlert2 -->
<script src="{{ asset('assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>

<!-- Popper JS -->
<script src="{{ asset('assets/libs/@popperjs/core/umd/popper.min.js') }}"></script>
<!-- Bootstrap JS -->
<script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>


<!-- Defaultmenu JS -->
<script src="{{ asset('assets/js/defaultmenu.min.js') }}"></script>

<!-- Node Waves JS -->
<script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>

<!-- Sticky JS -->
<script src="{{ asset('assets/js/sticky.js') }}"></script>

<!-- Simplebar JS -->
<script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ asset('assets/js/simplebar.js') }}"></script>

<!-- Color Picker JS -->
<script src="{{ asset('assets/libs/@simonwep/pickr/pickr.es5.min.js') }}"></script>

@stack('chart-scripts')

<!-- Custom-Switcher JS -->
<script src="{{ asset_v('assets/js/custom-switcher.min.js') }}"></script>

<script>
    (function () {
        function applyStudentPortalRtl() {
            var html = document.documentElement;
            var rtlStylesheet = document.getElementById('style-rtl');
            var style = document.getElementById('style');

            if (rtlStylesheet && style) {
                style.setAttribute('href', rtlStylesheet.getAttribute('href'));
            }

            html.setAttribute('dir', 'rtl');
            localStorage.setItem('valexrtl', 'true');
            localStorage.removeItem('valexltr');

            var rtlInput = document.getElementById('switcher-rtl');
            var ltrInput = document.getElementById('switcher-ltr');
            if (rtlInput) {
                rtlInput.checked = true;
            }
            if (ltrInput) {
                ltrInput.checked = false;
            }
        }

        function isMiniLayout() {
            var closed = document.getElementById('switcher-closed-menu');
            if (closed && closed.checked) {
                return true;
            }
            return localStorage.getItem('valexverticalstyles') === 'closed';
        }

        function syncSidebarLayoutToggles() {
            var mini = isMiniLayout();
            document.querySelectorAll('.js-sidebar-layout-toggle').forEach(function (root) {
                var track = root.querySelector('.student-sidebar-layout-toggle__track');
                if (track) {
                    track.setAttribute('data-active', mini ? 'mini' : 'full');
                }
            });
            document.querySelectorAll('.js-sidebar-layout-toggle [data-layout]').forEach(function (btn) {
                var active = (btn.getAttribute('data-layout') === 'mini') === mini;
                btn.classList.toggle('is-active', active);
                btn.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
        }

        function applySidebarLayout(mode) {
            var id = mode === 'mini' ? 'switcher-closed-menu' : 'switcher-default-menu';
            var input = document.getElementById(id);
            if (input) {
                input.click();
            }
            syncSidebarLayoutToggles();
        }

        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.js-sidebar-layout-toggle [data-layout]');
            if (!btn) {
                return;
            }
            applySidebarLayout(btn.getAttribute('data-layout'));
        });

        ['switcher-default-menu', 'switcher-closed-menu'].forEach(function (id) {
            var input = document.getElementById(id);
            if (input) {
                input.addEventListener('change', syncSidebarLayoutToggles);
            }
        });

        syncSidebarLayoutToggles();
        window.addEventListener('load', syncSidebarLayoutToggles);

        var resetAll = document.getElementById('reset-all');
        if (resetAll) {
            resetAll.addEventListener('click', function () {
                applyStudentPortalRtl();
                syncSidebarLayoutToggles();
            });
        }

        if (!localStorage.getItem('valexrtl') && !localStorage.getItem('valexltr')) {
            applyStudentPortalRtl();
        }
    })();
</script>

<!-- Custom JS -->
<script src="{{ asset_v('assets/js/custom.js') }}"></script>

<script>
    (function () {
        var scrollBtn = document.querySelector('.student-portal .student-scroll-to-top');
        if (!scrollBtn) {
            return;
        }

        scrollBtn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    })();
</script>

<!-- Show page after all resources load (required for dashboard-fade-in animations) -->
<script>
    window.addEventListener('load', function () {
        document.documentElement.classList.add('loaded');
        const loader = document.getElementById('loader');
        if (loader) {
            loader.style.display = 'none';
        }
    });
</script>

@auth
    @php
        $reverb = config('broadcasting.connections.reverb');
        $reverbEnabled = config('notification_hub.channels.realtime', true) && !empty($reverb['key']);
    @endphp
    <style>
        .student-toast-stack {
            position: fixed;
            top: 80px;
            left: 16px;
            z-index: 11000;
            width: min(360px, calc(100vw - 24px));
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }

        .student-toast-widget {
            pointer-events: auto;
            border-radius: 14px;
            background: #146c43;
            border: 1px solid #198754;
            box-shadow: 0 14px 30px rgba(20, 108, 67, 0.35);
            overflow: hidden;
            transform: translateX(-20px);
            opacity: 0;
            transition: all 0.25s ease;
        }

        .student-toast-widget.is-visible {
            transform: translateX(0);
            opacity: 1;
        }

        .student-toast-content {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 14px;
        }

        .student-toast-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .student-toast-text h6 {
            margin: 0 0 2px;
            font-size: 14px;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.35;
        }

        .student-toast-text p {
            margin: 0;
            font-size: 12px;
            color: #eaf7f0;
            line-height: 1.45;
        }

        .student-toast-close {
            margin-inline-start: auto;
            border: 0;
            background: transparent;
            color: #ffffff;
            font-size: 18px;
            line-height: 1;
            cursor: pointer;
            padding: 0;
        }

        .student-toast-progress {
            height: 3px;
            width: 100%;
            transform-origin: right;
            animation: studentToastTimer linear forwards;
        }

        .student-toast-widget.toast-success .student-toast-icon { background: #eaf7f0; color: #146c43; }
        .student-toast-widget.toast-success .student-toast-progress { background: #75d7a2; }

        .student-toast-widget.toast-info .student-toast-icon { background: #dbeafe; color: #1d4ed8; }
        .student-toast-widget.toast-info .student-toast-progress { background: #3b82f6; }

        .student-toast-widget.toast-warning .student-toast-icon { background: #fef3c7; color: #92400e; }
        .student-toast-widget.toast-warning .student-toast-progress { background: #f59e0b; }

        .student-toast-widget.toast-error .student-toast-icon { background: #fee2e2; color: #991b1b; }
        .student-toast-widget.toast-error .student-toast-progress { background: #ef4444; }

        @keyframes studentToastTimer {
            from { transform: scaleX(1); }
            to { transform: scaleX(0); }
        }
    </style>
    <script>
        (function () {
            const ensureToastStack = function () {
                let stack = document.querySelector('.student-toast-stack');
                if (!stack) {
                    stack = document.createElement('div');
                    stack.className = 'student-toast-stack';
                    document.body.appendChild(stack);
                }
                return stack;
            };

            const normalizeToastType = function (rawType) {
                const t = String(rawType || '').toLowerCase();
                if (t.includes('success') || t.includes('completed') || t.includes('earned')) return 'success';
                if (t.includes('warn') || t.includes('reminder') || t.includes('due')) return 'warning';
                if (t.includes('error') || t.includes('fail')) return 'error';
                return 'info';
            };

            const toastIcon = function (kind) {
                if (kind === 'success') return '✓';
                if (kind === 'warning') return '!';
                if (kind === 'error') return '×';
                return 'i';
            };

            window.showStudentToastWidget = function (payload) {
                const stack = ensureToastStack();
                const title = payload?.title || 'إشعار جديد';
                const body = payload?.body || payload?.message || 'لديك إشعار جديد';
                const rawType = payload?.data?.type || payload?.type || payload?.event_key || 'info';
                const kind = normalizeToastType(rawType);
                const duration = 5200;

                const card = document.createElement('div');
                card.className = `student-toast-widget toast-${kind}`;
                card.innerHTML = `
                    <div class="student-toast-content">
                        <div class="student-toast-icon">${toastIcon(kind)}</div>
                        <div class="student-toast-text">
                            <h6>${title}</h6>
                            <p>${body}</p>
                        </div>
                        <button type="button" class="student-toast-close" aria-label="Close">×</button>
                    </div>
                    <div class="student-toast-progress" style="animation-duration:${duration}ms"></div>
                `;

                stack.prepend(card);
                requestAnimationFrame(() => card.classList.add('is-visible'));

                const closeCard = () => {
                    card.classList.remove('is-visible');
                    setTimeout(() => card.remove(), 240);
                };

                card.querySelector('.student-toast-close')?.addEventListener('click', closeCard);
                setTimeout(closeCard, duration);
            };
        })();
    </script>
    @if($reverbEnabled)
        <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
        <script>
            (function () {
                const userId = {{ (int) auth()->id() }};
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!userId || !window.Pusher) return;

                const key = @json($reverb['key'] ?? null);
                const wsHost = @json(config('reverb.servers.reverb.hostname', '127.0.0.1'));
                const wsPort = Number(@json(config('reverb.servers.reverb.port', 8080)));
                const scheme = @json(config('reverb.servers.reverb.options.scheme', 'http'));
                const forceTLS = scheme === 'https';

                if (!key) return;

                const pusher = new window.Pusher(key, {
                    wsHost: wsHost,
                    wsPort: wsPort,
                    wssPort: wsPort,
                    forceTLS: forceTLS,
                    enabledTransports: ['ws', 'wss'],
                    authEndpoint: '/broadcasting/auth',
                    auth: {
                        headers: {
                            'X-CSRF-TOKEN': csrf || '',
                        }
                    }
                });

                const channel = pusher.subscribe(`private-user.notifications.${userId}`);
                channel.bind('notification.created', function (payload) {
                    try {
                        window.dispatchEvent(new CustomEvent('student-notification-received', {detail: payload}));
                        if (typeof window.showStudentToastWidget === 'function') {
                            window.showStudentToastWidget(payload);
                        }

                        if (window.Swal) {
                            window.Swal.fire({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 4000,
                                timerProgressBar: true,
                                icon: 'info',
                                title: payload?.title || 'إشعار جديد',
                                text: payload?.body || 'لديك إشعار جديد',
                            });
                        }
                    } catch (e) {
                        console.error('Realtime notification handling failed', e);
                    }
                });
            })();
        </script>
    @endif
@endauth

<script src="{{ asset('assets/js/device-token.js') }}?v={{ filemtime(public_path('assets/js/device-token.js')) }}"></script>
<script src="{{ asset('assets/js/device-fingerprint.js') }}?v={{ filemtime(public_path('assets/js/device-fingerprint.js')) }}"></script>

<!-- Page Specific Scripts -->
@yield('scripts')
@stack('scripts')
