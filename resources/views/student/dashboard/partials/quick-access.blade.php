@php
    $quickLinks = [
        ['route' => 'student.courses.my-courses', 'icon' => 'fe-book', 'color' => 'primary', 'title' => 'كورساتي', 'subtitle' => 'الكورسات المسجّلة'],
        ['route' => 'student.external-resources.index', 'icon' => 'fe-link', 'color' => 'info', 'title' => 'الموارد الخارجية', 'subtitle' => 'روابط ومراجع'],
        ['route' => 'student.question-module.stats.index', 'icon' => 'fe-help-circle', 'color' => 'info', 'title' => 'إحصائيات الاختبارات', 'subtitle' => 'الأداء والمحاولات'],
        ['route' => 'student.training-camps.index', 'icon' => 'fe-flag', 'color' => 'success', 'title' => 'المعسكرات التدريبية', 'subtitle' => 'التسجيل والمتابعة'],
        ['route' => 'student.groups.index', 'icon' => 'fe-users', 'color' => 'info', 'title' => 'المجموعات', 'subtitle' => 'مجموعاتي الدراسية'],
        ['route' => 'student.invoices.index', 'icon' => 'fe-file-text', 'color' => 'danger', 'title' => 'فواتيري', 'subtitle' => 'الفواتير والمدفوعات'],
        ['route' => 'gamification.badges.index', 'icon' => 'fe-award', 'color' => 'warning', 'title' => 'شاراتي', 'subtitle' => 'الإنجازات المكتسبة'],
        ['route' => 'gamification.leaderboards.index', 'icon' => 'fe-bar-chart-2', 'color' => 'primary', 'title' => 'لوحة المتصدرين', 'subtitle' => 'ترتيب الطلاب'],
        ['route' => 'student.progress.overview', 'icon' => 'fe-trending-up', 'color' => 'secondary', 'title' => 'تقدمي في الكورسات', 'subtitle' => 'نسب الإنجاز'],
        ['route' => 'gamification.dashboard', 'icon' => 'fe-zap', 'color' => 'orange', 'title' => 'لوحة التلعيب', 'subtitle' => 'النقاط والتحديات'],
    ];
@endphp

<div class="card custom-card admin-shortcuts-panel dashboard-fade-in mt-2">
    <div class="card-header border-0 pb-2">
        <div class="d-flex align-items-start gap-2">
            <span class="avatar avatar-sm bg-warning-transparent">
                <i class="fe fe-zap text-warning"></i>
            </span>
            <div>
                <h5 class="card-title mb-1">روابط سريعة</h5>
                <p class="text-muted fs-12 mb-0">الوصول السريع لأهم صفحات التعلم والمتابعة</p>
            </div>
        </div>
    </div>
    <div class="card-body pt-2">
        <div class="row g-3">
            @foreach ($quickLinks as $index => $link)
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 dashboard-stagger-item" style="--stagger-delay: {{ $index * 35 }}ms">
                    <a href="{{ route($link['route']) }}" class="admin-quick-link text-decoration-none d-block h-100">
                        <span class="admin-quick-link__icon bg-{{ $link['color'] }}-transparent">
                            <i class="fe {{ $link['icon'] }} text-{{ $link['color'] }}"></i>
                        </span>
                        <span class="admin-quick-link__title">{{ $link['title'] }}</span>
                        <span class="admin-quick-link__subtitle">{{ $link['subtitle'] }}</span>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
