<div class="row">
    <!-- Right Column - معلومات الكورس -->
    <div class="col-lg-8">

        <!-- معلومات أساسية -->
        <div class="card custom-card mb-4">
            <div class="card-header">
                <div class="card-title">معلومات الكورس الأساسية</div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">عنوان الكورس <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title', $course->title ?? '') }}" required>
                    @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">عنوان فرعي</label>
                    <input type="text" name="subtitle" class="form-control @error('subtitle') is-invalid @enderror"
                           value="{{ old('subtitle', $course->subtitle ?? '') }}">
                    @error('subtitle')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">الوصف <span class="text-danger">*</span></label>
                    <textarea name="description" rows="5" class="form-control @error('description') is-invalid @enderror" required>{{ old('description', $course->description ?? '') }}</textarea>
                    @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">المتطلبات</label>
                    <textarea name="requirements" rows="3" class="form-control @error('requirements') is-invalid @enderror">{{ old('requirements', $course->requirements ?? '') }}</textarea>
                    @error('requirements')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- محاور ودروس الكورس -->
        <div class="card custom-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">محاور ودروس الكورس</div>
                <button type="button" class="btn btn-sm btn-primary" onclick="addSection()">
                    <i class="bi bi-plus-circle me-1"></i> إضافة محور
                </button>
            </div>
            <div class="card-body">
                <div id="sections-container">
                    @if(isset($course) && $course->sections->count() > 0)
                        @foreach($course->sections as $sectionIndex => $section)
                            <div class="section-item border rounded p-3 mb-3" data-section-index="{{ $sectionIndex }}">
                                <input type="hidden" name="sections[{{ $sectionIndex }}][id]" value="{{ $section->id }}">

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">
                                        <i class="bi bi-folder2-open me-2"></i>
                                        محور #<span class="section-number">{{ $sectionIndex + 1 }}</span>
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeSection(this)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-10">
                                        <label class="form-label">اسم المحور</label>
                                        <input type="text" name="sections[{{ $sectionIndex }}][title]"
                                               class="form-control"
                                               value="{{ $section->title }}"
                                               required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">نشط</label>
                                        <select name="sections[{{ $sectionIndex }}][is_active]" class="form-select">
                                            <option value="1" {{ $section->is_active ? 'selected' : '' }}>نعم</option>
                                            <option value="0" {{ !$section->is_active ? 'selected' : '' }}>لا</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">وصف المحور</label>
                                    <textarea name="sections[{{ $sectionIndex }}][description]" rows="2" class="form-control">{{ $section->description }}</textarea>
                                </div>

                                <!-- Lessons -->
                                <div class="lessons-container ms-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label mb-0">الدروس</label>
                                        <button type="button" class="btn btn-sm btn-success" onclick="addLesson(this)">
                                            <i class="bi bi-plus me-1"></i> إضافة درس
                                        </button>
                                    </div>

                                    @foreach($section->lessons as $lessonIndex => $lesson)
                                        <div class="lesson-item border-start border-3 border-primary ps-3 py-2 mb-2">
                                            <input type="hidden" name="sections[{{ $sectionIndex }}][lessons][{{ $lessonIndex }}][id]" value="{{ $lesson->id }}">

                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <small class="text-muted">درس #<span class="lesson-number">{{ $lessonIndex + 1 }}</span></small>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="removeLesson(this)">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </div>

                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <input type="text" name="sections[{{ $sectionIndex }}][lessons][{{ $lessonIndex }}][title]"
                                                           class="form-control form-control-sm"
                                                           placeholder="اسم الدرس"
                                                           value="{{ $lesson->title }}"
                                                           required>
                                                </div>
                                                <div class="col-md-2">
                                                    <select name="sections[{{ $sectionIndex }}][lessons][{{ $lessonIndex }}][type]" class="form-select form-select-sm">
                                                        <option value="video" {{ $lesson->type == 'video' ? 'selected' : '' }}>فيديو</option>
                                                        <option value="text" {{ $lesson->type == 'text' ? 'selected' : '' }}>نص</option>
                                                        <option value="file" {{ $lesson->type == 'file' ? 'selected' : '' }}>ملف</option>
                                                        <option value="quiz" {{ $lesson->type == 'quiz' ? 'selected' : '' }}>اختبار</option>
                                                        <option value="live" {{ $lesson->type == 'live' ? 'selected' : '' }}>مباشر</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <input type="number" name="sections[{{ $sectionIndex }}][lessons][{{ $lessonIndex }}][duration]"
                                                           class="form-control form-control-sm"
                                                           placeholder="المدة (دقيقة)"
                                                           value="{{ $lesson->duration }}">
                                                </div>
                                                <div class="col-md-2">
                                                    <select name="sections[{{ $sectionIndex }}][lessons][{{ $lessonIndex }}][is_free]" class="form-select form-select-sm">
                                                        <option value="0" {{ !$lesson->is_free ? 'selected' : '' }}>مدفوع</option>
                                                        <option value="1" {{ $lesson->is_free ? 'selected' : '' }}>معاينة</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-folder-plus fs-1"></i>
                            <p>لم يتم إضافة محاور بعد</p>
                            <button type="button" class="btn btn-sm btn-primary" onclick="addSection()">
                                إضافة محور الآن
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <!-- Left Column - الإعدادات -->
    <div class="col-lg-4">

        <!-- إعدادات النشر -->
        <div class="card custom-card mb-4">
            <div class="card-header">
                <div class="card-title">إعدادات النشر</div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">الحالة</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="draft" {{ old('status', $course->status ?? 'draft') == 'draft' ? 'selected' : '' }}>مسودة</option>
                        <option value="published" {{ old('status', $course->status ?? '') == 'published' ? 'selected' : '' }}>منشور</option>
                        <option value="archived" {{ old('status', $course->status ?? '') == 'archived' ? 'selected' : '' }}>مؤرشف</option>
                    </select>
                    @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">التصنيف <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                        <option value="">اختر التصنيف</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $course->category_id ?? '') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('category_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">المدرب <span class="text-danger">*</span></label>
                    <select name="instructor_id" class="form-select @error('instructor_id') is-invalid @enderror" required>
                        <option value="">اختر المدرب</option>
                        @foreach($instructors as $instructor)
                        <option value="{{ $instructor->id }}" {{ old('instructor_id', $course->instructor_id ?? '') == $instructor->id ? 'selected' : '' }}>
                            {{ $instructor->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('instructor_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">المستوى</label>
                    <select name="level" class="form-select @error('level') is-invalid @enderror" required>
                        <option value="beginner" {{ old('level', $course->level ?? 'beginner') == 'beginner' ? 'selected' : '' }}>مبتدئ</option>
                        <option value="intermediate" {{ old('level', $course->level ?? '') == 'intermediate' ? 'selected' : '' }}>متوسط</option>
                        <option value="advanced" {{ old('level', $course->level ?? '') == 'advanced' ? 'selected' : '' }}>متقدم</option>
                    </select>
                    @error('level')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">اللغة</label>
                    <input type="text" name="language" class="form-control @error('language') is-invalid @enderror"
                           value="{{ old('language', $course->language ?? 'ar') }}" required>
                    @error('language')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_featured" value="1"
                               id="is_featured" {{ old('is_featured', $course->is_featured ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_featured">
                            كورس مميز
                        </label>
                    </div>
                    <small class="text-muted">الكورسات المميزة تظهر في الصفحة الرئيسية</small>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                               id="is_active" {{ old('is_active', $course->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            نشط
                        </label>
                    </div>
                    <small class="text-muted">الكورسات النشطة فقط تظهر في الواجهة الأمامية</small>
                </div>
            </div>
        </div>

        <!-- التسعير -->
        <div class="card custom-card mb-4">
            <div class="card-header">
                <div class="card-title">التسعير</div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_free" value="1"
                               id="is_free" {{ old('is_free', $course->is_free ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_free">
                            كورس مجاني
                        </label>
                    </div>
                </div>

                <div id="pricing-fields" style="{{ old('is_free', $course->is_free ?? false) ? 'display:none' : '' }}">
                    <div class="mb-3">
                        <label class="form-label">السعر</label>
                        <input type="number" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror"
                               value="{{ old('price', $course->price ?? 0) }}">
                        @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">سعر الخصم</label>
                        <input type="number" step="0.01" name="discount_price" class="form-control @error('discount_price') is-invalid @enderror"
                               value="{{ old('discount_price', $course->discount_price ?? '') }}">
                        @error('discount_price')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">العملة</label>
                        <select name="currency" class="form-select @error('currency') is-invalid @enderror">
                            <option value="SAR" {{ old('currency', $course->currency ?? 'SAR') == 'SAR' ? 'selected' : '' }}>ريال سعودي (SAR)</option>
                            <option value="USD" {{ old('currency', $course->currency ?? '') == 'USD' ? 'selected' : '' }}>دولار (USD)</option>
                            <option value="EGP" {{ old('currency', $course->currency ?? '') == 'EGP' ? 'selected' : '' }}>جنيه مصري (EGP)</option>
                        </select>
                        @error('currency')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- الصورة المصغرة -->
        <div class="card custom-card mb-4">
            <div class="card-header">
                <div class="card-title">الصورة المصغرة</div>
            </div>
            <div class="card-body">
                <div class="mb-3" id="thumbnail-preview-container">
                    @if(isset($course) && $course->thumbnail)
                    <img src="{{ $course->thumbnail_url }}"
                         alt="Current thumbnail"
                         id="thumbnail-preview"
                         class="img-fluid rounded"
                         style="max-height: 200px;"
                         onerror="this.style.display='none';">
                    @else
                    <img src=""
                         alt="Preview"
                         id="thumbnail-preview"
                         class="img-fluid rounded d-none"
                         style="max-height: 200px;">
                    @endif
                </div>
                <input type="file"
                       name="thumbnail"
                       id="thumbnail-input"
                       class="form-control @error('thumbnail') is-invalid @enderror"
                       accept="image/*"
                       onchange="previewThumbnail(this)">
                <small class="text-muted">يفضل 1200x630 بكسل، الحد الأقصى 2MB</small>
                @error('thumbnail')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- SEO -->
        <div class="card custom-card mb-4">
            <div class="card-header">
                <div class="card-title">تحسين محركات البحث (SEO)</div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">عنوان SEO</label>
                    <input type="text" name="meta_title" class="form-control"
                           value="{{ old('meta_title', $course->meta_title ?? '') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">وصف SEO</label>
                    <textarea name="meta_description" rows="3" class="form-control">{{ old('meta_description', $course->meta_description ?? '') }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">الكلمات المفتاحية</label>
                    <input type="text" name="meta_keywords" class="form-control"
                           value="{{ old('meta_keywords', $course->meta_keywords ?? '') }}"
                           placeholder="كلمة1, كلمة2, كلمة3">
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>
                        {{ isset($course) ? 'تحديث الكورس' : 'حفظ الكورس' }}
                    </button>
                    <a href="{{ route('admin.frontend-courses.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-2"></i>
                        إلغاء
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
console.log('✅ Form script loaded!');
let sectionIndex = {{ isset($course) && $course->sections->count() > 0 ? $course->sections->count() : 0 }};
console.log('Section index:', sectionIndex);

// Toggle pricing fields based on is_free checkbox
document.getElementById('is_free').addEventListener('change', function() {
    document.getElementById('pricing-fields').style.display = this.checked ? 'none' : 'block';
});

function addSection() {
    console.log('🔵 addSection() called');
    const container = document.getElementById('sections-container');
    console.log('Container:', container);

    // Remove empty state if exists
    const emptyState = container.querySelector('.text-center.text-muted');
    if (emptyState) {
        emptyState.remove();
    }

    const sectionHtml = `
        <div class="section-item border rounded p-3 mb-3" data-section-index="${sectionIndex}">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">
                    <i class="bi bi-folder2-open me-2"></i>
                    محور #<span class="section-number">${sectionIndex + 1}</span>
                </h6>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeSection(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>

            <div class="row mb-3">
                <div class="col-md-10">
                    <label class="form-label">اسم المحور</label>
                    <input type="text" name="sections[${sectionIndex}][title]" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">نشط</label>
                    <select name="sections[${sectionIndex}][is_active]" class="form-select">
                        <option value="1" selected>نعم</option>
                        <option value="0">لا</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">وصف المحور</label>
                <textarea name="sections[${sectionIndex}][description]" rows="2" class="form-control"></textarea>
            </div>

            <div class="lessons-container ms-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">الدروس</label>
                    <button type="button" class="btn btn-sm btn-success" onclick="addLesson(this)">
                        <i class="bi bi-plus me-1"></i> إضافة درس
                    </button>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', sectionHtml);
    sectionIndex++;
    updateSectionNumbers();
}

function removeSection(btn) {
    if (confirm('هل أنت متأكد من حذف هذا المحور وجميع دروسه؟')) {
        btn.closest('.section-item').remove();
        updateSectionNumbers();

        // Show empty state if no sections
        const container = document.getElementById('sections-container');
        if (container.children.length === 0) {
            container.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="bi bi-folder-plus fs-1"></i>
                    <p>لم يتم إضافة محاور بعد</p>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addSection()">
                        إضافة محور الآن
                    </button>
                </div>
            `;
        }
    }
}

function addLesson(btn) {
    const sectionItem = btn.closest('.section-item');
    const sectionIdx = sectionItem.dataset.sectionIndex;
    const lessonsContainer = sectionItem.querySelector('.lessons-container');
    const lessonCount = lessonsContainer.querySelectorAll('.lesson-item').length;

    const lessonHtml = `
        <div class="lesson-item border-start border-3 border-primary ps-3 py-2 mb-2">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <small class="text-muted">درس #<span class="lesson-number">${lessonCount + 1}</span></small>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeLesson(this)">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <div class="row g-2">
                <div class="col-md-6">
                    <input type="text" name="sections[${sectionIdx}][lessons][${lessonCount}][title]"
                           class="form-control form-control-sm"
                           placeholder="اسم الدرس"
                           required>
                </div>
                <div class="col-md-2">
                    <select name="sections[${sectionIdx}][lessons][${lessonCount}][type]" class="form-select form-select-sm">
                        <option value="video">فيديو</option>
                        <option value="text">نص</option>
                        <option value="file">ملف</option>
                        <option value="quiz">اختبار</option>
                        <option value="live">مباشر</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" name="sections[${sectionIdx}][lessons][${lessonCount}][duration]"
                           class="form-control form-control-sm"
                           placeholder="المدة (دقيقة)">
                </div>
                <div class="col-md-2">
                    <select name="sections[${sectionIdx}][lessons][${lessonCount}][is_free]" class="form-select form-select-sm">
                        <option value="0">مدفوع</option>
                        <option value="1">معاينة</option>
                    </select>
                </div>
            </div>
        </div>
    `;

    lessonsContainer.insertAdjacentHTML('beforeend', lessonHtml);
    updateLessonNumbers(sectionItem);
}

function removeLesson(btn) {
    const sectionItem = btn.closest('.section-item');
    btn.closest('.lesson-item').remove();
    updateLessonNumbers(sectionItem);
}

function updateSectionNumbers() {
    document.querySelectorAll('.section-item').forEach((section, index) => {
        section.querySelector('.section-number').textContent = index + 1;
    });
}

function updateLessonNumbers(sectionItem) {
    sectionItem.querySelectorAll('.lesson-item').forEach((lesson, index) => {
        lesson.querySelector('.lesson-number').textContent = index + 1;
    });
}

function previewThumbnail(input) {
    const preview = document.getElementById('thumbnail-preview');

    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
            console.log('✅ Thumbnail preview loaded');
        };

        reader.readAsDataURL(input.files[0]);
    } else {
        preview.src = '';
        preview.classList.add('d-none');
    }
}
</script>
