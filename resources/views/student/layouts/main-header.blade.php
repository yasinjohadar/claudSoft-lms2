         <!-- app-header -->
         <header class="app-header">

            <!-- Start::main-header-container -->
            <div class="main-header-container container-fluid">

                <!-- Start::header-content-left -->
                <div class="header-content-left">
                    @php
                        $studentHeaderLogoUrl = asset('frontend2/assets/images/logo.png');
                    @endphp

                    <!-- Start::header-element -->
                    <div class="header-element">
                        <!-- Start::header-link -->
                        {{--
                            صنف .sidemenu-toggle إجباري: defaultmenu.min.js يقرأه بـ querySelector
                            ثم يستدعي addEventListener بلا فحص null.
                        --}}
                        <a aria-label="إظهار/إخفاء القائمة الجانبية"
                           class="sidemenu-toggle header-link hr-navtoggle"
                           data-bs-toggle="sidebar"
                           href="javascript:void(0);">
                            <span class="hr-navtoggle__box" aria-hidden="true">
                                <span class="hr-navtoggle__bar"></span>
                                <span class="hr-navtoggle__bar"></span>
                                <span class="hr-navtoggle__bar"></span>
                            </span>
                        </a>
                        <div class="main-header-center hr-search d-none d-lg-block position-relative">
                            <input class="form-control hr-search__input" placeholder="إكتب للبحث..." type="search" aria-label="بحث">
                            <button class="btn hr-search__btn" type="button" aria-label="بحث">
                                <svg xmlns="http://www.w3.org/2000/svg" class="hr-search__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                            </button>
                            <span class="hr-search__underline" aria-hidden="true"></span>
                        </div>
                        <!-- End::header-link -->
                    </div>
                    <!-- End::header-element -->

                    <!-- Start::header-element — شعار الجوال -->
                    <div class="header-element d-lg-none student-mobile-header-logo-wrap">
                        <a href="{{ route('student.dashboard') }}" class="student-mobile-header-logo" aria-label="لوحة التحكم">
                            <img src="{{ $studentHeaderLogoUrl }}" alt="أكاديمية كلاودسوفت">
                        </a>
                    </div>
                    <!-- End::header-element -->

                    <!-- شعار سطح المكتب (مخفي في الجوال) -->
                    <div class="header-element d-none d-lg-block">
                        <div class="horizontal-logo">
                            <a href="{{ route('student.dashboard') }}" class="header-logo">
                                <img src="{{ asset('assets/images/brand-logos/desktop-logo.png') }}" alt="logo" class="desktop-logo">
                                <img src="{{ asset('assets/images/brand-logos/toggle-logo.png') }}" alt="logo" class="toggle-logo">
                                <img src="{{ asset('assets/images/brand-logos/desktop-white.png') }}" alt="logo" class="desktop-white">
                                <img src="{{ asset('assets/images/brand-logos/toggle-white.png') }}" alt="logo" class="toggle-white">
                            </a>
                        </div>
                    </div>

                </div>
                <!-- End::header-content-left -->

                <!-- Start::header-content-right -->
                <div class="header-content-right">

                    <div class="header-element Search-element d-block d-lg-none">
                        <!-- Start::header-link|dropdown-toggle -->
                        <a href="javascript:void(0);" class="header-link dropdown-toggle" data-bs-auto-close="outside" data-bs-toggle="dropdown">
                            <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon hr-hicon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                        </a>
                        <!-- End::header-link|dropdown-toggle -->
                        <ul class="main-header-dropdown dropdown-menu dropdown-menu-end Search-element-dropdown" data-popper-placement="none">
                            <li>
                                <div class="input-group w-100 p-2 hr-search hr-search--compact">
                                    <input type="text" class="form-control hr-search__input" placeholder="إكتب للبحث..." aria-label="بحث">
                                    <div class="btn btn-primary d-inline-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="hr-search__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- Start::header-element -->
                    <div class="header-element header-theme-mode">
                        <!-- Start::header-link|layout-setting -->
                        <a href="javascript:void(0);" class="header-link layout-setting">
                            <span class="light-layout">
                                <!-- Start::header-link-icon -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon hr-hicon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" /></svg>
                                <!-- End::header-link-icon -->
                            </span>
                            <span class="dark-layout">
                                <!-- Start::header-link-icon -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon hr-hicon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" /></svg>
                                <!-- End::header-link-icon -->
                            </span>
                        </a>
                        <!-- End::header-link|layout-setting -->
                    </div>
                    <!-- End::header-element -->

                    <!-- Start::header-element -->
                    <div class="header-element messages-dropdown">
                        <!-- Start::header-link|dropdown-toggle -->
                        <a href="javascript:void(0);" class="header-link dropdown-toggle position-relative" data-bs-auto-close="outside" data-bs-toggle="dropdown">
                            <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon hr-hicon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                            @php
                                $unreadMessagesCount = Auth::user()->gamificationNotifications()->messages()->unread()->count();
                            @endphp
                            @if($unreadMessagesCount > 0)
                                <span id="message-badge" class="badge bg-danger rounded-pill student-notif-count-badge">{{ $unreadMessagesCount > 99 ? '99+' : $unreadMessagesCount }}</span>
                            @endif
                        </a>
                        <!-- End::header-link|dropdown-toggle -->
                        <!-- Start::main-header-dropdown -->
                        <div class="main-header-dropdown dropdown-menu dropdown-menu-end main-header-message" data-popper-placement="none" style="width: 400px;">
                            <div class="menu-header-content bg-primary text-fixed-white">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="mb-0 fs-15 fw-semibold text-fixed-white">الرسائل</h6>
                                </div>
                                <p class="dropdown-title-text subtext mb-0 text-fixed-white op-6 pb-0 fs-12">
                                    لديك <span id="message-unread-count">{{ $unreadMessagesCount }}</span> رسالة غير مقروءة
                                </p>
                            </div>
                            <div><hr class="dropdown-divider"></div>
                            <ul class="list-unstyled mb-0" id="header-messages-scroll" style="max-height: 350px; overflow-y: auto;">
                                @php
                                    $headerMessages = Auth::user()->gamificationNotifications()
                                        ->messages()
                                        ->orderBy('created_at', 'desc')
                                        ->limit(10)
                                        ->get();
                                @endphp

                                @forelse($headerMessages as $message)
                                    <li class="dropdown-item {{ !$message->is_read ? 'unread-notification' : '' }} cursor-pointer border-bottom"
                                        onclick="markMessageAsReadAndRedirect({{ $message->id }}, '{{ $message->action_url ?? '#' }}')">
                                        <div class="d-flex align-items-start p-2">
                                            <div class="me-3 flex-shrink-0">
                                                {!! $message->icon_html !!}
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <h6 class="mb-0 fs-13 fw-semibold">{{ $message->title }}</h6>
                                                    @if(!$message->is_read)
                                                        <span class="badge bg-primary rounded-pill" style="font-size: 8px; padding: 2px 6px;">جديد</span>
                                                    @endif
                                                </div>
                                                <p class="mb-1 fs-12 text-muted">{{ Str::limit($message->message, 60) }}</p>
                                                <small class="text-muted fs-11">
                                                    <i class="far fa-clock me-1"></i>{{ $message->time_ago }}
                                                </small>
                                            </div>
                                        </div>
                                    </li>
                                @empty
                                    <li class="dropdown-item text-center py-4">
                                        <i class="fas fa-envelope-open fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">لا توجد رسائل</p>
                                    </li>
                                @endforelse
                            </ul>
                            <div class="text-center dropdown-footer border-top py-2">
                                <a href="{{ route('gamification.messages.index') }}" class="text-primary fs-13 fw-semibold">
                                    <i class="fas fa-arrow-left me-1"></i> عرض كل الرسائل
                                </a>
                            </div>
                        </div>
                        <!-- End::main-header-dropdown -->
                    </div>
                    <!-- End::header-element -->

                    {{-- أنماط شارة الرسائل في public/assets/css/custom.css --}}

                    @push('scripts')
                    <script>
                    function removeStudentMessageBadgeUi() {
                        var badge = document.getElementById('message-badge');
                        if (badge) {
                            badge.remove();
                        }
                    }
                    function markMessageAsReadAndRedirect(messageId, actionUrl) {
                        fetch(`/student/gamification/notifications/${messageId}/mark-as-read`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const unreadCountElement = document.getElementById('message-unread-count');
                                if (unreadCountElement) {
                                    const currentCount = parseInt(unreadCountElement.textContent);
                                    const newCount = Math.max(0, currentCount - 1);
                                    unreadCountElement.textContent = newCount;

                                    const badge = document.getElementById('message-badge');
                                    if (newCount > 0) {
                                        if (badge) {
                                            badge.textContent = newCount > 99 ? '99+' : newCount;
                                        }
                                    } else {
                                        removeStudentMessageBadgeUi();
                                    }
                                }

                                if (actionUrl && actionUrl !== '#') {
                                    window.location.href = actionUrl;
                                }
                            }
                        })
                        .catch(error => console.error('Error:', error));
                    }
                    </script>
                    @endpush

                    <!-- Start::header-element -->
                    <div class="header-element notifications-dropdown main-header-notification">
                        <!-- Start::header-link|dropdown-toggle -->
                        <a href="javascript:void(0);" class="header-link dropdown-toggle position-relative" data-bs-toggle="dropdown" data-bs-auto-close="outside" id="notificationDropdown" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon hr-hicon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                            @php
                                $unreadCount = Auth::user()->gamificationNotifications()->unread()->count();
                            @endphp
                            @if($unreadCount > 0)
                                <span id="notification-badge" class="badge bg-danger rounded-pill student-notif-count-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                            @endif
                        </a>
                        <!-- End::header-link|dropdown-toggle -->
                        <!-- Start::main-header-dropdown -->
                        <div class="main-header-dropdown dropdown-menu dropdown-menu-end main-header-message" data-popper-placement="none" style="width: 400px;">
                            <div class="menu-header-content bg-primary text-fixed-white">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="mb-0 fs-15 fw-semibold text-fixed-white">الإشعارات</h6>
                                    @if($unreadCount > 0)
                                        <span class="badge rounded-pill bg-warning pt-1 text-fixed-black cursor-pointer" onclick="markAllAsReadFromHeader()">
                                            تحديد الكل كمقروء
                                        </span>
                                    @endif
                                </div>
                                <p class="dropdown-title-text subtext mb-0 text-fixed-white op-6 pb-0 fs-12">
                                    لديك <span id="unread-count">{{ $unreadCount }}</span> إشعارات جديدة
                                </p>
                            </div>
                            <div><hr class="dropdown-divider"></div>
                            <ul class="list-unstyled mb-0" id="header-notification-scroll" style="max-height: 350px; overflow-y: auto;">
                                @php
                                    $notifications = Auth::user()->gamificationNotifications()
                                        ->orderBy('created_at', 'desc')
                                        ->limit(10)
                                        ->get();
                                @endphp

                                @forelse($notifications as $notification)
                                    <li class="dropdown-item {{ !$notification->is_read ? 'unread-notification' : '' }} cursor-pointer border-bottom"
                                        onclick="markNotificationAsReadAndRedirect({{ $notification->id }}, '{{ $notification->action_url ?? '#' }}')">
                                        <div class="d-flex align-items-start p-2">
                                            <div class="me-3 flex-shrink-0">
                                                {!! $notification->icon_html !!}
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <h6 class="mb-0 fs-13 fw-semibold">{{ $notification->title }}</h6>
                                                    @if(!$notification->is_read)
                                                        <span class="badge bg-primary rounded-pill" style="font-size: 8px; padding: 2px 6px;">جديد</span>
                                                    @endif
                                                </div>
                                                <p class="mb-1 fs-12 text-muted">{{ Str::limit($notification->message, 60) }}</p>
                                                <small class="text-muted fs-11">
                                                    <i class="far fa-clock me-1"></i>{{ $notification->time_ago }}
                                                </small>
                                            </div>
                                        </div>
                                    </li>
                                @empty
                                    <li class="dropdown-item text-center py-4" id="no-notifications-message">
                                        <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">لا توجد إشعارات</p>
                                    </li>
                                @endforelse
                            </ul>
                            <div class="text-center dropdown-footer border-top py-2">
                                <a href="{{ route('gamification.notifications.index') }}" class="text-primary fs-13 fw-semibold">
                                    <i class="fas fa-arrow-left me-1"></i> عرض جميع الإشعارات
                                </a>
                            </div>
                        </div>
                        <!-- End::main-header-dropdown -->
                    </div>
                    <!-- End::header-element -->

                    {{-- أنماط شارة الإشعارات في public/assets/css/custom.css (لا تستخدم @push styles هنا؛ الرأس يُعرض قبل تضمين الهيدر) --}}

                    @push('scripts')
                    <script>
                    function removeStudentNotificationBadgeUi() {
                        var badge = document.getElementById('notification-badge');
                        if (badge) {
                            badge.remove();
                        }
                    }
                    function appendStudentNotificationBadgeToHeader(count) {
                        var anchor = document.getElementById('notificationDropdown');
                        if (!anchor) {
                            return;
                        }
                        removeStudentNotificationBadgeUi();
                        var badge = document.createElement('span');
                        badge.id = 'notification-badge';
                        badge.className = 'badge bg-danger rounded-pill student-notif-count-badge';
                        badge.textContent = count > 99 ? '99+' : String(count);
                        anchor.appendChild(badge);
                    }
                    function markNotificationAsReadAndRedirect(notificationId, actionUrl) {
                        // Mark notification as read
                        fetch(`/student/gamification/notifications/${notificationId}/mark-as-read`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Update unread count
                                const unreadCountElement = document.getElementById('unread-count');
                                if (unreadCountElement) {
                                    const currentCount = parseInt(unreadCountElement.textContent);
                                    const newCount = Math.max(0, currentCount - 1);
                                    unreadCountElement.textContent = newCount;

                                    // Update or remove badge
                                    const badge = document.getElementById('notification-badge');
                                    if (newCount > 0) {
                                        if (badge) {
                                            badge.textContent = newCount > 99 ? '99+' : newCount;
                                        }
                                    } else {
                                        removeStudentNotificationBadgeUi();
                                    }
                                }

                                // Redirect if there's an action URL
                                if (actionUrl && actionUrl !== '#') {
                                    window.location.href = actionUrl;
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                    }

                    function markAllAsReadFromHeader() {
                        if (!confirm('هل تريد تحديد جميع الإشعارات كمقروءة؟')) {
                            return;
                        }

                        fetch('/student/gamification/notifications/mark-all-as-read', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                removeStudentNotificationBadgeUi();

                                // Update count
                                document.getElementById('unread-count').textContent = '0';

                                // Remove unread styling
                                document.querySelectorAll('.unread-notification').forEach(item => {
                                    item.classList.remove('unread-notification');
                                    const newBadge = item.querySelector('.badge.bg-primary');
                                    if (newBadge) newBadge.remove();
                                });

                                // Hide mark all as read button
                                const markAllBtn = document.querySelector('.badge.bg-warning');
                                if (markAllBtn) markAllBtn.style.display = 'none';

                                alert(data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('حدث خطأ أثناء تحديث الإشعارات');
                        });
                    }

                    // Real-time notification system with browser notifications
                    let lastNotificationCount = 0;
                    let lastNotificationId = null;

                    // Request browser notification permission
                    function requestNotificationPermission() {
                        if ('Notification' in window && Notification.permission === 'default') {
                            Notification.requestPermission();
                        }
                    }

                    // Show browser notification
                    function showBrowserNotification(title, message, icon) {
                        if ('Notification' in window && Notification.permission === 'granted') {
                            const notification = new Notification(title, {
                                body: message,
                                icon: icon || '/favicon.ico',
                                badge: '/favicon.ico',
                                tag: 'gamification-notification',
                                requireInteraction: false,
                                silent: false
                            });

                            // Play notification sound
                            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBiSE0fPTfTkHHGu36+OZRQ0OS6Hf8bllGAg+ltryxnMpBSp+zPLZjjkIGGS36Odz');
                            audio.volume = 0.3;
                            audio.play().catch(() => {}); // Ignore if autoplay blocked
                        }
                    }

                    // Auto-update notification count with real-time detection
                    function updateNotificationCount() {
                        fetch('/student/gamification/notifications/api/unread-count')
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    const count = data.count;
                                    const countElement = document.getElementById('unread-count');
                                    const badge = document.getElementById('notification-badge');

                                    if (countElement) {
                                        countElement.textContent = count;
                                    }

                                    // Detect NEW notifications
                                    if (count > lastNotificationCount && lastNotificationCount !== 0) {
                                        // New notification received!
                                        loadLatestNotification();
                                    }
                                    lastNotificationCount = count;

                                    if (count > 0) {
                                        if (!badge) {
                                            appendStudentNotificationBadgeToHeader(count);
                                        } else {
                                            badge.textContent = count > 99 ? '99+' : count;
                                        }

                                        // Auto-load notifications dropdown if it's open
                                        const dropdown = document.querySelector('#notificationDropdown + .dropdown-menu');
                                        if (dropdown && dropdown.classList.contains('show')) {
                                            loadNotificationsDropdown();
                                        }
                                    } else {
                                        removeStudentNotificationBadgeUi();
                                    }
                                }
                            })
                            .catch(error => console.error('Error updating notification count:', error));
                    }

                    // Load latest notification and show browser notification
                    function loadLatestNotification() {
                        fetch('/student/gamification/notifications/api?limit=1')
                            .then(response => response.json())
                            .then(data => {
                                if (data.success && data.notifications && data.notifications.length > 0) {
                                    const notification = data.notifications[0];

                                    // Check if it's truly a new notification
                                    if (lastNotificationId !== notification.id) {
                                        lastNotificationId = notification.id;

                                        if (typeof window.showStudentToastWidget === 'function') {
                                            window.showStudentToastWidget({
                                                title: notification.title,
                                                body: notification.message,
                                                type: notification.type,
                                            });
                                        }

                                        // Show browser notification
                                        showBrowserNotification(
                                            notification.title,
                                            notification.message,
                                            notification.icon
                                        );

                                        // Refresh the dropdown if open
                                        loadNotificationsDropdown();
                                    }
                                }
                            })
                            .catch(error => console.error('Error loading latest notification:', error));
                    }

                    // Request permission on page load
                    requestNotificationPermission();

                    // Initialize last count
                    fetch('/student/gamification/notifications/api/unread-count')
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                lastNotificationCount = data.count;
                            }
                            // Then start the update cycle
                            updateNotificationCount();
                        });

                    // Check every 3 seconds for instant notifications
                    setInterval(updateNotificationCount, 3000); // Every 3 seconds

                    // Load notifications when dropdown is opened
                    document.getElementById('notificationDropdown')?.addEventListener('click', function() {
                        loadNotificationsDropdown();
                    });

                    function loadNotificationsDropdown() {
                        fetch('/student/gamification/notifications/api')
                            .then(response => response.json())
                            .then(data => {
                                if (data.success && data.notifications) {
                                    updateNotificationsDropdown(data.notifications);
                                }
                            })
                            .catch(error => console.error('Error loading notifications:', error));
                    }

                    function updateNotificationsDropdown(notifications) {
                        const container = document.getElementById('header-notification-scroll');
                        if (!container) return;

                        if (notifications.length === 0) {
                            container.innerHTML = `
                                <li class="dropdown-item text-center py-4" id="no-notifications-message">
                                    <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">لا توجد إشعارات</p>
                                </li>
                            `;
                            return;
                        }

                        let html = '';
                        notifications.forEach(notification => {
                            const unreadClass = !notification.is_read ? 'unread-notification' : '';
                            const newBadge = !notification.is_read ? '<span class="badge bg-primary rounded-pill" style="font-size: 8px; padding: 2px 6px;">جديد</span>' : '';

                            html += `
                                <li class="dropdown-item ${unreadClass} cursor-pointer border-bottom"
                                    onclick="markNotificationAsReadAndRedirect(${notification.id}, '${notification.action_url || '#'}')">
                                    <div class="d-flex align-items-start p-2">
                                        <div class="me-3 flex-shrink-0">
                                            ${notification.icon_html}
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <h6 class="mb-0 fs-13 fw-semibold">${notification.title}</h6>
                                                ${newBadge}
                                            </div>
                                            <p class="mb-1 fs-12 text-muted">${notification.message.substring(0, 60)}${notification.message.length > 60 ? '...' : ''}</p>
                                            <small class="text-muted fs-11">
                                                <i class="far fa-clock me-1"></i>${notification.time_ago}
                                            </small>
                                        </div>
                                    </div>
                                </li>
                            `;
                        });

                        container.innerHTML = html;
                    }
                    </script>
                    @endpush

                    <!-- Start::header-element -->
                    <div class="header-element header-fullscreen">
                        <!-- Start::header-link -->
                        <a onclick="openFullscreen();" href="javascript:void(0);" class="header-link">
                            <svg xmlns="http://www.w3.org/2000/svg" class="full-screen-open full-screen-icon header-link-icon hr-hicon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" /></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="full-screen-close full-screen-icon header-link-icon hr-hicon d-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 9V4.5M9 9H4.5M9 9 3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5 5.25 5.25" /></svg>
                        </a>
                        <!-- End::header-link -->
                    </div>
                    <!-- End::header-element -->

                    <!-- Start::header-element -->
                    <div class="header-element header-sidebar">
                        <!-- Start::header-link-->
                        <a href="javascript:void(0);" class="header-link" data-bs-toggle="offcanvas" data-bs-target="#header-sidebar">
                            <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon hr-hicon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
                        </a>
                        <!-- End::header-link-->
                    </div>
                    <!-- End::header-element -->

                    <!-- Start::header-element -->
                    <div class="header-element headerProfile-dropdown">
                        <!-- Start::header-link|dropdown-toggle -->
                        <a href="javascript:void(0);" class="header-link dropdown-toggle" id="mainHeaderProfile" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            <img src="{{ student_profile_photo_url(Auth::user()) }}"
                                 alt="صورة المستخدم"
                                 width="37"
                                 height="37"
                                 class="rounded-circle {{ Auth::user()->photo ? '' : 'student-avatar--logo' }}"
                                 onerror="this.onerror=null;this.src='{{ student_default_avatar_url() }}';this.classList.add('student-avatar--logo');">
                        </a>
                        <!-- End::header-link|dropdown-toggle -->
                        <ul class="main-header-dropdown dropdown-menu pt-0 header-profile-dropdown dropdown-menu-end main-profile-menu" aria-labelledby="mainHeaderProfile">
                            <li>
                                <div class="main-header-profile bg-primary menu-header-content text-fixed-white">
                                    <div class="my-auto">
                                        <h6 class="mb-0 lh-1 text-fixed-white">{{ Auth::user()->name }}</h6>
                                        <span class="fs-11 op-7 lh-1">{{ Auth::user()->email }}</span>
                                        @if(Auth::user()->username)
                                            <br><span class="fs-11 op-7 lh-1">@{{ Auth::user()->username }}</span>
                                        @endif
                                    </div>
                                </div>
                            </li>

                            <li>
                                <a class="dropdown-item d-flex border-block-end" href="{{ route('student.profile.index') }}">
                                    <i class="bx bx-cog fs-18 me-2 op-7"></i>عرض الملف الشخصي
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex border-block-end" href="{{ route('student.profile.password') }}">
                                    <i class="bx bx-key fs-18 me-2 op-7"></i>تغيير كلمة المرور
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex border-block-end" href="{{ route('student.telegram.link') }}">
                                    <i class="fe fe-send fs-18 me-2 op-7"></i>
                                    {{ ($studentTelegram['linked'] ?? false) ? 'Telegram — مرتبط' : 'ربط Telegram' }}
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="bx bx-log-out fs-18 me-2 op-7"></i>تسجيل الخروج
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </div>
                    <!-- End::header-element -->

                    <!-- Start::header-element -->
                    <div class="header-element">
                        <!-- Start::header-link|switcher-icon -->
                        <a href="javascript:void(0);" class="header-link switcher-icon" data-bs-toggle="offcanvas" data-bs-target="#switcher-canvas">
                            <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon hr-hicon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" /></svg>
                        </a>
                        <!-- End::header-link|switcher-icon -->
                    </div>
                    <!-- End::header-element -->

                </div>
                <!-- End::header-content-right -->

            </div>
            <!-- End::main-header-container -->

        </header>
        <!-- /app-header -->

        @php
        if (! function_exists('getNotificationIconForHeader')) {
        function getNotificationIconForHeader($type) {
            $icons = [
                'achievement_unlocked' => '<i class="fas fa-trophy text-warning fa-lg"></i>',
                'points_earned' => '<i class="fas fa-star text-success fa-lg"></i>',
                'points_deducted' => '<i class="fas fa-minus-circle text-danger fa-lg"></i>',
                'level_up' => '<i class="fas fa-level-up-alt text-primary fa-lg"></i>',
                'course_enrolled' => '<i class="fas fa-book text-info fa-lg"></i>',
                'course_completed' => '<i class="fas fa-check-circle text-success fa-lg"></i>',
                'course_reminder' => '<i class="fas fa-bell text-warning fa-lg"></i>',
                'assignment_due' => '<i class="fas fa-exclamation-triangle text-danger fa-lg"></i>',
                'assignment_graded' => '<i class="fas fa-check text-success fa-lg"></i>',
                'assignment_reminder' => '<i class="fas fa-clock text-warning fa-lg"></i>',
                'invoice_created' => '<i class="fas fa-file-invoice text-info fa-lg"></i>',
                'payment_received' => '<i class="fas fa-money-bill text-success fa-lg"></i>',
                'payment_reminder' => '<i class="fas fa-exclamation-circle text-danger fa-lg"></i>',
                'system_announcement' => '<i class="fas fa-bullhorn text-primary fa-lg"></i>',
                'maintenance_notice' => '<i class="fas fa-tools text-warning fa-lg"></i>',
                'default' => '<i class="fas fa-bell text-secondary fa-lg"></i>',
            ];

            return $icons[$type] ?? $icons['default'];
        }
        }
        @endphp
