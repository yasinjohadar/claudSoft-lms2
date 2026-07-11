@php
    $student = $membershipRequest->student;
    $statusBadge = match ($membershipRequest->status) {
        'pending' => ['قيد المراجعة', 'warning', 'fe-clock'],
        'approved' => ['مقبول', 'success', 'fe-check-circle'],
        'rejected' => ['مرفوض', 'danger', 'fe-x-circle'],
        default => [$membershipRequest->status, 'secondary', 'fe-help-circle'],
    };
@endphp

<div class="membership-request-detail">
    <div class="membership-request-detail__hero mb-4">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="badge bg-{{ $statusBadge[1] }}-transparent text-{{ $statusBadge[1] }}">
                        <i class="fe {{ $statusBadge[2] }} me-1"></i>{{ $statusBadge[0] }}
                    </span>
                    <span class="group-show-chip group-show-chip--sm">{{ $group->name }}</span>
                    <span class="text-muted small">#{{ $membershipRequest->id }}</span>
                </div>
                <h5 class="mb-1 fw-semibold">{{ $student->name ?? 'طالب' }}</h5>
                @if($student?->name_ar)
                    <p class="text-muted mb-0 small">{{ $student->name_ar }}</p>
                @endif
            </div>
            <div class="text-muted small text-end">
                <div><i class="fe fe-calendar me-1"></i>{{ $membershipRequest->created_at->format('Y-m-d H:i') }}</div>
                @if($membershipRequest->payment_date)
                    <div class="mt-1">
                        <i class="fe fe-dollar-sign me-1"></i>تسديد الرسوم: {{ $membershipRequest->payment_date->format('Y-m-d') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="admin-profile-detail-field">
                <span class="admin-profile-detail-field__icon"><i class="fe fe-mail"></i></span>
                <div class="min-w-0">
                    <span class="admin-profile-detail-field__label">البريد الإلكتروني</span>
                    <span class="admin-profile-detail-field__value text-break">{{ $student->email ?? '—' }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-profile-detail-field">
                <span class="admin-profile-detail-field__icon"><i class="fe fe-phone"></i></span>
                <div class="min-w-0">
                    <span class="admin-profile-detail-field__label">الهاتف</span>
                    <span class="admin-profile-detail-field__value">
                        @if($student?->phone)
                            {{ $student->phone }}
                            @if($student->country_code)
                                <small class="text-muted">({{ $student->country_code }})</small>
                            @endif
                        @else
                            —
                        @endif
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-profile-detail-field">
                <span class="admin-profile-detail-field__icon"><i class="fe fe-user"></i></span>
                <div class="min-w-0">
                    <span class="admin-profile-detail-field__label">ملف الطالب</span>
                    <span class="admin-profile-detail-field__value">
                        @if($student)
                            <a href="{{ route('users.show', $student->id) }}" class="text-primary" target="_blank" rel="noopener">
                                عرض الملف <i class="fe fe-external-link ms-1"></i>
                            </a>
                        @else
                            —
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    @if($otherGroups->isNotEmpty())
        <div class="mb-4">
            <small class="text-muted d-block mb-2">مجموعات أخرى</small>
            <div class="d-flex flex-wrap gap-1">
                @foreach($otherGroups as $otherGroup)
                    @php $ogCourse = $otherGroup->courses->first(); @endphp
                    @if($ogCourse)
                        <a href="{{ route('courses.groups.show', [$ogCourse->id, $otherGroup->id]) }}"
                           class="group-show-chip group-show-chip--sm text-decoration-none" target="_blank" rel="noopener">
                            {{ $otherGroup->name }}
                        </a>
                    @else
                        <span class="group-show-chip group-show-chip--sm">{{ $otherGroup->name }}</span>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    @if($membershipRequest->message)
        <div class="alert alert-light border mb-4">
            <small class="text-muted d-block mb-1">رسالة الطلب</small>
            <p class="mb-0">{{ $membershipRequest->message }}</p>
        </div>
    @endif

    @if($registration)
        <div class="membership-request-detail__form-data">
            @include('admin.course-groups.partials.membership-request-form-data', ['registration' => $registration])
        </div>
    @else
        <div class="group-show-empty py-4 mb-4">
            <i class="fe fe-file-text group-show-empty__icon"></i>
            <h6 class="group-show-empty__title">لا توجد بيانات فورم مرتبطة</h6>
            <p class="text-muted mb-0 small">لم يُعثر على تسجيل فورم لهذا الطلب في هذه المجموعة.</p>
        </div>
    @endif

    @if($membershipRequest->admin_notes)
        <div class="alert alert-warning py-2 small mb-0">
            <strong>ملاحظات الإدارة:</strong> {{ $membershipRequest->admin_notes }}
        </div>
    @endif

    @if($membershipRequest->approved_at || $membershipRequest->rejected_at)
        <div class="mt-3 small text-muted">
            @if($membershipRequest->approved_at)
                <div><i class="fe fe-check me-1"></i>قُبل {{ $membershipRequest->approved_at->format('Y-m-d H:i') }}
                    @if($membershipRequest->approver) — {{ $membershipRequest->approver->name }} @endif
                </div>
            @endif
            @if($membershipRequest->rejected_at)
                <div><i class="fe fe-x me-1"></i>رُفض {{ $membershipRequest->rejected_at->format('Y-m-d H:i') }}
                    @if($membershipRequest->rejecter) — {{ $membershipRequest->rejecter->name }} @endif
                </div>
            @endif
        </div>
    @endif
</div>

<div class="membership-request-detail__actions d-flex flex-wrap gap-2 justify-content-between align-items-center pt-3 mt-3 border-top">
    <div class="d-flex flex-wrap gap-2">
        @if($membershipRequest->status === 'pending')
            <form action="{{ route('courses.groups.membership-requests.approve', [$course->id, $group->id, $membershipRequest->id]) }}"
                  method="POST" class="d-inline"
                  onsubmit="return confirm('هل أنت متأكد من قبول طلب الانضمام؟');">
                @csrf
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="fe fe-check me-1"></i>قبول
                </button>
            </form>
            <button type="button"
                    class="btn btn-danger btn-sm js-open-membership-reject js-membership-detail-reject"
                    data-reject-url="{{ route('courses.groups.membership-requests.reject', [$course->id, $group->id, $membershipRequest->id]) }}"
                    data-student-name="{{ $membershipRequest->student->name ?? 'الطالب' }}">
                <i class="fe fe-x me-1"></i>رفض
            </button>
        @elseif($membershipRequest->status === 'rejected')
            <form action="{{ route('courses.groups.membership-requests.approve', [$course->id, $group->id, $membershipRequest->id]) }}"
                  method="POST" class="d-inline"
                  onsubmit="return confirm('هل أنت متأكد من قبول هذا الطلب؟');">
                @csrf
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="fe fe-check me-1"></i>قبول
                </button>
            </form>
        @elseif($membershipRequest->status === 'approved')
            <button type="button"
                    class="btn btn-outline-danger btn-sm js-open-membership-reject js-membership-detail-reject"
                    data-reject-url="{{ route('courses.groups.membership-requests.reject', [$course->id, $group->id, $membershipRequest->id]) }}"
                    data-student-name="{{ $membershipRequest->student->name ?? 'الطالب' }}">
                <i class="fe fe-x me-1"></i>رفض
            </button>
        @endif
    </div>
    <div class="d-flex flex-wrap gap-2">
        @if($registration)
            <a href="{{ route('admin.group-registrations.show', $registration->id) }}"
               class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener">
                <i class="fe fe-external-link me-1"></i>سجل التسجيل الكامل
            </a>
        @endif
        <a href="{{ route('courses.groups.membership-requests.show', [$course->id, $group->id, $membershipRequest->id]) }}"
           class="btn btn-outline-secondary btn-sm">
            <i class="fe fe-maximize-2 me-1"></i>صفحة المراجعة الكاملة
        </a>
    </div>
</div>
