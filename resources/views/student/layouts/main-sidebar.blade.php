        <!-- Start::app-sidebar -->
        <aside class="app-sidebar sticky" id="sidebar">

            <!-- Start::main-sidebar-header -->
            <div class="main-sidebar-header">
                @php
                    $sidebarLogoUrl = asset('frontend2/assets/images/logo.png');
                @endphp
                <a href="{{ route('student.dashboard') }}" class="header-logo student-sidebar-brand">
                    <img src="{{ $sidebarLogoUrl }}" alt="أكاديمية كلاودسوفت" class="student-sidebar-brand__logo student-sidebar-brand__logo--expanded">
                    <img src="{{ $sidebarLogoUrl }}" alt="أكاديمية كلاودسوفت" class="student-sidebar-brand__logo student-sidebar-brand__logo--collapsed">
                    <span class="student-sidebar-brand__title">أكاديمية كلاودسوفت</span>
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
                        <!-- Start::slide - الواجهة الأمامية -->
                        <li class="slide">
                            <a href="{{ route('frontend.home') }}" target="_blank" rel="noopener noreferrer" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                                <span class="side-menu__label">الواجهة الأمامية</span>
                                <i class="fe fe-external-link side-menu__angle" style="font-size: 14px; margin-right: auto;"></i>
                            </a>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide - الصفحة الرئيسية -->
                        <li class="slide">
                            <a href="{{ route('student.dashboard') }}" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" ><path d="M0 0h24v24H0V0z" fill="none"/><path d="M5 5h4v6H5zm10 8h4v6h-4zM5 17h4v2H5zM15 5h4v2h-4z" opacity=".3"/><path d="M3 13h8V3H3v10zm2-8h4v6H5V5zm8 16h8V11h-8v10zm2-8h4v6h-4v-6zM13 3v6h8V3h-8zm6 4h-4V5h4v2zM3 21h8v-6H3v6zm2-4h4v2H5v-2z"/></svg>
                                <span class="side-menu__label">الصفحة الرئيسية</span>
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

                        <!-- Start::slide - اختباراتي -->
                        <li class="slide {{ request()->routeIs('student.quizzes.review.*') ? 'active' : '' }}">
                            <a href="{{ route('student.quizzes.review.index') }}" class="side-menu__item {{ request()->routeIs('student.quizzes.review.*') ? 'active' : '' }}">
                                <i class="fas fa-clipboard-check side-menu__icon"></i>
                                <span class="side-menu__label">اختباراتي</span>
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

                        @php
                            $reportsMenuActive = request()->routeIs(
                                'student.study-reports.*',
                                'student.progress.ai-reports.*',
                                'student.weekly-reports.*'
                            );
                        @endphp

                        <!-- Start::slide - التقارير (قائمة منسدلة) -->
                        <li class="slide has-sub student-sidebar-submenu {{ $reportsMenuActive ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ $reportsMenuActive ? 'active' : '' }}">
                                <i class="ri-file-chart-line side-menu__icon"></i>
                                <span class="side-menu__label">التقارير</span>
                                <i class="fe fe-chevron-down side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">التقارير</a>
                                </li>
                                <li class="slide {{ request()->routeIs('student.study-reports.*', 'student.progress.ai-reports.*') ? 'active' : '' }}">
                                    <a href="{{ route('student.study-reports.index') }}" class="side-menu__item {{ request()->routeIs('student.study-reports.*', 'student.progress.ai-reports.*') ? 'active' : '' }}">
                                        <i class="ri-file-text-line student-submenu__icon"></i>
                                        <span>تقارير الدراسة</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->routeIs('student.weekly-reports.*') ? 'active' : '' }}">
                                    <a href="{{ route('student.weekly-reports.index') }}" class="side-menu__item {{ request()->routeIs('student.weekly-reports.*') ? 'active' : '' }}">
                                        <i class="ri-calendar-schedule-line student-submenu__icon"></i>
                                        <span>التقارير الأسبوعية</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide - الموارد الخارجية -->
                        <li class="slide {{ request()->routeIs('student.external-resources.*') ? 'active' : '' }}">
                            <a href="{{ route('student.external-resources.index') }}" class="side-menu__item {{ request()->routeIs('student.external-resources.*') ? 'active' : '' }}">
                                <i class="ri-links-line side-menu__icon"></i>
                                <span class="side-menu__label">الموارد الخارجية</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                        <li class="slide {{ request()->routeIs('student.gifts.*') ? 'active' : '' }}">
                            <a href="{{ route('student.gifts.index') }}" class="side-menu__item {{ request()->routeIs('student.gifts.*') ? 'active' : '' }}">
                                <i class="ri ri-gift-line side-menu__icon"></i>
                                <span class="side-menu__label">هدايا الأكاديمية</span>
                            </a>
                        </li>

                        <!-- Start::slide - شهاداتي -->
                        <li class="slide">
                            <a href="{{ route('student.certificates.index') }}" class="side-menu__item">
                                <i class="fas fa-certificate side-menu__icon"></i>
                                <span class="side-menu__label">شهاداتي</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                        @php
                            $paymentsMenuActive = request()->routeIs('student.invoices.*', 'student.payments.*');
                        @endphp

                        <!-- Start::slide - الفواتير والمدفوعات (قائمة منسدلة) -->
                        <li class="slide has-sub student-sidebar-submenu {{ $paymentsMenuActive ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ $paymentsMenuActive ? 'active' : '' }}">
                                <i class="ri-wallet-3-line side-menu__icon"></i>
                                <span class="side-menu__label">الفواتير والمدفوعات</span>
                                <i class="fe fe-chevron-down side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">الفواتير والمدفوعات</a>
                                </li>
                                <li class="slide {{ request()->routeIs('student.invoices.*') ? 'active' : '' }}">
                                    <a href="{{ route('student.invoices.index') }}" class="side-menu__item {{ request()->routeIs('student.invoices.*') ? 'active' : '' }}">
                                        <i class="ri-file-list-3-line student-submenu__icon"></i>
                                        <span>فواتيري</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->routeIs('student.payments.*') ? 'active' : '' }}">
                                    <a href="{{ route('student.payments.index') }}" class="side-menu__item {{ request()->routeIs('student.payments.*') ? 'active' : '' }}">
                                        <i class="ri-money-dollar-circle-line student-submenu__icon"></i>
                                        <span>مدفوعاتي</span>
                                    </a>
                                </li>
                            </ul>
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

                        <!-- Start::slide - ملاحظات AI -->
                        <li class="slide">
                            <a href="{{ route('student.feedback.index') }}" class="side-menu__item">
                                <i class="fas fa-robot side-menu__icon"></i>
                                <span class="side-menu__label">ملاحظات AI</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                        @php
                            $enrollmentsMenuActive = request()->routeIs('student.groups.*', 'student.training-camps.*');
                        @endphp

                        <!-- Start::slide - الانضمامات (قائمة منسدلة) -->
                        <li class="slide has-sub student-sidebar-submenu {{ $enrollmentsMenuActive ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ $enrollmentsMenuActive ? 'active' : '' }}">
                                <i class="ri-user-add-line side-menu__icon"></i>
                                <span class="side-menu__label">الانضمامات</span>
                                <i class="fe fe-chevron-down side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">الانضمامات</a>
                                </li>
                                <li class="slide {{ request()->routeIs('student.groups.*') ? 'active' : '' }}">
                                    <a href="{{ route('student.groups.index') }}" class="side-menu__item {{ request()->routeIs('student.groups.*') ? 'active' : '' }}">
                                        <i class="ri-group-line student-submenu__icon"></i>
                                        <span>المجموعات</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->routeIs('student.training-camps.*') ? 'active' : '' }}">
                                    <a href="{{ route('student.training-camps.my-enrollments') }}" class="side-menu__item {{ request()->routeIs('student.training-camps.*') ? 'active' : '' }}">
                                        <i class="ri-clipboard-line student-submenu__icon"></i>
                                        <span>معسكراتي</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <!-- End::slide -->

                        @php
                            $gamificationActive = request()->routeIs('gamification.*');
                            $gamificationUnreadCount = Auth::user()->gamificationNotifications()->unread()->count();
                        @endphp

                        <!-- Start::slide - المكافآت والإنجازات (قائمة منسدلة) -->
                        <li class="slide has-sub student-sidebar-submenu {{ $gamificationActive ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ $gamificationActive ? 'active' : '' }}">
                                <i class="ri-trophy-line side-menu__icon"></i>
                                <span class="side-menu__label">المكافآت والإنجازات</span>
                                @if($gamificationUnreadCount > 0)
                                    <span class="badge bg-danger ms-auto">{{ $gamificationUnreadCount > 99 ? '99+' : $gamificationUnreadCount }}</span>
                                @endif
                                <i class="fe fe-chevron-down side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">المكافآت والإنجازات</a>
                                </li>
                                <li class="slide {{ request()->routeIs('gamification.dashboard', 'gamification.profile', 'gamification.statistics') ? 'active' : '' }}">
                                    <a href="{{ route('gamification.dashboard') }}" class="side-menu__item {{ request()->routeIs('gamification.dashboard', 'gamification.profile', 'gamification.statistics') ? 'active' : '' }}">
                                        <i class="ri-dashboard-line student-submenu__icon"></i>
                                        <span>لوحة التلعيب</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->routeIs('gamification.badges.*') ? 'active' : '' }}">
                                    <a href="{{ route('gamification.badges.index') }}" class="side-menu__item {{ request()->routeIs('gamification.badges.*') ? 'active' : '' }}">
                                        <i class="ri-medal-line student-submenu__icon"></i>
                                        <span>شاراتي</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->routeIs('gamification.achievements.*') ? 'active' : '' }}">
                                    <a href="{{ route('gamification.achievements.index') }}" class="side-menu__item {{ request()->routeIs('gamification.achievements.*') ? 'active' : '' }}">
                                        <i class="ri-trophy-line student-submenu__icon"></i>
                                        <span>إنجازاتي</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->routeIs('gamification.leaderboards.*') ? 'active' : '' }}">
                                    <a href="{{ route('gamification.leaderboards.index') }}" class="side-menu__item {{ request()->routeIs('gamification.leaderboards.*') ? 'active' : '' }}">
                                        <i class="ri-vip-crown-line student-submenu__icon"></i>
                                        <span>لوحة المتصدرين</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->routeIs('gamification.challenges.*') ? 'active' : '' }}">
                                    <a href="{{ route('gamification.challenges.index') }}" class="side-menu__item {{ request()->routeIs('gamification.challenges.*') ? 'active' : '' }}">
                                        <i class="ri-focus-3-line student-submenu__icon"></i>
                                        <span>التحديات</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->routeIs('gamification.shop.*') ? 'active' : '' }}">
                                    <a href="{{ route('gamification.shop.index') }}" class="side-menu__item {{ request()->routeIs('gamification.shop.*') ? 'active' : '' }}">
                                        <i class="ri-store-2-line student-submenu__icon"></i>
                                        <span>المتجر</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->routeIs('gamification.points.*') ? 'active' : '' }}">
                                    <a href="{{ route('gamification.points.index') }}" class="side-menu__item {{ request()->routeIs('gamification.points.*') ? 'active' : '' }}">
                                        <i class="ri-star-line student-submenu__icon"></i>
                                        <span>النقاط</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->routeIs('gamification.streak.*') ? 'active' : '' }}">
                                    <a href="{{ route('gamification.streak.index') }}" class="side-menu__item {{ request()->routeIs('gamification.streak.*') ? 'active' : '' }}">
                                        <i class="ri-fire-line student-submenu__icon"></i>
                                        <span>السلسلة اليومية</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->routeIs('gamification.notifications.*') ? 'active' : '' }}">
                                    <a href="{{ route('gamification.notifications.index') }}" class="side-menu__item {{ request()->routeIs('gamification.notifications.*') ? 'active' : '' }}">
                                        <i class="ri-notification-3-line student-submenu__icon"></i>
                                        <span>الإشعارات</span>
                                        @if($gamificationUnreadCount > 0)
                                            <span class="badge bg-danger ms-auto">{{ $gamificationUnreadCount > 99 ? '99+' : $gamificationUnreadCount }}</span>
                                        @endif
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <!-- End::slide -->

                        @php
                            $notesRemindersActive = request()->routeIs(
                                'student.calendar.*',
                                'student.works.*',
                                'student.notes.*',
                                'student.course-notes.*',
                                'student.reminders.*'
                            );
                        @endphp

                        <!-- Start::slide - الملاحظات والتذكيرات (قائمة منسدلة) -->
                        <li class="slide has-sub student-sidebar-submenu {{ $notesRemindersActive ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ $notesRemindersActive ? 'active' : '' }}">
                                <i class="ri-booklet-line side-menu__icon"></i>
                                <span class="side-menu__label">الملاحظات والتذكيرات</span>
                                <i class="fe fe-chevron-down side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">الملاحظات والتذكيرات</a>
                                </li>
                                <li class="slide {{ request()->routeIs('student.calendar.*') ? 'active' : '' }}">
                                    <a href="{{ route('student.calendar.index') }}" class="side-menu__item {{ request()->routeIs('student.calendar.*') ? 'active' : '' }}">
                                        <i class="ri-calendar-line student-submenu__icon"></i>
                                        <span>التقويم والمواعيد</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->routeIs('student.works.*') ? 'active' : '' }}">
                                    <a href="{{ route('student.works.index') }}" class="side-menu__item {{ request()->routeIs('student.works.*') ? 'active' : '' }}">
                                        <i class="ri-folder-user-line student-submenu__icon"></i>
                                        <span>جدول أعمالي</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->routeIs('student.notes.*') ? 'active' : '' }}">
                                    <a href="{{ route('student.notes.index') }}" class="side-menu__item {{ request()->routeIs('student.notes.*') ? 'active' : '' }}">
                                        <i class="ri-sticky-note-line student-submenu__icon"></i>
                                        <span>المفكرة الشخصية</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->routeIs('student.course-notes.*') ? 'active' : '' }}">
                                    <a href="{{ route('student.course-notes.index') }}" class="side-menu__item {{ request()->routeIs('student.course-notes.*') ? 'active' : '' }}">
                                        <i class="ri-file-list-3-line student-submenu__icon"></i>
                                        <span>ملاحظات الكورسات</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->routeIs('student.reminders.*') ? 'active' : '' }}">
                                    <a href="{{ route('student.reminders.index') }}" class="side-menu__item {{ request()->routeIs('student.reminders.*') ? 'active' : '' }}">
                                        <i class="ri-alarm-line student-submenu__icon"></i>
                                        <span>التذكيرات</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide - تقييمي للمنصة -->
                        <li class="slide {{ request()->routeIs('student.platform-review.*') ? 'active' : '' }}">
                            <a href="{{ route('student.platform-review.index') }}" class="side-menu__item {{ request()->routeIs('student.platform-review.*') ? 'active' : '' }}">
                                <i class="ri-star-line side-menu__icon"></i>
                                <span class="side-menu__label">تقييمي للمنصة</span>
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

            // تقارير الدراسة: الصفحات تحت /study-reports أو progress/.../ai-reports
            if (/\/student\/study-reports(\/|$)/.test(currentPath) || /\/student\/progress\/.*\/ai-reports/.test(currentPath) || /\/student\/progress\/ai-reports\//.test(currentPath)) {
                sidebarLinks.forEach(function(link) {
                    const href = link.getAttribute('href');
                    if (!href || href === '#' || href === 'javascript:void(0);') {
                        return;
                    }
                    try {
                        const linkUrl = new URL(link.href, window.location.origin);
                        if (/\/student\/study-reports\/?$/.test(linkUrl.pathname)) {
                            bestMatch = link;
                        }
                    } catch (e) {}
                });
            }

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

