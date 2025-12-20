@extends('student.layouts.master')

@section('page-title')
   فواتيري
@stop

@section('css')
<style>
    .stats-card {
        border-radius: 12px;
        transition: transform 0.3s;
    }
    .stats-card:hover {
        transform: translateY(-5px);
    }
</style>
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">
                        <i class="bi bi-file-invoice me-2"></i>فواتيري
                    </h5>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <div class="card stats-card bg-primary-gradient text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="mb-2">عدد الفواتير</p>
                                    <h3 class="mb-0">{{ $stats['total_invoices'] }}</h3>
                                </div>
                                <div>
                                    <i class="fas fa-file-invoice fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <div class="card stats-card bg-success-gradient text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="mb-2">المبلغ المدفوع</p>
                                    <h3 class="mb-0">${{ number_format($stats['paid_amount'], 2) }}</h3>
                                </div>
                                <div>
                                    <i class="fas fa-check-circle fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <div class="card stats-card bg-danger-gradient text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="mb-2">المبلغ المتبقي</p>
                                    <h3 class="mb-0">${{ number_format($stats['remaining_amount'], 2) }}</h3>
                                </div>
                                <div>
                                    <i class="fas fa-exclamation-circle fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <div class="card stats-card bg-warning-gradient text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="mb-2">فواتير متأخرة</p>
                                    <h3 class="mb-0">{{ $stats['overdue_count'] }}</h3>
                                </div>
                                <div>
                                    <i class="fas fa-clock fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('student.invoices.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <select name="status" class="form-select">
                                <option value="">جميع الحالات</option>
                                <option value="issued" {{ request('status') == 'issued' ? 'selected' : '' }}>صادرة</option>
                                <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>مدفوعة جزئياً</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>مدفوعة</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-funnel me-1"></i>تصفية
                            </button>
                            <a href="{{ route('student.invoices.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i>إعادة تعيين
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Invoices List -->
            @if($invoices->count() > 0)
                <div class="row">
                    @foreach($invoices as $invoice)
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow-sm border-0">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="fw-bold mb-2">{{ $invoice->invoice_number }}</h5>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar me-1"></i>{{ $invoice->issue_date->format('Y-m-d') }}
                                            </small>
                                        </div>
                                        @php
                                            $statusColors = [
                                                'issued' => 'bg-info',
                                                'partial' => 'bg-warning text-dark',
                                                'paid' => 'bg-success',
                                                'cancelled' => 'bg-danger'
                                            ];
                                            $statusLabels = [
                                                'issued' => 'صادرة',
                                                'partial' => 'جزئياً',
                                                'paid' => 'مدفوعة',
                                                'cancelled' => 'ملغاة'
                                            ];
                                        @endphp
                                        <span class="badge {{ $statusColors[$invoice->status] ?? 'bg-secondary' }}">
                                            {{ $statusLabels[$invoice->status] ?? $invoice->status }}
                                        </span>
                                    </div>

                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <small class="text-muted d-block">المبلغ الإجمالي</small>
                                            <strong class="text-primary">${{ number_format($invoice->total_amount, 2) }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block">المدفوع</small>
                                            <strong class="text-success">${{ number_format($invoice->paid_amount, 2) }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block">المتبقي</small>
                                            <strong class="text-danger">${{ number_format($invoice->remaining_amount, 2) }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block">تاريخ الاستحقاق</small>
                                            <strong>{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '-' }}</strong>
                                            @if($invoice->is_overdue)
                                                <br><span class="badge bg-danger">متأخرة</span>
                                            @endif
                                        </div>
                                    </div>

                                    @if($invoice->items->count() > 0)
                                        <div class="mb-3">
                                            <small class="text-muted d-block mb-2">البنود:</small>
                                            @foreach($invoice->items->take(2) as $item)
                                                <small class="d-block">
                                                    <i class="bi bi-check-circle text-success me-1"></i>{{ $item->description }}
                                                </small>
                                            @endforeach
                                            @if($invoice->items->count() > 2)
                                                <small class="text-muted">+ {{ $invoice->items->count() - 2 }} بند آخر</small>
                                            @endif
                                        </div>
                                    @endif

                                    <a href="{{ route('student.invoices.show', $invoice->id) }}" class="btn btn-primary w-100">
                                        <i class="bi bi-eye me-1"></i>عرض التفاصيل
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $invoices->links() }}
                </div>
            @else
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                        <h5 class="text-muted">لا توجد فواتير</h5>
                        <p class="text-muted mb-4">لم يتم إصدار أي فواتير لك بعد</p>
                        <a href="{{ route('student.training-camps.index') }}" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>تصفح المعسكرات المتاحة
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop
