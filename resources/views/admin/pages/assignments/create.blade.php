@extends('admin.layouts.master')

@section('page-title')
    إضافة واجب جديد
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إضافة واجب جديد</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('assignments.index') }}">الواجبات</a></li>
                            <li class="breadcrumb-item active">إضافة واجب</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Display Alerts -->
            @include('admin.components.alerts')

            <form action="{{ route('assignments.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- إخفاء section_id للإرسال -->
                @if($selectedSection)
                    <input type="hidden" name="section_id" value="{{ $selectedSection->id }}">
                @endif

                <!-- رسالة تنبيه إذا تم التحديد من القسم -->
                @if($selectedSection)
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>إضافة واجب للقسم:</strong> {{ $selectedSection->title }} -
                        <strong>الكورس:</strong> {{ $selectedCourse->title }}
                    </div>
                @endif

                <!-- Basic Information -->
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-info-circle me-2"></i>المعلومات الأساسية
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">عنوان الواجب <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                       value="{{ old('title') }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">الكورس <span class="text-danger">*</span></label>
                                <select name="course_id" id="course_id" class="form-select @error('course_id') is-invalid @enderror"
                                        {{ $selectedSection ? 'disabled' : '' }} required>
                                    <option value="">اختر الكورس</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}"
                                            {{ old('course_id', $selectedCourse?->id) == $course->id ? 'selected' : '' }}>
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                                <!-- إرسال القيمة حتى لو disabled -->
                                @if($selectedSection)
                                    <input type="hidden" name="course_id" value="{{ $selectedCourse->id }}">
                                @endif
                                @error('course_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @if(!$selectedSection)
                                <div class="col-md-3">
                                    <label class="form-label">الدرس (اختياري)</label>
                                    <select name="lesson_id" id="lesson_id" class="form-select @error('lesson_id') is-invalid @enderror">
                                        <option value="">لا يوجد دروس مرتبطة</option>
                                    </select>
                                    <small class="text-muted">الدروس مرتبطة بالأقسام عبر course_modules</small>
                                    @error('lesson_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

                            <div class="col-12">
                                <label class="form-label">الوصف</label>
                                <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                                          rows="3">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">التعليمات</label>
                                <textarea name="instructions" id="instructions" class="form-control @error('instructions') is-invalid @enderror"
                                          rows="4">{{ old('instructions') }}</textarea>
                                @error('instructions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grading Settings -->
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-star me-2"></i>إعدادات الدرجات
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">الدرجة القصوى <span class="text-danger">*</span></label>
                                <input type="number" name="max_grade" class="form-control @error('max_grade') is-invalid @enderror"
                                       value="{{ old('max_grade', 100) }}" min="1" max="1000" required>
                                @error('max_grade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">نوع التسليم <span class="text-danger">*</span></label>
                                <select name="submission_type" id="submission_type" class="form-select @error('submission_type') is-invalid @enderror" required>
                                    <option value="link" {{ old('submission_type') == 'link' ? 'selected' : '' }}>روابط فقط</option>
                                    <option value="file" {{ old('submission_type') == 'file' ? 'selected' : '' }}>ملفات فقط</option>
                                    <option value="both" {{ old('submission_type', 'both') == 'both' ? 'selected' : '' }}>روابط وملفات</option>
                                </select>
                                @error('submission_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submission Settings -->
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-cog me-2"></i>إعدادات التسليم
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4" id="link_settings">
                                <label class="form-label">الحد الأقصى للروابط</label>
                                <input type="number" name="max_links" class="form-control @error('max_links') is-invalid @enderror"
                                       value="{{ old('max_links', 5) }}" min="1" max="20">
                                @error('max_links')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4" id="file_settings_count">
                                <label class="form-label">الحد الأقصى للملفات</label>
                                <input type="number" name="max_files" class="form-control @error('max_files') is-invalid @enderror"
                                       value="{{ old('max_files', 5) }}" min="1" max="20">
                                @error('max_files')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4" id="file_settings_size">
                                <label class="form-label">الحد الأقصى لحجم الملف (KB)</label>
                                <input type="number" name="max_file_size" class="form-control @error('max_file_size') is-invalid @enderror"
                                       value="{{ old('max_file_size', 10240) }}" min="1024" max="102400">
                                @error('max_file_size')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">القيمة بالكيلوبايت (1 MB = 1024 KB)</small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">ترتيب العرض</label>
                                <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
                                       value="{{ old('sort_order', 0) }}" min="0">
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Deadlines -->
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-clock me-2"></i>المواعيد النهائية
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">متاح من</label>
                                <input type="datetime-local" name="available_from" class="form-control @error('available_from') is-invalid @enderror"
                                       value="{{ old('available_from') }}">
                                @error('available_from')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">موعد التسليم</label>
                                <input type="datetime-local" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                                       value="{{ old('due_date') }}">
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">آخر موعد للتسليم المتأخر</label>
                                <input type="datetime-local" name="late_submission_until" class="form-control @error('late_submission_until') is-invalid @enderror"
                                       value="{{ old('late_submission_until') }}">
                                @error('late_submission_until')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">السماح بالتسليم المتأخر</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="allow_late_submission" id="allow_late_submission"
                                           {{ old('allow_late_submission') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_late_submission">
                                        تفعيل التسليم المتأخر
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">نسبة الخصم على التأخير (%)</label>
                                <input type="number" name="late_penalty_percentage" class="form-control @error('late_penalty_percentage') is-invalid @enderror"
                                       value="{{ old('late_penalty_percentage', 0) }}" min="0" max="100">
                                @error('late_penalty_percentage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resubmission Settings -->
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-redo me-2"></i>إعدادات إعادة التسليم
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="allow_resubmission" id="allow_resubmission"
                                           {{ old('allow_resubmission') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_resubmission">
                                        السماح بإعادة التسليم
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6" id="resubmission_settings" style="display: none;">
                                <label class="form-label">الحد الأقصى لإعادة التسليم</label>
                                <input type="number" name="max_resubmissions" class="form-control @error('max_resubmissions') is-invalid @enderror"
                                       value="{{ old('max_resubmissions') }}" min="1" max="10" placeholder="اتركه فارغاً للسماح بعدد غير محدود">
                                @error('max_resubmissions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6" id="resubmission_grading" style="display: none;">
                                <label class="form-label">شرط إعادة التسليم</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="resubmit_after_grading_only" id="resubmit_after_grading_only"
                                           {{ old('resubmit_after_grading_only', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="resubmit_after_grading_only">
                                        السماح بإعادة التسليم فقط بعد التقييم
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attachments -->
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-paperclip me-2"></i>المرفقات (موارد للطلاب)
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">إضافة ملفات</label>
                            <input type="file" name="attachments[]" class="form-control @error('attachments.*') is-invalid @enderror"
                                   multiple accept=".pdf,.doc,.docx,.txt,.zip">
                            <small class="text-muted">يمكنك إضافة ملفات مساعدة للطلاب (PDF, DOC, TXT, ZIP) - الحد الأقصى 10 MB لكل ملف</small>
                            @error('attachments.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Visibility Settings -->
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-eye me-2"></i>إعدادات الظهور
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_published" id="is_published"
                                           {{ old('is_published', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_published">
                                        نشر الواجب
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_visible" id="is_visible"
                                           {{ old('is_visible', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_visible">
                                        إظهار الواجب للطلاب
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('assignments.index') }}" class="btn btn-light">
                                <i class="fas fa-times me-2"></i>إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>حفظ الواجب
                            </button>
                        </div>
                    </div>
                </div>

            </form>

        </div>
    </div>
@stop

@section('script')
<script src="https://cdn.jsdelivr.net/npm/tinymce@5.10.9/tinymce.min.js"></script>
<script>
    tinymce.init({
        selector: '#description, #instructions',
        directionality: 'rtl',
        height: 300,
        menubar: false,
        plugins: [
            'advlist autolink lists link charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime table paste code help wordcount codesample'
        ],
        toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link table | codesample code | fullscreen',
        codesample_languages: [
            { text: 'HTML/XML', value: 'markup' },
            { text: 'JavaScript', value: 'javascript' },
            { text: 'CSS', value: 'css' },
            { text: 'PHP', value: 'php' },
            { text: 'Python', value: 'python' },
            { text: 'Java', value: 'java' },
            { text: 'C', value: 'c' },
            { text: 'C++', value: 'cpp' },
            { text: 'SQL', value: 'sql' }
        ],
        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial; font-size: 14px; direction: rtl; }'
    });

    // Load lessons based on selected course
    document.getElementById('course_id').addEventListener('change', function() {
        const courseId = this.value;
        const lessonSelect = document.getElementById('lesson_id');

        lessonSelect.innerHTML = '<option value="">جاري التحميل...</option>';

        if (!courseId) {
            lessonSelect.innerHTML = '<option value="">اختر الدرس</option>';
            return;
        }

        const routeUrl = '{{ route("assignments.get-lessons", ["courseId" => ":courseId"]) }}'.replace(':courseId', courseId);
        fetch(routeUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                lessonSelect.innerHTML = '<option value="">لا يوجد دروس مرتبطة</option>';
                if (data && Array.isArray(data) && data.length > 0) {
                    data.forEach(lesson => {
                        if (lesson && lesson.id && lesson.title) {
                            const option = document.createElement('option');
                            option.value = lesson.id;
                            option.textContent = lesson.title;
                            lessonSelect.appendChild(option);
                        }
                    });
                } else {
                    lessonSelect.innerHTML = '<option value="">لا توجد دروس متاحة لهذا الكورس</option>';
                }
            })
            .catch(error => {
                console.error('Error loading lessons:', error);
                lessonSelect.innerHTML = '<option value="">خطأ في تحميل الدروس</option>';
            });
    });

    // Toggle submission type settings
    document.getElementById('submission_type').addEventListener('change', function() {
        const type = this.value;
        const linkSettings = document.getElementById('link_settings');
        const fileSettingsCount = document.getElementById('file_settings_count');
        const fileSettingsSize = document.getElementById('file_settings_size');

        if (type === 'link') {
            linkSettings.style.display = 'block';
            fileSettingsCount.style.display = 'none';
            fileSettingsSize.style.display = 'none';
        } else if (type === 'file') {
            linkSettings.style.display = 'none';
            fileSettingsCount.style.display = 'block';
            fileSettingsSize.style.display = 'block';
        } else { // both
            linkSettings.style.display = 'block';
            fileSettingsCount.style.display = 'block';
            fileSettingsSize.style.display = 'block';
        }
    });

    // Trigger on page load
    document.getElementById('submission_type').dispatchEvent(new Event('change'));

    // Toggle resubmission settings
    document.getElementById('allow_resubmission').addEventListener('change', function() {
        const resubmissionSettings = document.getElementById('resubmission_settings');
        const resubmissionGrading = document.getElementById('resubmission_grading');

        if (this.checked) {
            resubmissionSettings.style.display = 'block';
            resubmissionGrading.style.display = 'block';
        } else {
            resubmissionSettings.style.display = 'none';
            resubmissionGrading.style.display = 'none';
        }
    });

    // Trigger on page load
    if (document.getElementById('allow_resubmission').checked) {
        document.getElementById('resubmission_settings').style.display = 'block';
        document.getElementById('resubmission_grading').style.display = 'block';
    }

    // Clear hidden field values before form submission to prevent validation errors
    document.querySelector('form').addEventListener('submit', function(e) {
        const type = document.getElementById('submission_type').value;

        if (type === 'link') {
            // Clear file-related fields when only links are allowed
            const maxFiles = document.querySelector('input[name="max_files"]');
            const maxFileSize = document.querySelector('input[name="max_file_size"]');
            if (maxFiles) maxFiles.removeAttribute('required');
            if (maxFileSize) maxFileSize.removeAttribute('required');
        } else if (type === 'file') {
            // Clear link-related fields when only files are allowed
            const maxLinks = document.querySelector('input[name="max_links"]');
            if (maxLinks) maxLinks.removeAttribute('required');
        }
    });
</script>
@stop
