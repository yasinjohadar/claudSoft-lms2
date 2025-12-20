@extends('admin.layouts.master')

@section('page-title')
    تسجيل دفعة جديدة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تسجيل دفعة جديدة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('payments.index') }}">المدفوعات</a></li>
                            <li class="breadcrumb-item active">تسجيل دفعة</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-8">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">معلومات الدفعة</div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('payments.store') }}" method="POST" id="paymentForm">
                                @csrf

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label">الفاتورة <span class="text-danger">*</span></label>
                                        <select name="invoice_id" id="invoice_id" class="form-select @error('invoice_id') is-invalid @enderror" required>
                                            <option value="">اختر الفاتورة</option>
                                            @foreach($invoices as $invoice)
                                                <option value="{{ $invoice->id }}"
                                                        data-remaining="{{ $invoice->remaining_amount }}"
                                                        data-student="{{ $invoice->student ? $invoice->student->name : '-' }}"
                                                        {{ old('invoice_id') == $invoice->id || request('invoice_id') == $invoice->id ? 'selected' : '' }}>
                                                    {{ $invoice->invoice_number }} -
                                                    @if($invoice->student)
                                                        {{ $invoice->student->name }} -
                                                    @endif
                                                    المتبقي: ${{ number_format($invoice->remaining_amount, 2) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('invoice_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">المبلغ المدفوع ($) <span class="text-danger">*</span></label>
                                        <input type="number" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror"
                                               value="{{ old('amount') }}" min="0.01" step="0.01" required>
                                        @error('amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted" id="remainingAmount"></small>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">تاريخ الدفع <span class="text-danger">*</span></label>
                                        <input type="date" name="payment_date" class="form-control @error('payment_date') is-invalid @enderror"
                                               value="{{ old('payment_date', date('Y-m-d')) }}" required>
                                        @error('payment_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">طريقة الدفع <span class="text-danger">*</span></label>
                                        <select name="payment_method_id" class="form-select @error('payment_method_id') is-invalid @enderror" required>
                                            <option value="">اختر طريقة الدفع</option>
                                            @foreach($paymentMethods as $method)
                                                <option value="{{ $method->id }}" {{ old('payment_method_id') == $method->id ? 'selected' : '' }}>
                                                    {{ $method->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('payment_method_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">الحالة <span class="text-danger">*</span></label>
                                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                            <option value="completed" {{ old('status', 'completed') == 'completed' ? 'selected' : '' }}>مكتملة</option>
                                            <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>معلقة</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label">رقم المرجع</label>
                                        <input type="text" name="reference_number" class="form-control @error('reference_number') is-invalid @enderror"
                                               value="{{ old('reference_number') }}" placeholder="رقم العملية أو الإيصال">
                                        @error('reference_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label">ملاحظات</label>
                                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>حفظ الدفعة
                                    </button>
                                    <a href="{{ route('payments.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>إلغاء
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card custom-card" id="invoiceDetailsCard" style="display: none;">
                        <div class="card-header bg-primary text-white">
                            <div class="card-title text-white">تفاصيل الفاتورة</div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="text-muted small">اسم الطالب</label>
                                <p class="fw-semibold mb-0" id="studentName">-</p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">إجمالي الفاتورة</label>
                                <p class="fw-semibold mb-0" id="totalAmount">$0.00</p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">المبلغ المدفوع</label>
                                <p class="fw-semibold text-success mb-0" id="paidAmount">$0.00</p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">المبلغ المتبقي</label>
                                <p class="fw-semibold text-danger mb-0" id="remaining">$0.00</p>
                            </div>
                            <hr>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                <small>يمكنك الدفع على دفعات حتى يتم تسديد كامل المبلغ المتبقي</small>
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card mt-3">
                        <div class="card-header bg-success text-white">
                            <div class="card-title text-white">نصائح الدفع</div>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    تأكد من صحة رقم المرجع
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    يمكنك تسجيل دفعة جزئية
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    سيتم تحديث حالة الفاتورة تلقائياً
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

@section('scripts')
<script>
    const invoiceSelect = document.getElementById('invoice_id');
    const amountInput = document.getElementById('amount');
    const invoiceDetailsCard = document.getElementById('invoiceDetailsCard');
    const remainingAmountText = document.getElementById('remainingAmount');

    invoiceSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];

        if (this.value) {
            const remaining = parseFloat(selectedOption.getAttribute('data-remaining')) || 0;
            const studentName = selectedOption.getAttribute('data-student');

            // Show invoice details
            invoiceDetailsCard.style.display = 'block';
            document.getElementById('studentName').textContent = studentName;
            document.getElementById('remaining').textContent = '$' + remaining.toFixed(2);

            // Set max amount
            amountInput.max = remaining;
            amountInput.value = remaining.toFixed(2);

            remainingAmountText.textContent = 'المبلغ المتبقي: $' + remaining.toFixed(2);

            // Load full invoice details via AJAX (optional)
            fetch(`/admin/invoices/${this.value}`)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    // Extract amounts from the page (this is a simple example)
                    // You might need to adjust based on actual HTML structure
                    const totalAmountElement = doc.querySelector('[data-total-amount]');
                    const paidAmountElement = doc.querySelector('[data-paid-amount]');

                    if (totalAmountElement) {
                        document.getElementById('totalAmount').textContent = totalAmountElement.textContent;
                    }
                    if (paidAmountElement) {
                        document.getElementById('paidAmount').textContent = paidAmountElement.textContent;
                    }
                })
                .catch(error => {
                    console.log('Could not load full invoice details:', error);
                });
        } else {
            invoiceDetailsCard.style.display = 'none';
            amountInput.value = '';
            amountInput.removeAttribute('max');
            remainingAmountText.textContent = '';
        }
    });

    // Validate amount doesn't exceed remaining
    amountInput.addEventListener('input', function() {
        const max = parseFloat(this.max);
        const value = parseFloat(this.value);

        if (max && value > max) {
            this.setCustomValidity('المبلغ يتجاوز المبلغ المتبقي في الفاتورة');
        } else {
            this.setCustomValidity('');
        }
    });

    // Trigger change if invoice is pre-selected
    if (invoiceSelect.value) {
        invoiceSelect.dispatchEvent(new Event('change'));
    }
</script>
@stop
