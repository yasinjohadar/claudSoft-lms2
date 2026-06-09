<div class="table-responsive student-group-requests-table-wrap">
    <table class="table table-hover align-middle student-group-requests-table mb-0">
        <thead>
            <tr>
                <th>#</th>
                <th>المجموعة</th>
                <th>الكورسات</th>
                <th>تاريخ الطلب</th>
                <th>موعد تسديد الرسوم</th>
                <th>الحالة</th>
                <th class="text-end">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $membershipRequest)
                <tr class="student-group-request-row">
                    <td class="text-muted fs-12">{{ $membershipRequest->id }}</td>
                    <td>
                        <div class="fw-semibold">{{ $membershipRequest->group->name }}</div>
                        @if($membershipRequest->message)
                            <div class="text-muted fs-12 mt-1">{{ Str::limit($membershipRequest->message, 60) }}</div>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($membershipRequest->group->courses->take(2) as $course)
                                <span class="badge bg-primary-transparent fs-11">{{ $course->title }}</span>
                            @endforeach
                            @if($membershipRequest->group->courses->count() > 2)
                                <span class="badge bg-secondary-transparent fs-11">+{{ $membershipRequest->group->courses->count() - 2 }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="fs-12 text-nowrap">{{ $membershipRequest->created_at->format('Y-m-d H:i') }}</td>
                    <td class="fs-12 text-nowrap">
                        @if($membershipRequest->payment_date)
                            {{ $membershipRequest->payment_date->format('Y-m-d') }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($membershipRequest->status === 'pending')
                            <span class="badge bg-warning-transparent">
                                <i class="fe fe-clock me-1"></i>قيد المراجعة
                            </span>
                        @elseif($membershipRequest->status === 'approved')
                            <span class="badge bg-success-transparent">
                                <i class="fe fe-check-circle me-1"></i>مقبول
                            </span>
                            @if($membershipRequest->approved_at)
                                <div class="text-muted fs-11 mt-1">{{ $membershipRequest->approved_at->format('Y-m-d') }}</div>
                            @endif
                        @elseif($membershipRequest->status === 'rejected')
                            <span class="badge bg-danger-transparent">
                                <i class="fe fe-x-circle me-1"></i>مرفوض
                            </span>
                            @if($membershipRequest->rejected_at)
                                <div class="text-muted fs-11 mt-1">{{ $membershipRequest->rejected_at->format('Y-m-d') }}</div>
                            @endif
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('student.groups.show', $membershipRequest->group->id) }}"
                           class="btn btn-sm btn-outline-primary rounded-pill">
                            <i class="fe fe-eye me-1"></i>عرض
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="border-0 p-0">
                        <div class="student-my-courses-empty text-center py-5">
                            <div class="student-my-courses-empty__icon mb-4">
                                <i class="fe fe-inbox"></i>
                            </div>
                            <h4 class="mb-2">لا توجد طلبات</h4>
                            <p class="text-muted mb-4">لم تقم بإرسال أي طلبات انضمام للمجموعات بعد.</p>
                            <a href="{{ route('student.groups.index') }}" class="btn btn-primary rounded-pill">
                                <i class="fe fe-users me-1"></i>تصفح المجموعات
                            </a>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($requests->hasPages())
    <div class="d-flex justify-content-center mt-4 pt-2">
        {{ $requests->withQueryString()->links() }}
    </div>
@endif
