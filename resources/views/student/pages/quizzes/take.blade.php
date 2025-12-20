@extends('student.layouts.master')

@section('page-title')
    {{ $attempt->quiz->title }} - المحاولة #{{ $attempt->attempt_number }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Timer Bar (Fixed Top) -->
            @if($attempt->quiz->time_limit)
                <div class="card custom-card mb-3 bg-danger-transparent border-danger">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-clock text-danger me-2"></i>
                                <strong>الوقت المتبقي:</strong>
                            </div>
                            <div>
                                <span id="timer" class="fs-18 fw-bold text-danger">
                                    <i class="fas fa-hourglass-half me-1"></i>
                                    <span id="timer-display">--:--</span>
                                </span>
                            </div>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div id="timer-progress" class="progress-bar bg-danger" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Progress Bar -->
            <div class="card custom-card mb-4">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <i class="fas fa-tasks me-2 text-primary"></i>
                            <strong>التقدم:</strong>
                            <span id="progress-text">0 من {{ count($orderedQuestions) }}</span>
                        </div>
                        <div>
                            <span class="badge bg-warning" id="marked-count">0 للمراجعة</span>
                        </div>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div id="progress-bar" class="progress-bar bg-primary" style="width: 0%"></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Questions Area -->
                <div class="col-lg-9">
                    <form id="quiz-form" action="{{ route('student.quizzes.submit', $attempt->id) }}" method="POST">
                        @csrf

                        @foreach($orderedQuestions as $index => $item)
                            @php
                                $quizQuestion = $item['quiz_question'];
                                $question = $quizQuestion->question;
                                $response = $item['response'];
                                $questionNumber = $index + 1;
                            @endphp

                            <div class="question-card card custom-card mb-4" data-question-id="{{ $question->id }}" data-question-number="{{ $questionNumber }}" style="display: {{ $index == 0 ? 'block' : 'none' }}">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="card-title mb-0">
                                                <span class="badge bg-primary me-2">سؤال {{ $questionNumber }}</span>
                                                <span class="badge bg-info">{{ $question->questionType->display_name }}</span>
                                            </h5>
                                        </div>
                                        <div>
                                            <span class="badge bg-success">{{ $quizQuestion->max_score }} نقطة</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Question Text -->
                                    <div class="mb-4">
                                        <h6 class="fw-bold">{{ $question->question_text }}</h6>
                                        @if($question->media_url)
                                            <div class="mt-3">
                                                @if($question->media_type == 'image')
                                                    <img src="{{ $question->media_url }}" alt="صورة السؤال" class="img-fluid rounded" style="max-width: 500px;">
                                                @elseif($question->media_type == 'video')
                                                    <video controls class="w-100" style="max-width: 600px;">
                                                        <source src="{{ $question->media_url }}" type="video/mp4">
                                                    </video>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Answer Area -->
                                    <div class="answer-area">
                                        @if($question->questionType->name == 'multiple_choice_single')
                                            <!-- Multiple Choice Single -->
                                            @foreach($question->options as $option)
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input answer-input" type="radio"
                                                           name="question_{{ $question->id }}"
                                                           id="option_{{ $option->id }}"
                                                           value="{{ $option->id }}"
                                                           data-question-id="{{ $question->id }}">
                                                    <label class="form-check-label w-100" for="option_{{ $option->id }}">
                                                        <div class="p-3 border rounded">{{ $option->option_text }}</div>
                                                    </label>
                                                </div>
                                            @endforeach

                                        @elseif($question->questionType->name == 'multiple_choice_multiple')
                                            <!-- Multiple Choice Multiple -->
                                            @foreach($question->options as $option)
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input answer-input" type="checkbox"
                                                           name="question_{{ $question->id }}[]"
                                                           id="option_{{ $option->id }}"
                                                           value="{{ $option->id }}"
                                                           data-question-id="{{ $question->id }}">
                                                    <label class="form-check-label w-100" for="option_{{ $option->id }}">
                                                        <div class="p-3 border rounded">{{ $option->option_text }}</div>
                                                    </label>
                                                </div>
                                            @endforeach

                                        @elseif($question->questionType->name == 'true_false')
                                            <!-- True/False -->
                                            <div class="form-check mb-3">
                                                <input class="form-check-input answer-input" type="radio"
                                                       name="question_{{ $question->id }}"
                                                       id="true_{{ $question->id }}"
                                                       value="true"
                                                       data-question-id="{{ $question->id }}">
                                                <label class="form-check-label w-100" for="true_{{ $question->id }}">
                                                    <div class="p-3 border rounded bg-success-transparent">
                                                        <i class="fas fa-check-circle text-success me-2"></i>صحيح
                                                    </div>
                                                </label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input answer-input" type="radio"
                                                       name="question_{{ $question->id }}"
                                                       id="false_{{ $question->id }}"
                                                       value="false"
                                                       data-question-id="{{ $question->id }}">
                                                <label class="form-check-label w-100" for="false_{{ $question->id }}">
                                                    <div class="p-3 border rounded bg-danger-transparent">
                                                        <i class="fas fa-times-circle text-danger me-2"></i>خطأ
                                                    </div>
                                                </label>
                                            </div>

                                        @elseif($question->questionType->name == 'short_answer')
                                            <!-- Short Answer -->
                                            <textarea class="form-control answer-input"
                                                      name="question_{{ $question->id }}"
                                                      rows="3"
                                                      placeholder="اكتب إجابتك هنا..."
                                                      data-question-id="{{ $question->id }}"></textarea>

                                        @elseif($question->questionType->name == 'essay')
                                            <!-- Essay -->
                                            <textarea class="form-control answer-input"
                                                      name="question_{{ $question->id }}"
                                                      rows="8"
                                                      placeholder="اكتب إجابتك المفصلة هنا..."
                                                      data-question-id="{{ $question->id }}"></textarea>
                                            <small class="text-muted">سيتم تصحيح هذا السؤال يدوياً من قبل المدرس</small>

                                        @elseif($question->questionType->name == 'fill_in_blanks')
                                            <!-- Fill in Blanks -->
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>املأ الفراغات بالإجابة المناسبة
                                            </div>
                                            <textarea class="form-control answer-input"
                                                      name="question_{{ $question->id }}"
                                                      rows="4"
                                                      placeholder="اكتب الإجابات..."
                                                      data-question-id="{{ $question->id }}"></textarea>

                                        @else
                                            <!-- Default Text Input -->
                                            <textarea class="form-control answer-input"
                                                      name="question_{{ $question->id }}"
                                                      rows="4"
                                                      placeholder="اكتب إجابتك..."
                                                      data-question-id="{{ $question->id }}"></textarea>
                                        @endif
                                    </div>

                                    <!-- Mark for Review -->
                                    <div class="mt-4">
                                        <div class="form-check">
                                            <input class="form-check-input mark-review-checkbox" type="checkbox"
                                                   id="mark_{{ $question->id }}"
                                                   data-question-id="{{ $question->id }}">
                                            <label class="form-check-label" for="mark_{{ $question->id }}">
                                                <i class="fas fa-flag text-warning me-1"></i>وضع علامة للمراجعة
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between">
                                        <button type="button" class="btn btn-secondary nav-btn" data-direction="prev" {{ $index == 0 ? 'disabled' : '' }}>
                                            <i class="fas fa-arrow-right me-2"></i>السابق
                                        </button>
                                        @if($questionNumber < count($orderedQuestions))
                                            <button type="button" class="btn btn-primary nav-btn" data-direction="next">
                                                التالي<i class="fas fa-arrow-left ms-2"></i>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#submitModal">
                                                <i class="fas fa-check me-2"></i>إنهاء وتسليم
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    </form>
                </div>

                <!-- Sidebar - Question Navigator -->
                <div class="col-lg-3">
                    <div class="card custom-card sticky-top" style="top: 20px;">
                        <div class="card-header bg-primary-transparent">
                            <div class="card-title mb-0">
                                <i class="fas fa-list me-2"></i>الأسئلة
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2" id="question-navigator">
                                @foreach($orderedQuestions as $index => $item)
                                    <button type="button"
                                            class="btn btn-outline-primary question-nav-btn"
                                            data-question-number="{{ $index + 1 }}"
                                            data-question-id="{{ $item['quiz_question']->question->id }}">
                                        <span class="number">{{ $index + 1 }}</span>
                                        <i class="fas fa-flag flag-icon text-warning" style="display: none;"></i>
                                        <i class="fas fa-check check-icon text-success" style="display: none;"></i>
                                    </button>
                                @endforeach
                            </div>

                            <hr>

                            <!-- Legend -->
                            <div class="mb-3">
                                <small class="d-block mb-2">
                                    <span class="badge bg-primary me-1" style="width: 20px; height: 20px;"></span>
                                    السؤال الحالي
                                </small>
                                <small class="d-block mb-2">
                                    <i class="fas fa-check text-success me-1"></i>
                                    تمت الإجابة
                                </small>
                                <small class="d-block">
                                    <i class="fas fa-flag text-warning me-1"></i>
                                    للمراجعة
                                </small>
                            </div>

                            <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#submitModal">
                                <i class="fas fa-paper-plane me-2"></i>تسليم الاختبار
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Submit Confirmation Modal -->
    <div class="modal fade" id="submitModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle me-2"></i>تأكيد التسليم
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>تنبيه:</strong> بعد التسليم لن تتمكن من تعديل إجاباتك
                    </div>
                    <div id="submit-summary">
                        <p><strong>ملخص المحاولة:</strong></p>
                        <ul>
                            <li>إجمالي الأسئلة: <strong>{{ count($orderedQuestions) }}</strong></li>
                            <li>تمت الإجابة: <strong id="answered-count">0</strong></li>
                            <li>للمراجعة: <strong id="review-count">0</strong></li>
                        </ul>
                    </div>
                    <p class="mb-0">هل أنت متأكد من تسليم الاختبار؟</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>إلغاء
                    </button>
                    <button type="button" class="btn btn-success" id="confirm-submit">
                        <i class="fas fa-check me-2"></i>تأكيد التسليم
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
<script>
let currentQuestion = 1;
const totalQuestions = {{ count($orderedQuestions) }};
const attemptId = {{ $attempt->id }};
const timeLimit = {{ $attempt->quiz->time_limit ?? 'null' }};
let startTime = new Date('{{ $attempt->started_at }}');
let answeredQuestions = new Set();
let markedQuestions = new Set();

