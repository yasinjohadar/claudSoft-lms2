<div class="col-12 student-my-courses-stagger d-lg-none" style="--stagger-delay: {{ ($index ?? 0) * 40 }}ms">
    <article class="student-progress-section-card">
        <div class="student-progress-section-card__header">
            <span class="avatar avatar-sm bg-primary-transparent rounded-circle">
                <i class="fe fe-folder text-primary"></i>
            </span>
            <div class="flex-fill min-w-0">
                <h6 class="student-progress-section-card__title mb-0">{{ $sectionData['section']->title }}</h6>
            </div>
            @include('student.progress.partials.show-section-status', ['sectionData' => $sectionData])
        </div>

        <div class="student-progress-section-card__meta">
            <span class="badge bg-primary-transparent fs-11">
                {{ $sectionData['completed_modules'] }} / {{ $sectionData['total_modules'] }} محتوى
            </span>
        </div>

        <div class="student-progress-section-card__progress">
            @include('student.progress.partials.show-section-progress', ['sectionData' => $sectionData])
        </div>
    </article>
</div>
