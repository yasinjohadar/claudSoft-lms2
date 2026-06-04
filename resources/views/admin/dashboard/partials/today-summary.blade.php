@php
    $todayRows = [
        ['icon' => 'fe-user-plus', 'color' => 'primary', 'label' => 'مستخدمون جدد', 'value' => $todayStats['new_users'] ?? 0],
        ['icon' => 'fe-user-check', 'color' => 'success', 'label' => 'التحاقات جديدة', 'value' => $todayStats['new_enrollments'] ?? 0],
        ['icon' => 'fe-check-circle', 'color' => 'info', 'label' => 'كورسات أُكملت اليوم', 'value' => $todayStats['completed_today'] ?? 0],
        ['icon' => 'fe-file-text', 'color' => 'warning', 'label' => 'محاولات اختبارات جديدة', 'value' => $learningStats['quiz_attempts'] ?? 0],
    ];
@endphp

<div class="col-lg-12 col-xl-5 mb-3">
    <div class="card custom-card dashboard-today-card h-100">
        <div class="card-header border-0 pb-0">
            <h4 class="card-title mb-1">ملخص اليوم</h4>
            <p class="fs-12 text-muted mb-0">أهم أرقام اليوم الحالي في النظام.</p>
        </div>
        <div class="card-body pt-3">
            @foreach ($todayRows as $index => $row)
                <div class="dashboard-stat-row dashboard-stagger-item d-flex align-items-center justify-content-between gap-3" style="--stagger-delay: {{ $index * 60 }}ms">
                    <div class="d-flex align-items-center gap-3 min-w-0">
                        <span class="avatar avatar-sm bg-{{ $row['color'] }}-transparent flex-shrink-0">
                            <i class="fe {{ $row['icon'] }} text-{{ $row['color'] }}"></i>
                        </span>
                        <span class="fs-13 text-truncate">{{ $row['label'] }}</span>
                    </div>
                    <span class="fw-semibold fs-15 flex-shrink-0 ms-2" data-countup="{{ $row['value'] }}">0</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
