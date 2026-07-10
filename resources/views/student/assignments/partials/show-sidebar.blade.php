@php
    $submissionStatus = 'not_submitted';
    $statusLabel = 'لم يُسلّم بعد';
    $statusClass = 'warning';
    $statusIcon = 'fe-alert-circle';

    if ($latestSubmission) {
        if ($latestSubmission->status === 'graded') {
            $submissionStatus = 'graded';
            $statusLabel = 'تم التقييم';
            $statusClass = 'success';
            $statusIcon = 'fe-check-circle';
        } elseif ($latestSubmission->status === 'submitted') {
            $submissionStatus = 'submitted';
            $statusLabel = 'بانتظار التقييم';
            $statusClass = 'info';
            $statusIcon = 'fe-clock';
        } elseif ($latestSubmission->status === 'draft') {
            $submissionStatus = 'draft';
            $statusLabel = 'مسودة';
            $statusClass = 'secondary';
            $statusIcon = 'fe-edit-3';
        }
    } elseif ($assignment->isPastDue() && !$assignment->canSubmitLate()) {
        $submissionStatus = 'overdue';
        $statusLabel = 'انتهى الموعد';
        $statusClass = 'danger';
        $statusIcon = 'fe-x-circle';
    }

    $submissionTypeLabel = match ($assignment->submission_type) {
        'link' => 'روابط فقط',
        'file' => 'ملفات فقط',
        default => 'روابط وملفات',
    };
@endphp

<div class="card custom-card student-quizzes-panel dashboard-fade-in mb-4">
    <div class="card-header border-0 pb-0">
        <div class="d-flex align-items-center gap-2">
            <span class="avatar avatar-sm bg-warning-transparent">
                <i class="fe fe-clock text-warning"></i>
            </span>
            <h6 class="card-title mb-0">المواعيد النهائية</h6>
        </div>
    </div>
    <div class="card-body pt-3">
        @if($assignment->available_from)
            <div class="d-flex justify-content-between align-items-start gap-2 mb-3 pb-3 border-bottom">
                <div>
                    <span class="fs-12 text-muted d-block mb-1">متاح من</span>
                    <span class="fw-semibold">{{ $assignment->available_from->format('Y-m-d') }}</span>
                    <small class="text-muted d-block">{{ $assignment->available_from->format('h:i A') }}</small>
                </div>
                <span class="avatar avatar-sm bg-primary-transparent">
                    <i class="fe fe-unlock text-primary"></i>
                </span>
            </div>
        @endif

        @if($assignment->due_date)
            <div class="d-flex justify-content-between align-items-start gap-2 mb-3 pb-3 border-bottom">
                <div>
                    <span class="fs-12 text-muted d-block mb-1">موعد التسليم</span>
                    <span class="fw-semibold {{ $assignment->isPastDue() ? 'text-danger' : 'text-success' }}">
                        {{ $assignment->due_date->format('Y-m-d') }}
                    </span>
                    <small class="text-muted d-block">{{ $assignment->due_date->format('h:i A') }}</small>
                </div>
                @if($assignment->isPastDue())
                    <span class="badge bg-danger-transparent text-danger">منتهي</span>
                @else
                    <span class="badge bg-success-transparent text-success">نشط</span>
                @endif
            </div>
        @endif

        @if($assignment->allow_late_submission && $assignment->late_submission_until)
            <div class="d-flex justify-content-between align-items-start gap-2">
                <div>
                    <span class="fs-12 text-muted d-block mb-1">التسليم المتأخر حتى</span>
                    <span class="fw-semibold">{{ $assignment->late_submission_until->format('Y-m-d') }}</span>
                    <small class="text-muted d-block">{{ $assignment->late_submission_until->format('h:i A') }}</small>
                    @if($assignment->late_penalty_percentage > 0)
                        <small class="text-danger d-block mt-1">
                            <i class="fe fe-minus-circle me-1"></i>خصم {{ $assignment->late_penalty_percentage }}%
                        </small>
                    @endif
                </div>
                <span class="avatar avatar-sm bg-info-transparent">
                    <i class="fe fe-watch text-info"></i>
                </span>
            </div>
        @endif
    </div>
</div>

<div class="card custom-card student-quizzes-panel dashboard-fade-in">
    <div class="card-header border-0 pb-0">
        <div class="d-flex align-items-center gap-2">
            <span class="avatar avatar-sm bg-primary-transparent">
                <i class="fe fe-user text-primary"></i>
            </span>
            <h6 class="card-title mb-0">حالتك</h6>
        </div>
    </div>
    <div class="card-body pt-3">
        <div class="row g-3 mb-3">
            <div class="col-6">
                <div class="p-3 rounded border bg-light text-center">
                    <span class="fs-12 text-muted d-block mb-1">المحاولات</span>
                    <span class="fs-20 fw-bold">{{ $submissions->count() }}</span>
                </div>
            </div>
            <div class="col-6">
                <div class="p-3 rounded border bg-light text-center">
                    <span class="fs-12 text-muted d-block mb-1">الدرجة القصوى</span>
                    <span class="fs-20 fw-bold text-success">{{ $assignment->max_grade }}</span>
                </div>
            </div>
        </div>

        <div class="alert alert-{{ $statusClass }}-transparent border-0 d-flex align-items-center gap-2 mb-3">
            <i class="fe {{ $statusIcon }} fs-18"></i>
            <span>{{ $statusLabel }}</span>
        </div>

        @if($latestSubmission)
            @if($latestSubmission->submitted_at)
                <div class="mb-3">
                    <span class="fs-12 text-muted d-block mb-1">آخر تسليم</span>
                    <span class="fw-semibold">{{ $latestSubmission->submitted_at->format('Y-m-d H:i') }}</span>
                </div>
            @endif

            @if($latestSubmission->grade !== null)
                <div class="text-center p-3 rounded border border-success border-opacity-25 bg-success-transparent">
                    <span class="fs-12 text-muted d-block mb-1">درجتك الحالية</span>
                    <h3 class="text-success mb-0">{{ $latestSubmission->getFinalGrade() }} <small class="fs-14 text-muted">/ {{ $assignment->max_grade }}</small></h3>
                    <small class="text-muted">{{ number_format($latestSubmission->getGradePercentage(), 1) }}%</small>
                </div>
            @endif
        @endif

        <div class="mt-3 pt-3 border-top">
            <span class="fs-12 text-muted d-block mb-1">نوع التسليم</span>
            <span class="badge bg-secondary-transparent">{{ $submissionTypeLabel }}</span>
        </div>
    </div>
</div>
