@php
    $statusClass = match($payment->status) {
        'completed' => 'text-success',
        'pending' => 'text-warning',
        'failed', 'cancelled' => 'text-danger',
        'refunded' => 'text-info',
        default => '',
    };
@endphp
<tr class="admin-users-table__row">
    <td>
        <a href="{{ route('payments.show', $payment->id) }}" class="text-primary text-decoration-none fw-semibold">{{ $payment->payment_number }}</a>
    </td>
    <td>
        @if($payment->invoice)
            <small class="d-block text-muted">{{ $payment->invoice->invoice_number }}</small>
            @foreach($payment->invoice->relatedPrograms() as $program)
                <span class="badge bg-primary-transparent text-primary" title="{{ $program['type'] }}">
                    <i class="fe {{ $program['icon'] }} me-1"></i>{{ $program['name'] }}
                </span>
            @endforeach
        @else
            <span class="text-muted">—</span>
        @endif
    </td>
    <td>{{ optional($payment->payment_date)->format('Y-m-d H:i') }}</td>
    <td>{{ number_format($payment->amount, 2) }}</td>
    <td>{{ $payment->paymentMethod->name ?? '—' }}</td>
    <td><span class="group-show-chip group-show-chip--sm {{ $statusClass }}">{{ $payment->status_label }}</span></td>
</tr>
