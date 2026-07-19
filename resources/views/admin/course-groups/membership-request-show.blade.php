@extends('admin.layouts.master')

@section('page-title')
    مراجعة طلب انضمام — {{ $membershipRequest->student->name ?? 'طالب' }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="my-4 page-header-breadcrumb">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('courses.groups.index', $course->id) }}">المجموعات</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('courses.groups.show', [$course->id, $group->id]) }}">{{ $group->name }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('courses.groups.membership-requests', [$course->id, $group->id]) }}">طلبات الانضمام</a></li>
                        <li class="breadcrumb-item active">مراجعة الطلب #{{ $membershipRequest->id }}</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-clipboard me-1"></i>
                            مراجعة قبل الموافقة
                        </span>
                        <h2 class="group-show-hero__title mb-2">{{ $membershipRequest->student->name ?? 'طالب' }}</h2>
                        @if($membershipRequest->student?->name_ar)
                            <p class="text-muted mb-2">{{ $membershipRequest->student->name_ar }}</p>
                        @endif
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            @if($membershipRequest->status === 'pending')
                                <span class="badge bg-warning-transparent text-warning">
                                    <i class="fe fe-clock me-1"></i>قيد المراجعة
                                </span>
                            @elseif($membershipRequest->status === 'approved')
                                <span class="badge bg-success-transparent text-success">
                                    <i class="fe fe-check-circle me-1"></i>مقبول
                                </span>
                            @else
                                <span class="badge bg-danger-transparent text-danger">
                                    <i class="fe fe-x-circle me-1"></i>مرفوض
                                </span>
                            @endif
                            <span class="group-show-chip group-show-chip--sm">{{ $group->name }}</span>
                            <span class="text-muted small">
                                <i class="fe fe-calendar me-1"></i>{{ $membershipRequest->created_at->format('Y-m-d H:i') }}
                            </span>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions group-show-actions--single">
                            <a href="{{ route('courses.groups.membership-requests', [$course->id, $group->id]) }}"
                               class="group-show-action group-show-action--info">
                                <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                                <span class="group-show-action__text">رجوع للطلبات</span>
                            </a>
                            @if($membershipRequest->student)
                                <a href="{{ route('users.show', $membershipRequest->student_id) }}"
                                   class="group-show-action group-show-action--primary">
                                    <span class="group-show-action__icon"><i class="fe fe-user"></i></span>
                                    <span class="group-show-action__text">ملف الطالب</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-xl-8">
                    @include('admin.course-groups.partials.membership-request-camp-receipt', [
                        'membershipRequest' => $membershipRequest,
                        'group' => $group,
                        'course' => $course,
                    ])

                    @if($registration)
                        @include('admin.course-groups.partials.membership-request-form-data', ['registration' => $registration])
                    @else
                        <div class="card custom-card group-show-members-card">
                            <div class="card-body">
                                <div class="group-show-empty py-4">
                                    <i class="fe fe-file-text group-show-empty__icon"></i>
                                    <h5 class="group-show-empty__title">لا توجد بيانات فورم مرتبطة</h5>
                                    <p class="text-muted mb-0">
                                        لم يُعثر على تسجيل فورم خارجي لهذا الطلب في هذه المجموعة.
                                        يمكنك مراجعة بيانات الحساب من الشريط الجانبي.
                                    </p>
                                </div>
                            </div>
                        </div>

                        @if($membershipRequest->student)
                            <div class="card custom-card group-show-members-card mt-4">
                                <div class="card-header border-0 pb-0">
                                    <h6 class="group-show-members-card__title mb-1">بيانات الحساب</h6>
                                </div>
                                <div class="card-body pt-3">
                                    <div class="row g-3">
                                        @php $s = $membershipRequest->student; @endphp
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">البريد</small>
                                            <strong>{{ $s->email ?? '—' }}</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">الهاتف</small>
                                            <strong>{{ $s->phone ?? '—' }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    @if($membershipRequest->message)
                        <div class="card custom-card group-show-members-card mt-4">
                            <div class="card-header border-0 pb-0">
                                <h6 class="group-show-members-card__title mb-1">رسالة الطلب</h6>
                            </div>
                            <div class="card-body pt-3">
                                <p class="mb-0">{{ $membershipRequest->message }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-xl-4">
                    <div class="card custom-card group-show-members-card admin-course-form-page__sidebar mb-4">
                        <div class="card-header border-0 pb-0">
                            <h6 class="group-show-members-card__title mb-1">ملخص الطلب</h6>
                        </div>
                        <div class="card-body pt-3">
                            <div class="admin-profile-detail-field mb-3">
                                <span class="admin-profile-detail-field__icon"><i class="fe fe-hash"></i></span>
                                <div>
                                    <span class="admin-profile-detail-field__label">رقم الطلب</span>
                                    <span class="admin-profile-detail-field__value">#{{ $membershipRequest->id }}</span>
                                </div>
                            </div>
                            @if($membershipRequest->payment_date)
                                <div class="admin-profile-detail-field mb-3">
                                    <span class="admin-profile-detail-field__icon"><i class="fe fe-dollar-sign"></i></span>
                                    <div>
                                        <span class="admin-profile-detail-field__label">موعد تسديد الرسوم</span>
                                        <span class="admin-profile-detail-field__value">{{ $membershipRequest->payment_date->format('Y-m-d') }}</span>
                                    </div>
                                </div>
                            @endif
                            @if($membershipRequest->terms_accepted)
                                <div class="mb-3">
                                    <span class="group-show-chip group-show-chip--sm text-success">
                                        <i class="fe fe-check me-1"></i>وافق على الشروط
                                    </span>
                                </div>
                            @endif
                            @if($membershipRequest->hasReceipt())
                                <div class="mb-3">
                                    <span class="group-show-chip group-show-chip--sm text-warning">
                                        <i class="fe fe-paperclip me-1"></i>إيصال دفع مرفق
                                    </span>
                                    <div class="mt-2">
                                        <a href="{{ route('courses.groups.membership-requests.receipt', [$course->id, $group->id, $membershipRequest->id]) }}"
                                           target="_blank" rel="noopener" class="btn btn-sm btn-outline-warning w-100">
                                            <i class="fe fe-eye me-1"></i>عرض إيصال المعسكر
                                        </a>
                                    </div>
                                </div>
                            @endif
                            @if($membershipRequest->approved_at)
                                <div class="small text-muted mb-2">
                                    <i class="fe fe-check me-1"></i>
                                    قُبل في {{ $membershipRequest->approved_at->format('Y-m-d H:i') }}
                                    @if($membershipRequest->approver)
                                        — {{ $membershipRequest->approver->name }}
                                    @endif
                                </div>
                            @endif
                            @if($membershipRequest->rejected_at)
                                <div class="small text-muted mb-2">
                                    <i class="fe fe-x me-1"></i>
                                    رُفض في {{ $membershipRequest->rejected_at->format('Y-m-d H:i') }}
                                    @if($membershipRequest->rejecter)
                                        — {{ $membershipRequest->rejecter->name }}
                                    @endif
                                </div>
                            @endif
                            @if($membershipRequest->admin_notes)
                                <div class="alert alert-warning py-2 mb-0 small">
                                    <strong>ملاحظات الإدارة:</strong> {{ $membershipRequest->admin_notes }}
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($otherGroups->isNotEmpty())
                        <div class="card custom-card group-show-members-card mb-4">
                            <div class="card-header border-0 pb-0">
                                <h6 class="group-show-members-card__title mb-1">مجموعات أخرى</h6>
                            </div>
                            <div class="card-body pt-3">
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($otherGroups as $otherGroup)
                                        @php $ogCourse = $otherGroup->courses->first(); @endphp
                                        @if($ogCourse)
                                            <a href="{{ route('courses.groups.show', [$ogCourse->id, $otherGroup->id]) }}"
                                               class="group-show-chip group-show-chip--sm text-decoration-none">
                                                {{ $otherGroup->name }}
                                            </a>
                                        @else
                                            <span class="group-show-chip group-show-chip--sm">{{ $otherGroup->name }}</span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="card custom-card group-show-members-card">
                        <div class="card-header border-0 pb-0">
                            <h6 class="group-show-members-card__title mb-1">الإجراءات</h6>
                            <p class="fs-12 text-muted mb-0">راجع البيانات ثم اتخذ القرار.</p>
                        </div>
                        <div class="card-body pt-3">
                            <div class="admin-course-form-page__actions mt-0 pt-0 border-0">
                                @if($membershipRequest->status === 'pending')
                                    <form action="{{ route('courses.groups.membership-requests.approve', [$course->id, $group->id, $membershipRequest->id]) }}"
                                          method="POST"
                                          onsubmit="return confirm('هل أنت متأكد من قبول طلب الانضمام وإضافة الطالب للمجموعة؟');">
                                        @csrf
                                        <button type="submit" class="btn btn-success w-100">
                                            <i class="fe fe-check me-1"></i>قبول الطلب
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectRequestModal">
                                        <i class="fe fe-x me-1"></i>رفض الطلب
                                    </button>
                                @elseif($membershipRequest->status === 'rejected')
                                    <form action="{{ route('courses.groups.membership-requests.approve', [$course->id, $group->id, $membershipRequest->id]) }}"
                                          method="POST"
                                          onsubmit="return confirm('هل أنت متأكد من قبول هذا الطلب المرفوض؟');">
                                        @csrf
                                        <button type="submit" class="btn btn-success w-100">
                                            <i class="fe fe-check me-1"></i>قبول الطلب
                                        </button>
                                    </form>
                                @elseif($membershipRequest->status === 'approved')
                                    <div class="alert alert-success py-2 small mb-3">
                                        <i class="fe fe-check-circle me-1"></i>تم قبول هذا الطلب وإضافة الطالب للمجموعة.
                                    </div>
                                    <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectRequestModal">
                                        <i class="fe fe-x me-1"></i>رفض الطلب
                                    </button>
                                @endif

                                @if($registration)
                                    <a href="{{ route('admin.group-registrations.show', $registration->id) }}"
                                       class="btn btn-outline-primary w-100">
                                        <i class="fe fe-external-link me-1"></i>سجل التسجيل الكامل
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="rejectRequestModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form action="{{ route('courses.groups.membership-requests.reject', [$course->id, $group->id, $membershipRequest->id]) }}" method="POST">
                    @csrf
                    <div class="modal-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <div class="avatar avatar-xl bg-danger-transparent mx-auto mb-3">
                                <i class="fe fe-x-circle text-danger fs-24"></i>
                            </div>
                            <h5 class="mb-2 fw-bold">رفض طلب الانضمام</h5>
                            <p class="text-muted mb-0">
                                هل أنت متأكد من رفض طلب <strong>{{ $membershipRequest->student->name ?? 'الطالب' }}</strong>؟
                            </p>
                        </div>
                        <div class="mb-4 text-start">
                            <label class="form-label">ملاحظات (اختياري)</label>
                            <textarea name="admin_notes" class="form-control" rows="3"
                                      placeholder="سبب الرفض أو ملاحظات للإدارة..."></textarea>
                        </div>
                        <div class="d-flex justify-content-center gap-2">
                            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-danger px-4">
                                <i class="fe fe-x me-1"></i>رفض الطلب
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
