@php
    $currentStatus = request('status', '');
    $statusFilters = [
        '' => 'الكل',
        'pending' => 'قيد المراجعة',
        'approved' => 'مقبول',
        'rejected' => 'مرفوض',
    ];
@endphp

<div class="card custom-card student-quizzes-filters-panel mb-4">
    <div class="card-header border-0 pb-0">
        <div class="d-flex align-items-center gap-2">
            <span class="avatar avatar-sm bg-primary-transparent">
                <i class="fe fe-filter text-primary"></i>
            </span>
            <div>
                <h6 class="card-title mb-0">تصفية الطلبات</h6>
                <p class="text-muted fs-12 mb-0">اعرض طلباتك حسب الحالة</p>
            </div>
        </div>
    </div>
    <div class="card-body pt-3">
        <div class="student-my-courses-filters mb-3">
            @foreach($statusFilters as $value => $label)
                <a href="{{ route('student.groups.my-requests', $value !== '' ? ['status' => $value] : []) }}"
                   class="student-my-courses-filter {{ $currentStatus === $value ? 'is-active' : '' }}">
                    <i class="fe fe-{{ $value === 'pending' ? 'clock' : ($value === 'approved' ? 'check-circle' : ($value === 'rejected' ? 'x-circle' : 'layers')) }}"></i>
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('student.groups.my-requests') }}" class="row g-3 align-items-end">
            <div class="col-lg-4 col-md-6">
                <label class="form-label fs-12 fw-semibold">الحالة</label>
                <select name="status" class="form-select">
                    <option value="">جميع الحالات</option>
                    <option value="pending" {{ $currentStatus === 'pending' ? 'selected' : '' }}>قيد المراجعة</option>
                    <option value="approved" {{ $currentStatus === 'approved' ? 'selected' : '' }}>مقبول</option>
                    <option value="rejected" {{ $currentStatus === 'rejected' ? 'selected' : '' }}>مرفوض</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-6 d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-pill flex-fill">
                    <i class="fe fe-filter me-1"></i>تطبيق
                </button>
                <a href="{{ route('student.groups.my-requests') }}" class="btn btn-outline-secondary rounded-pill">
                    <i class="fe fe-rotate-ccw"></i>
                </a>
            </div>
        </form>
    </div>
</div>
