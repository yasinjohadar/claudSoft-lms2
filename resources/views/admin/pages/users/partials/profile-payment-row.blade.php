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
    <td>{{ $payment->payment_number }}</td>
    <td>{{ optional($payment->payment_date)->format('Y-m-d H:i') }}</td>
    <td>{{ number_format($payment->amount, 2) }}</td>
    <td>{{ $payment->paymentMethod->name ?? '—' }}</td>
    <td><span class="group-show-chip group-show-chip--sm {{ $statusClass }}">{{ $payment->status_label }}</span></td>
</tr>
