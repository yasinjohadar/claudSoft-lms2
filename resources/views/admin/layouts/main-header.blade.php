         <!-- app-header -->
         <header class="app-header">

            <!-- Start::main-header-container -->
            <div class="main-header-container container-fluid">

                <!-- Start::header-content-left -->
                <div class="header-content-left">

                    <!-- Start::header-element -->
                    <div class="header-element">
                        <div class="horizontal-logo">
                            <a href="index.html" class="header-logo">
                          <img src="{{ asset('assets/images/brand-logos/desktop-logo.png') }}" alt="logo" class="desktop-logo">
                            <img src="{{ asset('assets/images/brand-logos/toggle-logo.png') }}" alt="logo" class="toggle-logo">
                            <img src="{{ asset('assets/images/brand-logos/desktop-white.png') }}" alt="logo" class="desktop-white">
                            <img src="{{ asset('assets/images/brand-logos/toggle-white.png') }}" alt="logo" class="toggle-white">

                            </a>
                        </div>
                    </div>
                    <!-- End::header-element -->

                    <!-- Start::header-element -->
                    <div class="header-element">
                        <!-- Start::header-link -->
                        {{--
                            صنف .sidemenu-toggle إجباري: defaultmenu.min.js يقرأه بـ querySelector
                            ثم يستدعي addEventListener بلا فحص null. أما animated-arrow و hor-toggle
                            و horizontal-navtoggle فأُزيلت — كانت للتخطيط الأفقي فقط (CSS بحت).
                        --}}
                        <a aria-label="طيّ الشريط الجانبي" class="sidemenu-toggle header-link hr-navtoggle" data-bs-toggle="sidebar" href="javascript:void(0);">
                            <span class="hr-navtoggle__box" aria-hidden="true">
                                <span class="hr-navtoggle__bar"></span>
                                <span class="hr-navtoggle__bar"></span>
                                <span class="hr-navtoggle__bar"></span>
                            </span>
                        </a>
                        <div class="main-header-center hr-search d-none d-lg-block position-relative" id="adminHeaderUserSearch">
                            <input class="form-control hr-search__input" id="adminHeaderUserSearchInput" placeholder="بحث بالاسم أو البريد أو الهاتف..." type="search" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="adminHeaderUserSearchResults">
                            <button class="btn hr-search__btn" type="button" id="adminHeaderUserSearchBtn" aria-label="بحث">
                                <svg xmlns="http://www.w3.org/2000/svg" class="hr-search__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                            </button>
                            <span class="hr-search__underline" aria-hidden="true"></span>
                            <div id="adminHeaderUserSearchResults" class="admin-header-user-search-results" role="listbox" hidden></div>
                        </div>
                        <!-- End::header-link -->
                    </div>
                    <!-- End::header-element -->

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
                                <div class="input-group w-100 p-2 position-relative hr-search hr-search--compact" id="adminHeaderUserSearchMobile">
                                    <input type="text" class="form-control hr-search__input" id="adminHeaderUserSearchInputMobile" placeholder="بحث بالاسم أو البريد أو الهاتف..." autocomplete="off">
                                    <div class="btn btn-primary d-inline-flex align-items-center" id="adminHeaderUserSearchBtnMobile">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="hr-search__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                                    </div>
                                    <div id="adminHeaderUserSearchResultsMobile" class="admin-header-user-search-results admin-header-user-search-results--mobile" role="listbox" hidden></div>
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
                        <a href="javascript:void(0);" class="header-link dropdown-toggle" data-bs-auto-close="outside" data-bs-toggle="dropdown" id="adminMessagesDropdown">
                            <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon hr-hicon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                            <span class="badge bg-danger rounded-pill header-icon-badge" id="admin-message-badge" style="display: none;">0</span>
                        </a>
                        <!-- End::header-link|dropdown-toggle -->
                        <!-- Start::main-header-dropdown -->
                        <div class="main-header-dropdown dropdown-menu dropdown-menu-end main-header-message" data-popper-placement="none">
                            <div class="menu-header-content bg-primary text-fixed-white">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="mb-0 fs-15 fw-semibold text-fixed-white">الرسائل</h6>
                                </div>
                                <p class="dropdown-title-text subtext mb-0 text-fixed-white op-6 pb-0 fs-12" id="admin-message-subtitle">لديك 0 رسائل خلال آخر 7 أيام</p>
                            </div>
                            <div><hr class="dropdown-divider"></div>
                            <ul class="list-unstyled mb-0" id="header-messages-scroll" style="max-height: 350px; overflow-y: auto;">
                                <li class="dropdown-item text-center py-3">
                                    <p class="text-muted mb-0">لا توجد رسائل بعد</p>
                                </li>
                            </ul>
                            <div class="text-center dropdown-footer">
                                <a href="{{ route('admin.messages.index') }}" class="text-primary fs-13">عرض الكل</a>
                            </div>
                        </div>
                        <!-- End::main-header-dropdown -->
                    </div>
                    <!-- End::header-element -->

                    @push('scripts')
                    <script>
                    (function () {
                        const latestMessagesUrl = @json(route('admin.messages.latest'));

                        function updateMessageBadge(count) {
                            const badge = document.getElementById('admin-message-badge');
                            const subtitle = document.getElementById('admin-message-subtitle');
                            if (!badge || !subtitle) return;
                            if (count > 0) {
                                badge.style.display = '';
                                badge.textContent = count > 99 ? '99+' : String(count);
                            } else {
                                badge.style.display = 'none';
                            }
                            subtitle.textContent = 'لديك ' + count + ' رسالة خلال آخر 7 أيام';
                        }

                        function renderMessages(items) {
                            const container = document.getElementById('header-messages-scroll');
                            if (!container) return;
                            if (!items.length) {
                                container.innerHTML = '<li class="dropdown-item text-center py-3"><p class="text-muted mb-0">لا توجد رسائل بعد</p></li>';
                                return;
                            }
                            container.innerHTML = items.map(function (m) {
                                const safeUrl = (m.url || '#').replace(/'/g, "\\'");
                                return '<li class="dropdown-item border-bottom" style="cursor:pointer" data-msg-url="' + safeUrl + '">' +
                                    '<div class="d-flex align-items-start p-2">' +
                                    '<div class="flex-grow-1">' +
                                    '<h6 class="mb-1 fs-13">' + (m.title || 'رسالة') + '</h6>' +
                                    '<p class="mb-1 fs-12 text-muted">' + (m.group_name || '') + '</p>' +
                                    '<small class="text-muted fs-11">' + (m.time_ago || '') + '</small>' +
                                    '</div></div></li>';
                            }).join('');

                            container.querySelectorAll('[data-msg-url]').forEach(function (el) {
                                el.addEventListener('click', function () {
                                    window.location.href = el.getAttribute('data-msg-url') || '#';
                                });
                            });
                        }

                        function loadMessages() {
                            fetch(latestMessagesUrl, { headers: { 'Accept': 'application/json' } })
                                .then(function (r) { return r.json(); })
                                .then(function (data) {
                                    if (!data.success) return;
                                    updateMessageBadge(data.recent_count || 0);
                                    renderMessages(data.messages || []);
                                })
                                .catch(function () {});
                        }

                        document.getElementById('adminMessagesDropdown')?.addEventListener('click', loadMessages);

                        if (document.readyState === 'loading') {
                            document.addEventListener('DOMContentLoaded', loadMessages);
                        } else {
                            loadMessages();
                        }
                        setInterval(loadMessages, 60000);
                    })();
                    </script>
                    @endpush

                    <!-- Start::header-element -->
                    <div class="header-element notifications-dropdown main-header-notification">
                        <!-- Start::header-link|dropdown-toggle -->
                        <a href="javascript:void(0);" class="header-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" id="adminNotificationDropdown" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon hr-hicon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                            <span class="badge bg-danger rounded-pill header-icon-badge" id="admin-notification-badge" style="display: none;">0</span>
                        </a>
                        <!-- End::header-link|dropdown-toggle -->
                        <!-- Start::main-header-dropdown -->
                        <div class="main-header-dropdown dropdown-menu dropdown-menu-end main-header-message" data-popper-placement="none">
                            <div class="menu-header-content bg-primary text-fixed-white">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="mb-0 fs-15 fw-semibold text-fixed-white">الإشعارات</h6>
                                    <button type="button" class="badge rounded-pill bg-warning pt-1 text-fixed-black border-0" id="admin-mark-all-read">تحديد الكل كمقروء</button>
                                </div>
                                <p class="dropdown-title-text subtext mb-0 text-fixed-white op-6 pb-0 fs-12" id="admin-notification-subtitle">لديك 0 إشعارات جديدة</p>
                            </div>
                            <div><hr class="dropdown-divider"></div>
                            <ul class="list-unstyled mb-0" id="header-notification-scroll" style="max-height: 350px; overflow-y: auto;">
                                <li class="dropdown-item text-center py-3">
                                    <p class="text-muted mb-0">لا توجد إشعارات جديدة</p>
                                </li>
                            </ul>
                            <div class="text-center dropdown-footer">
                                <a href="{{ route('admin.user-devices.index', ['status' => 'pending_trust']) }}" class="text-primary fs-13">أجهزة بانتظار الموافقة</a>
                            </div>
                        </div>
                        <!-- End::main-header-dropdown -->
                    </div>
                    <!-- End::header-element -->

                    @push('scripts')
                    <script>
                    (function () {
                        const notificationsUrl = @json(route('admin.header-notifications.index'));
                        const markAllUrl = @json(route('admin.header-notifications.mark-all-read'));
                        const markReadUrlTemplate = @json(url('/admin/header-notifications/__ID__/mark-read'));
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                        function updateBadge(count) {
                            const badge = document.getElementById('admin-notification-badge');
                            const subtitle = document.getElementById('admin-notification-subtitle');
                            if (!badge || !subtitle) return;
                            if (count > 0) {
                                badge.style.display = '';
                                badge.textContent = count > 99 ? '99+' : String(count);
                            } else {
                                badge.style.display = 'none';
                            }
                            subtitle.textContent = 'لديك ' + count + ' إشعارات جديدة';
                        }

                        function renderNotifications(items) {
                            const container = document.getElementById('header-notification-scroll');
                            if (!container) return;
                            if (!items.length) {
                                container.innerHTML = '<li class="dropdown-item text-center py-3"><p class="text-muted mb-0">لا توجد إشعارات جديدة</p></li>';
                                return;
                            }
                            container.innerHTML = items.map(function (n) {
                                const unread = n.is_read ? '' : 'fw-semibold';
                                const safeUrl = (n.action_url || '#').replace(/'/g, "\\'");
                                return '<li class="dropdown-item border-bottom ' + unread + '" style="cursor:pointer" data-notif-id="' + n.id + '" data-notif-url="' + safeUrl + '">' +
                                    '<div class="d-flex align-items-start p-2">' +
                                    '<div class="flex-grow-1">' +
                                    '<h6 class="mb-1 fs-13">' + (n.title || 'إشعار') + '</h6>' +
                                    '<p class="mb-1 fs-12 text-muted">' + (n.message || '') + '</p>' +
                                    '<small class="text-muted fs-11">' + (n.time_ago || '') + '</small>' +
                                    '</div></div></li>';
                            }).join('');

                            container.querySelectorAll('[data-notif-id]').forEach(function (el) {
                                el.addEventListener('click', function () {
                                    const id = el.getAttribute('data-notif-id');
                                    const url = el.getAttribute('data-notif-url') || '#';
                                    fetch(markReadUrlTemplate.replace('__ID__', id), {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': csrfToken,
                                            'Accept': 'application/json',
                                        },
                                    }).finally(function () {
                                        window.location.href = url;
                                    });
                                });
                            });
                        }

                        function loadNotifications() {
                            fetch(notificationsUrl, { headers: { 'Accept': 'application/json' } })
                                .then(function (r) { return r.json(); })
                                .then(function (data) {
                                    if (!data.success) return;
                                    updateBadge(data.unread_count || 0);
                                    renderNotifications(data.notifications || []);
                                })
                                .catch(function () {});
                        }

                        document.getElementById('adminNotificationDropdown')?.addEventListener('click', loadNotifications);
                        document.getElementById('admin-mark-all-read')?.addEventListener('click', function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            fetch(markAllUrl, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json',
                                },
                            }).then(function () { loadNotifications(); });
                        });

                        if (document.readyState === 'loading') {
                            document.addEventListener('DOMContentLoaded', loadNotifications);
                        } else {
                            loadNotifications();
                        }
                        setInterval(loadNotifications, 60000);
                    })();
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
                            @if(Auth::user()->photo)
                                <img src="{{ asset('storage/' . Auth::user()->photo) }}" alt="صورة المستخدم" width="37" height="37" class="rounded-circle">
                            @else
                                <img src="{{ asset('assets/images/faces/default-avatar.jpg') }}" alt="صورة المستخدم" width="37" height="37" class="rounded-circle">
                            @endif
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
                                <a class="dropdown-item d-flex" href="{{ route('profile.edit') }}">
                                    <i class="bx bx-user-circle fs-18 me-2 op-7"></i>الملف الشخصي
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex border-block-end" href="{{ route('users.show', Auth::user()->id) }}">
                                    <i class="bx bx-cog fs-18 me-2 op-7"></i>عرض الملف الشخصي
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

        @auth
        <style>
            .admin-header-user-search-results {
                position: absolute;
                top: calc(100% + 6px);
                inset-inline: 0;
                z-index: 1080;
                max-height: 360px;
                overflow-y: auto;
                background: var(--custom-white, #fff);
                border: 1px solid rgba(0, 0, 0, .08);
                border-radius: 12px;
                box-shadow: 0 12px 28px rgba(15, 23, 42, .12);
                text-align: start;
            }
            .admin-header-user-search-results--mobile {
                position: absolute;
                top: calc(100% - 2px);
                inset-inline: 8px;
                width: auto;
            }
            .admin-header-user-search-item {
                display: flex;
                align-items: center;
                gap: .75rem;
                padding: .7rem .9rem;
                color: inherit;
                text-decoration: none;
                border-bottom: 1px solid rgba(0, 0, 0, .05);
                transition: background .15s ease;
            }
            .admin-header-user-search-item:last-child { border-bottom: 0; }
            .admin-header-user-search-item:hover,
            .admin-header-user-search-item:focus {
                background: rgba(99, 102, 241, .08);
                color: inherit;
            }
            .admin-header-user-search-avatar {
                width: 38px;
                height: 38px;
                border-radius: 50%;
                object-fit: cover;
                flex-shrink: 0;
                background: #eef2ff;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                color: #4f46e5;
            }
            .admin-header-user-search-meta { min-width: 0; flex: 1; }
            .admin-header-user-search-name {
                font-weight: 600;
                font-size: .92rem;
                margin: 0;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .admin-header-user-search-sub {
                margin: 0;
                font-size: .78rem;
                color: #6b7280;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .admin-header-user-search-empty,
            .admin-header-user-search-loading {
                padding: 1rem;
                text-align: center;
                color: #6b7280;
                font-size: .85rem;
            }
            [data-theme-mode="dark"] .admin-header-user-search-results {
                background: #1f2937;
                border-color: rgba(255, 255, 255, .08);
            }
            [data-theme-mode="dark"] .admin-header-user-search-sub,
            [data-theme-mode="dark"] .admin-header-user-search-empty,
            [data-theme-mode="dark"] .admin-header-user-search-loading {
                color: #9ca3af;
            }
        </style>
        <script>
        (function () {
            const searchUrl = @json(route('admin.users.quick-search'));
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function renderResults(container, users, query) {
                if (!container) return;
                if (!users.length) {
                    container.innerHTML = '<div class="admin-header-user-search-empty">لا توجد نتائج لـ «' + escapeHtml(query) + '»</div>';
                    container.hidden = false;
                    return;
                }

                container.innerHTML = users.map(function (user) {
                    const initial = (user.name || '?').trim().charAt(0);
                    const avatar = user.avatar_url
                        ? '<img class="admin-header-user-search-avatar" src="' + escapeHtml(user.avatar_url) + '" alt="">'
                        : '<span class="admin-header-user-search-avatar">' + escapeHtml(initial) + '</span>';
                    const phoneLine = user.phone ? ' · ' + escapeHtml(user.phone) : '';
                    const studentLine = user.student_id ? '<div class="admin-header-user-search-sub">' + escapeHtml(user.student_id) + '</div>' : '';
                    return '<a class="admin-header-user-search-item" role="option" href="' + escapeHtml(user.profile_url) + '">'
                        + avatar
                        + '<div class="admin-header-user-search-meta">'
                        + '<p class="admin-header-user-search-name">' + escapeHtml(user.name) + '</p>'
                        + '<p class="admin-header-user-search-sub">' + escapeHtml(user.email || '') + phoneLine + '</p>'
                        + studentLine
                        + '</div></a>';
                }).join('');
                container.hidden = false;
            }

            function bindSearch(input, results, button) {
                if (!input || !results) return;
                let timer = null;
                let controller = null;

                const hide = () => { results.hidden = true; input.setAttribute('aria-expanded', 'false'); };
                const showLoading = () => {
                    results.innerHTML = '<div class="admin-header-user-search-loading">جاري البحث…</div>';
                    results.hidden = false;
                    input.setAttribute('aria-expanded', 'true');
                };

                const runSearch = () => {
                    const q = (input.value || '').trim();
                    if (q.length < 2) {
                        hide();
                        return;
                    }
                    showLoading();
                    if (controller) controller.abort();
                    controller = new AbortController();

                    fetch(searchUrl + '?q=' + encodeURIComponent(q), {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrf
                        },
                        signal: controller.signal
                    })
                        .then(function (r) { return r.json(); })
                        .then(function (json) {
                            renderResults(results, (json && json.data) ? json.data : [], q);
                            input.setAttribute('aria-expanded', 'true');
                        })
                        .catch(function (err) {
                            if (err && err.name === 'AbortError') return;
                            results.innerHTML = '<div class="admin-header-user-search-empty">تعذر تنفيذ البحث</div>';
                            results.hidden = false;
                        });
                };

                input.addEventListener('input', function () {
                    clearTimeout(timer);
                    timer = setTimeout(runSearch, 280);
                });
                input.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        clearTimeout(timer);
                        runSearch();
                    }
                    if (e.key === 'Escape') hide();
                });
                if (button) button.addEventListener('click', function (e) {
                    e.preventDefault();
                    clearTimeout(timer);
                    runSearch();
                });
                document.addEventListener('click', function (e) {
                    if (!input.closest('.main-header-center')?.contains(e.target)
                        && !input.closest('#adminHeaderUserSearchMobile')?.contains(e.target)) {
                        hide();
                    }
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                bindSearch(
                    document.getElementById('adminHeaderUserSearchInput'),
                    document.getElementById('adminHeaderUserSearchResults'),
                    document.getElementById('adminHeaderUserSearchBtn')
                );
                bindSearch(
                    document.getElementById('adminHeaderUserSearchInputMobile'),
                    document.getElementById('adminHeaderUserSearchResultsMobile'),
                    document.getElementById('adminHeaderUserSearchBtnMobile')
                );
            });
        })();
        </script>
        @endauth
