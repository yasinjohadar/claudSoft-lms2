@extends('admin.layouts.master')

@section('page-title')
    المدفوعات
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
                        <li class="breadcrumb-item active">المدفوعات</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-dollar-sign me-1"></i>
                            إدارة المدفوعات
                        </span>
                        <h2 class="group-show-hero__title mb-2">التحصيل والمدفوعات</h2>
                        <p class="group-show-hero__desc mb-0">
                            متابعة الدفعات المكتملة والمعلقة والمستردة، مع إحصائيات التحصيل والمتبقي.
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
                    <h4 class="card-title mb-1">تصفية المدفوعات</h4>
                    <p class="fs-12 text-muted mb-0">ابحث برقم الدفعة أو فلتر حسب الحالة والمعسكر والتاريخ.</p>
                </div>
                <div class="card-body pt-3">
                    <form method="GET" action="{{ route('payments.index') }}" id="paymentsFilterForm" class="group-show-filters mb-0">
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                <label class="form-label" for="paymentsSearchInput">رقم الدفعة</label>
                                <input type="text" name="payment_number" id="paymentsSearchInput" class="form-control"
                                       value="{{ request('payment_number') }}" placeholder="البحث برقم الدفعة">
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                <label class="form-label" for="paymentsStatus">الحالة</label>
                                <select name="status" id="paymentsStatus" class="form-select">
                                    <option value="">جميع الحالات</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>معلقة</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتملة</option>
                                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>فاشلة</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغاة</option>
                                    <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>مستردة</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                <label class="form-label" for="paymentsPaymentStatus">حالة السداد</label>
                                <select name="payment_status" id="paymentsPaymentStatus" class="form-select">
                                    <option value="">جميع الحالات</option>
                                    <option value="fully_paid" {{ request('payment_status') == 'fully_paid' ? 'selected' : '' }}>كامل</option>
                                    <option value="partially_paid" {{ request('payment_status') == 'partially_paid' ? 'selected' : '' }}>جزئي</option>
                                    <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>غير مسدد</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                <label class="form-label" for="paymentsCampId">المعسكر</label>
                                <select name="camp_id" id="paymentsCampId" class="form-select">
                                    <option value="">جميع المعسكرات</option>
                                    @foreach($camps as $camp)
                                        <option value="{{ $camp->id }}" {{ (string) request('camp_id') === (string) $camp->id ? 'selected' : '' }}>
                                            {{ $camp->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                <label class="form-label" for="paymentsFromDate">من تاريخ</label>
                                <input type="date" name="from_date" id="paymentsFromDate" class="form-control" value="{{ request('from_date') }}">
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                <label class="form-label" for="paymentsToDate">إلى تاريخ</label>
                                <input type="date" name="to_date" id="paymentsToDate" class="form-control" value="{{ request('to_date') }}">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-sm" id="paymentsSearchBtn">
                                    <i class="fe fe-search me-1"></i>بحث
                                </button>
                                <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary btn-sm" id="paymentsResetBtn">
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
                        قائمة المدفوعات
                        <span class="group-show-members-card__count">{{ $payments->total() }}</span>
                    </h6>
                </div>
                <div class="card-body pt-3">
                    <div id="paymentsFilterFeedback" class="small text-muted mb-2"></div>
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
        const searchInput = document.getElementById('paymentsSearchInput');
        const resetBtn = document.getElementById('paymentsResetBtn');
        let activeController = null;

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
            feedback.textContent = 'جاري تحميل النتائج...';

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
                feedback.textContent = '';
            } catch (error) {
                if (error.name === 'AbortError') return;
                feedback.textContent = 'حدث خطأ أثناء تحميل النتائج';
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

        form.querySelectorAll('select, input[type="date"]').forEach((element) => {
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
