@php
    $typeLabels = [
        'team_project' => 'مشروع فريق',
        'open_challenge' => 'تحدي مفتوح',
        'hackathon' => 'هاكاثون',
        'capstone' => 'مشروع تخرج',
    ];
    $diffLabels = ['easy' => 'سهل', 'medium' => 'متوسط', 'hard' => 'صعب', 'expert' => 'خبير'];
    $diffClasses = ['easy' => 'success', 'medium' => 'info', 'hard' => 'warning', 'expert' => 'danger'];

    $cover = $challenge->cover_image;
    $isAbsoluteCover = is_string($cover) && (str_starts_with($cover, 'http://') || str_starts_with($cover, 'https://') || str_starts_with($cover, '/'));
    $coverUrl = $cover
        ? ($isAbsoluteCover ? $cover : asset('storage/' . ltrim($cover, '/')))
        : null;

    $diffClass = $diffClasses[$challenge->difficulty] ?? 'primary';
@endphp

<div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 student-my-courses-stagger" style="--stagger-delay: {{ ($index ?? 0) * 50 }}ms">
    <article class="student-course-card student-project-challenge-card h-100">
        <div class="student-course-card__media">
            <div class="student-course-card__placeholder {{ $coverUrl ? 'student-course-card__placeholder--under' : '' }}">
                <span class="student-course-card__placeholder-icon">
                    <i class="fe fe-layers"></i>
                </span>
            </div>
            @if($coverUrl)
                <img src="{{ $coverUrl }}"
                     alt="{{ $challenge->title }}"
                     class="student-course-card__img"
                     loading="lazy"
                     onerror="this.style.display='none'">
            @endif

            <span class="student-course-card__badge bg-{{ $diffClass }}-transparent text-{{ $diffClass }}">
                <i class="fe fe-activity me-1"></i>{{ $diffLabels[$challenge->difficulty] ?? $challenge->difficulty }}
            </span>
        </div>

        <div class="student-course-card__body">
            <div class="d-flex flex-wrap gap-1 mb-2">
                <span class="badge bg-primary-transparent fs-11">
                    {{ $typeLabels[$challenge->project_type] ?? $challenge->project_type }}
                </span>
                @if($challenge->is_featured)
                    <span class="badge bg-warning-transparent text-warning fs-11">
                        <i class="fe fe-star me-1"></i>مميز
                    </span>
                @endif
                @if($myTeam)
                    <span class="badge bg-success-transparent text-success fs-11">
                        <i class="fe fe-check me-1"></i>منضم
                    </span>
                @endif
            </div>

            <h5 class="student-course-card__title">
                <a href="{{ route('student.project-challenges.show', $challenge->id) }}">{{ $challenge->title }}</a>
            </h5>

            <p class="text-muted fs-13 mb-3">
                {{ Str::limit(strip_tags($challenge->summary ?? $challenge->description), 100) }}
            </p>

            <div class="row g-2 mb-3 student-course-card__stats">
                <div class="col-6">
                    <div class="student-course-card__stat">
                        <span class="student-course-card__stat-value">{{ $challenge->teams_count ?? 0 }}</span>
                        <span class="student-course-card__stat-label">فريق</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="student-course-card__stat">
                        <span class="student-course-card__stat-value text-primary">{{ $challenge->points_total ?? 0 }}</span>
                        <span class="student-course-card__stat-label">نقطة</span>
                    </div>
                </div>
            </div>

            @if($challenge->skills->isNotEmpty() || $challenge->technologies->isNotEmpty())
                <div class="d-flex flex-wrap gap-1 mb-3">
                    @foreach($challenge->skills->take(2) as $skill)
                        <span class="badge bg-light text-dark fs-11">{{ $skill->name }}</span>
                    @endforeach
                    @foreach($challenge->technologies->take(2) as $tech)
                        <span class="badge bg-primary-transparent fs-11">{{ $tech->name }}</span>
                    @endforeach
                </div>
            @endif

            <div class="student-course-card__meta">
                @if($challenge->expected_duration)
                    <span><i class="fe fe-clock me-1"></i>{{ $challenge->expected_duration }}</span>
                @endif
                @if($challenge->ends_at)
                    <span><i class="fe fe-calendar me-1"></i>ينتهي {{ $challenge->ends_at->format('Y-m-d') }}</span>
                @endif
            </div>

            <div class="student-course-card__actions">
                @if($myTeam)
                    <a href="{{ route('student.project-teams.workspace', $myTeam->id) }}"
                       class="btn btn-success btn-sm rounded-pill">
                        <i class="fe fe-monitor me-1"></i>مساحة العمل
                    </a>
                @else
                    <a href="{{ route('student.project-challenges.show', $challenge->id) }}"
                       class="btn btn-primary btn-sm rounded-pill">
                        <i class="fe fe-eye me-1"></i>عرض التفاصيل
                    </a>
                @endif
            </div>
        </div>
    </article>
</div>
