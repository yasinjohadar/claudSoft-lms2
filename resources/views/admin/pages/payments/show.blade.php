@extends('admin.layouts.master')

@section('page-title')
    تفاصيل الدفعة {{ $payment->payment_number }}
@stop

@section('css')
<style>
    @media print {
        .no-print,
        .app-sidebar,
        .app-header,
        .page-header-breadcrumb,
        .group-show-hero,
        .group-show-actions {
            display: none !important;
        }

        .main-content {
            margin: 0 !important;
            padding: 0 !important;
        }

        .group-show-members-card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }
    }
</style>
@stop

@section('content')
    @php
        $statusColors = [
            'pending' => 'bg-warning-transparent text-warning',
            'completed' => 'bg-success-transparent text-success',
            'failed' => 'bg-danger-transparent text-danger',
            'cancelled' => 'bg-secondary-transparent text-secondary',
            'refunded' => 'bg-info-transparent text-info',
        ];
        $statusLabels = [
            'pending' => 'معلقة',
            'completed' => 'مكتملة',
            'failed' => 'فاشلة',
            'cancelled' => 'ملغاة',
            'refunded' => 'مستردة',
        ];
    @endphp

    <div class="main-content app-content">
        <div class="container-fluid">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mt-3 mb-0" role="alert">
                    <i class="fe fe-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mt-3 mb-0" role="alert">
                    <i class="fe fe-alert-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="my-4 page-header-breadcrumb no-print">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('payments.index') }}">المدفوعات</a></li>
                        <li class="breadcrumb-item active">{{ $payment->payment_number }}</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4 no-print">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-dollar-sign me-1"></i>
                            تفاصيل الدفعة
                        </span>
                        <h2 class="group-show-hero__title mb-2">{{ $payment->payment_number }}</h2>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <span class="badge {{ $statusColors[$payment->status] ?? 'bg-secondary-transparent text-secondary' }}">
                                {{ $statusLabels[$payment->status] ?? $payment->status }}
                            </span>
                            <span class="group-show-chip group-show-chip--sm">
                                <i class="fe fe-calendar me-1"></i>{{ $payment->payment_date->format('Y-m-d') }}
                            </span>
                            @if($payment->paymentMethod)
                                <span class="group-show-chip group-show-chip--sm">
                                    <i class="fe fe-credit-card me-1"></i>{{ $payment->paymentMethod->name }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions">
                            <button type="button" onclick="window.print()"
                                    class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-printer"></i></span>
                                <span class="group-show-action__text">طباعة</span>
                            </button>
                            <a href="{{ route('payments.index') }}"
                               class="group-show-action group-show-action--info">
                                <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                                <span class="group-show-action__text">رجوع</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @include('admin.pages.payments.partials.show-stats', ['payment' => $payment])

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title mb-1">معلومات الدفعة</h4>
                            <p class="fs-12 text-muted mb-0">تفاصيل الدفعة والفاتورة المرتبطة.</p>
                        </div>
                        <div class="card-body pt-3">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3 fw-semibold">بيانات الدفعة</h6>
                                    <dl class="row mb-0">
                                        <dt class="col-sm-5 text-muted fw-normal">رقم الدفعة</dt>
                                        <dd class="col-sm-7 fw-semibold mb-2">{{ $payment->payment_number }}</dd>

                                        <dt class="col-sm-5 text-muted fw-normal">تاريخ الدفع</dt>
                                        <dd class="col-sm-7 mb-2">{{ $payment->payment_date->format('Y-m-d') }}</dd>

                                        <dt class="col-sm-5 text-muted fw-normal">المبلغ</dt>
                                        <dd class="col-sm-7 mb-2 text-success fw-bold">${{ number_format($payment->amount, 2) }}</dd>

                                        @if($payment->paymentMethod)
                                            <dt class="col-sm-5 text-muted fw-normal">طريقة الدفع</dt>
                                            <dd class="col-sm-7 mb-2">{{ $payment->paymentMethod->name }}</dd>
                                        @endif

                                        @if($payment->reference_number)
                                            <dt class="col-sm-5 text-muted fw-normal">رقم المرجع</dt>
                                            <dd class="col-sm-7 mb-2">{{ $payment->reference_number }}</dd>
                                        @endif

                                        @if($payment->transaction_id)
                                            <dt class="col-sm-5 text-muted fw-normal">رقم المعاملة</dt>
                                            <dd class="col-sm-7 mb-2">{{ $payment->transaction_id }}</dd>
                                        @endif

                                        @if($payment->receivedBy)
                                            <dt class="col-sm-5 text-muted fw-normal">استلمها</dt>
                                            <dd class="col-sm-7 mb-0">{{ $payment->receivedBy->name }}</dd>
                                        @endif
                                    </dl>
                                </div>

                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3 fw-semibold">معلومات الفاتورة</h6>
                                    @if($payment->invoice)
                                        <dl class="row mb-0">
                                            <dt class="col-sm-5 text-muted fw-normal">رقم الفاتورة</dt>
                                            <dd class="col-sm-7 mb-2">
                                                <a href="{{ route('invoices.show', $payment->invoice_id) }}" class="fw-semibold text-primary text-decoration-none">
                                                    {{ $payment->invoice->invoice_number }}
                                                </a>
                                            </dd>

                                            @if($payment->invoice->student)
                                                <dt class="col-sm-5 text-muted fw-normal">الطالب</dt>
                                                <dd class="col-sm-7 mb-2">{{ $payment->invoice->student->name }}</dd>

                                                <dt class="col-sm-5 text-muted fw-normal">البريد الإلكتروني</dt>
                                                <dd class="col-sm-7 mb-2 text-break">{{ $payment->invoice->student->email }}</dd>
                                            @endif

                                            <dt class="col-sm-5 text-muted fw-normal">إجمالي الفاتورة</dt>
                                            <dd class="col-sm-7 mb-2 fw-semibold">${{ number_format($payment->invoice->total_amount, 2) }}</dd>

                                            <dt class="col-sm-5 text-muted fw-normal">المبلغ المدفوع</dt>
                                            <dd class="col-sm-7 mb-2 text-success fw-semibold">${{ number_format($payment->invoice->paid_amount, 2) }}</dd>

                                            <dt class="col-sm-5 text-muted fw-normal">المبلغ المتبقي</dt>
                                            <dd class="col-sm-7 mb-0 text-danger fw-semibold">${{ number_format($payment->invoice->remaining_amount, 2) }}</dd>
                                        </dl>
                                    @else
                                        <p class="text-muted mb-0">لا توجد فاتورة مرتبطة بهذه الدفعة.</p>
                                    @endif
                                </div>
                            </div>

                            @if($payment->notes)
                                <div class="alert alert-info mt-4 mb-0">
                                    <i class="fe fe-info me-2"></i>
                                    <strong>ملاحظات:</strong> {{ $payment->notes }}
                                </div>
                            @endif

                            @if($payment->rejection_reason)
                                <div class="alert alert-danger mt-4 mb-0">
                                    <i class="fe fe-x-circle me-2"></i>
                                    <strong>سبب الرفض:</strong> {{ $payment->rejection_reason }}
                                </div>
                            @endif

                            @if($payment->cancellation_reason)
                                <div class="alert alert-danger mt-4 mb-0">
                                    <i class="fe fe-x-circle me-2"></i>
                                    <strong>سبب الإلغاء:</strong> {{ $payment->cancellation_reason }}
                                </div>
                            @endif

                            @if($payment->refund_reason)
                                <div class="alert alert-warning mt-4 mb-0">
                                    <i class="fe fe-rotate-ccw me-2"></i>
                                    <strong>سبب الاسترداد:</strong> {{ $payment->refund_reason }}
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($payment->has_receipt)
                        <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                            <div class="card-header border-0 pb-0">
                                <h4 class="card-title mb-1">إيصال الدفع</h4>
                                <p class="fs-12 text-muted mb-0">مرفق من الطالب — راجعه قبل الموافقة.</p>
                            </div>
                            <div class="card-body pt-3">
                                @if($payment->is_receipt_image)
                                    <div class="text-center mb-3">
                                        <img src="{{ route('payments.receipt', $payment->id) }}"
                                             alt="إيصال الدفع"
                                             class="img-fluid rounded border"
                                             style="max-height: 420px;">
                                    </div>
                                @endif
                                <a href="{{ route('payments.receipt', $payment->id) }}"
                                   class="btn btn-outline-primary"
                                   target="_blank">
                                    <i class="fe fe-external-link me-2"></i>
                                    {{ $payment->is_receipt_image ? 'فتح الإيصال بحجم كامل' : 'عرض / تحميل الإيصال' }}
                                </a>
                            </div>
                        </div>
                    @endif

                    <div class="card custom-card group-show-members-card dashboard-fade-in">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title mb-1">السجل</h4>
                            <p class="fs-12 text-muted mb-0">تواريخ الإنشاء والتحديث.</p>
                        </div>
                        <div class="card-body pt-3">
                            <dl class="row mb-0">
                                <dt class="col-sm-4 col-md-3 text-muted fw-normal">تاريخ الإنشاء</dt>
                                <dd class="col-sm-8 col-md-9 mb-2">{{ $payment->created_at->format('Y-m-d H:i:s') }}</dd>

                                @if($payment->created_at != $payment->updated_at)
                                    <dt class="col-sm-4 col-md-3 text-muted fw-normal">آخر تحديث</dt>
                                    <dd class="col-sm-8 col-md-9 mb-0">{{ $payment->updated_at->format('Y-m-d H:i:s') }}</dd>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 no-print">
                    <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title mb-1">الإجراءات</h4>
                        </div>
                        <div class="card-body pt-3">
                            @if($payment->status == 'completed')
                                <button type="button" class="btn btn-warning w-100 mb-3" onclick="confirmRefund()">
                                    <i class="fe fe-rotate-ccw me-2"></i>استرداد المبلغ
                                </button>
                                <div class="alert alert-info small mb-0">
                                    <i class="fe fe-info me-1"></i>
                                    سيتم إرجاع المبلغ إلى الفاتورة عند الاسترداد
                                </div>
                            @elseif($payment->status == 'pending')
                                @if($payment->has_receipt)
                                    <form action="{{ route('payments.approve', $payment->id) }}" method="POST" class="mb-3"
                                          onsubmit="return confirm('هل أنت متأكد من الموافقة على هذه الدفعة وتسجيلها في حساب الطالب؟');">
                                        @csrf
                                        <button type="submit" class="btn btn-success w-100">
                                            <i class="fe fe-check me-2"></i>الموافقة وتسجيل الدفعة
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-danger w-100 mb-3" onclick="confirmReject()">
                                        <i class="fe fe-x me-2"></i>رفض الطلب
                                    </button>
                                    <div class="alert alert-warning small mb-0">
                                        <i class="fe fe-alert-triangle me-1"></i>
                                        راجع الإيصال قبل الموافقة. لن تُحدَّث الفاتورة إلا بعد الموافقة.
                                    </div>
                                @else
                                    <button type="button" class="btn btn-danger w-100 mb-3" onclick="confirmCancel()">
                                        <i class="fe fe-x me-2"></i>إلغاء الدفعة
                                    </button>
                                    <div class="alert alert-warning small mb-0">
                                        <i class="fe fe-alert-triangle me-1"></i>
                                        لن يتم احتساب الدفعة الملغاة
                                    </div>
                                @endif
                            @elseif($payment->status == 'cancelled')
                                <div class="alert alert-secondary mb-0">
                                    <i class="fe fe-slash me-1"></i>
                                    تم إلغاء هذه الدفعة
                                </div>
                            @elseif($payment->status == 'refunded')
                                <div class="alert alert-info mb-0">
                                    <i class="fe fe-rotate-ccw me-1"></i>
                                    تم استرداد هذه الدفعة
                                </div>
                            @else
                                <p class="text-muted mb-0">لا توجد إجراءات متاحة لهذه الدفعة.</p>
                            @endif
                        </div>
                    </div>

                    @if($payment->invoice)
                        <div class="card custom-card group-show-members-card dashboard-fade-in">
                            <div class="card-header border-0 pb-0">
                                <h4 class="card-title mb-1">الفاتورة المرتبطة</h4>
                                <p class="fs-12 text-muted mb-0">{{ $payment->invoice->invoice_number }}</p>
                            </div>
                            <div class="card-body pt-3">
                                @if($payment->invoice->remaining_amount > 0)
                                    <a href="{{ route('payments.create', ['invoice_id' => $payment->invoice_id]) }}"
                                       class="btn btn-success-light w-100 mb-2">
                                        <i class="fe fe-plus-circle me-2"></i>استكمال المبلغ الناقص
                                    </a>
                                @endif
                                <a href="{{ route('invoices.show', $payment->invoice_id) }}"
                                   class="btn btn-primary w-100">
                                    <i class="fe fe-file-text me-2"></i>عرض الفاتورة
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('payments.reject', $payment->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">رفض طلب الدفع</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">سبب الرفض <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="alert alert-warning mb-0">
                            <i class="fe fe-alert-triangle me-2"></i>
                            سيتم إبلاغ الطالب بالرفض ولن تُحدَّث الفاتورة.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                        <button type="submit" class="btn btn-danger">تأكيد الرفض</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
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
                        <div class="alert alert-warning mb-0">
                            <i class="fe fe-alert-triangle me-2"></i>
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

@section('script')
<script>
    function confirmCancel() {
        new bootstrap.Modal(document.getElementById('cancelModal')).show();
    }

    function confirmReject() {
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    }

    function confirmRefund() {
        new bootstrap.Modal(document.getElementById('refundModal')).show();
    }

    (function () {
        function formatNumber(value, withDecimals) {
            if (withDecimals) {
                return new Intl.NumberFormat('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }).format(value);
            }
            return new Intl.NumberFormat('ar-EG').format(Math.round(value));
        }

        document.querySelectorAll('[data-countup]').forEach(function (el) {
            const target = parseFloat(el.dataset.countup || '0');
            const prefix = el.dataset.countupPrefix || '';
            const suffix = el.dataset.countupSuffix || '';
            const decimals = el.dataset.countupDecimals === '2' ? 2 : 0;
            const duration = 800;
            const start = performance.now();

            function step(now) {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = prefix + formatNumber(target * eased, decimals > 0) + suffix;
                if (progress < 1) requestAnimationFrame(step);
            }

            requestAnimationFrame(step);
        });
    })();
</script>
@stop
