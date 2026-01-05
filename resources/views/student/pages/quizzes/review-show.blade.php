@extends('student.layouts.master')

@section('page-title')
    مراجعة المحاولة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('student.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">{{ $attempt->quiz->title }} - المحاولة #{{ $attempt->attempt_number }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('student.quizzes.index') }}">الاختبارات</a></li>
                            <li class="breadcrumb-item active">مراجعة المحاولة</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('student.quizzes.review.download-report', $attempt->id) }}" class="btn btn-secondary">
                        <i class="fas fa-download me-2"></i>تحميل التقرير
                    </a>
                </div>
            </div>

            <!-- Results Summary -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-lg bg-{{ $attempt->passed ? 'success' : 'danger' }}-transparent me-3">
                                    <i class="fas fa-{{ $attempt->passed ? 'check-circle' : 'times-circle' }} fs-24"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-muted fs-12">النتيجة النهائية</p>
                                    <h4 class="mb-0 fw-bold text-{{ $attempt->passed ? 'success' : 'danger' }}">
                                        {{ number_format($attempt->percentage_score, 1) }}%
                                    </h4>
                                    <small class="text-muted">{{ $attempt->passed ? 'ناجح' : 'راسب' }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-lg bg-primary-transparent me-3">
                                    <i class="fas fa-star fs-24"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-muted fs-12">الدرجة المحصلة</p>
                                    <h4 class="mb-0 fw-bold">{{ number_format($attempt->total_score, 1) }}</h4>
                                    <small class="text-muted">من {{ $attempt->max_score }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-lg bg-success-transparent me-3">
                                    <i class="fas fa-check fs-24"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-muted fs-12">الإجابات الصحيحة</p>
                                    <h4 class="mb-0 fw-bold">{{ $stats['correct'] }}</h4>
                                    <small class="text-muted">من {{ $stats['total_questions'] }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-lg bg-info-transparent me-3">
                                    <i class="fas fa-clock fs-24"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-muted fs-12">الوقت المستغرق</p>
                                    <h4 class="mb-0 fw-bold">{{ $attempt->getTimeSpentHumanReadable() }}</h4>
                                    <small class="text-muted">
                                        {{ $attempt->quiz->time_limit ? 'من ' . $attempt->quiz->time_limit . ' دقيقة' : '' }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feedback -->
            @if($attempt->feedback)
                <div class="alert alert-info mb-4">
                    <h6 class="alert-heading">
                        <i class="fas fa-comment me-2"></i>ملاحظات المدرس
                    </h6>
                    <p class="mb-0">{{ $attempt->feedback }}</p>
                </div>
            @endif

            <!-- Questions Review -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-list me-2"></i>مراجعة الأسئلة والإجابات
                    </div>
                </div>
                <div class="card-body">
                    @foreach($orderedResponses as $index => $response)
                        @if($response)
                            @php
                                $question = $response->question;
                                $questionNumber = $index + 1;
                            @endphp

                            <div class="question-review mb-4 pb-4 {{ $loop->last ? '' : 'border-bottom' }}">
                                <!-- Question Header -->
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h6 class="fw-bold">
                                            <span class="badge bg-primary me-2">سؤال {{ $questionNumber }}</span>
                                            <span class="badge bg-info">{{ $question->questionType->display_name }}</span>
                                        </h6>
                                    </div>
                                    <div class="text-end">
                                        @if($response->is_correct)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i>صحيح
                                            </span>
                                        @elseif($response->is_correct === false)
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times-circle me-1"></i>خطأ
                                            </span>
                                        @else
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock me-1"></i>قيد التصحيح
                                            </span>
                                        @endif
                                        @if($response->score_obtained !== null)
                                            <span class="badge bg-secondary ms-2">
                                                {{ number_format($response->score_obtained, 1) }} / {{ $response->max_score }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Question Text -->
                                <div class="mb-3">
                                    <p class="fw-semibold mb-2">{{ $question->question_text }}</p>
                                    @if($question->media_url && $question->media_type == 'image')
                                        <img src="{{ $question->media_url }}" alt="صورة السؤال" class="img-fluid rounded mt-2" style="max-width: 400px;">
                                    @endif
                                </div>

                                <!-- Student Answer -->
                                <div class="mb-3">
                                    <p class="text-muted mb-2"><strong>إجابتك:</strong></p>
                                    <div class="p-3 bg-light rounded">
                                        @if($question->questionType->name == 'true_false')
                                            @php
                                                // Get student answer value
                                                $studentAnswerValue = null;
                                                if ($response->response_text) {
                                                    $answerStr = strtolower(trim(strip_tags($response->response_text)));
                                                    if ($answerStr === 'صح' || $answerStr === 'true' || $answerStr === '1' || $answerStr === 'صحيح') {
                                                        $studentAnswerValue = 'true';
                                                    } elseif ($answerStr === 'خطأ' || $answerStr === 'false' || $answerStr === '0') {
                                                        $studentAnswerValue = 'false';
                                                    }
                                                } elseif ($response->selected_option_ids && !empty($response->selected_option_ids)) {
                                                    $optionId = is_array($response->selected_option_ids) ? $response->selected_option_ids[0] : $response->selected_option_ids;
                                                    $selectedOption = $question->options->find($optionId);
                                                    if ($selectedOption) {
                                                        $optionText = strtolower(trim(strip_tags($selectedOption->option_text)));
                                                        $studentAnswerValue = ($optionText === 'صح' || $optionText === 'true' || $optionText === '1' || $optionText === 'صحيح') ? 'true' : 'false';
                                                    }
                                                } elseif ($response->response_data && isset($response->response_data['answer'])) {
                                                    $answer = $response->response_data['answer'];
                                                    if (is_array($answer) && !empty($answer)) {
                                                        $answer = $answer[0];
                                                    }
                                                    if (is_numeric($answer)) {
                                                        $selectedOption = $question->options->find($answer);
                                                        if ($selectedOption) {
                                                            $optionText = strtolower(trim(strip_tags($selectedOption->option_text)));
                                                            $studentAnswerValue = ($optionText === 'صح' || $optionText === 'true' || $optionText === '1' || $optionText === 'صحيح') ? 'true' : 'false';
                                                        }
                                                    } else {
                                                        $answerStr = strtolower(trim(strip_tags((string)$answer)));
                                                        if ($answerStr === 'صح' || $answerStr === 'true' || $answerStr === '1' || $answerStr === 'صحيح') {
                                                            $studentAnswerValue = 'true';
                                                        } elseif ($answerStr === 'خطأ' || $answerStr === 'false' || $answerStr === '0') {
                                                            $studentAnswerValue = 'false';
                                                        }
                                                    }
                                                }
                                                
                                                // Display answer
                                                if ($studentAnswerValue === 'true') {
                                                    $displayText = 'صحيح';
                                                    $badgeClass = $response->is_correct ? 'bg-success' : 'bg-danger';
                                                } elseif ($studentAnswerValue === 'false') {
                                                    $displayText = 'خطأ';
                                                    $badgeClass = $response->is_correct ? 'bg-success' : 'bg-danger';
                                                } else {
                                                    $displayText = 'لم يتم الإجابة';
                                                    $badgeClass = 'bg-secondary';
                                                }
                                            @endphp
                                            <span class="badge {{ $badgeClass }} text-white fs-14 px-3 py-2">
                                                {{ $displayText }}
                                            </span>
                                        @elseif($question->questionType->name == 'fill_blanks')
                                            @php
                                                // Extract answer from response_data
                                                $fillBlanksAnswer = null;
                                                if ($response->response_data) {
                                                    $responseData = is_array($response->response_data) ? $response->response_data : json_decode($response->response_data, true);
                                                    if (isset($responseData['answer'])) {
                                                        $fillBlanksAnswer = $responseData['answer'];
                                                    } else {
                                                        $fillBlanksAnswer = $responseData;
                                                    }
                                                } elseif ($response->response_text) {
                                                    $fillBlanksAnswer = json_decode($response->response_text, true);
                                                }
                                                
                                                // Display fill blanks answer
                                                if ($fillBlanksAnswer && is_array($fillBlanksAnswer) && !empty($fillBlanksAnswer)) {
                                                    $questionText = $question->question_text;
                                                    $normalizedText = preg_replace('/_{3,}/', '[[blank]]', $questionText);
                                                    $parts = preg_split('/\[\[blank\]\]/', $normalizedText);
                                                    $savedAnswers = [];
                                                    foreach ($fillBlanksAnswer as $key => $value) {
                                                        if (is_numeric($key)) {
                                                            $savedAnswers[(int)$key] = $value;
                                                        }
                                                    }
                                                } else {
                                                    $fillBlanksAnswer = null;
                                                }
                                            @endphp
                                            @if($fillBlanksAnswer && is_array($fillBlanksAnswer) && !empty($savedAnswers))
                                                <div class="p-3 bg-white rounded border">
                                                    @foreach($parts as $index => $part)
                                                        <span>{!! $part !!}</span>
                                                        @if($index < count($parts) - 1)
                                                            <span class="badge bg-primary text-white px-3 py-2 ms-1">
                                                                {{ $savedAnswers[$index] ?? '___' }}
                                                            </span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted">لم يتم الإجابة</span>
                                            @endif
                                        @elseif($question->questionType->name == 'matching')
                                            @php
                                                $matchingAnswer = null;
                                                if ($response->response_data) {
                                                    $responseData = is_array($response->response_data) ? $response->response_data : json_decode($response->response_data, true);
                                                    if (isset($responseData['answer'])) {
                                                        $matchingAnswer = $responseData['answer'];
                                                    } else {
                                                        $matchingAnswer = $responseData;
                                                    }
                                                }
                                            @endphp
                                            @if($matchingAnswer && is_array($matchingAnswer) && !empty($matchingAnswer))
                                                <ul class="mb-0">
                                                    @foreach($question->options as $option)
                                                        @if(isset($matchingAnswer[$option->id]))
                                                            <li class="mb-2">
                                                                <strong>{{ $option->option_text }}:</strong> {{ $matchingAnswer[$option->id] }}
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span class="text-muted">لم يتم الإجابة</span>
                                            @endif
                                        @elseif($question->questionType->name == 'ordering')
                                            @php
                                                $orderingAnswer = null;
                                                if ($response->response_data) {
                                                    $responseData = is_array($response->response_data) ? $response->response_data : json_decode($response->response_data, true);
                                                    if (isset($responseData['answer'])) {
                                                        $orderingAnswer = $responseData['answer'];
                                                    } else {
                                                        $orderingAnswer = $responseData;
                                                    }
                                                }
                                            @endphp
                                            @if($orderingAnswer && is_array($orderingAnswer) && !empty($orderingAnswer))
                                                <ol class="mb-0">
                                                    @foreach($orderingAnswer as $optionId)
                                                        @php
                                                            $option = $question->options->find($optionId);
                                                        @endphp
                                                        @if($option)
                                                            <li class="mb-2">{{ $option->option_text }}</li>
                                                        @endif
                                                    @endforeach
                                                </ol>
                                            @else
                                                <span class="text-muted">لم يتم الإجابة</span>
                                            @endif
                                        @elseif(in_array($question->questionType->name, ['numerical', 'calculated']))
                                            @php
                                                $numericalAnswer = null;
                                                if ($response->response_text) {
                                                    $numericalAnswer = $response->response_text;
                                                } elseif ($response->response_data) {
                                                    $responseData = is_array($response->response_data) ? $response->response_data : json_decode($response->response_data, true);
                                                    if (isset($responseData['answer'])) {
                                                        $numericalAnswer = is_array($responseData['answer']) ? (string)($responseData['answer']['numeric_value'] ?? $responseData['answer'][0] ?? '') : (string)$responseData['answer'];
                                                    } elseif (isset($responseData['numeric_value'])) {
                                                        $numericalAnswer = (string)$responseData['numeric_value'];
                                                    } else {
                                                        $numericalAnswer = is_numeric($responseData) ? (string)$responseData : null;
                                                    }
                                                }
                                            @endphp
                                            @if($numericalAnswer !== null && $numericalAnswer !== '')
                                                <span class="badge bg-info text-white fs-14 px-3 py-2">{{ $numericalAnswer }}</span>
                                            @else
                                                <span class="text-muted">لم يتم الإجابة</span>
                                            @endif
                                        @elseif($response->response_text)
                                            {{ $response->response_text }}
                                        @elseif($response->selected_option_ids)
                                            <ul class="mb-0">
                                                @foreach($question->options as $option)
                                                    @if(in_array($option->id, $response->selected_option_ids))
                                                        <li class="mb-2">
                                                            {{ $option->option_text }}
                                                            @if($option->is_correct)
                                                                <i class="fas fa-check-circle text-success ms-2"></i>
                                                            @else
                                                                <i class="fas fa-times-circle text-danger ms-2"></i>
                                                            @endif
                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        @elseif($response->response_data)
                                            @php
                                                $responseData = is_array($response->response_data) ? $response->response_data : json_decode($response->response_data, true);
                                                if (isset($responseData['answer'])) {
                                                    $genericAnswer = $responseData['answer'];
                                                } else {
                                                    $genericAnswer = $responseData;
                                                }
                                            @endphp
                                            @if(is_array($genericAnswer))
                                                <pre class="mb-0">{{ json_encode($genericAnswer, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            @else
                                                {{ $genericAnswer }}
                                            @endif
                                        @else
                                            <span class="text-muted">لم يتم الإجابة</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Correct Answer (if allowed) -->
                                @if($attempt->quiz->show_correct_answers && $question->questionType->name != 'essay')
                                    <div class="mb-3">
                                        <p class="text-success mb-2">
                                            <strong><i class="fas fa-lightbulb me-1"></i>الإجابة الصحيحة:</strong>
                                        </p>
                                        <div class="p-3 bg-success-transparent rounded border border-success">
                                            @if(in_array($question->questionType->name, ['multiple_choice_single', 'multiple_choice_multiple', 'matching', 'ordering']))
                                                <ul class="mb-0">
                                                    @foreach($question->options->where('is_correct', true) as $option)
                                                        <li>{{ $option->option_text }}</li>
                                                    @endforeach
                                                </ul>
                                            @elseif($question->questionType->name == 'true_false')
                                                @php
                                                    $correctOption = $question->options->where('is_correct', true)->first();
                                                    if ($correctOption) {
                                                        $correctOptionText = strtolower(trim(strip_tags($correctOption->option_text)));
                                                        $correctAnswerValue = ($correctOptionText === 'صح' || $correctOptionText === 'true' || $correctOptionText === '1' || $correctOptionText === 'صحيح') ? 'true' : 'false';
                                                        $displayText = $correctAnswerValue === 'true' ? 'صحيح' : 'خطأ';
                                                    } else {
                                                        $displayText = 'غير محدد';
                                                    }
                                                @endphp
                                                <span class="badge bg-success text-white fs-14 px-3 py-2">
                                                    {{ $displayText }}
                                                </span>
                                            @elseif($question->questionType->name == 'fill_blanks')
                                                @php
                                                    $correctAnswers = $question->metadata['correct_answers'] ?? [];
                                                    $questionText = $question->question_text;
                                                    $normalizedText = preg_replace('/_{3,}/', '[[blank]]', $questionText);
                                                    $parts = preg_split('/\[\[blank\]\]/', $normalizedText);
                                                @endphp
                                                @if(!empty($correctAnswers))
                                                    <div class="p-3 bg-white rounded border">
                                                        @foreach($parts as $index => $part)
                                                            <span>{!! $part !!}</span>
                                                            @if($index < count($parts) - 1)
                                                                <span class="badge bg-success text-white px-3 py-2 ms-1">
                                                                    {{ $correctAnswers[$index] ?? '___' }}
                                                                </span>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @else
                                                    {{ $question->metadata['answer'] ?? 'راجع المدرس' }}
                                                @endif
                                            @elseif($question->questionType->name == 'matching')
                                                @php
                                                    $correctMatching = [];
                                                    foreach ($question->options as $option) {
                                                        if ($option->is_correct) {
                                                            $correctMatching[$option->id] = $option->feedback ?? $option->option_text;
                                                        }
                                                    }
                                                @endphp
                                                @if(!empty($correctMatching))
                                                    <ul class="mb-0">
                                                        @foreach($correctMatching as $optionId => $correctAnswer)
                                                            @php
                                                                $option = $question->options->find($optionId);
                                                            @endphp
                                                            @if($option)
                                                                <li class="mb-2">
                                                                    <strong>{{ $option->option_text }}:</strong> {{ $correctAnswer }}
                                                                </li>
                                                            @endif
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    {{ $question->metadata['answer'] ?? 'راجع المدرس' }}
                                                @endif
                                            @elseif($question->questionType->name == 'ordering')
                                                @php
                                                    $correctOrder = $question->options->where('is_correct', true)->sortBy('option_order')->pluck('id')->toArray();
                                                    if (empty($correctOrder)) {
                                                        $correctOrder = $question->options->sortBy('option_order')->pluck('id')->toArray();
                                                    }
                                                @endphp
                                                @if(!empty($correctOrder))
                                                    <ol class="mb-0">
                                                        @foreach($correctOrder as $optionId)
                                                            @php
                                                                $option = $question->options->find($optionId);
                                                            @endphp
                                                            @if($option)
                                                                <li class="mb-2">{{ $option->option_text }}</li>
                                                            @endif
                                                        @endforeach
                                                    </ol>
                                                @else
                                                    {{ $question->metadata['answer'] ?? 'راجع المدرس' }}
                                                @endif
                                            @elseif(in_array($question->questionType->name, ['numerical', 'calculated']))
                                                @php
                                                    $correctNumerical = $question->metadata['correct_answer'] ?? $question->metadata['answer'] ?? null;
                                                @endphp
                                                @if($correctNumerical !== null)
                                                    <span class="badge bg-success text-white fs-14 px-3 py-2">{{ $correctNumerical }}</span>
                                                @else
                                                    {{ 'راجع المدرس' }}
                                                @endif
                                            @elseif($question->questionType->name == 'short_answer')
                                                @php
                                                    $correctShortAnswer = $question->metadata['correct_answer'] ?? $question->metadata['answer'] ?? null;
                                                @endphp
                                                @if($correctShortAnswer !== null)
                                                    {{ $correctShortAnswer }}
                                                @else
                                                    {{ 'راجع المدرس' }}
                                                @endif
                                            @else
                                                {{ $question->metadata['answer'] ?? 'راجع المدرس' }}
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                <!-- Explanation -->
                                @if($question->explanation)
                                    <div class="alert alert-info mb-0">
                                        <h6 class="alert-heading">
                                            <i class="fas fa-info-circle me-2"></i>شرح الإجابة
                                        </h6>
                                        <p class="mb-0">{{ $question->explanation }}</p>
                                    </div>
                                @endif

                                <!-- Feedback -->
                                @if($response->feedback)
                                    <div class="alert alert-warning mt-3 mb-0">
                                        <h6 class="alert-heading">
                                            <i class="fas fa-comment me-2"></i>ملاحظات المصحح
                                        </h6>
                                        <p class="mb-0">{{ $response->feedback }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Actions -->
            <div class="card custom-card mt-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between flex-wrap gap-2">
                        <a href="{{ route('student.quizzes.show', $attempt->quiz_id) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right me-2"></i>العودة للاختبار
                        </a>
                        <div>
                            @if($attempt->quiz->canAttempt(auth()->id()))
                                <a href="{{ route('student.quizzes.show', $attempt->quiz_id) }}" class="btn btn-primary">
                                    <i class="fas fa-redo me-2"></i>محاولة جديدة
                                </a>
                            @endif
                            <button type="button" class="btn btn-success" onclick="markAsCompleted()">
                                <i class="fas fa-check-circle me-2"></i>تم الإنجاز ✅
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

@section('scripts')
<script>
function markAsCompleted() {
    if (!confirm('هل تريد وضع علامة "تم الإنجاز" على هذا الاختبار؟')) {
        return;
    }

    $.ajax({
        url: '{{ route("student.quizzes.mark-completed", $attempt->id) }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            alert('تم وضع علامة الإنجاز بنجاح!');
            location.reload();
        },
        error: function(xhr) {
            alert('حدث خطأ: ' + (xhr.responseJSON?.message || 'حاول مرة أخرى'));
        }
    });
}
</script>
@endsection
