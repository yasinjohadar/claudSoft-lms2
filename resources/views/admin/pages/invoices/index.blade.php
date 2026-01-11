@extends('admin.layouts.master')

@section('page-title')
    الفواتير
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
    .select2-container {
        width: 100% !important;
    }
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        padding-right: 10px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }
    .table {
        width: 100%;
        min-width: 100%;
        table-layout: auto;
    }
    .table thead th {
        white-space: nowrap;
    }
    .table tbody td {
        white-space: nowrap;
    }
    /* Filters styling */
    #filters-form .form-label {
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }
    #filters-form .form-control,
    #filters-form .form-select {
        font-size: 0.875rem;
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

                        <div class="card-header py-3">
                            <form id="filters-form" action="{{ route('invoices.index') }}" method="GET">
                                <div class="d-flex flex-wrap gap-2 align-items-end">
                                    <div style="flex: 1; min-width: 150px;">
                                        <label class="form-label mb-1">بحث</label>
                                        <input type="text" name="search" id="search" class="form-control form-control-sm"
                                               placeholder="رقم الفاتورة أو الطالب..." value="{{ request('search') }}">
                                    </div>
                                    <div style="flex: 1; min-width: 120px;">
                                        <label class="form-label mb-1">الحالة</label>
                                        <select name="status" id="status" class="form-select form-select-sm">
                                            <option value="">الكل</option>
                                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                                            <option value="issued" {{ request('status') == 'issued' ? 'selected' : '' }}>صادرة</option>
                                            <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>جزئياً</option>
                                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>مدفوعة</option>
                                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغاة</option>
                                            <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>مستردة</option>
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
                                        <input type="date" name="from_date" id="from_date" class="form-control form-control-sm"
                                               value="{{ request('from_date') }}">
                                    </div>
                                    <div style="flex: 1; min-width: 130px;">
                                        <label class="form-label mb-1">إلى تاريخ</label>
                                        <input type="date" name="to_date" id="to_date" class="form-control form-control-sm"
                                               value="{{ request('to_date') }}">
                                    </div>
                                    <div style="min-width: 100px;">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="overdue" value="1"
                                                   id="overdueCheck" {{ request('overdue') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="overdueCheck">
                                                متأخرة
                                            </label>
                                        </div>
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // No Select2 - using native select for simplicity

        let filterTimeout;
        let isLoading = false;

        // Function to load invoices via AJAX
        function loadInvoices(page = 1) {
            if (isLoading) return;
            
            isLoading = true;
            const tableBody = $('#invoices-table-body');
            const pagination = $('#invoices-pagination');
            
            // Show loading indicator
            tableBody.html('<tr><td colspan="10" class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">جاري التحميل...</span></div></td></tr>');

            const formData = {
                search: $('#search').val(),
                status: $('#status').val(),
                camp_id: $('#camp_id').val(),
                from_date: $('#from_date').val(),
                to_date: $('#to_date').val(),
                overdue: $('#overdueCheck').is(':checked') ? '1' : '',
                page: page
            };

            $.ajax({
                url: '{{ route("invoices.index") }}',
                method: 'GET',
                data: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    tableBody.html(response.table);
                    pagination.html(response.pagination || '');
                    isLoading = false;
                },
                error: function(xhr) {
                    console.error('Error loading invoices:', xhr);
                    tableBody.html('<tr><td colspan="10" class="text-center py-5"><div class="alert alert-danger">حدث خطأ أثناء تحميل الفواتير</div></td></tr>');
                    isLoading = false;
                }
            });
        }

        // Handle filter changes with debounce for search
        $('#search').on('keyup', function() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function() {
                loadInvoices(1);
            }, 500);
        });

        // Handle other filter changes immediately
        $('#status, #camp_id, #from_date, #to_date, #overdueCheck').on('change', function() {
            loadInvoices(1);
        });

        // Handle pagination clicks
        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            const page = new URL(url).searchParams.get('page') || 1;
            loadInvoices(page);
        });

        // Auto-hide alerts
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    });
</script>
@stop
