@extends('admin.layouts.master')

@section('page-title')
    تفاصيل الدفعة {{ $payment->payment_number }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تفاصيل الدفعة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('payments.index') }}">المدفوعات</a></li>
                            <li class="breadcrumb-item active">{{ $payment->payment_number }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="bi bi-printer me-1"></i>طباعة
                    </button>
                    <a href="{{ route('payments.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-1"></i>رجوع
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card custom-card shadow-sm border-0">
                        <div class="card-header bg-success text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">الدفعة {{ $payment->payment_number }}</h5>
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-warning text-dark',
                                        'completed' => 'bg-light text-dark',
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
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Payment Info -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">معلومات الدفعة</h6>
                                    <p class="mb-2"><strong>رقم الدفعة:</strong> {{ $payment->payment_number }}</p>
                                    <p class="mb-2"><strong>تاريخ الدفع:</strong> {{ $payment->payment_date->format('Y-m-d') }}</p>
                                    <p class="mb-2"><strong>المبلغ:</strong> <span class="text-success fw-bold">${{ number_format($payment->amount, 2) }}</span></p>
                                    @if($payment->paymentMethod)
                                        <p class="mb-2">
                                            <strong>طريقة الدفع:</strong>
                                            <i class="bi bi-credit-card me-1"></i>{{ $payment->paymentMethod->name }}
                                        </p>
                                    @endif
                                    @if($payment->reference_number)
                                        <p class="mb-2"><strong>رقم المرجع:</strong> {{ $payment->reference_number }}</p>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">معلومات الفاتورة</h6>
                                    @if($payment->invoice)
                                        <p class="mb-2">
                                            <strong>رقم الفاتورة:</strong>
                                            <a href="{{ route('invoices.show', $payment->invoice_id) }}" class="text-primary">
                                                {{ $payment->invoice->invoice_number }}
                                            </a>
                                        </p>
                                        @if($payment->invoice->student)
                                            <p class="mb-2"><strong>الطالب:</strong> {{ $payment->invoice->student->name }}</p>
                                            <p class="mb-2"><strong>البريد الإلكتروني:</strong> {{ $payment->invoice->student->email }}</p>
                                        @endif
                                        <p class="mb-2"><strong>إجمالي الفاتورة:</strong> ${{ number_format($payment->invoice->total_amount, 2) }}</p>
                                        <p class="mb-2"><strong>المبلغ المدفوع:</strong> <span class="text-success">${{ number_format($payment->invoice->paid_amount, 2) }}</span></p>
                                        <p class="mb-2"><strong>المبلغ المتبقي:</strong> <span class="text-danger">${{ number_format($payment->invoice->remaining_amount, 2) }}</span></p>
                                    @else
                                        <p class="text-muted">لا توجد فاتورة مرتبطة</p>
                                    @endif
                                </div>
                            </div>

                            @if($payment->notes)
                                <div class="alert alert-info">
                                    <strong>ملاحظات:</strong> {{ $payment->notes }}
                                </div>
                            @endif

                            @if($payment->cancellation_reason)
                                <div class="alert alert-danger">
                                    <strong>سبب الإلغاء:</strong> {{ $payment->cancellation_reason }}
                                </div>
                            @endif

                            @if($payment->refund_reason)
                                <div class="alert alert-warning">
                                    <strong>سبب الاسترداد:</strong> {{ $payment->refund_reason }}
                                </div>
                            @endif

                            <!-- Audit Trail -->
                            <hr class="my-4">
                            <h6 class="text-muted mb-3">السجل</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <td><strong>تاريخ الإنشاء:</strong></td>
                                            <td>{{ $payment->created_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                        @if($payment->created_at != $payment->updated_at)
                                            <tr>
                                                <td><strong>آخر تحديث:</strong></td>
                                                <td>{{ $payment->updated_at->format('Y-m-d H:i:s') }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions Sidebar -->
                <div class="col-lg-4">
                    <div class="card custom-card shadow-sm border-0">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">الإجراءات</h6>
                        </div>
                        <div class="card-body">
                            @if($payment->status == 'completed')
                                <button type="button" class="btn btn-warning w-100 mb-2" onclick="confirmRefund()">
                                    <i class="fas fa-undo me-1"></i>استرداد المبلغ
                                </button>
                                <div class="alert alert-info small mb-0">
                                    <i class="fas fa-info-circle me-1"></i>
                                    سيتم إرجاع المبلغ إلى الفاتورة عند الاسترداد
                                </div>
                            @elseif($payment->status == 'pending')
                                <button type="button" class="btn btn-danger w-100 mb-2" onclick="confirmCancel()">
                                    <i class="fas fa-times me-1"></i>إلغاء الدفعة
                                </button>
                                <div class="alert alert-warning small mb-0">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    لن يتم احتساب الدفعة الملغاة
                                </div>
                            @elseif($payment->status == 'cancelled')
                                <div class="alert alert-secondary mb-0">
                                    <i class="fas fa-ban me-1"></i>
                                    تم إلغاء هذه الدفعة
                                </div>
                            @elseif($payment->status == 'refunded')
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-undo me-1"></i>
                                    تم استرداد هذه الدفعة
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($payment->invoice)
                        <div class="card custom-card shadow-sm border-0 mt-3">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">تفاصيل الفاتورة</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">الإجمالي:</span>
                                    <strong>${{ number_format($payment->invoice->total_amount, 2) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">المدفوع:</span>
                                    <strong class="text-success">${{ number_format($payment->invoice->paid_amount, 2) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">المتبقي:</span>
                                    <strong class="text-danger">${{ number_format($payment->invoice->remaining_amount, 2) }}</strong>
                                </div>
                                <hr>
                                <a href="{{ route('invoices.show', $payment->invoice_id) }}" class="btn btn-sm btn-primary w-100">
                                    <i class="fas fa-file-invoice me-1"></i>عرض الفاتورة
                                </a>
                            </div>
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
                <form action="{{ route('payments.cancel', $payment->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">إلغاء الدفعة</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">سبب الإلغاء <span class="text-danger">*</span></label>
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
                <form action="{{ route('payments.refund', $payment->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">استرداد المبلغ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">سبب الاسترداد <span class="text-danger">*</span></label>
                            <textarea name="refund_reason" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            سيتم استرداد المبلغ ${{ number_format($payment->amount, 2) }} وخصمه من رصيد الفاتورة المدفوع
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
    function confirmCancel() {
        new bootstrap.Modal(document.getElementById('cancelModal')).show();
    }

    function confirmRefund() {
        new bootstrap.Modal(document.getElementById('refundModal')).show();
    }
</script>
@stop
