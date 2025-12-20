@extends('admin.layouts.master')

@section('page-title')
    تصحيح المحاولة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تصحيح: {{ $attempt->quiz->title }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('grading.index') }}">التصحيح</a></li>
                            <li class="breadcrumb-item active">تصحيح المحاولة</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Student Info -->
                    <div class="card custom-card mb-4">
                        <div class="card-header bg-primary-transparent">
                            <div class="card-title mb-0">
                                <i class="fas fa-user me-2"></i>معلومات الطالب
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>الاسم:</strong> {{ $attempt->student->name }}</p>
                                    <p class="mb-2"><strong>البريد الإلكتروني:</strong> {{ $attempt->student->email }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>المحاولة رقم:</strong> #{{ $attempt->attempt_number }}</p>
                                    <p class="mb-2"><strong>تاريخ التسليم:</strong> {{ $attempt->submitted_at ? $attempt->submitted_at->format('Y-m-d H:i') : '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Grading Form -->
                    <form id="grading-form">
                        @csrf

                        @foreach($responsesNeedingGrading as $index => $response)
                            @php
                                $question = $response->question;
                                $questionNumber = $index + 1;
                            @endphp

                            <div class="card custom-card mb-4">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-0">
                                                <span class="badge bg-primary me-2">سؤال {{ $questionNumber }}</span>
                                                <span class="badge bg-info">{{ $question->questionType->display_name }}</span>
                                            </h6>
                                        </div>
                                        <div>
                                            <span class="badge bg-secondary">الدرجة القصوى: {{ $response->max_score }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Question Text -->
                                    <div class="mb-3">
                                        <h6 class="fw-bold mb-2">نص السؤال:</h6>
                                        <p>{{ $question->question_text }}</p>
                                        @if($question->media_url && $question->media_type == 'image')
                                            <img src="{{ $question->media_url }}" alt="صورة السؤال" class="img-fluid rounded mt-2" style="max-width: 400px;">
                                        @endif
                                    </div>

                                    <!-- Student Answer -->
                                    <div class="mb-3">
                                        <h6 class="fw-bold mb-2">إجابة الطالب:</h6>
                                        <div class="p-3 bg-light rounded">
                                            @if($response->response_text)
                                                <p class="mb-0">{{ $response->response_text }}</p>
                                            @elseif($response->selected_option_ids)
                                                <ul class="mb-0">
                                                    @foreach($question->options as $option)
                                                        @if(in_array($option->id, $response->selected_option_ids))
                                                            <li>{{ $option->option_text }}</li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span class="text-muted">لم يتم الإجابة</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Correct Answer -->
                                    @if($question->explanation)
                                        <div class="mb-3">
                                            <h6 class="fw-bold mb-2 text-success">
                                                <i class="fas fa-lightbulb me-1"></i>الإجابة النموذجية:
                                            </h6>
                                            <div class="alert alert-success mb-0">
                                                {{ $question->explanation }}
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Grading Section -->
                                    <div class="row g-3 mt-3 p-3 bg-warning-transparent rounded">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">الدرجة المحصلة <span class="text-danger">*</span></label>
                                            <input type="number"
                                                   class="form-control score-input"
                                                   name="score_{{ $response->id }}"
                                                   data-response-id="{{ $response->id }}"
                                                   data-max-score="{{ $response->max_score }}"
                                                   min="0"
                                                   max="{{ $response->max_score }}"
                                                   step="0.5"
                                                   value="{{ old('score_' . $response->id, $response->score_obtained) }}"
                                                   required>
                                            <small class="text-muted">الحد الأقصى: {{ $response->max_score }}</small>
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label fw-bold">الملاحظات (اختياري)</label>
                                            <textarea class="form-control feedback-input"
                                                      name="feedback_{{ $response->id }}"
                                                      data-response-id="{{ $response->id }}"
                                                      rows="2"
                                                      placeholder="أضف ملاحظات للطالب...">{{ old('feedback_' . $response->id, $response->feedback) }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <button type="button"
                                                    class="btn btn-success btn-sm save-response-btn"
                                                    data-response-id="{{ $response->id }}">
                                                <i class="fas fa-save me-1"></i>حفظ التصحيح
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    </form>

                    <!-- Auto-Graded Questions -->
                    @if($attempt->responses()->where('auto_graded', true)->count() > 0)
                        <div class="card custom-card mb-4">
                            <div class="card-header bg-success-transparent">
                                <div class="card-title mb-0">
                                    <i class="fas fa-check-circle me-2 text-success"></i>الأسئلة المصححة تلقائياً
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>السؤال</th>
                                                <th>النوع</th>
                                                <th>الدرجة</th>
                                                <th>الحالة</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($attempt->responses()->where('auto_graded', true)->get() as $response)
                                                <tr>
                                                    <td>{{ Str::limit($response->question->question_text, 50) }}</td>
                                                    <td><span class="badge bg-info-transparent">{{ $response->questionType->display_name }}</span></td>
                                                    <td><span class="badge bg-secondary">{{ $response->score_obtained }}/{{ $response->max_score }}</span></td>
                                                    <td>
                                                        @if($response->is_correct)
                                                            <span class="badge bg-success">صحيح</span>
                                                        @else
                                                            <span class="badge bg-danger">خطأ</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Final Feedback -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-comment me-2"></i>ملاحظات عامة (اختياري)
                            </div>
                        </div>
                        <div class="card-body">
                            <textarea class="form-control" id="general-feedback" rows="4"
                                      placeholder="أضف ملاحظات عامة للطالب...">{{ $attempt->feedback }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Summary -->
                    <div class="card custom-card mb-4">
                        <div class="card-header bg-info-transparent">
                            <div class="card-title mb-0">
                                <i class="fas fa-chart-bar me-2"></i>ملخص التصحيح
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">إجمالي الأسئلة:</span>
                                    <strong>{{ $attempt->responses()->count() }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">مُصحح تلقائياً:</span>
                                    <strong class="text-success">{{ $attempt->responses()->where('auto_graded', true)->count() }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">يحتاج تصحيح:</span>
                                    <strong class="text-danger">{{ $responsesNeedingGrading->count() }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">تم التصحيح:</span>
                                    <strong class="text-primary" id="graded-count">{{ $attempt->responses()->whereNotNull('score_obtained')->count() }}</strong>
                                </div>
                            </div>

                            <hr>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">الدرجة الحالية:</span>
                                    <strong class="text-primary" id="current-score">{{ number_format($attempt->total_score ?? 0, 1) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">الدرجة القصوى:</span>
                                    <strong>{{ $attempt->max_score }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">النسبة المئوية:</span>
                                    <strong class="text-{{ $attempt->passed ? 'success' : 'danger' }}" id="percentage-score">
                                        {{ number_format($attempt->percentage_score ?? 0, 1) }}%
                                    </strong>
                                </div>
                            </div>

                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-{{ $attempt->passed ? 'success' : 'danger' }}"
                                     id="score-progress"
                                     style="width: {{ $attempt->percentage_score ?? 0 }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card custom-card">
                        <div class="card-header bg-warning-transparent">
                            <div class="card-title mb-0">
                                <i class="fas fa-cog me-2"></i>الإجراءات
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-success" id="complete-grading-btn">
                                    <i class="fas fa-check-circle me-2"></i>إنهاء التصحيح
                                </button>
                                <form action="{{ route('grading.regrade', $attempt->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-warning w-100"
                                            onclick="return confirm('هل تريد إعادة تصحيح الأسئلة التلقائية؟')">
                                        <i class="fas fa-redo me-2"></i>إعادة التصحيح التلقائي
                                    </button>
                                </form>
                                <hr>
                                <a href="{{ route('grading.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-right me-2"></i>العودة للقائمة
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

@section('scripts')
<script>
$(document).ready(function() {
    // Save individual response
    $('.save-response-btn').click(function() {
        const responseId = $(this).data('response-id');
        const score = $(`input[name="score_${responseId}"]`).val();
        const feedback = $(`textarea[name="feedback_${responseId}"]`).val();
        const maxScore = $(`input[name="score_${responseId}"]`).data('max-score');

        if (parseFloat(score) > parseFloat(maxScore)) {
            alert('الدرجة المدخلة أكبر من الدرجة القصوى!');
            return;
        }

        $.ajax({
            url: '{{ route("grading.grade-response", ":id") }}'.replace(':id', responseId),
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                score_obtained: score,
                feedback: feedback,
                is_correct: parseFloat(score) >= parseFloat(maxScore)
            },
            success: function(response) {
                if (response.success) {
                    // Update summary
                    updateSummary(response.attempt);

                    // Show success message
                    alert('تم حفظ التصحيح بنجاح');

                    // Disable inputs
                    $(`input[name="score_${responseId}"]`).prop('disabled', true);
                    $(`textarea[name="feedback_${responseId}"]`).prop('disabled', true);
                    $(`button[data-response-id="${responseId}"]`).prop('disabled', true)
                        .removeClass('btn-success').addClass('btn-secondary')
                        .html('<i class="fas fa-check me-1"></i>تم الحفظ');
                }
            },
            error: function(xhr) {
                alert('حدث خطأ: ' + (xhr.responseJSON?.message || 'حاول مرة أخرى'));
            }
        });
    });

    // Complete grading
    $('#complete-grading-btn').click(function() {
        const feedback = $('#general-feedback').val();

        if (!confirm('هل أنت متأكد من إنهاء التصحيح؟ تأكد من تصحيح جميع الأسئلة.')) {
            return;
        }

        $.ajax({
            url: '{{ route("grading.complete", $attempt->id) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                feedback: feedback
            },
            success: function(response) {
                alert('تم إنهاء التصحيح بنجاح');
                window.location.href = '{{ route("grading.index") }}';
            },
            error: function(xhr) {
                alert('خطأ: ' + (xhr.responseJSON?.message || xhr.responseJSON?.error || 'حاول مرة أخرى'));
            }
        });
    });

    function updateSummary(attempt) {
        $('#current-score').text(parseFloat(attempt.total_score || 0).toFixed(1));
        $('#percentage-score').text(parseFloat(attempt.percentage_score || 0).toFixed(1) + '%');
        $('#score-progress').css('width', (attempt.percentage_score || 0) + '%');

        // Update graded count
        const gradedCount = $('.save-response-btn:disabled').length;
        $('#graded-count').text(gradedCount);
    }
});
</script>
@endsection
