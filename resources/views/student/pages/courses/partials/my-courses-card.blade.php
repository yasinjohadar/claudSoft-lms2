@php
    $course = $enrollment->course;
    $progress = $enrollment->completion_percentage ?? 0;
    $courseId = $enrollment->course_id;
    $courseImage = $course->thumbnail ?? $course->image ?? null;

    $statusMap = [
        'active' => ['class' => 'success', 'icon' => 'fe-play', 'label' => 'قيد الدراسة'],
        'completed' => ['class' => 'primary', 'icon' => 'fe-check-circle', 'label' => 'مكتمل'],
        'suspended' => ['class' => 'warning', 'icon' => 'fe-pause', 'label' => 'متوقف'],
    ];
    $status = $statusMap[$enrollment->enrollment_status] ?? ['class' => 'secondary', 'icon' => 'fe-book', 'label' => $enrollment->enrollment_status];
@endphp

<div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 student-my-courses-stagger" style="--stagger-delay: {{ (($index ?? 0) % 16) * 50 }}ms">
    <article class="student-course-card h-100">
        <div class="student-course-card__media">
            <div class="student-course-card__placeholder {{ $course && $courseImage ? 'student-course-card__placeholder--under' : '' }}">
                <span class="student-course-card__placeholder-icon">
                    <i class="fe fe-book-open"></i>
                </span>
            </div>
            @if($course && $courseImage)
                <img src="{{ course_image_url($courseImage) }}"
                     alt="{{ $course->title }}"
                     class="student-course-card__img"
                     loading="lazy"
                     onerror="this.style.display='none'">
            @endif
            <span class="student-course-card__badge bg-{{ $status['class'] }}-transparent text-{{ $status['class'] }}">
                <i class="fe {{ $status['icon'] }} me-1"></i>{{ $status['label'] }}
            </span>
        </div>

        <div class="student-course-card__body">
            @if($course && $course->category)
                <span class="student-course-card__category">{{ $course->category->name }}</span>
            @endif

            <h5 class="student-course-card__title">
                <a href="{{ route('student.courses.show', $courseId) }}">
                    {{ $course ? $course->title : 'كورس غير متوفر' }}
                </a>
            </h5>

            <div class="student-course-card__progress">
                <div class="d-flex justify-content-between align-items-center student-course-card__progress-header">
                    <small class="text-muted">نسبة الإنجاز</small>
                    <small class="fw-semibold text-primary">{{ number_format($progress, 0) }}%</small>
                </div>
                <div class="student-course-card__progress-track">
                    <div class="student-course-card__progress-bar"
                         style="width: {{ max(0, min(100, (float) $progress)) }}%"
                         role="progressbar"
                         aria-valuenow="{{ $progress }}"
                         aria-valuemin="0"
                         aria-valuemax="100"></div>
                </div>
            </div>

            <div class="row g-2 mb-3 student-course-card__stats">
                <div class="col-6">
                    <div class="student-course-card__stat">
                        <span class="student-course-card__stat-value">{{ $course?->sections_count ?? 0 }}</span>
                        <span class="student-course-card__stat-label">أقسام</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="student-course-card__stat">
                        <span class="student-course-card__stat-value text-success">{{ $course?->modules_count ?? 0 }}</span>
                        <span class="student-course-card__stat-label">دروس</span>
                    </div>
                </div>
            </div>

            <div class="student-course-card__meta">
                <span><i class="fe fe-calendar me-1"></i>{{ $enrollment->enrollment_date->format('Y-m-d') }}</span>
                @if($enrollment->last_accessed)
                    <span><i class="fe fe-clock me-1"></i>{{ $enrollment->last_accessed->diffForHumans() }}</span>
                @endif
            </div>

            <div class="student-course-card__actions">
                @if($enrollment->enrollment_status == 'completed')
                    <a href="{{ route('student.courses.show', $courseId) }}" class="btn btn-outline-primary btn-sm rounded-pill">
                        <i class="fe fe-eye me-1"></i>مراجعة الكورس
                    </a>
                    @if($course && $course->certificate_enabled)
                        <a href="{{ route('student.progress.certificate', $courseId) }}"
                           class="btn btn-warning btn-sm rounded-pill"
                           target="_blank">
                            <i class="fe fe-award me-1"></i>تحميل الشهادة
                        </a>
                    @endif
                @else
                    <a href="{{ route('student.courses.show', $courseId) }}" class="btn btn-primary btn-sm rounded-pill">
                        <i class="fe fe-play me-1"></i>متابعة التعلم
                    </a>
                @endif
                <a href="{{ route('student.progress.show', $courseId) }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                    <i class="fe fe-bar-chart-2 me-1"></i>التقدم التفصيلي
                </a>
            </div>
        </div>
    </article>
</div>
