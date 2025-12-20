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
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-primary-transparent">
                                        <i class="fas fa-money-bill-wave fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">إجمالي المدفوعات</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">${{ number_format($payments->where('status', 'completed')->sum('amount'), 2) }}</h4>
                                    <span class="badge bg-primary-transparent">{{ $payments->where('status', 'completed')->count() }} دفعة</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-warning-transparent">
                                        <i class="fas fa-clock fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">دفعات معلقة</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">${{ number_format($payments->where('status', 'pending')->sum('amount'), 2) }}</h4>
                                    <span class="badge bg-warning-transparent">{{ $payments->where('status', 'pending')->count() }} دفعة</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-danger-transparent">
                                        <i class="fas fa-times-circle fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">دفعات ملغاة</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">${{ number_format($payments->where('status', 'cancelled')->sum('amount'), 2) }}</h4>
                                    <span class="badge bg-danger-transparent">{{ $payments->where('status', 'cancelled')->count() }} دفعة</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-info-transparent">
                                        <i class="fas fa-undo fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">مبالغ مستردة</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">${{ number_format($payments->where('status', 'refunded')->sum('amount'), 2) }}</h4>
                                    <span class="badge bg-info-transparent">{{ $payments->where('status', 'refunded')->count() }} دفعة</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card custom-card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('payments.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">رقم الدفعة</label>
                                <input type="text" name="payment_number" class="form-control"
                                       value="{{ request('payment_number') }}" placeholder="البحث برقم الدفعة">
                            </div>
                            <div class="col-md-3">
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
                            <div class="col-md-3">
                                <label class="form-label">من تاريخ</label>
                                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">إلى تاريخ</label>
                                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i>بحث
                                </button>
                                <a href="{{ route('payments.index') }}" class="btn btn-secondary">
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
                    @if($payments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered text-nowrap">
                                <thead>
                                    <tr>
                                        <th>رقم الدفعة</th>
                                        <th>رقم الفاتورة</th>
                                        <th>الطالب</th>
                                        <th>المبلغ</th>
                                        <th>طريقة الدفع</th>
                                        <th>تاريخ الدفع</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $payment)
                                        <tr>
                                            <td>
                                                <a href="{{ route('payments.show', $payment->id) }}" class="text-primary fw-semibold">
                                                    {{ $payment->payment_number }}
                                                </a>
                                            </td>
                                            <td>
                                                @if($payment->invoice)
                                                    <a href="{{ route('invoices.show', $payment->invoice_id) }}">
                                                        {{ $payment->invoice->invoice_number }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($payment->invoice && $payment->invoice->student)
                                                    {{ $payment->invoice->student->name }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="fw-bold">${{ number_format($payment->amount, 2) }}</td>
                                            <td>
                                                @if($payment->paymentMethod)
                                                    <i class="bi bi-credit-card me-1"></i>{{ $payment->paymentMethod->name }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'bg-warning',
                                                        'completed' => 'bg-success',
                                                        'failed' => 'bg-danger',
                                                        'cancelled' => 'bg-secondary',
                                                        'refunded' => 'bg-info'
                                                    ];
                                                    $statusLabels = [
                                                        'pending' => 'معلقة',
                                                        'completed' => 'مكتملة',
                                                        'failed' => 'فاشلة',
                                                        'cancelled' => 'ملغاة',
                                                        'refunded' => 'مستردة'
                                                    ];
                                                @endphp
                                                <span class="badge {{ $statusColors[$payment->status] ?? 'bg-secondary' }}">
                                                    {{ $statusLabels[$payment->status] ?? $payment->status }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('payments.show', $payment->id) }}"
                                                       class="btn btn-sm btn-info" title="عرض">
                                                        <i class="fas fa-eye"></i>
                                                    </a>

                                                    @if($payment->status == 'completed')
                                                        <button type="button" class="btn btn-sm btn-warning"
                                                                onclick="confirmRefund({{ $payment->id }})" title="استرداد">
                                                            <i class="fas fa-undo"></i>
                                                        </button>
                                                    @endif

                                                    @if($payment->status == 'pending')
                                                        <button type="button" class="btn btn-sm btn-danger"
                                                                onclick="confirmCancel({{ $payment->id }})" title="إلغاء">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $payments->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                            <p class="text-muted">لا توجد مدفوعات</p>
                        </div>
                    @endif
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
</script>
@stop
