@extends('admin.layouts.master')

@section('page-title')
    عرض الواجب
@stop

@section('styles')
    @include('admin.pages.assignments.partials.page-styles')
@stop

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="my-4 page-header-breadcrumb assignments-page-animate dashboard-fade-in">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('assignments.index') }}">الواجبات</a></li>
                        <li class="breadcrumb-item active">عرض الواجب</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in assignments-page-animate mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow"><i class="fe fe-clipboard me-1"></i>تفاصيل الواجب</span>
                        <h2 class="group-show-hero__title mb-2">{{ $assignment->title }}</h2>
                        <p class="group-show-hero__desc mb-0">
                            @if($assignment->course)
                                {{ $assignment->course->title }}
                                @if($assignment->lesson) · {{ $assignment->lesson->title }} @endif
                            @else
                                متابعة التسليمات وتقييم أعمال الطلاب.
                            @endif
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions">
                            <a href="{{ route('assignments.edit', $assignment->id) }}" class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-edit-2"></i></span>
                                <span class="group-show-action__text">تعديل الواجب</span>
                            </a>
                            <a href="{{ route('assignments.index') }}" class="group-show-action">
                                <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                                <span class="group-show-action__text">العودة للقائمة</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card custom-card group-show-members-card dashboard-fade-in assignments-page-animate mb-4">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                                <span class="assignments-section-icon"><i class="fe fe-info"></i></span>
                                تفاصيل الواجب
                            </h4>
                        </div>
                        <div class="card-body pt-3">
                            <div class="assignments-info-grid mb-3">
                                <div class="assignments-info-item">
                                    <div class="assignments-info-item__label">الكورس</div>
                                    <div class="assignments-info-item__value">
                                        @if($assignment->course)
                                            <span class="assignments-course-chip">{{ $assignment->course->title }}</span>
                                        @else
                                            <span class="text-muted">غير محدد</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="assignments-info-item">
                                    <div class="assignments-info-item__label">المجموعة المستهدفة</div>
                                    <div class="assignments-info-item__value">
                                        @if($assignment->targetGroup)
                                            <span class="badge bg-info-transparent text-info">{{ $assignment->targetGroup->name }}</span>
                                        @else
                                            <span class="text-muted">كل طلاب الكورس</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="assignments-info-item">
                                    <div class="assignments-info-item__label">الدرس</div>
                                    <div class="assignments-info-item__value">
                                        @if($assignment->lesson)
                                            <span class="assignments-lesson-chip">{{ $assignment->lesson->title }}</span>
                                        @else
                                            <span class="text-muted">غير محدد</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="assignments-info-item">
                                    <div class="assignments-info-item__label">الدرجة القصوى</div>
                                    <div class="assignments-info-item__value"><span class="assignments-grade-chip">{{ $assignment->max_grade }}</span></div>
                                </div>
                                <div class="assignments-info-item">
                                    <div class="assignments-info-item__label">نوع التسليم</div>
                                    <div class="assignments-info-item__value">
                                        @if($assignment->submission_type === 'link') روابط فقط
                                        @elseif($assignment->submission_type === 'file') ملفات فقط
                                        @else روابط وملفات @endif
                                    </div>
                                </div>
                                <div class="assignments-info-item">
                                    <div class="assignments-info-item__label">الحالة</div>
                                    <div class="assignments-info-item__value">
                                        @if($assignment->is_published)
                                            <span class="assignments-status-chip assignments-status-chip--published">منشور</span>
                                        @else
                                            <span class="assignments-status-chip assignments-status-chip--draft">مسودة</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($assignment->description)
                                <div class="mb-3">
                                    <p class="mb-2 fw-semibold">الوصف</p>
                                    <div class="text-muted">{!! $assignment->description !!}</div>
                                </div>
                            @endif

                            @if($assignment->instructions)
                                <div class="mb-0">
                                    <p class="mb-2 fw-semibold">التعليمات</p>
                                    <div class="alert alert-info mb-0">{!! $assignment->instructions !!}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card custom-card group-show-members-card dashboard-fade-in assignments-page-animate mb-4">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                                <span class="assignments-section-icon"><i class="fe fe-clock"></i></span>
                                المواعيد النهائية
                            </h4>
                        </div>
                        <div class="card-body pt-3">
                            <div class="assignments-info-grid">
                                <div class="assignments-info-item">
                                    <div class="assignments-info-item__label">متاح من</div>
                                    <div class="assignments-info-item__value">{{ $assignment->available_from ? $assignment->available_from->format('Y-m-d H:i') : 'غير محدد' }}</div>
                                </div>
                                <div class="assignments-info-item">
                                    <div class="assignments-info-item__label">موعد التسليم</div>
                                    <div class="assignments-info-item__value">{{ $assignment->due_date ? $assignment->due_date->format('Y-m-d H:i') : 'غير محدد' }}</div>
                                </div>
                                <div class="assignments-info-item">
                                    <div class="assignments-info-item__label">التسليم المتأخر حتى</div>
                                    <div class="assignments-info-item__value">{{ $assignment->late_submission_until ? $assignment->late_submission_until->format('Y-m-d H:i') : 'غير محدد' }}</div>
                                </div>
                            </div>

                            @if($assignment->allow_late_submission)
                                <div class="alert alert-warning mt-3 mb-0">
                                    <i class="fe fe-alert-triangle me-2"></i>
                                    السماح بالتسليم المتأخر مع خصم {{ $assignment->late_penalty_percentage }}%
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Resubmission Settings -->
                    @if($assignment->allow_resubmission)
                        <div class="card custom-card group-show-members-card dashboard-fade-in assignments-page-animate mb-4">
                            <div class="card-header border-0 pb-0">
                                <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                                    <span class="assignments-section-icon"><i class="fe fe-rotate-cw"></i></span>
                                    إعدادات إعادة التسليم
                                </h4>
                            </div>
                            <div class="card-body pt-3">
                                <div class="alert alert-info mb-0">
                                    <i class="fe fe-info me-2"></i>
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
                        <div class="card custom-card group-show-members-card dashboard-fade-in assignments-page-animate mb-4">
                            <div class="card-header border-0 pb-0">
                                <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                                    <span class="assignments-section-icon"><i class="fe fe-paperclip"></i></span>
                                    المرفقات
                                </h4>
                            </div>
                            <div class="card-body pt-3">
                                <div class="row g-3">
                                    @foreach($assignment->attachments as $attachment)
                                        <div class="col-md-6">
                                            <div class="assignments-attachment-card">
                                                <i class="fe fe-file me-2 text-primary"></i>
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

                    <div class="card custom-card group-show-members-card dashboard-fade-in assignments-page-animate">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                            <h6 class="group-show-members-card__title mb-0">
                                التسليمات
                                <span class="group-show-members-card__count">{{ $submissions->total() }}</span>
                            </h6>
                        </div>
                        <div class="card-body pt-3 p-0">
                            <div class="table-responsive px-3 pb-3">
                                <table class="table table-hover text-nowrap dashboard-table mb-0">
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
                                                <td><span class="badge bg-info-transparent">#{{ $submission->attempt_number }}</span></td>
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
                                                        <span class="assignments-status-chip assignments-status-chip--graded">تم التقييم</span>
                                                    @elseif($submission->status === 'submitted')
                                                        <span class="assignments-status-chip assignments-status-chip--pending">قيد الانتظار</span>
                                                    @elseif($submission->status === 'draft')
                                                        <span class="assignments-status-chip assignments-status-chip--submission-draft">مسودة</span>
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
                                                    <button type="button" class="btn btn-primary-light btn-sm assignments-actions__btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#submissionModal{{ $submission->id }}">
                                                        <i class="fe fe-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-5">
                                                    <span class="assignments-empty-state__icon d-inline-flex"><i class="fe fe-inbox"></i></span>
                                                    <p class="text-muted mb-0">لا توجد تسليمات بعد</p>
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

                <div class="col-lg-4">
                    <div class="card custom-card group-show-members-card dashboard-fade-in assignments-page-animate mb-4">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                                <span class="assignments-section-icon"><i class="fe fe-bar-chart-2"></i></span>
                                إحصائيات سريعة
                            </h4>
                        </div>
                        <div class="card-body pt-3">
                            @php
                                $totalSubs = $stats['total_submissions'] ?? 0;
                            @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>إجمالي التسليمات</span>
                                    <span class="badge bg-primary">{{ $totalSubs }}</span>
                                </div>
                                <div class="assignments-stat-progress">
                                    <div class="progress-bar bg-primary" style="width: {{ $totalSubs > 0 ? 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>تم التقييم</span>
                                    <span class="badge bg-success">{{ $stats['graded'] ?? 0 }}</span>
                                </div>
                                <div class="assignments-stat-progress">
                                    <div class="progress-bar bg-success" style="width: {{ $totalSubs > 0 ? (($stats['graded'] ?? 0) / $totalSubs) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>قيد الانتظار</span>
                                    <span class="badge bg-warning">{{ $stats['pending'] ?? 0 }}</span>
                                </div>
                                <div class="assignments-stat-progress">
                                    <div class="progress-bar bg-warning" style="width: {{ $totalSubs > 0 ? (($stats['pending'] ?? 0) / $totalSubs) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>مسودات</span>
                                    <span class="badge bg-secondary">{{ $stats['draft'] ?? 0 }}</span>
                                </div>
                                <div class="assignments-stat-progress">
                                    <div class="progress-bar bg-secondary" style="width: {{ $totalSubs > 0 ? (($stats['draft'] ?? 0) / $totalSubs) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            @if($stats['average_grade'])
                                <hr>
                                <div class="text-center">
                                    <p class="mb-1 text-muted">متوسط الدرجات</p>
                                    <h3 class="text-primary mb-0">{{ number_format($stats['average_grade'], 2) }}</h3>
                                    <small class="text-muted">من {{ $assignment->max_grade }}</small>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card custom-card group-show-members-card dashboard-fade-in assignments-page-animate">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                                <span class="assignments-section-icon"><i class="fe fe-info"></i></span>
                                معلومات إضافية
                            </h4>
                        </div>
                        <div class="card-body pt-3">
                            <div class="assignments-info-grid">
                                <div class="assignments-info-item">
                                    <div class="assignments-info-item__label">أنشئ بواسطة</div>
                                    <div class="assignments-info-item__value">{{ $assignment->creator->name ?? 'غير محدد' }}</div>
                                </div>
                                <div class="assignments-info-item">
                                    <div class="assignments-info-item__label">تاريخ الإنشاء</div>
                                    <div class="assignments-info-item__value">{{ $assignment->created_at->format('Y-m-d H:i') }}</div>
                                </div>
                                @if($assignment->updated_at != $assignment->created_at)
                                    <div class="assignments-info-item">
                                        <div class="assignments-info-item__label">آخر تحديث</div>
                                        <div class="assignments-info-item__value">{{ $assignment->updated_at->format('Y-m-d H:i') }}</div>
                                    </div>
                                @endif
                                <div class="assignments-info-item">
                                    <div class="assignments-info-item__label">ترتيب العرض</div>
                                    <div class="assignments-info-item__value">{{ $assignment->sort_order }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @foreach($submissions as $submission)
        @include('admin.pages.assignments.partials._submission_modal', [
            'submission' => $submission,
            'assignment' => $assignment,
        ])
    @endforeach
@stop
