@php
    $passRate = ($questionModuleStats['total_attempts'] ?? 0) > 0
        ? round((($questionModuleStats['passed_attempts'] ?? 0) / $questionModuleStats['total_attempts']) * 100)
        : 0;

    $summaryRows = [
        ['icon' => 'fe-book-open', 'color' => 'primary', 'label' => 'كورسات مسجّلة', 'value' => $courseStats['total_courses'] ?? 0],
        ['icon' => 'fe-play-circle', 'color' => 'info', 'label' => 'قيد التقدم', 'value' => $courseStats['in_progress'] ?? 0],
        ['icon' => 'fe-check-circle', 'color' => 'success', 'label' => 'كورسات مكتملة', 'value' => $courseStats['completed'] ?? 0],
        ['icon' => 'fe-file-text', 'color' => 'warning', 'label' => 'محاولات اختبار', 'value' => $questionModuleStats['total_attempts'] ?? 0],
    ];
@endphp

<div class="card custom-card dashboard-today-card dashboard-fade-in">
    <div class="card-header border-0 pb-0">
        <div class="d-flex align-items-start gap-2">
            <span class="avatar avatar-sm bg-warning-transparent">
                <i class="fe fe-bell text-warning"></i>
            </span>
            <div>
                <h4 class="card-title mb-1">تنبيهات مهمة</h4>
                <p class="fs-12 text-muted mb-0">متابعة سريعة لحالتك الحالية.</p>
            </div>
        </div>
    </div>
    <div class="card-body pt-3">
        <div class="group-show-empty py-3">
            <i class="fe fe-check-circle text-success fs-2 mb-2 d-block"></i>
            <p class="text-muted mb-0 fs-13">لا توجد تنبيهات جديدة</p>
        </div>
    </div>
</div>

<div class="card custom-card dashboard-today-card dashboard-fade-in mt-3">
    <div class="card-header border-0 pb-0">
        <div class="d-flex align-items-start gap-2">
            <span class="avatar avatar-sm bg-warning-transparent">
                <i class="fe fe-award text-warning"></i>
            </span>
            <div>
                <h4 class="card-title mb-1">آخر الشارات</h4>
                <p class="fs-12 text-muted mb-0">إنجازاتك الأخيرة في التلعيب.</p>
            </div>
        </div>
    </div>
    <div class="card-body pt-3">
        <div class="group-show-empty py-3">
            <i class="fe fe-award group-show-empty__icon" style="width: 48px; height: 48px; font-size: 1.25rem;"></i>
            <p class="text-muted mb-2 fs-13">لم تحصل على شارات بعد</p>
            <a href="{{ route('gamification.badges.index') }}" class="btn btn-sm btn-warning-light">
                استكشف الشارات
            </a>
        </div>
    </div>
</div>

<div class="card custom-card dashboard-today-card dashboard-fade-in mt-3">
    <div class="card-header border-0 pb-0">
        <h4 class="card-title mb-1">ملخص التعلم</h4>
        <p class="fs-12 text-muted mb-0">نظرة سريعة على تقدمك.</p>
    </div>
    <div class="card-body pt-3">
        @foreach ($summaryRows as $index => $row)
            <div class="dashboard-stat-row dashboard-stagger-item d-flex align-items-center justify-content-between gap-3"
                 style="--stagger-delay: {{ $index * 60 }}ms">
                <div class="d-flex align-items-center gap-3 min-w-0">
                    <span class="avatar avatar-sm bg-{{ $row['color'] }}-transparent flex-shrink-0">
                        <i class="fe {{ $row['icon'] }} text-{{ $row['color'] }}"></i>
                    </span>
                    <span class="fs-13 text-truncate">{{ $row['label'] }}</span>
                </div>
                <span class="fw-semibold fs-15 flex-shrink-0 ms-2" data-countup="{{ $row['value'] }}">0</span>
            </div>
        @endforeach

        <div class="border-top mt-3 pt-3">
            <div class="d-flex align-items-center gap-2 pb-2">
                <span class="avatar avatar-sm bg-success-transparent">
                    <i class="fe fe-check-circle text-success"></i>
                </span>
                <p class="mb-0 fs-13">نسبة النجاح في الاختبارات ({{ $passRate }}%)</p>
            </div>
            <div class="progress progress-style progress-sm mb-2">
                <div class="progress-bar bg-success" style="width: {{ $passRate }}%"></div>
            </div>
            <div class="d-flex justify-content-between fs-12 text-muted">
                <span>ناجحة: {{ $questionModuleStats['passed_attempts'] ?? 0 }}</span>
                <span>متوسط: {{ $questionModuleStats['average_score'] ?? 0 }}%</span>
            </div>
        </div>
    </div>
</div>
