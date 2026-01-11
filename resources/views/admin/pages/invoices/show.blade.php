@extends('admin.layouts.master')

@section('page-title')
    تفاصيل الفاتورة {{ $invoice->invoice_number }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تفاصيل الفاتورة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">الفواتير</a></li>
                            <li class="breadcrumb-item active">{{ $invoice->invoice_number }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    @if($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
                        <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>إضافة دفعة
                        </a>
                    @endif
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fas fa-print me-2"></i>طباعة
                    </button>
                </div>
            </div>

            <!-- Invoice Details Card -->
            <div class="row">
                <div class="col-xl-8">
                    <div class="card custom-card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">الفاتورة {{ $invoice->invoice_number }}</h5>
                        </div>
                        <div class="card-body">
                            <!-- Student Info -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="fw-bold">معلومات الطالب:</h6>
                                    <p class="mb-1"><strong>الاسم:</strong> {{ $invoice->student->name }}</p>
                                    <p class="mb-1"><strong>البريد:</strong> {{ $invoice->student->email }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold">معلومات الفاتورة:</h6>
                                    <p class="mb-1"><strong>تاريخ الإصدار:</strong> {{ $invoice->issue_date->format('Y-m-d') }}</p>
                                    <p class="mb-1"><strong>تاريخ الاستحقاق:</strong> {{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '-' }}</p>
                                </div>
                            </div>

                            <!-- Invoice Items -->
                            <h6 class="fw-bold mb-3">البنود:</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>الوصف</th>
                                            <th>الكمية</th>
                                            <th>سعر الوحدة</th>
                                            <th>المجموع</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($invoice->items as $item)
                                            <tr>
                                                <td>
                                                    {{ $item->description }}
                                                    @if($item->campEnrollment)
                                                        <br><small class="text-muted">المعسكر: {{ $item->campEnrollment->camp->name }}</small>
                                                    @endif
                                                </td>
                                                <td>{{ $item->quantity }}</td>
                                                <td>${{ number_format($item->unit_price, 2) }}</td>
                                                <td>${{ number_format($item->total_price, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>المجموع:</strong></td>
                                            <td><strong>${{ number_format($invoice->total_amount, 2) }}</strong></td>
                                        </tr>
                                        <tr class="table-success">
                                            <td colspan="3" class="text-end"><strong>المدفوع:</strong></td>
                                            <td><strong>${{ number_format($invoice->paid_amount, 2) }}</strong></td>
                                        </tr>
                                        <tr class="table-danger">
                                            <td colspan="3" class="text-end"><strong>المتبقي:</strong></td>
                                            <td><strong>${{ number_format($invoice->remaining_amount, 2) }}</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            @if($invoice->notes)
                                <div class="alert alert-info mt-3">
                                    <strong>ملاحظات:</strong> {{ $invoice->notes }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <!-- Payments History -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <h6 class="mb-0">المدفوعات</h6>
                        </div>
                        <div class="card-body">
                            @forelse($invoice->payments as $payment)
                                <div class="border-bottom pb-3 mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <strong>{{ $payment->payment_number }}</strong>
                                        <span class="badge bg-success">${{ number_format($payment->amount, 2) }}</span>
                                    </div>
                                    <p class="mb-1 small text-muted">
                                        <i class="fas fa-calendar me-1"></i>{{ $payment->payment_date->format('Y-m-d') }}
                                    </p>
                                    <p class="mb-0 small text-muted">
                                        <i class="fas fa-credit-card me-1"></i>{{ $payment->paymentMethod->name ?? 'غير محدد' }}
                                    </p>
                                </div>
                            @empty
                                <p class="text-muted text-center">لا توجد مدفوعات</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete Invoice Section -->
            <div class="row mt-4">
                <div class="col-xl-12">
                    <div class="card border border-danger">
                        <div class="card-body text-center">
                            <div class="avatar avatar-lg bg-danger-transparent mb-3 mx-auto">
                                <i class="fas fa-trash fs-18"></i>
                            </div>
                            <h6 class="mb-2">حذف الفاتورة نهائياً</h6>
                            <p class="text-muted mb-3 small">حذف الفاتورة نهائياً من النظام. هذا الإجراء لا يمكن التراجع عنه.</p>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#forceDeleteModal{{ $invoice->id }}">
                                <i class="fas fa-trash-alt me-2"></i>حذف نهائياً
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Force Delete Modal -->
            <div class="modal fade" id="forceDeleteModal{{ $invoice->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-exclamation-triangle me-2"></i>حذف نهائي
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-danger">
                                <strong>تحذير!</strong> هذا الإجراء لا يمكن التراجع عنه.
                            </div>
                            <p>هل أنت متأكد من حذف الفاتورة <strong>{{ $invoice->invoice_number }}</strong> نهائياً؟</p>
                            <ul class="list-unstyled">
                                <li><strong>الطالب:</strong> {{ $invoice->student->name }}</li>
                                <li><strong>المبلغ الإجمالي:</strong> ${{ number_format($invoice->total_amount, 2) }}</li>
                                <li><strong>الحالة:</strong> 
                                    @php
                                        $statusLabels = [
                                            'draft' => 'مسودة',
                                            'issued' => 'صادرة',
                                            'partial' => 'مدفوعة جزئياً',
                                            'paid' => 'مدفوعة',
                                            'cancelled' => 'ملغاة',
                                            'refunded' => 'مستردة'
                                        ];
                                    @endphp
                                    {{ $statusLabels[$invoice->status] ?? $invoice->status }}
                                </li>
                            </ul>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                            <form action="{{ route('invoices.force-delete', $invoice->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('POST')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash-alt me-2"></i>حذف نهائياً
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop
