        <!-- Start::app-sidebar -->
        <aside class="app-sidebar sticky" id="sidebar">

            <!-- Start::main-sidebar-header -->
            <div class="main-sidebar-header">
                <a href="{{ route('admin.dashboard') }}" class="header-logo">
                    <img src="{{ asset('assets/images/brand-logos/desktop-logo.png') }}" alt="logo" class="desktop-logo">
                    <img src="{{ asset('assets/images/brand-logos/toggle-logo.png') }}" alt="logo" class="toggle-logo">
                    <img src="{{ asset('assets/images/brand-logos/desktop-white.png') }}" alt="logo" class="desktop-white">
                    <img src="{{ asset('assets/images/brand-logos/toggle-white.png') }}" alt="logo" class="toggle-white">
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

                        <li class="slide">
                            <a href="{{ route('frontend.home') }}" target="_blank" rel="noopener noreferrer" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                                <span class="side-menu__label">الواجهة الأمامية</span>
                                <i class="fe fe-external-link side-menu__angle" style="font-size: 14px; margin-right: auto;"></i>
                            </a>
                        </li>

                        <li class="slide {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <a href="{{ route('admin.dashboard') }}" class="side-menu__item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" ><path d="M0 0h24v24H0V0z" fill="none"/><path d="M5 5h4v6H5zm10 8h4v6h-4zM5 17h4v2H5zM15 5h4v2h-4z" opacity=".3"/><path d="M3 13h8V3H3v10zm2-8h4v6H5V5zm8 16h8V11h-8v10zm2-8h4v6h-4v-6zM13 3v6h8V3h-8zm6 4h-4V5h4v2zM3 21h8v-6H3v6zm2-4h4v2H5v-2z"/></svg>
                                <span class="side-menu__label">الرئيسية</span>
                            </a>
                        </li>

                        <!-- الصلاحيات -->
                        <li class="slide {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                            <a href="{{route("roles.index")}}" class="side-menu__item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                                <i class="fas fa-user-shield side-menu__icon"></i>
                                <span class="side-menu__label">الصلاحيات</span>
                            </a>
                        </li>

                        <!-- المستخدمون -->
                        <li class="slide has-sub {{ request()->routeIs('users.*') ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                <i class="fas fa-users side-menu__icon"></i>
                                <span class="side-menu__label">المستخدمون</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1 {{ request()->routeIs('users.*') ? 'active' : '' }}" style="{{ request()->routeIs('users.*') ? 'display: block;' : '' }}">
                                <li class="slide {{ request()->routeIs('users.index') && !request()->routeIs('users.bulk-import.*') ? 'active' : '' }}">
                                    <a href="{{ route('users.index') }}" class="side-menu__item {{ request()->routeIs('users.index') && !request()->routeIs('users.bulk-import.*') ? 'active' : '' }}">جميع المستخدمين</a>
                                </li>
                                <li class="slide {{ request()->routeIs('users.bulk-import.index') ? 'active' : '' }}">
                                    <a href="{{ route('users.bulk-import.index') }}" class="side-menu__item {{ request()->routeIs('users.bulk-import.index') ? 'active' : '' }}">رفع جماعي من Excel</a>
                                </li>
                                <li class="slide {{ request()->routeIs('users.bulk-import.reports') ? 'active' : '' }}">
                                    <a href="{{ route('users.bulk-import.reports') }}" class="side-menu__item {{ request()->routeIs('users.bulk-import.reports') ? 'active' : '' }}">تقارير الرفع</a>
                                </li>
                            </ul>
                        </li>

                        <!-- التصنيفات -->
                        <li class="slide {{ request()->routeIs('course-categories.*') ? 'active' : '' }}">
                            <a href="{{route('course-categories.index')}}" class="side-menu__item {{ request()->routeIs('course-categories.*') ? 'active' : '' }}">
                                <i class="fas fa-th-large side-menu__icon"></i>
                                <span class="side-menu__label">التصنيفات</span>
                            </a>
                        </li>

                        <!-- الكورسات -->
                        <li class="slide has-sub {{ request()->routeIs('courses.*') && !request()->routeIs('frontend-courses.*') ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('courses.*') && !request()->routeIs('frontend-courses.*') ? 'active' : '' }}">
                                <i class="fas fa-book side-menu__icon"></i>
                                <span class="side-menu__label">الكورسات الداخلية</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1 {{ request()->routeIs('courses.*') && !request()->routeIs('frontend-courses.*') ? 'active' : '' }}" style="{{ request()->routeIs('courses.*') && !request()->routeIs('frontend-courses.*') ? 'display: block;' : '' }}">
                                <li class="slide {{ request()->routeIs('courses.index') ? 'active' : '' }}">
                                    <a href="{{ route('courses.index') }}" class="side-menu__item {{ request()->routeIs('courses.index') ? 'active' : '' }}">جميع الكورسات</a>
                                </li>
                                <li class="slide {{ request()->routeIs('courses.create') ? 'active' : '' }}">
                                    <a href="{{ route('courses.create') }}" class="side-menu__item {{ request()->routeIs('courses.create') ? 'active' : '' }}">إضافة كورس جديد</a>
                                </li>
                            </ul>
                        </li>

                        <!-- كورسات الواجهة الأمامية -->
                        <li class="slide has-sub {{ request()->routeIs('admin.frontend-courses.*') ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('admin.frontend-courses.*') ? 'active' : '' }}">
                                <i class="fas fa-laptop-code side-menu__icon"></i>
                                <span class="side-menu__label">كورسات الواجهة الأمامية</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1 {{ request()->routeIs('admin.frontend-courses.*') ? 'active' : '' }}" style="{{ request()->routeIs('admin.frontend-courses.*') ? 'display: block;' : '' }}">
                                <li class="slide {{ request()->routeIs('admin.frontend-courses.index') ? 'active' : '' }}">
                                    <a href="{{ route('admin.frontend-courses.index') }}" class="side-menu__item {{ request()->routeIs('admin.frontend-courses.index') ? 'active' : '' }}">
                                        <i class="ri-list-check me-2"></i>جميع الكورسات
                                    </a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.frontend-courses.create') ? 'active' : '' }}">
                                    <a href="{{ route('admin.frontend-courses.create') }}" class="side-menu__item {{ request()->routeIs('admin.frontend-courses.create') ? 'active' : '' }}">
                                        <i class="ri-add-circle-line me-2"></i>إضافة كورس جديد
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- المدونة -->
                        <li class="slide has-sub {{ request()->routeIs('admin.blog.*') ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('admin.blog.*') ? 'active' : '' }}">
                                <i class="fas fa-blog side-menu__icon"></i>
                                <span class="side-menu__label">المدونة</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1 {{ request()->routeIs('admin.blog.*') ? 'active' : '' }}" style="{{ request()->routeIs('admin.blog.*') ? 'display: block;' : '' }}">
                                <li class="slide {{ request()->routeIs('admin.blog.posts.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.blog.posts.index') }}" class="side-menu__item {{ request()->routeIs('admin.blog.posts.*') ? 'active' : '' }}">
                                        <i class="ri-article-line me-2"></i>المقالات
                                    </a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.blog.categories.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.blog.categories.index') }}" class="side-menu__item {{ request()->routeIs('admin.blog.categories.*') ? 'active' : '' }}">
                                        <i class="ri-folder-line me-2"></i>التصنيفات
                                    </a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.blog.tags.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.blog.tags.index') }}" class="side-menu__item {{ request()->routeIs('admin.blog.tags.*') ? 'active' : '' }}">
                                        <i class="ri-price-tag-3-line me-2"></i>الوسوم
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- الأسئلة الشائعة -->
                        <li class="slide has-sub {{ request()->routeIs('admin.faqs.*') ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}">
                                <i class="fas fa-circle-question side-menu__icon"></i>
                                <span class="side-menu__label">الأسئلة الشائعة</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1 {{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}" style="{{ request()->routeIs('admin.faqs.*') ? 'display: block;' : '' }}">
                                <li class="slide {{ request()->routeIs('admin.faqs.index') ? 'active' : '' }}">
                                    <a href="{{ route('admin.faqs.index') }}" class="side-menu__item {{ request()->routeIs('admin.faqs.index') ? 'active' : '' }}">
                                        <i class="ri-list-check me-2"></i>جميع الأسئلة
                                    </a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.faqs.create') ? 'active' : '' }}">
                                    <a href="{{ route('admin.faqs.create') }}" class="side-menu__item {{ request()->routeIs('admin.faqs.create') ? 'active' : '' }}">
                                        <i class="ri-add-circle-line me-2"></i>إضافة سؤال جديد
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- الدروس -->
                        <li class="slide {{ request()->routeIs('lessons.all') ? 'active' : '' }}">
                            <a href="{{route('lessons.all')}}" class="side-menu__item {{ request()->routeIs('lessons.all') ? 'active' : '' }}">
                                <i class="fas fa-book-reader side-menu__icon"></i>
                                <span class="side-menu__label">الدروس</span>
                            </a>
                        </li>

                        <!-- الفيديوهات -->
                        <li class="slide has-sub {{ request()->routeIs('videos.*') ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('videos.*') ? 'active' : '' }}">
                                <i class="fas fa-video side-menu__icon"></i>
                                <span class="side-menu__label">مكتبة الفيديوهات</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1 {{ request()->routeIs('videos.*') ? 'active' : '' }}" style="{{ request()->routeIs('videos.*') ? 'display: block;' : '' }}">
                                <li class="slide {{ request()->routeIs('videos.index') ? 'active' : '' }}">
                                    <a href="{{ route('videos.index') }}" class="side-menu__item {{ request()->routeIs('videos.index') ? 'active' : '' }}">جميع الفيديوهات</a>
                                </li>
                                <li class="slide {{ request()->routeIs('videos.create') ? 'active' : '' }}">
                                    <a href="{{ route('videos.create') }}" class="side-menu__item {{ request()->routeIs('videos.create') ? 'active' : '' }}">إضافة فيديو جديد</a>
                                </li>
                            </ul>
                        </li>

                        <!-- الموارد -->
                        <li class="slide {{ request()->routeIs('resources.*') ? 'active' : '' }}">
                            <a href="{{ route('resources.index') }}" class="side-menu__item {{ request()->routeIs('resources.*') ? 'active' : '' }}">
                                <i class="fas fa-folder side-menu__icon"></i>
                                <span class="side-menu__label">مكتبة الموارد</span>
                            </a>
                        </li>

                        <!-- المعسكرات التدريبية -->
                        <li class="slide has-sub {{ request()->routeIs('training-camps.*') ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('training-camps.*') ? 'active' : '' }}">
                                <i class="fas fa-campground side-menu__icon"></i>
                                <span class="side-menu__label">المعسكرات التدريبية</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1 {{ request()->routeIs('training-camps.*') ? 'active' : '' }}" style="{{ request()->routeIs('training-camps.*') ? 'display: block;' : '' }}">
                                <li class="slide {{ request()->routeIs('training-camps.index') ? 'active' : '' }}">
                                    <a href="{{route('training-camps.index')}}" class="side-menu__item {{ request()->routeIs('training-camps.index') ? 'active' : '' }}">جميع المعسكرات</a>
                                </li>
                                <li class="slide {{ request()->routeIs('training-camps.enrollments') ? 'active' : '' }}">
                                    <a href="{{route('training-camps.enrollments')}}" class="side-menu__item {{ request()->routeIs('training-camps.enrollments') ? 'active' : '' }}">طلبات التسجيل</a>
                                </li>
                            </ul>
                        </li>

                        <!-- الانضمامات -->
                        <li class="slide {{ request()->routeIs('enrollments.all') ? 'active' : '' }}">
                            <a href="{{route('enrollments.all')}}" class="side-menu__item {{ request()->routeIs('enrollments.all') ? 'active' : '' }}">
                                <i class="fas fa-user-graduate side-menu__icon"></i>
                                <span class="side-menu__label">الانضمامات</span>
                            </a>
                        </li>

                        <!-- الشهادات -->
                        <li class="slide has-sub {{ request()->routeIs('admin.certificates.*') || request()->routeIs('admin.certificate-templates.*') ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('admin.certificates.*') || request()->routeIs('admin.certificate-templates.*') ? 'active' : '' }}">
                                <i class="fas fa-certificate side-menu__icon"></i>
                                <span class="side-menu__label">الشهادات</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1 {{ request()->routeIs('admin.certificates.*') || request()->routeIs('admin.certificate-templates.*') ? 'active' : '' }}" style="{{ request()->routeIs('admin.certificates.*') || request()->routeIs('admin.certificate-templates.*') ? 'display: block;' : '' }}">
                                <li class="slide {{ request()->routeIs('admin.certificates.index') ? 'active' : '' }}">
                                    <a href="{{ route('admin.certificates.index') }}" class="side-menu__item">جميع الشهادات</a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.certificates.create') ? 'active' : '' }}">
                                    <a href="{{ route('admin.certificates.create') }}" class="side-menu__item">إصدار شهادة</a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.certificate-templates.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.certificate-templates.index') }}" class="side-menu__item">قوالب الشهادات</a>
                                </li>
                            </ul>
                        </li>

                        <!-- المجموعات -->
                        <li class="slide {{ request()->routeIs('groups.all') ? 'active' : '' }}">
                            <a href="{{route('groups.all')}}" class="side-menu__item {{ request()->routeIs('groups.all') ? 'active' : '' }}">
                                <i class="fas fa-users-cog side-menu__icon"></i>
                                <span class="side-menu__label">المجموعات</span>
                            </a>
                        </li>

                        <!-- الواجبات -->
                        <li class="slide has-sub {{ request()->routeIs('assignments.*') ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('assignments.*') ? 'active' : '' }}">
                                <i class="fas fa-tasks side-menu__icon"></i>
                                <span class="side-menu__label">الواجبات</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1 {{ request()->routeIs('assignments.*') ? 'active' : '' }}" style="{{ request()->routeIs('assignments.*') ? 'display: block;' : '' }}">
                                <li class="slide {{ request()->routeIs('assignments.index') ? 'active' : '' }}">
                                    <a href="{{ route('assignments.index') }}" class="side-menu__item {{ request()->routeIs('assignments.index') ? 'active' : '' }}">جميع الواجبات</a>
                                </li>
                                <li class="slide {{ request()->routeIs('assignments.create') ? 'active' : '' }}">
                                    <a href="{{ route('assignments.create') }}" class="side-menu__item {{ request()->routeIs('assignments.create') ? 'active' : '' }}">إضافة واجب جديد</a>
                                </li>
                            </ul>
                        </li>

                        <!-- جدول أعمال الطلاب -->
                        <li class="slide {{ request()->routeIs('admin.student-works.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.student-works.index') }}" class="side-menu__item">
                                <i class="ri-folder-user-line side-menu__icon"></i>
                                <span class="side-menu__label">جدول الأعمال</span>
                            </a>
                        </li>

                        <!-- الاختبارات -->
                        <li class="slide has-sub {{ request()->routeIs('quizzes.*') ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('quizzes.*') ? 'active' : '' }}">
                                <i class="fas fa-clipboard-list side-menu__icon"></i>
                                <span class="side-menu__label">الاختبارات</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1 {{ request()->routeIs('quizzes.*') ? 'active' : '' }}" style="{{ request()->routeIs('quizzes.*') ? 'display: block;' : '' }}">
                                <li class="slide {{ request()->routeIs('quizzes.index') ? 'active' : '' }}">
                                    <a href="{{ route('quizzes.index') }}" class="side-menu__item {{ request()->routeIs('quizzes.index') ? 'active' : '' }}">جميع الاختبارات</a>
                                </li>
                                <li class="slide {{ request()->routeIs('quizzes.create') ? 'active' : '' }}">
                                    <a href="{{ route('quizzes.create') }}" class="side-menu__item {{ request()->routeIs('quizzes.create') ? 'active' : '' }}">إضافة اختبار جديد</a>
                                </li>
                            </ul>
                        </li>

                        <!-- بنك الأسئلة -->
                        <li class="slide has-sub {{ request()->routeIs('question-bank.*') ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('question-bank.*') ? 'active' : '' }}">
                                <i class="fas fa-question-circle side-menu__icon"></i>
                                <span class="side-menu__label">بنك الأسئلة</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1 {{ request()->routeIs('question-bank.*') ? 'active' : '' }}" style="{{ request()->routeIs('question-bank.*') ? 'display: block;' : '' }}">
                                <li class="slide {{ request()->routeIs('question-bank.index') ? 'active' : '' }}">
                                    <a href="{{ route('question-bank.index') }}" class="side-menu__item {{ request()->routeIs('question-bank.index') ? 'active' : '' }}">جميع الأسئلة</a>
                                </li>
                                <li class="slide {{ request()->routeIs('question-bank.create') ? 'active' : '' }}">
                                    <a href="{{ route('question-bank.create') }}" class="side-menu__item {{ request()->routeIs('question-bank.create') ? 'active' : '' }}">إضافة سؤال جديد</a>
                                </li>
                            </ul>
                        </li>

                        <!-- مجموعات الأسئلة -->
                        <li class="slide {{ request()->routeIs('question-pools.*') ? 'active' : '' }}">
                            <a href="{{ route('question-pools.index') }}" class="side-menu__item">
                                <i class="fas fa-layer-group side-menu__icon"></i>
                                <span class="side-menu__label">مجموعات الأسئلة</span>
                            </a>
                        </li>

                        <!-- تحليلات الاختبارات -->
                        <li class="slide {{ request()->routeIs('quiz-analytics.*') ? 'active' : '' }}">
                            <a href="{{ route('quiz-analytics.index') }}" class="side-menu__item">
                                <i class="fas fa-chart-pie side-menu__icon"></i>
                                <span class="side-menu__label">تحليلات الاختبارات</span>
                            </a>
                        </li>

                        <!-- الفواتير -->
                        <li class="slide {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                            <a href="{{route('invoices.index')}}" class="side-menu__item {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                                <i class="fas fa-file-invoice-dollar side-menu__icon"></i>
                                <span class="side-menu__label">الفواتير</span>
                            </a>
                        </li>

                        <!-- المدفوعات -->
                        <li class="slide {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                            <a href="{{route('payments.index')}}" class="side-menu__item {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                                <i class="fas fa-money-bill-wave side-menu__icon"></i>
                                <span class="side-menu__label">المدفوعات</span>
                            </a>
                        </li>

                        <!-- طرق الدفع -->
                        <li class="slide {{ request()->routeIs('payment-methods.*') ? 'active' : '' }}">
                            <a href="{{route('payment-methods.index')}}" class="side-menu__item {{ request()->routeIs('payment-methods.*') ? 'active' : '' }}">
                                <i class="fas fa-credit-card side-menu__icon"></i>
                                <span class="side-menu__label">طرق الدفع</span>
                            </a>
                        </li>

                        <!-- لوحة التحكم -->
                        <li class="slide {{ request()->routeIs('admin.gamification.dashboard') ? 'active' : '' }}">
                            <a href="{{ route('admin.gamification.dashboard') }}" class="side-menu__item {{ request()->routeIs('admin.gamification.dashboard') ? 'active' : '' }}">
                                <i class="fas fa-gamepad side-menu__icon"></i>
                                <span class="side-menu__label">لوحة التحكم</span>
                            </a>
                        </li>

                        <!-- المستويات والإنجازات -->
                        <li class="slide has-sub {{ request()->routeIs('admin.gamification.levels.*') || request()->routeIs('admin.gamification.badges.*') || request()->routeIs('admin.gamification.achievements.*') ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <i class="fas fa-trophy side-menu__icon"></i>
                                <span class="side-menu__label">المستويات والإنجازات</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide {{ request()->routeIs('admin.gamification.levels.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.gamification.levels.index') }}" class="side-menu__item">المستويات</a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.gamification.badges.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.gamification.badges.index') }}" class="side-menu__item">الشارات</a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.gamification.achievements.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.gamification.achievements.index') }}" class="side-menu__item">الإنجازات</a>
                                </li>
                            </ul>
                        </li>

                        <!-- النقاط والمكافآت -->
                        <li class="slide has-sub {{ request()->routeIs('admin.gamification.points.*') || request()->routeIs('admin.gamification.shop.*') ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <i class="fas fa-coins side-menu__icon"></i>
                                <span class="side-menu__label">النقاط والمكافآت</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide {{ request()->routeIs('admin.gamification.points.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.gamification.points.index') }}" class="side-menu__item {{ request()->routeIs('admin.gamification.points.*') ? 'active' : '' }}">النقاط</a>
                                </li>
                                <li class="slide has-sub {{ request()->routeIs('admin.gamification.shop.*') ? 'open active' : '' }}">
                                    <a href="javascript:void(0);" class="side-menu__item">
                                        <i class="fas fa-store me-2"></i>المتجر
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child2">
                                        <li class="slide {{ request()->routeIs('admin.gamification.shop.categories.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.gamification.shop.categories.index') }}" class="side-menu__item">الفئات</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.gamification.shop.items.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.gamification.shop.items.index') }}" class="side-menu__item">العناصر</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.gamification.shop.purchases.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.gamification.shop.purchases.index') }}" class="side-menu__item">المشتريات</a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>

                        <!-- المنافسة -->
                        <li class="slide has-sub {{ request()->routeIs('admin.gamification.leaderboards.*') || request()->routeIs('admin.gamification.challenges.*') ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <i class="fas fa-crown side-menu__icon"></i>
                                <span class="side-menu__label">المنافسة</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide {{ request()->routeIs('admin.gamification.leaderboards.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.gamification.leaderboards.index') }}" class="side-menu__item {{ request()->routeIs('admin.gamification.leaderboards.*') ? 'active' : '' }}">لوحات المتصدرين</a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.gamification.challenges.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.gamification.challenges.index') }}" class="side-menu__item {{ request()->routeIs('admin.gamification.challenges.*') ? 'active' : '' }}">التحديات</a>
                                </li>
                            </ul>
                        </li>

                        <!-- التحليلات -->
                        <li class="slide {{ request()->routeIs('admin.gamification.analytics.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.gamification.analytics.dashboard') }}" class="side-menu__item">
                                <i class="fas fa-chart-line side-menu__icon"></i>
                                <span class="side-menu__label">التحليلات</span>
                            </a>
                        </li>

                        <!-- الإشعارات -->
                        <li class="slide has-sub {{ request()->routeIs('admin.notifications.*') ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <i class="fas fa-bell side-menu__icon"></i>
                                <span class="side-menu__label">الإشعارات</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide {{ request()->routeIs('admin.notifications.index') ? 'active' : '' }}">
                                    <a href="{{ route('admin.notifications.index') }}" class="side-menu__item {{ request()->routeIs('admin.notifications.index') ? 'active' : '' }}">إرسال إشعار</a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.notifications.history') ? 'active' : '' }}">
                                    <a href="{{ route('admin.notifications.history') }}" class="side-menu__item {{ request()->routeIs('admin.notifications.history') ? 'active' : '' }}">السجل</a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.notifications.statistics') ? 'active' : '' }}">
                                    <a href="{{ route('admin.notifications.statistics') }}" class="side-menu__item {{ request()->routeIs('admin.notifications.statistics') ? 'active' : '' }}">الإحصائيات</a>
                                </li>
                            </ul>
                        </li>

                        <!-- التذكيرات -->
                        <li class="slide has-sub {{ request()->routeIs('admin.reminders.*') ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <i class="ri-notification-badge-line side-menu__icon"></i>
                                <span class="side-menu__label">التذكيرات</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide {{ request()->routeIs('admin.reminders.index') ? 'active' : '' }}">
                                    <a href="{{ route('admin.reminders.index') }}" class="side-menu__item {{ request()->routeIs('admin.reminders.index') ? 'active' : '' }}">جميع التذكيرات</a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.reminders.create') ? 'active' : '' }}">
                                    <a href="{{ route('admin.reminders.create') }}" class="side-menu__item {{ request()->routeIs('admin.reminders.create') ? 'active' : '' }}">إرسال تذكير جديد</a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.reminders.statistics') ? 'active' : '' }}">
                                    <a href="{{ route('admin.reminders.statistics') }}" class="side-menu__item {{ request()->routeIs('admin.reminders.statistics') ? 'active' : '' }}">الإحصائيات</a>
                                </li>
                            </ul>
                        </li>

                        <!-- إعدادات البريد -->
                        <li class="slide has-sub {{ request()->routeIs('admin.settings.email.*') ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <i class="ri-mail-settings-line side-menu__icon"></i>
                                <span class="side-menu__label">إعدادات البريد</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide {{ request()->routeIs('admin.settings.email.index') ? 'active' : '' }}">
                                    <a href="{{ route('admin.settings.email.index') }}" class="side-menu__item {{ request()->routeIs('admin.settings.email.index') ? 'active' : '' }}">جميع الإعدادات</a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.settings.email.create') ? 'active' : '' }}">
                                    <a href="{{ route('admin.settings.email.create') }}" class="side-menu__item {{ request()->routeIs('admin.settings.email.create') ? 'active' : '' }}">إضافة إعدادات</a>
                                </li>
                            </ul>
                        </li>

                        <!-- إعدادات صفحة الاتصال -->
                        <li class="slide {{ request()->routeIs('admin.contact-settings.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.contact-settings.edit') }}" class="side-menu__item {{ request()->routeIs('admin.contact-settings.*') ? 'active' : '' }}">
                                <i class="fas fa-address-card side-menu__icon"></i>
                                <span class="side-menu__label">إعدادات صفحة الاتصال</span>
                            </a>
                        </li>

                        <!-- التقويم -->
                        <li class="slide {{ request()->routeIs('admin.calendar.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.calendar.index') }}" class="side-menu__item {{ request()->routeIs('admin.calendar.*') ? 'active' : '' }}">
                                <i class="ri-calendar-line side-menu__icon"></i>
                                <span class="side-menu__label">التقويم والمواعيد</span>
                            </a>
                        </li>

                        <!-- Webhooks & Integration -->
                        <li class="slide has-sub {{ request()->routeIs('admin.webhooks.*') || request()->routeIs('admin.n8n.*') ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <i class="fas fa-exchange-alt side-menu__icon"></i>
                                <span class="side-menu__label">الويب هوكس</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1 {{ request()->routeIs('admin.webhooks.*') || request()->routeIs('admin.n8n.*') ? 'active' : '' }}" style="{{ request()->routeIs('admin.webhooks.*') || request()->routeIs('admin.n8n.*') ? 'display: block;' : '' }}">
                                <li class="slide {{ request()->routeIs('admin.webhooks.index') ? 'active' : '' }}">
                                    <a href="{{ route('admin.webhooks.index') }}" class="side-menu__item">WPForms Webhooks</a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.webhooks.tokens.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.webhooks.tokens.index') }}" class="side-menu__item">إدارة التوكنات</a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.webhooks.submissions') ? 'active' : '' }}">
                                    <a href="{{ route('admin.webhooks.submissions') }}" class="side-menu__item">الإرساليات</a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.webhooks.logs') ? 'active' : '' }}">
                                    <a href="{{ route('admin.webhooks.logs') }}" class="side-menu__item">السجلات</a>
                                </li>

                                <!-- n8n Integration -->
                                <li class="slide has-sub {{ request()->routeIs('admin.n8n.*') ? 'open active' : '' }}">
                                    <a href="javascript:void(0);" class="side-menu__item">
                                        <i class="fas fa-plug me-2"></i>n8n Integration
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child2">
                                        <li class="slide {{ request()->routeIs('admin.n8n.index') ? 'active' : '' }}">
                                            <a href="{{ route('admin.n8n.index') }}" class="side-menu__item">لوحة التحكم</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.n8n.endpoints.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.n8n.endpoints.index') }}" class="side-menu__item">نقاط النهاية</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.n8n.handlers.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.n8n.handlers.index') }}" class="side-menu__item">المعالجات</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.n8n.logs.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.n8n.logs.index') }}" class="side-menu__item">السجلات</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.n8n.statistics') ? 'active' : '' }}">
                                            <a href="{{ route('admin.n8n.statistics') }}" class="side-menu__item">الإحصائيات</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.n8n.documentation') ? 'active' : '' }}">
                                            <a href="{{ route('admin.n8n.documentation') }}" class="side-menu__item">التوثيق</a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>

                    </ul>
                    <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24"> <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path> </svg></div>
                </nav>
                <!-- End::nav -->

            </div>
            <!-- End::main-sidebar -->

        </aside>
        <!-- End::app-sidebar -->
