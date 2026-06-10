@php
    $camp = $campEnrollment->camp;
    $displayFee = $campFee ?? (float) ($campEnrollment->invoice?->total_amount ?? $camp?->price ?? 0);
    $statusClass = match($campEnrollment->status) {
        'approved' => 'text-success',
        'pending' => 'text-warning',
        'rejected' => 'text-danger',
        'cancelled' => 'text-muted',
        default => '',
    };
    $paymentClass = match($campEnrollment->payment_status) {
        'paid' => 'text-success',
        'unpaid' => 'text-warning',
        'refunded' => 'text-info',
        default => '',
    };
@endphp
<tr class="admin-users-table__row">
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
    <td><span class="group-show-chip group-show-chip--sm {{ $statusClass }}">{{ $campEnrollment->status_label }}</span></td>
    <td><span class="group-show-chip group-show-chip--sm {{ $paymentClass }}">{{ $campEnrollment->payment_status_label }}</span></td>
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
        @if($camp)
            <a href="{{ route('training-camps.enrollments.show', [$camp->id, $campEnrollment->id]) }}"
               class="btn btn-sm btn-info-light" title="تفاصيل التسجيل">
                <i class="fe fe-eye"></i>
            </a>
        @endif
    </td>
</tr>
