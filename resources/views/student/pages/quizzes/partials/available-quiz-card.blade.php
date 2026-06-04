@php
    $typeMap = [
        'practice' => ['class' => 'info', 'label' => 'تدريبي'],
        'graded' => ['class' => 'warning', 'label' => 'مُقيّم'],
        'final_exam' => ['class' => 'danger', 'label' => 'نهائي'],
    ];
    $type = $typeMap[$quiz->quiz_type] ?? ['class' => 'secondary', 'label' => $quiz->quiz_type];
    $bestPct = $quiz->best_attempt ? (float) $quiz->best_attempt->percentage_score : 0;
    $passed = $quiz->best_attempt && $quiz->best_attempt->passed;
@endphp

<div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 student-quizzes-stagger" style="--stagger-delay: {{ ($index ?? 0) * 50 }}ms">
    <article class="student-quiz-available-card">
        <div class="student-quiz-available-card__header">
            <div class="d-flex justify-content-between align-items-start gap-2">
                <div class="min-w-0 flex-grow-1">
                    <h6 class="student-quiz-available-card__title">
                        <a href="{{ route('student.quizzes.show', $quiz->id) }}">{{ $quiz->title }}</a>
                    </h6>
                    @if($quiz->course)
                        <span class="student-quiz-available-card__course">
                            <i class="fe fe-book text-primary"></i>
                            {{ Str::limit($quiz->course->title, 40) }}
                        </span>
                    @endif
                </div>
                <span class="student-quiz-available-card__type badge bg-{{ $type['class'] }}-transparent text-{{ $type['class'] }}">
                    {{ $type['label'] }}
                </span>
            </div>
        </div>

        <div class="student-quiz-available-card__body">
            @if($quiz->description)
                <p class="student-quiz-available-card__desc">{{ Str::limit($quiz->description, 90) }}</p>
            @endif

            <div class="student-quiz-available-card__stats">
                <div class="student-quiz-available-card__stat">
                    <span class="student-quiz-available-card__stat-icon bg-primary-transparent text-primary"><i class="fe fe-help-circle"></i></span>
                    <div>
                        <span class="student-quiz-available-card__stat-label">الأسئلة</span>
                        <span class="student-quiz-available-card__stat-value">{{ $quiz->getQuestionCount() }}</span>
                    </div>
                </div>
                <div class="student-quiz-available-card__stat">
                    <span class="student-quiz-available-card__stat-icon bg-success-transparent text-success"><i class="fe fe-star"></i></span>
                    <div>
                        <span class="student-quiz-available-card__stat-label">الدرجة</span>
                        <span class="student-quiz-available-card__stat-value">{{ number_format($quiz->max_score, 0) }}</span>
                    </div>
                </div>
                <div class="student-quiz-available-card__stat">
                    <span class="student-quiz-available-card__stat-icon bg-warning-transparent text-warning"><i class="fe fe-clock"></i></span>
                    <div>
                        <span class="student-quiz-available-card__stat-label">الوقت</span>
                        <span class="student-quiz-available-card__stat-value">{{ $quiz->time_limit ? $quiz->time_limit . ' د' : 'مفتوح' }}</span>
                    </div>
                </div>
                <div class="student-quiz-available-card__stat">
                    <span class="student-quiz-available-card__stat-icon bg-info-transparent text-info"><i class="fe fe-refresh-cw"></i></span>
                    <div>
                        <span class="student-quiz-available-card__stat-label">المحاولات</span>
                        <span class="student-quiz-available-card__stat-value">{{ $quiz->remaining_attempts ?? '∞' }}</span>
                    </div>
                </div>
            </div>

            @if($quiz->due_date)
                <div class="student-quiz-available-card__due alert alert-{{ $quiz->due_date->isPast() ? 'danger' : 'warning' }} py-2 mb-0">
                    <i class="fe fe-calendar me-1"></i>
                    {{ $quiz->due_date->isPast() ? 'انتهى: ' : 'ينتهي: ' }}{{ $quiz->due_date->format('Y-m-d H:i') }}
                </div>
            @endif

            @if($quiz->student_attempts_count > 0)
                <div class="student-quiz-available-card__progress">
                    <div class="student-quiz-available-card__progress-meta">
                        <span class="text-muted">المحاولات: {{ $quiz->student_attempts_count }}</span>
                        @if($quiz->best_attempt)
                            <span class="badge bg-{{ $passed ? 'success' : 'danger' }}-transparent text-{{ $passed ? 'success' : 'danger' }}">
                                أفضل: {{ number_format($bestPct, 1) }}%
                            </span>
                        @endif
                    </div>
                    <div class="student-quizzes-result__track">
                        <div class="student-quizzes-result__bar {{ $passed ? 'is-passed' : 'is-failed' }}"
                             style="width: {{ min(100, max(0, $bestPct)) }}%"
                             role="progressbar"
                             aria-valuenow="{{ $bestPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            @endif

            <div class="student-quiz-available-card__actions">
                @if($quiz->can_attempt)
                    <a href="{{ route('student.quizzes.show', $quiz->id) }}" class="btn btn-primary btn-sm">
                        <i class="fe fe-play me-1"></i>
                        {{ $quiz->student_attempts_count > 0 ? 'محاولة جديدة' : 'بدء الاختبار' }}
                    </a>
                @else
                    <button type="button" class="btn btn-secondary btn-sm" disabled>
                        <i class="fe fe-lock me-1"></i>غير متاح
                    </button>
                @endif
                @if($quiz->student_attempts_count > 0)
                    <a href="{{ route('student.quizzes.review.history', $quiz->id) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fe fe-clock me-1"></i>عرض المحاولات السابقة
                    </a>
                @endif
            </div>
        </div>
    </article>
</div>
