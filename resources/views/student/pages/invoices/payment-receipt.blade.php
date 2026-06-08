@extends('student.layouts.master')

@section('page-title')
    إيصال الدفع {{ $payment->payment_number }}
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
        .student-receipt-stats {
            display: none !important;
        }

        body {
            background: #fff !important;
        }

        .main-content,
        .container-fluid {
            margin: 0 !important;
            padding: 0 !important;
        }

        .student-receipt-doc {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }

        @page {
            margin: 1cm;
            size: A4;
        }
    }
</style>
@stop

@section('content')
    @php
        $statusColors = [
            'pending' => 'bg-warning-transparent text-warning',
            'completed' => 'bg-success-transparent text-success',
            'failed' => 'bg-danger-transparent text-danger',
            'cancelled' => 'bg-secondary-transparent text-secondary',
            'refunded' => 'bg-info-transparent text-info',
        ];
        $statusLabels = [
            'pending' => 'معلقة',
            'completed' => 'مكتملة',
            'failed' => 'فاشلة',
            'cancelled' => 'ملغاة',
            'refunded' => 'مستردة',
        ];
    @endphp

    <div class="main-content app-content student-payment-receipt-page">
        <div class="container-fluid">

            <div class="my-4 page-header-breadcrumb no-print">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('student.payments.index') }}">مدفوعاتي</a></li>
                        <li class="breadcrumb-item active">{{ $payment->payment_number }}</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4 no-print">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-file-text me-1"></i>
                            إيصال الدفع
                        </span>
                        <h2 class="group-show-hero__title mb-2">{{ $payment->payment_number }}</h2>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge {{ $statusColors[$payment->status] ?? 'bg-secondary-transparent text-secondary' }}">
                                {{ $statusLabels[$payment->status] ?? $payment->status }}
                            </span>
                            @if($payment->payment_date)
                                <span class="group-show-chip group-show-chip--sm">
                                    <i class="fe fe-calendar me-1"></i>{{ $payment->payment_date->format('Y-m-d') }}
                                </span>
                            @endif
                            @if($payment->paymentMethod)
                                <span class="group-show-chip group-show-chip--sm">
                                    <i class="fe fe-credit-card me-1"></i>{{ $payment->paymentMethod->name }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions">
                            <button type="button" onclick="window.print()"
                                    class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-printer"></i></span>
                                <span class="group-show-action__text">طباعة الإيصال</span>
                            </button>
                            <a href="{{ route('student.payments.index') }}"
                               class="group-show-action group-show-action--info">
                                <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                                <span class="group-show-action__text">رجوع</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 dashboard-fade-in mb-4 student-receipt-stats no-print">
                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                    <div class="card admin-stats-card admin-stats-card--green">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="admin-stats-card__icon-wrap">
                                <i class="fe fe-dollar-sign admin-stats-card__icon"></i>
                            </div>
                            <div class="admin-stats-card__content flex-fill min-w-0">
                                <p class="admin-stats-card__label mb-1">المبلغ المدفوع</p>
                                <h3 class="admin-stats-card__value mb-1"
                                    data-countup="{{ round($payment->amount, 2) }}"
                                    data-countup-prefix="$"
                                    data-countup-decimals="2">0</h3>
                                <p class="admin-stats-card__sub mb-0">في هذه الدفعة</p>
                            </div>
                        </div>
                    </div>
                </div>
                @if($payment->invoice)
                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                        <div class="card admin-stats-card admin-stats-card--blue">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="admin-stats-card__icon-wrap">
                                    <i class="fe fe-file-text admin-stats-card__icon"></i>
                                </div>
                                <div class="admin-stats-card__content flex-fill min-w-0">
                                    <p class="admin-stats-card__label mb-1">إجمالي الفاتورة</p>
                                    <h3 class="admin-stats-card__value mb-1"
                                        data-countup="{{ round($payment->invoice->total_amount, 2) }}"
                                        data-countup-prefix="$"
                                        data-countup-decimals="2">0</h3>
                                    <p class="admin-stats-card__sub mb-0">{{ $payment->invoice->invoice_number }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                        <div class="card admin-stats-card admin-stats-card--orange">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="admin-stats-card__icon-wrap">
                                    <i class="fe fe-alert-circle admin-stats-card__icon"></i>
                                </div>
                                <div class="admin-stats-card__content flex-fill min-w-0">
                                    <p class="admin-stats-card__label mb-1">المبلغ المتبقي</p>
                                    <h3 class="admin-stats-card__value mb-1"
                                        data-countup="{{ round($payment->invoice->remaining_amount, 2) }}"
                                        data-countup-prefix="$"
                                        data-countup-decimals="2">0</h3>
                                    <p class="admin-stats-card__sub mb-0">بعد هذه الدفعة</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="row justify-content-center">
                <div class="col-xl-9 col-lg-10">
                    <div class="card custom-card group-show-members-card dashboard-fade-in student-receipt-doc" id="receipt">
                        <div class="student-receipt-doc__header text-center py-4 px-3 border-bottom">
                            <p class="text-muted fs-12 mb-1 text-uppercase letter-spacing-1">أكاديمية كلاودسوفت</p>
                            <h3 class="mb-2 fw-bold">إيصال دفع</h3>
                            <span class="badge {{ $statusColors[$payment->status] ?? 'bg-secondary-transparent text-secondary' }}">
                                {{ $statusLabels[$payment->status] ?? $payment->status }}
                            </span>
                        </div>

                        <div class="card-body p-4 p-md-5">
                            <div class="student-receipt-doc__amount text-center mb-4">
                                <p class="text-muted mb-2">المبلغ المدفوع</p>
                                <h1 class="text-success fw-bold mb-0"
                                    data-countup="{{ round($payment->amount, 2) }}"
                                    data-countup-prefix="$"
                                    data-countup-decimals="2">0</h1>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-5 text-muted fw-normal">رقم الإيصال</dt>
                                        <dd class="col-sm-7 fw-semibold mb-2">{{ $payment->payment_number }}</dd>

                                        <dt class="col-sm-5 text-muted fw-normal">تاريخ الدفع</dt>
                                        <dd class="col-sm-7 mb-2">{{ $payment->payment_date->format('Y-m-d') }}</dd>

                                        @if($payment->paymentMethod)
                                            <dt class="col-sm-5 text-muted fw-normal">طريقة الدفع</dt>
                                            <dd class="col-sm-7 mb-0">
                                                <i class="fe fe-credit-card me-1 text-muted"></i>{{ $payment->paymentMethod->name }}
                                            </dd>
                                        @endif
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row mb-0">
                                        @if($payment->invoice)
                                            <dt class="col-sm-5 text-muted fw-normal">رقم الفاتورة</dt>
                                            <dd class="col-sm-7 mb-2">
                                                <a href="{{ route('student.invoices.show', $payment->invoice_id) }}"
                                                   class="fw-semibold text-primary text-decoration-none no-print-link">
                                                    {{ $payment->invoice->invoice_number }}
                                                </a>
                                            </dd>
                                        @endif

                                        @if($payment->reference_number)
                                            <dt class="col-sm-5 text-muted fw-normal">رقم المرجع</dt>
                                            <dd class="col-sm-7 mb-2">{{ $payment->reference_number }}</dd>
                                        @endif

                                        <dt class="col-sm-5 text-muted fw-normal">الطالب</dt>
                                        <dd class="col-sm-7 mb-0">{{ Auth::user()->name }}</dd>
                                    </dl>
                                </div>
                            </div>

                            @if($payment->invoice && $payment->invoice->items->count() > 0)
                                <div class="mb-4">
                                    <h6 class="fw-semibold mb-3">تفاصيل الفاتورة</h6>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0 dashboard-table student-invoice-show-table">
                                            <thead>
                                                <tr>
                                                    <th class="ps-3">البند</th>
                                                    <th class="text-end pe-3">المبلغ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($payment->invoice->items as $item)
                                                    <tr>
                                                        <td class="ps-3">
                                                            <strong class="d-block">{{ $item->description }}</strong>
                                                            @if($item->campEnrollment && $item->campEnrollment->camp)
                                                                <span class="group-show-chip group-show-chip--sm mt-1">
                                                                    {{ $item->campEnrollment->camp->name }}
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td class="text-end pe-3 fw-semibold">${{ number_format($item->total_price, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr class="student-invoice-show-table__total">
                                                    <td class="ps-3"><strong>إجمالي الفاتورة</strong></td>
                                                    <td class="text-end pe-3 fw-bold">${{ number_format($payment->invoice->total_amount, 2) }}</td>
                                                </tr>
                                                <tr class="student-invoice-show-table__paid">
                                                    <td class="ps-3"><strong>مدفوع سابقاً</strong></td>
                                                    <td class="text-end pe-3 fw-bold text-success">${{ number_format($payment->invoice->paid_amount - $payment->amount, 2) }}</td>
                                                </tr>
                                                <tr class="student-invoice-show-table__remaining">
                                                    <td class="ps-3"><strong>هذه الدفعة</strong></td>
                                                    <td class="text-end pe-3 fw-bold text-primary">${{ number_format($payment->amount, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="ps-3"><strong>المبلغ المتبقي</strong></td>
                                                    <td class="text-end pe-3 fw-bold text-danger">${{ number_format($payment->invoice->remaining_amount, 2) }}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            @if($payment->notes)
                                <div class="alert alert-info mb-3">
                                    <i class="fe fe-info me-2"></i>
                                    <strong>ملاحظات:</strong> {{ $payment->notes }}
                                </div>
                            @endif

                            @if($payment->cancellation_reason)
                                <div class="alert alert-danger mb-3">
                                    <i class="fe fe-x-circle me-2"></i>
                                    <strong>سبب الإلغاء:</strong> {{ $payment->cancellation_reason }}
                                </div>
                            @endif

                            @if($payment->refund_reason)
                                <div class="alert alert-warning mb-3">
                                    <i class="fe fe-rotate-ccw me-2"></i>
                                    <strong>سبب الاسترداد:</strong> {{ $payment->refund_reason }}
                                </div>
                            @endif

                            <div class="text-center text-muted small border-top pt-4 mt-2">
                                <p class="mb-1">تم إنشاء هذا الإيصال في: {{ $payment->created_at->format('Y-m-d H:i:s') }}</p>
                                <p class="mb-0">إيصال رسمي من أكاديمية كلاودسوفت</p>
                            </div>
                        </div>

                        <div class="card-footer bg-transparent border-top d-none d-print-block text-center py-5">
                            <div class="row">
                                <div class="col-6">
                                    <div class="border-top border-dark d-inline-block px-5 pt-2">
                                        <small>توقيع الطالب</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border-top border-dark d-inline-block px-5 pt-2">
                                        <small>توقيع الإدارة</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

@push('scripts')
<script>
(function () {
    function formatNumber(value, decimals) {
        if (decimals) {
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
        const decimals = el.dataset.countupDecimals === '2';
        const duration = 900;
        const start = performance.now();

        function step(now) {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = prefix + formatNumber(target * eased, decimals) + suffix;
            if (progress < 1) requestAnimationFrame(step);
        }

        requestAnimationFrame(step);
    });
})();
</script>
@endpush
