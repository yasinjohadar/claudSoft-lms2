@extends('student.layouts.master')

@section('page-title')
    {{ $assignment->title }}
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/toolbar/prism-toolbar.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/line-numbers/prism-line-numbers.min.css">
<style>
    .assignment-content pre {
        border-radius: 8px;
        margin: 15px 0;
        direction: ltr !important;
        text-align: left !important;
        background: #2d2d2d;
        padding: 0;
        overflow: hidden;
    }
    .assignment-content pre code {
        font-family: 'Fira Code', 'Consolas', 'Monaco', monospace;
        font-size: 14px;
        line-height: 1.6;
        direction: ltr !important;
        text-align: left !important;
        display: block;
        padding: 1em;
    }
    .assignment-content code:not(pre code) {
        background: rgba(0, 0, 0, 0.1);
        padding: 2px 6px;
        border-radius: 4px;
        font-family: 'Fira Code', 'Consolas', monospace;
        font-size: 0.9em;
        color: #e83e8c;
    }
    /* Prism toolbar styling */
    div.code-toolbar > .toolbar {
        opacity: 1;
    }
    div.code-toolbar > .toolbar > .toolbar-item > button {
        background: #4a4a4a;
        color: #fff;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 12px;
    }
    div.code-toolbar > .toolbar > .toolbar-item > button:hover {
        background: #5a5a5a;
    }
    /* Line numbers */
    pre.line-numbers {
        padding-left: 3.8em;
    }
</style>
@stop

@php
    use Illuminate\Support\Facades\Storage;
@endphp

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
@endphp

