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
                                        @if($response->response_text)
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
                                                {{ $question->metadata['correct_answer'] ?? 'true' }}
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
