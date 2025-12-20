@extends('student.layouts.master')

@section('page-title', 'حل الاختبار - ' . $attempt->questionModule->title)

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">
                    <i class="fas fa-clipboard-question me-2"></i>
                    {{ $attempt->questionModule->title }}
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
                    @if($remainingTime !== null)
                    <div class="alert alert-warning mb-3 text-center" id="timer-container">
                        <i class="fas fa-clock me-2"></i>
                        <strong>الوقت المتبقي:</strong>
                        <div class="fs-3 fw-bold mt-2" id="timer">
                            <span id="timer-minutes">--</span>:<span id="timer-seconds">--</span>
                        </div>
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
                            {!! $question->question_text !!}
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
                                $savedAnswer = $response ? $response->student_answer : null;
                            @endphp

                            @switch($question->questionType->name)
                                @case('multiple_choice_single')
                                    @foreach($question->options as $option)
                                    <div class="form-check mb-3 p-3 border rounded hover-shadow">
                                        <input class="form-check-input answer-input"
                                               type="radio"
                                               name="question_{{ $question->id }}"
                                               id="option_{{ $option->id }}"
                                               value="{{ $option->id }}"
                                               data-question-id="{{ $question->id }}"
                                               {{ $savedAnswer == $option->id ? 'checked' : '' }}>
                                        <label class="form-check-label w-100" for="option_{{ $option->id }}">
                                            {!! $option->option_text !!}
                                        </label>
                                    </div>
                                    @endforeach
                                    @break

                                @case('multiple_choice_multiple')
                                    @foreach($question->options as $option)
                                    <div class="form-check mb-3 p-3 border rounded hover-shadow">
                                        <input class="form-check-input answer-input"
                                               type="checkbox"
                                               name="question_{{ $question->id }}[]"
                                               id="option_{{ $option->id }}"
                                               value="{{ $option->id }}"
                                               data-question-id="{{ $question->id }}"
                                               {{ is_array($savedAnswer) && in_array($option->id, $savedAnswer) ? 'checked' : '' }}>
                                        <label class="form-check-label w-100" for="option_{{ $option->id }}">
                                            {!! $option->option_text !!}
                                        </label>
                                    </div>
                                    @endforeach
                                    @break

                                @case('true_false')
                                    <div class="form-check mb-3 p-3 border rounded hover-shadow">
                                        <input class="form-check-input answer-input"
                                               type="radio"
                                               name="question_{{ $question->id }}"
                                               id="true_{{ $question->id }}"
                                               value="true"
                                               data-question-id="{{ $question->id }}"
                                               {{ $savedAnswer === 'true' ? 'checked' : '' }}>
                                        <label class="form-check-label w-100 fs-5" for="true_{{ $question->id }}">
                                            <i class="fas fa-check-circle text-success me-2"></i>صحيح
                                        </label>
                                    </div>
                                    <div class="form-check mb-3 p-3 border rounded hover-shadow">
                                        <input class="form-check-input answer-input"
                                               type="radio"
                                               name="question_{{ $question->id }}"
                                               id="false_{{ $question->id }}"
                                               value="false"
                                               data-question-id="{{ $question->id }}"
                                               {{ $savedAnswer === 'false' ? 'checked' : '' }}>
                                        <label class="form-check-label w-100 fs-5" for="false_{{ $question->id }}">
                                            <i class="fas fa-times-circle text-danger me-2"></i>خطأ
                                        </label>
                                    </div>
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
                                        $blankCount = substr_count($questionText, '[[blank]]');
                                        $savedAnswers = is_array($savedAnswer) ? $savedAnswer : [];
                                        $parts = preg_split('/\[\[blank\]\]/', $questionText);
                                    @endphp
                                    <div class="fill-blank-container" data-question-id="{{ $question->id }}">
                                        <div class="p-4 bg-light rounded border">
                                            @foreach($parts as $index => $part)
                                                <span>{!! $part !!}</span>
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
                                                    {{ $option->option_text }}
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <select class="form-select answer-input matching-select"
                                                        name="question_{{ $question->id }}[{{ $option->id }}]"
                                                        data-question-id="{{ $question->id }}">
                                                    <option value="">-- اختر الإجابة --</option>
                                                    @foreach($answers as $answer)
                                                        <option value="{{ $answer }}" {{ isset($savedAnswers[$option->id]) && $savedAnswers[$option->id] == $answer ? 'selected' : '' }}>
                                                            {{ $answer }}
                                                        </option>
                                                    @endforeach
                                                </select>
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
                                                                    {{ $option->option_text }}
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
                                                        <span class="ordering-text">{{ $item->option_text }}</span>
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
                            @endswitch
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                            <button type="button"
                                    class="btn btn-outline-secondary"
                                    onclick="previousQuestion()"
                                    {{ $index === 0 ? 'disabled' : '' }}>
                                <i class="fas fa-arrow-right me-2"></i>السابق
                            </button>

                            @if($loop->last)
                                <button type="button"
                                        class="btn btn-success btn-lg"
                                        onclick="showSubmitConfirmation()">
                                    <i class="fas fa-check me-2"></i>إرسال الاختبار
                                </button>
                            @else
                                <button type="button"
                                        class="btn btn-primary"
                                        onclick="nextQuestion()">
                                    التالي<i class="fas fa-arrow-left ms-2"></i>
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
                <button type="button" class="btn btn-success" onclick="submitQuiz()">
                    <i class="fas fa-check me-2"></i>إرسال الآن
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .hover-shadow:hover {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        cursor: pointer;
    }

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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 8px;
        cursor: grab;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        user-select: none;
    }

    .drag-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
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
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
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
        background: rgba(255,255,255,0.3);
        border: none;
        color: white;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        margin-right: 8px;
        transition: all 0.2s ease;
    }

    .btn-remove-item:hover {
        background: rgba(255,255,255,0.5);
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

    /* Ordering Styles */
    .ordering-item {
        padding: 15px;
        margin-bottom: 10px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 8px;
        cursor: grab;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        user-select: none;
    }

    .ordering-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    }

    .ordering-item:active {
        cursor: grabbing;
    }

    .ordering-item.dragging {
        opacity: 0.5;
    }

    .ordering-item.drag-over {
        border-top: 3px solid #4caf50;
    }

    .ordering-handle {
        color: rgba(255,255,255,0.7);
    }

    .ordering-number {
        background: rgba(255,255,255,0.3);
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
    const attemptId = {{ $attempt->id }};
    const totalQuestions = {{ $questions->count() }};
    let currentQuestionIndex = 0;
    let answeredQuestions = new Set();
    let remainingTimeSeconds = {{ $remainingTime ?? 'null' }};
    let timerInterval = null;

    // Initialize on page load
    $(document).ready(function() {
        initializeAnswers();
        updateProgress();
        updateQuestionNavigation();

        @if($remainingTime !== null)
            startTimer();
        @endif

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
                url: "{{ route('student.question-module.save-answer', $attempt->id) }}",
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
        const answer = {};
        let allAnswered = true;

        $(`.fill-blank-input[data-question-id="${questionId}"]`).each(function() {
            const blankIndex = $(this).data('blank-index');
            const value = $(this).val().trim();

            if (value) {
                answer[blankIndex] = value;
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
                url: "{{ route('student.question-module.save-answer', $attempt->id) }}",
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    question_id: questionId,
                    answer: answer
                },
                success: function(response) {
                    console.log('Fill blank answer saved:', response);
                },
                error: function(xhr) {
                    console.error('Error saving answer:', xhr);
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
            url: "{{ route('student.question-module.save-answer', $attempt->id) }}",
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
        if (remainingTimeSeconds === null) return;

        updateTimerDisplay();

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
                clearInterval(timerInterval);
                timeUp();
            }
        }, 1000);
    }

    function updateTimerDisplay() {
        const minutes = Math.floor(remainingTimeSeconds / 60);
        const seconds = remainingTimeSeconds % 60;
        $('#timer-minutes').text(String(minutes).padStart(2, '0'));
        $('#timer-seconds').text(String(seconds).padStart(2, '0'));
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

        // Send AJAX request
        if (hasValidAnswer) {
            $.ajax({
                url: "{{ route('student.question-module.save-answer', $attempt->id) }}",
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
        $('.question-nav-btn').each(function() {
            const questionId = parseInt($(this).data('question-id'));
            const questionIndex = $(this).data('question-index');

            $(this).removeClass('answered active');

            if (answeredQuestions.has(questionId)) {
                $(this).addClass('answered');
            }

            if (questionIndex === currentQuestionIndex) {
                $(this).addClass('active');
            }
        });
    }

    // Navigation functions
    function goToQuestion(index) {
        $('.question-container').hide();
        $(`.question-container[data-question-index="${index}"]`).show();
        currentQuestionIndex = index;
        updateQuestionNavigation();
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

    // Submit confirmation
    function showSubmitConfirmation() {
        const answeredCount = answeredQuestions.size;
        const unansweredCount = totalQuestions - answeredCount;

        $('#submit-answered-count').text(answeredCount);
        $('#submit-unanswered-count').text(unansweredCount);

        const submitModal = new bootstrap.Modal(document.getElementById('submitModal'));
        submitModal.show();
    }

    // Submit quiz
    function submitQuiz(autoSubmit = false) {
        if (timerInterval) {
            clearInterval(timerInterval);
        }

        const form = $('<form>', {
            method: 'POST',
            action: "{{ route('student.question-module.submit', $attempt->id) }}"
        });

        form.append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: '{{ csrf_token() }}'
        }));

        $('body').append(form);
        form.submit();
    }

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

    // Prevent accidental page close
    window.addEventListener('beforeunload', function (e) {
        e.preventDefault();
        e.returnValue = '';
    });
</script>
@endpush