@section('content')
    <div class="main-content app-content student-assignment-show-page">
        <div class="container-fluid pb-3">

            @include('student.components.alerts')

            <div class="my-4 page-header-breadcrumb dashboard-fade-in">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                        @if($assignment->course)
                            <li class="breadcrumb-item"><a href="{{ route('student.courses.show', $assignment->course_id) }}">{{ $assignment->course->title }}</a></li>
                        @else
                            <li class="breadcrumb-item"><a href="{{ route('student.assignments.index') }}">واجباتي</a></li>
                        @endif
                        <li class="breadcrumb-item active">الواجب</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-clipboard me-1"></i>
                            تفاصيل الواجب
                        </span>
                        <h2 class="group-show-hero__title mb-2">{{ $assignment->title }}</h2>
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            <span class="badge bg-{{ $statusClass }}-transparent text-{{ $statusClass }}">
                                <i class="fe {{ $statusIcon }} me-1"></i>{{ $statusLabel }}
                            </span>
                            <span class="badge bg-success-transparent text-success">
                                <i class="fe fe-award me-1"></i>{{ $assignment->max_grade }} درجة
                            </span>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @if($assignment->course)
                                <span class="group-show-chip group-show-chip--sm">
                                    <i class="fe fe-book me-1"></i>{{ $assignment->course->title }}
                                </span>
                            @endif
                            @if($assignment->lesson)
                                <span class="group-show-chip group-show-chip--sm">
                                    <i class="fe fe-book-open me-1"></i>{{ $assignment->lesson->title }}
                                </span>
                            @endif
                            @if($assignment->due_date)
                                <span class="group-show-chip group-show-chip--sm {{ $assignment->isPastDue() ? 'text-danger' : '' }}">
                                    <i class="fe fe-calendar me-1"></i>{{ $assignment->due_date->format('Y-m-d H:i') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions group-show-actions--single">
                            <a href="{{ route('student.assignments.index') }}"
                               class="group-show-action group-show-action--info">
                                <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                                <span class="group-show-action__text">رجوع للواجبات</span>
                            </a>
                            @if($canSubmit || $canResubmit)
                                <a href="#submission-form"
                                   class="group-show-action group-show-action--primary">
                                    <span class="group-show-action__icon"><i class="fe fe-upload"></i></span>
                                    <span class="group-show-action__text">{{ $latestSubmission ? 'إعادة التسليم' : 'تسليم الواجب' }}</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-xl-8">
                    <div class="card custom-card student-quizzes-panel dashboard-fade-in mb-4">
                        <div class="card-header border-0 pb-0">
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar avatar-sm bg-primary-transparent">
                                    <i class="fe fe-file-text text-primary"></i>
                                </span>
                                <div>
                                    <h6 class="card-title mb-1">وصف الواجب</h6>
                                    <p class="fs-12 text-muted mb-0">اقرأ المتطلبات والتعليمات بعناية قبل التسليم</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-3">
                            @if($assignment->description)
                                <div class="mb-4 assignment-content weekly-report-html-content">
                                    {!! $assignment->description !!}
                                </div>
                            @endif

                            @if($assignment->instructions)
                                <div class="p-3 rounded border border-info border-opacity-25 bg-info-transparent assignment-content">
                                    <h6 class="mb-2 d-flex align-items-center gap-2">
                                        <i class="fe fe-list text-info"></i>التعليمات
                                    </h6>
                                    {!! $assignment->instructions !!}
                                </div>
                            @endif

                            @if(!$assignment->description && !$assignment->instructions)
                                <p class="text-muted mb-0">لا يوجد وصف أو تعليمات إضافية لهذا الواجب.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Attachments -->
                    @if($assignment->attachments && is_array($assignment->attachments) && count($assignment->attachments) > 0)
                        <div class="card custom-card student-quizzes-panel dashboard-fade-in mb-4">
                            <div class="card-header border-0 pb-0">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar avatar-sm bg-cyan-transparent">
                                        <i class="fe fe-paperclip text-info"></i>
                                    </span>
                                    <h6 class="card-title mb-0">المرفقات</h6>
                                </div>
                            </div>
                            <div class="card-body pt-3">
                                <div class="row g-3">
                                    @foreach($assignment->attachments as $attachment)
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center gap-3 p-3 rounded border bg-light">
                                                <span class="avatar avatar-md bg-primary-transparent flex-shrink-0">
                                                    <i class="fe fe-file text-primary"></i>
                                                </span>
                                                <div class="flex-fill min-w-0">
                                                    <span class="fw-semibold d-block text-truncate">{{ $attachment['name'] }}</span>
                                                    <small class="text-muted">{{ number_format($attachment['size'] / 1024, 2) }} KB</small>
                                                </div>
                                                <a href="{{ Storage::url($attachment['path']) }}" target="_blank" class="btn btn-sm btn-primary rounded-pill">
                                                    <i class="fe fe-download"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Current Grade -->
                    @if($latestSubmission && $latestSubmission->grade !== null)
                        <div class="card custom-card student-quizzes-panel dashboard-fade-in mb-4 border border-success border-opacity-25">
                            <div class="card-header border-0 pb-0 bg-success-transparent">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar avatar-sm bg-success-transparent">
                                        <i class="fe fe-award text-success"></i>
                                    </span>
                                    <h6 class="card-title mb-0 text-success">التقييم</h6>
                                </div>
                            </div>
                            <div class="card-body pt-3">
                                <div class="text-center mb-3 py-3">
                                    <h2 class="text-success mb-1">{{ $latestSubmission->getFinalGrade() }} <small class="fs-16 text-muted">/ {{ $assignment->max_grade }}</small></h2>
                                    <p class="text-muted mb-0">
                                        النسبة المئوية: {{ number_format($latestSubmission->getGradePercentage(), 2) }}%
                                    </p>
                                    @if($latestSubmission->is_late && $assignment->late_penalty_percentage > 0)
                                        <small class="text-danger d-block mt-2">
                                            <i class="fe fe-minus-circle me-1"></i>تم خصم {{ $assignment->late_penalty_percentage }}% بسبب التأخير
                                        </small>
                                    @endif
                                </div>

                                @if($latestSubmission->feedback)
                                    <div class="p-3 rounded border bg-light">
                                        <h6 class="mb-2 d-flex align-items-center gap-2">
                                            <i class="fe fe-message-circle text-primary"></i>ملاحظات المدرس
                                        </h6>
                                        <p class="mb-0">{{ $latestSubmission->feedback }}</p>
                                    </div>
                                @endif

                                <small class="text-muted d-block text-center mt-3">
                                    تم التقييم بواسطة {{ $latestSubmission->grader->name ?? 'المدرس' }}
                                    في {{ $latestSubmission->graded_at->format('Y-m-d H:i') }}
                                </small>
                            </div>
                        </div>
                    @endif

                    <!-- Submission Form -->
                    @if($canSubmit || $canResubmit)
                        <div class="card custom-card student-quizzes-panel dashboard-fade-in mb-4" id="submission-form">
                            <div class="card-header border-0 pb-0">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar avatar-sm bg-warning-transparent">
                                        <i class="fe fe-upload text-warning"></i>
                                    </span>
                                    <div>
                                        <h6 class="card-title mb-1">{{ $latestSubmission ? 'إعادة التسليم' : 'تسليم الواجب' }}</h6>
                                        <p class="fs-12 text-muted mb-0">أرفق روابطك أو ملفاتك ثم اضغط تسليم</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-3">
                                @if($canResubmit)
                                    <div class="alert alert-info border-0 d-flex align-items-center gap-2">
                                        <i class="fe fe-info fs-18"></i>
                                        <span>
                                            يمكنك إعادة تسليم الواجب.
                                            @php
                                                $remaining = $assignment->getRemainingResubmissions(auth()->id());
                                            @endphp
                                            @if($remaining !== null)
                                                المحاولات المتبقية: <strong>{{ $remaining }}</strong>
                                            @endif
                                        </span>
                                    </div>
                                @endif

                                <form action="{{ route('student.assignments.submit', $assignment->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf

                                    <!-- Submission Text -->
                                    <div class="mb-3">
                                        <label class="form-label">نص التسليم (اختياري)</label>
                                        <textarea name="submission_text" class="form-control @error('submission_text') is-invalid @enderror"
                                                  rows="4" placeholder="أضف أي ملاحظات أو شرح للتسليم...">{{ old('submission_text') }}</textarea>
                                        @error('submission_text')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Links -->
                                    @if(in_array($assignment->submission_type, ['link', 'both']))
                                        <div class="mb-3">
                                            <label class="form-label">الروابط (حتى {{ $assignment->max_links }} روابط)</label>
                                            <div id="links-container">
                                                <div class="input-group mb-2">
                                                    <input type="url" name="links[]" class="form-control @error('links.*') is-invalid @enderror"
                                                           placeholder="https://example.com" value="{{ old('links.0') }}">
                                                    <button type="button" class="btn btn-outline-success" onclick="addLinkField()">
                                                        <i class="fe fe-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <small class="text-muted">مثال: رابط Google Drive، GitHub، أو أي رابط آخر</small>
                                            @error('links.*')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endif

                                    <!-- Files -->
                                    @if(in_array($assignment->submission_type, ['file', 'both']))
                                        <div class="mb-3">
                                            <label class="form-label">الملفات (حتى {{ $assignment->max_files }} ملفات، {{ $assignment->max_file_size / 1024 }} MB لكل ملف)</label>
                                            <input type="file" name="files[]" class="form-control @error('files.*') is-invalid @enderror"
                                                   multiple>
                                            <small class="text-muted">يمكنك اختيار عدة ملفات في وقت واحد</small>
                                            @error('files.*')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endif

                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                                            <i class="fe fe-send me-2"></i>تسليم الواجب
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="saveDraft()">
                                            <i class="fe fe-save me-2"></i>حفظ كمسودة
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @elseif($assignment->isPastDue() && !$assignment->canSubmitLate())
                        <div class="card custom-card student-quizzes-panel dashboard-fade-in mb-4">
                            <div class="card-body text-center py-5">
                                <span class="avatar avatar-lg bg-danger-transparent mb-3">
                                    <i class="fe fe-x-circle fs-24 text-danger"></i>
                                </span>
                                <h5 class="mb-2">انتهى موعد التسليم</h5>
                                <p class="text-muted mb-0">لم يعد بإمكانك تسليم هذا الواجب</p>
                            </div>
                        </div>
                    @elseif(!$assignment->isAvailable())
                        <div class="card custom-card student-quizzes-panel dashboard-fade-in mb-4">
                            <div class="card-body text-center py-5">
                                <span class="avatar avatar-lg bg-warning-transparent mb-3">
                                    <i class="fe fe-clock fs-24 text-warning"></i>
                                </span>
                                <h5 class="mb-2">الواجب غير متاح حالياً</h5>
                                @if($assignment->available_from)
                                    <p class="text-muted mb-0">سيكون متاحاً من: {{ $assignment->available_from->format('Y-m-d H:i') }}</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Previous Submissions -->
                    @if($submissions->count() > 0)
                        <div class="card custom-card student-quizzes-panel dashboard-fade-in">
                            <div class="card-header border-0 pb-0">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar avatar-sm bg-secondary-transparent">
                                        <i class="fe fe-rotate-ccw text-secondary"></i>
                                    </span>
                                    <div>
                                        <h6 class="card-title mb-1">محاولاتك السابقة</h6>
                                        <p class="fs-12 text-muted mb-0">{{ $submissions->count() }} محاولة</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-3 p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 group-show-table">
                                        <thead>
                                            <tr>
                                                <th>المحاولة</th>
                                                <th>تاريخ التسليم</th>
                                                <th>الحالة</th>
                                                <th>الدرجة</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($submissions as $submission)
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-info-transparent">المحاولة #{{ $submission->attempt_number }}</span>
                                                    </td>
                                                    <td>
                                                        @if($submission->submitted_at)
                                                            {{ $submission->submitted_at->format('Y-m-d H:i') }}
                                                            @if($submission->is_late)
                                                                <br><span class="badge bg-danger-transparent">متأخر</span>
                                                            @endif
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($submission->status === 'graded')
                                                            <span class="badge bg-success">تم التقييم</span>
                                                        @elseif($submission->status === 'submitted')
                                                            <span class="badge bg-warning">قيد الانتظار</span>
                                                        @elseif($submission->status === 'draft')
                                                            <span class="badge bg-secondary">مسودة</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($submission->grade !== null)
                                                            <span class="badge bg-success fs-14">
                                                                {{ $submission->getFinalGrade() }} / {{ $assignment->max_grade }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#viewSubmissionModal{{ $submission->id }}">
                                                            <i class="fe fe-eye me-1"></i>عرض
                                                        </button>
                                                    </td>
                                                </tr>

                                                <!-- View Submission Modal -->
                                                <div class="modal fade" id="viewSubmissionModal{{ $submission->id }}" tabindex="-1">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">المحاولة #{{ $submission->attempt_number }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <!-- Submission Date -->
                                                                <div class="mb-3">
                                                                    <p class="mb-1"><strong>تاريخ التسليم:</strong></p>
                                                                    <p class="text-muted">
                                                                        {{ $submission->submitted_at ? $submission->submitted_at->format('Y-m-d H:i') : 'لم يتم التسليم' }}
                                                                        @if($submission->is_late)
                                                                            <span class="badge bg-danger ms-2">متأخر</span>
                                                                        @endif
                                                                    </p>
                                                                </div>

                                                                <!-- Submission Text -->
                                                                @if($submission->submission_text)
                                                                    <div class="mb-3">
                                                                        <p class="mb-1"><strong>نص التسليم:</strong></p>
                                                                        <div class="alert alert-light">
                                                                            {{ $submission->submission_text }}
                                                                        </div>
                                                                    </div>
                                                                @endif

                                                                <!-- Links -->
                                                                @if($submission->submitted_links && is_array($submission->submitted_links) && count($submission->submitted_links) > 0)
                                                                    <div class="mb-3">
                                                                        <p class="mb-1"><strong>الروابط:</strong></p>
                                                                        <ul class="list-group">
                                                                            @foreach($submission->submitted_links as $link)
                                                                                <li class="list-group-item">
                                                                                    <a href="{{ $link }}" target="_blank" class="text-primary">
                                                                                        <i class="fas fa-external-link-alt me-2"></i>{{ $link }}
                                                                                    </a>
                                                                                </li>
                                                                            @endforeach
                                                                        </ul>
                                                                    </div>
                                                                @endif

                                                                <!-- Files -->
                                                                @if($submission->submitted_files && is_array($submission->submitted_files) && count($submission->submitted_files) > 0)
                                                                    <div class="mb-3">
                                                                        <p class="mb-1"><strong>الملفات:</strong></p>
                                                                        <div class="row g-2">
                                                                            @foreach($submission->submitted_files as $index => $file)
                                                                                <div class="col-md-6">
                                                                                    <div class="border rounded p-2">
                                                                                        <i class="fas fa-file-{{ $file['type'] ?? 'alt' }} me-2"></i>
                                                                                        {{ $file['name'] }}
                                                                                        <br>
                                                                                        <small class="text-muted">{{ number_format($file['size'] / 1024, 2) }} KB</small>
                                                                                        <a href="{{ Storage::url($file['path']) }}" target="_blank" class="btn btn-sm btn-info float-end">
                                                                                            <i class="fas fa-download"></i>
                                                                                        </a>
                                                                                        @if($submission->status === 'draft')
                                                                                            <form action="{{ route('student.assignments.delete-file', $submission->id) }}" method="POST" class="d-inline"
                                                                                                  onsubmit="return confirm('هل أنت متأكد من حذف هذا الملف؟')">
                                                                                                @csrf
                                                                                                @method('DELETE')
                                                                                                <input type="hidden" name="index" value="{{ $index }}">
                                                                                                <button type="submit" class="btn btn-sm btn-danger float-end me-1">
                                                                                                    <i class="fas fa-trash"></i>
                                                                                                </button>
                                                                                            </form>
                                                                                        @endif
                                                                                    </div>
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                @endif

                                                                <!-- Grade & Feedback -->
                                                                @if($submission->grade !== null)
                                                                    <div class="alert alert-success">
                                                                        <h6 class="mb-2">التقييم</h6>
                                                                        <p class="mb-1">
                                                                            <strong>الدرجة:</strong> {{ $submission->getFinalGrade() }} / {{ $assignment->max_grade }}
                                                                            ({{ number_format($submission->getGradePercentage(), 2) }}%)
                                                                        </p>
                                                                        @if($submission->feedback)
                                                                            <hr>
                                                                            <p class="mb-1"><strong>ملاحظات المدرس:</strong></p>
                                                                            <p class="mb-0">{{ $submission->feedback }}</p>
                                                                        @endif
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-xl-4">
                    @include('student.assignments.partials.show-sidebar', compact('assignment', 'submissions', 'latestSubmission'))
                </div>
            </div>

        </div>
    </div>
@stop

@section('scripts')
<script>
    // Add link field
    let linkCount = 1;
    const maxLinks = {{ $assignment->max_links }};

    function addLinkField() {
        if (linkCount >= maxLinks) {
            alert('لقد وصلت للحد الأقصى من الروابط (' + maxLinks + ')');
            return;
        }

        const container = document.getElementById('links-container');
        const newField = document.createElement('div');
        newField.className = 'input-group mb-2';
        newField.innerHTML = `
            <input type="url" name="links[]" class="form-control" placeholder="https://example.com">
            <button type="button" class="btn btn-outline-danger" onclick="removeLinkField(this)">
                <i class="fe fe-minus"></i>
            </button>
        `;
        container.appendChild(newField);
        linkCount++;
    }

    function removeLinkField(button) {
        button.parentElement.remove();
        linkCount--;
    }

    // Save draft
    function saveDraft() {
        const form = document.querySelector('form');
        const formData = new FormData(form);

        fetch('{{ route("student.assignments.save-draft", $assignment->id) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('تم حفظ المسودة بنجاح');
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء حفظ المسودة');
        });
    }
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/toolbar/prism-toolbar.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/copy-to-clipboard/prism-copy-to-clipboard.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/line-numbers/prism-line-numbers.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Process all code blocks in assignment content
        document.querySelectorAll('.assignment-content pre').forEach(function(pre) {
            pre.classList.add('line-numbers');

            // Get the code element
            var code = pre.querySelector('code');
            if (code) {
                // Convert TinyMCE language class to Prism format
                var classes = code.className.split(' ');
                for (var i = 0; i < classes.length; i++) {
                    if (classes[i].startsWith('language-')) {
                        // Already in correct format
                        break;
                    }
                    // Map common language names
                    var langMap = {
                        'markup': 'markup',
                        'html': 'markup',
                        'xml': 'markup',
                        'javascript': 'javascript',
                        'js': 'javascript',
                        'css': 'css',
                        'php': 'php',
                        'python': 'python',
                        'java': 'java',
                        'c': 'c',
                        'cpp': 'cpp',
                        'sql': 'sql'
                    };
                    if (langMap[classes[i]]) {
                        code.classList.remove(classes[i]);
                        code.classList.add('language-' + langMap[classes[i]]);
                    }
                }
            }
        });

        // Highlight all code blocks
        Prism.highlightAll();
    });
</script>
@endsection
