@php
    $canPay = $invoice->canAcceptStudentPayment();
    $pendingPayment = $invoice->pendingPayment ?? $invoice->payments->firstWhere('status', 'pending');
@endphp

@if($canPay && isset($paymentMethods) && $paymentMethods->count() > 0)
    <button type="button"
            class="btn btn-success rounded-pill"
            data-bs-toggle="modal"
            data-bs-target="#submitPaymentModal">
        <i class="fe fe-credit-card me-1"></i>سداد الفاتورة
    </button>
@endif
