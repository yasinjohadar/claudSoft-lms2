@extends('student.layouts.master')

@section('page-title')
    الفواتير غير المدفوعة
@stop

@section('css')
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">الفواتير غير المدفوعة</h5>
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
                                            <th scope="col">رقم الفاتورة</th>
                                            <th scope="col">تاريخ الاستحقاق</th>
                                            <th scope="col">المبلغ الإجمالي</th>
                                            <th scope="col">المدفوع</th>
                                            <th scope="col">المتبقي</th>
                                            <th scope="col">الأيام المتبقية</th>
                                            <th scope="col">العمليات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($unpaidInvoices as $invoice)
                                            <tr>
                                                <th scope="row">{{ $loop->iteration }}</th>
                                                <td>
                                                    <a href="{{ route('student.invoices.show', $invoice->id) }}" class="text-decoration-none">
                                                        {{ $invoice->invoice_number }}
                                                    </a>
                                                </td>
                                                <td>{{ $invoice->due_date?->format('Y-m-d') }}</td>
                                                <td>${{ number_format($invoice->total_amount, 2) }}</td>
                                                <td>${{ number_format($invoice->paid_amount, 2) }}</td>
                                                <td class="text-danger fw-bold">${{ number_format($invoice->remaining_amount, 2) }}</td>
                                                <td>
                                                    @php
                                                        $daysRemaining = $invoice->due_date ? now()->diffInDays($invoice->due_date, false) : null;
                                                    @endphp
                                                    @if($daysRemaining !== null)
                                                        @if($daysRemaining < 0)
                                                            <span class="badge bg-danger">متأخرة {{ abs($daysRemaining) }} يوم</span>
                                                        @elseif($daysRemaining == 0)
                                                            <span class="badge bg-warning">تستحق اليوم</span>
                                                        @else
                                                            <span class="badge bg-info">{{ $daysRemaining }} يوم</span>
                                                        @endif
                                                    @endif
                                                </td>
                                                <td>
                                                    <a class="btn btn-info btn-sm" href="{{ route('student.invoices.show', $invoice->id) }}" title="عرض التفاصيل">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-success fw-bold">لا توجد فواتير غير مدفوعة</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
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
