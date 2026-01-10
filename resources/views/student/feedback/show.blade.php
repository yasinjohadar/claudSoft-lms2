@extends('student.layouts.master')

@section('page-title')
    ملاحظات AI
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">
                    <i class="fas fa-robot text-primary me-2"></i>
                    ملاحظات AI
                </h5>
            </div>
            <a href="{{ route('student.feedback.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-right me-1"></i> رجوع
            </a>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card custom-card">
                    <div class="card-header bg-{{ $feedback->feedback_type === 'performance' ? 'info' : ($feedback->feedback_type === 'improvement' ? 'warning' : 'primary') }} text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-comment-dots me-2"></i>
                            الملاحظات
                            <span class="badge bg-light text-dark ms-2">
                                {{ \App\Models\AIStudentFeedback::FEEDBACK_TYPES[$feedback->feedback_type] ?? $feedback->feedback_type }}
                            </span>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            {!! nl2br(e($feedback->feedback_text)) !!}
                        </div>

                        @if($feedback->suggestions && count($feedback->suggestions) > 0)
                            <div class="alert alert-success">
                                <h6 class="mb-3">
                                    <i class="fas fa-lightbulb me-2"></i>
                                    اقتراحات للتحسين:
                                </h6>
                                <ul class="mb-0">
                                    @foreach($feedback->suggestions as $suggestion)
                                        <li>{{ $suggestion }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card custom-card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>معلومات</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            @if($feedback->quizAttempt)
                                <tr>
                                    <td class="text-muted">الاختبار:</td>
                                    <td><strong>{{ $feedback->quizAttempt->quiz->title ?? '-' }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">الدرجة:</td>
                                    <td>
                                        <span class="badge bg-{{ $feedback->quizAttempt->percentage >= 70 ? 'success' : ($feedback->quizAttempt->percentage >= 50 ? 'warning' : 'danger') }}">
                                            {{ $feedback->quizAttempt->score ?? 0 }} / {{ $feedback->quizAttempt->max_score ?? 0 }}
                                            ({{ $feedback->quizAttempt->percentage ?? 0 }}%)
                                        </span>
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td class="text-muted">نوع الملاحظات:</td>
                                <td>
                                    <span class="badge bg-{{ $feedback->feedback_type === 'performance' ? 'info' : ($feedback->feedback_type === 'improvement' ? 'warning' : 'secondary') }}">
                                        {{ \App\Models\AIStudentFeedback::FEEDBACK_TYPES[$feedback->feedback_type] ?? $feedback->feedback_type }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">التاريخ:</td>
                                <td>{{ $feedback->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card custom-card mt-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-question-circle me-2"></i>معلومات إضافية</h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-0">
                            هذه الملاحظات تم إنشاؤها تلقائياً باستخدام الذكاء الاصطناعي بناءً على أدائك في النظام.
                            يمكنك استخدامها كدليل لتحسين مستواك.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('scripts')
@stop



