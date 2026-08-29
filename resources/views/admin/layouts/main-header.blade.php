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
                        <a aria-label="Hide Sidebar" class="sidemenu-toggle header-link animated-arrow hor-toggle horizontal-navtoggle" data-bs-toggle="sidebar" href="javascript:void(0);">
                            <i class="header-icon fe fe-align-left"></i>
                        </a>
                        <div class="main-header-center d-none d-lg-block position-relative" id="adminHeaderUserSearch">
                            <input class="form-control" id="adminHeaderUserSearchInput" placeholder="بحث بالاسم أو البريد أو الهاتف..." type="search" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="adminHeaderUserSearchResults">
                            <button class="btn" type="button" id="adminHeaderUserSearchBtn" aria-label="بحث"><i class="fa fa-search d-none d-md-block"></i></button>
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
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960"  class="header-link-icon"><path d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/></svg>
                        </a>
                        <!-- End::header-link|dropdown-toggle -->
                        <ul class="main-header-dropdown dropdown-menu dropdown-menu-end Search-element-dropdown" data-popper-placement="none">
                            <li>
                                <div class="input-group w-100 p-2 position-relative" id="adminHeaderUserSearchMobile">
                                    <input type="text" class="form-control" id="adminHeaderUserSearchInputMobile" placeholder="بحث بالاسم أو البريد أو الهاتف..." autocomplete="off">
                                    <div class="btn btn-primary" id="adminHeaderUserSearchBtnMobile">
                                        <i class="fa fa-search" aria-hidden="true"></i>
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
                                <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon" height="24" viewBox="0 -960 960 960" width="24"><path d="M480-120q-150 0-255-105T120-480q0-150 105-255t255-105q14 0 27.5 1t26.5 3q-41 29-65.5 75.5T444-660q0 90 63 153t153 63q55 0 101-24.5t75-65.5q2 13 3 26.5t1 27.5q0 150-105 255T480-120Zm0-80q88 0 158-48.5T740-375q-20 5-40 8t-40 3q-123 0-209.5-86.5T364-660q0-20 3-40t8-40q-78 32-126.5 102T200-480q0 116 82 198t198 82Zm-10-270Z"/></svg>
                                <!-- End::header-link-icon -->
                            </span>
                            <span class="dark-layout">
                                <!-- Start::header-link-icon -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon" fill="currentColor" height="24" viewBox="0 -960 960 960" width="24"><path d="M480-360q50 0 85-35t35-85q0-50-35-85t-85-35q-50 0-85 35t-35 85q0 50 35 85t85 35Zm0 80q-83 0-141.5-58.5T280-480q0-83 58.5-141.5T480-680q83 0 141.5 58.5T680-480q0 83-58.5 141.5T480-280ZM200-440H40v-80h160v80Zm720 0H760v-80h160v80Zm0-320v-160h80v160h-80Zm0 720v-160h80v160h-80ZM256-650l-101-97 57-59 96 100-52 56Zm492 496-97-101 53-55 101 97-57 59Zm-98-550 97-101 59 57-100 96-56-52ZM154-212l101-97 55 53-97 101-59-57Zm326-268Z"/></svg>
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
                            <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon" height="24px" viewBox="0 0 24 24" width="24px" fill="currentColor"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M22 6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6zm-2 0l-8 5-8-5h16zm0 12H4V8l8 5 8-5v10z"/></svg>
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
                            <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon"  height="24px" viewBox="0 0 24 24" width="24px" fill="currentColor"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2zm-2 1H8v-6c0-2.48 1.51-4.5 4-4.5s4 2.02 4 4.5v6z"/></svg>
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
                            <svg xmlns="http://www.w3.org/2000/svg" class="full-screen-open full-screen-icon header-link-icon" height="24px" viewBox="0 0 24 24" width="24px" fill="currentColor"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="full-screen-close full-screen-icon header-link-icon d-none" fill="currentColor" height="24" viewBox="0 -960 960 960" width="24"><path d="M320-200v-120H200v-80h200v200h-80Zm240 0v-200h200v80H640v120h-80ZM200-560v-80h120v-120h80v200H200Zm360 0v-200h80v120h120v80H560Z"/></svg>
                        </a>
                        <!-- End::header-link -->
                    </div>
                    <!-- End::header-element -->

                    <!-- Start::header-element -->
                    <div class="header-element header-sidebar">
                        <!-- Start::header-link-->
                        <a href="javascript:void(0);" class="header-link" data-bs-toggle="offcanvas" data-bs-target="#header-sidebar">
                            <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" ><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
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
                            <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon" width="24" height="24" viewBox="0 0 24 24"><path d="M12 16c2.206 0 4-1.794 4-4s-1.794-4-4-4-4 1.794-4 4 1.794 4 4 4zm0-6c1.084 0 2 .916 2 2s-.916 2-2 2-2-.916-2-2 .916-2 2-2z"></path><path d="m2.845 16.136 1 1.73c.531.917 1.809 1.261 2.73.73l.529-.306A8.1 8.1 0 0 0 9 19.402V20c0 1.103.897 2 2 2h2c1.103 0 2-.897 2-2v-.598a8.132 8.132 0 0 0 1.896-1.111l.529.306c.923.53 2.198.188 2.731-.731l.999-1.729a2.001 2.001 0 0 0-.731-2.732l-.505-.292a7.718 7.718 0 0 0 0-2.224l.505-.292a2.002 2.002 0 0 0 .731-2.732l-.999-1.729c-.531-.92-1.808-1.265-2.731-.732l-.529.306A8.1 8.1 0 0 0 15 4.598V4c0-1.103-.897-2-2-2h-2c-1.103 0-2 .897-2 2v.598a8.132 8.132 0 0 0-1.896 1.111l-.529-.306c-.924-.531-2.2-.187-2.731.732l-.999 1.729a2.001 2.001 0 0 0 .731 2.732l.505.292a7.683 7.683 0 0 0 0 2.223l-.505.292a2.003 2.003 0 0 0-.731 2.733zm3.326-2.758A5.703 5.703 0 0 1 6 12c0-.462.058-.926.17-1.378a.999.999 0 0 0-.47-1.108l-1.123-.65.998-1.729 1.145.662a.997.997 0 0 0 1.188-.142 6.071 6.071 0 0 1 2.384-1.399A1 1 0 0 0 11 5.3V4h2v1.3a1 1 0 0 0 .708.956 6.083 6.083 0 0 1 2.384 1.399.999.999 0 0 0 1.188.142l1.144-.661 1 1.729-1.124.649a1 1 0 0 0-.47 1.108c.112.452.17.916.17 1.378 0 .461-.058.925-.171 1.378a1 1 0 0 0 .471 1.108l1.123.649-.998 1.729-1.145-.661a.996.996 0 0 0-1.188.142 6.071 6.071 0 0 1-2.384 1.399A1 1 0 0 0 13 18.7l.002 1.3H11v-1.3a1 1 0 0 0-.708-.956 6.083 6.083 0 0 1-2.384-1.399.992.992 0 0 0-1.188-.141l-1.144.662-1-1.729 1.124-.651a1 1 0 0 0 .471-1.108z"></path></svg>
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
