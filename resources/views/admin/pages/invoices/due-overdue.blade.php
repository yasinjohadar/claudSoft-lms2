@extends('admin.layouts.master')

@section('page-title')
    الفواتير المستحقة والمتأخرة
@stop

@section('css')
<style>
    .invoice-status-badge { font-size: 0.75rem; padding: 0.35rem 0.65rem; }
    .overdue-badge { animation: pulse 2s infinite; }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }
    .table-responsive { width: 100%; overflow-x: auto; }
    .table { width: 100%; min-width: 100%; table-layout: auto; }
    .table thead th, .table tbody td { white-space: nowrap; }
    #filters-form .form-label { font-size: 0.875rem; margin-bottom: 0.25rem; }
    #filters-form .form-control, #filters-form .form-select { font-size: 0.875rem; }
</style>
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">الفواتير المستحقة والمتأخرة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">الفواتير</a></li>
                            <li class="breadcrumb-item active">المستحقة والمتأخرة</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0 d-flex gap-2">
                    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-list me-2"></i>كل الفواتير
                    </a>
                    <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>إنشاء فاتورة جديدة
                    </a>
                </div>
            </div>

            <div class="row mb-4" id="invoices-stats-container">
                @include('admin.pages.invoices.partials.stats', ['stats' => $stats])
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">قائمة الفواتير غير المسددة</div>
                        </div>

                        <div class="card-header py-3">
                            <form id="filters-form" action="{{ route('invoices.due-overdue') }}" method="GET">
                                <div class="d-flex flex-wrap gap-2 align-items-end">
                                    <div style="flex: 1; min-width: 180px;">
                                        <label class="form-label mb-1">بحث</label>
                                        <input type="text" name="search" id="search" class="form-control form-control-sm"
                                               placeholder="رقم الفاتورة أو الطالب..." value="{{ request('search') }}">
                                    </div>
                                    <div style="flex: 1; min-width: 130px;">
                                        <label class="form-label mb-1">الحالة</label>
                                        <select name="status" id="status" class="form-select form-select-sm">
                                            <option value="">الكل</option>
                                            <option value="issued" {{ request('status') == 'issued' ? 'selected' : '' }}>صادرة</option>
                                            <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>جزئياً</option>
                                        </select>
                                    </div>
                                    <div style="flex: 1; min-width: 150px;">
                                        <label class="form-label mb-1">نوع الاستحقاق</label>
                                        <select name="due_type" id="due_type" class="form-select form-select-sm">
                                            <option value="">الكل</option>
                                            <option value="due" {{ request('due_type') == 'due' ? 'selected' : '' }}>مستحقة</option>
                                            <option value="overdue" {{ request('due_type') == 'overdue' ? 'selected' : '' }}>متأخرة</option>
                                        </select>
                                    </div>
                                    <div style="flex: 1; min-width: 140px;">
                                        <label class="form-label mb-1">المعسكر</label>
                                        <select name="camp_id" id="camp_id" class="form-select form-select-sm">
                                            <option value="">الكل</option>
                                            @foreach($trainingCamps as $camp)
                                                <option value="{{ $camp->id }}" {{ request('camp_id') == $camp->id ? 'selected' : '' }}>
                                                    {{ $camp->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div style="flex: 1; min-width: 130px;">
                                        <label class="form-label mb-1">من تاريخ</label>
                                        <input type="date" name="from_date" id="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
                                    </div>
                                    <div style="flex: 1; min-width: 130px;">
                                        <label class="form-label mb-1">إلى تاريخ</label>
                                        <input type="date" name="to_date" id="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                                    </div>
                                    <div style="min-width: 110px;">
                                        <label class="form-label mb-1 d-block">&nbsp;</label>
                                        <a href="{{ route('invoices.due-overdue') }}" class="btn btn-outline-secondary btn-sm w-100">
                                            <i class="fas fa-redo me-1"></i>إعادة تعيين
                                        </a>
                                    </div>
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
                                            <th scope="col">المعسكر المرتبط</th>
                                            <th scope="col">تاريخ الإصدار</th>
                                            <th scope="col">الاستحقاق</th>
                                            <th scope="col">المبلغ الإجمالي</th>
                                            <th scope="col">المدفوع</th>
                                            <th scope="col">المتبقي</th>
                                            <th scope="col">الحالة</th>
                                            <th scope="col">العمليات</th>
                                        </tr>
                                    </thead>
                                    <tbody id="invoices-table-body">
                                        @include('admin.pages.invoices.partials.table')
                                    </tbody>
                                </table>
                            </div>

                            <div id="invoices-pagination" class="d-flex justify-content-center mt-4">
                                @if($invoices->hasPages())
                                    {{ $invoices->links() }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
<script>
    $(document).ready(function() {
        let filterTimeout;
        let isLoading = false;

        function loadInvoices(page = 1) {
            if (isLoading) return;
            isLoading = true;

            const tableBody = $('#invoices-table-body');
            const pagination = $('#invoices-pagination');
            const statsContainer = $('#invoices-stats-container');

            tableBody.html('<tr><td colspan="11" class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">جاري التحميل...</span></div></td></tr>');

            const formData = {
                search: $('#search').val(),
                status: $('#status').val(),
                due_type: $('#due_type').val(),
                camp_id: $('#camp_id').val(),
                from_date: $('#from_date').val(),
                to_date: $('#to_date').val(),
                page: page
            };

            $.ajax({
                url: '{{ route("invoices.due-overdue") }}',
                method: 'GET',
                data: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.stats) {
                        statsContainer.html(response.stats);
                    }
                    tableBody.html(response.table);
                    pagination.html(response.pagination || '');
                    isLoading = false;
                },
                error: function() {
                    tableBody.html('<tr><td colspan="11" class="text-center py-5"><div class="alert alert-danger">حدث خطأ أثناء تحميل الفواتير</div></td></tr>');
                    isLoading = false;
                }
            });
        }

        $('#search').on('keyup', function() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function() {
                loadInvoices(1);
            }, 500);
        });

        $('#status, #due_type, #camp_id, #from_date, #to_date').on('change', function() {
            loadInvoices(1);
        });

        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            const page = new URL(url).searchParams.get('page') || 1;
            loadInvoices(page);
        });
    });
</script>
@stop
