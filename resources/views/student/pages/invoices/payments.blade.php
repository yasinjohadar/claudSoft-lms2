@extends('student.layouts.master')

@section('page-title')
    مدفوعاتي
@stop

@section('content')
<div class="main-content app-content student-payments-page">
    <div class="container-fluid">

        @include('student.components.alerts')

        <div class="my-4 student-my-courses-welcome">
            <h4 class="student-my-courses-welcome__title mb-1">مدفوعاتي</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item active">مدفوعاتي</li>
                </ol>
            </nav>
        </div>

        @include('student.pages.invoices.partials.payments-stats', ['stats' => $stats])

        <div class="card custom-card student-quizzes-panel">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="avatar avatar-sm bg-primary-transparent">
                        <i class="fe fe-credit-card text-primary"></i>
                    </span>
                    <h6 class="card-title mb-0">سجل المدفوعات</h6>
                </div>

                @include('student.pages.invoices.partials.payments-filters')

                @if($payments->count() > 0)
                    @include('student.pages.invoices.partials.payments-table', ['payments' => $payments])

                    <div class="row g-3 d-lg-none">
                        @foreach($payments as $index => $payment)
                            @include('student.pages.invoices.partials.payments-card', [
                                'payment' => $payment,
                                'index' => $index,
                            ])
                        @endforeach
                    </div>

                    @if($payments->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $payments->appends(request()->query())->links() }}
                        </div>
                    @endif
                @else
                    @include('student.pages.invoices.partials.payments-empty')
                @endif
            </div>
        </div>

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
