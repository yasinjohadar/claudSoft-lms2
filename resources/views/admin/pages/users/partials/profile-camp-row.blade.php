@php
    $camp = $campEnrollment->camp;
    $displayFee = $campFee ?? (float) ($campEnrollment->invoice?->total_amount ?? $camp?->price ?? 0);
    $updateUrl = route('users.update-camp-enrollment', [$campEnrollment->student_id, $campEnrollment->id]);
@endphp
<tr class="admin-users-table__row profile-camp-row"
    data-enrollment-id="{{ $campEnrollment->id }}"
    data-update-url="{{ $updateUrl }}">
    <td>{{ $rowNumber }}</td>
    <td>
        @if($camp)
            <a href="{{ route('training-camps.show', $camp->id) }}" class="fw-semibold text-decoration-none admin-users-table__name">
                {{ $camp->name }}
            </a>
            @if($camp->location)
                <br><small class="text-muted"><i class="fe fe-map-pin me-1"></i>{{ $camp->location }}</small>
            @endif
        @else
            <span class="text-muted">—</span>
        @endif
    </td>
    <td>
        @if($camp?->category)
            <span class="group-show-chip group-show-chip--sm">{{ $camp->category->name }}</span>
        @else
            <span class="text-muted">—</span>
        @endif
    </td>
    <td><small class="text-muted">{{ optional($campEnrollment->enrollment_date)->format('Y-m-d') ?? '—' }}</small></td>
    <td><span class="fw-semibold">{{ number_format($displayFee, 2) }}</span></td>
    <td class="profile-camp-col-status">
        <div class="profile-camp-status-picker is-{{ $campEnrollment->status }}">
            <span class="profile-camp-status-picker__dot" aria-hidden="true"></span>
            <select class="profile-camp-field-select profile-camp-status-select is-{{ $campEnrollment->status }}"
                    name="status"
                    title="تغيير حالة التسجيل"
                    aria-label="حالة التسجيل">
                <option value="pending" @selected($campEnrollment->status === 'pending')>قيد الانتظار</option>
                <option value="approved" @selected($campEnrollment->status === 'approved')>مقبول</option>
                <option value="rejected" @selected($campEnrollment->status === 'rejected')>مرفوض</option>
                <option value="cancelled" @selected($campEnrollment->status === 'cancelled')>ملغي</option>
            </select>
        </div>
    </td>
    <td class="profile-camp-col-payment">
        <div class="profile-camp-status-picker profile-camp-status-picker--payment is-{{ $campEnrollment->payment_status }}">
            <span class="profile-camp-status-picker__dot" aria-hidden="true"></span>
            <select class="profile-camp-field-select profile-camp-payment-select is-{{ $campEnrollment->payment_status }}"
                    name="payment_status"
                    title="تغيير حالة الدفع"
                    aria-label="حالة الدفع">
                <option value="unpaid" @selected($campEnrollment->payment_status === 'unpaid')>غير مدفوع</option>
                <option value="paid" @selected($campEnrollment->payment_status === 'paid')>مدفوع</option>
                <option value="refunded" @selected($campEnrollment->payment_status === 'refunded')>مسترجع</option>
            </select>
        </div>
    </td>
    <td>
        @if($camp?->start_date)
            <small class="text-muted">
                {{ $camp->start_date->format('Y-m-d') }}
                @if($camp->end_date)
                    — {{ $camp->end_date->format('Y-m-d') }}
                @endif
            </small>
        @else
            <span class="text-muted">—</span>
        @endif
    </td>
    <td>
        <div class="d-flex align-items-center gap-1">
            @if($camp)
                <a href="{{ route('training-camps.enrollments.show', [$camp->id, $campEnrollment->id]) }}"
                   class="btn btn-sm btn-info-light" title="تفاصيل التسجيل">
                    <i class="fe fe-eye"></i>
                </a>
                <button type="button"
                        class="btn btn-sm btn-danger-light profile-remove-camp-btn"
                        data-enrollment-id="{{ $campEnrollment->id }}"
                        data-camp-id="{{ $camp->id }}"
                        data-camp-name="{{ $camp->name }}"
                        data-remove-url="{{ route('users.remove-from-camp', [$campEnrollment->student_id, $campEnrollment->id]) }}"
                        title="إلغاء الطالب من المعسكر">
                    <i class="fe fe-user-minus"></i>
                </button>
            @endif
        </div>
    </td>
</tr>
