@php
    $cover = $showcase->cover_image;
    $isAbsoluteCover = is_string($cover) && (str_starts_with($cover, 'http://') || str_starts_with($cover, 'https://') || str_starts_with($cover, '/'));
    $coverUrl = $cover
        ? ($isAbsoluteCover ? $cover : asset('storage/' . ltrim($cover, '/')))
        : null;
@endphp

<div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 student-my-courses-stagger" style="--stagger-delay: {{ ($index ?? 0) * 50 }}ms">
    <article class="student-course-card student-community-showcase-card h-100">
        <div class="student-course-card__media">
            <div class="student-course-card__placeholder {{ $coverUrl ? 'student-course-card__placeholder--under' : '' }}">
                <span class="student-course-card__placeholder-icon">
                    <i class="fe fe-globe"></i>
                </span>
            </div>
            @if($coverUrl)
                <img src="{{ $coverUrl }}"
                     alt="{{ $showcase->title }}"
                     class="student-course-card__img"
                     loading="lazy"
                     onerror="this.style.display='none'">
            @endif

            @if($showcase->challenge)
                <span class="student-course-card__badge bg-primary-transparent text-primary">
                    <i class="fe fe-layers me-1"></i>{{ Str::limit($showcase->challenge->title, 22) }}
                </span>
            @endif
        </div>

        <div class="student-course-card__body">
            <h5 class="student-course-card__title">
                <a href="{{ route('student.community-projects.show', $showcase->slug) }}">{{ $showcase->title }}</a>
            </h5>

            @if($showcase->summary)
                <p class="text-muted fs-13 mb-3">{{ Str::limit($showcase->summary, 100) }}</p>
            @endif

            <div class="row g-2 mb-3 student-course-card__stats">
                <div class="col-6">
                    <div class="student-course-card__stat">
                        <span class="student-course-card__stat-value">{{ $showcase->all_comments_count ?? 0 }}</span>
                        <span class="student-course-card__stat-label">تعليق</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="student-course-card__stat">
                        <span class="student-course-card__stat-value text-warning">
                            {{ number_format((float) ($showcase->avg_rating ?? 0), 1) }}
                        </span>
                        <span class="student-course-card__stat-label">تقييم</span>
                    </div>
                </div>
            </div>

            <div class="student-course-card__meta">
                @if($showcase->team?->name)
                    <span><i class="fe fe-users me-1"></i>{{ $showcase->team->name }}</span>
                @endif
                @if($showcase->published_at)
                    <span><i class="fe fe-calendar me-1"></i>{{ $showcase->published_at->format('Y-m-d') }}</span>
                @endif
            </div>

            <div class="student-course-card__actions">
                <a href="{{ route('student.community-projects.show', $showcase->slug) }}"
                   class="btn btn-primary btn-sm rounded-pill">
                    <i class="fe fe-eye me-1"></i>عرض المشروع
                </a>
                @if($showcase->demo_url)
                    <a href="{{ $showcase->demo_url }}" target="_blank" rel="noopener"
                       class="btn btn-outline-success btn-sm rounded-pill">
                        <i class="fe fe-external-link me-1"></i>Demo
                    </a>
                @endif
            </div>
        </div>
    </article>
</div>
