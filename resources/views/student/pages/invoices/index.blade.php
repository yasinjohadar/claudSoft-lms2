@extends('student.layouts.master')

@section('page-title')
    فواتيري
@stop

@section('content')
<div class="main-content app-content student-invoices-page">
    <div class="container-fluid">

        @include('student.components.alerts')

        <div class="my-4 student-my-courses-welcome">
            <h4 class="student-my-courses-welcome__title mb-1">فواتيري</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item active">فواتيري</li>
                </ol>
            </nav>
        </div>

        @include('student.pages.invoices.partials.invoices-stats', ['stats' => $stats])

        <div class="card custom-card student-quizzes-panel">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="avatar avatar-sm bg-primary-transparent">
                        <i class="fe fe-file-text text-primary"></i>
                    </span>
                    <h6 class="card-title mb-0">قائمة الفواتير</h6>
                </div>

                @include('student.pages.invoices.partials.invoices-filters')

                @if($invoices->count() > 0)
                    @include('student.pages.invoices.partials.invoices-table', ['invoices' => $invoices])

                    <div class="row g-4 d-xl-none">
                        @foreach($invoices as $index => $invoice)
                            @include('student.pages.invoices.partials.invoices-card', [
                                'invoice' => $invoice,
                                'index' => $index,
                            ])
                        @endforeach
                    </div>

                    @if($invoices->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $invoices->appends(request()->query())->links() }}
                        </div>
                    @endif
                @else
                    @include('student.pages.invoices.partials.invoices-empty')
                @endif
            </div>
        </div>

        @if(isset($paymentMethods) && $paymentMethods->count() > 0)
            @include('student.pages.invoices.partials.index-submit-payment-modal', ['paymentMethods' => $paymentMethods])
        @endif

    </div>
</div>
@stop

@section('scripts')
<script>
    (function () {
        document.querySelectorAll('.js-open-pay-modal').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var invoiceId = btn.dataset.invoiceId;
                var invoiceNumber = btn.dataset.invoiceNumber;
                var remaining = btn.dataset.remaining;

                var form = document.getElementById('indexSubmitPaymentForm');
                var modalEl = document.getElementById('indexSubmitPaymentModal');

                if (!form || !modalEl) {
                    return;
                }

                form.action = @json(route('student.invoices.pay', ['id' => '__INVOICE_ID__'])).replace('__INVOICE_ID__', invoiceId);
                document.getElementById('indexPayInvoiceNumber').textContent = invoiceNumber || '';
                document.getElementById('indexPayRemaining').textContent = remaining || '0.00';

                var amountInput = document.getElementById('index_payment_amount');
                if (amountInput) {
                    amountInput.value = remaining || '';
                    amountInput.max = remaining || '';
                }

                var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            });
        });

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
            var decimals = el.dataset.countupDecimals === '2' ? 2 : (el.dataset.countupDecimals === '1' ? 1 : 0);
            var duration = 800;
            var start = performance.now();

            function step(now) {
                var progress = Math.min((now - start) / duration, 1);
                var eased = 1 - Math.pow(1 - progress, 3);
                var value = formatNumber(target * eased, decimals > 0);
                el.textContent = prefix + value + suffix;
                if (progress < 1) requestAnimationFrame(step);
            }

            requestAnimationFrame(step);
        });
    })();
</script>
@stop
