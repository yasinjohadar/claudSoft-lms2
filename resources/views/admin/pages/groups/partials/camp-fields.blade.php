{{-- Camp-style paid group fields (shown when is_camp is enabled) --}}
@php
    $hasOldInput = old('_token') !== null;
    $campEnabled = $hasOldInput
        ? (bool) old('is_camp')
        : (isset($group) ? (bool) $group->is_camp : false);
    $campPriceValue = old('price', isset($group) ? $group->price : '');
    $campStartValue = old('start_date', isset($group) && $group->start_date ? $group->start_date->format('Y-m-d') : '');
    $campEndValue = old('end_date', isset($group) && $group->end_date ? $group->end_date->format('Y-m-d') : '');
    $requireReceipt = $hasOldInput
        ? (bool) old('require_payment_receipt')
        : (isset($group) ? (bool) ($group->require_payment_receipt ?? true) : true);
@endphp
<div class="admin-group-form-toggle">
    <div class="admin-group-form-toggle__info">
        <span class="admin-group-form-toggle__label">مجموعة معسكر (مدفوعة)</span>
        <span class="admin-group-form-toggle__hint">تفعيل السعر وإنشاء فاتورة عند إضافة عضو أو الموافقة على طلبه</span>
    </div>
    <div class="form-check form-switch mb-0">
        <input class="form-check-input" type="checkbox" name="is_camp" id="is_camp"
               {{ $campEnabled ? 'checked' : '' }}>
    </div>
</div>

<div id="camp-group-fields" class="{{ $campEnabled ? '' : 'd-none' }}">
    <div class="mb-3">
        <label class="form-label fw-semibold" for="camp_price">السعر <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number" name="price" id="camp_price" step="0.01" min="0"
                   class="form-control @error('price') is-invalid @enderror"
                   value="{{ $campPriceValue }}"
                   placeholder="0.00">
        </div>
        @error('price')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="admin-group-form-toggle mb-3">
        <div class="admin-group-form-toggle__info">
            <span class="admin-group-form-toggle__label">طلب إيصال الدفع</span>
            <span class="admin-group-form-toggle__hint">عند التفعيل يظهر حقل رفع الإيصال للطالب ويكون مطلوباً</span>
        </div>
        <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" name="require_payment_receipt" id="require_payment_receipt"
                   {{ $requireReceipt ? 'checked' : '' }}>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold" for="camp_start_date">تاريخ البداية</label>
        <input type="date" name="start_date" id="camp_start_date"
               class="form-control @error('start_date') is-invalid @enderror"
               value="{{ $campStartValue }}">
        <small class="text-muted fs-12">للعرض والتنظيم فقط</small>
        @error('start_date')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold" for="camp_end_date">تاريخ النهاية</label>
        <input type="date" name="end_date" id="camp_end_date"
               class="form-control @error('end_date') is-invalid @enderror"
               value="{{ $campEndValue }}">
        <small class="text-muted fs-12">للعرض والتنظيم فقط</small>
        @error('end_date')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>
