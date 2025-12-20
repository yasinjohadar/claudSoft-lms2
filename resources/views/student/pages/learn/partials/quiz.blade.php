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
                <div class="fw-bold">{{ $quiz->passing_score ?? 70 }}%</div>
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

    <!-- Check if student already took the quiz -->
    @php
        $previousAttempt = auth()->user()->quizAttempts()
            ->where('quiz_id', $quiz->id)
            ->latest()
            ->first();
        $attemptsCount = auth()->user()->quizAttempts()
            ->where('quiz_id', $quiz->id)
            ->count();
        $canTakeQuiz = !$quiz->max_attempts || $attemptsCount < $quiz->max_attempts;
    @endphp

    @if($previousAttempt && $previousAttempt->status == 'completed')
        <!-- Show Previous Results -->
        <div class="card mb-4">
            <div class="card-header bg-{{ $previousAttempt->is_passed ? 'success' : 'danger' }} text-white">
                <h5 class="mb-0">
                    <i class="fas fa-{{ $previousAttempt->is_passed ? 'check-circle' : 'times-circle' }} me-2"></i>
                    نتيجة المحاولة السابقة
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <h3 class="text-{{ $previousAttempt->is_passed ? 'success' : 'danger' }}">
                            {{ number_format($previousAttempt->score_percentage, 1) }}%
                        </h3>
                        <p class="text-muted">النتيجة النهائية</p>
                    </div>
                    <div class="col-md-4">
                        <h3>{{ $previousAttempt->correct_answers }}/{{ $previousAttempt->total_questions }}</h3>
                        <p class="text-muted">الإجابات الصحيحة</p>
                    </div>
                    <div class="col-md-4">
                        <h3>{{ $previousAttempt->time_taken ?? '-' }}</h3>
                        <p class="text-muted">الوقت المستغرق</p>
                    </div>
                </div>

                @if($previousAttempt->is_passed)
                    <div class="alert alert-success text-center mb-0 mt-3">
                        <i class="fas fa-trophy fa-2x mb-2"></i>
                        <h5>تهانينا! لقد اجتزت الاختبار بنجاح</h5>
                    </div>
                @else
                    <div class="alert alert-warning text-center mb-0 mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        الحد الأدنى للنجاح: {{ $quiz->passing_score }}%
                    </div>
                @endif

                @if($canTakeQuiz)
                    <div class="text-center mt-4">
                        <p class="text-muted">
                            المحاولات المتبقية: {{ $quiz->max_attempts ? ($quiz->max_attempts - $attemptsCount) : 'غير محدود' }}
                        </p>
                        <button class="btn btn-primary btn-lg" onclick="startNewAttempt()">
                            <i class="fas fa-redo me-2"></i>محاولة جديدة
                        </button>
                    </div>
                @else
                    <div class="alert alert-danger text-center mt-4 mb-0">
                        <i class="fas fa-ban me-2"></i>
                        لقد استنفذت جميع المحاولات المسموحة
                    </div>
                @endif
            </div>
        </div>

    @elseif(!$canTakeQuiz)
        <!-- No more attempts allowed -->
        <div class="alert alert-danger text-center">
            <i class="fas fa-ban fa-3x mb-3"></i>
            <h5>لقد استنفذت جميع المحاولات المسموحة</h5>
            <p class="mb-0">عدد المحاولات المتاحة: {{ $quiz->max_attempts }}</p>
        </div>

    @else
        <!-- Start Quiz Button -->
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
                <li class="mb-2">راجع جميع الإجابات قبل تسليم الاختبار</li>
                <li class="mb-2">تأكد من استقرار اتصالك بالإنترنت طوال فترة الاختبار</li>
                @if($quiz->time_limit)
                    <li class="mb-2">انتبه للوقت المتبقي وخطط لإجاباتك بناءً عليه</li>
                @endif
                <li class="mb-2">لا تغلق النافذة أو تحدث الصفحة أثناء الاختبار</li>
                <li>إذا واجهت أي مشكلة تقنية، تواصل مع الدعم الفني فوراً</li>
            </ul>
        </div>
    </div>

    <!-- FAQ -->
    <div class="card mt-4">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-question-circle me-2"></i>الأسئلة الشائعة
            </h6>
        </div>
        <div class="card-body">
            <div class="accordion" id="quizFaq">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faqOne">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                            ماذا يحدث إذا انقطع الاتصال أثناء الاختبار؟
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#quizFaq">
                        <div class="accordion-body">
                            سيتم حفظ إجاباتك تلقائياً، ويمكنك العودة لإكمال الاختبار من حيث توقفت.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header" id="faqTwo">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                            هل يمكنني العودة للأسئلة السابقة؟
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#quizFaq">
                        <div class="accordion-body">
                            نعم، يمكنك التنقل بين الأسئلة وتغيير إجاباتك قبل التسليم النهائي.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header" id="faqThree">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                            متى سأرى نتائجي؟
                        </button>
                    </h2>
                    <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#quizFaq">
                        <div class="accordion-body">
                            @if($quiz->show_results_immediately)
                                ستظهر النتائج فوراً بعد تسليم الاختبار.
                            @else
                                سيتم إظهار النتائج بعد مراجعة المدرب للاختبار.
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@else
    <div class="text-center text-muted py-5">
        <i class="fas fa-question-circle fa-4x mb-3 opacity-25"></i>
        <h5>الاختبار غير متوفر</h5>
        <p>يرجى المحاولة لاحقاً أو التواصل مع الدعم الفني</p>
    </div>
@endif

@push('scripts')
<script>
    function startNewAttempt() {
        if (confirm('هل أنت متأكد من بدء محاولة جديدة؟')) {
            document.getElementById('startQuizForm').submit();
        }
    }

    // Prevent accidental page refresh
    window.addEventListener('beforeunload', function (e) {
        // Only show warning if quiz is in progress
        // This would be set by the quiz taking page
        if (window.quizInProgress) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
</script>
@endpush
