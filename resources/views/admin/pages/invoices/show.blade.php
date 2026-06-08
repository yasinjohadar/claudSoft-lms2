@extends('admin.layouts.master')

@section('page-title')
    تفاصيل الفاتورة {{ $invoice->invoice_number }}
@stop

@section('styles')
<style>
    @media print {
        .no-print,
        .app-sidebar,
        .app-header,
        .page-header-breadcrumb,
        .group-show-hero,
        .group-show-actions,
        .invoice-delete-section {
            display: none !important;
        }

        .main-content {
            margin: 0 !important;
            padding: 0 !important;
        }

        .group-show-members-card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }

        @page {
            margin: 1cm;
            size: A4;
        }

        body {
            background: white !important;
        }
    }
</style>
@stop

@section('content')
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

    <div class="main-content app-content">
        <div class="container-fluid">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show no-print" role="alert">
                    <i class="fe fe-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show no-print" role="alert">
                    <i class="fe fe-alert-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="my-4 page-header-breadcrumb no-print">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">الفواتير</a></li>
                        <li class="breadcrumb-item active">{{ $invoice->invoice_number }}</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4 no-print">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-file-text me-1"></i>
                            تفاصيل الفاتورة
                        </span>
                        <h2 class="group-show-hero__title mb-2">{{ $invoice->invoice_number }}</h2>
                        <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                            <span class="badge {{ $statusColors[$invoice->status] ?? 'bg-secondary-transparent text-secondary' }}">
                                {{ $statusLabels[$invoice->status] ?? $invoice->status }}
                            </span>
                            @if($invoice->is_overdue)
                                <span class="badge bg-danger-transparent text-danger">متأخرة</span>
                            @endif
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="group-show-chip group-show-chip--sm">
                                <i class="fe fe-user me-1"></i>{{ $invoice->student->name }}
                            </span>
                            <span class="group-show-chip group-show-chip--sm">
                                <i class="fe fe-calendar me-1"></i>الإصدار: {{ $invoice->issue_date->format('Y-m-d') }}
                            </span>
                            @if($invoice->due_date)
                                <span class="group-show-chip group-show-chip--sm">
                                    <i class="fe fe-clock me-1"></i>الاستحقاق: {{ $invoice->due_date->format('Y-m-d') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions">
                            @if($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
                                <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}"
                                   class="group-show-action group-show-action--success">
                                    <span class="group-show-action__icon"><i class="fe fe-plus"></i></span>
                                    <span class="group-show-action__text">إضافة دفعة</span>
                                </a>
                            @endif
                            <button type="button" onclick="window.print()"
                                    class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-printer"></i></span>
                                <span class="group-show-action__text">طباعة</span>
                            </button>
                            <form action="{{ route('invoices.send-whatsapp', $invoice->id) }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="group-show-action group-show-action--success w-100"
                                        onclick="return confirm('هل أنت متأكد من إرسال الفاتورة عبر WhatsApp للطالب {{ $invoice->student->name }}؟');">
                                    <span class="group-show-action__icon"><i class="fe fe-message-circle"></i></span>
                                    <span class="group-show-action__text">إرسال عبر WhatsApp</span>
                                </button>
                            </form>
                            <a href="{{ route('invoices.index') }}"
                               class="group-show-action group-show-action--info">
                                <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                                <span class="group-show-action__text">رجوع</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @include('admin.pages.invoices.partials.show-stats', ['invoice' => $invoice])

            <div class="row g-4">
                <div class="col-xl-8">
                    <div class="card custom-card group-show-members-card dashboard-fade-in">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title mb-1">بنود الفاتورة</h4>
                            <p class="fs-12 text-muted mb-0">تفاصيل الطالب والبنود والمبالغ.</p>
                        </div>
                        <div class="card-body pt-3">
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3 fw-semibold">معلومات الطالب</h6>
                                    <dl class="row mb-0">
                                        <dt class="col-sm-4 text-muted fw-normal">الاسم</dt>
                                        <dd class="col-sm-8 mb-2">{{ $invoice->student->name }}</dd>
                                        <dt class="col-sm-4 text-muted fw-normal">البريد</dt>
                                        <dd class="col-sm-8 mb-0 text-break">{{ $invoice->student->email }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3 fw-semibold">معلومات الفاتورة</h6>
                                    <dl class="row mb-0">
                                        <dt class="col-sm-5 text-muted fw-normal">تاريخ الإصدار</dt>
                                        <dd class="col-sm-7 mb-2">{{ $invoice->issue_date->format('Y-m-d') }}</dd>
                                        <dt class="col-sm-5 text-muted fw-normal">تاريخ الاستحقاق</dt>
                                        <dd class="col-sm-7 mb-0">{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '—' }}</dd>
                                    </dl>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover mb-0 dashboard-table student-invoice-show-table">
                                    <thead>
                                        <tr>
                                            <th class="ps-3">الوصف</th>
                                            <th>الكمية</th>
                                            <th>سعر الوحدة</th>
                                            <th class="pe-3">المجموع</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($invoice->items as $item)
                                            <tr>
                                                <td class="ps-3">
                                                    <strong class="d-block">{{ $item->description }}</strong>
                                                    @if($item->campEnrollment && $item->campEnrollment->camp)
                                                        <span class="group-show-chip group-show-chip--sm mt-1">
                                                            {{ $item->campEnrollment->camp->name }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>{{ $item->quantity }}</td>
                                                <td>${{ number_format($item->unit_price, 2) }}</td>
                                                <td class="pe-3 fw-semibold">${{ number_format($item->total_price, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="student-invoice-show-table__total">
                                            <td colspan="3" class="text-end ps-3"><strong>المجموع</strong></td>
                                            <td class="pe-3 fw-bold">${{ number_format($invoice->total_amount, 2) }}</td>
                                        </tr>
                                        <tr class="student-invoice-show-table__paid">
                                            <td colspan="3" class="text-end ps-3"><strong>المدفوع</strong></td>
                                            <td class="pe-3 fw-bold text-success">${{ number_format($invoice->paid_amount, 2) }}</td>
                                        </tr>
                                        <tr class="student-invoice-show-table__remaining">
                                            <td colspan="3" class="text-end ps-3"><strong>المتبقي</strong></td>
                                            <td class="pe-3 fw-bold text-danger">${{ number_format($invoice->remaining_amount, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            @if($invoice->notes)
                                <div class="alert alert-info mt-4 mb-0">
                                    <i class="fe fe-info me-2"></i>
                                    <strong>ملاحظات:</strong> {{ $invoice->notes }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 no-print">
                    @include('admin.pages.invoices.partials.show-payments', ['invoice' => $invoice])
                </div>
            </div>

            <div class="row mt-4 invoice-delete-section no-print">
                <div class="col-xl-12">
                    <div class="card custom-card group-show-members-card border border-danger dashboard-fade-in">
                        <div class="card-body text-center py-4">
                            <div class="admin-stats-card__icon-wrap mx-auto mb-3" style="width: 56px; height: 56px;">
                                <i class="fe fe-trash-2 admin-stats-card__icon text-danger"></i>
                            </div>
                            <h6 class="mb-2">حذف الفاتورة نهائياً</h6>
                            <p class="text-muted mb-3 small">حذف الفاتورة نهائياً من النظام. هذا الإجراء لا يمكن التراجع عنه.</p>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#forceDeleteModal{{ $invoice->id }}">
                                <i class="fe fe-trash-2 me-2"></i>حذف نهائياً
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="forceDeleteModal{{ $invoice->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">
                                <i class="fe fe-alert-triangle me-2"></i>حذف نهائي
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-danger">
                                <strong>تحذير!</strong> هذا الإجراء لا يمكن التراجع عنه.
                            </div>
                            <p>هل أنت متأكد من حذف الفاتورة <strong>{{ $invoice->invoice_number }}</strong> نهائياً؟</p>
                            <ul class="list-unstyled mb-0">
                                <li><strong>الطالب:</strong> {{ $invoice->student->name }}</li>
                                <li><strong>المبلغ الإجمالي:</strong> ${{ number_format($invoice->total_amount, 2) }}</li>
                                <li><strong>الحالة:</strong> {{ $statusLabels[$invoice->status] ?? $invoice->status }}</li>
                            </ul>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                            <form action="{{ route('invoices.force-delete', $invoice->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('POST')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fe fe-trash-2 me-2"></i>حذف نهائياً
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

@section('script')
<script>
    (function () {
        function formatNumber(value, withDecimals) {
            if (withDecimals) {
                return new Intl.NumberFormat('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }).format(value);
            }
            return new Intl.NumberFormat('ar-EG').format(Math.round(value));
        }

        document.querySelectorAll('[data-countup]').forEach(function (el) {
            const target = parseFloat(el.dataset.countup || '0');
            const prefix = el.dataset.countupPrefix || '';
            const suffix = el.dataset.countupSuffix || '';
            const decimals = el.dataset.countupDecimals === '2' ? 2 : 0;
            const duration = 800;
            const start = performance.now();

            function step(now) {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = prefix + formatNumber(target * eased, decimals > 0) + suffix;
                if (progress < 1) requestAnimationFrame(step);
            }

            requestAnimationFrame(step);
        });
    })();
</script>
@stop
