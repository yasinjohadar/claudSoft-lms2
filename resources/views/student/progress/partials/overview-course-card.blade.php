@php
    $course = $progress['course'];
    $enrollment = $progress['enrollment'];
    $pct = (float) ($progress['completion_percentage'] ?? 0);
    $courseId = $course?->id;
    $courseImage = $course?->thumbnail ?? $course?->image ?? null;

    $statusMap = [
        'completed' => ['class' => 'primary', 'icon' => 'fe-check-circle', 'label' => 'مكتمل'],
        'active' => ['class' => 'success', 'icon' => 'fe-play', 'label' => 'نشط'],
        'suspended' => ['class' => 'warning', 'icon' => 'fe-pause', 'label' => 'معلق'],
    ];
    $status = $statusMap[$progress['status']] ?? ['class' => 'secondary', 'icon' => 'fe-book', 'label' => $progress['status']];
@endphp

<div class="col-xl-6 col-lg-12 student-my-courses-stagger" style="--stagger-delay: {{ ($index ?? 0) * 50 }}ms">
    <article class="student-course-card student-progress-card h-100">
        <div class="student-course-card__media student-progress-card__media">
            @if($course && $courseImage)
                <img src="{{ course_image_url($courseImage) }}"
                     alt="{{ $course->title }}"
                     class="student-course-card__img"
                     loading="lazy"
                     onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                <div class="student-course-card__placeholder d-none">
                    <span class="student-course-card__placeholder-icon">
                        <i class="fe fe-bar-chart-2"></i>
                    </span>
                </div>
            @else
                <div class="student-course-card__placeholder">
                    <span class="student-course-card__placeholder-icon">
                        <i class="fe fe-bar-chart-2"></i>
                    </span>
                </div>
            @endif
            <span class="student-course-card__badge bg-{{ $status['class'] }}-transparent text-{{ $status['class'] }}">
                <i class="fe {{ $status['icon'] }} me-1"></i>{{ $status['label'] }}
            </span>
            <div class="student-progress-card__ring" style="--progress: {{ max(0, min(100, $pct)) }}">
                <span class="student-progress-card__ring-value">{{ number_format($pct, 1) }}%</span>
            </div>
        </div>

        <div class="student-course-card__body">
            <h5 class="student-course-card__title">
                @if($course)
                    <a href="{{ route('student.courses.show', $courseId) }}">{{ $course->title }}</a>
                @else
                    <span>كورس غير متوفر</span>
                @endif
            </h5>

            @if($course)
                <p class="student-progress-card__instructor mb-3">
                    <i class="fe fe-user me-1"></i>{{ $course->instructor->name ?? 'غير محدد' }}
                </p>
            @endif

            <div class="student-course-card__progress">
                <div class="d-flex justify-content-between align-items-center student-course-card__progress-header">
                    <small class="text-muted">نسبة الإكمال</small>
                    <small class="fw-semibold text-primary">{{ number_format($pct, 1) }}%</small>
                </div>
                <div class="student-course-card__progress-track">
                    <div class="student-course-card__progress-bar"
                         style="width: {{ max(0, min(100, $pct)) }}%"
                         role="progressbar"
                         aria-valuenow="{{ $pct }}"
                         aria-valuemin="0"
                         aria-valuemax="100"></div>
                </div>
            </div>

            <div class="student-course-card__meta">
                @if($progress['last_accessed'])
                    <span><i class="fe fe-clock me-1"></i>آخر دخول: {{ $progress['last_accessed']->diffForHumans() }}</span>
                @else
                    <span><i class="fe fe-info me-1"></i>لم يتم الدخول بعد</span>
                @endif
            </div>

            <div class="student-course-card__actions">
                @if($course)
                    <a href="{{ route('student.progress.show', $courseId) }}" class="btn btn-primary btn-sm rounded-pill">
                        <i class="fe fe-bar-chart-2 me-1"></i>عرض التفاصيل
                    </a>
                    @if($enrollment->certificate_issued)
                        <a href="{{ route('student.progress.certificate', $courseId) }}"
                           class="btn btn-outline-success btn-sm rounded-pill">
                            <i class="fe fe-award me-1"></i>الشهادة
                        </a>
                    @endif
                @endif
            </div>
        </div>
    </article>
</div>
