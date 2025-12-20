@extends('student.layouts.master')

@section('page-title')
    إيصال الدفع {{ $payment->payment_number }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إيصال الدفع</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.payments.index') }}">مدفوعاتي</a></li>
                            <li class="breadcrumb-item active">{{ $payment->payment_number }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="bi bi-printer me-1"></i>طباعة الإيصال
                    </button>
                    <a href="{{ route('student.payments.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-1"></i>رجوع
                    </a>
                </div>
            </div>

            <!-- Receipt Card -->
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-lg border-0" id="receipt">
                        <!-- Receipt Header -->
                        <div class="card-header bg-success text-white text-center py-4">
                            <h3 class="mb-1">إيصال دفع</h3>
                            <h5 class="mb-0">أكاديمية كلاودسوفت</h5>
                        </div>

                        <div class="card-body p-4">
                            <!-- Payment Status Badge -->
                            <div class="text-center mb-4">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-warning text-dark',
                                        'completed' => 'bg-success',
                                        'failed' => 'bg-danger',
                                        'cancelled' => 'bg-secondary',
                                        'refunded' => 'bg-info'
                                    ];
                                    $statusLabels = [
                                        'pending' => 'معلقة',
                                        'completed' => 'مكتملة',
                                        'failed' => 'فاشلة',
                                        'cancelled' => 'ملغاة',
                                        'refunded' => 'مستردة'
                                    ];
                                @endphp
                                <span class="badge {{ $statusColors[$payment->status] ?? 'bg-secondary' }} fs-5 px-4 py-2">
                                    {{ $statusLabels[$payment->status] ?? $payment->status }}
                                </span>
                            </div>

                            <!-- Payment Amount -->
                            <div class="text-center bg-light rounded p-4 mb-4">
                                <p class="text-muted mb-2">المبلغ المدفوع</p>
                                <h1 class="text-success fw-bold mb-0">${{ number_format($payment->amount, 2) }}</h1>
                            </div>

                            <!-- Payment Details -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted small">رقم الإيصال</label>
                                        <p class="fw-bold mb-0">{{ $payment->payment_number }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted small">تاريخ الدفع</label>
                                        <p class="fw-bold mb-0">{{ $payment->payment_date->format('Y-m-d') }}</p>
                                    </div>
                                    @if($payment->paymentMethod)
                                        <div class="mb-3">
                                            <label class="text-muted small">طريقة الدفع</label>
                                            <p class="fw-bold mb-0">
                                                <i class="bi bi-credit-card me-1"></i>{{ $payment->paymentMethod->name }}
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    @if($payment->invoice)
                                        <div class="mb-3">
                                            <label class="text-muted small">رقم الفاتورة</label>
                                            <p class="fw-bold mb-0">
                                                <a href="{{ route('student.invoices.show', $payment->invoice_id) }}" class="text-primary">
                                                    {{ $payment->invoice->invoice_number }}
                                                </a>
                                            </p>
                                        </div>
                                    @endif
                                    @if($payment->reference_number)
                                        <div class="mb-3">
                                            <label class="text-muted small">رقم المرجع</label>
                                            <p class="fw-bold mb-0">{{ $payment->reference_number }}</p>
                                        </div>
                                    @endif
                                    <div class="mb-3">
                                        <label class="text-muted small">الطالب</label>
                                        <p class="fw-bold mb-0">{{ Auth::user()->name }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice Items (if available) -->
                            @if($payment->invoice && $payment->invoice->items->count() > 0)
                                <hr class="my-4">
                                <h6 class="fw-bold mb-3">تفاصيل الفاتورة</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>البند</th>
                                                <th class="text-end">المبلغ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($payment->invoice->items as $item)
                                                <tr>
                                                    <td>
                                                        {{ $item->description }}
                                                        @if($item->campEnrollment && $item->campEnrollment->camp)
                                                            <br><small class="text-muted">
                                                                <i class="bi bi-patch-check me-1"></i>{{ $item->campEnrollment->camp->name }}
                                                            </small>
                                                        @endif
                                                    </td>
                                                    <td class="text-end">${{ number_format($item->total_price, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="border-top">
                                            <tr>
                                                <td><strong>إجمالي الفاتورة:</strong></td>
                                                <td class="text-end"><strong>${{ number_format($payment->invoice->total_amount, 2) }}</strong></td>
                                            </tr>
                                            <tr class="table-success">
                                                <td><strong>المبلغ المدفوع سابقاً:</strong></td>
                                                <td class="text-end"><strong>${{ number_format($payment->invoice->paid_amount - $payment->amount, 2) }}</strong></td>
                                            </tr>
                                            <tr class="table-info">
                                                <td><strong>هذه الدفعة:</strong></td>
                                                <td class="text-end"><strong>${{ number_format($payment->amount, 2) }}</strong></td>
                                            </tr>
                                            <tr class="table-warning">
                                                <td><strong>المبلغ المتبقي:</strong></td>
                                                <td class="text-end"><strong>${{ number_format($payment->invoice->remaining_amount, 2) }}</strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @endif

                            @if($payment->notes)
                                <hr class="my-4">
                                <div class="alert alert-info mb-0">
                                    <strong>ملاحظات:</strong> {{ $payment->notes }}
                                </div>
                            @endif

                            @if($payment->cancellation_reason)
                                <hr class="my-4">
                                <div class="alert alert-danger mb-0">
                                    <strong>سبب الإلغاء:</strong> {{ $payment->cancellation_reason }}
                                </div>
                            @endif

                            @if($payment->refund_reason)
                                <hr class="my-4">
                                <div class="alert alert-warning mb-0">
                                    <strong>سبب الاسترداد:</strong> {{ $payment->refund_reason }}
                                </div>
                            @endif

                            <!-- Receipt Footer -->
                            <hr class="my-4">
                            <div class="text-center text-muted small">
                                <p class="mb-1">تم إنشاء هذا الإيصال في: {{ $payment->created_at->format('Y-m-d H:i:s') }}</p>
                                <p class="mb-0">هذا إيصال رسمي من أكاديمية كلاودسوفت</p>
                            </div>
                        </div>

                        <!-- Signature Area (for print) -->
                        <div class="card-footer bg-light text-center d-print-block d-none">
                            <div class="row mt-5">
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

@section('styles')
<style>
    @media print {
        .page-header-breadcrumb,
        .btn,
        .breadcrumb {
            display: none !important;
        }

        .card {
            border: 2px solid #000 !important;
            box-shadow: none !important;
        }

        body {
            background: white !important;
        }

        .main-content {
            padding: 0 !important;
        }
    }
</style>
@stop
