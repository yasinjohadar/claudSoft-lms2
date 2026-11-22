@extends('admin.layouts.master')

@section('page-title')
    الفواتير المسددة بالكامل
@stop

@section('css')
<style>
    .table-responsive { width: 100%; overflow-x: auto; }
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
                    <h5 class="page-title fs-21 mb-1">الفواتير المسددة بالكامل</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">الفواتير</a></li>
                            <li class="breadcrumb-item active">المسددة بالكامل</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0 d-flex gap-2">
                    <a href="{{ route('invoices.due-overdue') }}" class="btn btn-outline-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>المستحقة والمتأخرة
                    </a>
                    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-list me-2"></i>كل الفواتير
                    </a>
                </div>
            </div>

            <div class="mb-4" id="invoices-stats-container">
                @include('admin.pages.invoices.partials.fully-paid-stats', ['stats' => $stats])
            </div>

            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        الفواتير التي سُدّد مبلغها بالكامل
                        <small class="text-muted fw-normal fs-12 ms-2">حسب حالة سداد الفاتورة، وليس حالة الدفعة الواحدة</small>
                    </div>
                </div>

                <div class="card-header py-3">
                    <form id="filters-form" action="{{ route('invoices.fully-paid') }}" method="GET" onsubmit="return false;">
                        <div class="d-flex flex-wrap gap-2 align-items-end">
                            <div style="flex: 2; min-width: 200px;">
                                <label class="form-label mb-1">بحث</label>
                                <input type="text" name="search" id="search" class="form-control form-control-sm"
                                       placeholder="رقم الفاتورة أو اسم/بريد الطالب..." value="{{ request('search') }}">
                            </div>
                            <div style="flex: 1; min-width: 180px;">
                                <label class="form-label mb-1">المعسكر / المجموعة</label>
                                <select name="program" id="program" class="form-select form-select-sm">
                                    <option value="">الكل</option>
                                    @if($campGroups->isNotEmpty())
                                        <optgroup label="معسكرات المجموعات">
                                            @foreach($campGroups as $group)
                                                <option value="group:{{ $group->id }}" @selected(request('program') === 'group:'.$group->id)>{{ $group->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                    @if($trainingCamps->isNotEmpty())
                                        <optgroup label="المعسكرات التدريبية">
                                            @foreach($trainingCamps as $camp)
                                                <option value="camp:{{ $camp->id }}" @selected(request('program') === 'camp:'.$camp->id)>{{ $camp->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                </select>
                            </div>
                            <div style="flex: 1; min-width: 140px;">
                                <label class="form-label mb-1">تاريخ الدفع من</label>
                                <input type="date" name="from_date" id="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
                            </div>
                            <div style="flex: 1; min-width: 140px;">
                                <label class="form-label mb-1">تاريخ الدفع إلى</label>
                                <input type="date" name="to_date" id="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                            </div>
                            <div style="min-width: 110px;">
                                <a href="{{ route('invoices.fully-paid') }}" class="btn btn-outline-secondary btn-sm w-100">
                                    <i class="fas fa-redo me-1"></i>إعادة تعيين
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-nowrap mb-0">
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
                                    <th scope="col">مبلغ الطالب</th>
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

            <div id="invoices-modals-container">
                @include('admin.pages.invoices.partials.table-modals')
            </div>
        </div>
    </div>
@stop

@section('script')
<script>
    (function () {
        let filterTimeout;
        let isLoading = false;

        function formatCountupNumber(value, withDecimals) {
            if (withDecimals) {
                return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);
            }
            return new Intl.NumberFormat('ar-EG').format(Math.round(value));
        }

        function initCountup(root) {
            root.querySelectorAll('[data-countup]').forEach(function (el) {
                const target = parseFloat(el.dataset.countup || '0');
                const prefix = el.dataset.countupPrefix || '';
                const decimals = el.dataset.countupDecimals === '2';
                const start = performance.now();
                function step(now) {
                    const progress = Math.min((now - start) / 800, 1);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    el.textContent = prefix + formatCountupNumber(target * eased, decimals);
                    if (progress < 1) requestAnimationFrame(step);
                }
                requestAnimationFrame(step);
            });
        }

        function loadInvoices(page) {
            if (isLoading) return;
            isLoading = true;

            const tableBody = document.getElementById('invoices-table-body');
            tableBody.innerHTML = '<tr><td colspan="12" class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">جاري التحميل...</span></div></td></tr>';

            const params = new URLSearchParams({
                search: document.getElementById('search').value,
                program: document.getElementById('program').value,
                from_date: document.getElementById('from_date').value,
                to_date: document.getElementById('to_date').value,
                page: String(page || 1),
            });

            fetch('{{ route("invoices.fully-paid") }}?' + params.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            })
                .then(function (r) { if (!r.ok) throw new Error(); return r.json(); })
                .then(function (data) {
                    const stats = document.getElementById('invoices-stats-container');
                    stats.innerHTML = data.stats;
                    initCountup(stats);
                    tableBody.innerHTML = data.table;
                    document.getElementById('invoices-pagination').innerHTML = data.pagination || '';
                    if (data.modals) document.getElementById('invoices-modals-container').innerHTML = data.modals;
                })
                .catch(function () {
                    tableBody.innerHTML = '<tr><td colspan="12" class="text-center py-5"><div class="alert alert-danger mb-0">حدث خطأ أثناء تحميل الفواتير</div></td></tr>';
                })
                .finally(function () { isLoading = false; });
        }

        document.addEventListener('DOMContentLoaded', function () {
            initCountup(document);

            document.getElementById('search').addEventListener('keyup', function () {
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(function () { loadInvoices(1); }, 500);
            });

            ['program', 'from_date', 'to_date'].forEach(function (id) {
                document.getElementById(id).addEventListener('change', function () { loadInvoices(1); });
            });

            document.addEventListener('click', function (e) {
                const link = e.target.closest('#invoices-pagination .pagination a');
                if (!link) return;
                e.preventDefault();
                loadInvoices(new URL(link.href).searchParams.get('page') || 1);
            });
        });
    })();
</script>
@stop
