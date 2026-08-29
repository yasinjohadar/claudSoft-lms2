@extends('admin.layouts.master')

@section('page-title')
    الدفعات المكتملة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fe fe-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fe fe-alert-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('payments.index') }}">المدفوعات</a></li>
                        <li class="breadcrumb-item active">الدفعات المكتملة</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-check-circle me-1"></i>
                            إدارة المدفوعات
                        </span>
                        <h2 class="group-show-hero__title mb-2">الدفعات المكتملة</h2>
                        <p class="group-show-hero__desc mb-0">
                            عرض الدفعات المكتملة فقط، مع نفس فلاتر وإحصائيات صفحة المدفوعات.
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions group-show-actions--single">
                            <a href="{{ route('payments.create') }}"
                               class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-plus"></i></span>
                                <span class="group-show-action__text">تسجيل دفعة جديدة</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div id="paymentsStatsContainer" class="mb-4">
                @include('admin.pages.payments.partials.stats', ['stats' => $stats])
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-1">تصفية الدفعات المكتملة</h4>
                    <p class="fs-12 text-muted mb-0">جميع الفلاتر تعمل فوراً عبر AJAX.</p>
                </div>
                <div class="card-body pt-3">
                    <form method="GET" action="{{ route('payments.completed') }}" id="paymentsFilterForm" class="group-show-filters mb-0">
                        <input type="hidden" name="status" id="paymentsStatusHidden" value="completed">
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <label class="form-label" for="paymentsSearchInput">بحث شامل</label>
                                <input type="text" name="search" id="paymentsSearchInput" class="form-control"
                                       value="{{ request('search', request('payment_number')) }}"
                                       placeholder="رقم الدفعة، الفاتورة، الطالب، البريد...">
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="paymentsInvoiceNumber">رقم الفاتورة</label>
                                <input type="text" name="invoice_number" id="paymentsInvoiceNumber" class="form-control"
                                       value="{{ request('invoice_number') }}" placeholder="INV-...">
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="paymentsPaymentStatus">حالة السداد</label>
                                <select name="payment_status" id="paymentsPaymentStatus" class="form-select">
                                    <option value="">جميع الحالات</option>
                                    <option value="fully_paid" {{ request('payment_status') == 'fully_paid' ? 'selected' : '' }}>كامل</option>
                                    <option value="partially_paid" {{ request('payment_status') == 'partially_paid' ? 'selected' : '' }}>جزئي</option>
                                    <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>غير مسدد</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="paymentsMethod">طريقة الدفع</label>
                                <select name="payment_method_id" id="paymentsMethod" class="form-select">
                                    <option value="">الكل</option>
                                    @foreach($paymentMethods as $method)
                                        <option value="{{ $method->id }}" {{ (string) request('payment_method_id') === (string) $method->id ? 'selected' : '' }}>
                                            {{ $method->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="paymentsCampId">المعسكر / المجموعة</label>
                                <select name="camp_id" id="paymentsCampId" class="form-select">
                                    <option value="">الكل</option>
                                    @if(isset($camps) && $camps->isNotEmpty())
                                        <optgroup label="المعسكرات القديمة">
                                            @foreach($camps as $camp)
                                                <option value="{{ $camp->id }}" {{ (string) request('camp_id') === (string) $camp->id ? 'selected' : '' }}>
                                                    {{ $camp->name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                    @if(isset($campGroups) && $campGroups->isNotEmpty())
                                        <optgroup label="مجموعات المعسكر">
                                            @foreach($campGroups as $campGroup)
                                                <option value="group:{{ $campGroup->id }}" {{ (string) request('camp_id') === 'group:'.$campGroup->id ? 'selected' : '' }}>
                                                    {{ $campGroup->name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="paymentsSource">مصدر الدفعة</label>
                                <select name="source" id="paymentsSource" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="student" {{ request('source') === 'student' ? 'selected' : '' }}>طلب من الطالب</option>
                                    <option value="admin" {{ request('source') === 'admin' ? 'selected' : '' }}>تسجيل إداري</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="paymentsHasReceipt">إيصال مرفق</label>
                                <select name="has_receipt" id="paymentsHasReceipt" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="1" {{ request('has_receipt') === '1' ? 'selected' : '' }}>نعم</option>
                                    <option value="0" {{ request('has_receipt') === '0' ? 'selected' : '' }}>لا</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="paymentsMinAmount">من مبلغ ($)</label>
                                <input type="number" step="0.01" min="0" name="min_amount" id="paymentsMinAmount" class="form-control"
                                       value="{{ request('min_amount') }}" placeholder="0">
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="paymentsMaxAmount">إلى مبلغ ($)</label>
                                <input type="number" step="0.01" min="0" name="max_amount" id="paymentsMaxAmount" class="form-control"
                                       value="{{ request('max_amount') }}" placeholder="">
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="paymentsFromDate">من تاريخ</label>
                                <input type="date" name="from_date" id="paymentsFromDate" class="form-control" value="{{ request('from_date') }}">
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="paymentsToDate">إلى تاريخ</label>
                                <input type="date" name="to_date" id="paymentsToDate" class="form-control" value="{{ request('to_date') }}">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-sm" id="paymentsSearchBtn">
                                    <i class="fe fe-search me-1"></i>بحث
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="paymentsResetBtn">
                                    <i class="fe fe-rotate-cw me-1"></i>إعادة تعيين
                                </button>
                                <span id="paymentsFilterFeedback" class="fs-12 text-muted ms-2"></span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                    <h6 class="group-show-members-card__title mb-0">
                        قائمة الدفعات
                        <span class="group-show-members-card__count" id="paymentsCountBadge">{{ $payments->total() }}</span>
                    </h6>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap dashboard-table mb-0">
                            <thead>
                                <tr>
                                    <th>رقم الدفعة</th>
                                    <th>رقم الفاتورة</th>
                                    <th>الطالب</th>
                                    <th>المعسكر المرتبط</th>
                                    <th>المبلغ</th>
                                    <th>المبلغ المتبقي</th>
                                    <th>حالة السداد</th>
                                    <th>طريقة الدفع</th>
                                    <th>تاريخ الدفع</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody id="paymentsTableBody">
                                @include('admin.pages.payments.partials.table', ['payments' => $payments])
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4" id="paymentsPagination">
                        {{ $payments->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="cancelForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">إلغاء الدفعة</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">سبب الإلغاء</label>
                            <textarea name="cancellation_reason" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="alert alert-warning mb-0">
                            <i class="fe fe-alert-triangle me-2"></i>
                            سيتم إلغاء هذه الدفعة ولن يتم احتسابها في الفاتورة
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                        <button type="submit" class="btn btn-danger">تأكيد الإلغاء</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="refundModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="refundForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">استرداد المبلغ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">سبب الاسترداد</label>
                            <textarea name="refund_reason" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="alert alert-warning mb-0">
                            <i class="fe fe-alert-triangle me-2"></i>
                            سيتم استرداد المبلغ وخصمه من رصيد الفاتورة المدفوع
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                        <button type="submit" class="btn btn-warning">تأكيد الاسترداد</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('script')
<script>
    function confirmCancel(paymentId) {
        const form = document.getElementById('cancelForm');
        form.action = '{{ url("admin/payments") }}/' + paymentId + '/cancel';
        new bootstrap.Modal(document.getElementById('cancelModal')).show();
    }

    function confirmRefund(paymentId) {
        const form = document.getElementById('refundForm');
        form.action = '{{ url("admin/payments") }}/' + paymentId + '/refund';
        new bootstrap.Modal(document.getElementById('refundModal')).show();
    }

    function formatCountupNumber(value, withDecimals) {
        if (withDecimals) {
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }).format(value);
        }
        return new Intl.NumberFormat('ar-EG').format(Math.round(value));
    }

    function initPaymentsCountup(container) {
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

    document.addEventListener('DOMContentLoaded', function () {
        initPaymentsCountup(document);

        const form = document.getElementById('paymentsFilterForm');
        if (!form) return;

        const tableBody = document.getElementById('paymentsTableBody');
        const paginationContainer = document.getElementById('paymentsPagination');
        const statsContainer = document.getElementById('paymentsStatsContainer');
        const feedback = document.getElementById('paymentsFilterFeedback');
        const countBadge = document.getElementById('paymentsCountBadge');
        const searchInput = document.getElementById('paymentsSearchInput');
        const resetBtn = document.getElementById('paymentsResetBtn');
        let activeController = null;

        const updateBrowserUrl = function (url) {
            const queryString = url.includes('?') ? url.split('?')[1] : '';
            const nextUrl = queryString ? (form.action + '?' + queryString) : form.action;
            window.history.replaceState({}, '', nextUrl);
        };

        const debounce = (fn, wait = 400) => {
            let timeout;
            return (...args) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => fn(...args), wait);
            };
        };

        const loadPayments = async (url = null) => {
            if (activeController) activeController.abort();
            activeController = new AbortController();

            const params = new URLSearchParams(new FormData(form));
            const targetUrl = url || `${form.action}?${params.toString()}`;
            if (feedback) feedback.textContent = 'جاري تحميل النتائج...';

            try {
                const response = await fetch(targetUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    signal: activeController.signal,
                });

                if (!response.ok) throw new Error('Failed request');
                const data = await response.json();

                if (statsContainer && data.stats) {
                    statsContainer.innerHTML = data.stats;
                    initPaymentsCountup(statsContainer);
                }
                tableBody.innerHTML = data.table || '';
                paginationContainer.innerHTML = data.pagination || '';
                if (countBadge && typeof data.count === 'number') {
                    countBadge.textContent = data.count;
                }
                updateBrowserUrl(targetUrl);
                if (feedback) feedback.textContent = 'تم تحديث النتائج';
            } catch (error) {
                if (error.name === 'AbortError') return;
                if (feedback) feedback.textContent = 'حدث خطأ أثناء تحميل النتائج';
            }
        };

        const debouncedLoad = debounce(() => loadPayments(), 450);

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            loadPayments();
        });

        if (searchInput) {
            searchInput.addEventListener('input', debouncedLoad);
        }

        form.querySelectorAll('select, input[type="date"], input[type="number"]').forEach((element) => {
            element.addEventListener('change', () => loadPayments());
        });

        if (resetBtn) {
            resetBtn.addEventListener('click', function (e) {
                e.preventDefault();
                form.reset();
                loadPayments(form.action);
            });
        }

        paginationContainer.addEventListener('click', function (e) {
            const link = e.target.closest('a');
            if (!link) return;
            e.preventDefault();
            loadPayments(link.href);
        });
    });
</script>
@stop