// Initialize
$(document).ready(function() {
    initializeTimer();
    updateProgress();
    showQuestion(1);

    // Navigation buttons
    $('.nav-btn').click(function() {
        const direction = $(this).data('direction');
        if (direction === 'next' && currentQuestion < totalQuestions) {
            saveCurrentAnswer();
            showQuestion(currentQuestion + 1);
        } else if (direction === 'prev' && currentQuestion > 1) {
            saveCurrentAnswer();
            showQuestion(currentQuestion - 1);
        }
    });

    // Question navigator
    $('.question-nav-btn').click(function() {
        const questionNum = $(this).data('question-number');
        saveCurrentAnswer();
        showQuestion(questionNum);
    });

    // Answer inputs
    $('.answer-input').on('change keyup', function() {
        const questionId = $(this).data('question-id');
        answeredQuestions.add(questionId);
        updateQuestionStatus(questionId);
        updateProgress();
        autoSaveAnswer(questionId);
    });

    // Mark for review
    $('.mark-review-checkbox').change(function() {
        const questionId = $(this).data('question-id');
        if ($(this).is(':checked')) {
            markedQuestions.add(questionId);
        } else {
            markedQuestions.delete(questionId);
        }
        updateQuestionStatus(questionId);
        updateProgress();
        markForReview(questionId, $(this).is(':checked'));
    });

    // Submit confirmation
    $('#confirm-submit').click(function() {
        $('#quiz-form').submit();
    });
});

