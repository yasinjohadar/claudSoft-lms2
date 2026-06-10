@php
    $canPay = in_array($invoice->status, ['issued', 'partial'], true) && (float) $invoice->remaining_amount > 0;
    $statusClass = match($invoice->status) {
        'paid' => 'text-success',
        'partial' => 'text-warning',
        'issued' => 'text-info',
        'cancelled', 'refunded' => 'text-danger',
        default => '',
    };
@endphp
<tr class="admin-users-table__row" id="profile-invoice-row-{{ $invoice->id }}" data-invoice-id="{{ $invoice->id }}">
    <td>{{ $invoice->invoice_number }}</td>
    <td>{{ optional($invoice->issue_date)->format('Y-m-d') }}</td>
    <td>{{ number_format($invoice->total_amount, 2) }}</td>
    <td>{{ number_format($invoice->paid_amount, 2) }}</td>
    <td>{{ number_format($invoice->remaining_amount, 2) }}</td>
    <td><span class="group-show-chip group-show-chip--sm {{ $statusClass }}">{{ $invoice->status_label }}</span></td>
    <td>
        @if($canPay)
            <button type="button"
                    class="btn btn-sm btn-success-light js-profile-record-payment"
                    data-invoice-id="{{ $invoice->id }}"
                    data-invoice-number="{{ $invoice->invoice_number }}"
                    data-remaining="{{ $invoice->remaining_amount }}"
                    title="تسديد">
                <i class="fe fe-dollar-sign"></i>
                <span class="d-none d-md-inline ms-1">تسديد</span>
            </button>
        @else
            <span class="text-muted">—</span>
        @endif
    </td>
</tr>
