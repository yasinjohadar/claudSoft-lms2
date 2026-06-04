<div class="col-12 student-quizzes-stagger d-lg-none" style="--stagger-delay: {{ ($index ?? 0) * 40 }}ms">
    <article class="student-quiz-attempt-card">
        <div class="student-quiz-attempt-card__header">
            <div class="flex-fill min-w-0">
                <h6 class="student-quiz-attempt-card__title mb-1">{{ $attempt->quiz?->title ?? 'غير محدد' }}</h6>
                @if($attempt->quiz && $attempt->quiz->course)
                    <span class="badge bg-info-transparent fs-11">
                        <i class="fe fe-book me-1"></i>{{ $attempt->quiz->course->title }}
                    </span>
                @endif
            </div>
            <span class="badge bg-secondary-transparent fs-11">#{{ $attempt->attempt_number }}</span>
        </div>

        <div class="student-quiz-attempt-card__dates">
            @if($attempt->started_at)
                <span><i class="fe fe-calendar me-1"></i>بدء: {{ $attempt->started_at->format('Y/m/d H:i') }}</span>
            @endif
            @if($attempt->submitted_at)
                <span><i class="fe fe-check me-1"></i>تسليم: {{ $attempt->submitted_at->format('Y/m/d H:i') }}</span>
            @endif
        </div>

        <div class="student-quiz-attempt-card__result">
            @include('student.pages.quizzes.partials.review-result', ['attempt' => $attempt])
        </div>

        <div class="student-quiz-attempt-card__footer">
            @include('student.pages.quizzes.partials.review-status-badge', ['attempt' => $attempt])
            @include('student.pages.quizzes.partials.review-actions', ['attempt' => $attempt])
        </div>
    </article>
</div>
