@extends('student.layouts.master')

@section('page-title')
    تفاصيل الدفعة
@stop

@section('css')
<style>
    .payment-box {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        padding: 2rem;
    }
    .payment-header {
        border-bottom: 2px solid #6c5ce7;
        padding-bottom: 1rem;
        margin-bottom: 1.5rem;
    }
</style>
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تفاصيل الدفعة #{{ $payment->payment_number }}</h5>
                </div>
                <div>
                    <a href="{{ route('student.payments.index') }}" class="btn btn-secondary btn-sm">رجوع</a>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-8">
                    <div class="card payment-box">
                        <div class="payment-header">
                            <div class="row">
                                <div class="col-md-6">
                                    <h3>دفعة #{{ $payment->payment_number }}</h3>
                                    <p class="mb-0">
                                        <span class="badge
                                            @if($payment->status === 'pending') bg-warning
                                            @elseif($payment->status === 'completed') bg-success
                                            @elseif($payment->status === 'failed') bg-danger
                                            @elseif($payment->status === 'cancelled') bg-secondary
                                            @endif">
                                            @if($payment->status === 'pending') قيد الانتظار
                                            @elseif($payment->status === 'completed') مكتملة
                                            @elseif($payment->status === 'failed') فشلت
                                            @elseif($payment->status === 'cancelled') ملغاة
                                            @endif
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-6 text-end">
                                    <h4 class="text-primary mb-2">${{ number_format($payment->amount, 2) }}</h4>
                                    <p class="mb-0"><strong>تاريخ الدفع:</strong> {{ $payment->payment_date?->format('Y-m-d') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6>معلومات الدفع:</h6>
                                <p class="mb-1"><strong>طريقة الدفع:</strong> {{ $payment->paymentMethod->name }}</p>
                                <p class="mb-1"><strong>رقم الإيصال:</strong> {{ $payment->receipt_number ?? 'غير متوفر' }}</p>
                                @if($payment->transaction_id)
                                    <p class="mb-0"><strong>رقم المعاملة:</strong> {{ $payment->transaction_id }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6>معلومات الفاتورة:</h6>
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>رقم الفاتورة:</strong>
                                                <a href="{{ route('student.invoices.show', $payment->invoice_id) }}">{{ $payment->invoice->invoice_number }}</a>
                                            </p>
                                            <p class="mb-1"><strong>المبلغ الإجمالي:</strong> ${{ number_format($payment->invoice->total_amount, 2) }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>المبلغ المدفوع:</strong> ${{ number_format($payment->invoice->paid_amount, 2) }}</p>
                                            <p class="mb-0"><strong>المبلغ المتبقي:</strong> ${{ number_format($payment->invoice->remaining_amount, 2) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($payment->notes)
                            <div class="mb-3">
                                <h6>الملاحظات:</h6>
                                <p>{{ $payment->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-xl-4">
                    @if($payment->status === 'completed' && $payment->receipt_number)
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">الإيصال</h6>
                            </div>
                            <div class="card-body text-center">
                                <p><strong>رقم الإيصال:</strong> {{ $payment->receipt_number }}</p>
                                <a href="{{ route('student.payments.receipt', $payment->id) }}" class="btn btn-primary btn-sm">
                                    <i class="fa-solid fa-download me-2"></i>تحميل الإيصال
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
@stop
