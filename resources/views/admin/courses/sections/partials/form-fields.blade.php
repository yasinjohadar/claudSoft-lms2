@php
    /** @var \App\Models\Course $course */
    $isEdit = isset($section);
    $titleValue = old('title', $isEdit ? $section->title : '');
    $descriptionValue = old('description', $isEdit ? $section->description : '');
    $isVisible = old('is_visible', $isEdit ? $section->is_visible : true);
    $isLocked = old('is_locked', $isEdit ? $section->is_locked : false);
    $showUnavailable = old('show_unavailable', $isEdit ? $section->show_unavailable : true);
    $availableFrom = old(
        'available_from',
        $isEdit && $section->available_from ? $section->available_from->format('Y-m-d\TH:i') : ''
    );
    $availableUntil = old(
        'available_until',
        $isEdit && $section->available_until ? $section->available_until->format('Y-m-d\TH:i') : ''
    );
@endphp

<div class="row g-4 dashboard-fade-in">
    <div class="col-lg-8">
        <div class="card custom-card group-show-members-card">
            <div class="card-header border-0 pb-0">
                <h6 class="group-show-members-card__title mb-1">المعلومات الأساسية</h6>
                <p class="fs-12 text-muted mb-0">عنوان القسم ووصفه الظاهران للطلاب داخل الكورس.</p>
            </div>
            <div class="card-body pt-3">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">عنوان القسم <span class="text-danger">*</span></label>
                        <input type="text"
                               name="title"
                               class="form-control @error('title') is-invalid @enderror"
                               value="{{ $titleValue }}"
                               required
                               placeholder="مثال: دروس شرح لغة HTML5">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">الوصف</label>
                        <textarea name="description"
                                  rows="4"
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="وصف مختصر يوضح محتوى هذا القسم (اختياري)">{{ $descriptionValue }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card custom-card group-show-members-card mt-4">
            <div class="card-header border-0 pb-0">
                <h6 class="group-show-members-card__title mb-1">نوع القسم <span class="text-danger">*</span></h6>
                <p class="fs-12 text-muted mb-0">اختر النوع المناسب ليظهر بأيقونة ولون مميّزين في قائمة الأقسام.</p>
            </div>
            <div class="card-body pt-3">
                @include('admin.courses.sections.partials.section-type-field', [
                    'section' => $section ?? null,
                ])
            </div>
        </div>

        <div class="card custom-card group-show-members-card mt-4 d-none d-lg-block">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="fe fe-save me-1"></i>{{ $isEdit ? 'حفظ التعديلات' : 'حفظ القسم' }}
                    </button>
                    <a href="{{ route('courses.show', $course->id) }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fe fe-x me-1"></i>إلغاء
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card custom-card group-show-members-card admin-section-form-page__sidebar">
            <div class="card-header border-0 pb-0">
                <h6 class="group-show-members-card__title mb-1">إعدادات الظهور</h6>
                <p class="fs-12 text-muted mb-0">تحكم بظهور القسم وقفله للطلاب.</p>
            </div>
            <div class="card-body pt-3">
                <div class="admin-section-form-page__toggle-list">
                    <label class="admin-section-form-page__toggle">
                        <span class="admin-section-form-page__toggle-text">
                            <i class="fe fe-eye text-primary"></i>
                            <span>
                                <strong>مرئي للطلاب</strong>
                                <small>يظهر القسم ضمن مسار التعلّم</small>
                            </span>
                        </span>
                        <input class="form-check-input m-0" type="checkbox" name="is_visible" value="1" {{ $isVisible ? 'checked' : '' }}>
                    </label>
                    <label class="admin-section-form-page__toggle">
                        <span class="admin-section-form-page__toggle-text">
                            <i class="fe fe-lock text-warning"></i>
                            <span>
                                <strong>قفل القسم</strong>
                                <small>يتطلب شروطاً لفتحه</small>
                            </span>
                        </span>
                        <input class="form-check-input m-0" type="checkbox" name="is_locked" value="1" {{ $isLocked ? 'checked' : '' }}>
                    </label>
                    <label class="admin-section-form-page__toggle">
                        <span class="admin-section-form-page__toggle-text">
                            <i class="fe fe-eye-off text-info"></i>
                            <span>
                                <strong>إظهار غير المتاح</strong>
                                <small>يعرض المحتوى المغلق كمعاينة</small>
                            </span>
                        </span>
                        <input class="form-check-input m-0" type="checkbox" name="show_unavailable" value="1" {{ $showUnavailable ? 'checked' : '' }}>
                    </label>
                </div>
            </div>
        </div>

        <div class="card custom-card group-show-members-card admin-section-form-page__sidebar mt-4">
            <div class="card-header border-0 pb-0">
                <h6 class="group-show-members-card__title mb-1">فترة الإتاحة</h6>
                <p class="fs-12 text-muted mb-0">اترك الحقول فارغة إن لم ترد تقييد الوقت.</p>
            </div>
            <div class="card-body pt-3">
                <div class="mb-3">
                    <label class="form-label fw-semibold">متاح من</label>
                    <input type="datetime-local"
                           name="available_from"
                           class="form-control @error('available_from') is-invalid @enderror"
                           value="{{ $availableFrom }}">
                    @error('available_from')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">فارغ = متاح فوراً</small>
                </div>
                <div>
                    <label class="form-label fw-semibold">متاح حتى</label>
                    <input type="datetime-local"
                           name="available_until"
                           class="form-control @error('available_until') is-invalid @enderror"
                           value="{{ $availableUntil }}">
                    @error('available_until')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">فارغ = بدون نهاية</small>
                </div>
            </div>
        </div>

        <div class="card custom-card group-show-members-card mt-4 d-lg-none">
            <div class="card-body py-3">
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill">
                        <i class="fe fe-save me-1"></i>{{ $isEdit ? 'حفظ التعديلات' : 'حفظ القسم' }}
                    </button>
                    <a href="{{ route('courses.show', $course->id) }}" class="btn btn-outline-secondary rounded-pill">
                        إلغاء
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
