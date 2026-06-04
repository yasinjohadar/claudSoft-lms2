<div class="col-md-12 col-lg-12 col-xl-7 mb-3">
    <div class="card custom-card dashboard-chart-card h-100">
        <div class="card-header pb-0 border-0">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="card-title mb-1">تطور الالتحاقات خلال آخر 6 أشهر</h4>
                    <p class="fs-12 text-muted mb-0">
                        عدد الالتحاقات بالكورسات شهرياً خلال الفترة الأخيرة.
                    </p>
                </div>
                <a href="javascript:void(0);" class="btn btn-icon btn-sm btn-light bg-transparent rounded-pill" data-bs-toggle="dropdown">
                    <i class="fe fe-more-horizontal"></i>
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ route('enrollments.all') }}">عرض كل الالتحاقات</a>
                </div>
            </div>
        </div>
        <div class="card-body pt-2">
            <div id="enrollments-chart"
                 data-labels='@json($chartData['enrollments']['labels'] ?? [])'
                 data-values='@json($chartData['enrollments']['data'] ?? [])'>
            </div>
        </div>
    </div>
</div>
