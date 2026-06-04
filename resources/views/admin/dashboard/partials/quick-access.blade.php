<div class="card custom-card admin-shortcuts-panel dashboard-fade-in mt-2">
    <div class="card-header border-0 pb-2">
        <div class="d-flex align-items-start gap-2">
            <span class="avatar avatar-sm bg-warning-transparent">
                <i class="fe fe-zap text-warning"></i>
            </span>
            <div>
                <h5 class="card-title mb-1">اختصارات سريعة</h5>
                <p class="text-muted fs-12 mb-0">الوصول السريع لأهم صفحات إدارة المنصة</p>
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
