@php
    $camp = $camp ?? null;
    $isEdit = $camp !== null;
    $val = function ($field, $default = '') use ($isEdit, $camp) {
        if ($isEdit) {
            return old($field, data_get($camp, $field, $default));
        }
        return old($field, $default);
    };
    $dateVal = function ($field) use ($isEdit, $camp) {
        if (old($field)) {
            return old($field);
        }
        if ($isEdit && $camp->{$field}) {
            return $camp->{$field}->format('Y-m-d');
        }
        return '';
    };
@endphp

<div class="row g-4 dashboard-fade-in">
    <div class="col-lg-8">
        <div class="card custom-card group-show-members-card">
            <div class="card-header border-0 pb-0">
                <h6 class="group-show-members-card__title mb-1">البيانات الأساسية</h6>
                <p class="fs-12 text-muted mb-0">اسم المعسكر، الوصف، المدرب، والموقع.</p>
            </div>
            <div class="card-body pt-3">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold" for="campName">اسم المعسكر <span class="text-danger">*</span></label>
                        <input type="text" id="campName" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ $val('name') }}"
                               placeholder="مثال: معسكر البرمجة المكثف" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold" for="campSlug">المعرف (Slug)</label>
                        <input type="text" id="campSlug" name="slug"
                               class="form-control @error('slug') is-invalid @enderror"
                               value="{{ $val('slug') }}"
                               placeholder="يُنشأ تلقائياً من الاسم">
                        <small class="text-muted fs-12">اختياري — يُستخدم في الروابط</small>
                        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold" for="campDescription">وصف المعسكر</label>
                        <textarea id="campDescription" name="description" rows="5"
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="أدخل وصفاً تفصيلياً للمعسكر...">{{ $val('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="campInstructor">اسم المدرب</label>
                        <input type="text" id="campInstructor" name="instructor_name"
                               class="form-control @error('instructor_name') is-invalid @enderror"
                               value="{{ $val('instructor_name') }}"
                               placeholder="أدخل اسم المدرب">
                        @error('instructor_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="campLocation">الموقع / نوع المعسكر</label>
                        <input type="text" id="campLocation" name="location"
                               class="form-control @error('location') is-invalid @enderror"
                               value="{{ $val('location') }}"
                               placeholder="مثال: أونلاين / حضوري — الرياض">
                        @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card custom-card group-show-members-card mt-4">
            <div class="card-header border-0 pb-0">
                <h6 class="group-show-members-card__title mb-1">التواريخ والأسعار</h6>
                <p class="fs-12 text-muted mb-0">حدد فترة المعسكر والتكلفة والحد الأقصى للمشاركين.</p>
            </div>
            <div class="card-body pt-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="campStartDate">تاريخ البداية <span class="text-danger">*</span></label>
                        <input type="date" id="campStartDate" name="start_date"
                               class="form-control @error('start_date') is-invalid @enderror"
                               value="{{ $dateVal('start_date') }}"
                               @unless($isEdit) min="{{ date('Y-m-d') }}" @endunless
                               required>
                        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="campEndDate">تاريخ النهاية <span class="text-danger">*</span></label>
                        <input type="date" id="campEndDate" name="end_date"
                               class="form-control @error('end_date') is-invalid @enderror"
                               value="{{ $dateVal('end_date') }}"
                               @unless($isEdit) min="{{ date('Y-m-d') }}" @endunless
                               required>
                        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="campPrice">السعر ($) <span class="text-danger">*</span></label>
                        <input type="number" id="campPrice" name="price"
                               class="form-control @error('price') is-invalid @enderror"
                               value="{{ $val('price', $isEdit ? null : 0) }}"
                               min="0" step="0.01" placeholder="0.00" required>
                        @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="campMaxParticipants">الحد الأقصى للمشاركين</label>
                        <input type="number" id="campMaxParticipants" name="max_participants"
                               class="form-control @error('max_participants') is-invalid @enderror"
                               value="{{ $val('max_participants') }}"
                               min="1" placeholder="غير محدود">
                        <small class="text-muted fs-12">
                            @if($isEdit)
                                المسجّلون حالياً: <strong>{{ $camp->current_participants }}</strong> —
                            @endif
                            اتركه فارغاً لعدم التحديد
                        </small>
                        @error('max_participants')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card custom-card group-show-members-card mt-4">
            <div class="card-header border-0 pb-0">
                <h6 class="group-show-members-card__title mb-1">صورة المعسكر</h6>
                <p class="fs-12 text-muted mb-0">صورة جذابة تظهر في قائمة المعسكرات وصفحة التفاصيل.</p>
            </div>
            <div class="card-body pt-3">
                @include('admin.pages.courses.partials.form-thumbnail', [
                    'inputId' => 'campImageInput',
                    'previewWrapId' => 'campImagePreview',
                    'existingImageUrl' => ($isEdit && $camp->image) ? asset('storage/' . $camp->image) : null,
                    'altText' => $isEdit ? $camp->name : 'صورة المعسكر',
                ])
            </div>
        </div>

        @include('admin.pages.training-camps.partials.audience-targets', [
            'camp' => $camp ?? null,
            'courses' => $courses ?? collect(),
        ])
    </div>

    <div class="col-lg-4">
        <div class="card custom-card group-show-members-card admin-course-form-page__sidebar">
            <div class="card-header border-0 pb-0">
                <h6 class="group-show-members-card__title mb-1">الإعدادات</h6>
                <p class="fs-12 text-muted mb-0">التصنيف، الترتيب، والحالة.</p>
            </div>
            <div class="card-body pt-3">
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="campCategory">التصنيف</label>
                    <select id="campCategory" name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                        <option value="">اختر التصنيف</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ (string) $val('category_id') === (string) $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="campOrder">الترتيب</label>
                    <input type="number" id="campOrder" name="order"
                           class="form-control @error('order') is-invalid @enderror"
                           value="{{ $val('order', 0) }}" min="0">
                    <small class="text-muted fs-12">يُستخدم لترتيب عرض المعسكرات</small>
                    @error('order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="admin-group-form-toggle">
                    <div class="admin-group-form-toggle__info">
                        <span class="admin-group-form-toggle__label">تفعيل المعسكر</span>
                        <span class="admin-group-form-toggle__hint">يظهر فقط للمجموعات المستهدفة عند التفعيل</span>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="is_active" id="campIsActive"
                               {{ $isEdit ? (old('is_active', $camp->is_active) ? 'checked' : '') : (old('is_active', true) ? 'checked' : '') }}>
                    </div>
                </div>

                <div class="admin-group-form-toggle">
                    <div class="admin-group-form-toggle__info">
                        <span class="admin-group-form-toggle__label">معسكر مميز</span>
                        <span class="admin-group-form-toggle__hint">يظهر في القسم المميز</span>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="is_featured" id="campIsFeatured"
                               {{ $isEdit ? (old('is_featured', $camp->is_featured) ? 'checked' : '') : (old('is_featured') ? 'checked' : '') }}>
                    </div>
                </div>

                <div class="admin-group-form-toggle">
                    <div class="admin-group-form-toggle__info">
                        <span class="admin-group-form-toggle__label">طلب إيصال الدفع</span>
                        <span class="admin-group-form-toggle__hint">إظهار حقل رفع الإيصال وإلزامه عند التسجيل</span>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="require_payment_receipt" id="campRequireReceipt"
                               {{ $isEdit
                                    ? (old('require_payment_receipt', $camp->require_payment_receipt ?? true) ? 'checked' : '')
                                    : (old('require_payment_receipt', true) ? 'checked' : '') }}>
                    </div>
                </div>

                <div class="admin-course-form-page__actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fe fe-save me-1"></i>{{ $submitLabel ?? 'حفظ المعسكر' }}
                    </button>
                    <a href="{{ route('training-camps.index') }}" class="btn btn-outline-secondary">
                        <i class="fe fe-x me-1"></i>إلغاء
                    </a>
                    @if($isEdit)
                        <a href="{{ route('training-camps.show', $camp->id) }}" class="btn btn-outline-primary">
                            <i class="fe fe-eye me-1"></i>عرض المعسكر
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
