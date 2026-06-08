@extends('student.layouts.master')

@section('page-title')
    الفاتورة {{ $invoice->invoice_number }}
@stop

@section('styles')
<style>
    @media print {
        .no-print,
        .app-header,
        .app-sidebar,
        .page-header-breadcrumb,
        .student-invoice-show-sidebar {
            display: none !important;
        }

        .student-invoice-show-page {
            padding: 0 !important;
        }

        .student-quizzes-panel {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }
    }
</style>
@stop

@section('content')
<div class="main-content app-content student-invoice-show-page">
    <div class="container-fluid">

        @include('student.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4 no-print">
            <div class="min-w-0">
                <h4 class="student-my-courses-welcome__title mb-1">تفاصيل الفاتورة</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('student.invoices.index') }}">فواتيري</a></li>
                        <li class="breadcrumb-item active">{{ $invoice->invoice_number }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
                @include('student.pages.invoices.partials.submit-payment-button', [
                    'invoice' => $invoice,
                    'paymentMethods' => $paymentMethods ?? collect(),
                ])
                <a href="{{ route('student.invoices.index') }}" class="btn btn-outline-secondary rounded-pill">
                    <i class="fe fe-arrow-right me-1"></i>العودة للفواتير
                </a>
                <button type="button" onclick="window.print()" class="btn btn-primary rounded-pill">
                    <i class="fe fe-printer me-1"></i>طباعة
                </button>
            </div>
        </div>

        @include('student.pages.invoices.partials.show-stats', ['invoice' => $invoice])

        <div class="row g-4">
            <div class="col-lg-8">
                @include('student.pages.invoices.partials.show-items', ['invoice' => $invoice])
            </div>
            <div class="col-lg-4 student-invoice-show-sidebar">
                @include('student.pages.invoices.partials.show-payments-sidebar', ['invoice' => $invoice])
            </div>
        </div>

        @if(isset($paymentMethods) && $invoice->canAcceptStudentPayment())
            @include('student.pages.invoices.partials.submit-payment-modal', [
                'invoice' => $invoice,
                'paymentMethods' => $paymentMethods,
            ])
        @endif

    </div>
</div>
@stop

@section('scripts')
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
            var target = parseFloat(el.dataset.countup || '0');
            var prefix = el.dataset.countupPrefix || '';
            var suffix = el.dataset.countupSuffix || '';
            var decimals = el.dataset.countupDecimals === '2';
            var duration = 800;
            var start = performance.now();

            function step(now) {
                var progress = Math.min((now - start) / duration, 1);
                var eased = 1 - Math.pow(1 - progress, 3);
                var value = formatNumber(target * eased, decimals);
                el.textContent = prefix + value + suffix;
                if (progress < 1) requestAnimationFrame(step);
            }

            requestAnimationFrame(step);
        });
    })();
</script>
@stop
