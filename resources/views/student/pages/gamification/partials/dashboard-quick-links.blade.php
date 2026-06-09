@php
    $quickLinks = [
        ['route' => 'gamification.badges.index', 'icon' => 'fe-award', 'color' => 'warning', 'title' => 'الشارات', 'subtitle' => 'مجموعتك'],
        ['route' => 'gamification.challenges.index', 'icon' => 'fe-target', 'color' => 'primary', 'title' => 'التحديات', 'subtitle' => 'أكمل المهام'],
        ['route' => 'gamification.leaderboards.index', 'icon' => 'fe-bar-chart-2', 'color' => 'info', 'title' => 'المتصدرون', 'subtitle' => 'الترتيب'],
        ['route' => 'gamification.points.index', 'icon' => 'fe-star', 'color' => 'success', 'title' => 'النقاط', 'subtitle' => 'السجل والمكافآت'],
        ['route' => 'gamification.streak.index', 'icon' => 'fe-zap', 'color' => 'orange', 'title' => 'السلسلة', 'subtitle' => 'أيام النشاط'],
        ['route' => 'gamification.shop.index', 'icon' => 'fe-shopping-bag', 'color' => 'cyan', 'title' => 'المتجر', 'subtitle' => 'استبدل نقاطك'],
    ];
@endphp

<div class="card custom-card admin-shortcuts-panel dashboard-fade-in mb-4">
    <div class="card-header border-0 pb-2">
        <div class="d-flex align-items-start gap-2">
            <span class="avatar avatar-sm bg-primary-transparent">
                <i class="fe fe-grid text-primary"></i>
            </span>
            <div>
                <h5 class="card-title mb-1">روابط سريعة</h5>
                <p class="text-muted fs-12 mb-0">انتقل بسرعة لأقسام التلعيب</p>
            </div>
        </div>
    </div>
    <div class="card-body pt-2">
        <div class="row g-3">
            @foreach ($quickLinks as $index => $link)
                <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 dashboard-stagger-item" style="--stagger-delay: {{ $index * 35 }}ms">
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
