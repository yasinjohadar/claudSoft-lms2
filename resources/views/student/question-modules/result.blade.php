@extends('student.layouts.master')

@section('page-title', 'نتيجة الاختبار - ' . $attempt->questionModule->title)

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    نتيجة الاختبار
                </h4>
            </div>
            <div class="ms-auto d-print-none">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print me-2"></i>طباعة النتيجة
                </button>
            </div>
        </div>
        <!-- End Page Header -->
    <!-- Summary Card -->
    <div class="card mb-4">
        <div class="card-header {{ ($attempt->is_passed ?? false) ? 'bg-success' : 'bg-danger' }} text-white">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="mb-0">
                        <i class="fas fa-{{ ($attempt->is_passed ?? false) ? 'check-circle' : 'times-circle' }} me-2"></i>
                        {{ $attempt->questionModule->title }}
                    </h3>
                </div>
                <div class="col-auto">
                    @if($attempt->is_passed ?? false)
                        <span class="badge bg-white text-success fs-4 px-4 py-2">
                            <i class="fas fa-trophy me-2"></i>ناجح
                        </span>
                    @else
                        <span class="badge bg-white text-danger fs-4 px-4 py-2">
                            <i class="fas fa-times me-2"></i>راسب
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <!-- Score -->
                <div class="col-md-3">
                    <div class="text-center p-4 border rounded">
                        <div class="text-muted mb-2">الدرجة النهائية</div>
                        <div class="display-4 fw-bold text-primary">
                            {{ number_format($attempt->total_score ?? 0, 2) }}
                        </div>
                        <div class="text-muted">
                            من {{ number_format($attempt->responses->sum('max_score') ?? 0, 2) }}
                        </div>
                    </div>
                </div>

                <!-- Percentage -->
                <div class="col-md-3">
                    <div class="text-center p-4 border rounded">
                        <div class="text-muted mb-2">النسبة المئوية</div>
                        <div class="display-4 fw-bold {{ ($attempt->is_passed ?? false) ? 'text-success' : 'text-danger' }}">
                            {{ number_format($attempt->percentage ?? 0, 1) }}%
                        </div>
                        <div class="text-muted">
                            الحد الأدنى: {{ $attempt->questionModule->pass_percentage ?? 50 }}%
                        </div>
                    </div>
                </div>

                <!-- Time Spent -->
                <div class="col-md-3">
                    <div class="text-center p-4 border rounded">
                        <div class="text-muted mb-2">الوقت المستغرق</div>
                        <div class="display-4 fw-bold text-info">
                            {{ floor($attempt->time_spent / 60) }}
                        </div>
                        <div class="text-muted">
                            دقيقة
                            @if($attempt->questionModule->time_limit)
                                من {{ $attempt->questionModule->time_limit }}
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Attempt Number -->
                <div class="col-md-3">
                    <div class="text-center p-4 border rounded">
                        <div class="text-muted mb-2">رقم المحاولة</div>
                        <div class="display-4 fw-bold text-secondary">
                            {{ $attempt->attempt_number }}
                        </div>
                        <div class="text-muted">
                            من {{ $attempt->questionModule->attempts_allowed }} محاولات
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="mt-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-bold">التقدم الإجمالي</span>
                    <span>{{ $attempt->responses->where('student_answer', '!=', null)->count() }} / {{ $attempt->responses->count() }} سؤال</span>
                </div>
                <div class="progress" style="height: 25px;">
                    @php
                        $totalCount = $attempt->responses->count();
                        $correctCount = $attempt->responses->where('is_correct', true)->count();
                        $incorrectCount = $attempt->responses->where('is_correct', false)->count();
                        // Manual grading: questions that require manual grading (short_answer, essay) and have answers but no score yet
                        $manualGradingCount = $attempt->responses->filter(function($r) {
                            $questionType = $r->question->questionType->name ?? '';
                            $requiresManual = in_array($questionType, ['short_answer', 'essay']);
                            $hasAnswer = $r->student_answer !== null && $r->student_answer !== '' && !(is_array($r->student_answer) && empty($r->student_answer));
                            $notGraded = $r->is_correct === null || $r->score_obtained === null;
                            return $requiresManual && $hasAnswer && $notGraded;
                        })->count();
                        // Unanswered questions
                        $unansweredCount = $attempt->responses->filter(function($r) {
                            return $r->student_answer === null || $r->student_answer === '' || (is_array($r->student_answer) && empty($r->student_answer));
                        })->count();
                        
                        $correctPercentage = $totalCount > 0 ? ($correctCount / $totalCount) * 100 : 0;
                        $incorrectPercentage = $totalCount > 0 ? ($incorrectCount / $totalCount) * 100 : 0;
                        $manualPercentage = $totalCount > 0 ? ($manualGradingCount / $totalCount) * 100 : 0;
                        $unansweredPercentage = $totalCount > 0 ? ($unansweredCount / $totalCount) * 100 : 0;
                    @endphp
                    @if($correctCount > 0)
                    <div class="progress-bar bg-success" style="width: {{ $correctPercentage }}%">
                        {{ $correctCount }} صحيح
                    </div>
                    @endif
                    @if($incorrectCount > 0)
                    <div class="progress-bar bg-danger" style="width: {{ $incorrectPercentage }}%">
                        {{ $incorrectCount }} خطأ
                    </div>
                    @endif
                    @if($manualGradingCount > 0)
                    <div class="progress-bar bg-warning" style="width: {{ $manualPercentage }}%">
                        {{ $manualGradingCount }} بانتظار التصحيح
                    </div>
                    @endif
                    @if($unansweredCount > 0)
                    <div class="progress-bar bg-secondary" style="width: {{ $unansweredPercentage }}%">
                        {{ $unansweredCount }} لم يتم الإجابة
                    </div>
                    @endif
                </div>
            </div>

            <!-- Date Info -->
            <div class="row mt-4 text-muted">
                <div class="col-md-6">
                    <i class="fas fa-calendar me-2"></i>
                    <strong>تاريخ البدء:</strong> {{ $attempt->started_at->format('Y-m-d H:i') }}
                </div>
                <div class="col-md-6">
                    <i class="fas fa-calendar-check me-2"></i>
                    <strong>تاريخ الإنهاء:</strong> {{ $attempt->completed_at->format('Y-m-d H:i') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Questions Review: بطاقة مستقلة لكل سؤال -->
    @if($showResults)
    <div class="quiz-review-questions-section mb-4">
        <h4 class="fs-18 fw-semibold mb-3 d-flex align-items-center">
            <i class="fas fa-list-check me-2 text-primary"></i>
            مراجعة الأسئلة والإجابات
        </h4>
            @foreach($questionsWithResponses as $index => $item)
                @php
                    $question = $item['question'];
                    $response = $item['response'];
                @endphp
                <div class="card custom-card question-review-card mb-4">
                    <div class="card-body p-4">
                    <!-- Question Header -->
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-2">
                                <span class="badge bg-secondary me-2">السؤال {{ $index + 1 }}</span>
                                <span class="badge bg-info">{{ $question->questionType->display_name }}</span>
                                @php
                                    $questionTypeName = $question->questionType->name ?? '';
                                    $requiresManualGrading = in_array($questionTypeName, ['short_answer', 'essay']);
                                @endphp
                                @if($response && $response->is_correct === true)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i>إجابة صحيحة
                                    </span>
                                @elseif($response && $response->is_correct === false)
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times me-1"></i>إجابة خاطئة
                                    </span>
                                @elseif(!$response || !$response->student_answer)
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-minus me-1"></i>لم يتم الإجابة
                                    </span>
                                @elseif($requiresManualGrading && ($response->is_correct === null || $response->score_obtained === null))
                                    <span class="badge bg-warning">
                                        <i class="fas fa-clock me-1"></i>بانتظار التصحيح
                                    </span>
                                @else
                                    <span class="badge bg-info">
                                        <i class="fas fa-info-circle me-1"></i>تم التصحيح
                                    </span>
                                @endif
                            </h5>
                        </div>
                        <div class="text-end">
                            @php
                                $scoreColorClass = 'text-secondary';
                                if ($response && $response->is_correct === true) {
                                    $scoreColorClass = 'text-success';
                                } elseif ($response && $response->is_correct === false) {
                                    $scoreColorClass = 'text-danger';
                                } elseif ($response && $response->is_correct === null && $requiresManualGrading) {
                                    $scoreColorClass = 'text-warning';
                                }
                            @endphp
                            <div class="fs-4 fw-bold {{ $scoreColorClass }}">
                                {{ number_format($response->score_obtained ?? 0, 2) }} / {{ number_format($response->max_score ?? 0, 2) }}
                            </div>
                            <small class="text-muted">الدرجة</small>
                        </div>
                    </div>

                    <!-- Question Text -->
                    <div class="question-text mb-3 p-3 bg-light rounded">
                        {!! mixed_bidi_html($question->question_text) !!}
                    </div>

                    <!-- Question Image -->
                    @if($question->question_image)
                    <div class="mb-3">
                        <img src="{{ asset('storage/' . $question->question_image) }}"
                             alt="Question Image"
                             class="img-fluid rounded border"
                             style="max-width: 400px;">
                    </div>
                    @endif

                    <!-- Answers -->
                    <div class="answers">
                        @switch($question->questionType->name)
                            @case('multiple_choice_single')
                            @case('multiple_choice_multiple')
                                @foreach($question->options as $option)
                                    @php
                                        $studentAnswer = $response ? $response->student_answer : null;
                                        $isStudentAnswer = false;
                                        
                                        if ($studentAnswer) {
                                            if (is_array($studentAnswer)) {
                                                // Handle array format: direct array of IDs or array with keys
                                                if (isset($studentAnswer['selected_options'])) {
                                                    $isStudentAnswer = in_array($option->id, array_map('intval', $studentAnswer['selected_options']));
                                                } elseif (isset($studentAnswer['selected_option'])) {
                                                    $isStudentAnswer = (int)$option->id == (int)$studentAnswer['selected_option'];
                                                } else {
                                                    // Direct array of IDs
                                                    $isStudentAnswer = in_array((int)$option->id, array_map('intval', $studentAnswer));
                                                }
                                            } else {
                                                // Direct value (string or int)
                                                $isStudentAnswer = (int)$option->id == (int)$studentAnswer;
                                            }
                                        }
                                        
                                        $isCorrectOption = $option->is_correct;
                                    @endphp
                                    @php
                                        // Determine border and background colors
                                        $optionBorderClass = '';
                                        $optionBgClass = '';
                                        
                                        if ($isStudentAnswer && $isCorrectOption) {
                                            // Student selected correct answer - green
                                            $optionBorderClass = 'border-success';
                                            $optionBgClass = 'bg-success bg-opacity-10';
                                        } elseif ($isStudentAnswer && !$isCorrectOption) {
                                            // Student selected wrong answer - red
                                            $optionBorderClass = 'border-danger';
                                            $optionBgClass = 'bg-danger bg-opacity-10';
                                        } elseif ($isCorrectOption && !$isStudentAnswer) {
                                            // Correct answer but student didn't select - green (subtle)
                                            $optionBorderClass = 'border-success';
                                            $optionBgClass = 'bg-success bg-opacity-5';
                                        } else {
                                            // Not selected and not correct - default
                                            $optionBorderClass = 'border-secondary';
                                            $optionBgClass = '';
                                        }
                                    @endphp
                                    <div class="option mb-2 p-3 rounded border {{ $optionBorderClass }} {{ $optionBgClass }}">
                                        <div class="d-flex align-items-start">
                                            <div class="me-3">
                                                @if($isCorrectOption)
                                                    <i class="fas fa-check-circle text-success fs-5"></i>
                                                @elseif($isStudentAnswer)
                                                    <i class="fas fa-times-circle text-danger fs-5"></i>
                                                @else
                                                    <i class="far fa-circle text-muted fs-5"></i>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1">
                                                {!! mixed_bidi_html($option->option_text) !!}
                                                @if($isStudentAnswer)
                                                    <span class="badge {{ $isCorrectOption ? 'bg-success' : 'bg-danger' }} ms-2">
                                                        <i class="fas fa-user me-1"></i>إجابتك
                                                    </span>
                                                @endif
                                                @if($isCorrectOption)
                                                    <span class="badge bg-success ms-2">
                                                        <i class="fas fa-check-circle me-1"></i>الإجابة الصحيحة
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                @break

                            @case('true_false')
                                <div class="row">
                                    @php
                                        $correctAnswer = $question->options->where('is_correct', true)->first();
                                        $correctValue = $correctAnswer ? (strtolower(strip_tags($correctAnswer->option_text)) === 'صح' || strtolower(strip_tags($correctAnswer->option_text)) === 'true' || strtolower(strip_tags($correctAnswer->option_text)) === 'صحيح' ? 'true' : 'false') : null;
                                        $studentAnswerRaw = $response ? $response->student_answer : null;
                                        
                                        // Extract student answer value
                                        $studentAnswer = null;
                                        if ($studentAnswerRaw) {
                                            if (is_array($studentAnswerRaw)) {
                                                $studentAnswer = $studentAnswerRaw['answer'] ?? $studentAnswerRaw['selected_option'] ?? null;
                                            } else {
                                                $studentAnswer = $studentAnswerRaw;
                                            }
                                            // Convert to 'true' or 'false' string
                                            if (is_numeric($studentAnswer)) {
                                                // If it's an option ID, get the option text
                                                $selectedOption = $question->options->find($studentAnswer);
                                                if ($selectedOption) {
                                                    $optionText = strtolower(strip_tags($selectedOption->option_text));
                                                    $studentAnswer = ($optionText === 'صح' || $optionText === 'true' || $optionText === 'صحيح') ? 'true' : 'false';
                                                }
                                            } elseif (is_string($studentAnswer)) {
                                                $answerStr = strtolower(strip_tags($studentAnswer));
                                                if ($answerStr === 'صح' || $answerStr === 'true' || $answerStr === '1' || $answerStr === 'صحيح') {
                                                    $studentAnswer = 'true';
                                                } elseif ($answerStr === 'خطأ' || $answerStr === 'false' || $answerStr === '0') {
                                                    $studentAnswer = 'false';
                                                }
                                            }
                                        }
                                        
                                        // Determine if answer is correct
                                        $isCorrect = ($studentAnswer === $correctValue);
                                    @endphp
                                    <div class="col-md-6">
                                        @php
                                            // Determine border and background colors for "صحيح" option
                                            $isTrueCorrect = ($correctValue === 'true');
                                            $isTrueSelected = ($studentAnswer === 'true');
                                            $trueBorderClass = '';
                                            $trueBgClass = '';
                                            $trueIconClass = '';
                                            
                                            if ($isTrueSelected && $isTrueCorrect) {
                                                // Student selected correct answer - green
                                                $trueBorderClass = 'border-success';
                                                $trueBgClass = 'bg-success bg-opacity-10';
                                                $trueIconClass = 'text-success';
                                            } elseif ($isTrueSelected && !$isTrueCorrect) {
                                                // Student selected wrong answer - red
                                                $trueBorderClass = 'border-danger';
                                                $trueBgClass = 'bg-danger bg-opacity-10';
                                                $trueIconClass = 'text-danger';
                                            } elseif ($isTrueCorrect && !$isTrueSelected) {
                                                // Correct answer but student didn't select - green (subtle)
                                                $trueBorderClass = 'border-success';
                                                $trueBgClass = 'bg-success bg-opacity-5';
                                                $trueIconClass = 'text-success';
                                            } else {
                                                // Not selected and not correct - muted
                                                $trueBorderClass = 'border-secondary';
                                                $trueBgClass = '';
                                                $trueIconClass = 'text-muted';
                                            }
                                        @endphp
                                        <div class="p-3 rounded border {{ $trueBorderClass }} {{ $trueBgClass }}">
                                            <i class="fas fa-check-circle {{ $trueIconClass }} me-2"></i>
                                            <strong>صحيح</strong>
                                            @if($isTrueSelected)
                                                <span class="badge {{ $isTrueCorrect ? 'bg-success' : 'bg-danger' }} ms-2">
                                                    <i class="fas fa-user me-1"></i>إجابتك
                                                </span>
                                            @endif
                                            @if($isTrueCorrect)
                                                <span class="badge bg-success ms-2">
                                                    <i class="fas fa-check-circle me-1"></i>الإجابة الصحيحة
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        @php
                                            // Determine border and background colors for "خطأ" option
                                            $isFalseCorrect = ($correctValue === 'false');
                                            $isFalseSelected = ($studentAnswer === 'false');
                                            $falseBorderClass = '';
                                            $falseBgClass = '';
                                            $falseIconClass = '';
                                            
                                            if ($isFalseSelected && $isFalseCorrect) {
                                                // Student selected correct answer - green
                                                $falseBorderClass = 'border-success';
                                                $falseBgClass = 'bg-success bg-opacity-10';
                                                $falseIconClass = 'text-success';
                                            } elseif ($isFalseSelected && !$isFalseCorrect) {
                                                // Student selected wrong answer - red
                                                $falseBorderClass = 'border-danger';
                                                $falseBgClass = 'bg-danger bg-opacity-10';
                                                $falseIconClass = 'text-danger';
                                            } elseif ($isFalseCorrect && !$isFalseSelected) {
                                                // Correct answer but student didn't select - green (subtle)
                                                $falseBorderClass = 'border-success';
                                                $falseBgClass = 'bg-success bg-opacity-5';
                                                $falseIconClass = 'text-success';
                                            } else {
                                                // Not selected and not correct - muted
                                                $falseBorderClass = 'border-secondary';
                                                $falseBgClass = '';
                                                $falseIconClass = 'text-muted';
                                            }
                                        @endphp
                                        <div class="p-3 rounded border {{ $falseBorderClass }} {{ $falseBgClass }}">
                                            <i class="fas fa-times-circle {{ $falseIconClass }} me-2"></i>
                                            <strong>خطأ</strong>
                                            @if($isFalseSelected)
                                                <span class="badge {{ $isFalseCorrect ? 'bg-success' : 'bg-danger' }} ms-2">
                                                    <i class="fas fa-user me-1"></i>إجابتك
                                                </span>
                                            @endif
                                            @if($isFalseCorrect)
                                                <span class="badge bg-success ms-2">
                                                    <i class="fas fa-check-circle me-1"></i>الإجابة الصحيحة
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @break

                            @case('short_answer')
                            @case('essay')
                                <div class="mb-3">
                                    <strong class="d-block mb-2">إجابتك:</strong>
                                    <div class="p-3 bg-light rounded border">
                                        @php
                                            $studentAnswerRaw = $response ? $response->student_answer : null;
                                            $answerText = 'لم يتم الإجابة';
                                            
                                            if ($studentAnswerRaw) {
                                                if (is_array($studentAnswerRaw)) {
                                                    // Extract text from array
                                                    $answerText = $studentAnswerRaw['answer'] ?? $studentAnswerRaw['text'] ?? null;
                                                    // If still null, try to get first non-numeric value
                                                    if ($answerText === null) {
                                                        foreach ($studentAnswerRaw as $key => $val) {
                                                            if (!is_numeric($val) && !is_numeric($key)) {
                                                                $answerText = $val;
                                                                break;
                                                            }
                                                        }
                                                    }
                                                    // If still null, use first value
                                                    if ($answerText === null && !empty($studentAnswerRaw)) {
                                                        $answerText = is_array($studentAnswerRaw[0] ?? null) ? json_encode($studentAnswerRaw[0]) : ($studentAnswerRaw[0] ?? '');
                                                    }
                                                } else {
                                                    // Direct string value
                                                    $answerText = (string)$studentAnswerRaw;
                                                }
                                                
                                                if (empty(trim($answerText))) {
                                                    $answerText = 'لم يتم الإجابة';
                                                }
                                            }
                                            $answerLines = preg_split('/\r\n|\r|\n/', (string) $answerText);
                                        @endphp
                                        @foreach($answerLines as $line)
                                            {!! mixed_bidi_html($line) !!}@if(!$loop->last)<br>@endif
                                        @endforeach
                                    </div>
                                </div>
                                @if($question->model_answer)
                                <div class="mb-3">
                                    <strong class="d-block mb-2 text-success">
                                        <i class="fas fa-lightbulb me-1"></i>الإجابة النموذجية:
                                    </strong>
                                    <div class="p-3 bg-success bg-opacity-10 rounded border border-success">
                                        @foreach(preg_split('/\r\n|\r|\n/', (string) $question->model_answer) as $modelLine)
                                            {!! mixed_bidi_html($modelLine) !!}@if(!$loop->last)<br>@endif
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                @break

                            @case('matching')
                            @case('drag_drop')
                                @php
                                    $studentAnswers = $response && $response->student_answer ? $response->student_answer : [];
                                @endphp
                                <div class="matching-results">
                                    @foreach($question->options as $option)
                                        @php
                                            $studentAnswer = $studentAnswers[$option->id] ?? null;
                                            $correctAnswer = $option->feedback;
                                            $isCorrect = $studentAnswer === $correctAnswer;
                                        @endphp
                                        <div class="row mb-2 p-2 rounded border {{ $isCorrect ? 'border-success bg-success bg-opacity-10' : 'border-danger bg-danger bg-opacity-10' }}">
                                            <div class="col-5">
                                                <strong>{!! mixed_bidi_html($option->option_text) !!}</strong>
                                            </div>
                                            <div class="col-1 text-center">
                                                <i class="fas fa-arrow-left"></i>
                                            </div>
                                            <div class="col-6">
                                                @if($studentAnswer)
                                                    <span class="{{ $isCorrect ? 'text-success' : 'text-danger' }}">
                                                        {!! mixed_bidi_html(is_scalar($studentAnswer) ? (string) $studentAnswer : json_encode($studentAnswer, JSON_UNESCAPED_UNICODE)) !!}
                                                        @if($isCorrect)
                                                            <i class="fas fa-check ms-1"></i>
                                                        @else
                                                            <i class="fas fa-times ms-1"></i>
                                                        @endif
                                                    </span>
                                                    @if(!$isCorrect)
                                                        <br><small class="text-success">الإجابة الصحيحة: {!! mixed_bidi_html(is_scalar($correctAnswer) ? (string) $correctAnswer : json_encode($correctAnswer, JSON_UNESCAPED_UNICODE)) !!}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">لم يتم الإجابة</span>
                                                    <br><small class="text-success">الإجابة الصحيحة: {!! mixed_bidi_html(is_scalar($correctAnswer) ? (string) $correctAnswer : json_encode($correctAnswer, JSON_UNESCAPED_UNICODE)) !!}</small>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @break

                            @case('fill_blanks')
                                @php
                                    $studentAnswers = $response && $response->student_answer ? $response->student_answer : [];
                                    $correctAnswers = $question->options->pluck('option_text', 'option_order')->toArray();
                                    $questionText = $question->question_text;
                                    $parts = preg_split('/\[\[blank\]\]/', $questionText);
                                @endphp
                                <div class="fill-blank-results p-3 bg-light rounded">
                                    @foreach($parts as $index => $part)
                                        <span>{!! mixed_bidi_html($part) !!}</span>
                                        @if($index < count($parts) - 1)
                                            @php
                                                $studentAnswer = $studentAnswers[$index] ?? null;
                                                $correctAnswer = $correctAnswers[$index] ?? '';
                                                $isCorrect = $studentAnswer && strtolower(trim((string) $studentAnswer)) === strtolower(trim((string) $correctAnswer));
                                            @endphp
                                            <span class="badge {{ $isCorrect ? 'bg-success' : 'bg-danger' }} mx-1">
                                                {!! mixed_bidi_html((string) ($studentAnswer ?? '___')) !!}
                                                @if($isCorrect)
                                                    <i class="fas fa-check ms-1"></i>
                                                @else
                                                    <i class="fas fa-times ms-1"></i>
                                                @endif
                                            </span>
                                            @if(!$isCorrect && $correctAnswer)
                                                <small class="text-success">({!! mixed_bidi_html((string) $correctAnswer) !!})</small>
                                            @endif
                                        @endif
                                    @endforeach
                                </div>
                                @break
                        @endswitch
                    </div>

                    <!-- Feedback -->
                    @if($response && $response->feedback)
                    <div class="alert alert-info mt-3 mb-0">
                        <strong><i class="fas fa-comment me-2"></i>ملاحظات المدرس:</strong>
                        <p class="mb-0 mt-2">{!! mixed_bidi_html($response->feedback) !!}</p>
                    </div>
                    @endif

                    <!-- Explanation -->
                    @if($question->explanation)
                    <div class="alert alert-light border mt-3 mb-0">
                        <strong><i class="fas fa-info-circle me-2"></i>شرح:</strong>
                        <p class="mb-0 mt-2">{!! mixed_bidi_html($question->explanation) !!}</p>
                    </div>
                    @endif
                    </div>
                </div>
            @endforeach
    </div>
    @else
    <!-- Results Hidden Message -->
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-eye-slash text-muted mb-3" style="font-size: 4rem;"></i>
            <h4 class="text-muted">الإجابات والأسئلة مخفية</h4>
            <p class="text-muted">لم يتم السماح بعرض تفاصيل الأسئلة والإجابات لهذا الاختبار</p>
        </div>
    </div>
    @endif

    <!-- Action Buttons -->
    <div class="card mt-4">
        <div class="card-body text-center">
            <a href="{{ route('student.learn.course', $attempt->questionModule->courseModules->first()->course_id ?? '#') }}"
               class="btn btn-primary btn-lg">
                <i class="fas fa-arrow-right me-2"></i>العودة إلى الكورس
            </a>

            <a href="{{ route('student.question-module.stats.module', $attempt->questionModule->id) }}"
               class="btn btn-info btn-lg">
                <i class="fas fa-chart-bar me-2"></i>عرض الإحصائيات التفصيلية
            </a>

            @if($attempt->attempt_number < $attempt->questionModule->attempts_allowed)
            <a href="{{ route('student.question-module.start', $attempt->questionModule->id) }}"
               class="btn btn-success btn-lg">
                <i class="fas fa-redo me-2"></i>محاولة جديدة
            </a>
            @endif
        </div>
    </div>
    </div>
</div>
<!-- End::app-content -->
@endsection

@push('styles')
<style>
    @media print {
        .page-header,
        .btn,
        .sidebar,
        .navbar {
            display: none !important;
        }

        .card {
            border: 1px solid #dee2e6 !important;
            page-break-inside: avoid;
        }

        .question-review-card {
            page-break-inside: avoid;
        }
    }
</style>
@endpush
