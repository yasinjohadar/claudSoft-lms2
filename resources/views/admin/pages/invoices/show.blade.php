@extends('admin.layouts.master')

@section('page-title')
    تفاصيل الفاتورة {{ $invoice->invoice_number }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تفاصيل الفاتورة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">الفواتير</a></li>
                            <li class="breadcrumb-item active">{{ $invoice->invoice_number }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    @if($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
                        <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>إضافة دفعة
                        </a>
                    @endif
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fas fa-print me-2"></i>طباعة
                    </button>
                </div>
            </div>

            <!-- Invoice Details Card -->
            <div class="row">
                <div class="col-xl-8">
                    <div class="card custom-card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">الفاتورة {{ $invoice->invoice_number }}</h5>
                        </div>
                        <div class="card-body">
                            <!-- Student Info -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="fw-bold">معلومات الطالب:</h6>
                                    <p class="mb-1"><strong>الاسم:</strong> {{ $invoice->student->name }}</p>
                                    <p class="mb-1"><strong>البريد:</strong> {{ $invoice->student->email }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold">معلومات الفاتورة:</h6>
                                    <p class="mb-1"><strong>تاريخ الإصدار:</strong> {{ $invoice->issue_date->format('Y-m-d') }}</p>
                                    <p class="mb-1"><strong>تاريخ الاستحقاق:</strong> {{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '-' }}</p>
                                </div>
                            </div>

                            <!-- Invoice Items -->
                            <h6 class="fw-bold mb-3">البنود:</h6>
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
                                                    @if($item->campEnrollment)
                                                        <br><small class="text-muted">المعسكر: {{ $item->campEnrollment->camp->name }}</small>
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
                                            <td colspan="3" class="text-end"><strong>المجموع:</strong></td>
                                            <td><strong>${{ number_format($invoice->total_amount, 2) }}</strong></td>
                                        </tr>
                                        <tr class="table-success">
                                            <td colspan="3" class="text-end"><strong>المدفوع:</strong></td>
                                            <td><strong>${{ number_format($invoice->paid_amount, 2) }}</strong></td>
                                        </tr>
                                        <tr class="table-danger">
                                            <td colspan="3" class="text-end"><strong>المتبقي:</strong></td>
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

                <div class="col-xl-4">
                    <!-- Payments History -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <h6 class="mb-0">المدفوعات</h6>
                        </div>
                        <div class="card-body">
                            @forelse($invoice->payments as $payment)
                                <div class="border-bottom pb-3 mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <strong>{{ $payment->payment_number }}</strong>
                                        <span class="badge bg-success">${{ number_format($payment->amount, 2) }}</span>
                                    </div>
                                    <p class="mb-1 small text-muted">
                                        <i class="fas fa-calendar me-1"></i>{{ $payment->payment_date->format('Y-m-d') }}
                                    </p>
                                    <p class="mb-0 small text-muted">
                                        <i class="fas fa-credit-card me-1"></i>{{ $payment->paymentMethod->name ?? 'غير محدد' }}
                                    </p>
                                </div>
                            @empty
                                <p class="text-muted text-center">لا توجد مدفوعات</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop
