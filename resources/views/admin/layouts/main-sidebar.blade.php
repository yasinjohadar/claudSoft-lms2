        <!-- Start::app-sidebar -->
        <aside class="app-sidebar admin-sidebar sticky" id="sidebar">

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

                        @php
                            $internalCoursesActive = request()->routeIs('courses.*');
                            $frontendCoursesActive = request()->routeIs('admin.frontend-courses.*');
                            $coursesSectionActive = $internalCoursesActive
                                || $frontendCoursesActive
                                || request()->routeIs('course-categories.*', 'lessons.all', 'videos.*', 'resources.*');
                            $assessmentsActive = request()->routeIs(
                                'assignments.*',
                                'quizzes.*',
                                'random-pool-quizzes.*',
                                'programming-challenges.*',
                                'admin.challenge-grading.*',
                                'admin.project-challenges.*',
                                'admin.project-grading.*',
                                'admin.question-module-grading.*',
                                'question-bank.*',
                                'question-pools.*',
                                'quiz-analytics.*'
                            );
                            $programmingChallengesActive = request()->routeIs('programming-challenges.*', 'admin.challenge-grading.*');
                            $projectChallengesActive = request()->routeIs('admin.project-challenges.*', 'admin.project-grading.*');
                            $enrollmentSectionActive = request()->routeIs(
                                'enrollments.all',
                                'training-camps.*',
                                'groups.all',
                                'admin.group-registrations.*',
                                'admin.group-registration-settings.*'
                            );
                            $financeActive = request()->routeIs('invoices.*', 'payments.*', 'admin.settings.payment-whatsapp-message.*');
                            $communicationActive = request()->routeIs(
                                'admin.notifications.*',
                                'admin.reminders.*',
                                'admin.calendar.*'
                            );
                            $contentActive = request()->routeIs('admin.blog.*', 'admin.docs.*', 'admin.faqs.*', 'admin.lesson-simulators.*');
                            $gamificationActive = request()->routeIs('admin.gamification.*');
                        @endphp

                        <li class="slide">
                            <a href="{{ route('frontend.home') }}" target="_blank" rel="noopener noreferrer" class="side-menu__item">
                                <i class="fe fe-globe side-menu__icon"></i>
                                <span class="side-menu__label">الواجهة الأمامية</span>
                                <i class="fe fe-external-link side-menu__angle" style="font-size: 14px; margin-right: auto;"></i>
                            </a>
                        </li>

                        <li class="slide {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <a href="{{ route('admin.dashboard') }}" class="side-menu__item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                <i class="fe fe-activity side-menu__icon"></i>
                                <span class="side-menu__label">الرئيسية</span>
                            </a>
                        </li>

                        <!-- إدارة الكورسات والمحتوى -->
                        <li class="slide has-sub {{ $coursesSectionActive ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ $coursesSectionActive ? 'active' : '' }}">
                                <i class="fe fe-book-open side-menu__icon"></i>
                                <span class="side-menu__label">إدارة الكورسات</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1 {{ $coursesSectionActive ? 'active' : '' }}" style="{{ $coursesSectionActive ? 'display: block;' : '' }}">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">إدارة الكورسات</a>
                                </li>

                                <li class="slide has-sub {{ $internalCoursesActive ? 'open active' : '' }}">
                                    <a href="javascript:void(0);" class="side-menu__item {{ $internalCoursesActive ? 'active' : '' }}">
                                        <i class="fas fa-book me-2"></i>الكورسات الداخلية
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child2 {{ $internalCoursesActive ? 'active' : '' }}" style="{{ $internalCoursesActive ? 'display: block;' : '' }}">
                                        <li class="slide {{ request()->routeIs('courses.index') ? 'active' : '' }}">
                                            <a href="{{ route('courses.index') }}" class="side-menu__item {{ request()->routeIs('courses.index') ? 'active' : '' }}">جميع الكورسات</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('courses.create') ? 'active' : '' }}">
                                            <a href="{{ route('courses.create') }}" class="side-menu__item {{ request()->routeIs('courses.create') ? 'active' : '' }}">إضافة كورس جديد</a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="slide has-sub {{ $frontendCoursesActive ? 'open active' : '' }}">
                                    <a href="javascript:void(0);" class="side-menu__item {{ $frontendCoursesActive ? 'active' : '' }}">
                                        <i class="fas fa-laptop-code me-2"></i>كورسات الواجهة الأمامية
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child2 {{ $frontendCoursesActive ? 'active' : '' }}" style="{{ $frontendCoursesActive ? 'display: block;' : '' }}">
                                        <li class="slide {{ request()->routeIs('admin.frontend-courses.index') ? 'active' : '' }}">
                                            <a href="{{ route('admin.frontend-courses.index') }}" class="side-menu__item {{ request()->routeIs('admin.frontend-courses.index') ? 'active' : '' }}">جميع الكورسات</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.frontend-courses.create') ? 'active' : '' }}">
                                            <a href="{{ route('admin.frontend-courses.create') }}" class="side-menu__item {{ request()->routeIs('admin.frontend-courses.create') ? 'active' : '' }}">إضافة كورس جديد</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.frontend-courses.ai.create') ? 'active' : '' }}">
                                            <a href="{{ route('admin.frontend-courses.ai.create') }}" class="side-menu__item {{ request()->routeIs('admin.frontend-courses.ai.create') ? 'active' : '' }}">توليد كورس بالذكاء الاصطناعي</a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="slide {{ request()->routeIs('course-categories.*') ? 'active' : '' }}">
                                    <a href="{{ route('course-categories.index') }}" class="side-menu__item {{ request()->routeIs('course-categories.*') ? 'active' : '' }}">
                                        <i class="fas fa-th-large me-2"></i>التصنيفات
                                    </a>
                                </li>

                                <li class="slide {{ request()->routeIs('lessons.all') ? 'active' : '' }}">
                                    <a href="{{ route('lessons.all') }}" class="side-menu__item {{ request()->routeIs('lessons.all') ? 'active' : '' }}">
                                        <i class="fas fa-book-reader me-2"></i>الدروس
                                    </a>
                                </li>

                                <li class="slide has-sub {{ request()->routeIs('videos.*') ? 'open active' : '' }}">
                                    <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('videos.*') ? 'active' : '' }}">
                                        <i class="fas fa-video me-2"></i>مكتبة الفيديوهات
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child2 {{ request()->routeIs('videos.*') ? 'active' : '' }}" style="{{ request()->routeIs('videos.*') ? 'display: block;' : '' }}">
                                        <li class="slide {{ request()->routeIs('videos.index') ? 'active' : '' }}">
                                            <a href="{{ route('videos.index') }}" class="side-menu__item {{ request()->routeIs('videos.index') ? 'active' : '' }}">جميع الفيديوهات</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('videos.create') ? 'active' : '' }}">
                                            <a href="{{ route('videos.create') }}" class="side-menu__item {{ request()->routeIs('videos.create') ? 'active' : '' }}">إضافة فيديو جديد</a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="slide {{ request()->routeIs('resources.*') ? 'active' : '' }}">
                                    <a href="{{ route('resources.index') }}" class="side-menu__item {{ request()->routeIs('resources.*') ? 'active' : '' }}">
                                        <i class="fas fa-folder me-2"></i>مكتبة الموارد
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- الاختبارات والواجبات -->
                        <li class="slide has-sub {{ $assessmentsActive ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ $assessmentsActive ? 'active' : '' }}">
                                <i class="fe fe-clipboard side-menu__icon"></i>
                                <span class="side-menu__label">الاختبارات والواجبات</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1 {{ $assessmentsActive ? 'active' : '' }}" style="{{ $assessmentsActive ? 'display: block;' : '' }}">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">الاختبارات والواجبات</a>
                                </li>

                                <li class="slide has-sub {{ request()->routeIs('assignments.*') ? 'open active' : '' }}">
                                    <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('assignments.*') ? 'active' : '' }}">
                                        <i class="fas fa-tasks me-2"></i>الواجبات
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child2 {{ request()->routeIs('assignments.*') ? 'active' : '' }}" style="{{ request()->routeIs('assignments.*') ? 'display: block;' : '' }}">
                                        <li class="slide {{ request()->routeIs('assignments.index') ? 'active' : '' }}">
                                            <a href="{{ route('assignments.index') }}" class="side-menu__item {{ request()->routeIs('assignments.index') ? 'active' : '' }}">جميع الواجبات</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('assignments.create') ? 'active' : '' }}">
                                            <a href="{{ route('assignments.create') }}" class="side-menu__item {{ request()->routeIs('assignments.create') ? 'active' : '' }}">إضافة واجب جديد</a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="slide has-sub {{ request()->routeIs('quizzes.*') ? 'open active' : '' }}">
                                    <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('quizzes.*') ? 'active' : '' }}">
                                        <i class="fas fa-clipboard-list me-2"></i>الاختبارات
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child2 {{ request()->routeIs('quizzes.*') ? 'active' : '' }}" style="{{ request()->routeIs('quizzes.*') ? 'display: block;' : '' }}">
                                        <li class="slide {{ request()->routeIs('quizzes.index') ? 'active' : '' }}">
                                            <a href="{{ route('quizzes.index') }}" class="side-menu__item {{ request()->routeIs('quizzes.index') ? 'active' : '' }}">جميع الاختبارات</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('quizzes.create') ? 'active' : '' }}">
                                            <a href="{{ route('quizzes.create') }}" class="side-menu__item {{ request()->routeIs('quizzes.create') ? 'active' : '' }}">إضافة اختبار جديد</a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="slide has-sub {{ request()->routeIs('random-pool-quizzes.*') ? 'open active' : '' }}">
                                    <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('random-pool-quizzes.*') ? 'active' : '' }}">
                                        <i class="fas fa-random me-2"></i>بنك عشوائي
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child2 {{ request()->routeIs('random-pool-quizzes.*') ? 'active' : '' }}" style="{{ request()->routeIs('random-pool-quizzes.*') ? 'display: block;' : '' }}">
                                        <li class="slide {{ request()->routeIs('random-pool-quizzes.index') ? 'active' : '' }}">
                                            <a href="{{ route('random-pool-quizzes.index') }}" class="side-menu__item {{ request()->routeIs('random-pool-quizzes.index') ? 'active' : '' }}">جميع اختبارات البنك</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('random-pool-quizzes.create') ? 'active' : '' }}">
                                            <a href="{{ route('random-pool-quizzes.create') }}" class="side-menu__item {{ request()->routeIs('random-pool-quizzes.create') ? 'active' : '' }}">إضافة اختبار بنك عشوائي</a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="slide has-sub {{ $programmingChallengesActive ? 'open active' : '' }}">
                                    <a href="javascript:void(0);" class="side-menu__item {{ $programmingChallengesActive ? 'active' : '' }}">
                                        <i class="fas fa-code me-2"></i>التحديات البرمجية
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child2 {{ $programmingChallengesActive ? 'active' : '' }}" style="{{ $programmingChallengesActive ? 'display: block;' : '' }}">
                                        <li class="slide {{ request()->routeIs('programming-challenges.index') ? 'active' : '' }}">
                                            <a href="{{ route('programming-challenges.index') }}" class="side-menu__item {{ request()->routeIs('programming-challenges.index') ? 'active' : '' }}">جميع التحديات</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('programming-challenges.create') ? 'active' : '' }}">
                                            <a href="{{ route('programming-challenges.create') }}" class="side-menu__item {{ request()->routeIs('programming-challenges.create') ? 'active' : '' }}">إضافة تحدي جديد</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.challenge-grading.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.challenge-grading.index') }}" class="side-menu__item {{ request()->routeIs('admin.challenge-grading.*') ? 'active' : '' }}">تقييم التحديات</a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="slide has-sub {{ $projectChallengesActive ? 'open active' : '' }}">
                                    <a href="javascript:void(0);" class="side-menu__item {{ $projectChallengesActive ? 'active' : '' }}">
                                        <i class="fas fa-project-diagram me-2"></i>مشاريع التحدي
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child2 {{ $projectChallengesActive ? 'active' : '' }}" style="{{ $projectChallengesActive ? 'display: block;' : '' }}">
                                        <li class="slide {{ request()->routeIs('admin.project-challenges.index') ? 'active' : '' }}">
                                            <a href="{{ route('admin.project-challenges.index') }}" class="side-menu__item {{ request()->routeIs('admin.project-challenges.index') ? 'active' : '' }}">جميع المشاريع</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.project-challenges.create') ? 'active' : '' }}">
                                            <a href="{{ route('admin.project-challenges.create') }}" class="side-menu__item {{ request()->routeIs('admin.project-challenges.create') ? 'active' : '' }}">إضافة مشروع</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.project-grading.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.project-grading.index') }}" class="side-menu__item {{ request()->routeIs('admin.project-grading.*') ? 'active' : '' }}">تقييم التسليمات</a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="slide {{ request()->routeIs('admin.question-module-grading.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.question-module-grading.index') }}" class="side-menu__item {{ request()->routeIs('admin.question-module-grading.*') ? 'active' : '' }}">
                                        <i class="fas fa-check-circle me-2"></i>تصحيح اختبارات الكورسات
                                    </a>
                                </li>

                                <li class="slide has-sub {{ request()->routeIs('question-bank.*') ? 'open active' : '' }}">
                                    <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('question-bank.*') ? 'active' : '' }}">
                                        <i class="fas fa-question-circle me-2"></i>بنك الأسئلة
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child2 {{ request()->routeIs('question-bank.*') ? 'active' : '' }}" style="{{ request()->routeIs('question-bank.*') ? 'display: block;' : '' }}">
                                        <li class="slide {{ request()->routeIs('question-bank.index') ? 'active' : '' }}">
                                            <a href="{{ route('question-bank.index') }}" class="side-menu__item {{ request()->routeIs('question-bank.index') ? 'active' : '' }}">جميع الأسئلة</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('question-bank.create') ? 'active' : '' }}">
                                            <a href="{{ route('question-bank.create') }}" class="side-menu__item {{ request()->routeIs('question-bank.create') ? 'active' : '' }}">إضافة سؤال جديد</a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="slide {{ request()->routeIs('question-pools.*') ? 'active' : '' }}">
                                    <a href="{{ route('question-pools.index') }}" class="side-menu__item {{ request()->routeIs('question-pools.*') ? 'active' : '' }}">
                                        <i class="fas fa-layer-group me-2"></i>مجموعات الأسئلة
                                    </a>
                                </li>

                                <li class="slide {{ request()->routeIs('quiz-analytics.*') ? 'active' : '' }}">
                                    <a href="{{ route('quiz-analytics.index') }}" class="side-menu__item {{ request()->routeIs('quiz-analytics.*') ? 'active' : '' }}">
                                        <i class="fas fa-chart-pie me-2"></i>تحليلات الاختبارات
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- التسجيل والمجموعات -->
                        <li class="slide has-sub {{ $enrollmentSectionActive ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ $enrollmentSectionActive ? 'active' : '' }}">
                                <i class="fe fe-user-check side-menu__icon"></i>
                                <span class="side-menu__label">التسجيل والمجموعات</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1 {{ $enrollmentSectionActive ? 'active' : '' }}" style="{{ $enrollmentSectionActive ? 'display: block;' : '' }}">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">التسجيل والمجموعات</a>
                                </li>

                                <li class="slide {{ request()->routeIs('enrollments.all') ? 'active' : '' }}">
                                    <a href="{{ route('enrollments.all') }}" class="side-menu__item {{ request()->routeIs('enrollments.all') ? 'active' : '' }}">
                                        <i class="fas fa-user-check me-2"></i>الانضمامات
                                    </a>
                                </li>

                                <li class="slide has-sub {{ request()->routeIs('training-camps.*') ? 'open active' : '' }}">
                                    <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('training-camps.*') ? 'active' : '' }}">
                                        <i class="fas fa-campground me-2"></i>المعسكرات التدريبية
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child2 {{ request()->routeIs('training-camps.*') ? 'active' : '' }}" style="{{ request()->routeIs('training-camps.*') ? 'display: block;' : '' }}">
                                        <li class="slide {{ request()->routeIs('training-camps.index') ? 'active' : '' }}">
                                            <a href="{{ route('training-camps.index') }}" class="side-menu__item {{ request()->routeIs('training-camps.index') ? 'active' : '' }}">جميع المعسكرات</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('training-camps.enrollments') ? 'active' : '' }}">
                                            <a href="{{ route('training-camps.enrollments') }}" class="side-menu__item {{ request()->routeIs('training-camps.enrollments') ? 'active' : '' }}">طلبات التسجيل</a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="slide {{ request()->routeIs('groups.all') ? 'active' : '' }}">
                                    <a href="{{ route('groups.all') }}" class="side-menu__item {{ request()->routeIs('groups.all') ? 'active' : '' }}">
                                        <i class="fas fa-users-cog me-2"></i>المجموعات
                                    </a>
                                </li>

                                <li class="slide has-sub {{ request()->routeIs('admin.group-registrations.*') || request()->routeIs('admin.group-registration-settings.*') ? 'open active' : '' }}">
                                    <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('admin.group-registrations.*') || request()->routeIs('admin.group-registration-settings.*') ? 'active' : '' }}">
                                        <i class="fas fa-users me-2"></i>تسجيلات المجموعات
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child2 {{ request()->routeIs('admin.group-registrations.*') || request()->routeIs('admin.group-registration-settings.*') ? 'active' : '' }}" style="{{ request()->routeIs('admin.group-registrations.*') || request()->routeIs('admin.group-registration-settings.*') ? 'display: block;' : '' }}">
                                        <li class="slide {{ request()->routeIs('admin.group-registrations.index') || request()->routeIs('admin.group-registrations.show') ? 'active' : '' }}">
                                            <a href="{{ route('admin.group-registrations.index') }}" class="side-menu__item {{ request()->routeIs('admin.group-registrations.index') || request()->routeIs('admin.group-registrations.show') ? 'active' : '' }}">جميع التسجيلات</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.group-registrations.whatsapp-report') ? 'active' : '' }}">
                                            <a href="{{ route('admin.group-registrations.whatsapp-report') }}" class="side-menu__item {{ request()->routeIs('admin.group-registrations.whatsapp-report') ? 'active' : '' }}">تقارير رسائل الواتساب</a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>

                        <!-- المالية -->
                        <li class="slide has-sub {{ $financeActive ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ $financeActive ? 'active' : '' }}">
                                <i class="fe fe-credit-card side-menu__icon"></i>
                                <span class="side-menu__label">المالية</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1 {{ $financeActive ? 'active' : '' }}" style="{{ $financeActive ? 'display: block;' : '' }}">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">المالية</a>
                                </li>
                                <li class="slide {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                                    <a href="{{ route('invoices.index') }}" class="side-menu__item {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                                        <i class="fas fa-file-invoice-dollar me-2"></i>الفواتير
                                    </a>
                                </li>
                                <li class="slide {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                                    <a href="{{ route('payments.index') }}" class="side-menu__item {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                                        <i class="fas fa-money-bill-wave me-2"></i>المدفوعات
                                    </a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.settings.payment-whatsapp-message.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.settings.payment-whatsapp-message.edit') }}" class="side-menu__item {{ request()->routeIs('admin.settings.payment-whatsapp-message.*') ? 'active' : '' }}">
                                        <i class="ri-whatsapp-line me-2"></i>إشعار الدفع — واتساب
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- المستخدمون -->
                        <li class="slide has-sub {{ request()->routeIs('users.*') || request()->routeIs('admin.user-sessions.*') || request()->routeIs('admin.user-devices.*') || request()->routeIs('admin.student-profile-cards.*') ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('users.*') || request()->routeIs('admin.user-sessions.*') || request()->routeIs('admin.user-devices.*') || request()->routeIs('admin.student-profile-cards.*') ? 'active' : '' }}">
                                <i class="fe fe-users side-menu__icon"></i>
                                <span class="side-menu__label">المستخدمون</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1 {{ request()->routeIs('users.*') || request()->routeIs('admin.user-sessions.*') || request()->routeIs('admin.user-devices.*') || request()->routeIs('admin.student-profile-cards.*') ? 'active' : '' }}" style="{{ request()->routeIs('users.*') || request()->routeIs('admin.user-sessions.*') || request()->routeIs('admin.user-devices.*') || request()->routeIs('admin.student-profile-cards.*') ? 'display: block;' : '' }}">
                                <li class="slide {{ request()->routeIs('users.index') && !request()->routeIs('users.bulk-import.*') ? 'active' : '' }}">
                                    <a href="{{ route('users.index') }}" class="side-menu__item {{ request()->routeIs('users.index') && !request()->routeIs('users.bulk-import.*') ? 'active' : '' }}">جميع المستخدمين</a>
                                </li>
                                <li class="slide {{ request()->routeIs('users.bulk-import.index') ? 'active' : '' }}">
                                    <a href="{{ route('users.bulk-import.index') }}" class="side-menu__item {{ request()->routeIs('users.bulk-import.index') ? 'active' : '' }}">رفع جماعي من Excel</a>
                                </li>
                                <li class="slide {{ request()->routeIs('users.bulk-import.reports') ? 'active' : '' }}">
                                    <a href="{{ route('users.bulk-import.reports') }}" class="side-menu__item {{ request()->routeIs('users.bulk-import.reports') ? 'active' : '' }}">تقارير الرفع</a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.user-sessions.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.user-sessions.index') }}" class="side-menu__item {{ request()->routeIs('admin.user-sessions.*') ? 'active' : '' }}">
                                        <i class="fas fa-history me-2"></i>جلسات المستخدمين
                                    </a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.user-devices.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.user-devices.index') }}" class="side-menu__item {{ request()->routeIs('admin.user-devices.*') ? 'active' : '' }}">
                                        <i class="fas fa-mobile-alt me-2"></i>أجهزة المستخدمين
                                    </a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.student-profile-cards.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.student-profile-cards.index') }}" class="side-menu__item {{ request()->routeIs('admin.student-profile-cards.*') ? 'active' : '' }}">
                                        <i class="fe fe-credit-card me-2"></i>البطاقات التعريفية
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- الشهادات -->
                        <li class="slide has-sub {{ request()->routeIs('admin.certificates.*') || request()->routeIs('admin.certificate-templates.*') ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('admin.certificates.*') || request()->routeIs('admin.certificate-templates.*') ? 'active' : '' }}">
                                <i class="fe fe-award side-menu__icon"></i>
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

                        <!-- تقييمات المنصة -->
                        <li class="slide {{ request()->routeIs('admin.platform-reviews.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.platform-reviews.index') }}" class="side-menu__item {{ request()->routeIs('admin.platform-reviews.*') ? 'active' : '' }}">
                                <i class="fe fe-star side-menu__icon"></i>
                                <span class="side-menu__label">تقييمات المنصة</span>
                            </a>
                        </li>

                        <!-- التواصل والجدولة -->
                        <li class="slide has-sub {{ $communicationActive ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ $communicationActive ? 'active' : '' }}">
                                <i class="fe fe-bell side-menu__icon"></i>
                                <span class="side-menu__label">التواصل والجدولة</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1 {{ $communicationActive ? 'active' : '' }}" style="{{ $communicationActive ? 'display: block;' : '' }}">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">التواصل والجدولة</a>
                                </li>

                                <li class="slide has-sub {{ request()->routeIs('admin.notifications.*') ? 'open active' : '' }}">
                                    <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                                        <i class="fas fa-bell me-2"></i>الإشعارات
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child2 {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}" style="{{ request()->routeIs('admin.notifications.*') ? 'display: block;' : '' }}">
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

                                <li class="slide has-sub {{ request()->routeIs('admin.reminders.*') ? 'open active' : '' }}">
                                    <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('admin.reminders.*') ? 'active' : '' }}">
                                        <i class="ri-notification-badge-line me-2"></i>التذكيرات
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child2 {{ request()->routeIs('admin.reminders.*') ? 'active' : '' }}" style="{{ request()->routeIs('admin.reminders.*') ? 'display: block;' : '' }}">
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

                                <li class="slide {{ request()->routeIs('admin.calendar.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.calendar.index') }}" class="side-menu__item {{ request()->routeIs('admin.calendar.*') ? 'active' : '' }}">
                                        <i class="ri-calendar-line me-2"></i>التقويم والمواعيد
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- جدول أعمال الطلاب -->
                        <li class="slide {{ request()->routeIs('admin.student-works.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.student-works.index') }}" class="side-menu__item">
                                <i class="fe fe-briefcase side-menu__icon"></i>
                                <span class="side-menu__label">جدول الأعمال</span>
                            </a>
                        </li>

                        <li class="slide {{ request()->routeIs('admin.gifts.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.gifts.index') }}" class="side-menu__item {{ request()->routeIs('admin.gifts.*') ? 'active' : '' }}">
                                <i class="fe fe-package side-menu__icon"></i>
                                <span class="side-menu__label">هدايا الطلاب</span>
                            </a>
                        </li>

                        <!-- المحتوى والوثائق -->
                        <li class="slide has-sub {{ $contentActive ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ $contentActive ? 'active' : '' }}">
                                <i class="fe fe-file-text side-menu__icon"></i>
                                <span class="side-menu__label">المحتوى والوثائق</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1 {{ $contentActive ? 'active' : '' }}" style="{{ $contentActive ? 'display: block;' : '' }}">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">المحتوى والوثائق</a>
                                </li>

                                <li class="slide has-sub {{ request()->routeIs('admin.blog.*') ? 'open active' : '' }}">
                                    <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('admin.blog.*') ? 'active' : '' }}">
                                        <i class="fas fa-blog me-2"></i>المدونة
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child2 {{ request()->routeIs('admin.blog.*') ? 'active' : '' }}" style="{{ request()->routeIs('admin.blog.*') ? 'display: block;' : '' }}">
                                        <li class="slide {{ request()->routeIs('admin.blog.posts.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.blog.posts.index') }}" class="side-menu__item {{ request()->routeIs('admin.blog.posts.*') ? 'active' : '' }}">المقالات</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.blog.ai-posts.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.blog.ai-posts.create') }}" class="side-menu__item {{ request()->routeIs('admin.blog.ai-posts.*') ? 'active' : '' }}">إنشاء مقال بالذكاء الاصطناعي</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.blog.categories.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.blog.categories.index') }}" class="side-menu__item {{ request()->routeIs('admin.blog.categories.*') ? 'active' : '' }}">التصنيفات</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.blog.tags.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.blog.tags.index') }}" class="side-menu__item {{ request()->routeIs('admin.blog.tags.*') ? 'active' : '' }}">الوسوم</a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="slide has-sub {{ request()->routeIs('admin.docs.*') ? 'open active' : '' }}">
                                    <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('admin.docs.*') ? 'active' : '' }}">
                                        <i class="ri-book-open-line me-2"></i>التوثيق
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child2 {{ request()->routeIs('admin.docs.*') ? 'active' : '' }}" style="{{ request()->routeIs('admin.docs.*') ? 'display: block;' : '' }}">
                                        <li class="slide {{ request()->routeIs('admin.docs.pages.index', 'admin.docs.pages.show', 'admin.docs.pages.edit') ? 'active' : '' }}">
                                            <a href="{{ route('admin.docs.pages.index') }}" class="side-menu__item">صفحات التوثيق</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.docs.pages.create') ? 'active' : '' }}">
                                            <a href="{{ route('admin.docs.pages.create') }}" class="side-menu__item">إضافة صفحة</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.docs.ai-pages.create') ? 'active' : '' }}">
                                            <a href="{{ route('admin.docs.ai-pages.create') }}" class="side-menu__item">توليد بالذكاء الاصطناعي</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.docs.ai-pages.enhance') ? 'active' : '' }}">
                                            <a href="{{ route('admin.docs.ai-pages.enhance') }}" class="side-menu__item">إضافة أفكار (AI)</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.docs.ai-pages.improve') ? 'active' : '' }}">
                                            <a href="{{ route('admin.docs.ai-pages.improve') }}" class="side-menu__item">تحسين محتوى (AI)</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.docs.categories.index', 'admin.docs.categories.show', 'admin.docs.categories.edit') ? 'active' : '' }}">
                                            <a href="{{ route('admin.docs.categories.index') }}" class="side-menu__item">أقسام التوثيق</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.docs.categories.create') ? 'active' : '' }}">
                                            <a href="{{ route('admin.docs.categories.create') }}" class="side-menu__item">إضافة قسم</a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="slide has-sub {{ request()->routeIs('admin.lesson-simulators.*') ? 'open active' : '' }}">
                                    <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('admin.lesson-simulators.*') ? 'active' : '' }}">
                                        <i class="fe fe-cpu me-2"></i>محاكيات الدروس
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child2 {{ request()->routeIs('admin.lesson-simulators.*') ? 'active' : '' }}" style="{{ request()->routeIs('admin.lesson-simulators.*') ? 'display: block;' : '' }}">
                                        <li class="slide {{ request()->routeIs('admin.lesson-simulators.index', 'admin.lesson-simulators.edit', 'admin.lesson-simulators.preview') ? 'active' : '' }}">
                                            <a href="{{ route('admin.lesson-simulators.index') }}" class="side-menu__item">جميع المحاكيات</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.lesson-simulators.categories.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.lesson-simulators.categories.index') }}" class="side-menu__item">تصنيفات المحاكيات</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.lesson-simulators.create') ? 'active' : '' }}">
                                            <a href="{{ route('admin.lesson-simulators.create') }}" class="side-menu__item">إنشاء محاكاة</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.lesson-simulators.global-assets') ? 'active' : '' }}">
                                            <a href="{{ route('admin.lesson-simulators.global-assets') }}" class="side-menu__item">CSS/JS مركزي</a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="slide has-sub {{ request()->routeIs('admin.faqs.*') ? 'open active' : '' }}">
                                    <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}">
                                        <i class="fas fa-circle-question me-2"></i>الأسئلة الشائعة
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child2 {{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}" style="{{ request()->routeIs('admin.faqs.*') ? 'display: block;' : '' }}">
                                        <li class="slide {{ request()->routeIs('admin.faqs.index') ? 'active' : '' }}">
                                            <a href="{{ route('admin.faqs.index') }}" class="side-menu__item {{ request()->routeIs('admin.faqs.index') ? 'active' : '' }}">جميع الأسئلة</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.faqs.create') ? 'active' : '' }}">
                                            <a href="{{ route('admin.faqs.create') }}" class="side-menu__item {{ request()->routeIs('admin.faqs.create') ? 'active' : '' }}">إضافة سؤال جديد</a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>

                        <!-- التقارير الأسبوعية -->
                        <li class="slide {{ request()->routeIs('admin.weekly-reports.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.weekly-reports.index') }}" class="side-menu__item {{ request()->routeIs('admin.weekly-reports.*') ? 'active' : '' }}">
                                <i class="fe fe-calendar side-menu__icon"></i>
                                <span class="side-menu__label">التقارير الأسبوعية</span>
                            </a>
                        </li>

                        <!-- الذكاء الاصطناعي -->
                        <li class="slide has-sub {{ request()->routeIs('admin.ai.*') || request()->routeIs('admin.ai-sdk.*') ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('admin.ai.*') || request()->routeIs('admin.ai-sdk.*') ? 'active' : '' }}">
                                <i class="fe fe-cpu side-menu__icon"></i>
                                <span class="side-menu__label">الذكاء الاصطناعي</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1 {{ request()->routeIs('admin.ai.*') || request()->routeIs('admin.ai-sdk.*') ? 'active' : '' }}" style="{{ request()->routeIs('admin.ai.*') || request()->routeIs('admin.ai-sdk.*') ? 'display: block;' : '' }}">
                                <li class="slide {{ request()->routeIs('admin.ai.models.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.ai.models.index') }}" class="side-menu__item {{ request()->routeIs('admin.ai.models.*') ? 'active' : '' }}">موديلات AI</a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.ai-sdk.models.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.ai-sdk.models.index') }}" class="side-menu__item {{ request()->routeIs('admin.ai-sdk.models.*') ? 'active' : '' }}">موديلات Laravel AI SDK</a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.ai.question-generations.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.ai.question-generations.index') }}" class="side-menu__item {{ request()->routeIs('admin.ai.question-generations.*') ? 'active' : '' }}">توليد الأسئلة</a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.ai.question-creation.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.ai.question-creation.create') }}" class="side-menu__item {{ request()->routeIs('admin.ai.question-creation.*') ? 'active' : '' }}">إنشاء أسئلة</a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.ai.question-solutions.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.ai.question-solutions.index') }}" class="side-menu__item {{ request()->routeIs('admin.ai.question-solutions.*') ? 'active' : '' }}">حلول الأسئلة</a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.ai.student-feedback.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.ai.student-feedback.index') }}" class="side-menu__item {{ request()->routeIs('admin.ai.student-feedback.*') ? 'active' : '' }}">ملاحظات الطلاب</a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.ai.student-progress-reports.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.ai.student-progress-reports.index') }}" class="side-menu__item {{ request()->routeIs('admin.ai.student-progress-reports.*') ? 'active' : '' }}">تقارير الدراسة (AI)</a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.ai.settings.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.ai.settings.index') }}" class="side-menu__item {{ request()->routeIs('admin.ai.settings.*') ? 'active' : '' }}">الإعدادات</a>
                                </li>
                            </ul>
                        </li>

                        <!-- التلعيب -->
                        <li class="slide has-sub {{ $gamificationActive ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ $gamificationActive ? 'active' : '' }}">
                                <i class="fe fe-target side-menu__icon"></i>
                                <span class="side-menu__label">التلعيب</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1 {{ $gamificationActive ? 'active' : '' }}" style="{{ $gamificationActive ? 'display: block;' : '' }}">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">التلعيب</a>
                                </li>

                                <li class="slide {{ request()->routeIs('admin.gamification.dashboard') ? 'active' : '' }}">
                                    <a href="{{ route('admin.gamification.dashboard') }}" class="side-menu__item {{ request()->routeIs('admin.gamification.dashboard') ? 'active' : '' }}">
                                        <i class="fas fa-tachometer-alt me-2"></i>لوحة التحكم
                                    </a>
                                </li>

                                <li class="slide has-sub {{ request()->routeIs('admin.gamification.levels.*') || request()->routeIs('admin.gamification.badges.*') || request()->routeIs('admin.gamification.achievements.*') ? 'open active' : '' }}">
                                    <a href="javascript:void(0);" class="side-menu__item">
                                        <i class="fas fa-trophy me-2"></i>المستويات والإنجازات
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child2">
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

                                <li class="slide has-sub {{ request()->routeIs('admin.gamification.points.*') || request()->routeIs('admin.gamification.shop.*') ? 'open active' : '' }}">
                                    <a href="javascript:void(0);" class="side-menu__item">
                                        <i class="fas fa-coins me-2"></i>النقاط والمكافآت
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child2">
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

                                <li class="slide has-sub {{ request()->routeIs('admin.gamification.leaderboards.*') || request()->routeIs('admin.gamification.challenges.*') ? 'open active' : '' }}">
                                    <a href="javascript:void(0);" class="side-menu__item">
                                        <i class="fas fa-crown me-2"></i>المنافسة
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child2">
                                        <li class="slide {{ request()->routeIs('admin.gamification.leaderboards.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.gamification.leaderboards.index') }}" class="side-menu__item {{ request()->routeIs('admin.gamification.leaderboards.*') ? 'active' : '' }}">لوحات المتصدرين</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.gamification.challenges.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.gamification.challenges.index') }}" class="side-menu__item {{ request()->routeIs('admin.gamification.challenges.*') ? 'active' : '' }}">التحديات</a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="slide {{ request()->routeIs('admin.gamification.analytics.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.gamification.analytics.dashboard') }}" class="side-menu__item">
                                        <i class="fas fa-chart-line me-2"></i>التحليلات
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- الإعدادات -->
                        <li class="slide has-sub {{ request()->routeIs('roles.*') || request()->routeIs('payment-methods.*') || request()->routeIs('admin.settings.email.*') || request()->routeIs('admin.email-templates.*') || request()->routeIs('admin.bulk-emails.*') || request()->routeIs('admin.contact-settings.*') || request()->routeIs('admin.google-settings.*') || request()->routeIs('admin.meta-pixel-settings.*') || request()->routeIs('admin.settings.site.*') || request()->routeIs('admin.settings.payment-whatsapp-message.*') || request()->routeIs('admin.settings.password-reset-message.*') || (request()->routeIs('admin.whatsapp-*') || request()->routeIs('admin.flaxxa-wapi.*') || request()->routeIs('admin.evolution-api.*') || request()->routeIs('admin.telegram.*')) || request()->routeIs('admin.webhooks.*') || request()->routeIs('admin.n8n.*') || request()->routeIs('backups.*') || request()->routeIs('backup-*') || request()->routeIs('app-storage.*') || request()->routeIs('storage-disk-mappings.*') || request()->routeIs('admin.database-info.*') ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('roles.*') || request()->routeIs('payment-methods.*') || request()->routeIs('admin.settings.email.*') || request()->routeIs('admin.email-templates.*') || request()->routeIs('admin.bulk-emails.*') || request()->routeIs('admin.contact-settings.*') || request()->routeIs('admin.google-settings.*') || request()->routeIs('admin.meta-pixel-settings.*') || request()->routeIs('admin.settings.site.*') || request()->routeIs('admin.settings.payment-whatsapp-message.*') || request()->routeIs('admin.settings.password-reset-message.*') || (request()->routeIs('admin.whatsapp-*') || request()->routeIs('admin.flaxxa-wapi.*') || request()->routeIs('admin.evolution-api.*') || request()->routeIs('admin.telegram.*')) || request()->routeIs('admin.webhooks.*') || request()->routeIs('admin.n8n.*') || request()->routeIs('backups.*') || request()->routeIs('backup-*') || request()->routeIs('app-storage.*') || request()->routeIs('storage-disk-mappings.*') || request()->routeIs('admin.database-info.*') ? 'active' : '' }}">
                                <i class="fe fe-settings side-menu__icon"></i>
                                <span class="side-menu__label">الإعدادات</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1 {{ request()->routeIs('roles.*') || request()->routeIs('payment-methods.*') || request()->routeIs('admin.settings.email.*') || request()->routeIs('admin.email-templates.*') || request()->routeIs('admin.bulk-emails.*') || request()->routeIs('admin.contact-settings.*') || request()->routeIs('admin.google-settings.*') || request()->routeIs('admin.meta-pixel-settings.*') || request()->routeIs('admin.settings.site.*') || request()->routeIs('admin.settings.payment-whatsapp-message.*') || request()->routeIs('admin.settings.password-reset-message.*') || (request()->routeIs('admin.whatsapp-*') || request()->routeIs('admin.flaxxa-wapi.*') || request()->routeIs('admin.evolution-api.*') || request()->routeIs('admin.telegram.*')) || request()->routeIs('admin.webhooks.*') || request()->routeIs('admin.n8n.*') || request()->routeIs('backups.*') || request()->routeIs('backup-*') || request()->routeIs('app-storage.*') || request()->routeIs('storage-disk-mappings.*') || request()->routeIs('admin.database-info.*') ? 'active' : '' }}" style="{{ request()->routeIs('roles.*') || request()->routeIs('payment-methods.*') || request()->routeIs('admin.settings.email.*') || request()->routeIs('admin.email-templates.*') || request()->routeIs('admin.bulk-emails.*') || request()->routeIs('admin.contact-settings.*') || request()->routeIs('admin.google-settings.*') || request()->routeIs('admin.meta-pixel-settings.*') || request()->routeIs('admin.settings.site.*') || request()->routeIs('admin.settings.payment-whatsapp-message.*') || request()->routeIs('admin.settings.password-reset-message.*') || (request()->routeIs('admin.whatsapp-*') || request()->routeIs('admin.flaxxa-wapi.*') || request()->routeIs('admin.evolution-api.*') || request()->routeIs('admin.telegram.*')) || request()->routeIs('admin.webhooks.*') || request()->routeIs('admin.n8n.*') || request()->routeIs('backups.*') || request()->routeIs('backup-*') || request()->routeIs('app-storage.*') || request()->routeIs('storage-disk-mappings.*') || request()->routeIs('admin.database-info.*') ? 'display: block;' : '' }}">
                                <li class="slide {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                                    <a href="{{ route('roles.index') }}" class="side-menu__item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                                        <i class="fas fa-user-shield me-2"></i>الصلاحيات
                                    </a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.activity-logs.index') }}" class="side-menu__item {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}">
                                        <i class="fe fe-shield me-2"></i>سجل النشاط
                                    </a>
                                </li>
                                <li class="slide {{ request()->routeIs('payment-methods.*') ? 'active' : '' }}">
                                    <a href="{{ route('payment-methods.index') }}" class="side-menu__item {{ request()->routeIs('payment-methods.*') ? 'active' : '' }}">
                                        <i class="fas fa-credit-card me-2"></i>طرق الدفع
                                    </a>
                                </li>

                                <li class="slide has-sub {{ request()->routeIs('admin.settings.email.*') || request()->routeIs('admin.email-templates.*') || request()->routeIs('admin.bulk-emails.*') || request()->routeIs('admin.bulk-emails.settings.*') || request()->routeIs('admin.settings.password-reset-message.*') ? 'open active' : '' }}">
                                    <a href="javascript:void(0);" class="side-menu__item">
                                        <i class="ri-mail-settings-line me-2"></i>إعدادات البريد
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child2 {{ request()->routeIs('admin.settings.email.*') || request()->routeIs('admin.email-templates.*') || request()->routeIs('admin.bulk-emails.*') || request()->routeIs('admin.bulk-emails.settings.*') || request()->routeIs('admin.settings.password-reset-message.*') ? 'active' : '' }}" style="{{ request()->routeIs('admin.settings.email.*') || request()->routeIs('admin.email-templates.*') || request()->routeIs('admin.bulk-emails.*') || request()->routeIs('admin.bulk-emails.settings.*') || request()->routeIs('admin.settings.password-reset-message.*') ? 'display: block;' : '' }}">
                                        <li class="slide {{ request()->routeIs('admin.settings.email.index') ? 'active' : '' }}">
                                            <a href="{{ route('admin.settings.email.index') }}" class="side-menu__item {{ request()->routeIs('admin.settings.email.index') ? 'active' : '' }}">جميع الإعدادات</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.settings.email.create') ? 'active' : '' }}">
                                            <a href="{{ route('admin.settings.email.create') }}" class="side-menu__item {{ request()->routeIs('admin.settings.email.create') ? 'active' : '' }}">إضافة إعدادات</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.email-templates.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.email-templates.index') }}" class="side-menu__item {{ request()->routeIs('admin.email-templates.*') ? 'active' : '' }}">قوالب البريد الإلكتروني</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.bulk-emails.create') ? 'active' : '' }}">
                                            <a href="{{ route('admin.bulk-emails.create') }}" class="side-menu__item {{ request()->routeIs('admin.bulk-emails.create') ? 'active' : '' }}">إرسال بريد جماعي</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.bulk-emails.index') || request()->routeIs('admin.bulk-emails.show') ? 'active' : '' }}">
                                            <a href="{{ route('admin.bulk-emails.index') }}" class="side-menu__item {{ request()->routeIs('admin.bulk-emails.index') || request()->routeIs('admin.bulk-emails.show') ? 'active' : '' }}">سجل الإرسال</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.bulk-emails.settings.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.bulk-emails.settings.index') }}" class="side-menu__item {{ request()->routeIs('admin.bulk-emails.settings.*') ? 'active' : '' }}">إعدادات الإرسال الجماعي</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.settings.password-reset-message.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.settings.password-reset-message.edit') }}" class="side-menu__item {{ request()->routeIs('admin.settings.password-reset-message.*') ? 'active' : '' }}">رسالة استعادة كلمة المرور</a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="slide {{ request()->routeIs('admin.settings.password-reset-message.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.settings.password-reset-message.edit') }}" class="side-menu__item {{ request()->routeIs('admin.settings.password-reset-message.*') ? 'active' : '' }}">
                                        <i class="ri-lock-password-line me-2"></i>رسالة استعادة كلمة المرور
                                    </a>
                                </li>

                                <li class="slide {{ request()->routeIs('admin.contact-settings.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.contact-settings.edit') }}" class="side-menu__item {{ request()->routeIs('admin.contact-settings.*') ? 'active' : '' }}">
                                        <i class="fas fa-address-card me-2"></i>إعدادات صفحة الاتصال
                                    </a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.google-settings.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.google-settings.edit') }}" class="side-menu__item {{ request()->routeIs('admin.google-settings.*') ? 'active' : '' }}">
                                        <i class="fab fa-google me-2"></i>إعدادات Google
                                    </a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.marketing-analytics.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.marketing-analytics.index') }}" class="side-menu__item {{ request()->routeIs('admin.marketing-analytics.*') ? 'active' : '' }}">
                                        <i class="fe fe-bar-chart-2 me-2"></i>إحصائيات التسويق
                                    </a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.meta-pixel-settings.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.meta-pixel-settings.edit') }}" class="side-menu__item {{ request()->routeIs('admin.meta-pixel-settings.*') ? 'active' : '' }}">
                                        <i class="fab fa-facebook me-2"></i>Facebook Pixel
                                    </a>
                                </li>
                                <li class="slide {{ request()->routeIs('admin.settings.site.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.settings.site.index') }}" class="side-menu__item {{ request()->routeIs('admin.settings.site.*') ? 'active' : '' }}">
                                        <i class="ri-settings-3-line me-2"></i>إعدادات الموقع
                                    </a>
                                </li>

                                @php
                                    $whatsappMenuActive = request()->routeIs('admin.whatsapp-*')
                                        || request()->routeIs('admin.flaxxa-wapi.*')
                                        || request()->routeIs('admin.evolution-api.*')
                                        || request()->routeIs('admin.settings.payment-whatsapp-message.*')
                                        || request()->routeIs('admin.settings.password-reset-message.*');
                                    $evolutionMenuActive = request()->routeIs('admin.evolution-api.*')
                                        || request()->routeIs('admin.whatsapp-web-settings.*')
                                        || request()->routeIs('admin.whatsapp-messages.*')
                                        || request()->routeIs('admin.whatsapp-templates.*')
                                        || request()->routeIs('admin.settings.payment-whatsapp-message.*')
                                        || request()->routeIs('admin.settings.password-reset-message.*');
                                    $flaxxaMenuActive = request()->routeIs('admin.flaxxa-wapi.*');
                                    $telegramMenuActive = request()->routeIs('admin.telegram.*');
                                @endphp
                                <li class="slide has-sub {{ $whatsappMenuActive ? 'open active' : '' }}">
                                    <a href="javascript:void(0);" class="side-menu__item {{ $whatsappMenuActive ? 'active' : '' }}">
                                        <i class="ri-whatsapp-line me-2"></i>WhatsApp
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child2 {{ $whatsappMenuActive ? 'active' : '' }}" style="{{ $whatsappMenuActive ? 'display: block;' : '' }}">
                                        <li class="slide {{ request()->routeIs('admin.whatsapp-settings.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.whatsapp-settings.index') }}" class="side-menu__item {{ request()->routeIs('admin.whatsapp-settings.*') ? 'active' : '' }}">الإعدادات العامة</a>
                                        </li>

                                        <li class="slide has-sub {{ $evolutionMenuActive ? 'open active' : '' }}">
                                            <a href="javascript:void(0);" class="side-menu__item {{ $evolutionMenuActive ? 'active' : '' }}">
                                                <i class="ri-plug-line me-2 text-success"></i>Evolution API
                                                <i class="fe fe-chevron-right side-menu__angle"></i>
                                            </a>
                                            <ul class="slide-menu child2 {{ $evolutionMenuActive ? 'active' : '' }}" style="{{ $evolutionMenuActive ? 'display: block;' : '' }}">
                                                <li class="slide {{ request()->routeIs('admin.evolution-api.settings.*') ? 'active' : '' }}">
                                                    <a href="{{ route('admin.evolution-api.settings.index') }}" class="side-menu__item {{ request()->routeIs('admin.evolution-api.settings.*') ? 'active' : '' }}">إعدادات Evolution</a>
                                                </li>
                                                <li class="slide {{ request()->routeIs('admin.evolution-api.instances.*') ? 'active' : '' }}">
                                                    <a href="{{ route('admin.evolution-api.instances.index') }}" class="side-menu__item {{ request()->routeIs('admin.evolution-api.instances.*') ? 'active' : '' }}">Instances</a>
                                                </li>
                                                <li class="slide {{ request()->routeIs('admin.evolution-api.send.*') ? 'active' : '' }}">
                                                    <a href="{{ route('admin.evolution-api.send.text') }}" class="side-menu__item {{ request()->routeIs('admin.evolution-api.send.*') ? 'active' : '' }}">إرسال Evolution</a>
                                                </li>
                                                <li class="slide {{ request()->routeIs('admin.evolution-api.groups.*') && !request()->routeIs('admin.evolution-api.groups.compare*') ? 'active' : '' }}">
                                                    <a href="{{ route('admin.evolution-api.groups.index') }}" class="side-menu__item {{ request()->routeIs('admin.evolution-api.groups.index') || request()->routeIs('admin.evolution-api.groups.show') || request()->routeIs('admin.evolution-api.groups.members') ? 'active' : '' }}">المجموعات</a>
                                                </li>
                                                <li class="slide {{ request()->routeIs('admin.evolution-api.groups.compare*') ? 'active' : '' }}">
                                                    <a href="{{ route('admin.evolution-api.groups.compare') }}" class="side-menu__item {{ request()->routeIs('admin.evolution-api.groups.compare*') ? 'active' : '' }}">مقارنة WA</a>
                                                </li>
                                                <li class="slide {{ request()->routeIs('admin.evolution-api.contacts.*') ? 'active' : '' }}">
                                                    <a href="{{ route('admin.evolution-api.contacts.index') }}" class="side-menu__item {{ request()->routeIs('admin.evolution-api.contacts.*') ? 'active' : '' }}">جهات الاتصال</a>
                                                </li>
                                                <li class="slide {{ request()->routeIs('admin.evolution-api.webhook.*') ? 'active' : '' }}">
                                                    <a href="{{ route('admin.evolution-api.webhook.index') }}" class="side-menu__item {{ request()->routeIs('admin.evolution-api.webhook.*') ? 'active' : '' }}">Webhook Evolution</a>
                                                </li>
                                                <li class="slide {{ request()->routeIs('admin.whatsapp-web-settings.*') ? 'active' : '' }}">
                                                    <a href="{{ route('admin.whatsapp-web-settings.index') }}" class="side-menu__item {{ request()->routeIs('admin.whatsapp-web-settings.*') ? 'active' : '' }}">WhatsApp Web</a>
                                                </li>
                                                <li class="slide {{ request()->routeIs('admin.whatsapp-messages.index') || request()->routeIs('admin.whatsapp-messages.show') || request()->routeIs('admin.whatsapp-messages.create') ? 'active' : '' }}">
                                                    <a href="{{ route('admin.whatsapp-messages.index') }}" class="side-menu__item {{ request()->routeIs('admin.whatsapp-messages.index') || request()->routeIs('admin.whatsapp-messages.show') || request()->routeIs('admin.whatsapp-messages.create') ? 'active' : '' }}">الرسائل</a>
                                                </li>
                                                <li class="slide {{ request()->routeIs('admin.whatsapp-messages.broadcasts.*') ? 'active' : '' }}">
                                                    <a href="{{ route('admin.whatsapp-messages.broadcasts.index') }}" class="side-menu__item {{ request()->routeIs('admin.whatsapp-messages.broadcasts.*') ? 'active' : '' }}">تقارير الإرسال الجماعي</a>
                                                </li>
                                                <li class="slide {{ request()->routeIs('admin.whatsapp-templates.*') ? 'active' : '' }}">
                                                    <a href="{{ route('admin.whatsapp-templates.index') }}" class="side-menu__item {{ request()->routeIs('admin.whatsapp-templates.*') ? 'active' : '' }}">قوالب الرسائل</a>
                                                </li>
                                                <li class="slide {{ request()->routeIs('admin.settings.phone-otp.*') ? 'active' : '' }}">
                                                    <a href="{{ route('admin.settings.phone-otp.edit') }}" class="side-menu__item {{ request()->routeIs('admin.settings.phone-otp.*') ? 'active' : '' }}">OTP — واتساب Flaxxa</a>
                                                </li>
                                                <li class="slide {{ request()->routeIs('admin.settings.payment-whatsapp-message.*') ? 'active' : '' }}">
                                                    <a href="{{ route('admin.settings.payment-whatsapp-message.edit') }}" class="side-menu__item {{ request()->routeIs('admin.settings.payment-whatsapp-message.*') ? 'active' : '' }}">إشعار الدفع — واتساب</a>
                                                </li>
                                                <li class="slide {{ request()->routeIs('admin.settings.password-reset-message.*') ? 'active' : '' }}">
                                                    <a href="{{ route('admin.settings.password-reset-message.edit') }}" class="side-menu__item {{ request()->routeIs('admin.settings.password-reset-message.*') ? 'active' : '' }}">استعادة كلمة المرور — واتساب/بريد</a>
                                                </li>
                                            </ul>
                                        </li>

                                        <li class="slide has-sub {{ $flaxxaMenuActive ? 'open active' : '' }}">
                                            <a href="javascript:void(0);" class="side-menu__item {{ $flaxxaMenuActive ? 'active' : '' }}">
                                                <i class="ri-cloud-line me-2 text-muted"></i>Flaxxa (WAPI)
                                                <i class="fe fe-chevron-right side-menu__angle"></i>
                                            </a>
                                            <ul class="slide-menu child2 {{ $flaxxaMenuActive ? 'active' : '' }}" style="{{ $flaxxaMenuActive ? 'display: block;' : '' }}">
                                                <li class="slide {{ request()->routeIs('admin.flaxxa-wapi.settings.*') ? 'active' : '' }}">
                                                    <a href="{{ route('admin.flaxxa-wapi.settings.index') }}" class="side-menu__item {{ request()->routeIs('admin.flaxxa-wapi.settings.*') ? 'active' : '' }}">إعدادات Flaxxa</a>
                                                </li>
                                                <li class="slide {{ request()->routeIs('admin.settings.phone-otp.*') ? 'active' : '' }}">
                                                    <a href="{{ route('admin.settings.phone-otp.edit') }}" class="side-menu__item {{ request()->routeIs('admin.settings.phone-otp.*') ? 'active' : '' }}">OTP — واتساب</a>
                                                </li>
                                                <li class="slide {{ request()->routeIs('admin.flaxxa-wapi.messages.*') ? 'active' : '' }}">
                                                    <a href="{{ route('admin.flaxxa-wapi.messages.index') }}" class="side-menu__item {{ request()->routeIs('admin.flaxxa-wapi.messages.*') ? 'active' : '' }}">سجل Flaxxa</a>
                                                </li>
                                                <li class="slide {{ request()->routeIs('admin.flaxxa-wapi.send.message') ? 'active' : '' }}">
                                                    <a href="{{ route('admin.flaxxa-wapi.send.message') }}" class="side-menu__item {{ request()->routeIs('admin.flaxxa-wapi.send.message') ? 'active' : '' }}">إرسال نص Flaxxa</a>
                                                </li>
                                                <li class="slide {{ request()->routeIs('admin.flaxxa-wapi.send.template') ? 'active' : '' }}">
                                                    <a href="{{ route('admin.flaxxa-wapi.send.template') }}" class="side-menu__item {{ request()->routeIs('admin.flaxxa-wapi.send.template') ? 'active' : '' }}">إرسال قالب Flaxxa</a>
                                                </li>
                                                <li class="slide {{ request()->routeIs('admin.flaxxa-wapi.send.campaign') ? 'active' : '' }}">
                                                    <a href="{{ route('admin.flaxxa-wapi.send.campaign') }}" class="side-menu__item {{ request()->routeIs('admin.flaxxa-wapi.send.campaign') ? 'active' : '' }}">حملة Flaxxa</a>
                                                </li>
                                                <li class="slide {{ request()->routeIs('admin.flaxxa-wapi.templates.*') ? 'active' : '' }}">
                                                    <a href="{{ route('admin.flaxxa-wapi.templates.index') }}" class="side-menu__item {{ request()->routeIs('admin.flaxxa-wapi.templates.*') ? 'active' : '' }}">قوالب Flaxxa المحلية</a>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>

                                <li class="slide has-sub {{ $telegramMenuActive ? 'open active' : '' }}">
                                    <a href="javascript:void(0);" class="side-menu__item {{ $telegramMenuActive ? 'active' : '' }}">
                                        <i class="ri-telegram-line me-2"></i>Telegram
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child2 {{ $telegramMenuActive ? 'active' : '' }}" style="{{ $telegramMenuActive ? 'display: block;' : '' }}">
                                        <li class="slide {{ request()->routeIs('admin.telegram.settings.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.telegram.settings.index') }}" class="side-menu__item {{ request()->routeIs('admin.telegram.settings.*') ? 'active' : '' }}">الإعدادات</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.telegram.send*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.telegram.send') }}" class="side-menu__item {{ request()->routeIs('admin.telegram.send*') ? 'active' : '' }}">إرسال رسالة</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.telegram.broadcast') || request()->routeIs('admin.telegram.broadcast.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.telegram.broadcast') }}" class="side-menu__item {{ request()->routeIs('admin.telegram.broadcast') || request()->routeIs('admin.telegram.broadcast.*') ? 'active' : '' }}">بث جماعي</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.telegram.broadcasts.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.telegram.broadcasts.index') }}" class="side-menu__item {{ request()->routeIs('admin.telegram.broadcasts.*') ? 'active' : '' }}">تقارير البث</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.telegram.templates.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.telegram.templates.index') }}" class="side-menu__item {{ request()->routeIs('admin.telegram.templates.*') ? 'active' : '' }}">قوالب الرسائل</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.telegram.groups.link*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.telegram.groups.link') }}" class="side-menu__item {{ request()->routeIs('admin.telegram.groups.link*') ? 'active' : '' }}">ربط مجموعة / قناة</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.telegram.groups.post*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.telegram.groups.post') }}" class="side-menu__item {{ request()->routeIs('admin.telegram.groups.post*') ? 'active' : '' }}">نشر في مجموعة</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('admin.telegram.groups.compare*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.telegram.groups.compare') }}" class="side-menu__item {{ request()->routeIs('admin.telegram.groups.compare*') ? 'active' : '' }}">مقارنة الأعضاء</a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="slide has-sub {{ request()->routeIs('admin.webhooks.*') || request()->routeIs('admin.n8n.*') ? 'open active' : '' }}">
                                    <a href="javascript:void(0);" class="side-menu__item">
                                        <i class="fas fa-exchange-alt me-2"></i>الويب هوكس
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child2 {{ request()->routeIs('admin.webhooks.*') || request()->routeIs('admin.n8n.*') ? 'active' : '' }}" style="{{ request()->routeIs('admin.webhooks.*') || request()->routeIs('admin.n8n.*') ? 'display: block;' : '' }}">
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

                                <li class="slide has-sub {{ request()->routeIs('backups.*') || request()->routeIs('backup-*') ? 'open active' : '' }}">
                                    <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('backups.*') || request()->routeIs('backup-*') ? 'active' : '' }}">
                                        <i class="ri-database-2-line me-2"></i>النسخ الاحتياطية
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child2 {{ request()->routeIs('backups.*') || request()->routeIs('backup-*') ? 'active' : '' }}" style="{{ request()->routeIs('backups.*') || request()->routeIs('backup-*') ? 'display: block;' : '' }}">
                                        <li class="slide {{ request()->routeIs('backups.index') ? 'active' : '' }}">
                                            <a href="{{ route('backups.index') }}" class="side-menu__item {{ request()->routeIs('backups.index') ? 'active' : '' }}">قائمة النسخ</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('backups.create') ? 'active' : '' }}">
                                            <a href="{{ route('backups.create') }}" class="side-menu__item {{ request()->routeIs('backups.create') ? 'active' : '' }}">نسخة جديدة</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('backup-schedules.*') ? 'active' : '' }}">
                                            <a href="{{ route('backup-schedules.index') }}" class="side-menu__item {{ request()->routeIs('backup-schedules.*') ? 'active' : '' }}">الجدولة</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('app-storage.configs.*') ? 'active' : '' }}">
                                            <a href="{{ route('app-storage.configs.index') }}" class="side-menu__item {{ request()->routeIs('app-storage.configs.*') ? 'active' : '' }}">إعدادات التخزين</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('app-storage.analytics') ? 'active' : '' }}">
                                            <a href="{{ route('app-storage.analytics') }}" class="side-menu__item {{ request()->routeIs('app-storage.analytics') ? 'active' : '' }}">إحصائيات التخزين</a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="slide has-sub {{ request()->routeIs('app-storage.*') || request()->routeIs('storage-disk-mappings.*') ? 'open active' : '' }}">
                                    <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('app-storage.*') || request()->routeIs('storage-disk-mappings.*') ? 'active' : '' }}">
                                        <i class="ri-cloud-line me-2"></i>التخزين السحابي
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child2 {{ request()->routeIs('app-storage.*') || request()->routeIs('storage-disk-mappings.*') ? 'active' : '' }}" style="{{ request()->routeIs('app-storage.*') || request()->routeIs('storage-disk-mappings.*') ? 'display: block;' : '' }}">
                                        <li class="slide {{ request()->routeIs('app-storage.configs.*') ? 'active' : '' }}">
                                            <a href="{{ route('app-storage.configs.index') }}" class="side-menu__item {{ request()->routeIs('app-storage.configs.*') ? 'active' : '' }}">إعدادات التخزين</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('app-storage.analytics') ? 'active' : '' }}">
                                            <a href="{{ route('app-storage.analytics') }}" class="side-menu__item {{ request()->routeIs('app-storage.analytics') ? 'active' : '' }}">الإحصائيات</a>
                                        </li>
                                        <li class="slide {{ request()->routeIs('storage-disk-mappings.*') ? 'active' : '' }}">
                                            <a href="{{ route('storage-disk-mappings.index') }}" class="side-menu__item {{ request()->routeIs('storage-disk-mappings.*') ? 'active' : '' }}">ربط الأقراص</a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="slide {{ request()->routeIs('admin.database-info.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.database-info.index') }}" class="side-menu__item {{ request()->routeIs('admin.database-info.*') ? 'active' : '' }}">
                                        <i class="ri-database-2-line me-2"></i>معلومات قاعدة البيانات
                                    </a>
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
