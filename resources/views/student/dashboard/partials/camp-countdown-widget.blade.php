@php
    $group = $membership->group;
    $hasEndDate = $group->end_date !== null;
    $daysRemaining = $hasEndDate
        ? max(0, (int) now()->startOfDay()->diffInDays($group->end_date->copy()->startOfDay(), false))
        : null;

    $statusClass = 'primary';
    $statusLabel = 'قادم';
    $countdownLabel = 'يوم متبقي';

    if ($group->hasEnded()) {
        $statusClass = 'secondary';
        $statusLabel = 'منتهي';
        $countdownLabel = 'انتهى';
    } elseif ($group->isOngoing()) {
        $statusClass = 'success';
        $statusLabel = 'جاري';
        $countdownLabel = $daysRemaining === null ? 'مستمر' : ($daysRemaining === 0 ? 'ينتهي اليوم' : 'يوم متبقي');
    } elseif ($daysRemaining === 0) {
        $countdownLabel = 'ينتهي اليوم';
    }

    $formatDate = fn ($date) => $date?->locale('ar')->translatedFormat('j F Y') ?? '—';
@endphp

<div class="{{ $columnClass ?? 'col-xl-6 col-lg-6 col-md-6 col-sm-12' }} dashboard-stagger-item" style="--stagger-delay: {{ $staggerDelay ?? 0 }}ms">
    <a href="{{ route('student.training-camps.show', $group->id) }}"
       class="student-camp-widget student-camp-widget--{{ $statusClass }} text-decoration-none d-block h-100">
        <div class="student-camp-widget__header">
            <span class="student-camp-widget__icon bg-{{ $statusClass }}-transparent">
                <i class="fe fe-flag text-{{ $statusClass }}"></i>
            </span>
            <div class="student-camp-widget__title-wrap">
                <span class="student-camp-widget__title">{{ $group->name }}</span>
                <span class="badge bg-{{ $statusClass }}-transparent text-{{ $statusClass }} fs-10">
                    <i class="fe fe-clock me-1"></i>{{ $statusLabel }}
                </span>
            </div>
            <span class="student-camp-widget__link ms-auto">
                <i class="fe fe-arrow-left"></i>
            </span>
        </div>

        <div class="student-camp-widget__body">
            <div class="student-camp-widget__countdown-wrap">
                <div class="student-camp-widget__countdown-ring">
                    <span class="student-camp-widget__countdown-value student-camp-widget__countdown-value--danger">{{ $daysRemaining ?? 0 }}</span>
                </div>
                <span class="student-camp-widget__countdown-label">{{ $countdownLabel }}</span>
            </div>

            <ul class="student-camp-widget__dates list-unstyled mb-0">
                <li>
                    <i class="fe fe-play-circle text-muted"></i>
                    <span class="text-muted">بداية المعسكر:</span>
                    <span>{{ $formatDate($group->start_date) }}</span>
                </li>
                <li>
                    <i class="fe fe-flag text-muted"></i>
                    <span class="text-muted">نهاية المعسكر:</span>
                    <span>{{ $hasEndDate ? $formatDate($group->end_date) : 'بلا تاريخ نهاية' }}</span>
                </li>
            </ul>
        </div>
    </a>
</div>
