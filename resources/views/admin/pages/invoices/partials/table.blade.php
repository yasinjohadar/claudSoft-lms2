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
            @php $programs = $invoice->relatedPrograms(); @endphp

            @if($programs->isNotEmpty())
                <div class="d-flex flex-wrap gap-1">
                    @foreach($programs as $program)
                        <span class="group-show-chip group-show-chip--sm" title="{{ $program['type'] }}">
                            <i class="fe {{ $program['icon'] }} me-1"></i>{{ $program['name'] }}
                        </span>
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
            {{-- المبلغ الذي أدخله الطالب في طلب الدفع (غير محتسب حتى القبول) — للمطابقة مع الفاتورة --}}
            @php
                $pendingPayments = $invoice->payments->where('status', 'pending')->sortBy('created_at');
                $pendingTotal = round((float) $pendingPayments->sum('amount'), 2);
                $remaining = round((float) $invoice->remaining_amount, 2);
                $diff = round($remaining - $pendingTotal, 2);
            @endphp

            @if($pendingPayments->isNotEmpty())
                <div class="d-flex flex-column gap-1">
                    @foreach($pendingPayments as $pp)
                        <a href="{{ route('payments.show', $pp->id) }}" class="fw-bold text-warning text-decoration-none" title="مراجعة طلب الدفع {{ $pp->payment_number }}">
                            <i class="fe fe-clock me-1"></i>${{ number_format($pp->amount, 2) }}
                        </a>
                    @endforeach
                    @if(abs($diff) < 0.01)
                        <span class="badge bg-success-transparent text-success"><i class="fe fe-check me-1"></i>مطابق للمتبقي</span>
                    @elseif($diff > 0)
                        <span class="badge bg-warning-transparent text-warning" title="المتبقي بعد القبول">أقل بـ ${{ number_format($diff, 2) }}</span>
                    @else
                        <span class="badge bg-danger-transparent text-danger">أكبر بـ ${{ number_format(abs($diff), 2) }}</span>
                    @endif
                </div>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>

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
        <td colspan="12">
            <div class="group-show-empty py-5">
                <i class="fe fe-inbox group-show-empty__icon"></i>
                <h5 class="group-show-empty__title">لا توجد فواتير</h5>
                <p class="group-show-empty__desc mb-0">جرّب تعديل الفلاتر أو أنشئ فاتورة جديدة.</p>
            </div>
        </td>
    </tr>
@endforelse
