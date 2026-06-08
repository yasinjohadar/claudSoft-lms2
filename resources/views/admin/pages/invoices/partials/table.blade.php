@php
    $statusColors = [
        'draft' => 'bg-secondary-transparent text-secondary',
        'issued' => 'bg-info-transparent text-info',
        'partial' => 'bg-warning-transparent text-warning',
        'paid' => 'bg-success-transparent text-success',
        'cancelled' => 'bg-danger-transparent text-danger',
        'refunded' => 'bg-dark-transparent text-dark',
    ];
    $statusLabels = [
        'draft' => 'مسودة',
        'issued' => 'صادرة',
        'partial' => 'جزئياً',
        'paid' => 'مدفوعة',
        'cancelled' => 'ملغاة',
        'refunded' => 'مستردة',
    ];
@endphp

@forelse ($invoices as $invoice)
    <tr>
        <td>{{ $loop->iteration + ($invoices->currentPage() - 1) * $invoices->perPage() }}</td>

        <td>
            <a href="{{ route('invoices.show', $invoice->id) }}" class="fw-semibold text-primary text-decoration-none">
                {{ $invoice->invoice_number }}
            </a>
        </td>

        <td>
            <div class="min-w-0">
                <strong class="d-block text-truncate">{{ $invoice->student->name }}</strong>
                <small class="text-muted d-block text-truncate">{{ $invoice->student->email }}</small>
            </div>
        </td>

        <td>
            @php
                $campNames = $invoice->items
                    ->map(fn ($item) => optional(optional($item->campEnrollment)->camp)->name)
                    ->filter()
                    ->unique()
                    ->values();
            @endphp

            @if($campNames->isNotEmpty())
                <div class="d-flex flex-wrap gap-1">
                    @foreach($campNames as $campName)
                        <span class="group-show-chip group-show-chip--sm">{{ $campName }}</span>
                    @endforeach
                </div>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>

        <td><small>{{ $invoice->issue_date->format('Y-m-d') }}</small></td>

        <td>
            <small>{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '—' }}</small>
            @if($invoice->is_overdue)
                <span class="badge bg-danger-transparent text-danger ms-1">متأخرة</span>
            @endif
        </td>

        <td><strong>${{ number_format($invoice->total_amount, 2) }}</strong></td>
        <td class="text-success fw-semibold">${{ number_format($invoice->paid_amount, 2) }}</td>
        <td class="text-danger fw-semibold">${{ number_format($invoice->remaining_amount, 2) }}</td>

        <td>
            <span class="badge {{ $statusColors[$invoice->status] ?? 'bg-secondary-transparent text-secondary' }}">
                {{ $statusLabels[$invoice->status] ?? $invoice->status }}
            </span>
        </td>

        <td>
            <div class="d-flex flex-wrap gap-1">
                <a href="{{ route('invoices.show', $invoice->id) }}"
                   class="btn btn-sm btn-info-light" title="عرض التفاصيل">
                    <i class="fe fe-eye"></i>
                </a>

                @if($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
                    <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}"
                       class="btn btn-sm btn-success-light" title="إضافة دفعة">
                        <i class="fe fe-plus"></i>
                    </a>
                @endif

                @if($invoice->status !== 'cancelled' && $invoice->status !== 'paid')
                    <button type="button" class="btn btn-sm btn-warning-light"
                            data-bs-toggle="modal"
                            data-bs-target="#cancelModal{{ $invoice->id }}"
                            title="إلغاء">
                        <i class="fe fe-slash"></i>
                    </button>
                @endif

                <button type="button" class="btn btn-sm btn-danger-light"
                        data-bs-toggle="modal"
                        data-bs-target="#forceDeleteModal{{ $invoice->id }}"
                        title="حذف نهائي">
                    <i class="fe fe-trash-2"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="11">
            <div class="group-show-empty py-5">
                <i class="fe fe-inbox group-show-empty__icon"></i>
                <h5 class="group-show-empty__title">لا توجد فواتير</h5>
                <p class="group-show-empty__desc mb-0">جرّب تعديل الفلاتر أو أنشئ فاتورة جديدة.</p>
            </div>
        </td>
    </tr>
@endforelse
