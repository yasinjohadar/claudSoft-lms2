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
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="card custom-card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-comments me-2"></i>ملاحظات الذكاء الاصطناعي</h6>
            </div>
            <div class="card-body">
                @if($feedbacks->count() > 0)
                    <div class="row">
                        @foreach($feedbacks as $feedback)
                            <div class="col-md-6 mb-3">
                                <div class="card border-start border-{{ $feedback->feedback_type === 'performance' ? 'info' : ($feedback->feedback_type === 'improvement' ? 'warning' : 'primary') }} border-3">
                                    <div class="card-header bg-light">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0">
                                                    <span class="badge bg-{{ $feedback->feedback_type === 'performance' ? 'info' : ($feedback->feedback_type === 'improvement' ? 'warning' : 'secondary') }} me-2">
                                                        {{ \App\Models\AIStudentFeedback::FEEDBACK_TYPES[$feedback->feedback_type] ?? $feedback->feedback_type }}
                                                    </span>
                                                    @if($feedback->quizAttempt)
                                                        <small class="text-muted">{{ $feedback->quizAttempt->quiz->title ?? '' }}</small>
                                                    @endif
                                                </h6>
                                            </div>
                                            <small class="text-muted">{{ $feedback->created_at->format('Y-m-d') }}</small>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted small mb-2">
                                            {{ Str::limit(strip_tags($feedback->feedback_text), 150) }}
                                        </p>
                                        @if($feedback->suggestions && count($feedback->suggestions) > 0)
                                            <div class="mb-2">
                                                <small class="text-success">
                                                    <i class="fas fa-lightbulb me-1"></i>
                                                    {{ count($feedback->suggestions) }} اقتراح
                                                </small>
                                            </div>
                                        @endif
                                        <a href="{{ route('student.feedback.show', $feedback) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye me-1"></i> عرض التفاصيل
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-3">
                        {{ $feedbacks->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-comments fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">لا توجد ملاحظات</h5>
                        <p class="text-muted">لم يتم إنشاء أي ملاحظات لك بعد. سيتم إنشاء الملاحظات تلقائياً من قبل المدير.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('scripts')
@stop



