@extends('student.layouts.master')

@section('page-title')
   مدفوعاتي
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">
                        <i class="bi bi-credit-card me-2"></i>مدفوعاتي
                    </h5>
                </div>
            </div>

            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card bg-success-gradient text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="mb-2">عدد المدفوعات</p>
                                    <h3 class="mb-0">{{ $stats['total_payments'] }}</h3>
                                </div>
                                <div>
                                    <i class="fas fa-receipt fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-primary-gradient text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="mb-2">إجمالي المدفوع</p>
                                    <h3 class="mb-0">${{ number_format($stats['total_paid'], 2) }}</h3>
                                </div>
                                <div>
                                    <i class="fas fa-dollar-sign fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payments List -->
            @if($payments->count() > 0)
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>رقم الدفعة</th>
                                        <th>الفاتورة</th>
                                        <th>المبلغ</th>
                                        <th>الطريقة</th>
                                        <th>التاريخ</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $payment)
                                        <tr>
                                            <td>{{ $loop->iteration + ($payments->currentPage() - 1) * $payments->perPage() }}</td>
                                            <td><strong>{{ $payment->payment_number }}</strong></td>
                                            <td>
                                                <a href="{{ route('student.invoices.show', $payment->invoice_id) }}">
                                                    {{ $payment->invoice->invoice_number }}
                                                </a>
                                            </td>
                                            <td class="text-success"><strong>${{ number_format($payment->amount, 2) }}</strong></td>
                                            <td>
                                                @if($payment->paymentMethod)
                                                    <span class="badge bg-info">{{ $payment->paymentMethod->name }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'completed' => 'bg-success',
                                                        'pending' => 'bg-warning text-dark',
                                                        'failed' => 'bg-danger',
                                                        'cancelled' => 'bg-secondary',
                                                        'refunded' => 'bg-dark'
                                                    ];
                                                    $statusLabels = [
                                                        'completed' => 'مكتملة',
                                                        'pending' => 'قيد الانتظار',
                                                        'failed' => 'فاشلة',
                                                        'cancelled' => 'ملغاة',
                                                        'refunded' => 'مستردة'
                                                    ];
                                                @endphp
                                                <span class="badge {{ $statusColors[$payment->status] ?? 'bg-secondary' }}">
                                                    {{ $statusLabels[$payment->status] ?? $payment->status }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('student.payments.show', $payment->id) }}"
                                                   class="btn btn-sm btn-primary"
                                                   title="عرض الإيصال">
                                                    <i class="bi bi-receipt me-1"></i>عرض الإيصال
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($payments->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $payments->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-credit-card fs-1 text-muted mb-3 d-block"></i>
                        <h5 class="text-muted">لا توجد مدفوعات</h5>
                        <p class="text-muted">لم تقم بأي عملية دفع بعد</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop
