@extends('admin.layouts.master')

@section('page-title')
    الفواتير
@stop

@section('css')
<style>
    .invoice-status-badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.65rem;
    }
    .overdue-badge {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
</style>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-check-circle me-2"></i>نجح!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-exclamation-circle me-2"></i>خطأ!</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إدارة الفواتير</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">الفواتير</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>إنشاء فاتورة جديدة
                    </a>
                </div>
            </div>

            <!-- Start::row-1 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">قائمة الفواتير</div>
                        </div>

                        <div class="card-header">
                            <form action="{{ route('invoices.index') }}" method="GET" class="row g-3">
                                <div class="col-md-3">
                                    <input type="text" name="search" class="form-control"
                                           placeholder="بحث برقم الفاتورة أو اسم الطالب..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2">
                                    <select name="status" class="form-select">
                                        <option value="">جميع الحالات</option>
                                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                                        <option value="issued" {{ request('status') == 'issued' ? 'selected' : '' }}>صادرة</option>
                                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>مدفوعة جزئياً</option>
                                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>مدفوعة</option>
                                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغاة</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="date" name="from_date" class="form-control"
                                           placeholder="من تاريخ" value="{{ request('from_date') }}">
                                </div>
                                <div class="col-md-2">
                                    <input type="date" name="to_date" class="form-control"
                                           placeholder="إلى تاريخ" value="{{ request('to_date') }}">
                                </div>
                                <div class="col-md-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="overdue" value="1"
                                               id="overdueCheck" {{ request('overdue') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="overdueCheck">
                                            متأخرة
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-secondary w-100">
                                        <i class="fas fa-search me-1"></i>بحث
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle table-nowrap mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width: 50px;">#</th>
                                            <th scope="col">رقم الفاتورة</th>
                                            <th scope="col">الطالب</th>
                                            <th scope="col">تاريخ الإصدار</th>
                                            <th scope="col">الاستحقاق</th>
                                            <th scope="col">المبلغ الإجمالي</th>
                                            <th scope="col">المدفوع</th>
                                            <th scope="col">المتبقي</th>
                                            <th scope="col">الحالة</th>
                                            <th scope="col">العمليات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($invoices as $invoice)
                                            <tr>
                                                <td>{{ $loop->iteration + ($invoices->currentPage() - 1) * $invoices->perPage() }}</td>

                                                <td>
                                                    <strong>{{ $invoice->invoice_number }}</strong>
                                                </td>

                                                <td>
                                                    <div>
                                                        <strong>{{ $invoice->student->name }}</strong>
                                                        <br><small class="text-muted">{{ $invoice->student->email }}</small>
                                                    </div>
                                                </td>

                                                <td>{{ $invoice->issue_date->format('Y-m-d') }}</td>

                                                <td>
                                                    {{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '-' }}
                                                    @if($invoice->is_overdue)
                                                        <br><span class="badge bg-danger overdue-badge">متأخرة</span>
                                                    @endif
                                                </td>

                                                <td><strong>${{ number_format($invoice->total_amount, 2) }}</strong></td>
                                                <td class="text-success">${{ number_format($invoice->paid_amount, 2) }}</td>
                                                <td class="text-danger">${{ number_format($invoice->remaining_amount, 2) }}</td>

                                                <td>
                                                    @php
                                                        $statusColors = [
                                                            'draft' => 'bg-secondary',
                                                            'issued' => 'bg-info',
                                                            'partial' => 'bg-warning text-dark',
                                                            'paid' => 'bg-success',
                                                            'cancelled' => 'bg-danger',
                                                            'refunded' => 'bg-dark'
                                                        ];
                                                        $statusLabels = [
                                                            'draft' => 'مسودة',
                                                            'issued' => 'صادرة',
                                                            'partial' => 'جزئياً',
                                                            'paid' => 'مدفوعة',
                                                            'cancelled' => 'ملغاة',
                                                            'refunded' => 'مستردة'
                                                        ];
                                                    @endphp
                                                    <span class="badge {{ $statusColors[$invoice->status] ?? 'bg-secondary' }} invoice-status-badge">
                                                        {{ $statusLabels[$invoice->status] ?? $invoice->status }}
                                                    </span>
                                                </td>

                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('invoices.show', $invoice->id) }}"
                                                           class="btn btn-sm btn-info" title="عرض التفاصيل">
                                                            <i class="fas fa-eye"></i>
                                                        </a>

                                                        @if($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
                                                            <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}"
                                                               class="btn btn-sm btn-success" title="إضافة دفعة">
                                                                <i class="fas fa-plus"></i>
                                                            </a>
                                                        @endif

                                                        @if($invoice->status !== 'cancelled' && $invoice->status !== 'paid')
                                                            <button type="button" class="btn btn-sm btn-danger"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#cancelModal{{ $invoice->id }}"
                                                                    title="إلغاء">
                                                                <i class="fas fa-ban"></i>
                                                            </button>
                                                        @endif
                                                    </div>

                                                    <!-- Cancel Modal -->
                                                    <div class="modal fade" id="cancelModal{{ $invoice->id }}" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">إلغاء الفاتورة</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form action="{{ route('invoices.cancel', $invoice->id) }}" method="POST">
                                                                    @csrf
                                                                    <div class="modal-body">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">سبب الإلغاء (اختياري)</label>
                                                                            <textarea class="form-control" name="reason" rows="3"
                                                                                      placeholder="أدخل سبب الإلغاء..."></textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                                                        <button type="submit" class="btn btn-danger">إلغاء الفاتورة</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center py-5">
                                                    <div class="text-muted">
                                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                                        <h5>لا توجد فواتير</h5>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($invoices->hasPages())
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $invoices->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

@section('script')
<script>
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
</script>
@stop
