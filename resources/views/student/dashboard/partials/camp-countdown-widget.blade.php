@php
    $camp = $enrollment->camp;
    $daysRemaining = max(0, (int) now()->startOfDay()->diffInDays($camp->end_date->copy()->startOfDay(), false));

    $statusClass = 'primary';
    $statusLabel = 'قادم';
    if ($camp->isOngoing()) {
        $statusClass = 'success';
        $statusLabel = 'جاري';
    }

    $campEnrollmentDate = $enrollment->enrollment_date ?? $enrollment->created_at;
    $formatDate = fn ($date) => $date?->locale('ar')->translatedFormat('j F Y') ?? '—';
@endphp

<div class="{{ $columnClass ?? 'col-xl-6 col-lg-6 col-md-6 col-sm-12' }} dashboard-stagger-item" style="--stagger-delay: {{ $staggerDelay ?? 0 }}ms">
    <a href="{{ route('student.training-camps.show', $camp->slug) }}"
       class="student-camp-widget student-camp-widget--{{ $statusClass }} text-decoration-none d-block h-100">
        <div class="student-camp-widget__header">
            <span class="student-camp-widget__icon bg-{{ $statusClass }}-transparent">
                <i class="fe fe-flag text-{{ $statusClass }}"></i>
            </span>
            <div class="student-camp-widget__title-wrap">
                <span class="student-camp-widget__title">{{ $camp->name }}</span>
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
                    <span class="student-camp-widget__countdown-value"
                          data-countup="{{ $daysRemaining }}">0</span>
                </div>
                <span class="student-camp-widget__countdown-label">
                    @if ($daysRemaining === 0)
                        ينتهي اليوم
                    @else
                        يوم متبقي
                    @endif
                </span>
            </div>

            <ul class="student-camp-widget__dates list-unstyled mb-0">
                <li>
                    <i class="fe fe-user-plus text-muted"></i>
                    <span class="text-muted">المنصة:</span>
                    <span>{{ $formatDate($platformJoinedAt) }}</span>
                </li>
                <li>
                    <i class="fe fe-check-circle text-muted"></i>
                    <span class="text-muted">المعسكر:</span>
                    <span>{{ $formatDate($campEnrollmentDate) }}</span>
                </li>
                <li>
                    <i class="fe fe-calendar text-muted"></i>
                    <span class="text-muted">الفترة:</span>
                    <span>{{ $formatDate($camp->start_date) }} — {{ $formatDate($camp->end_date) }}</span>
                </li>
            </ul>
        </div>
    </a>
</div>
