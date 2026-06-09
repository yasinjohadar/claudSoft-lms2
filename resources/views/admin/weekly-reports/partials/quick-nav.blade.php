@php
    $links = [
        'overview' => [
            'route' => 'admin.weekly-reports.groups-overview',
            'icon' => 'fe-layers',
            'title' => 'تقارير المجموعات',
            'subtitle' => 'المنشأة والمسلّمة',
            'color' => 'info',
        ],
        'all' => [
            'route' => 'admin.weekly-reports.all',
            'icon' => 'fe-list',
            'title' => 'كافة التقارير',
            'subtitle' => 'كل الحالات',
            'color' => 'primary',
        ],
        'created' => [
            'route' => 'admin.weekly-reports.created',
            'icon' => 'fe-file-plus',
            'title' => 'التقارير المنشأة',
            'subtitle' => 'دفعات + تفاصيل الطلاب',
            'color' => 'secondary',
        ],
        'submitted' => [
            'route' => 'admin.weekly-reports.index',
            'icon' => 'fe-check-square',
            'title' => 'التقارير المسلّمة',
            'subtitle' => 'مرسل ومراجع',
            'color' => 'success',
        ],
        'pending' => [
            'route' => 'admin.weekly-reports.pending',
            'icon' => 'fe-clock',
            'title' => 'بانتظار التسليم',
            'subtitle' => 'لم يُسلّموا بعد',
            'color' => 'warning',
        ],
        'create' => [
            'route' => 'admin.weekly-reports.create',
            'icon' => 'fe-plus-circle',
            'title' => 'إنشاء يدوي',
            'subtitle' => 'تقرير لمجموعة',
            'color' => 'primary',
        ],
        'schedules' => [
            'route' => 'admin.weekly-reports.schedules.index',
            'icon' => 'fe-calendar',
            'title' => 'الجدولة',
            'subtitle' => 'تقارير تلقائية',
            'color' => 'warning',
        ],
    ];
    $active = $navActive ?? 'overview';
@endphp

<div class="card custom-card admin-shortcuts-panel dashboard-fade-in mb-4">
    <div class="card-header border-0 pb-2">
        <div class="d-flex align-items-start gap-2">
            <span class="avatar avatar-sm bg-primary-transparent">
                <i class="fe fe-navigation text-primary"></i>
            </span>
            <div>
                <h5 class="card-title mb-1">التقارير الأسبوعية</h5>
                <p class="text-muted fs-12 mb-0">تنقل سريع بين صفحات إدارة تقارير الطلاب</p>
            </div>
        </div>
    </div>
    <div class="card-body pt-2">
        <div class="row g-3">
            @foreach ($links as $key => $link)
                <div class="col-xl col-lg-4 col-md-6 col-sm-12 dashboard-stagger-item" style="--stagger-delay: {{ $loop->index * 40 }}ms">
                    <a href="{{ route($link['route']) }}"
                       class="admin-quick-link text-decoration-none d-block h-100 {{ $active === $key ? 'border-primary shadow-sm' : '' }}">
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