function showQuestion(num) {
    $('.question-card').hide();
    $(`.question-card[data-question-number="${num}"]`).show();
    currentQuestion = num;

    $('.question-nav-btn').removeClass('btn-primary').addClass('btn-outline-primary');
    $(`.question-nav-btn[data-question-number="${num}"]`).removeClass('btn-outline-primary').addClass('btn-primary');

    $('.nav-btn[data-direction="prev"]').prop('disabled', num === 1);
}

function updateProgress() {
    const answered = answeredQuestions.size;
    const percentage = (answered / totalQuestions) * 100;

    $('#progress-bar').css('width', percentage + '%');
    $('#progress-text').text(`${answered} من ${totalQuestions}`);
    $('#marked-count').text(`${markedQuestions.size} للمراجعة`);
    $('#answered-count').text(answered);
    $('#review-count').text(markedQuestions.size);
}

function updateQuestionStatus(questionId) {
    const navBtn = $(`.question-nav-btn[data-question-id="${questionId}"]`);
    const isAnswered = answeredQuestions.has(questionId);
    const isMarked = markedQuestions.has(questionId);

    navBtn.find('.check-icon').toggle(isAnswered);
    navBtn.find('.flag-icon').toggle(isMarked);
}

function saveCurrentAnswer() {
    // Auto-save current question answer
    const currentCard = $(`.question-card[data-question-number="${currentQuestion}"]`);
    const questionId = currentCard.data('question-id');
    autoSaveAnswer(questionId);
}

function autoSaveAnswer(questionId) {
    const formData = {
        question_id: questionId,
        response_text: $(`[name="question_${questionId}"]`).val(),
        selected_option_ids: []
    };

    // Collect selected options
    $(`input[name="question_${questionId}"]:checked, input[name="question_${questionId}[]"]:checked`).each(function() {
        formData.selected_option_ids.push($(this).val());
    });

    // AJAX save
    $.ajax({
        url: `{{ url('student/quizzes/attempt') }}/${attemptId}/save-answer`,
        method: 'POST',
        data: {
            ...formData,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            console.log('Answer saved');
        }
    });
}

function markForReview(questionId, marked) {
    $.ajax({
        url: `{{ url('student/quizzes/attempt') }}/${attemptId}/mark-review/${questionId}`,
        method: 'POST',
        data: {
            marked: marked,
            _token: '{{ csrf_token() }}'
        }
    });
}

function initializeTimer() {
    if (!timeLimit) return;

    const endTime = new Date(startTime.getTime() + timeLimit * 60000);

    setInterval(function() {
        const now = new Date();
        const remaining = endTime - now;

        if (remaining <= 0) {
            alert('انتهى الوقت! سيتم تسليم الاختبار تلقائياً');
            $('#quiz-form').submit();
            return;
        }

        const minutes = Math.floor(remaining / 60000);
        const seconds = Math.floor((remaining % 60000) / 1000);

        $('#timer-display').text(`${minutes}:${seconds.toString().padStart(2, '0')}`);

        const percentage = (remaining / (timeLimit * 60000)) * 100;
        $('#timer-progress').css('width', percentage + '%');

        if (remaining < 60000) {
            $('#timer, #timer-progress').addClass('text-danger bg-danger');
        }
    }, 1000);
}
</script>
@endsection
