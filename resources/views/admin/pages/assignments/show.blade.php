@extends('admin.layouts.master')

@section('page-title')
    عرض الواجب
@stop

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">{{ $assignment->title }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('assignments.index') }}">الواجبات</a></li>
                            <li class="breadcrumb-item active">عرض الواجب</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('assignments.edit', $assignment->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>تعديل الواجب
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Assignment Details -->
                <div class="col-lg-8">
                    <!-- Basic Info -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-info-circle me-2"></i>تفاصيل الواجب
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>الكورس:</strong></p>
                                    <span class="badge bg-primary-transparent">{{ $assignment->course->title }}</span>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>الدرس:</strong></p>
                                    @if($assignment->lesson)
                                        <span class="badge bg-info-transparent">{{ $assignment->lesson->title }}</span>
                                    @else
                                        <span class="text-muted">غير محدد</span>
                                    @endif
                                </div>
                            </div>

                            @if($assignment->description)
                                <div class="mb-3">
                                    <p class="mb-2"><strong>الوصف:</strong></p>
                                    <p class="text-muted">{{ $assignment->description }}</p>
                                </div>
                            @endif

                            @if($assignment->instructions)
                                <div class="mb-3">
                                    <p class="mb-2"><strong>التعليمات:</strong></p>
                                    <div class="alert alert-info">
                                        {{ $assignment->instructions }}
                                    </div>
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
                                    <p class="mb-2"><strong>الحالة:</strong></p>
                                    @if($assignment->is_published)
                                        <span class="badge bg-success">منشور</span>
                                    @else
                                        <span class="badge bg-warning">مسودة</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Deadlines -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-clock me-2"></i>المواعيد النهائية
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <p class="mb-2"><strong>متاح من:</strong></p>
                                    <p class="text-muted">
                                        {{ $assignment->available_from ? $assignment->available_from->format('Y-m-d H:i') : 'غير محدد' }}
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-2"><strong>موعد التسليم:</strong></p>
                                    <p class="text-muted">
                                        {{ $assignment->due_date ? $assignment->due_date->format('Y-m-d H:i') : 'غير محدد' }}
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-2"><strong>التسليم المتأخر حتى:</strong></p>
                                    <p class="text-muted">
                                        {{ $assignment->late_submission_until ? $assignment->late_submission_until->format('Y-m-d H:i') : 'غير محدد' }}
                                    </p>
                                </div>
                            </div>

                            @if($assignment->allow_late_submission)
                                <div class="alert alert-warning mt-3">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    السماح بالتسليم المتأخر مع خصم {{ $assignment->late_penalty_percentage }}%
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Resubmission Settings -->
                    @if($assignment->allow_resubmission)
                        <div class="card custom-card mb-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <i class="fas fa-redo me-2"></i>إعدادات إعادة التسليم
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    السماح بإعادة التسليم:
                                    @if($assignment->max_resubmissions)
                                        حتى {{ $assignment->max_resubmissions }} مرات
                                    @else
                                        عدد غير محدود
                                    @endif
                                    @if($assignment->resubmit_after_grading_only)
                                        - فقط بعد التقييم
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

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
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Submissions List -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-list me-2"></i>التسليمات ({{ $submissions->total() }})
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover text-nowrap">
                                    <thead>
                                        <tr>
                                            <th>الطالب</th>
                                            <th>المحاولة</th>
                                            <th>تاريخ التسليم</th>
                                            <th>الحالة</th>
                                            <th>الدرجة</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($submissions as $submission)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div>
                                                            <span class="fw-semibold">{{ $submission->student->name }}</span>
                                                            <br>
                                                            <small class="text-muted">{{ $submission->student->email }}</small>
                                                        </div>
                                                    </div>
                                                </td>
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
                                                    @else
                                                        <span class="badge bg-info">{{ $submission->status }}</span>
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
                                                    <button type="button" class="btn btn-sm btn-primary"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#submissionModal{{ $submission->id }}">
                                                        <i class="fas fa-eye me-1"></i>عرض
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Submission Modal -->
                                            <div class="modal fade" id="submissionModal{{ $submission->id }}" tabindex="-1">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">تسليم {{ $submission->student->name }} - المحاولة #{{ $submission->attempt_number }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <!-- Submission Info -->
                                                            <div class="mb-4">
                                                                <h6 class="mb-3">معلومات التسليم</h6>
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <p><strong>تاريخ التسليم:</strong></p>
                                                                        <p class="text-muted">{{ $submission->submitted_at ? $submission->submitted_at->format('Y-m-d H:i') : 'لم يتم التسليم' }}</p>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <p><strong>الحالة:</strong></p>
                                                                        @if($submission->is_late)
                                                                            <span class="badge bg-danger">متأخر</span>
                                                                        @else
                                                                            <span class="badge bg-success">في الموعد</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Submission Text -->
                                                            @if($submission->submission_text)
                                                                <div class="mb-4">
                                                                    <h6 class="mb-3">نص التسليم</h6>
                                                                    <div class="alert alert-light">
                                                                        {{ $submission->submission_text }}
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            <!-- Submitted Links -->
                                                            @if($submission->submitted_links && is_array($submission->submitted_links) && count($submission->submitted_links) > 0)
                                                                <div class="mb-4">
                                                                    <h6 class="mb-3">الروابط المرسلة</h6>
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

                                                            <!-- Submitted Files -->
                                                            @if($submission->submitted_files && is_array($submission->submitted_files) && count($submission->submitted_files) > 0)
                                                                <div class="mb-4">
                                                                    <h6 class="mb-3">الملفات المرسلة</h6>
                                                                    <div class="row g-2">
                                                                        @foreach($submission->submitted_files as $file)
                                                                            <div class="col-md-6">
                                                                                <div class="border rounded p-2">
                                                                                    <i class="fas fa-file-{{ $file['type'] ?? 'alt' }} me-2"></i>
                                                                                    {{ $file['name'] }}
                                                                                    <br>
                                                                                    <small class="text-muted">{{ number_format($file['size'] / 1024, 2) }} KB</small>
                                                                                    <a href="{{ Storage::url($file['path']) }}" target="_blank" class="btn btn-sm btn-info float-end">
                                                                                        <i class="fas fa-download"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            <!-- Current Grade -->
                                                            @if($submission->grade !== null)
                                                                <div class="alert alert-success mb-4">
                                                                    <h6 class="mb-2">التقييم الحالي</h6>
                                                                    <p class="mb-1"><strong>الدرجة:</strong> {{ $submission->grade }} / {{ $assignment->max_grade }}</p>
                                                                    @if($submission->is_late && $assignment->late_penalty_percentage > 0)
                                                                        <p class="mb-1 text-danger">
                                                                            <strong>بعد خصم التأخير ({{ $assignment->late_penalty_percentage }}%):</strong>
                                                                            {{ $submission->getFinalGrade() }} / {{ $assignment->max_grade }}
                                                                        </p>
                                                                    @endif
                                                                    @if($submission->feedback)
                                                                        <p class="mb-1"><strong>الملاحظات:</strong></p>
                                                                        <p class="mb-0">{{ $submission->feedback }}</p>
                                                                    @endif
                                                                    <small class="text-muted">
                                                                        تم التقييم بواسطة {{ $submission->grader->name ?? 'غير محدد' }}
                                                                        في {{ $submission->graded_at ? $submission->graded_at->format('Y-m-d H:i') : '-' }}
                                                                    </small>
                                                                </div>
                                                            @endif

                                                            <!-- Grading Form -->
                                                            @if($submission->status !== 'draft')
                                                                <div class="card bg-light mb-3">
                                                                    <div class="card-body">
                                                                        <h6 class="mb-3">{{ $submission->grade !== null ? 'تعديل التقييم' : 'إضافة تقييم' }}</h6>
                                                                        <form action="{{ route('submissions.grade', $submission->id) }}" method="POST">
                                                                            @csrf
                                                                            <div class="mb-3">
                                                                                <label class="form-label">الدرجة (من {{ $assignment->max_grade }})</label>
                                                                                <input type="number" name="grade" class="form-control"
                                                                                       value="{{ $submission->grade }}"
                                                                                       min="0" max="{{ $assignment->max_grade }}"
                                                                                       step="0.01" required>
                                                                            </div>
                                                                            <div class="mb-3">
                                                                                <label class="form-label">الملاحظات</label>
                                                                                <textarea name="feedback" class="form-control" rows="3">{{ $submission->feedback }}</textarea>
                                                                            </div>
                                                                            <button type="submit" class="btn btn-success">
                                                                                <i class="fas fa-check me-2"></i>حفظ التقييم
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                </div>

                                                                <!-- Allow Resubmission -->
                                                                @if($assignment->allow_resubmission && $submission->status === 'graded')
                                                                    <div class="card border-info">
                                                                        <div class="card-body">
                                                                            <h6 class="mb-3 text-info">
                                                                                <i class="fas fa-redo me-2"></i>إدارة إعادة التسليم
                                                                            </h6>
                                                                            <p class="text-muted small mb-3">
                                                                                يمكنك منح الطالب محاولة إضافية لإعادة تسليم الواجب بعد مراجعة التقييم والملاحظات.
                                                                            </p>

                                                                            @php
                                                                                $studentId = $submission->student_id;
                                                                                $canResubmit = $assignment->canResubmit($studentId);
                                                                                $remaining = $assignment->getRemainingResubmissions($studentId);
                                                                                $totalAttempts = $assignment->submissions()
                                                                                    ->where('student_id', $studentId)
                                                                                    ->count();
                                                                                $extraAttempts = $assignment->getExtraAttemptsForStudent($studentId);
                                                                            @endphp

                                                                            <div class="mb-3">
                                                                                <small class="d-block mb-1">
                                                                                    <strong>عدد المحاولات الحالية:</strong> {{ $totalAttempts }}
                                                                                </small>
                                                                                @if($assignment->max_resubmissions !== null)
                                                                                    <small class="d-block mb-1">
                                                                                        <strong>الحد الأقصى الأساسي:</strong> {{ $assignment->max_resubmissions + 1 }}
                                                                                    </small>
                                                                                    @if($extraAttempts > 0)
                                                                                        <small class="d-block mb-1 text-info">
                                                                                            <strong>محاولات إضافية ممنوحة:</strong> {{ $extraAttempts }}
                                                                                            <i class="fas fa-gift ms-1"></i>
                                                                                        </small>
                                                                                        <small class="d-block mb-1">
                                                                                            <strong>الإجمالي المسموح:</strong> {{ $assignment->max_resubmissions + 1 + $extraAttempts }}
                                                                                        </small>
                                                                                    @endif
                                                                                    <small class="d-block">
                                                                                        <strong>المحاولات المتبقية:</strong>
                                                                                        @if($canResubmit)
                                                                                            <span class="text-success">{{ $remaining ?? 'غير محدود' }}</span>
                                                                                        @else
                                                                                            <span class="text-danger">0 (استُنفدت جميع المحاولات)</span>
                                                                                        @endif
                                                                                    </small>
                                                                                @else
                                                                                    <small class="d-block">
                                                                                        <strong>المحاولات المتبقية:</strong> <span class="text-success">غير محدود</span>
                                                                                    </small>
                                                                                @endif
                                                                            </div>

                                                                            @if($canResubmit)
                                                                                <div class="alert alert-success alert-sm">
                                                                                    <i class="fas fa-check-circle me-2"></i>
                                                                                    الطالب يمكنه إعادة التسليم حالياً
                                                                                </div>
                                                                            @else
                                                                                @if($assignment->max_resubmissions !== null)
                                                                                    <form action="{{ route('submissions.grant-resubmission', $submission->id) }}"
                                                                                          method="POST"
                                                                                          onsubmit="return confirm('هل أنت متأكد من منح الطالب محاولة إضافية؟')">
                                                                                        @csrf
                                                                                        <button type="submit" class="btn btn-info btn-sm">
                                                                                            <i class="fas fa-plus-circle me-2"></i>
                                                                                            منح محاولة إضافية
                                                                                        </button>
                                                                                        <small class="d-block text-muted mt-2">
                                                                                            سيتم زيادة الحد الأقصى للمحاولات المسموحة بمقدار واحد
                                                                                        </small>
                                                                                    </form>
                                                                                @else
                                                                                    <div class="alert alert-warning alert-sm">
                                                                                        <i class="fas fa-info-circle me-2"></i>
                                                                                        إعادة التسليم مسموحة بدون حد أقصى
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-4">
                                                    <i class="fas fa-inbox fs-48 text-muted mb-3"></i>
                                                    <p class="text-muted">لا توجد تسليمات بعد</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @if($submissions->hasPages())
                            <div class="card-footer">
                                {{ $submissions->links() }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Statistics Sidebar -->
                <div class="col-lg-4">
                    <!-- Quick Stats -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-chart-bar me-2"></i>إحصائيات سريعة
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>إجمالي التسليمات</span>
                                    <span class="badge bg-primary">{{ $stats['total_submissions'] }}</span>
                                </div>
                                <div class="progress mb-3" style="height: 6px;">
                                    <div class="progress-bar bg-primary" style="width: 100%"></div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>تم التقييم</span>
                                    <span class="badge bg-success">{{ $stats['graded'] }}</span>
                                </div>
                                <div class="progress mb-3" style="height: 6px;">
                                    <div class="progress-bar bg-success"
                                         style="width: {{ $stats['total_submissions'] > 0 ? ($stats['graded'] / $stats['total_submissions']) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>قيد الانتظار</span>
                                    <span class="badge bg-warning">{{ $stats['pending'] }}</span>
                                </div>
                                <div class="progress mb-3" style="height: 6px;">
                                    <div class="progress-bar bg-warning"
                                         style="width: {{ $stats['total_submissions'] > 0 ? ($stats['pending'] / $stats['total_submissions']) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>مسودات</span>
                                    <span class="badge bg-secondary">{{ $stats['draft'] }}</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-secondary"
                                         style="width: {{ $stats['total_submissions'] > 0 ? ($stats['draft'] / $stats['total_submissions']) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>

                            @if($stats['average_grade'])
                                <hr>
                                <div class="text-center">
                                    <p class="mb-1"><strong>متوسط الدرجات</strong></p>
                                    <h3 class="text-primary">{{ number_format($stats['average_grade'], 2) }}</h3>
                                    <small class="text-muted">من {{ $assignment->max_grade }}</small>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Assignment Meta -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-info-circle me-2"></i>معلومات إضافية
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <p class="mb-1"><strong>أنشئ بواسطة:</strong></p>
                                <p class="text-muted mb-0">{{ $assignment->creator->name ?? 'غير محدد' }}</p>
                            </div>
                            <div class="mb-3">
                                <p class="mb-1"><strong>تاريخ الإنشاء:</strong></p>
                                <p class="text-muted mb-0">{{ $assignment->created_at->format('Y-m-d H:i') }}</p>
                            </div>
                            @if($assignment->updated_at != $assignment->created_at)
                                <div class="mb-3">
                                    <p class="mb-1"><strong>آخر تحديث:</strong></p>
                                    <p class="text-muted mb-0">{{ $assignment->updated_at->format('Y-m-d H:i') }}</p>
                                </div>
                            @endif
                            <div>
                                <p class="mb-1"><strong>ترتيب العرض:</strong></p>
                                <p class="text-muted mb-0">{{ $assignment->sort_order }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop
