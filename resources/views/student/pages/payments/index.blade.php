@extends('student.layouts.master')

@section('page-title')
    مدفوعاتي
@stop

@section('css')
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">مدفوعاتي</h5>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle table-nowrap mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">رقم الدفع</th>
                                            <th scope="col">رقم الفاتورة</th>
                                            <th scope="col">المبلغ</th>
                                            <th scope="col">طريقة الدفع</th>
                                            <th scope="col">التاريخ</th>
                                            <th scope="col">الحالة</th>
                                            <th scope="col">العمليات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($payments as $payment)
                                            <tr>
                                                <th scope="row">{{ $loop->iteration }}</th>
                                                <td>
                                                    <a href="{{ route('student.payments.show', $payment->id) }}" class="text-decoration-none">
                                                        {{ $payment->payment_number }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <a href="{{ route('student.invoices.show', $payment->invoice_id) }}" class="text-decoration-none">
                                                        {{ $payment->invoice->invoice_number }}
                                                    </a>
                                                </td>
                                                <td>${{ number_format($payment->amount, 2) }}</td>
                                                <td>{{ $payment->paymentMethod->name }}</td>
                                                <td>{{ $payment->payment_date?->format('Y-m-d') }}</td>
                                                <td>
                                                    @if ($payment->status === 'pending')
                                                        <span class="badge bg-warning">قيد الانتظار</span>
                                                    @elseif($payment->status === 'completed')
                                                        <span class="badge bg-success">مكتملة</span>
                                                    @elseif($payment->status === 'failed')
                                                        <span class="badge bg-danger">فشلت</span>
                                                    @elseif($payment->status === 'cancelled')
                                                        <span class="badge bg-secondary">ملغاة</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a class="btn btn-info btn-sm me-1" href="{{ route('student.payments.show', $payment->id) }}" title="عرض التفاصيل">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </a>

                                                    @if($payment->status === 'completed' && $payment->receipt_number)
                                                        <a class="btn btn-success btn-sm" href="{{ route('student.payments.receipt', $payment->id) }}" title="تحميل الإيصال">
                                                            <i class="fa-solid fa-download"></i>
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-danger fw-bold">لا توجد مدفوعات متاحة</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>

                                <div class="mt-3">
                                    {{ $payments->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
@stop
