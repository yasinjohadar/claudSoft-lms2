@php
    $quiz = $module->content; // Assuming polymorphic relationship
@endphp

@if($quiz)
    <!-- Quiz Header -->
    <div class="text-center mb-5">
        <div class="mx-auto mb-4"
             style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-question-circle fa-3x text-white"></i>
        </div>

        <h3 class="mb-2">{{ $quiz->title ?? $module->title }}</h3>

        @if($quiz->description)
            <p class="text-muted lead">{{ $quiz->description }}</p>
        @endif
    </div>

    <!-- Quiz Information -->
    <div class="row g-3 mb-5">
        <div class="col-md-3">
            <div class="text-center p-4 bg-light rounded">
                <i class="fas fa-list-ol text-primary fa-2x mb-3"></i>
                <div class="fw-bold">{{ $quiz->questions_count ?? count($quiz->questions ?? []) }}</div>
                <small class="text-muted">عدد الأسئلة</small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="text-center p-4 bg-light rounded">
                <i class="fas fa-clock text-success fa-2x mb-3"></i>
                <div class="fw-bold">{{ $quiz->time_limit ?? 'غير محدد' }}</div>
                <small class="text-muted">الوقت المسموح</small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="text-center p-4 bg-light rounded">
                <i class="fas fa-chart-line text-info fa-2x mb-3"></i>
                <div class="fw-bold">{{ $quiz->passing_score ?? $quiz->passing_grade ?? 70 }}%</div>
                <small class="text-muted">درجة النجاح</small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="text-center p-4 bg-light rounded">
                <i class="fas fa-redo text-warning fa-2x mb-3"></i>
                <div class="fw-bold">{{ $quiz->max_attempts ?? 'غير محدود' }}</div>
                <small class="text-muted">عدد المحاولات</small>
            </div>
        </div>
    </div>

    <!-- Quiz Instructions -->
    @if($quiz->instructions)
        <div class="alert alert-info mb-4">
            <h6 class="alert-heading">
                <i class="fas fa-info-circle me-2"></i>تعليمات الاختبار
            </h6>
            <div>{{ $quiz->instructions }}</div>
        </div>
    @endif

    @php
        app(\App\Services\Quiz\QuizAttemptLifecycleService::class)
            ->reconcileForStudent($quiz, auth()->id());

        $studentAttempts = \App\Models\QuizAttempt::query()
            ->realAttempts()
            ->where('quiz_id', $quiz->id)
            ->where('student_id', auth()->id())
            ->latest('id')
            ->get();

        $currentAttempt = $studentAttempts->firstWhere('status', 'in_progress');
        $previousAttempt = $studentAttempts->first(function ($attempt) {
            return in_array($attempt->status, ['submitted', 'graded'], true);
        });
        $finishedAttemptsCount = $studentAttempts
            ->whereIn('status', \App\Models\Quiz::FINISHED_ATTEMPT_STATUSES)
            ->count();
        $attemptsCount = $finishedAttemptsCount;
        $canTakeQuiz = ! $quiz->max_attempts || $finishedAttemptsCount < $quiz->max_attempts;
        $scorePercent = $previousAttempt
            ? ($previousAttempt->percentage_score ?? $previousAttempt->score_percentage ?? null)
            : null;
        $passed = $previousAttempt
            ? (bool) ($previousAttempt->passed ?? $previousAttempt->is_passed ?? false)
            : false;
    @endphp

    @if($currentAttempt)
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-play-circle fa-5x text-warning mb-4 opacity-50"></i>
                <h4 class="mb-3">لديك محاولة قيد التقدم</h4>
                <p class="text-muted mb-4">
                    يمكنك متابعة الاختبار من حيث توقفت. الوقت المتبقي يُحتسب من بداية المحاولة.
                </p>
                <a href="{{ route('student.quizzes.take', $currentAttempt->id) }}" class="btn btn-warning btn-lg">
                    <i class="fas fa-play me-2"></i>متابعة الاختبار
                </a>
            </div>
        </div>

    @elseif($previousAttempt)
        <div class="card mb-4">
            <div class="card-header bg-{{ $passed ? 'success' : 'danger' }} text-white">
                <h5 class="mb-0">
                    <i class="fas fa-{{ $passed ? 'check-circle' : 'times-circle' }} me-2"></i>
                    نتيجة المحاولة السابقة
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <h3 class="text-{{ $passed ? 'success' : 'danger' }}">
                            {{ $scorePercent !== null ? number_format((float) $scorePercent, 1) . '%' : '—' }}
                        </h3>
                        <p class="text-muted">النتيجة النهائية</p>
                    </div>
                    <div class="col-md-4">
                        <h3>{{ $previousAttempt->total_score ?? '—' }}/{{ $previousAttempt->max_score ?? '—' }}</h3>
                        <p class="text-muted">الدرجة</p>
                    </div>
                    <div class="col-md-4">
                        <h3>{{ $previousAttempt->time_spent ? $previousAttempt->getTimeSpentHumanReadable() : '—' }}</h3>
                        <p class="text-muted">الوقت المستغرق</p>
                    </div>
                </div>

                @if($passed)
                    <div class="alert alert-success text-center mb-0 mt-3">
                        <i class="fas fa-trophy fa-2x mb-2"></i>
                        <h5>تهانينا! لقد اجتزت الاختبار بنجاح</h5>
                    </div>
                @else
                    <div class="alert alert-warning text-center mb-0 mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        الحد الأدنى للنجاح: {{ $quiz->passing_score ?? $quiz->passing_grade ?? 70 }}%
                    </div>
                @endif

                @if($canTakeQuiz)
                    <div class="text-center mt-4">
                        <p class="text-muted">
                            المحاولات المتبقية: {{ $quiz->max_attempts ? ($quiz->max_attempts - $attemptsCount) : 'غير محدود' }}
                        </p>
                        <form action="{{ route('student.quizzes.start', $quiz->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-redo me-2"></i>محاولة جديدة
                            </button>
                        </form>
                    </div>
                @else
                    <div class="alert alert-danger text-center mb-0 mt-3">
                        <i class="fas fa-ban me-2"></i>
                        لقد استنفذت جميع المحاولات المسموحة
                    </div>
                @endif
            </div>
        </div>

    @elseif(! $canTakeQuiz)
        <div class="alert alert-danger text-center">
            <i class="fas fa-ban fa-3x mb-3"></i>
            <h5>لقد استنفذت جميع المحاولات المسموحة</h5>
            <p class="mb-0">عدد المحاولات المتاحة: {{ $quiz->max_attempts }}</p>
        </div>

    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-play-circle fa-5x text-primary mb-4 opacity-50"></i>
                <h4 class="mb-3">جاهز لبدء الاختبار؟</h4>

                @if($attemptsCount > 0)
                    <p class="text-muted mb-4">
                        هذه هي المحاولة رقم {{ $attemptsCount + 1 }}
                        @if($quiz->max_attempts)
                            من أصل {{ $quiz->max_attempts }}
                        @endif
                    </p>
                @endif

                <form action="{{ route('student.quizzes.start', $quiz->id) }}" method="POST" id="startQuizForm">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-play me-2"></i>بدء الاختبار الآن
                    </button>
                </form>

                <div class="mt-4">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        تأكد من أنك في مكان هادئ ولديك اتصال مستقر بالإنترنت
                    </small>
                </div>
            </div>
        </div>
    @endif

    <!-- Quiz Tips -->
    <div class="card mt-4 border-primary">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0">
                <i class="fas fa-lightbulb me-2"></i>نصائح للنجاح في الاختبار
            </h6>
        </div>
        <div class="card-body">
            <ul class="mb-0">
                <li class="mb-2">اقرأ كل سؤال بعناية قبل الإجابة</li>
                <li class="mb-2">راجع إجاباتك قبل التسليم النهائي</li>
                <li class="mb-2">راقب الوقت المتبقي إن وُجد حد زمني</li>
                <li>لا تغلق الصفحة أثناء الاختبار إذا أمكن</li>
            </ul>
        </div>
    </div>
@else
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        محتوى الاختبار غير متاح حالياً.
    </div>
@endif
