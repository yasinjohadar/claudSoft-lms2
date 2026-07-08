@extends('admin.layouts.master')

@include('shared.quizzes.take-assets')

@section('page-title', 'تجربة الاختبار - ' . $attempt->quiz->title)

@push('head-scripts')
<meta name="turbo-visit-control" content="reload">
<script>
var attemptId = {{ $attempt->id }};
var totalQuestions = {{ $questions->count() }};
var currentQuestionIndex = 0;
var answeredQuestions = new Set();
var remainingTimeSeconds = {{ $remainingTime ?? 'null' }};
var timerInterval = null;
var isSubmitting = false;

window.totalQuestions = totalQuestions;
window.currentQuestionIndex = currentQuestionIndex;

if (remainingTimeSeconds !== null) {
    remainingTimeSeconds = Math.floor(remainingTimeSeconds);
}
</script>
@endpush

@section('content')
    @php
        $quizEndsAtMs = $attempt->getQuizEndsAtMs();
    @endphp
    <div class="main-content app-content quiz-take-page">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="alert alert-warning border-warning d-flex align-items-center gap-2 mb-4" role="alert">
                <i class="fe fe-eye fs-4"></i>
                <div>
                    <strong>وضع معاينة للأدمن</strong> — هذه التجربة لن تُحسب في إحصائيات الطلاب ولا في التصحيح.
                    <a href="{{ $quiz->adminShowRoute() }}" class="alert-link ms-2">العودة لتفاصيل الاختبار</a>
                </div>
            </div>

            <div class="quiz-take-header">
                <h4 class="quiz-take-header__title">
                    <i class="fe fe-clipboard"></i>
                    {{ $attempt->quiz->title }}
                </h4>
                <div class="quiz-take-header__meta">
                    <span class="quiz-take-chip"><i class="fe fe-help-circle"></i>{{ $questions->count() }} سؤال</span>
                </div>
            </div>

            @include('shared.quizzes.take-mobile-chrome', ['questions' => $questions])

            <div class="row quiz-take-layout">
                <div class="col-12 col-lg-3 mb-4 quiz-take-sidebar-col" id="quiz-take-sidebar-col">
                    <aside class="card quiz-take-sidebar sticky-top">
                        <div class="card-header text-white">
                            <h5 class="mb-0">
                                <i class="fe fe-grid me-2"></i>
                                الأسئلة
                            </h5>
                        </div>
                        <div class="card-body">
                    @if($remainingTime !== null && $remainingTime > 0)
                    <div class="quiz-take-timer" id="timer-container"
                         data-remaining-seconds="{{ (int) $remainingTime }}"
                         data-server-now-ms="{{ $serverNowMs ?? (int) (now()->getTimestamp() * 1000) }}"
                         @if($quizEndsAtMs) data-ends-at="{{ $quizEndsAtMs }}" @endif>
                        <div class="quiz-take-timer__label"><i class="fe fe-clock me-1"></i>الوقت المتبقي</div>
                        <div class="quiz-take-timer__value" id="timer">
                            <span id="timer-minutes">{{ str_pad(floor($remainingTime / 60), 2, '0', STR_PAD_LEFT) }}</span>:<span id="timer-seconds">{{ str_pad($remainingTime % 60, 2, '0', STR_PAD_LEFT) }}</span>
                        </div>
                    </div>
                    <script>
                    document.addEventListener('quiz-take-timer:ready', function () {
                        if (window.QuizTakeTimer) {
                            window.QuizTakeTimer.start();
                        }
                    }, { once: true });
                    </script>
            @elseif($attempt->quiz->time_limit === null)
                    <div class="quiz-take-timer mb-3">
                        <div class="quiz-take-timer__label"><i class="fe fe-infinity me-1"></i>بدون حد زمني</div>
                    </div>
            @endif

                    <div class="quiz-take-progress mb-3">
                        <div class="quiz-take-progress__head">
                            <span class="text-muted">التقدم</span>
                            <span class="fw-bold"><span id="answered-count">0</span> / {{ $questions->count() }}</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" id="progress-bar" style="width: 0%"></div>
                        </div>
                    </div>

                    <div class="questions-grid quiz-take-nav-grid">
                        @foreach($questions as $index => $question)
                        <button type="button"
                                class="btn btn-outline-secondary btn-sm question-nav-btn quiz-take-nav-btn"
                                data-question-index="{{ $index }}"
                                data-question-id="{{ $question->id }}"
                                onclick="goToQuestion({{ $index }})"
                                aria-label="السؤال {{ $index + 1 }}">
                            {{ $index + 1 }}
                        </button>
                        @endforeach
                    </div>
                        </div>
                    </aside>
                </div>

        <div class="col-12 col-lg-9 quiz-take-main">
            <form id="quiz-form">
                        @csrf
                @if($questions->isEmpty())
                    <div class="alert alert-danger mb-0" id="quiz-no-questions-fallback" role="alert">
                        <i class="fe fe-alert-triangle me-2"></i>
                        تعذر تحميل أسئلة الاختبار. يرجى
                        <button type="button" class="btn btn-sm btn-outline-danger ms-1" onclick="window.location.reload()">إعادة تحميل الصفحة</button>
                        أو التواصل مع الدعم الفني.
                    </div>
                @endif
                @foreach($questions as $question)
                @php $index = $loop->index; @endphp
                <div class="question-container card mb-4{{ $index === 0 ? ' is-active' : '' }}"
                     data-question-index="{{ $index }}"
                     data-question-id="{{ $question->id }}">
                    <div class="card-header quiz-take-q-header">
                        <div class="quiz-take-q-badges">
                            <span class="badge bg-primary">السؤال {{ $index + 1 }}</span>
                            <span class="badge bg-info-transparent text-info">{{ $question->questionType->display_name }}</span>
                            <span class="badge bg-success-transparent text-success">{{ $question->pivot->question_grade }} نقطة</span>
                        </div>
                    </div>
                                <div class="card-body pt-4">
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

                            @switch($question->questionType->name)
                                @case('multiple_choice_single')
                                    @php
                                        $optionsCollection = $question->options ?? collect();
                                        $optionsCount = $optionsCollection->count();
                                    @endphp
                                    @if($optionsCount > 0)
                                        @foreach($question->options as $option)
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
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            لا توجد خيارات متاحة لهذا السؤال. يرجى التواصل مع المدير.
                                        </div>
                                    @endif
                                    @break

                                @case('multiple_choice_multiple')
                                    @php
                                        $optionsCollection = $question->options ?? collect();
                                        $optionsCount = $optionsCollection->count();
                                    @endphp
                                    @if($optionsCount > 0)
                                        @foreach($question->options as $option)
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
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            لا توجد خيارات متاحة لهذا السؤال. يرجى التواصل مع المدير.
                                        </div>
                                    @endif
                                    @break

                                @case('true_false')
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
                                            <div class="ordering-drop-hint" hidden role="status" aria-live="polite">
                                                <i class="fas fa-hand-pointer me-2"></i>
                                                يمكنك الإفلات الآن — أسقط العنصر عند الشريط الأخضر
                                            </div>
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
                                        <div class="ordering-drop-success" hidden role="status" aria-live="polite">
                                            <i class="fas fa-check-circle me-2"></i>
                                            تم تحديث الترتيب بنجاح
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
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    نوع السؤال غير معروف: <strong>{{ $question->questionType->name }}</strong>
                                    <br>
                                    <small>الرجاء التواصل مع المدير.</small>
                                </div>
                            @endif
                            </div>

                        <div class="quiz-take-nav-row d-none d-lg-flex">
                            <button type="button"
                                    class="btn btn-outline-secondary"
                                    onclick="previousQuestion()"
                                    {{ $index === 0 ? 'disabled' : '' }}>
                                <i class="fe fe-chevron-right me-2"></i>السابق
                            </button>

                            @if(!$loop->last)
                                <button type="button"
                                        id="next-btn-{{ $index }}"
                                        class="btn btn-primary"
                                        onclick="nextQuestion()">
                                    التالي<i class="fe fe-chevron-left ms-2"></i>
                                </button>
                            @else
                                <button type="button"
                                        class="btn btn-success"
                                        onclick="showSubmitConfirmation()">
                                    <i class="fe fe-check me-2"></i>إرسال الاختبار
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

    @include('shared.quizzes.submit-modal')

