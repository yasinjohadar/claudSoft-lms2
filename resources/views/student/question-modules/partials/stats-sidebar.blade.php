@php
    $passRate = $totalAttempts > 0 ? round(($passedAttempts / $totalAttempts) * 100, 1) : 0;
    $avgMinutes = $totalAttempts > 0 ? round(($totalTimeSpent / $totalAttempts) / 60) : 0;

    $quickRows = [
        ['icon' => 'fe-book-open', 'color' => 'primary', 'label' => 'اختبارات مختلفة', 'value' => $uniqueModules],
        ['icon' => 'fe-x-circle', 'color' => 'danger', 'label' => 'محاولات راسبة', 'value' => $failedAttempts],
        ['icon' => 'fe-trending-up', 'color' => 'success', 'label' => 'معدل النجاح', 'value' => $passRate, 'suffix' => '%', 'decimals' => true],
        ['icon' => 'fe-clock', 'color' => 'warning', 'label' => 'متوسط الوقت', 'value' => $avgMinutes, 'suffix' => ' د'],
    ];
@endphp

@if($bestAttempt)
    <div class="card custom-card dashboard-today-card dashboard-fade-in student-qm-stats-best">
        <div class="card-body">
            <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="avatar avatar-md bg-warning-transparent">
                        <i class="fe fe-award text-warning"></i>
                    </span>
                    <div>
                        <h4 class="card-title mb-1">أفضل أداء</h4>
                        <p class="fs-12 text-muted mb-0">أعلى نتيجة حققتها.</p>
                    </div>
                </div>
                <span class="badge bg-warning-transparent text-warning fs-12">
                    {{ number_format($bestAttempt->percentage, 1) }}%
                </span>
            </div>
            <p class="fw-semibold mb-2">{{ Str::limit($bestAttempt->questionModule->title ?? 'اختبار', 60) }}</p>
            <small class="text-muted">
                <i class="fe fe-calendar me-1"></i>{{ $bestAttempt->completed_at->format('Y-m-d') }}
            </small>
        </div>
    </div>
@endif

<div class="card custom-card dashboard-today-card dashboard-fade-in">
    <div class="card-header border-0 pb-0">
        <div class="d-flex align-items-center gap-2">
            <span class="avatar avatar-sm bg-info-transparent">
                <i class="fe fe-info text-info"></i>
            </span>
            <div>
                <h4 class="card-title mb-1">معلومات سريعة</h4>
                <p class="fs-12 text-muted mb-0">ملخص أدائك التفصيلي.</p>
            </div>
        </div>
    </div>
    <div class="card-body pt-3">
        @foreach ($quickRows as $index => $row)
            <div class="dashboard-stat-row dashboard-stagger-item d-flex align-items-center justify-content-between gap-3"
                 style="--stagger-delay: {{ $index * 50 }}ms">
                <div class="d-flex align-items-center gap-3 min-w-0">
                    <span class="avatar avatar-sm bg-{{ $row['color'] }}-transparent flex-shrink-0">
                        <i class="fe {{ $row['icon'] }} text-{{ $row['color'] }}"></i>
                    </span>
                    <span class="fs-13">{{ $row['label'] }}</span>
                </div>
                <span class="fw-semibold fs-15 flex-shrink-0"
                      data-countup="{{ $row['value'] }}"
                      @if(!empty($row['suffix'])) data-countup-suffix="{{ $row['suffix'] }}" @endif
                      @if(!empty($row['decimals'])) data-countup-decimals="1" @endif>0</span>
            </div>
        @endforeach
    </div>
</div>

@if($availableModules->count() > 0)
    <div class="card custom-card group-show-members-card dashboard-fade-in">
        <div class="card-header border-0 pb-0">
            <div class="d-flex align-items-center gap-2">
                <span class="avatar avatar-sm bg-success-transparent">
                    <i class="fe fe-play-circle text-success"></i>
                </span>
                <div>
                    <h4 class="card-title mb-1">اختبارات متاحة</h4>
                    <p class="fs-12 text-muted mb-0">ابدأ محاولة جديدة الآن.</p>
                </div>
            </div>
        </div>
        <div class="card-body pt-3 px-0 pb-0">
            <div class="list-group list-group-flush">
                @foreach($availableModules->take(5) as $module)
                    <a href="{{ route('student.question-module.start', $module->id) }}"
                       class="list-group-item list-group-item-action border-0 px-4 py-3">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <div class="min-w-0">
                                <div class="fw-semibold fs-13 text-truncate">{{ Str::limit($module->title, 36) }}</div>
                                <small class="text-muted">
                                    <i class="fe fe-help-circle me-1"></i>{{ $module->questions->count() }} سؤال
                                </small>
                            </div>
                            <i class="fe fe-arrow-left text-primary flex-shrink-0"></i>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endif

<div class="card custom-card student-qm-stats-motivation dashboard-fade-in">
    <div class="card-body text-center py-4">
        <span class="avatar avatar-lg bg-primary-transparent mb-3">
            <i class="fe fe-zap text-primary fs-20"></i>
        </span>
        <h5 class="mb-2">استمر في التفوق!</h5>
        <p class="text-muted mb-0 fs-13">أنت تقوم بعمل رائع. استمر في التدريب والممارسة لتحسين نتائجك.</p>
    </div>
</div>
