@php
    $work = $work ?? null;
    $isEdit = $work !== null;

    $val = function ($field, $default = '') use ($work) {
        if (old($field) !== null) {
            return old($field);
        }
        if ($work === null) {
            return $default;
        }
        $value = data_get($work, $field);
        if ($value instanceof \Carbon\CarbonInterface) {
            return $value->format('Y-m-d');
        }
        return $value ?? $default;
    };
@endphp

<div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
    <div class="card-header border-0 pb-0">
        <h6 class="group-show-members-card__title mb-1">
            <i class="fe fe-edit-3 me-2 text-primary"></i>معلومات العمل الأساسية
        </h6>
        <p class="fs-12 text-muted mb-0">العنوان والتصنيف والوصف والتقنيات.</p>
    </div>
    <div class="card-body pt-3">
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">عنوان العمل <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                       value="{{ $val('title') }}" placeholder="مثال: نظام إدارة المكتبة" required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">التصنيف <span class="text-danger">*</span></label>
                <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                    <option value="">اختر التصنيف</option>
                    @foreach($categories as $key => $category)
                        <option value="{{ $key }}" {{ $val('category') == $key ? 'selected' : '' }}>
                            {{ $category['name'] }}
                        </option>
                    @endforeach
                </select>
                @error('category')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">الدورة التدريبية</label>
                <select name="course_id" class="form-select @error('course_id') is-invalid @enderror">
                    <option value="">اختر الدورة (اختياري)</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ (string) $val('course_id') === (string) $course->id ? 'selected' : '' }}>
                            {{ $course->title }}
                        </option>
                    @endforeach
                </select>
                @error('course_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold">الوصف</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                          rows="5" placeholder="اكتب وصفاً تفصيلياً عن عملك...">{{ $val('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">صف فكرة العمل، الأهداف، والميزات الرئيسية</small>
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold">التقنيات المستخدمة</label>
                <input type="text" name="technologies" class="form-control @error('technologies') is-invalid @enderror"
                       value="{{ $val('technologies') }}" placeholder="مثال: Laravel, Vue.js, MySQL, Bootstrap">
                @error('technologies')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">اكتب التقنيات مفصولة بفواصل</small>
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold">الوسوم</label>
                <div id="tags-container" class="student-work-form__tags mb-2"></div>
                <input type="text" id="tag-input" class="form-control" placeholder="اضغط Enter لإضافة وسم">
                <div id="tags-hidden-container"></div>
                <small class="text-muted">أضف وسوماً للمساعدة في البحث عن عملك</small>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">تاريخ الإنجاز</label>
                <input type="date" name="completion_date" class="form-control @error('completion_date') is-invalid @enderror"
                       value="{{ $val('completion_date') }}">
                @error('completion_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

<div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
    <div class="card-header border-0 pb-0">
        <h6 class="group-show-members-card__title mb-1">
            <i class="fe fe-link me-2 text-primary"></i>الروابط والمصادر
        </h6>
        <p class="fs-12 text-muted mb-0">أضف روابط GitHub والتجربة الحية والفيديو.</p>
    </div>
    <div class="card-body pt-3">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fe fe-github me-1"></i>رابط GitHub
                </label>
                <input type="url" name="github_url" class="form-control @error('github_url') is-invalid @enderror"
                       value="{{ $val('github_url') }}" placeholder="https://github.com/username/project">
                @error('github_url')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fe fe-globe me-1"></i>رابط التجربة الحية (Demo)
                </label>
                <input type="url" name="demo_url" class="form-control @error('demo_url') is-invalid @enderror"
                       value="{{ $val('demo_url') }}" placeholder="https://demo.example.com">
                @error('demo_url')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fe fe-link me-1"></i>رابط الموقع
                </label>
                <input type="url" name="website_url" class="form-control @error('website_url') is-invalid @enderror"
                       value="{{ $val('website_url') }}" placeholder="https://example.com">
                @error('website_url')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fe fe-play-circle me-1"></i>رابط فيديو توضيحي
                </label>
                <input type="url" name="video_url" class="form-control @error('video_url') is-invalid @enderror"
                       value="{{ $val('video_url') }}" placeholder="https://youtube.com/watch?v=...">
                @error('video_url')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">رابط YouTube أو Vimeo</small>
            </div>
        </div>
    </div>
</div>
