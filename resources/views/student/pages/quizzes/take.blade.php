@extends('student.layouts.master')

@section('page-title', 'حل الاختبار - ' . $attempt->quiz->title)

@push('head-scripts')
<script>
// Global variables - initialize early with Blade values
var attemptId = {{ $attempt->id }};
var totalQuestions = {{ $questions->count() }};
var currentQuestionIndex = 0;
var answeredQuestions = new Set();
var remainingTimeSeconds = {{ $remainingTime ?? 'null' }};
var timerInterval = null;
var isSubmitting = false;

// Ensure remainingTimeSeconds is an integer
if (remainingTimeSeconds !== null) {
    remainingTimeSeconds = Math.floor(remainingTimeSeconds);
}

// Navigation functions - defined globally for onclick handlers
function goToQuestion(index) {
    console.log('goToQuestion called with index:', index);
    if (typeof totalQuestions === 'undefined' || index < 0 || index >= totalQuestions) {
        console.error('Invalid question index or totalQuestions not set:', index, totalQuestions);
        return;
    }
    
    // Use vanilla JS since jQuery may not be loaded yet
    document.querySelectorAll('.question-container').forEach(el => el.style.display = 'none');
    const target = document.querySelector(`.question-container[data-question-index="${index}"]`);
    if (target) target.style.display = 'block';
    
    currentQuestionIndex = index;
    
    // Update navigation buttons using vanilla JS (jQuery may not be loaded yet)
    document.querySelectorAll('.question-nav-btn').forEach(function(btn) {
        btn.classList.remove('answered', 'active');
        const btnIndex = parseInt(btn.getAttribute('data-question-index'));
        if (btnIndex === index) {
            btn.classList.add('active');
        }
    });
    
    // Also call updateQuestionNavigation if jQuery is available
    if (typeof $ !== 'undefined' && typeof updateQuestionNavigation === 'function') {
        updateQuestionNavigation();
    }
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function nextQuestion() {
    console.log('nextQuestion called, current:', currentQuestionIndex, 'total:', totalQuestions);
    if (currentQuestionIndex < totalQuestions - 1) {
        goToQuestion(currentQuestionIndex + 1);
    }
}

function previousQuestion() {
    console.log('previousQuestion called, current:', currentQuestionIndex);
    if (currentQuestionIndex > 0) {
        goToQuestion(currentQuestionIndex - 1);
    }
}

// showSubmitConfirmation is defined in the page scripts block (after jQuery/Bootstrap load)

// Start timer when DOM is ready - using vanilla JS
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DOMContentLoaded (head-scripts) ===');
    console.log('remainingTimeSeconds:', remainingTimeSeconds);
    
    if (remainingTimeSeconds !== null && remainingTimeSeconds > 0 && !timerInterval) {
        console.log('Starting timer from head-scripts...');
        
        // Simple timer using vanilla JS
        function updateTimerDisplaySimple() {
            var minutes = Math.floor(remainingTimeSeconds / 60);
            var seconds = Math.floor(remainingTimeSeconds % 60);
            var timerMinutesEl = document.getElementById('timer-minutes');
            var timerSecondsEl = document.getElementById('timer-seconds');
            
            if (timerMinutesEl && timerSecondsEl) {
                timerMinutesEl.textContent = String(minutes).padStart(2, '0');
                timerSecondsEl.textContent = String(seconds).padStart(2, '0');
            }
        }
        
        updateTimerDisplaySimple();
        
        timerInterval = setInterval(function() {
            remainingTimeSeconds--;
            updateTimerDisplaySimple();
            
            if (remainingTimeSeconds <= 0) {
                clearInterval(timerInterval);
                console.log('Time is up!');
                // Submit quiz automatically (assigned on window in page scripts)
                if (typeof window.submitQuiz === 'function') {
                    window.submitQuiz(true);
                }
            }
        }, 1000);
        
        console.log('Timer started successfully from head-scripts');
    }
});
</script>
@endpush