@endsection

@push('scripts')
<script>
attemptId = {{ $attempt->id }};
totalQuestions = {{ $questions->count() }};
currentQuestionIndex = 0;
answeredQuestions = new Set();
remainingTimeSeconds = {{ $remainingTime ?? 'null' }};

window.totalQuestions = totalQuestions;
window.currentQuestionIndex = currentQuestionIndex;

if (remainingTimeSeconds !== null) {
    remainingTimeSeconds = Math.floor(remainingTimeSeconds);
}

function goToQuestion(index) {
    index = parseInt(index, 10);
    if (isNaN(index) || typeof totalQuestions === 'undefined' || index < 0 || index >= totalQuestions) {
        return;
    }

    var target = document.querySelector('.question-container[data-question-index="' + index + '"]');
    if (!target) {
        return;
    }

    document.querySelectorAll('.question-container').forEach(function (el) {
        el.classList.remove('is-active');
    });
    target.classList.add('is-active');

    currentQuestionIndex = index;
    window.currentQuestionIndex = index;

    if (typeof updateQuestionNavigation === 'function') {
        updateQuestionNavigation();
    }
    if (window.QuizTakeUI && typeof window.QuizTakeUI.syncMobileNav === 'function') {
        window.QuizTakeUI.syncMobileNav();
    }

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

window.goToQuestion = goToQuestion;

function nextQuestion() {
    if (currentQuestionIndex < totalQuestions - 1) {
        goToQuestion(currentQuestionIndex + 1);
    }
}

window.nextQuestion = nextQuestion;

function previousQuestion() {
    if (currentQuestionIndex > 0) {
        goToQuestion(currentQuestionIndex - 1);
    }
}

window.previousQuestion = previousQuestion;

function ensureActiveQuestionVisible() {
    var active = document.querySelector('.question-container.is-active');
    if (active) {
        return true;
    }

    var first = document.querySelector('.question-container[data-question-index="0"]')
        || document.querySelector('.question-container');

    if (first) {
        goToQuestion(parseInt(first.getAttribute('data-question-index'), 10) || 0);
        return !!document.querySelector('.question-container.is-active');
    }

    return false;
}

function showQuizLoadError() {
    var main = document.querySelector('.quiz-take-main');
    if (!main || document.getElementById('quiz-no-questions-fallback')) {
        return;
    }

    main.insertAdjacentHTML('afterbegin',
        '<div class="alert alert-danger mb-3" id="quiz-load-error-fallback" role="alert">' +
        '<i class="fe fe-alert-triangle me-2"></i>تعذر عرض السؤال. ' +
        '<button type="button" class="btn btn-sm btn-outline-danger ms-1" onclick="window.location.reload()">إعادة تحميل الصفحة</button>' +
        '</div>'
    );
}

function timeUp() {
    const answeredCount = typeof answeredQuestions !== 'undefined' ? answeredQuestions.size : 0;
    const total = typeof totalQuestions !== 'undefined' ? totalQuestions : 0;
    const incomplete = total > 0 && answeredCount < total;

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'انتهى الوقت!',
            text: incomplete
                ? 'انتهى الوقت المحدد. يجب الإجابة على جميع الأسئلة قبل التسليم — أكمل الإجابات ثم أرسل الاختبار يدوياً.'
                : 'تم انتهاء الوقت المحدد للاختبار وسيتم إرسال إجاباتك تلقائياً',
            icon: 'warning',
            showConfirmButton: !incomplete,
            confirmButtonText: 'موافق',
            allowOutsideClick: incomplete,
            timer: incomplete ? undefined : 3000
        }).then(function () {
            if (!incomplete && typeof window.submitQuiz === 'function') {
                window.submitQuiz(true);
            }
        });
    } else if (!incomplete && typeof window.submitQuiz === 'function') {
        window.submitQuiz(true);
    }
}

