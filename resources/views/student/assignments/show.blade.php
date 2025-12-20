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

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('student.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">{{ $assignment->title }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('student.courses.show', $assignment->course_id) }}">{{ $assignment->course->title }}</a></li>
                            <li class="breadcrumb-item active">الواجب</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="row">
                <!-- Assignment Details & Submission Form -->
                <div class="col-lg-8">
                    <!-- Assignment Info -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-info-circle me-2"></i>تفاصيل الواجب
                            </div>
                        </div>
                        <div class="card-body">
                            @if($assignment->description)
                                <div class="mb-3 assignment-content">
                                    <div class="text-muted">{!! $assignment->description !!}</div>
                                </div>
                            @endif

                            @if($assignment->instructions)
                                <div class="alert alert-info assignment-content">
                                    <h6 class="mb-2"><i class="fas fa-clipboard-list me-2"></i>التعليمات:</h6>
                                    {!! $assignment->instructions !!}
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-4">
                                    <p class="mb-2"><strong>الدرجة القصوى:</strong></p>
                                    <span class="badge bg-success fs-14">{{ $assignment->max_grade }}</span>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-2"><strong>نوع التسليم:</strong></p>
                                    <span class="badge bg-secondary-transparent">
                                        @if($assignment->submission_type === 'link')
                                            روابط فقط
                                        @elseif($assignment->submission_type === 'file')
                                            ملفات فقط
                                        @else
                                            روابط وملفات
                                        @endif
                                    </span>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-2"><strong>الدرس:</strong></p>
                                    @if($assignment->lesson)
                                        <span class="badge bg-info-transparent">{{ $assignment->lesson->title }}</span>
                                    @else
                                        <span class="text-muted">غير محدد</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attachments -->
                    @if($assignment->attachments && is_array($assignment->attachments) && count($assignment->attachments) > 0)
                        <div class="card custom-card mb-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <i class="fas fa-paperclip me-2"></i>المرفقات
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    @foreach($assignment->attachments as $attachment)
                                        <div class="col-md-6">
                                            <div class="border rounded p-3">
                                                <i class="fas fa-file-{{ $attachment['type'] ?? 'alt' }} me-2"></i>
                                                {{ $attachment['name'] }}
                                                <br>
                                                <small class="text-muted">{{ number_format($attachment['size'] / 1024, 2) }} KB</small>
                                                <a href="{{ Storage::url($attachment['path']) }}" target="_blank" class="btn btn-sm btn-primary float-end">
                                                    <i class="fas fa-download"></i>
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
                        <div class="card custom-card mb-4">
                            <div class="card-header bg-success text-white">
                                <div class="card-title text-white">
                                    <i class="fas fa-star me-2"></i>التقييم
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    <h2 class="text-success">{{ $latestSubmission->getFinalGrade() }} / {{ $assignment->max_grade }}</h2>
                                    <p class="text-muted mb-0">
                                        النسبة المئوية: {{ number_format($latestSubmission->getGradePercentage(), 2) }}%
                                    </p>
                                    @if($latestSubmission->is_late && $assignment->late_penalty_percentage > 0)
                                        <small class="text-danger">
                                            (تم خصم {{ $assignment->late_penalty_percentage }}% بسبب التأخير)
                                        </small>
                                    @endif
                                </div>

                                @if($latestSubmission->feedback)
                                    <hr>
                                    <div class="alert alert-light">
                                        <h6 class="mb-2"><i class="fas fa-comment me-2"></i>ملاحظات المدرس:</h6>
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
                        <div class="card custom-card mb-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <i class="fas fa-upload me-2"></i>{{ $latestSubmission ? 'إعادة التسليم' : 'تسليم الواجب' }}
                                </div>
                            </div>
                            <div class="card-body">
                                @if($canResubmit)
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        يمكنك إعادة تسليم الواجب.
                                        @php
                                            $remaining = $assignment->getRemainingResubmissions(auth()->id());
                                        @endphp
                                        @if($remaining !== null)
                                            المحاولات المتبقية: {{ $remaining }}
                                        @endif
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
                                                        <i class="fas fa-plus"></i>
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

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane me-2"></i>تسليم الواجب
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="saveDraft()">
                                            <i class="fas fa-save me-2"></i>حفظ كمسودة
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @elseif($assignment->isPastDue() && !$assignment->canSubmitLate())
                        <div class="card custom-card mb-4">
                            <div class="card-body">
                                <div class="alert alert-danger text-center">
                                    <i class="fas fa-times-circle fs-48 mb-3"></i>
                                    <h5>انتهى موعد التسليم</h5>
                                    <p class="mb-0">لم يعد بإمكانك تسليم هذا الواجب</p>
                                </div>
                            </div>
                        </div>
                    @elseif(!$assignment->isAvailable())
                        <div class="card custom-card mb-4">
                            <div class="card-body">
                                <div class="alert alert-warning text-center">
                                    <i class="fas fa-clock fs-48 mb-3"></i>
                                    <h5>الواجب غير متاح حالياً</h5>
                                    @if($assignment->available_from)
                                        <p class="mb-0">سيكون متاحاً من: {{ $assignment->available_from->format('Y-m-d H:i') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Previous Submissions -->
                    @if($submissions->count() > 0)
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">
                                    <i class="fas fa-history me-2"></i>محاولاتك السابقة ({{ $submissions->count() }})
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover text-nowrap">
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
                                                        <button type="button" class="btn btn-sm btn-info"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#viewSubmissionModal{{ $submission->id }}">
                                                            <i class="fas fa-eye"></i>
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

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Deadlines -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-clock me-2"></i>المواعيد النهائية
                            </div>
                        </div>
                        <div class="card-body">
                            @if($assignment->available_from)
                                <div class="mb-3">
                                    <p class="mb-1"><strong>متاح من:</strong></p>
                                    <p class="text-muted mb-0">{{ $assignment->available_from->format('Y-m-d H:i') }}</p>
                                </div>
                            @endif

                            @if($assignment->due_date)
                                <div class="mb-3">
                                    <p class="mb-1"><strong>موعد التسليم:</strong></p>
                                    <p class="mb-0 {{ $assignment->isPastDue() ? 'text-danger' : 'text-success' }}">
                                        {{ $assignment->due_date->format('Y-m-d H:i') }}
                                        @if($assignment->isPastDue())
                                            <span class="badge bg-danger ms-2">منتهي</span>
                                        @else
                                            <span class="badge bg-success ms-2">نشط</span>
                                        @endif
                                    </p>
                                </div>
                            @endif

                            @if($assignment->allow_late_submission && $assignment->late_submission_until)
                                <div class="mb-3">
                                    <p class="mb-1"><strong>التسليم المتأخر حتى:</strong></p>
                                    <p class="text-muted mb-0">{{ $assignment->late_submission_until->format('Y-m-d H:i') }}</p>
                                    @if($assignment->late_penalty_percentage > 0)
                                        <small class="text-danger">خصم {{ $assignment->late_penalty_percentage }}%</small>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Your Status -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-user me-2"></i>حالتك
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <p class="mb-1"><strong>عدد المحاولات:</strong></p>
                                <p class="text-muted mb-0">{{ $submissions->count() }}</p>
                            </div>

                            @if($latestSubmission)
                                <div class="mb-3">
                                    <p class="mb-1"><strong>آخر تسليم:</strong></p>
                                    <p class="text-muted mb-0">
                                        {{ $latestSubmission->submitted_at ? $latestSubmission->submitted_at->format('Y-m-d H:i') : '-' }}
                                    </p>
                                </div>

                                <div class="mb-3">
                                    <p class="mb-1"><strong>حالة آخر تسليم:</strong></p>
                                    @if($latestSubmission->status === 'graded')
                                        <span class="badge bg-success">تم التقييم</span>
                                    @elseif($latestSubmission->status === 'submitted')
                                        <span class="badge bg-warning">قيد الانتظار</span>
                                    @elseif($latestSubmission->status === 'draft')
                                        <span class="badge bg-secondary">مسودة</span>
                                    @endif
                                </div>

                                @if($latestSubmission->grade !== null)
                                    <div>
                                        <p class="mb-1"><strong>درجتك:</strong></p>
                                        <h4 class="text-success">{{ $latestSubmission->getFinalGrade() }} / {{ $assignment->max_grade }}</h4>
                                    </div>
                                @endif
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-info-circle me-2"></i>لم تقم بتسليم الواجب بعد
                                </div>
                            @endif
                        </div>
                    </div>
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
                <i class="fas fa-minus"></i>
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
