        <!-- Start::app-sidebar -->
        <aside class="app-sidebar sticky" id="sidebar">

            <!-- Start::main-sidebar-header -->
            <div class="main-sidebar-header">
                <a href="{{ route('student.dashboard') }}" class="header-logo">
                     أكاديمية كلاودسوفت
                </a>
            </div>
            <!-- End::main-sidebar-header -->

            <!-- Start::main-sidebar -->
            <div class="main-sidebar" id="sidebar-scroll">

                <!-- Start::nav -->
                <nav class="main-menu-container nav nav-pills flex-column sub-open">
                    <div class="slide-left" id="slide-left">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24"> <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path> </svg>
                    </div>
                    <ul class="main-menu">
                        <!-- Start::slide - الصفحة الرئيسية -->
                        <li class="slide">
                            <a href="{{ route('student.dashboard') }}" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" ><path d="M0 0h24v24H0V0z" fill="none"/><path d="M5 5h4v6H5zm10 8h4v6h-4zM5 17h4v2H5zM15 5h4v2h-4z" opacity=".3"/><path d="M3 13h8V3H3v10zm2-8h4v6H5V5zm8 16h8V11h-8v10zm2-8h4v6h-4v-6zM13 3v6h8V3h-8zm6 4h-4V5h4v2zM3 21h8v-6H3v6zm2-4h4v2H5v-2z"/></svg>
                                <span class="side-menu__label">الصفحة الرئيسية</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide - ملفي الشخصي -->
                        <li class="slide">
                            <a href="{{ route('student.profile.index') }}" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <span class="side-menu__label">ملفي الشخصي</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide - إعدادات الإشعارات -->
                        <li class="slide">
                            <a href="{{ route('student.settings.notifications') }}" class="side-menu__item">
                                <i class="ri-settings-3-line side-menu__icon"></i>
                                <span class="side-menu__label">إعدادات الإشعارات</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide - المعسكرات التدريبية -->
                        <li class="slide">
                            <a href="{{ route('student.training-camps.index') }}" class="side-menu__item">
                                <i class="fas fa-campground side-menu__icon"></i>
                                <span class="side-menu__label">المعسكرات المتاحة</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide - تسجيلاتي -->
                        <li class="slide">
                            <a href="{{ route('student.training-camps.my-enrollments') }}" class="side-menu__item">
                                <i class="fas fa-clipboard-list side-menu__icon"></i>
                                <span class="side-menu__label">معسكراتي</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide - تصفح الكورسات -->
                        <li class="slide">
                            <a href="{{ route('student.courses.index') }}" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                                </svg>
                                <span class="side-menu__label">تصفح الكورسات</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide - كورساتي -->
                        <li class="slide">
                            <a href="{{ route('student.courses.my-courses') }}" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                </svg>
                                <span class="side-menu__label">كورساتي</span>
                                <span class="badge bg-primary-transparent ms-auto">جديد</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide - تقدمي -->
                        <li class="slide">
                            <a href="{{ route('student.progress.overview') }}" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="20" x2="18" y2="10"></line>
                                    <line x1="12" y1="20" x2="12" y2="4"></line>
                                    <line x1="6" y1="20" x2="6" y2="14"></line>
                                </svg>
                                <span class="side-menu__label">تقدمي في الكورسات</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide - شهاداتي -->
                        <li class="slide">
                            <a href="{{ route('student.certificates.index') }}" class="side-menu__item">
                                <i class="fas fa-certificate side-menu__icon"></i>
                                <span class="side-menu__label">شهاداتي</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide - فواتيري -->
                        <li class="slide">
                            <a href="{{ route('student.invoices.index') }}" class="side-menu__item">
                                <i class="fas fa-file-invoice-dollar side-menu__icon"></i>
                                <span class="side-menu__label">فواتيري</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide - مدفوعاتي -->
                        <li class="slide">
                            <a href="{{ route('student.payments.index') }}" class="side-menu__item">
                                <i class="fas fa-money-bill-wave side-menu__icon"></i>
                                <span class="side-menu__label">مدفوعاتي</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide - لوحة التلعيب -->
                        <li class="slide">
                            <a href="{{ route('gamification.dashboard') }}" class="side-menu__item">
                                <i class="fas fa-gamepad side-menu__icon"></i>
                                <span class="side-menu__label">لوحة التلعيب</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide - شاراتي -->
                        <li class="slide">
                            <a href="{{ route('gamification.badges.index') }}" class="side-menu__item">
                                <i class="fas fa-medal side-menu__icon"></i>
                                <span class="side-menu__label">شاراتي</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide - إنجازاتي -->
                        <li class="slide">
                            <a href="{{ route('gamification.achievements.index') }}" class="side-menu__item">
                                <i class="fas fa-trophy side-menu__icon"></i>
                                <span class="side-menu__label">إنجازاتي</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide - لوحة المتصدرين -->
                        <li class="slide">
                            <a href="{{ route('gamification.leaderboards.index') }}" class="side-menu__item">
                                <i class="fas fa-crown side-menu__icon"></i>
                                <span class="side-menu__label">لوحة المتصدرين</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide - التحديات -->
                        <li class="slide">
                            <a href="{{ route('gamification.challenges.index') }}" class="side-menu__item">
                                <i class="fas fa-bullseye side-menu__icon"></i>
                                <span class="side-menu__label">التحديات</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide - المتجر -->
                        <li class="slide">
                            <a href="{{ route('gamification.shop.index') }}" class="side-menu__item">
                                <i class="fas fa-store side-menu__icon"></i>
                                <span class="side-menu__label">المتجر</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide - النقاط -->
                        <li class="slide {{ request()->routeIs('gamification.points.*') ? 'active' : '' }}">
                            <a href="{{ route('gamification.points.index') }}" class="side-menu__item {{ request()->routeIs('gamification.points.*') ? 'active' : '' }}">
                                <i class="fas fa-star side-menu__icon"></i>
                                <span class="side-menu__label">النقاط</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide - السلسلة اليومية -->
                        <li class="slide {{ request()->routeIs('gamification.streak.*') ? 'active' : '' }}">
                            <a href="{{ route('gamification.streak.index') }}" class="side-menu__item {{ request()->routeIs('gamification.streak.*') ? 'active' : '' }}">
                                <i class="fas fa-fire side-menu__icon"></i>
                                <span class="side-menu__label">السلسلة اليومية</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide - الإشعارات -->
                        <li class="slide">
                            <a href="{{ route('gamification.notifications.index') }}" class="side-menu__item">
                                <i class="fas fa-bell side-menu__icon"></i>
                                <span class="side-menu__label">الإشعارات</span>
                                @php
                                    $unreadCount = Auth::user()->gamificationNotifications()->unread()->count();
                                @endphp
                                @if($unreadCount > 0)
                                    <span class="badge bg-danger ms-auto">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                                @endif
                            </a>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide__category -->
                        <li class="slide__category"><span class="category-name">الملاحظات والتذكيرات</span></li>
                        <!-- End::slide__category -->

                        <!-- Start::slide - التقويم -->
                        <li class="slide {{ request()->routeIs('student.calendar.*') ? 'active' : '' }}">
                            <a href="{{ route('student.calendar.index') }}" class="side-menu__item {{ request()->routeIs('student.calendar.*') ? 'active' : '' }}">
                                <i class="ri-calendar-line side-menu__icon"></i>
                                <span class="side-menu__label">التقويم والمواعيد</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide - جدول الأعمال -->
                        <li class="slide {{ request()->routeIs('student.works.*') ? 'active' : '' }}">
                            <a href="{{ route('student.works.index') }}" class="side-menu__item {{ request()->routeIs('student.works.*') ? 'active' : '' }}">
                                <i class="ri-folder-user-line side-menu__icon"></i>
                                <span class="side-menu__label">جدول أعمالي</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide - المفكرة الشخصية -->
                        <li class="slide {{ request()->routeIs('student.notes.*') ? 'active' : '' }}">
                            <a href="{{ route('student.notes.index') }}" class="side-menu__item {{ request()->routeIs('student.notes.*') ? 'active' : '' }}">
                                <i class="ri-sticky-note-line side-menu__icon"></i>
                                <span class="side-menu__label">المفكرة الشخصية</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide - ملاحظات الكورسات -->
                        <li class="slide {{ request()->routeIs('student.course-notes.*') ? 'active' : '' }}">
                            <a href="{{ route('student.course-notes.index') }}" class="side-menu__item {{ request()->routeIs('student.course-notes.*') ? 'active' : '' }}">
                                <i class="ri-file-list-3-line side-menu__icon"></i>
                                <span class="side-menu__label">ملاحظات الكورسات</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide - التذكيرات -->
                        <li class="slide {{ request()->routeIs('student.reminders.*') ? 'active' : '' }}">
                            <a href="{{ route('student.reminders.index') }}" class="side-menu__item {{ request()->routeIs('student.reminders.*') ? 'active' : '' }}">
                                <i class="ri-alarm-line side-menu__icon"></i>
                                <span class="side-menu__label">التذكيرات</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                    </ul>
                    <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24"> <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path> </svg></div>
                </nav>
                <!-- End::nav -->

            </div>
            <!-- End::main-sidebar -->

        </aside>
        <!-- End::app-sidebar -->

        @push('scripts')
        <script>
        // Auto-activate sidebar links based on current route
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const sidebarLinks = document.querySelectorAll('.side-menu__item');

            // Remove any existing active classes first
            document.querySelectorAll('.side-menu__item.active').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.slide.active').forEach(el => el.classList.remove('active'));

            let bestMatch = null;
            let longestMatch = 0;

            // Find the best matching link (longest path match)
            sidebarLinks.forEach(function(link) {
                const linkHref = link.getAttribute('href');

                if (!linkHref || linkHref === '#' || linkHref === 'javascript:void(0);') {
                    return;
                }

                try {
                    const linkUrl = new URL(linkHref, window.location.origin);
                    const linkPath = linkUrl.pathname;

                    // Check for exact match first
                    if (linkPath === currentPath) {
                        if (linkPath.length > longestMatch) {
                            longestMatch = linkPath.length;
                            bestMatch = link;
                        }
                    }
                    // Then check if current path starts with link path
                    else if (currentPath.startsWith(linkPath) && linkPath !== '/') {
                        if (linkPath.length > longestMatch) {
                            longestMatch = linkPath.length;
                            bestMatch = link;
                        }
                    }
                } catch (e) {
                    // Invalid URL, skip
                }
            });

            // Activate the best matching link
            if (bestMatch) {
                bestMatch.classList.add('active');

                // Add active class to parent slide
                const parentSlide = bestMatch.closest('.slide');
                if (parentSlide) {
                    parentSlide.classList.add('active');
                }

                // If link is inside a dropdown, open it and activate parent
                const parentHasChild = bestMatch.closest('.slide.has-sub');
                if (parentHasChild) {
                    parentHasChild.classList.add('open');

                    const slideMenu = parentHasChild.querySelector('.slide-menu');
                    if (slideMenu) {
                        slideMenu.style.display = 'block';
                    }

                    const parentLink = parentHasChild.querySelector(':scope > .side-menu__item');
                    if (parentLink && parentLink !== bestMatch) {
                        parentLink.classList.add('active');
                    }
                }
            }
        });
        </script>
        @endpush

        @push('styles')
        <style>
        /* Active link styling */
        .side-menu__item.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transform: translateX(-5px);
            transition: all 0.3s ease;
        }

        .side-menu__item.active .side-menu__icon {
            color: white !important;
            fill: white !important;
        }

        .side-menu__item.active .side-menu__label {
            color: white !important;
            font-weight: 600;
        }

        .side-menu__item.active .badge {
            background: white !important;
            color: #667eea !important;
        }

        /* Hover effect for non-active links */
        .side-menu__item:not(.active):hover {
            background-color: #f8f9fa;
            border-radius: 8px;
            transform: translateX(-3px);
            transition: all 0.3s ease;
        }

        /* Parent slide active state */
        .slide.active > .side-menu__item {
            border-right: 3px solid #667eea;
        }

        /* Dropdown open state */
        .slide.has-sub.open > .side-menu__item {
            background-color: #f8f9fa;
            border-radius: 8px 8px 0 0;
        }

        /* Smooth transitions */
        .side-menu__item {
            transition: all 0.3s ease;
        }

        .slide-menu {
            transition: all 0.3s ease;
        }
        </style>
        @endpush
