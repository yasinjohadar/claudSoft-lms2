<div class="col-12 student-my-courses-stagger d-lg-none" style="--stagger-delay: {{ ($index ?? 0) * 40 }}ms">
    <article class="student-study-report-card">
        <div class="student-study-report-card__header">
            <span class="avatar avatar-sm bg-primary-transparent rounded-circle">
                <i class="fe fe-cpu text-primary"></i>
            </span>
            <div class="min-w-0 flex-fill">
                <h6 class="student-study-report-card__title mb-1">{{ $report->course?->title ?? '—' }}</h6>
                @if($report->courseGroup)
                    <span class="badge bg-info-transparent fs-11">{{ $report->courseGroup->name }}</span>
                @endif
            </div>
        </div>
        <p class="student-study-report-card__date mb-3">
            <i class="fe fe-calendar me-1"></i>{{ $report->created_at?->format('Y-m-d H:i') ?? '—' }}
        </p>
        <a href="{{ route('student.progress.ai-reports.show', $report) }}" class="btn btn-primary rounded-pill w-100">
            <i class="fe fe-eye me-1"></i>عرض التقرير
        </a>
    </article>
</div>