window.timeUp = timeUp;

$(document).ready(function() {
    var domQuestionCount = document.querySelectorAll('.question-container').length;
    if (domQuestionCount > 0) {
        totalQuestions = domQuestionCount;
        window.totalQuestions = totalQuestions;
    }

    try {
        if (domQuestionCount === 0) {
            return;
        }

        if (!ensureActiveQuestionVisible()) {
            showQuizLoadError();
            return;
        }

        goToQuestion(currentQuestionIndex);

        initializeAnswers();
        updateProgress();
        updateQuestionNavigation();
    } catch (error) {
        console.error('Error initializing quiz:', error);
        if (!ensureActiveQuestionVisible()) {
            showQuizLoadError();
        }
    } finally {
        if (window.QuizTakeTimer) {
            window.QuizTakeTimer.start();
        }
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
                url: "{{ route('quizzes.preview.save-answer', $attempt->id) }}",
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

        // Update answered questions set - all blanks must be filled
        if (allAnswered && Object.keys(answer).length > 0) {
            answeredQuestions.add(questionId);
        } else {
            answeredQuestions.delete(questionId);
        }

        updateProgress();
        updateQuestionNavigation();

        // Send AJAX request - save even if not all blanks are filled
        if (Object.keys(answer).length > 0) {
            $.ajax({
                url: "{{ route('quizzes.preview.save-answer', $attempt->id) }}",
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    question_id: questionId,
                    answer: answer
                },
                success: function(response) {
                    console.log('Fill blank answer saved:', response);
                    if (response.success && allAnswered && Object.keys(answer).length > 0) {
                        answeredQuestions.add(questionId);
                        updateProgress();
                        updateQuestionNavigation();
                    } else if (!allAnswered) {
                        answeredQuestions.delete(questionId);
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
                url: "{{ route('quizzes.preview.save-answer', $attempt->id) }}",
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
        if (window.__quizOrderingBound) {
            return;
        }
        window.__quizOrderingBound = true;

        let draggedItem = null;

        function clearOrderingDragState() {
            $('.ordering-item').removeClass('dragging drag-over');
            $('.ordering-container').removeClass('is-drag-active');
            $('.ordering-drop-hint').prop('hidden', true);
            draggedItem = null;
        }

        function activateOrderingDragUi($container) {
            if ($container.hasClass('is-drag-active')) {
                return;
            }

            $container.addClass('is-drag-active');
            $container.find('.ordering-drop-hint').prop('hidden', false);
        }

        function showOrderingDropSuccess($item) {
            const $container = $item.closest('.ordering-container');
            const $success = $container.find('.ordering-drop-success');

            $item.addClass('ordering-item--placed');
            $item.find('.ordering-number').addClass('ordering-number--placed');
            $success.prop('hidden', false);

            window.setTimeout(function () {
                $item.removeClass('ordering-item--placed');
                $item.find('.ordering-number').removeClass('ordering-number--placed');
                $success.prop('hidden', true);
            }, 1800);
        }

        function finishOrderingDrop($item) {
            const questionId = $item.data('question-id');
            const $list = $item.closest('.ordering-list');

            updateOrderingNumbers($list);
            saveOrderingAnswer(questionId);
            showOrderingDropSuccess($item);
            clearOrderingDragState();
        }

        $(document).on('dragstart', '.ordering-item', function(e) {
            draggedItem = this;
            $(this).addClass('dragging');
            e.originalEvent.dataTransfer.effectAllowed = 'move';
            e.originalEvent.dataTransfer.setData('text/plain', String($(this).data('item-id') || 'ordering'));
        });

        $(document).on('dragend', '.ordering-item', function() {
            clearOrderingDragState();
        });

        $(document).on('dragover', '.ordering-item', function(e) {
            e.preventDefault();

            if (!draggedItem || this === draggedItem) {
                return;
            }

            e.originalEvent.dataTransfer.dropEffect = 'move';
            activateOrderingDragUi($(this).closest('.ordering-container'));
            $('.ordering-item').not(this).removeClass('drag-over');
            $(this).addClass('drag-over');
        });

        $(document).on('dragleave', '.ordering-item', function(e) {
            const related = e.originalEvent.relatedTarget;
            if (related && this.contains(related)) {
                return;
            }

            $(this).removeClass('drag-over');
        });

        $(document).on('drop', '.ordering-item', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!draggedItem || this === draggedItem) {
                return;
            }

            const $target = $(this);
            const rect = this.getBoundingClientRect();
            const insertBefore = e.originalEvent.clientY < rect.top + (rect.height / 2);

            if (insertBefore) {
                $(draggedItem).insertBefore($target);
            } else {
                $(draggedItem).insertAfter($target);
            }

            finishOrderingDrop($(draggedItem));
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
            url: "{{ route('quizzes.preview.save-answer', $attempt->id) }}",
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
            let allBlanksAnswered = true;
            fillBlankInputs.each(function() {
                const blankIndex = $(this).data('blank-index');
                const value = $(this).val().trim();
                if (value) {
                    answer[blankIndex] = value;
                } else {
                    allBlanksAnswered = false;
                }
            });
            hasValidAnswer = allBlanksAnswered && Object.keys(answer).length > 0;
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
                url: "{{ route('quizzes.preview.save-answer', $attempt->id) }}",
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
        if (window.QuizTakeUI && typeof window.QuizTakeUI.syncMobileNav === 'function') {
            window.QuizTakeUI.syncMobileNav();
        }
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
        if (typeof window.applyQuizSubmitModalState === 'function') {
            window.applyQuizSubmitModalState(answeredCount, totalQuestions);
        }

        const el = document.getElementById('submitModal');
        if (el) {
            const submitModal = bootstrap.Modal.getOrCreateInstance(el);
            submitModal.show();
        }
    }

    // Submit quiz
    function submitQuiz(autoSubmit = false) {
        const answeredCount = answeredQuestions.size;
        const canSubmit = totalQuestions > 0 && answeredCount >= totalQuestions;

        if (!canSubmit) {
            if (typeof window.applyQuizSubmitModalState === 'function') {
                window.applyQuizSubmitModalState(answeredCount, totalQuestions);
            }

            const modalEl = document.getElementById('submitModal');
            if (!autoSubmit && modalEl) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            } else if (autoSubmit && typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'تعذر التسليم',
                    text: 'يجب الإجابة على جميع الأسئلة قبل تسليم الاختبار.',
                    icon: 'warning',
                    confirmButtonText: 'متابعة الحل'
                });
            }

            return;
        }

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

        if (window.QuizTakeTimer) {
            window.QuizTakeTimer.stop();
        } else if (timerInterval) {
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
                action: "{{ route('quizzes.preview.submit', $attempt->id) }}"
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
