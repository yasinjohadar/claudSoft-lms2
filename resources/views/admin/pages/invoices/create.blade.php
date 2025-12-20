@extends('admin.layouts.master')

@section('page-title')
    إنشاء فاتورة جديدة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إنشاء فاتورة جديدة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">الفواتير</a></li>
                            <li class="breadcrumb-item active">إنشاء فاتورة</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">معلومات الفاتورة</div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('invoices.store') }}" method="POST" id="invoiceForm">
                                @csrf

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">الطالب <span class="text-danger">*</span></label>
                                        <select name="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
                                            <option value="">اختر الطالب</option>
                                            @foreach($students as $student)
                                                <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                                    {{ $student->name }} ({{ $student->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('student_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">تاريخ الإصدار <span class="text-danger">*</span></label>
                                        <input type="date" name="issue_date" class="form-control @error('issue_date') is-invalid @enderror"
                                               value="{{ old('issue_date', date('Y-m-d')) }}" required>
                                        @error('issue_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">تاريخ الاستحقاق</label>
                                        <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                                               value="{{ old('due_date') }}">
                                        @error('due_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">الحالة <span class="text-danger">*</span></label>
                                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                            <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                                            <option value="issued" {{ old('status', 'issued') == 'issued' ? 'selected' : '' }}>صادرة</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">ملاحظات</label>
                                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes') }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <hr class="my-4">

                                <h6 class="mb-3">بنود الفاتورة</h6>

                                <div id="invoiceItems">
                                    <div class="invoice-item border rounded p-3 mb-3">
                                        <div class="row">
                                            <div class="col-md-5">
                                                <label class="form-label">الوصف <span class="text-danger">*</span></label>
                                                <input type="text" name="items[0][description]" class="form-control" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">الكمية <span class="text-danger">*</span></label>
                                                <input type="number" name="items[0][quantity]" class="form-control item-quantity"
                                                       value="1" min="1" step="1" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">سعر الوحدة ($) <span class="text-danger">*</span></label>
                                                <input type="number" name="items[0][unit_price]" class="form-control item-unit-price"
                                                       value="0" min="0" step="0.01" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">المجموع ($)</label>
                                                <input type="text" class="form-control item-total" value="$0.00" readonly>
                                            </div>
                                            <div class="col-md-1">
                                                <label class="form-label">&nbsp;</label>
                                                <button type="button" class="btn btn-danger btn-sm w-100 remove-item" disabled>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" id="addItem" class="btn btn-success btn-sm mb-4">
                                    <i class="fas fa-plus me-1"></i>إضافة بند جديد
                                </button>

                                <hr class="my-4">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">الضريبة ($)</label>
                                                <input type="number" name="tax_amount" id="tax_amount" class="form-control"
                                                       value="{{ old('tax_amount', 0) }}" min="0" step="0.01">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">الخصم ($)</label>
                                                <input type="number" name="discount_amount" id="discount_amount" class="form-control"
                                                       value="{{ old('discount_amount', 0) }}" min="0" step="0.01">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>المجموع الفرعي:</span>
                                                    <strong id="subtotal">$0.00</strong>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>الضريبة:</span>
                                                    <strong id="displayTax">$0.00</strong>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>الخصم:</span>
                                                    <strong class="text-danger" id="displayDiscount">$0.00</strong>
                                                </div>
                                                <hr>
                                                <div class="d-flex justify-content-between">
                                                    <span class="fs-5">الإجمالي:</span>
                                                    <strong class="fs-5 text-primary" id="grandTotal">$0.00</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>حفظ الفاتورة
                                    </button>
                                    <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>إلغاء
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

@section('scripts')
<script>
    let itemCount = 1;

    // Add new item
    document.getElementById('addItem').addEventListener('click', function() {
        const itemsContainer = document.getElementById('invoiceItems');
        const newItem = document.createElement('div');
        newItem.className = 'invoice-item border rounded p-3 mb-3';
        newItem.innerHTML = `
            <div class="row">
                <div class="col-md-5">
                    <label class="form-label">الوصف <span class="text-danger">*</span></label>
                    <input type="text" name="items[${itemCount}][description]" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">الكمية <span class="text-danger">*</span></label>
                    <input type="number" name="items[${itemCount}][quantity]" class="form-control item-quantity"
                           value="1" min="1" step="1" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">سعر الوحدة ($) <span class="text-danger">*</span></label>
                    <input type="number" name="items[${itemCount}][unit_price]" class="form-control item-unit-price"
                           value="0" min="0" step="0.01" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">المجموع ($)</label>
                    <input type="text" class="form-control item-total" value="$0.00" readonly>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-sm w-100 remove-item">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        itemsContainer.appendChild(newItem);
        itemCount++;
        updateRemoveButtons();
    });

    // Remove item
    document.getElementById('invoiceItems').addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            e.target.closest('.invoice-item').remove();
            updateRemoveButtons();
            calculateTotals();
        }
    });

    // Update remove buttons state
    function updateRemoveButtons() {
        const items = document.querySelectorAll('.invoice-item');
        const removeButtons = document.querySelectorAll('.remove-item');
        removeButtons.forEach(btn => {
            btn.disabled = items.length <= 1;
        });
    }

    // Calculate item totals
    document.getElementById('invoiceItems').addEventListener('input', function(e) {
        if (e.target.classList.contains('item-quantity') || e.target.classList.contains('item-unit-price')) {
            const row = e.target.closest('.invoice-item');
            const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
            const unitPrice = parseFloat(row.querySelector('.item-unit-price').value) || 0;
            const total = quantity * unitPrice;
            row.querySelector('.item-total').value = '$' + total.toFixed(2);
            calculateTotals();
        }
    });

    // Calculate grand totals
    function calculateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.invoice-item').forEach(item => {
            const quantity = parseFloat(item.querySelector('.item-quantity').value) || 0;
            const unitPrice = parseFloat(item.querySelector('.item-unit-price').value) || 0;
            subtotal += quantity * unitPrice;
        });

        const tax = parseFloat(document.getElementById('tax_amount').value) || 0;
        const discount = parseFloat(document.getElementById('discount_amount').value) || 0;
        const grandTotal = subtotal + tax - discount;

        document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
        document.getElementById('displayTax').textContent = '$' + tax.toFixed(2);
        document.getElementById('displayDiscount').textContent = '$' + discount.toFixed(2);
        document.getElementById('grandTotal').textContent = '$' + grandTotal.toFixed(2);
    }

    // Listen to tax and discount changes
    document.getElementById('tax_amount').addEventListener('input', calculateTotals);
    document.getElementById('discount_amount').addEventListener('input', calculateTotals);

    // Initialize
    updateRemoveButtons();
    calculateTotals();
</script>
@stop
