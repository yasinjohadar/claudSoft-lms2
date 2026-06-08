@extends('admin.layouts.master')

@section('page-title')
    الفواتير
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fe fe-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fe fe-alert-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">الفواتير</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-7">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-file-text me-1"></i>
                            إدارة الفواتير
                        </span>
                        <h2 class="group-show-hero__title mb-2">الفواتير والتحصيل</h2>
                        <p class="group-show-hero__desc mb-0">
                            متابعة الفواتير، المبالغ المسددة والمتبقية، والفواتير المتأخرة من لوحة واحدة.
                        </p>
                    </div>
                    <div class="col-lg-5">
                        <div class="group-show-actions">
                            <a href="{{ route('invoices.due-overdue') }}"
                               class="group-show-action group-show-action--warning">
                                <span class="group-show-action__icon"><i class="fe fe-clock"></i></span>
                                <span class="group-show-action__text">المستحقة والمتأخرة</span>
                            </a>
                            <a href="{{ route('invoices.create') }}"
                               class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-plus"></i></span>
                                <span class="group-show-action__text">إنشاء فاتورة جديدة</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div id="invoices-stats-container" class="mb-4">
                @include('admin.pages.invoices.partials.stats', ['stats' => $stats])
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-1">تصفية الفواتير</h4>
                    <p class="fs-12 text-muted mb-0">ابحث برقم الفاتورة أو الطالب، أو فلتر حسب الحالة والمعسكر والتاريخ.</p>
                </div>
                <div class="card-body pt-3">
                    <form id="filters-form" action="{{ route('invoices.index') }}" method="GET" class="group-show-filters mb-0">
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <label class="form-label" for="search">بحث</label>
                                <input type="text" name="search" id="search" class="form-control"
                                       placeholder="رقم الفاتورة أو الطالب..." value="{{ request('search') }}">
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="status">الحالة</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                                    <option value="issued" {{ request('status') == 'issued' ? 'selected' : '' }}>صادرة</option>
                                    <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>جزئياً</option>
                                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>مدفوعة</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغاة</option>
                                    <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>مستردة</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="camp_id">المعسكر</label>
                                <select name="camp_id" id="camp_id" class="form-select">
                                    <option value="">الكل</option>
                                    @foreach($trainingCamps as $camp)
                                        <option value="{{ $camp->id }}" {{ request('camp_id') == $camp->id ? 'selected' : '' }}>
                                            {{ $camp->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="from_date">من تاريخ</label>
                                <input type="date" name="from_date" id="from_date" class="form-control"
                                       value="{{ request('from_date') }}">
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="to_date">إلى تاريخ</label>
                                <input type="date" name="to_date" id="to_date" class="form-control"
                                       value="{{ request('to_date') }}">
                            </div>
                            <div class="col-xl-1 col-lg-2 col-md-6">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="overdue" value="1"
                                           id="overdueCheck" {{ request('overdue') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="overdueCheck">متأخرة</label>
                                </div>
                            </div>
                            <div class="col-xl-12 col-lg-12">
                                <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fe fe-rotate-cw me-1"></i>إعادة تعيين
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                    <h6 class="group-show-members-card__title mb-0">
                        قائمة الفواتير
                        <span class="group-show-members-card__count">{{ $invoices->total() }}</span>
                    </h6>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap dashboard-table mb-0">
                            <thead>
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
                return new Intl.NumberFormat('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }).format(value);
            }
            return new Intl.NumberFormat('ar-EG').format(Math.round(value));
        }

        function initInvoiceCountup(container) {
            const root = container || document;
            root.querySelectorAll('[data-countup]').forEach(function (el) {
                const target = parseFloat(el.dataset.countup || '0');
                const prefix = el.dataset.countupPrefix || '';
                const suffix = el.dataset.countupSuffix || '';
                const decimals = el.dataset.countupDecimals === '2' ? 2 : (el.dataset.countupDecimals === '1' ? 1 : 0);
                const duration = 800;
                const start = performance.now();

                function step(now) {
                    const progress = Math.min((now - start) / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    const value = formatCountupNumber(target * eased, decimals > 0);
                    el.textContent = prefix + value + suffix;
                    if (progress < 1) requestAnimationFrame(step);
                }

                requestAnimationFrame(step);
            });
        }

        function loadInvoices(page = 1) {
            if (isLoading) return;

            isLoading = true;
            const tableBody = document.getElementById('invoices-table-body');
            const pagination = document.getElementById('invoices-pagination');
            const statsContainer = document.getElementById('invoices-stats-container');
            const modalsContainer = document.getElementById('invoices-modals-container');

            tableBody.innerHTML = '<tr><td colspan="11" class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">جاري التحميل...</span></div></td></tr>';

            const params = new URLSearchParams({
                search: document.getElementById('search').value,
                status: document.getElementById('status').value,
                camp_id: document.getElementById('camp_id').value,
                from_date: document.getElementById('from_date').value,
                to_date: document.getElementById('to_date').value,
                overdue: document.getElementById('overdueCheck').checked ? '1' : '',
                page: String(page),
            });

            fetch('{{ route("invoices.index") }}?' + params.toString(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            })
                .then(function (response) {
                    if (!response.ok) throw new Error('Network error');
                    return response.json();
                })
                .then(function (data) {
                    if (data.stats) {
                        statsContainer.innerHTML = data.stats;
                        initInvoiceCountup(statsContainer);
                    }
                    tableBody.innerHTML = data.table;
                    pagination.innerHTML = data.pagination || '';
                    if (modalsContainer && data.modals) {
                        modalsContainer.innerHTML = data.modals;
                    }
                })
                .catch(function () {
                    tableBody.innerHTML = '<tr><td colspan="11" class="text-center py-5"><div class="alert alert-danger mb-0">حدث خطأ أثناء تحميل الفواتير</div></td></tr>';
                })
                .finally(function () {
                    isLoading = false;
                });
        }

        document.addEventListener('DOMContentLoaded', function () {
            initInvoiceCountup(document);

            const searchInput = document.getElementById('search');
            if (searchInput) {
                searchInput.addEventListener('keyup', function () {
                    clearTimeout(filterTimeout);
                    filterTimeout = setTimeout(function () {
                        loadInvoices(1);
                    }, 500);
                });
            }

            ['status', 'camp_id', 'from_date', 'to_date', 'overdueCheck'].forEach(function (id) {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('change', function () {
                        loadInvoices(1);
                    });
                }
            });

            document.addEventListener('click', function (e) {
                const link = e.target.closest('#invoices-pagination .pagination a');
                if (!link) return;
                e.preventDefault();
                const url = new URL(link.href);
                const page = url.searchParams.get('page') || '1';
                loadInvoices(page);
            });

            setTimeout(function () {
                document.querySelectorAll('.alert').forEach(function (alert) {
                    alert.classList.remove('show');
                });
            }, 5000);
        });
    })();
</script>
@stop
