@extends('admin.layouts.master')

@section('page-title')
    المدفوعات
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إدارة المدفوعات</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">المدفوعات</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('payments.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>تسجيل دفعة جديدة
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4" id="paymentsStatsContainer">
                @include('admin.pages.payments.partials.stats', ['stats' => $stats])
            </div>

            <!-- Filters -->
            <div class="card custom-card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('payments.index') }}" id="paymentsFilterForm">
                        <div class="row g-2">
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                <label class="form-label">رقم الدفعة</label>
                                <input type="text" name="payment_number" id="paymentsSearchInput" class="form-control"
                                       value="{{ request('payment_number') }}" placeholder="البحث برقم الدفعة">
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                <label class="form-label">الحالة</label>
                                <select name="status" class="form-select">
                                    <option value="">جميع الحالات</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>معلقة</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتملة</option>
                                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>فاشلة</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغاة</option>
                                    <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>مستردة</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                <label class="form-label">حالة السداد</label>
                                <select name="payment_status" class="form-select">
                                    <option value="">جميع الحالات</option>
                                    <option value="fully_paid" {{ request('payment_status') == 'fully_paid' ? 'selected' : '' }}>كامل</option>
                                    <option value="partially_paid" {{ request('payment_status') == 'partially_paid' ? 'selected' : '' }}>جزئي</option>
                                    <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>غير مسدد</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                <label class="form-label">المعسكر</label>
                                <select name="camp_id" class="form-select">
                                    <option value="">جميع المعسكرات</option>
                                    @foreach($camps as $camp)
                                        <option value="{{ $camp->id }}" {{ (string) request('camp_id') === (string) $camp->id ? 'selected' : '' }}>
                                            {{ $camp->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                <label class="form-label">من تاريخ</label>
                                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                <label class="form-label">إلى تاريخ</label>
                                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary" id="paymentsSearchBtn">
                                    <i class="fas fa-search me-1"></i>بحث
                                </button>
                                <a href="{{ route('payments.index') }}" class="btn btn-secondary" id="paymentsResetBtn">
                                    <i class="fas fa-redo me-1"></i>إعادة تعيين
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Payments Table -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">قائمة المدفوعات</div>
                </div>
                <div class="card-body">
                    <div id="paymentsFilterFeedback" class="small text-muted mb-2"></div>
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap">
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

                    <div class="mt-3" id="paymentsPagination">
                        {{ $payments->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Cancel Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1">
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
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
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

    <!-- Refund Modal -->
    <div class="modal fade" id="refundModal" tabindex="-1">
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
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
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

@section('scripts')
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

    document.addEventListener('DOMContentLoaded', function () {
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
                        'Accept': 'application/json'
                    },
                    signal: activeController.signal
                });

                if (!response.ok) throw new Error('Failed request');
                const data = await response.json();

                if (statsContainer && data.stats) {
                    statsContainer.innerHTML = data.stats;
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
