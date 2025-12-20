@extends('student.layouts.master')

@section('page-title')
   الفاتورة {{ $invoice->invoice_number }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تفاصيل الفاتورة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.invoices.index') }}">فواتيري</a></li>
                            <li class="breadcrumb-item active">{{ $invoice->invoice_number }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="bi bi-printer me-1"></i>طباعة
                    </button>
                </div>
            </div>

            <!-- Invoice Card -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">الفاتورة {{ $invoice->invoice_number }}</h5>
                                @php
                                    $statusColors = [
                                        'issued' => 'bg-info',
                                        'partial' => 'bg-warning text-dark',
                                        'paid' => 'bg-success',
                                        'cancelled' => 'bg-danger'
                                    ];
                                    $statusLabels = [
                                        'issued' => 'صادرة',
                                        'partial' => 'مدفوعة جزئياً',
                                        'paid' => 'مدفوعة',
                                        'cancelled' => 'ملغاة'
                                    ];
                                @endphp
                                <span class="badge {{ $statusColors[$invoice->status] ?? 'bg-secondary' }}">
                                    {{ $statusLabels[$invoice->status] ?? $invoice->status }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Invoice Info -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>تاريخ الإصدار:</strong> {{ $invoice->issue_date->format('Y-m-d') }}</p>
                                    <p class="mb-2"><strong>تاريخ الاستحقاق:</strong> {{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '-' }}</p>
                                    @if($invoice->is_overdue)
                                        <span class="badge bg-danger">فاتورة متأخرة</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Items -->
                            <h6 class="fw-bold mb-3">بنود الفاتورة:</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>الوصف</th>
                                            <th>الكمية</th>
                                            <th>سعر الوحدة</th>
                                            <th>المجموع</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($invoice->items as $item)
                                            <tr>
                                                <td>
                                                    {{ $item->description }}
                                                    @if($item->campEnrollment && $item->campEnrollment->camp)
                                                        <br><small class="text-muted">
                                                            <i class="bi bi-patch-check me-1"></i>{{ $item->campEnrollment->camp->name }}
                                                        </small>
                                                    @endif
                                                </td>
                                                <td>{{ $item->quantity }}</td>
                                                <td>${{ number_format($item->unit_price, 2) }}</td>
                                                <td>${{ number_format($item->total_price, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>المجموع الإجمالي:</strong></td>
                                            <td><strong>${{ number_format($invoice->total_amount, 2) }}</strong></td>
                                        </tr>
                                        <tr class="table-success">
                                            <td colspan="3" class="text-end"><strong>المبلغ المدفوع:</strong></td>
                                            <td><strong>${{ number_format($invoice->paid_amount, 2) }}</strong></td>
                                        </tr>
                                        <tr class="table-danger">
                                            <td colspan="3" class="text-end"><strong>المبلغ المتبقي:</strong></td>
                                            <td><strong>${{ number_format($invoice->remaining_amount, 2) }}</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            @if($invoice->notes)
                                <div class="alert alert-info mt-3">
                                    <strong>ملاحظات:</strong> {{ $invoice->notes }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Payments Sidebar -->
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">المدفوعات</h6>
                        </div>
                        <div class="card-body">
                            @if($invoice->payments->count() > 0)
                                @foreach($invoice->payments as $payment)
                                    <div class="border-bottom pb-3 mb-3">
                                        <div class="d-flex justify-content-between mb-2">
                                            <small class="text-muted">{{ $payment->payment_number }}</small>
                                            <strong class="text-success">${{ number_format($payment->amount, 2) }}</strong>
                                        </div>
                                        <p class="mb-1 small">
                                            <i class="bi bi-calendar me-1"></i>{{ $payment->payment_date->format('Y-m-d') }}
                                        </p>
                                        @if($payment->paymentMethod)
                                            <p class="mb-0 small text-muted">
                                                <i class="bi bi-credit-card me-1"></i>{{ $payment->paymentMethod->name }}
                                            </p>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted text-center">لا توجد مدفوعات بعد</p>
                            @endif
                        </div>
                    </div>

                    @if($invoice->remaining_amount > 0 && $invoice->status !== 'cancelled')
                        <div class="alert alert-warning mt-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>تنبيه:</strong> لديك مبلغ متبقي ${{ number_format($invoice->remaining_amount, 2) }} يجب دفعه
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@stop
