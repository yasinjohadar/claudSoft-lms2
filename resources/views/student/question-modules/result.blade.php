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
        <div class="card-header {{ $attempt->is_passed ? 'bg-success' : 'bg-danger' }} text-white">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="mb-0">
                        <i class="fas fa-{{ $attempt->is_passed ? 'check-circle' : 'times-circle' }} me-2"></i>
                        {{ $attempt->questionModule->title }}
                    </h3>
                </div>
                <div class="col-auto">
                    @if($attempt->is_passed)
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
                            {{ number_format($attempt->total_score, 2) }}
                        </div>
                        <div class="text-muted">
                            من {{ number_format($attempt->responses->sum('max_score'), 2) }}
                        </div>
                    </div>
                </div>

                <!-- Percentage -->
                <div class="col-md-3">
                    <div class="text-center p-4 border rounded">
                        <div class="text-muted mb-2">النسبة المئوية</div>
                        <div class="display-4 fw-bold {{ $attempt->is_passed ? 'text-success' : 'text-danger' }}">
                            {{ number_format($attempt->percentage, 1) }}%
                        </div>
                        <div class="text-muted">
                            الحد الأدنى: {{ $attempt->questionModule->pass_percentage }}%
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
                        $correctCount = $attempt->responses->where('is_correct', true)->count();
                        $incorrectCount = $attempt->responses->where('is_correct', false)->count();
                        $manualGradingCount = $attempt->responses->where('is_correct', null)->where('student_answer', '!=', null)->count();
                        $totalCount = $attempt->responses->count();
                        $correctPercentage = ($correctCount / $totalCount) * 100;
                        $incorrectPercentage = ($incorrectCount / $totalCount) * 100;
                        $manualPercentage = ($manualGradingCount / $totalCount) * 100;
                    @endphp
                    <div class="progress-bar bg-success" style="width: {{ $correctPercentage }}%">
                        {{ $correctCount }} صحيح
                    </div>
                    <div class="progress-bar bg-danger" style="width: {{ $incorrectPercentage }}%">
                        {{ $incorrectCount }} خطأ
                    </div>
                    @if($manualGradingCount > 0)
                    <div class="progress-bar bg-warning" style="width: {{ $manualPercentage }}%">
                        {{ $manualGradingCount }} بانتظار التصحيح
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

    <!-- Questions Review -->
    @if($showResults)
    <div class="card">
        <div class="card-header bg-light">
            <h4 class="mb-0">
                <i class="fas fa-list-check me-2"></i>
                مراجعة الأسئلة والإجابات
            </h4>
        </div>
        <div class="card-body p-0">
            @foreach($questionsWithResponses as $index => $item)
                @php
                    $question = $item['question'];
                    $response = $item['response'];
                @endphp
                <div class="question-review border-bottom p-4 {{ $loop->last ? '' : 'border-bottom' }}">
                    <!-- Question Header -->
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-2">
                                <span class="badge bg-secondary me-2">السؤال {{ $index + 1 }}</span>
                                <span class="badge bg-info">{{ $question->questionType->display_name }}</span>
                                @if($response && $response->is_correct === true)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i>إجابة صحيحة
                                    </span>
                                @elseif($response && $response->is_correct === false)
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times me-1"></i>إجابة خاطئة
                                    </span>
                                @elseif(!$response)
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-minus me-1"></i>لم يتم الإجابة
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="fas fa-clock me-1"></i>بانتظار التصحيح
                                    </span>
                                @endif
                            </h5>
                        </div>
                        <div class="text-end">
                            <div class="fs-4 fw-bold {{ $response && $response->is_correct ? 'text-success' : 'text-danger' }}">
                                {{ number_format($response->score_obtained ?? 0, 2) }} / {{ number_format($response->max_score ?? 0, 2) }}
                            </div>
                            <small class="text-muted">الدرجة</small>
                        </div>
                    </div>

                    <!-- Question Text -->
                    <div class="question-text mb-3 p-3 bg-light rounded">
                        {!! $question->question_text !!}
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
                                        $isStudentAnswer = $studentAnswer && (is_array($studentAnswer)
                                            ? in_array($option->id, $studentAnswer)
                                            : $studentAnswer == $option->id);
                                        $isCorrectOption = $option->is_correct;
                                    @endphp
                                    <div class="option mb-2 p-3 rounded border
                                        {{ $isCorrectOption ? 'border-success bg-success bg-opacity-10' : '' }}
                                        {{ $isStudentAnswer && !$isCorrectOption ? 'border-danger bg-danger bg-opacity-10' : '' }}">
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
                                                {!! $option->option_text !!}
                                                @if($isStudentAnswer && !$isCorrectOption)
                                                    <span class="badge bg-danger ms-2">إجابتك</span>
                                                @endif
                                                @if($isCorrectOption)
                                                    <span class="badge bg-success ms-2">الإجابة الصحيحة</span>
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
                                        $correctValue = $correctAnswer ? ($correctAnswer->option_text === 'صحيح' || $correctAnswer->option_text === 'true' ? 'true' : 'false') : null;
                                        $studentAnswer = $response ? $response->student_answer : null;
                                    @endphp
                                    <div class="col-md-6">
                                        <div class="p-3 rounded border {{ $correctValue === 'true' ? 'border-success bg-success bg-opacity-10' : '' }}
                                            {{ $studentAnswer === 'true' && $correctValue !== 'true' ? 'border-danger bg-danger bg-opacity-10' : '' }}">
                                            <i class="fas fa-check-circle {{ $correctValue === 'true' ? 'text-success' : 'text-muted' }} me-2"></i>
                                            صحيح
                                            @if($studentAnswer === 'true')
                                                <span class="badge {{ $correctValue === 'true' ? 'bg-success' : 'bg-danger' }} ms-2">إجابتك</span>
                                            @endif
                                            @if($correctValue === 'true')
                                                <span class="badge bg-success ms-2">الإجابة الصحيحة</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 rounded border {{ $correctValue === 'false' ? 'border-success bg-success bg-opacity-10' : '' }}
                                            {{ $studentAnswer === 'false' && $correctValue !== 'false' ? 'border-danger bg-danger bg-opacity-10' : '' }}">
                                            <i class="fas fa-times-circle {{ $correctValue === 'false' ? 'text-success' : 'text-muted' }} me-2"></i>
                                            خطأ
                                            @if($studentAnswer === 'false')
                                                <span class="badge {{ $correctValue === 'false' ? 'bg-success' : 'bg-danger' }} ms-2">إجابتك</span>
                                            @endif
                                            @if($correctValue === 'false')
                                                <span class="badge bg-success ms-2">الإجابة الصحيحة</span>
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
                                        {{ $response && $response->student_answer ? $response->student_answer : 'لم يتم الإجابة' }}
                                    </div>
                                </div>
                                @if($question->model_answer)
                                <div class="mb-3">
                                    <strong class="d-block mb-2 text-success">
                                        <i class="fas fa-lightbulb me-1"></i>الإجابة النموذجية:
                                    </strong>
                                    <div class="p-3 bg-success bg-opacity-10 rounded border border-success">
                                        {!! $question->model_answer !!}
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
                                                <strong>{{ $option->option_text }}</strong>
                                            </div>
                                            <div class="col-1 text-center">
                                                <i class="fas fa-arrow-left"></i>
                                            </div>
                                            <div class="col-6">
                                                @if($studentAnswer)
                                                    <span class="{{ $isCorrect ? 'text-success' : 'text-danger' }}">
                                                        {{ $studentAnswer }}
                                                        @if($isCorrect)
                                                            <i class="fas fa-check ms-1"></i>
                                                        @else
                                                            <i class="fas fa-times ms-1"></i>
                                                        @endif
                                                    </span>
                                                    @if(!$isCorrect)
                                                        <br><small class="text-success">الإجابة الصحيحة: {{ $correctAnswer }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">لم يتم الإجابة</span>
                                                    <br><small class="text-success">الإجابة الصحيحة: {{ $correctAnswer }}</small>
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
                                        <span>{!! $part !!}</span>
                                        @if($index < count($parts) - 1)
                                            @php
                                                $studentAnswer = $studentAnswers[$index] ?? null;
                                                $correctAnswer = $correctAnswers[$index] ?? '';
                                                $isCorrect = $studentAnswer && strtolower(trim($studentAnswer)) === strtolower(trim($correctAnswer));
                                            @endphp
                                            <span class="badge {{ $isCorrect ? 'bg-success' : 'bg-danger' }} mx-1">
                                                {{ $studentAnswer ?? '___' }}
                                                @if($isCorrect)
                                                    <i class="fas fa-check ms-1"></i>
                                                @else
                                                    <i class="fas fa-times ms-1"></i>
                                                @endif
                                            </span>
                                            @if(!$isCorrect && $correctAnswer)
                                                <small class="text-success">({{ $correctAnswer }})</small>
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
                        <p class="mb-0 mt-2">{{ $response->feedback }}</p>
                    </div>
                    @endif

                    <!-- Explanation -->
                    @if($question->explanation)
                    <div class="alert alert-light border mt-3 mb-0">
                        <strong><i class="fas fa-info-circle me-2"></i>شرح:</strong>
                        <p class="mb-0 mt-2">{!! $question->explanation !!}</p>
                    </div>
                    @endif
                </div>
            @endforeach
        </div>
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

        .question-review {
            page-break-inside: avoid;
        }
    }

    .question-review:hover {
        background-color: #f8f9fa;
    }
</style>
@endpush
