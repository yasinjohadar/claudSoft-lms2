@php
    $campStatus = 'upcoming';
    $campStatusLabel = 'قادم';
    $campStatusClass = 'primary';

    if ($camp->hasEnded()) {
        $campStatus = 'completed';
        $campStatusLabel = 'منتهي';
        $campStatusClass = 'secondary';
    } elseif ($camp->isOngoing()) {
        $campStatus = 'ongoing';
        $campStatusLabel = 'جاري';
        $campStatusClass = 'success';
    }

    $isEnrolled = in_array($camp->id, $userEnrollments ?? [], true);
@endphp

<div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 student-my-courses-stagger" style="--stagger-delay: {{ ($index ?? 0) * 50 }}ms">
    <article class="student-course-card student-camp-card h-100">
        <div class="student-course-card__media">
            <div class="student-course-card__placeholder {{ $camp->image ? 'student-course-card__placeholder--under' : '' }}">
                <span class="student-course-card__placeholder-icon">
                    <i class="fe fe-flag"></i>
                </span>
            </div>
            @if($camp->image)
                <img src="{{ asset('storage/' . $camp->image) }}"
                     alt="{{ $camp->name }}"
                     class="student-course-card__img"
                     loading="lazy"
                     onerror="this.style.display='none'">
            @endif

            <span class="student-camp-card__price">${{ number_format($camp->price, 2) }}</span>

            <span class="student-course-card__badge bg-{{ $campStatusClass }}-transparent text-{{ $campStatusClass }}">
                <i class="fe fe-clock me-1"></i>{{ $campStatusLabel }}
            </span>
        </div>

        <div class="student-course-card__body">
            <div class="d-flex flex-wrap gap-1 mb-2">
                @if($camp->category)
                    <span class="student-course-card__category">{{ $camp->category->name }}</span>
                @endif
                @if($camp->is_featured)
                    <span class="badge bg-warning-transparent text-warning fs-11">
                        <i class="fe fe-star me-1"></i>مميز
                    </span>
                @endif
                @if($isEnrolled)
                    <span class="badge bg-success-transparent text-success fs-11">
                        <i class="fe fe-check me-1"></i>مسجّل
                    </span>
                @endif
            </div>

            <h5 class="student-course-card__title">
                <a href="{{ route('student.training-camps.show', $camp->slug) }}">{{ $camp->name }}</a>
            </h5>

            @if($camp->description)
                <p class="text-muted fs-13 mb-3">{{ Str::limit($camp->description, 90) }}</p>
            @endif

            <div class="row g-2 mb-3 student-course-card__stats">
                <div class="col-6">
                    <div class="student-course-card__stat">
                        <span class="student-course-card__stat-value">{{ $camp->duration_days }}</span>
                        <span class="student-course-card__stat-label">يوم</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="student-course-card__stat">
                        <span class="student-course-card__stat-value text-success">
                            {{ $camp->availableSeats() ?? '∞' }}
                        </span>
                        <span class="student-course-card__stat-label">مقعد متبقي</span>
                    </div>
                </div>
            </div>

            <div class="student-course-card__meta">
                @if($camp->instructor_name)
                    <span><i class="fe fe-user me-1"></i>{{ $camp->instructor_name }}</span>
                @endif
                <span><i class="fe fe-calendar me-1"></i>{{ $camp->start_date->format('Y-m-d') }}</span>
                @if($camp->location)
                    <span><i class="fe fe-map-pin me-1"></i>{{ $camp->location }}</span>
                @endif
            </div>

            <div class="student-course-card__actions">
                <a href="{{ route('student.training-camps.show', $camp->slug) }}"
                   class="btn btn-primary btn-sm rounded-pill">
                    <i class="fe fe-eye me-1"></i>عرض التفاصيل
                </a>
                @if($isEnrolled)
                    <a href="{{ route('student.training-camps.my-enrollments') }}"
                       class="btn btn-outline-success btn-sm rounded-pill">
                        <i class="fe fe-check-circle me-1"></i>تسجيلاتي
                    </a>
                @endif
            </div>
        </div>
    </article>
</div>