@section('content')
<!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                            <div>
                <h4 class="mb-0">
                    <i class="fas fa-clipboard-question me-2"></i>
                    {{ $attempt->quiz->title }}
                </h4>
                            </div>
                            </div>
        <!-- End Page Header -->
    <div class="row">
        <!-- Sidebar - Questions Navigator -->
        <div class="col-lg-3 mb-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        الأسئلة
                    </h5>
                        </div>
                <div class="card-body">
                    <!-- Timer -->
                    @if($remainingTime !== null && $remainingTime > 0)
                    <div class="alert alert-warning mb-3 text-center" id="timer-container">
                        <i class="fas fa-clock me-2"></i>
                        <strong>الوقت المتبقي:</strong>
                        <div class="fs-3 fw-bold mt-2" id="timer">
                            <span id="timer-minutes">{{ floor($remainingTime / 60) }}</span>:<span id="timer-seconds">{{ str_pad($remainingTime % 60, 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                </div>
            @elseif($attempt->quiz->time_limit === null)
                    <div class="alert alert-info mb-3 text-center">
                        <i class="fas fa-infinity me-2"></i>
                        <strong>بدون حد زمني</strong>
                    </div>
            @endif

                    <!-- Progress -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">التقدم</span>
                            <span class="fw-bold"><span id="answered-count">0</span> / {{ $questions->count() }}</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" role="progressbar" id="progress-bar" style="width: 0%"></div>
                        </div>
                    </div>

                    <!-- Questions Grid -->
                    <div class="questions-grid">
                        @foreach($questions as $index => $question)
                        <button type="button"
                                class="btn btn-outline-secondary btn-sm question-nav-btn m-1"
                                data-question-index="{{ $index }}"
                                data-question-id="{{ $question->id }}"
                                onclick="goToQuestion({{ $index }})">
                            {{ $index + 1 }}
                        </button>
                        @endforeach
                    </div>
                    </div>
                </div>
            </div>

        <!-- Main Content - Questions -->
                <div class="col-lg-9">
            <form id="quiz-form">
                        @csrf
                @foreach($questions as $question)
                @php $index = $loop->index; @endphp
                <div class="question-container card mb-4"
                     data-question-index="{{ $index }}"
                     data-question-id="{{ $question->id }}"
                     style="display: {{ $index === 0 ? 'block' : 'none' }}">
                    <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <span class="badge bg-primary me-2">السؤال {{ $index + 1 }}</span>
                                                <span class="badge bg-info">{{ $question->questionType->display_name }}</span>
                                <span class="badge bg-success">{{ $question->pivot->question_grade }} نقطة</span>
                                            </h5>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Question Text -->
                        @if($question->questionType->name !== 'fill_blanks')
                        <div class="question-text mb-4">
                            {!! mixed_bidi_html($question->question_text) !!}
                                            </div>
                                        @endif

                        <!-- Question Image -->
                        @if($question->question_image)
                        <div class="mb-4">
                            <img src="{{ asset('storage/' . $question->question_image) }}"
                                 alt="Question Image"
                                 class="img-fluid rounded border"
                                 style="max-width: 500px;">
                                    </div>
                        @endif

                        <!-- Answer Options -->
                        <div class="answer-options">
                            @php
                                $response = $attempt->responses->where('question_id', $question->id)->first();
                                // QuizResponse uses response_data or selected_option_ids, convert to student_answer format
                                if ($response) {
                                    // For numerical and calculated questions, prefer response_text
                                    if (in_array($question->questionType->name, ['numerical', 'calculated']) && $response->response_text) {
                                        $savedAnswer = $response->response_text;
                                    } elseif ($response->response_data) {
                                        $responseData = is_array($response->response_data) ? $response->response_data : json_decode($response->response_data, true);
                                        
                                        // Special handling for fill_blanks - data is stored as indexed array {0: "answer1", 1: "answer2"}
                                        if ($question->questionType->name === 'fill_blanks') {
                                            // If response_data has 'answer' key, use it; otherwise use the whole array
                                            if (is_array($responseData) && isset($responseData['answer'])) {
                                                $savedAnswer = $responseData['answer'];
                                            } else {
                                                // Check if responseData is already an indexed array (0, 1, 2, etc.)
                                                $isIndexedArray = is_array($responseData) && array_keys($responseData) === range(0, count($responseData) - 1);
                                                if ($isIndexedArray) {
                                                    $savedAnswer = $responseData;
                                                } else {
                                                    // Try to extract indexed values from object/associative array
                                                    $savedAnswer = [];
                                                    foreach ($responseData as $key => $value) {
                                                        if (is_numeric($key)) {
                                                            $savedAnswer[(int)$key] = $value;
                                                        } elseif ($key === 'answer' && is_array($value)) {
                                                            $savedAnswer = $value;
                                                            break;
                                                        }
                                                    }
                                                }
                                            }
                                        } elseif (is_array($responseData) && isset($responseData['answer'])) {
                                            // For numerical/calculated, extract the numeric value if it's nested
                                            if (in_array($question->questionType->name, ['numerical', 'calculated'])) {
                                                $savedAnswer = is_array($responseData['answer']) ? (string)($responseData['answer']['numeric_value'] ?? $responseData['answer'][0] ?? '') : (string)$responseData['answer'];
                                            } else {
                                                $savedAnswer = $responseData['answer'];
                                            }
                                        } else {
                                            $savedAnswer = $responseData;
                                        }
                                    } elseif ($response->selected_option_ids) {
                                        $savedAnswer = is_array($response->selected_option_ids) ? $response->selected_option_ids : json_decode($response->selected_option_ids, true);
                                        // If single value, convert to direct value
                                        if (is_array($savedAnswer) && count($savedAnswer) === 1) {
                                            $savedAnswer = $savedAnswer[0];
                                        }
                                    } elseif ($response->response_text) {
                                        $savedAnswer = $response->response_text;
                                    } else {
                                        $savedAnswer = null;
                                    }
                                } else {
                                    $savedAnswer = null;
                                }
                            @endphp

                            {{-- Debug: Question Type Info --}}
                            <script>
                            console.log('DEBUG: Question type info BEFORE switch', {
                                question_id: {{ $question->id }},
                                question_index: {{ $index }},
                                type_id: {{ $question->question_type_id }},
                                type_name: '{{ $question->questionType->name }}',
                                type_display: '{{ $question->questionType->display_name }}'
                            });
                            </script>

                            @switch($question->questionType->name)
                                @case('multiple_choice_single')
                                    {{-- #region agent log --}}
                                    <script>
                                    console.log('DEBUG: Checking options for multiple_choice_single', {
                                        question_id: {{ $question->id }},
                                        question_type: '{{ $question->questionType->name ?? "unknown" }}',
                                        has_options: {{ $question->options ? 'true' : 'false' }},
                                        options_count: {{ $question->options ? $question->options->count() : 0 }},
                                        options_type: '{{ get_class($question->options ?? new stdClass()) }}',
                                        hypothesisId: 'B'
                                    });
                                    </script>
                                    {{-- #endregion --}}
                                    @php
                                        $optionsCollection = $question->options ?? collect();
                                        $optionsCount = $optionsCollection->count();
                                    @endphp
                                    @if($optionsCount > 0)
                                        @foreach($question->options as $option)
                                            {{-- #region agent log --}}
                                            <script>
                                            console.log('DEBUG: Rendering option (single)', {
                                                question_id: {{ $question->id }},
                                                option_id: {{ $option->id }},
                                                option_text: '{{ addslashes($option->option_text ?? '') }}',
                                                option_text_length: {{ strlen($option->option_text ?? '') }},
                                                option_text_empty: {{ empty($option->option_text) ? 'true' : 'false' }},
                                                hypothesisId: 'B'
                                            });
                                            </script>
                                            {{-- #endregion --}}
                                            <label class="form-check d-flex align-items-start gap-2 w-100 mb-3 p-3 border rounded hover-shadow quiz-option-hit">
                                                <input class="form-check-input flex-shrink-0 answer-input"
                                                       type="radio"
                                                       name="question_{{ $question->id }}"
                                                       id="option_{{ $option->id }}"
                                                       value="{{ $option->id }}"
                                                       data-question-id="{{ $question->id }}"
                                                       {{ $savedAnswer == $option->id ? 'checked' : '' }}>
                                                <span class="flex-grow-1">
                                                    @if(filled($option->option_text))
                                                        {!! mixed_bidi_html($option->option_text) !!}
                                                    @else
                                                        (نص الخيار غير متوفر)
                                                    @endif
                                                </span>
                                            </label>
                                        @endforeach
                                    @else
                                        {{-- #region agent log --}}
                                        <script>
                                        console.log('DEBUG: Options condition failed (single), showing warning', {
                                            question_id: {{ $question->id }},
                                            has_options: {{ $question->options ? 'true' : 'false' }},
                                            options_count: {{ $optionsCount }},
                                            hypothesisId: 'B'
                                        });
                                        </script>
                                        {{-- #endregion --}}
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            لا توجد خيارات متاحة لهذا السؤال. يرجى التواصل مع المدير.
                                        </div>
                                    @endif
                                    @break

                                @case('multiple_choice_multiple')
                                    {{-- #region agent log --}}
                                    <script>
                                    console.log('DEBUG: Checking options for multiple_choice_multiple', {
                                        question_id: {{ $question->id }},
                                        question_type: '{{ $question->questionType->name ?? "unknown" }}',
                                        has_options: {{ $question->options ? 'true' : 'false' }},
                                        options_count: {{ $question->options ? $question->options->count() : 0 }},
                                        options_type: '{{ get_class($question->options ?? new stdClass()) }}',
                                        hypothesisId: 'B'
                                    });
                                    </script>
                                    {{-- #endregion --}}
                                    @php
                                        $optionsCollection = $question->options ?? collect();
                                        $optionsCount = $optionsCollection->count();
                                    @endphp
                                    @if($optionsCount > 0)
                                        @foreach($question->options as $option)
                                            {{-- #region agent log --}}
                                            <script>
                                            console.log('DEBUG: Rendering option', {
                                                question_id: {{ $question->id }},
                                                option_id: {{ $option->id }},
                                                option_text: '{{ addslashes($option->option_text ?? '') }}',
                                                option_text_length: {{ strlen($option->option_text ?? '') }},
                                                option_text_empty: {{ empty($option->option_text) ? 'true' : 'false' }},
                                                hypothesisId: 'B'
                                            });
                                            </script>
                                            {{-- #endregion --}}
                                            <label class="form-check d-flex align-items-start gap-2 w-100 mb-3 p-3 border rounded hover-shadow quiz-option-hit">
                                                <input class="form-check-input flex-shrink-0 answer-input"
                                                       type="checkbox"
                                                       name="question_{{ $question->id }}[]"
                                                       id="option_{{ $option->id }}"
                                                       value="{{ $option->id }}"
                                                       data-question-id="{{ $question->id }}"
                                                       {{ is_array($savedAnswer) && in_array($option->id, $savedAnswer) ? 'checked' : '' }}>
                                                <span class="flex-grow-1">
                                                    @if(filled($option->option_text))
                                                        {!! mixed_bidi_html($option->option_text) !!}
                                                    @else
                                                        (نص الخيار غير متوفر)
                                                    @endif
                                                </span>
                                            </label>
                                        @endforeach
                                    @else
                                        {{-- #region agent log --}}
                                        <script>
                                        console.log('DEBUG: Options condition failed, showing warning', {
                                            question_id: {{ $question->id }},
                                            has_options: {{ $question->options ? 'true' : 'false' }},
                                            options_count: {{ $optionsCount }},
                                            hypothesisId: 'B'
                                        });
                                        </script>
                                        {{-- #endregion --}}
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            لا توجد خيارات متاحة لهذا السؤال. يرجى التواصل مع المدير.
                                        </div>
                                    @endif
                                    @break

                                @case('true_false')
                                    {{-- Debug: true_false rendering --}}
                                    <script>
                                    console.log('DEBUG: Rendering true_false options', {
                                        question_id: {{ $question->id }},
                                        question_type_name: '{{ $question->questionType->name }}',
                                        savedAnswer: '{{ $savedAnswer ?? "null" }}'
                                    });
                                    </script>
                                    <label class="form-check d-flex align-items-start gap-2 w-100 mb-3 p-3 border rounded hover-shadow quiz-option-hit">
                                        <input class="form-check-input flex-shrink-0 answer-input"
                                               type="radio"
                                               name="question_{{ $question->id }}"
                                               id="true_{{ $question->id }}"
                                               value="true"
                                               data-question-id="{{ $question->id }}"
                                               {{ $savedAnswer === 'true' ? 'checked' : '' }}>
                                        <span class="flex-grow-1 fs-5">
                                            <i class="fas fa-check-circle text-success me-2"></i>صحيح
                                        </span>
                                    </label>
                                    <label class="form-check d-flex align-items-start gap-2 w-100 mb-3 p-3 border rounded hover-shadow quiz-option-hit">
                                        <input class="form-check-input flex-shrink-0 answer-input"
                                               type="radio"
                                               name="question_{{ $question->id }}"
                                               id="false_{{ $question->id }}"
                                               value="false"
                                               data-question-id="{{ $question->id }}"
                                               {{ $savedAnswer === 'false' ? 'checked' : '' }}>
                                        <span class="flex-grow-1 fs-5">
                                            <i class="fas fa-times-circle text-danger me-2"></i>خطأ
                                        </span>
                                    </label>
                                    @break

                                @case('short_answer')
                                    <div class="mb-3">
                                            <textarea class="form-control answer-input"
                                                      name="question_{{ $question->id }}"
                                                  id="short_answer_{{ $question->id }}"
                                                  rows="4"
                                                      placeholder="اكتب إجابتك هنا..."
                                                  data-question-id="{{ $question->id }}">{{ $savedAnswer }}</textarea>
                                    </div>
                                    @break

                                @case('essay')
                                    <div class="mb-3">
                                            <textarea class="form-control answer-input"
                                                      name="question_{{ $question->id }}"
                                                  id="essay_{{ $question->id }}"
                                                      rows="8"
                                                      placeholder="اكتب إجابتك المفصلة هنا..."
                                                  data-question-id="{{ $question->id }}">{{ $savedAnswer }}</textarea>
                                            </div>
                                    @break

                                @case('fill_blanks')
                                    @php
                                        $questionText = $question->question_text;
                                        
                                        // Support both formats: [[blank]] and ___
                                        // First, normalize the text by replacing ___ with [[blank]]
                                        $normalizedText = preg_replace('/_{3,}/', '[[blank]]', $questionText);
                                        $blankCount = substr_count($normalizedText, '[[blank]]');
                                        
                                        // Ensure $savedAnswer is converted to indexed array
                                        $savedAnswers = [];
                                        if ($savedAnswer !== null) {
                                            if (is_array($savedAnswer)) {
                                                // Convert associative array to indexed array if needed
                                                foreach ($savedAnswer as $key => $value) {
                                                    if (is_numeric($key)) {
                                                        $savedAnswers[(int)$key] = $value;
                                                    }
                                                }
                                                // If no numeric keys found, try to use values directly
                                                if (empty($savedAnswers) && !empty($savedAnswer)) {
                                                    $savedAnswers = array_values($savedAnswer);
                                                }
                                            } else {
                                                // If it's a string, try to decode it
                                                $decoded = json_decode($savedAnswer, true);
                                                if (is_array($decoded)) {
                                                    foreach ($decoded as $key => $value) {
                                                        if (is_numeric($key)) {
                                                            $savedAnswers[(int)$key] = $value;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        
                                        // Split by [[blank]] (after normalization)
                                        $parts = preg_split('/\[\[blank\]\]/', $normalizedText);
                                    @endphp
                                    <div class="fill-blank-container" data-question-id="{{ $question->id }}">
                                        <div class="p-4 bg-light rounded border">
                                            @foreach($parts as $index => $part)
                                                <span>{!! mixed_bidi_html($part) !!}</span>
                                                @if($index < count($parts) - 1)
                                                    <input type="text"
                                                           class="form-control d-inline-block fill-blank-input"
                                                           style="width: 150px; display: inline-block !important;"
                                                           name="question_{{ $question->id }}[{{ $index }}]"
                                                           value="{{ $savedAnswers[$index] ?? '' }}"
                                                           data-question-id="{{ $question->id }}"
                                                           data-blank-index="{{ $index }}"
                                                           placeholder="...">
                                        @endif
                                            @endforeach
                                    </div>
                                    </div>
                                    @break

                                @case('matching')
                                    @php
                                        $matchingOptions = $question->options;
                                        $answers = $matchingOptions->pluck('feedback')->shuffle();
                                        $savedAnswers = is_array($savedAnswer) ? $savedAnswer : [];
                                    @endphp
                                    <div class="matching-container">
                                        <div class="row mb-3">
                                            <div class="col-6 text-center">
                                                <strong class="text-primary"><i class="fas fa-question me-1"></i>السؤال</strong>
                                            </div>
                                            <div class="col-6 text-center">
                                                <strong class="text-success"><i class="fas fa-check me-1"></i>الإجابة</strong>
                                            </div>
                                        </div>
                                        @foreach($matchingOptions as $optionIndex => $option)
                                        <div class="row mb-3 align-items-center">
                                            <div class="col-6">
                                                <div class="p-3 border rounded bg-light">
                                                    <span class="badge bg-primary me-2">{{ $optionIndex + 1 }}</span>
                                                    {!! mixed_bidi_html($option->option_text) !!}
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <label for="matching_q{{ $question->id }}_opt{{ $option->id }}" class="matching-select-hit d-block w-100 mb-0 p-2 rounded">
                                                    <select class="form-select answer-input matching-select"
                                                            id="matching_q{{ $question->id }}_opt{{ $option->id }}"
                                                            name="question_{{ $question->id }}[{{ $option->id }}]"
                                                            data-question-id="{{ $question->id }}">
                                                        <option value="">-- اختر الإجابة --</option>
                                                        @foreach($answers as $answer)
                                                            <option value="{{ $answer }}" {{ isset($savedAnswers[$option->id]) && $savedAnswers[$option->id] == $answer ? 'selected' : '' }}>
                                                                {{ $answer }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </label>
                                            </div>
                                        </div>
                                        @endforeach
                                </div>
                                    @break

                                @case('drag_drop')
                                    @php
                                        $dropOptions = $question->options;
                                        $dragItems = $dropOptions->pluck('feedback', 'id')->shuffle();
                                        $savedAnswers = is_array($savedAnswer) ? $savedAnswer : [];
                                    @endphp
                                    <div class="drag-drop-container" data-question-id="{{ $question->id }}">
                                        <div class="row">
                                            <!-- Draggable Items -->
                                            <div class="col-md-4 mb-4">
                                                <div class="card border-primary">
                                                    <div class="card-header bg-primary text-white">
                                                        <i class="fas fa-hand-pointer me-2"></i>اسحب من هنا
                                                    </div>
                                                    <div class="card-body drag-items-container" id="drag-source-{{ $question->id }}">
                                                        @foreach($dragItems as $itemId => $itemText)
                                                            @php
                                                                $isUsed = in_array($itemText, $savedAnswers);
                                                            @endphp
                                                            <div class="drag-item {{ $isUsed ? 'd-none' : '' }}"
                                                                 draggable="true"
                                                                 data-item-id="{{ $itemId }}"
                                                                 data-item-text="{{ $itemText }}">
                                                                <i class="fas fa-grip-vertical me-2 text-muted"></i>
                                                                {{ $itemText }}
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Drop Zones -->
                                            <div class="col-md-8">
                                                <div class="card border-success">
                                                    <div class="card-header bg-success text-white">
                                                        <i class="fas fa-bullseye me-2"></i>أفلت هنا
                                                    </div>
                                                    <div class="card-body">
                                                        @foreach($dropOptions as $optionIndex => $option)
                                                        <div class="drop-zone-row mb-3">
                                                            <div class="d-flex align-items-center">
                                                                <div class="drop-zone-label flex-grow-1 p-3 bg-light rounded-start border">
                                                                    <span class="badge bg-primary me-2">{{ $optionIndex + 1 }}</span>
                                                                    {!! mixed_bidi_html($option->option_text) !!}
                                                                </div>
                                                                <div class="drop-zone rounded-end border border-start-0"
                                                                     data-option-id="{{ $option->id }}"
                                                                     data-question-id="{{ $question->id }}">
                                                                    @if(isset($savedAnswers[$option->id]))
                                                                        <div class="dropped-item"
                                                                             data-item-text="{{ $savedAnswers[$option->id] }}">
                                                                            {{ $savedAnswers[$option->id] }}
                                                                            <button type="button" class="btn-remove-item">
                                                                                <i class="fas fa-times"></i>
                                        </button>
                                                                        </div>
                                        @else
                                                                        <span class="drop-placeholder">
                                                                            <i class="fas fa-arrow-left me-1"></i>اسحب الإجابة هنا
                                                                        </span>
                                        @endif
                                    </div>
                                </div>
                                                            <input type="hidden"
                                                                   name="question_{{ $question->id }}[{{ $option->id }}]"
                                                                   value="{{ $savedAnswers[$option->id] ?? '' }}"
                                                                   class="drop-zone-input">
                            </div>
                        @endforeach
                </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @break

                                @case('ordering')
                                    @php
                                        $orderItems = $question->options->sortBy('option_order');
                                        $savedOrder = is_array($savedAnswer) ? $savedAnswer : [];
                                    @endphp
                                    <div class="ordering-container" data-question-id="{{ $question->id }}">
                                        <div class="alert alert-info mb-3">
                                            <i class="fas fa-info-circle me-2"></i>
                                            اسحب العناصر لترتيبها بالشكل الصحيح
                            </div>
                                        <div class="ordering-list" id="ordering-list-{{ $question->id }}">
                                            @php
                                                // If saved order exists, use it; otherwise shuffle for display
                                                if (!empty($savedOrder)) {
                                                    $displayItems = collect($savedOrder)->map(function($itemId) use ($orderItems) {
                                                        return $orderItems->firstWhere('id', $itemId);
                                                    })->filter();
                                                } else {
                                                    $displayItems = $orderItems->shuffle();
                                                }
                                            @endphp
                                            @foreach($displayItems as $itemIndex => $item)
                                                <div class="ordering-item"
                                                     draggable="true"
                                                     data-item-id="{{ $item->id }}"
                                                     data-question-id="{{ $question->id }}">
                                                    <div class="d-flex align-items-center">
                                                        <span class="ordering-handle me-3">
                                                            <i class="fas fa-grip-vertical"></i>
                                                        </span>
                                                        <span class="ordering-number me-3">{{ $itemIndex + 1 }}</span>
                                                        <span class="ordering-text">{!! mixed_bidi_html($item->option_text) !!}</span>
                        </div>
                                                </div>
                                @endforeach
                            </div>
                                        <input type="hidden"
                                               name="question_{{ $question->id }}"
                                               id="ordering-input-{{ $question->id }}"
                                               value="{{ json_encode($savedOrder) }}"
                                               class="ordering-input">
                                    </div>
                                    @break

                                @case('numerical')
                                    @php
                                        $metadata = $question->metadata ?? [];
                                        $tolerance = $metadata['tolerance'] ?? 0;
                                        $hint = $metadata['hint'] ?? null;
                                        // Ensure savedAnswer is a string for numerical input
                                        $numericalAnswer = is_array($savedAnswer) ? (isset($savedAnswer['answer']) ? (string)$savedAnswer['answer'] : (string)($savedAnswer[0] ?? '')) : (string)($savedAnswer ?? '');
                                    @endphp
                                    <div class="mb-3">
                                        @if($hint)
                                            <div class="alert alert-info mb-3">
                                                <i class="fas fa-lightbulb me-2"></i>
                                                <strong>ملاحظة:</strong> {{ $hint }}
                                            </div>
                                        @endif
                                        <input type="number"
                                               class="form-control answer-input"
                                               name="question_{{ $question->id }}"
                                               id="numerical_{{ $question->id }}"
                                               value="{{ $numericalAnswer }}"
                                               step="any"
                                               placeholder="أدخل الرقم..."
                                               data-question-id="{{ $question->id }}"
                                               style="font-size: 1.1rem; padding: 12px;">
                                        @if($tolerance > 0)
                                            <small class="text-muted mt-2 d-block">
                                                <i class="fas fa-info-circle me-1"></i>
                                                هامش الخطأ المسموح: ±{{ $tolerance }}
                                            </small>
                                        @endif
                                    </div>
                                    @break

                                @case('calculated')
                                    @php
                                        $metadata = $question->metadata ?? [];
                                        $tolerance = $metadata['tolerance'] ?? 0;
                                        $formula = $metadata['formula'] ?? null;
                                        // Ensure savedAnswer is a string for calculated input
                                        $calculatedAnswer = is_array($savedAnswer) ? (isset($savedAnswer['answer']) ? (string)$savedAnswer['answer'] : (string)($savedAnswer[0] ?? '')) : (string)($savedAnswer ?? '');
                                    @endphp
                            <div class="mb-3">
                                        @if($formula)
                                            <div class="alert alert-primary mb-3">
                                                <i class="fas fa-calculator me-2"></i>
                                                <strong>المعادلة:</strong> {{ $formula }}
                                            </div>
                                        @endif
                                        <input type="number"
                                               class="form-control answer-input"
                                               name="question_{{ $question->id }}"
                                               id="calculated_{{ $question->id }}"
                                               value="{{ $calculatedAnswer }}"
                                               step="any"
                                               placeholder="أدخل النتيجة..."
                                               data-question-id="{{ $question->id }}"
                                               style="font-size: 1.1rem; padding: 12px;">
                                        @if($tolerance > 0)
                                            <small class="text-muted mt-2 d-block">
                                                <i class="fas fa-info-circle me-1"></i>
                                                هامش الخطأ المسموح: ±{{ $tolerance }}
                                </small>
                                        @endif
                                    </div>
                                    @break
                            @endswitch

                            {{-- Fallback for unknown question types --}}
                            @if(!in_array($question->questionType->name, ['multiple_choice_single', 'multiple_choice_multiple', 'true_false', 'short_answer', 'essay', 'fill_blanks', 'matching', 'drag_drop', 'ordering', 'numerical', 'calculated']))
                                {{-- Debug: Unknown question type --}}
                                <script>
                                console.log('DEBUG: Unknown question type - fallback check', {
                                    question_id: {{ $question->id }},
                                    type_name: '{{ $question->questionType->name }}',
                                    type_id: {{ $question->question_type_id }}
                                });
                                </script>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    نوع السؤال غير معروف: <strong>{{ $question->questionType->name }}</strong>
                                    <br>
                                    <small>الرجاء التواصل مع المدير.</small>
                                </div>
                            @endif
                            </div>

                        <!-- Navigation Buttons -->
                        <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                            {{-- #region agent log --}}
                            <script>
                            console.log('DEBUG: Navigation buttons section', {
                                question_index: {{ $index }},
                                loop_last: {{ $loop->last ? 'true' : 'false' }},
                                loop_index: {{ $loop->index }},
                                total_questions: {{ $questions->count() }},
                                hypothesisId: 'D'
                            });
                            </script>
                            {{-- #endregion --}}
                            <button type="button"
                                    class="btn btn-outline-secondary"
                                    onclick="previousQuestion()"
                                    {{ $index === 0 ? 'disabled' : '' }}
                                    style="display: block !important;">
                                <i class="fas fa-arrow-right me-2"></i>السابق
                            </button>

                            @if(!$loop->last)
                                {{-- #region agent log --}}
                                <script>
                                console.log('DEBUG: Showing next button', {
                                    question_index: {{ $index }},
                                    loop_last: {{ $loop->last ? 'true' : 'false' }},
                                    total_questions: {{ $questions->count() }},
                                    is_last: {{ $loop->last ? 'true' : 'false' }},
                                    hypothesisId: 'D'
                                });
                                </script>
                                {{-- #endregion --}}
                                <button type="button"
                                        id="next-btn-{{ $index }}"
                                        class="btn btn-primary"
                                        onclick="nextQuestion()"
                                        style="display: block !important; visibility: visible !important; opacity: 1 !important;">
                                    التالي<i class="fas fa-arrow-left ms-2"></i>
                                </button>
                            @else
                                {{-- #region agent log --}}
                                <script>
                                console.log('DEBUG: Showing submit button (last question)', {
                                    question_index: {{ $index }},
                                    loop_last: {{ $loop->last ? 'true' : 'false' }},
                                    hypothesisId: 'D'
                                });
                                </script>
                                {{-- #endregion --}}
                                <button type="button"
                                        class="btn btn-success btn-lg"
                                        onclick="showSubmitConfirmation()"
                                        style="display: block !important;">
                                    <i class="fas fa-check me-2"></i>إرسال الاختبار
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </form>
            </div>
        </div>
    </div>
</div>
<!-- End::app-content -->

    <!-- Submit Confirmation Modal -->
    <div class="modal fade" id="submitModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>تأكيد الإرسال
                    </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                <p class="mb-3">هل أنت متأكد من إرسال الاختبار؟</p>
                <div class="alert alert-info">
                    <strong>ملخص إجاباتك:</strong>
                    <ul class="mb-0 mt-2">
                        <li>عدد الأسئلة المجابة: <strong><span id="submit-answered-count">0</span></strong></li>
                        <li>عدد الأسئلة غير المجابة: <strong><span id="submit-unanswered-count">0</span></strong></li>
                        </ul>
                    </div>
                <p class="text-danger mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    لن تتمكن من تعديل إجاباتك بعد الإرسال
                </p>
                </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-success" id="confirm-submit-quiz" onclick="submitQuiz()">
                    <i class="fas fa-check me-2"></i>إرسال الآن
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
<style>
    /* تفاعل خيارات الاختبار: انظر public/assets/css/custom.css (.quiz-option-hit وغيرها) */

    .question-nav-btn.answered {
        background-color: #28a745;
        color: white;
        border-color: #28a745;
    }

    .question-nav-btn.answered:hover {
        background-color: #218838;
        border-color: #1e7e34;
    }

    .question-nav-btn.active {
        background-color: #007bff;
        color: white;
        border-color: #007bff;
    }

    #timer-container.time-warning {
        background-color: #dc3545;
        color: white;
        border-color: #dc3545;
    }

    .sticky-top {
        z-index: 1020;
    }

    /* Drag and Drop Styles */
    .drag-item {
        padding: 12px 15px;
        margin-bottom: 10px;
        background: #fff;
        color: inherit;
        border: 1px solid var(--bs-border-color, #dee2e6);
        border-radius: 8px;
        cursor: grab;
        transition:
            transform 0.22s cubic-bezier(0.25, 0.8, 0.25, 1),
            box-shadow 0.28s ease,
            border-color 0.22s ease,
            background-color 0.22s ease;
        box-shadow: 0 0.125rem 0.4rem rgba(0, 0, 0, 0.04);
        user-select: none;
    }

    .drag-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.4rem 1rem rgba(0, 0, 0, 0.08);
        border-color: rgba(var(--bs-primary-rgb), 0.45);
        background-color: rgba(var(--bs-primary-rgb), 0.06);
    }

    .drag-item:active {
        cursor: grabbing;
        transform: scale(0.95);
    }

    .drag-item.dragging {
        opacity: 0.5;
    }

    .drop-zone {
        min-width: 200px;
        min-height: 50px;
        padding: 10px 15px;
        background: #f8f9fa;
        border: 2px dashed #dee2e6 !important;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .drop-zone.drag-over {
        background: #e8f5e9;
        border-color: #4caf50 !important;
        border-style: solid !important;
    }

    .drop-placeholder {
        color: #adb5bd;
        font-size: 0.85rem;
    }

    .dropped-item {
        background: rgba(var(--bs-success-rgb), 0.1);
        color: inherit;
        border: 1px solid rgba(var(--bs-success-rgb), 0.35);
        padding: 8px 12px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        animation: dropIn 0.3s ease;
    }

    @keyframes dropIn {
        from {
            transform: scale(0.8);
            opacity: 0;
        }
        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    .btn-remove-item {
        background: rgba(var(--bs-danger-rgb), 0.12);
        border: 1px solid rgba(var(--bs-danger-rgb), 0.35);
        color: var(--bs-danger);
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        margin-right: 8px;
        transition: transform 0.2s ease, background-color 0.2s ease;
    }

    .btn-remove-item:hover {
        background: rgba(var(--bs-danger-rgb), 0.2);
        transform: scale(1.1);
    }

    .drag-items-container {
        min-height: 100px;
    }

    .drag-items-container:empty::after {
        content: 'تم استخدام جميع العناصر';
        color: #6c757d;
        font-style: italic;
        display: block;
        text-align: center;
        padding: 20px;
    }

    /* Ordering Styles — خلفية بيضاء مثل باقي الخيارات + نفس أسلوب hover */
    .ordering-item {
        display: flex;
        align-items: center;
        padding: 15px;
        margin-bottom: 10px;
        background: #fff;
        color: inherit;
        border: 1px solid var(--bs-border-color, #dee2e6);
        border-radius: 8px;
        cursor: grab;
        transition:
            transform 0.22s cubic-bezier(0.25, 0.8, 0.25, 1),
            box-shadow 0.28s ease,
            border-color 0.22s ease,
            background-color 0.22s ease;
        box-shadow: 0 0.125rem 0.4rem rgba(0, 0, 0, 0.04);
        user-select: none;
    }

    .ordering-item:hover:not(.dragging) {
        transform: translateY(-2px);
        box-shadow: 0 0.4rem 1rem rgba(0, 0, 0, 0.08);
        border-color: rgba(var(--bs-primary-rgb), 0.45);
        background-color: rgba(var(--bs-primary-rgb), 0.06);
    }

    .ordering-item:active {
        cursor: grabbing;
        transform: translateY(0);
        transition-duration: 0.12s;
    }

    .ordering-item.dragging {
        opacity: 0.55;
    }

    .ordering-item.drag-over {
        border-top: 3px solid var(--bs-success, #198754);
    }

    .ordering-handle {
        color: var(--bs-secondary-color, #6c757d);
    }

    .ordering-number {
        background: rgba(var(--bs-primary-rgb), 0.12);
        color: var(--bs-primary);
        border: 1px solid rgba(var(--bs-primary-rgb), 0.22);
        padding: 5px 10px;
        border-radius: 50%;
        font-weight: bold;
        min-width: 30px;
        text-align: center;
    }

    .ordering-text {
        flex-grow: 1;
    }
</style>
@endpush

@push('scripts')
<script>
// Initialize global variables with actual values
attemptId = {{ $attempt->id }};
totalQuestions = {{ $questions->count() }};
currentQuestionIndex = 0;
answeredQuestions = new Set();
remainingTimeSeconds = {{ $remainingTime ?? 'null' }};

// Debug logging
console.log('=== Quiz Page Initialization ===');
console.log('Attempt ID:', attemptId);
console.log('Total Questions:', totalQuestions);
console.log('Remaining Time (seconds):', remainingTimeSeconds);

// Ensure remainingTimeSeconds is an integer
if (remainingTimeSeconds !== null) {
    remainingTimeSeconds = Math.floor(remainingTimeSeconds);
    console.log('Remaining Time (formatted):', remainingTimeSeconds, 'seconds');
} else {
    console.warn('Remaining time is null - timer will not start');
}

// Override head-scripts navigation: jQuery hide/show matches the rest of the page and avoids display quirks.
function goToQuestion(index) {
    index = parseInt(index, 10);
    if (isNaN(index) || typeof totalQuestions === 'undefined' || index < 0 || index >= totalQuestions) {
        return;
    }
    $('.question-container').hide();
    $('.question-container[data-question-index="' + index + '"]').show();
    currentQuestionIndex = index;
    if (typeof updateQuestionNavigation === 'function') {
        updateQuestionNavigation();
    }
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function nextQuestion() {
    if (currentQuestionIndex < totalQuestions - 1) {
        goToQuestion(currentQuestionIndex + 1);
    }
}

function previousQuestion() {
    if (currentQuestionIndex > 0) {
        goToQuestion(currentQuestionIndex - 1);
    }
}

// Initialize on page load
$(document).ready(function() {
    console.log('=== Document Ready ===');
    console.log('jQuery version:', $.fn.jquery);
    var domQuestionCount = $('.question-container').length;
    if (domQuestionCount > 0) {
        totalQuestions = domQuestionCount;
    }
    console.log('totalQuestions:', totalQuestions);
    console.log('remainingTimeSeconds:', remainingTimeSeconds);
    console.log('currentQuestionIndex:', currentQuestionIndex);
    
    try {
        // Check if questions exist
        if ($('.question-container').length === 0) {
            console.error('No question containers found!');
            return;
        }
        
        console.log('Question containers found:', $('.question-container').length);
        
        // Check if timer container exists
        if ($('#timer-container').length > 0) {
            console.log('Timer container found');
        } else {
            console.warn('Timer container not found in DOM');
        }
        
        console.log('Calling initializeAnswers...');
        initializeAnswers();
        console.log('Calling updateProgress...');
        updateProgress();
        console.log('Calling updateQuestionNavigation...');
        updateQuestionNavigation();
        
        if (remainingTimeSeconds !== null && remainingTimeSeconds > 0) {
            console.log('Starting timer with', remainingTimeSeconds, 'seconds');
            startTimer();
        } else {
            console.warn('Timer not started - remainingTimeSeconds is', remainingTimeSeconds);
        }
        
        console.log('=== Initialization Complete ===');
    } catch (error) {
        console.error('Error initializing quiz:', error);
        console.error('Error stack:', error.stack);
    }

        // Auto-save answers
        $('.answer-input').on('change', function() {
            const questionId = $(this).data('question-id');
            saveAnswer(questionId);
        });

        // Auto-save for textareas with delay
        let typingTimer;
        $('textarea.answer-input').on('input', function() {
            clearTimeout(typingTimer);
            const questionId = $(this).data('question-id');
            typingTimer = setTimeout(() => saveAnswer(questionId), 1000);
        });

        // Auto-save for fill in blank inputs
        let blankTimer;
        $(document).on('input', '.fill-blank-input', function() {
            clearTimeout(blankTimer);
        const questionId = $(this).data('question-id');
            blankTimer = setTimeout(() => saveFillBlankAnswer(questionId), 1000);
        });

        // Initialize drag and drop
        initDragAndDrop();

        // Initialize ordering
        initOrdering();
    });

    // Drag and Drop functionality
    function initDragAndDrop() {
        // Drag start
        $(document).on('dragstart', '.drag-item', function(e) {
            $(this).addClass('dragging');
            e.originalEvent.dataTransfer.setData('text/plain', $(this).data('item-text'));
            e.originalEvent.dataTransfer.effectAllowed = 'move';
        });

        // Drag end
        $(document).on('dragend', '.drag-item', function() {
            $(this).removeClass('dragging');
        });

        // Drag over drop zone
        $(document).on('dragover', '.drop-zone', function(e) {
            e.preventDefault();
            e.originalEvent.dataTransfer.dropEffect = 'move';
            $(this).addClass('drag-over');
        });

        // Drag leave drop zone
        $(document).on('dragleave', '.drop-zone', function() {
            $(this).removeClass('drag-over');
        });

        // Drop on drop zone
        $(document).on('drop', '.drop-zone', function(e) {
            e.preventDefault();
            $(this).removeClass('drag-over');

            const itemText = e.originalEvent.dataTransfer.getData('text/plain');
        const questionId = $(this).data('question-id');
            const optionId = $(this).data('option-id');

            // Check if zone already has an item
            if ($(this).find('.dropped-item').length > 0) {
                // Return existing item to source
                const existingText = $(this).find('.dropped-item').data('item-text');
                returnItemToSource(questionId, existingText);
            }

            // Add item to drop zone
            $(this).html(`
                <div class="dropped-item" data-item-text="${itemText}">
                    ${itemText}
                    <button type="button" class="btn-remove-item">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `);

            // Update hidden input
            $(this).closest('.drop-zone-row').find('.drop-zone-input').val(itemText);

            // Hide dragged item from source
            $(`.drag-item[data-item-text="${itemText}"]`).addClass('d-none');

            // Save answer
            saveDragDropAnswer(questionId);
        });

        // Remove item from drop zone
        $(document).on('click', '.btn-remove-item', function() {
            const dropZone = $(this).closest('.drop-zone');
            const questionId = dropZone.data('question-id');
            const itemText = $(this).closest('.dropped-item').data('item-text');

            // Return item to source
            returnItemToSource(questionId, itemText);

            // Reset drop zone
            dropZone.html(`
                <span class="drop-placeholder">
                    <i class="fas fa-arrow-left me-1"></i>اسحب الإجابة هنا
                </span>
            `);

            // Clear hidden input
            dropZone.closest('.drop-zone-row').find('.drop-zone-input').val('');

            // Save answer
            saveDragDropAnswer(questionId);
        });
    }

    function returnItemToSource(questionId, itemText) {
        $(`#drag-source-${questionId} .drag-item[data-item-text="${itemText}"]`).removeClass('d-none');
    }

    function saveDragDropAnswer(questionId) {
        const answer = {};
        let allAnswered = true;

        $(`.drop-zone[data-question-id="${questionId}"]`).each(function() {
            const optionId = $(this).data('option-id');
            const input = $(this).closest('.drop-zone-row').find('.drop-zone-input');
            const value = input.val();

            if (value) {
                answer[optionId] = value;
        } else {
                allAnswered = false;
            }
        });

        // Update answered questions set
        if (allAnswered && Object.keys(answer).length > 0) {
            answeredQuestions.add(parseInt(questionId));
        } else {
            answeredQuestions.delete(parseInt(questionId));
        }

        updateProgress();
        updateQuestionNavigation();

        // Send AJAX request
        if (Object.keys(answer).length > 0) {
            $.ajax({
                url: "{{ route('student.quizzes.save-answer', $attempt->id) }}",
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    question_id: questionId,
                    answer: answer
                },
                success: function(response) {
                    console.log('Drag & drop answer saved:', response);
                },
                error: function(xhr) {
                    console.error('Error saving answer:', xhr);
                }
            });
        }
    }

    function saveFillBlankAnswer(questionId) {
        questionId = parseInt(questionId); // Ensure it's a number
        const answer = {};
        let allAnswered = true;
        let hasAnyAnswer = false;

        $(`.fill-blank-input[data-question-id="${questionId}"]`).each(function() {
            const blankIndex = $(this).data('blank-index');
            const value = $(this).val().trim();

            if (value) {
                answer[blankIndex] = value;
                hasAnyAnswer = true;
            } else {
                allAnswered = false;
            }
        });

        // Update answered questions set - mark as answered if at least one blank is filled
        if (hasAnyAnswer && Object.keys(answer).length > 0) {
            answeredQuestions.add(questionId);
        } else {
            answeredQuestions.delete(questionId);
        }

        updateProgress();
        updateQuestionNavigation();

        // Send AJAX request - save even if not all blanks are filled
        if (Object.keys(answer).length > 0) {
            $.ajax({
                url: "{{ route('student.quizzes.save-answer', $attempt->id) }}",
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    question_id: questionId,
                    answer: answer
                },
                success: function(response) {
                    console.log('Fill blank answer saved:', response);
                    // Update answered status based on response
                    if (response.success && hasAnyAnswer) {
                        answeredQuestions.add(questionId);
                        updateProgress();
                        updateQuestionNavigation();
                    }
                },
                error: function(xhr) {
                    console.error('Error saving fill blank answer:', xhr);
                    if (xhr.responseJSON && xhr.responseJSON.time_up) {
                        timeUp();
                    }
                }
            });
        } else {
            // Even if no answer, try to save empty answer to clear previous answers
            $.ajax({
                url: "{{ route('student.quizzes.save-answer', $attempt->id) }}",
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    question_id: questionId,
                    answer: {}
                },
                success: function(response) {
                    console.log('Fill blank answer cleared:', response);
                },
                error: function(xhr) {
                    console.error('Error clearing fill blank answer:', xhr);
                }
            });
        }
    }

    // Ordering functionality
    function initOrdering() {
        let draggedItem = null;

        // Drag start
        $(document).on('dragstart', '.ordering-item', function(e) {
            draggedItem = this;
            $(this).addClass('dragging');
            e.originalEvent.dataTransfer.effectAllowed = 'move';
        });

        // Drag end
        $(document).on('dragend', '.ordering-item', function() {
            $(this).removeClass('dragging');
            $('.ordering-item').removeClass('drag-over');
            draggedItem = null;
        });

        // Drag over
        $(document).on('dragover', '.ordering-item', function(e) {
            e.preventDefault();
            if (this !== draggedItem) {
                $(this).addClass('drag-over');
            }
        });

        // Drag leave
        $(document).on('dragleave', '.ordering-item', function() {
            $(this).removeClass('drag-over');
        });

        // Drop
        $(document).on('drop', '.ordering-item', function(e) {
            e.preventDefault();
            $(this).removeClass('drag-over');

            if (draggedItem && this !== draggedItem) {
                const list = $(this).parent();
                const questionId = $(draggedItem).data('question-id');

                // Insert before or after based on position
                const draggedIndex = $(draggedItem).index();
                const targetIndex = $(this).index();

                if (draggedIndex < targetIndex) {
                    $(draggedItem).insertAfter(this);
                } else {
                    $(draggedItem).insertBefore(this);
                }

                // Update numbers
                updateOrderingNumbers(list);

                // Save answer
                saveOrderingAnswer(questionId);
            }
        });
    }

    function updateOrderingNumbers(list) {
        list.find('.ordering-item').each(function(index) {
            $(this).find('.ordering-number').text(index + 1);
        });
    }

    function saveOrderingAnswer(questionId) {
        const order = [];
        $(`#ordering-list-${questionId} .ordering-item`).each(function() {
            order.push($(this).data('item-id'));
        });

        // Update hidden input
        $(`#ordering-input-${questionId}`).val(JSON.stringify(order));

        // Update answered questions
        if (order.length > 0) {
            answeredQuestions.add(parseInt(questionId));
        }

        updateProgress();
        updateQuestionNavigation();

        // Send AJAX request
    $.ajax({
            url: "{{ route('student.quizzes.save-answer', $attempt->id) }}",
        method: 'POST',
        data: {
                _token: '{{ csrf_token() }}',
                question_id: questionId,
                answer: order
        },
        success: function(response) {
                console.log('Ordering answer saved:', response);
            },
            error: function(xhr) {
                console.error('Error saving answer:', xhr);
        }
    });
}

    // Initialize answered questions from saved responses
    function initializeAnswers() {
        console.log('=== Initializing Answers ===');

        $('.question-container').each(function() {
            const questionId = parseInt($(this).data('question-id'));
            console.log('Checking question ID:', questionId);

            let hasAnswer = false;

            // Check for radio buttons
            const radioInputs = $(`input[type="radio"][name="question_${questionId}"]`);
            if (radioInputs.length > 0) {
                hasAnswer = radioInputs.filter(':checked').length > 0;
                console.log('Question', questionId, '(radio) - has answer:', hasAnswer);
            }

            // Check for checkboxes
            const checkboxInputs = $(`input[type="checkbox"][name="question_${questionId}[]"]`);
            if (checkboxInputs.length > 0) {
                hasAnswer = checkboxInputs.filter(':checked').length > 0;
                console.log('Question', questionId, '(checkbox) - has answer:', hasAnswer);
            }

            // Check for textareas
            const textareaInput = $(`textarea[name="question_${questionId}"]`);
            if (textareaInput.length > 0) {
                const value = textareaInput.val();
                hasAnswer = value && value.trim() !== '';
                console.log('Question', questionId, '(textarea) - has answer:', hasAnswer, '- value:', value);
            }

            // Check for numerical input
            const numericalInput = $(`input[type="number"][name="question_${questionId}"]#numerical_${questionId}`);
            if (numericalInput.length > 0) {
                const value = numericalInput.val();
                hasAnswer = value !== '' && value !== null && !isNaN(value);
                console.log('Question', questionId, '(numerical) - has answer:', hasAnswer, '- value:', value);
            }

            // Check for calculated input
            const calculatedInput = $(`input[type="number"][name="question_${questionId}"]#calculated_${questionId}`);
            if (calculatedInput.length > 0) {
                const value = calculatedInput.val();
                hasAnswer = value !== '' && value !== null && !isNaN(value);
                console.log('Question', questionId, '(calculated) - has answer:', hasAnswer, '- value:', value);
            }

            // Check for matching selects
            const matchingSelects = $(`select[name^="question_${questionId}["]`);
            if (matchingSelects.length > 0) {
                let allAnswered = true;
                matchingSelects.each(function() {
                    if (!$(this).val()) {
                        allAnswered = false;
                    }
                });
                hasAnswer = allAnswered;
                console.log('Question', questionId, '(matching) - has answer:', hasAnswer);
            }

            // Check for drag and drop
            const dropZones = $(`.drop-zone[data-question-id="${questionId}"]`);
            if (dropZones.length > 0) {
                let allDropped = true;
                dropZones.each(function() {
                    const input = $(this).closest('.drop-zone-row').find('.drop-zone-input');
                    if (!input.val()) {
                        allDropped = false;
                    }
                });
                hasAnswer = allDropped;
                console.log('Question', questionId, '(drag_drop) - has answer:', hasAnswer);
            }

            // Check for fill in blank inputs
            const fillBlankInputs = $(`.fill-blank-input[data-question-id="${questionId}"]`);
            if (fillBlankInputs.length > 0) {
                let allFilled = true;
                fillBlankInputs.each(function() {
                    if (!$(this).val().trim()) {
                        allFilled = false;
                    }
                });
                hasAnswer = allFilled;
                console.log('Question', questionId, '(fill_blank) - has answer:', hasAnswer);
            }

            if (hasAnswer) {
                answeredQuestions.add(questionId);
                console.log('✓ Question', questionId, 'marked as answered');
            }
        });

        console.log('Total answered questions:', answeredQuestions.size);
        console.log('Answered question IDs:', Array.from(answeredQuestions));
    }

    // Timer functionality
    function startTimer() {
        console.log('startTimer() called');
        console.log('remainingTimeSeconds:', remainingTimeSeconds);
        
        if (remainingTimeSeconds === null || remainingTimeSeconds === undefined || remainingTimeSeconds <= 0) {
            console.error('Cannot start timer - remainingTimeSeconds is invalid:', remainingTimeSeconds);
            return;
        }
        
        // Check if timer elements exist
        const timerContainer = $('#timer-container');
        const timerMinutes = $('#timer-minutes');
        const timerSeconds = $('#timer-seconds');
        
        if (timerContainer.length === 0) {
            console.error('Timer container not found in DOM');
            return;
        }
        
        if (timerMinutes.length === 0 || timerSeconds.length === 0) {
            console.error('Timer display elements not found in DOM');
            console.log('timerMinutes found:', timerMinutes.length);
            console.log('timerSeconds found:', timerSeconds.length);
            return;
        }

        console.log('Initializing timer display...');
        updateTimerDisplay();

        console.log('Starting timer interval...');
        timerInterval = setInterval(function() {
            remainingTimeSeconds--;
            updateTimerDisplay();

            // Warning at 5 minutes
            if (remainingTimeSeconds === 300) {
                $('#timer-container').addClass('time-warning');
                showToast('تحذير: تبقى 5 دقائق فقط!', 'warning');
            }

            // Time up
            if (remainingTimeSeconds <= 0) {
                console.log('Time is up!');
                clearInterval(timerInterval);
                timeUp();
            }
        }, 1000);
        
        console.log('Timer started successfully');
    }

    function updateTimerDisplay() {
        // Ensure remainingTimeSeconds is an integer
        remainingTimeSeconds = Math.floor(remainingTimeSeconds);
        const minutes = Math.floor(remainingTimeSeconds / 60);
        const seconds = Math.floor(remainingTimeSeconds % 60);
        
        const minutesStr = String(minutes).padStart(2, '0');
        const secondsStr = String(seconds).padStart(2, '0');
        
        $('#timer-minutes').text(minutesStr);
        $('#timer-seconds').text(secondsStr);
        
        // Debug log every 10 seconds
        if (remainingTimeSeconds % 10 === 0) {
            console.log('Timer update:', minutesStr + ':' + secondsStr, '(' + remainingTimeSeconds + ' seconds remaining)');
        }
    }

    function timeUp() {
        Swal.fire({
            title: 'انتهى الوقت!',
            text: 'تم انتهاء الوقت المحدد للاختبار وسيتم إرسال إجاباتك تلقائياً',
            icon: 'warning',
            showConfirmButton: false,
            allowOutsideClick: false,
            timer: 3000
        }).then(() => {
            submitQuiz(true);
        });
    }

    // Save answer via AJAX
    function saveAnswer(questionId) {
        questionId = parseInt(questionId); // Ensure it's a number
        console.log('Saving answer for question:', questionId);

        let answer = null;
        let hasValidAnswer = false;

        // Check for radio buttons
        const radioInput = $(`input[type="radio"][name="question_${questionId}"]:checked`);
        if (radioInput.length > 0) {
            answer = radioInput.val();
            hasValidAnswer = true;
            console.log('Radio answer:', answer);
        }

        // Check for checkboxes
        const checkboxInputs = $(`input[type="checkbox"][name="question_${questionId}[]"]:checked`);
        if (checkboxInputs.length > 0) {
            answer = checkboxInputs.map(function() {
                return $(this).val();
            }).get();
            hasValidAnswer = answer.length > 0;
            console.log('Checkbox answer:', answer);
        }

        // Check for textareas
        const textareaInput = $(`textarea[name="question_${questionId}"]`);
        if (textareaInput.length > 0) {
            answer = textareaInput.val();
            hasValidAnswer = answer && answer.trim() !== '';
            console.log('Textarea answer:', answer, '- valid:', hasValidAnswer);
        }

        // Check for numerical input
        const numericalInput = $(`input[type="number"][name="question_${questionId}"]#numerical_${questionId}`);
        if (numericalInput.length > 0) {
            answer = numericalInput.val();
            hasValidAnswer = answer !== '' && answer !== null && !isNaN(answer);
            console.log('Numerical answer:', answer, '- valid:', hasValidAnswer);
        }

        // Check for calculated input
        const calculatedInput = $(`input[type="number"][name="question_${questionId}"]#calculated_${questionId}`);
        if (calculatedInput.length > 0) {
            answer = calculatedInput.val();
            hasValidAnswer = answer !== '' && answer !== null && !isNaN(answer);
            console.log('Calculated answer:', answer, '- valid:', hasValidAnswer);
        }

        // Check for matching selects
        const matchingSelects = $(`select[name^="question_${questionId}["]`);
        if (matchingSelects.length > 0) {
            answer = {};
            let allAnswered = true;
            matchingSelects.each(function() {
                const optionId = $(this).attr('name').match(/\[(\d+)\]/)[1];
                const val = $(this).val();
                if (val) {
                    answer[optionId] = val;
                } else {
                    allAnswered = false;
                }
            });
            hasValidAnswer = allAnswered && Object.keys(answer).length > 0;
            console.log('Matching answer:', answer, '- valid:', hasValidAnswer);
        }

        // Check for fill in blank inputs
        const fillBlankInputs = $(`.fill-blank-input[data-question-id="${questionId}"]`);
        if (fillBlankInputs.length > 0) {
            answer = {};
            let hasAnyAnswer = false;
            fillBlankInputs.each(function() {
                const blankIndex = $(this).data('blank-index');
                const value = $(this).val().trim();
                if (value) {
                    answer[blankIndex] = value;
                    hasAnyAnswer = true;
                }
            });
            // Mark as valid if at least one blank is filled
            hasValidAnswer = hasAnyAnswer && Object.keys(answer).length > 0;
            console.log('Fill blank answer:', answer, '- valid:', hasValidAnswer);
        }

        // Update answered questions set
        if (hasValidAnswer) {
            answeredQuestions.add(questionId);
            console.log('Question', questionId, 'added to answered set');
        } else {
            answeredQuestions.delete(questionId);
            console.log('Question', questionId, 'removed from answered set');
        }

        console.log('Current answered questions:', Array.from(answeredQuestions));
        updateProgress();
        updateQuestionNavigation();

        // Send AJAX request and return promise
        if (hasValidAnswer) {
            return $.ajax({
                url: "{{ route('student.quizzes.save-answer', $attempt->id) }}",
        method: 'POST',
        data: {
                    _token: '{{ csrf_token() }}',
                    question_id: questionId,
                    answer: answer
                },
                success: function(response) {
                    console.log('Answer saved to server:', response);
                },
                error: function(xhr) {
                    console.error('Error saving answer:', xhr);
                    if (xhr.responseJSON && xhr.responseJSON.time_up) {
                        timeUp();
                    }
                }
            });
        }
        return Promise.resolve();
    }

    // Update progress bar
    function updateProgress() {
        const answeredCount = answeredQuestions.size;
        const percentage = (answeredCount / totalQuestions) * 100;
        $('#answered-count').text(answeredCount);
        $('#progress-bar').css('width', percentage + '%');
    }

    // Update question navigation buttons
    function updateQuestionNavigation() {
        try {
            console.log('updateQuestionNavigation called');
            console.log('Current question index:', currentQuestionIndex);
            console.log('Answered questions:', Array.from(answeredQuestions));
            
            // Use both jQuery and vanilla JS for compatibility
            if (typeof $ !== 'undefined') {
                $('.question-nav-btn').each(function() {
                    const questionId = parseInt($(this).data('question-id'), 10);
                    const questionIndex = parseInt($(this).attr('data-question-index'), 10);

                    $(this).removeClass('answered active');

                    if (answeredQuestions && answeredQuestions.has(questionId)) {
                        $(this).addClass('answered');
                    }

                    if (!isNaN(questionIndex) && questionIndex === currentQuestionIndex) {
                        $(this).addClass('active');
                    }
                });
            } else {
                // Fallback to vanilla JS
                document.querySelectorAll('.question-nav-btn').forEach(function(btn) {
                    const questionId = parseInt(btn.getAttribute('data-question-id'), 10);
                    const questionIndex = parseInt(btn.getAttribute('data-question-index'), 10);
                    
                    btn.classList.remove('answered', 'active');
                    
                    if (answeredQuestions && answeredQuestions.has(questionId)) {
                        btn.classList.add('answered');
                    }
                    
                    if (!isNaN(questionIndex) && questionIndex === currentQuestionIndex) {
                        btn.classList.add('active');
                    }
                });
            }
            
            console.log('Question navigation updated successfully');
        } catch (error) {
            console.error('Error in updateQuestionNavigation:', error);
        }
    }

    // Navigation functions are now defined in global scope above (before document.ready)
    // These duplicate definitions are removed to avoid conflicts

    const SUBMIT_SAVE_TIMEOUT_MS = 10000;

    function showSubmitConfirmation() {
        const answeredCount = answeredQuestions.size;
        const unansweredCount = totalQuestions - answeredCount;

        $('#submit-answered-count').text(answeredCount);
        $('#submit-unanswered-count').text(unansweredCount);

        const el = document.getElementById('submitModal');
        if (el) {
            const submitModal = bootstrap.Modal.getOrCreateInstance(el);
            submitModal.show();
        }
    }

    // Submit quiz
    function submitQuiz(autoSubmit = false) {
        isSubmitting = true;

        const confirmBtn = document.getElementById('confirm-submit-quiz');
        if (confirmBtn) {
            confirmBtn.disabled = true;
        }

        const modalEl = document.getElementById('submitModal');
        if (modalEl) {
            const inst = bootstrap.Modal.getInstance(modalEl);
            if (inst) {
                inst.hide();
            }
        }

        window.removeEventListener('beforeunload', preventUnload);

        if (timerInterval) {
            clearInterval(timerInterval);
        }

        const savePromises = [];
        $('.question-container').each(function() {
            const questionId = parseInt($(this).data('question-id'));
            if (questionId) {
                console.log('Saving answer for question:', questionId, 'before submit');
                const promise = saveAnswer(questionId);
                if (promise && promise.then) {
                    savePromises.push(promise.catch(function(err) {
                        console.error('Save failed for question', questionId, err);
                    }));
                } else {
                    savePromises.push(Promise.resolve());
                }
            }
        });

        console.log('Total promises to wait for:', savePromises.length);

        const saveAllSettled = savePromises.length > 0
            ? Promise.all(savePromises)
            : Promise.resolve();

        const timeoutPromise = new Promise(function(resolve) {
            setTimeout(function() {
                console.warn('Submit: save wait capped at', SUBMIT_SAVE_TIMEOUT_MS, 'ms; proceeding.');
                resolve();
            }, SUBMIT_SAVE_TIMEOUT_MS);
        });

        Promise.race([saveAllSettled, timeoutPromise]).then(function() {
            setTimeout(function() {
                submitForm();
            }, 300);
        }).catch(function(error) {
            console.error('Error saving answers:', error);
            setTimeout(function() {
                submitForm();
            }, 300);
        });
        
        function submitForm() {
            const form = $('<form>', {
                method: 'POST',
                action: "{{ route('student.quizzes.submit', $attempt->id) }}"
            });

            form.append($('<input>', {
                type: 'hidden',
                name: '_token',
                value: '{{ csrf_token() }}'
            }));

            // Collect all answers from the current form state and add to form
            console.log('Collecting all answers for final submission...');
            $('.question-container').each(function() {
                const questionId = $(this).data('question-id');
                let answer = null;
                
                // Radio buttons
                const radioInput = $(`input[type="radio"][name="question_${questionId}"]:checked`);
                if (radioInput.length > 0) {
                    answer = radioInput.val();
                    console.log('Question', questionId, '- Radio answer:', answer);
                }

                // Checkboxes
                const checkboxInputs = $(`input[type="checkbox"][name="question_${questionId}[]"]:checked`);
                if (checkboxInputs.length > 0) {
                    answer = checkboxInputs.map(function() { return $(this).val(); }).get();
                    console.log('Question', questionId, '- Checkbox answer:', answer);
                }

                // Textareas
                const textareaInput = $(`textarea[name="question_${questionId}"]`);
                if (textareaInput.length > 0) {
                    answer = textareaInput.val();
                    console.log('Question', questionId, '- Textarea answer:', answer);
                }

                // Numerical input
                const numericalInput = $(`input[type="number"][name="question_${questionId}"]#numerical_${questionId}`);
                if (numericalInput.length > 0) {
                    answer = numericalInput.val();
                    console.log('Question', questionId, '- Numerical answer:', answer);
                }

                // Calculated input
                const calculatedInput = $(`input[type="number"][name="question_${questionId}"]#calculated_${questionId}`);
                if (calculatedInput.length > 0) {
                    answer = calculatedInput.val();
                    console.log('Question', questionId, '- Calculated answer:', answer);
                }

                // Matching selects
                const matchingSelects = $(`select[name^="question_${questionId}["]`);
                if (matchingSelects.length > 0) {
                    answer = {};
                    matchingSelects.each(function() {
                        const optionId = $(this).attr('name').match(/\[(\d+)\]/)[1];
                        const val = $(this).val();
                        if (val) {
                            answer[optionId] = val;
                        }
                    });
                    console.log('Question', questionId, '- Matching answer:', answer);
                }

                // Ordering
                const orderingInput = $(`#ordering-input-${questionId}`);
                if (orderingInput.length > 0) {
                    answer = JSON.parse(orderingInput.val() || '[]');
                    console.log('Question', questionId, '- Ordering answer:', answer);
                }

                // Fill in blanks
                const fillBlankInputs = $(`.fill-blank-input[data-question-id="${questionId}"]`);
                if (fillBlankInputs.length > 0) {
                    answer = {};
                    fillBlankInputs.each(function() {
                        const blankIndex = $(this).data('blank-index');
                        const value = $(this).val().trim();
                        if (value) {
                            answer[blankIndex] = value;
                        }
                    });
                    console.log('Question', questionId, '- Fill blank answer:', answer);
                }

                if (answer !== null) {
                    form.append($('<input>', {
                        type: 'hidden',
                        name: `answers[${questionId}]`,
                        value: JSON.stringify(answer)
                    }));
                    console.log('Added answer for question', questionId, 'to form');
                } else {
                    console.warn('No answer found for question', questionId);
                }
            });

            console.log('Submitting form with all answers...');
            $('body').append(form);
            form.submit();
        }
    }

    window.showSubmitConfirmation = showSubmitConfirmation;
    window.submitQuiz = submitQuiz;
    window.goToQuestion = goToQuestion;
    window.nextQuestion = nextQuestion;
    window.previousQuestion = previousQuestion;
    
    // Prevent accidental page close - only when quiz is in progress
    function preventUnload(e) {
        // Don't show warning if quiz is being submitted
        if (isSubmitting) {
            return;
        }
        
        // Only show warning if there are answered questions
        if (answeredQuestions.size > 0) {
            e.preventDefault();
            e.returnValue = '';
            return '';
        }
    }
    
    // Add event listener
    window.addEventListener('beforeunload', preventUnload);

    // Toast notification
    function showToast(message, type = 'info') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-start',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });

        Toast.fire({
            icon: type,
            title: message
        });
    }

</script>
@endpush
