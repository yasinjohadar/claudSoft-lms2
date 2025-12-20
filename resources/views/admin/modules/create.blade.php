@extends('admin.layouts.master')

@section('page-title')
    إضافة درس جديد
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إضافة درس جديد للقسم: {{ $section->title }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.show', $section->course_id) }}">{{ $section->course->title }}</a></li>
                            <li class="breadcrumb-item active">إضافة درس</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Module Form -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">معلومات الدرس</div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('sections.modules.store', $section->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <!-- Hidden Fields -->
                                <input type="hidden" name="course_id" value="{{ $section->course_id }}">
                                <input type="hidden" name="section_id" value="{{ $section->id }}">

                                <!-- Basic Information -->
                                <div class="row gy-3">

                                    <!-- Module Type -->
                                    <div class="col-xl-12">
                                        <label class="form-label">نوع الدرس <span class="text-danger">*</span></label>
                                        <select name="module_type" id="module_type" class="form-select @error('module_type') is-invalid @enderror" required>
                                            <option value="">اختر نوع الدرس</option>
                                            @foreach($moduleTypes as $type)
                                                <option value="{{ $type }}" {{ old('module_type') == $type ? 'selected' : '' }}>
                                                    @if($type == 'lesson') درس نصي
                                                    @elseif($type == 'video') فيديو
                                                    @elseif($type == 'resource') ملف/مورد
                                                    @elseif($type == 'quiz') اختبار
                                                    @elseif($type == 'assignment') واجب
                                                    @elseif($type == 'programming_challenge') تحدي برمجي
                                                    @elseif($type == 'forum') منتدى نقاش
                                                    @elseif($type == 'live_session') جلسة مباشرة
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('module_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Select Existing Content -->
                                    <div class="col-xl-12" id="existing_content_section" style="display: none;">
                                        <div class="card border border-warning">
                                            <div class="card-header bg-warning-transparent">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                    اختر محتوى موجود (مطلوب)
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <!-- Lesson Selection -->
                                                <div id="lesson_select_field" style="display: none;">
                                                    <label class="form-label">اختر درس نصي <span class="text-danger">*</span></label>
                                                    <select name="modulable_id_lesson" id="modulable_id_lesson" class="form-select">
                                                        <option value="">اختر درس...</option>
                                                        @foreach($lessons as $lesson)
                                                            <option value="{{ $lesson->id }}" data-title="{{ $lesson->title }}">
                                                                {{ $lesson->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <small class="text-muted">أو <a href="{{ route('lessons.create') }}" target="_blank">أنشئ درس جديد</a></small>
                                                </div>

                                                <!-- Video Selection -->
                                                <div id="video_select_field" style="display: none;">
                                                    <div class="mb-3">
                                                        <div class="btn-group w-100" role="group">
                                                            <input type="radio" class="btn-check" name="video_source_type" id="video_source_existing" value="existing" checked>
                                                            <label class="btn btn-outline-primary" for="video_source_existing">
                                                                <i class="fas fa-list me-1"></i>اختر فيديو موجود
                                                            </label>
                                                            
                                                            <input type="radio" class="btn-check" name="video_source_type" id="video_source_new" value="new">
                                                            <label class="btn btn-outline-success" for="video_source_new">
                                                                <i class="fas fa-plus me-1"></i>إنشاء فيديو جديد من رابط
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <!-- Existing Video Selection -->
                                                    <div id="existing_video_section">
                                                        <label class="form-label">اختر فيديو <span class="text-danger">*</span></label>
                                                        <select name="modulable_id_video" id="modulable_id_video" class="form-select">
                                                            <option value="">اختر فيديو...</option>
                                                            @foreach($videos as $video)
                                                                <option value="{{ $video->id }}" data-title="{{ $video->title }}">
                                                                    {{ $video->title }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <!-- New Video Creation -->
                                                    <div id="new_video_section" style="display: none;">
                                                        <div class="row gy-3">
                                                            <div class="col-md-12">
                                                                <label class="form-label">رابط الفيديو <span class="text-danger">*</span></label>
                                                                <input type="url" name="new_video_url" id="new_video_url" class="form-control" 
                                                                       placeholder="https://www.youtube.com/watch?v=... أو https://vimeo.com/...">
                                                                <small class="text-muted">يدعم YouTube و Vimeo</small>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">عنوان الفيديو <span class="text-danger">*</span></label>
                                                                <input type="text" name="new_video_title" id="new_video_title" class="form-control" 
                                                                       placeholder="أدخل عنوان الفيديو">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">نوع الفيديو</label>
                                                                <select name="new_video_type" id="new_video_type" class="form-select">
                                                                    <option value="youtube">YouTube</option>
                                                                    <option value="vimeo">Vimeo</option>
                                                                    <option value="external">رابط خارجي</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <label class="form-label">وصف الفيديو (اختياري)</label>
                                                                <textarea name="new_video_description" id="new_video_description" class="form-control" rows="2" 
                                                                          placeholder="أدخل وصف الفيديو"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Resource Selection -->
                                                <div id="resource_select_field" style="display: none;">
                                                    <div class="mb-3">
                                                        <label class="form-label mb-3">اختر نوع المصدر <span class="text-danger">*</span></label>
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="resource-source-option" for="resource_source_file">
                                                                    <input class="form-check-input" type="radio" name="resource_source_type" 
                                                                           id="resource_source_file" value="file" 
                                                                           {{ old('resource_source_type', 'file') === 'file' ? 'checked' : '' }}
                                                                           onchange="toggleResourceSource()">
                                                                    <div class="card border h-100 resource-source-card">
                                                                        <div class="card-body text-center p-4">
                                                                            <i class="fas fa-cloud-upload-alt fs-24 text-primary mb-3"></i>
                                                                            <h6 class="mb-0 fw-semibold">رفع ملف</h6>
                                                                            <small class="text-muted d-block mt-2">رفع ملف من جهازك</small>
                                                                        </div>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="resource-source-option" for="resource_source_url">
                                                                    <input class="form-check-input" type="radio" name="resource_source_type" 
                                                                           id="resource_source_url" value="url"
                                                                           {{ old('resource_source_type') === 'url' ? 'checked' : '' }}
                                                                           onchange="toggleResourceSource()">
                                                                    <div class="card border h-100 resource-source-card">
                                                                        <div class="card-body text-center p-4">
                                                                            <i class="fas fa-link fs-24 text-success mb-3"></i>
                                                                            <h6 class="mb-0 fw-semibold">رابط خارجي</h6>
                                                                            <small class="text-muted d-block mt-2">إدخال رابط مباشر</small>
                                                                        </div>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- File Upload Section -->
                                                    <div id="resource_file_upload_section">
                                                        <div class="mb-3">
                                                            <label class="form-label">اختر الملف <span class="text-danger">*</span></label>
                                                            <input type="file" id="resource_file_input" name="resource_file"
                                                                   class="form-control @error('resource_file') is-invalid @enderror"
                                                                   accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.txt">
                                                            @error('resource_file')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                            <small class="text-muted">الحد الأقصى لحجم الملف: 50 MB</small>
                                                        </div>

                                                        <!-- File Preview -->
                                                        <div id="resource_file_preview" class="alert alert-info" style="display: none;">
                                                            <h6 class="mb-2"><i class="fas fa-file me-2"></i>معاينة الملف</h6>
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <strong>اسم الملف:</strong>
                                                                    <span id="resource_file_name" class="d-block text-muted"></span>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <strong>نوع الملف:</strong>
                                                                    <span id="resource_file_type" class="d-block text-muted"></span>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <strong>حجم الملف:</strong>
                                                                    <span id="resource_file_size" class="d-block text-muted"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Supported Formats -->
                                                        <div class="alert alert-light border">
                                                            <strong class="d-block mb-2"><i class="fas fa-check-circle me-2 text-success"></i>الصيغ المدعومة:</strong>
                                                            <span class="badge bg-secondary me-1">PDF</span>
                                                            <span class="badge bg-secondary me-1">DOC</span>
                                                            <span class="badge bg-secondary me-1">DOCX</span>
                                                            <span class="badge bg-secondary me-1">PPT</span>
                                                            <span class="badge bg-secondary me-1">PPTX</span>
                                                            <span class="badge bg-secondary me-1">XLS</span>
                                                            <span class="badge bg-secondary me-1">XLSX</span>
                                                            <span class="badge bg-secondary me-1">ZIP</span>
                                                            <span class="badge bg-secondary me-1">RAR</span>
                                                            <span class="badge bg-secondary me-1">TXT</span>
                                                        </div>
                                                    </div>

                                                    <!-- URL Input Section -->
                                                    <div id="resource_url_input_section" style="display: none;">
                                                        <div class="mb-3">
                                                            <label class="form-label">رابط المورد <span class="text-danger">*</span></label>
                                                            <input type="url" id="resource_url_input" name="resource_url" 
                                                                   class="form-control @error('resource_url') is-invalid @enderror"
                                                                   placeholder="https://example.com/resource.pdf"
                                                                   value="{{ old('resource_url') }}">
                                                            @error('resource_url')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                            <small class="text-muted">أدخل رابط مباشر للمورد (PDF، DOC، فيديو، إلخ)</small>
                                                        </div>
                                                        <div class="alert alert-info">
                                                            <i class="fas fa-info-circle me-2"></i>
                                                            <strong>ملاحظة:</strong> تأكد من أن الرابط مباشر ويمكن الوصول إليه من قبل الطلاب.
                                                        </div>
                                                    </div>

                                                    <!-- Resource Type -->
                                                    <div class="mb-3">
                                                        <label class="form-label">نوع المورد <span class="text-danger">*</span></label>
                                                        <select name="resource_type" id="resource_type" class="form-select @error('resource_type') is-invalid @enderror" required>
                                                            <option value="">اختر النوع</option>
                                                            <option value="pdf" {{ old('resource_type') == 'pdf' ? 'selected' : '' }}>PDF</option>
                                                            <option value="doc" {{ old('resource_type') == 'doc' ? 'selected' : '' }}>DOC/DOCX</option>
                                                            <option value="ppt" {{ old('resource_type') == 'ppt' ? 'selected' : '' }}>PPT/PPTX</option>
                                                            <option value="excel" {{ old('resource_type') == 'excel' ? 'selected' : '' }}>Excel</option>
                                                            <option value="image" {{ old('resource_type') == 'image' ? 'selected' : '' }}>صورة</option>
                                                            <option value="audio" {{ old('resource_type') == 'audio' ? 'selected' : '' }}>صوت</option>
                                                            <option value="archive" {{ old('resource_type') == 'archive' ? 'selected' : '' }}>أرشيف</option>
                                                            <option value="other" {{ old('resource_type') == 'other' ? 'selected' : '' }}>أخرى</option>
                                                        </select>
                                                        @error('resource_type')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <!-- Or Select Existing Resource -->
                                                    <div class="alert alert-light border">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span><i class="fas fa-info-circle me-2"></i>أو اختر مورد موجود</span>
                                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleExistingResource()">
                                                                <i class="fas fa-list me-1"></i>اختر مورد موجود
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <!-- Existing Resource Selection (Hidden by default) -->
                                                    <div id="existing_resource_section" style="display: none;">
                                                        <label class="form-label">اختر مورد موجود</label>
                                                        <select name="modulable_id_resource" id="modulable_id_resource" class="form-select">
                                                            <option value="">اختر مورد...</option>
                                                            @foreach($resources as $resource)
                                                                <option value="{{ $resource->id }}" data-title="{{ $resource->title }}">
                                                                    {{ $resource->title }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Title -->
                                    <div class="col-xl-12">
                                        <label class="form-label">عنوان الدرس <span class="text-danger">*</span></label>
                                        <input type="text" name="title" id="module_title" class="form-control @error('title') is-invalid @enderror"
                                               value="{{ old('title') }}" required placeholder="أدخل عنوان الدرس">
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">سيتم ملؤه تلقائياً عند اختيار محتوى موجود</small>
                                    </div>

                                    <!-- Description -->
                                    <div class="col-xl-12">
                                        <label class="form-label">الوصف</label>
                                        <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror"
                                                  placeholder="أدخل وصف الدرس">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Settings -->
                                    <div class="col-xl-12">
                                        <div class="card border">
                                            <div class="card-header">
                                                <h6 class="mb-0">إعدادات الدرس</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row gy-3">

                                                    <!-- Is Visible -->
                                                    <div class="col-xl-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="is_visible" id="is_visible"
                                                                   value="1" {{ old('is_visible', true) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="is_visible">
                                                                الدرس مرئي للطلاب
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <!-- Is Required -->
                                                    <div class="col-xl-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="is_required" id="is_required"
                                                                   value="1" {{ old('is_required') ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="is_required">
                                                                الدرس مطلوب للإكمال
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <!-- Is Graded -->
                                                    <div class="col-xl-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="is_graded" id="is_graded"
                                                                   value="1" {{ old('is_graded') ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="is_graded">
                                                                الدرس له درجة
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <!-- Max Score (appears if is_graded) -->
                                                    <div class="col-xl-6" id="max_score_field" style="display: none;">
                                                        <label class="form-label">الدرجة القصوى</label>
                                                        <input type="number" name="max_score" class="form-control"
                                                               value="{{ old('max_score', 100) }}" min="0" step="0.01">
                                                    </div>

                                                    <!-- Completion Type -->
                                                    <div class="col-xl-6">
                                                        <label class="form-label">نوع الإكمال</label>
                                                        <select name="completion_type" class="form-select">
                                                            @foreach($completionTypes as $type)
                                                                <option value="{{ $type }}" {{ old('completion_type', 'auto') == $type ? 'selected' : '' }}>
                                                                    @if($type == 'auto') تلقائي
                                                                    @elseif($type == 'manual') يدوي
                                                                    @elseif($type == 'score_based') بناءً على الدرجة
                                                                    @endif
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <!-- Estimated Duration -->
                                                    <div class="col-xl-6">
                                                        <label class="form-label">المدة المقدرة (بالدقائق)</label>
                                                        <input type="number" name="estimated_duration" class="form-control"
                                                               value="{{ old('estimated_duration') }}" min="0" placeholder="مثال: 30">
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Availability Dates -->
                                    <div class="col-xl-12">
                                        <div class="card border">
                                            <div class="card-header">
                                                <h6 class="mb-0">فترة الإتاحة</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row gy-3">
                                                    <!-- Available From -->
                                                    <div class="col-xl-6">
                                                        <label class="form-label">متاح من</label>
                                                        <input type="datetime-local" name="available_from" class="form-control"
                                                               value="{{ old('available_from') }}">
                                                        <small class="text-muted">اتركه فارغاً للإتاحة الفورية</small>
                                                    </div>

                                                    <!-- Available Until -->
                                                    <div class="col-xl-6">
                                                        <label class="form-label">متاح حتى</label>
                                                        <input type="datetime-local" name="available_until" class="form-control"
                                                               value="{{ old('available_until') }}">
                                                        <small class="text-muted">اتركه فارغاً لعدم التحديد بوقت</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="col-xl-12">
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>حفظ الدرس
                                            </button>
                                            <a href="{{ route('courses.show', $section->course_id) }}" class="btn btn-light">
                                                <i class="fas fa-times me-2"></i>إلغاء
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        // Toggle between file and URL for resource
        function toggleResourceSource() {
            const sourceFile = document.getElementById('resource_source_file');
            const sourceUrl = document.getElementById('resource_source_url');
            const fileUploadSection = document.getElementById('resource_file_upload_section');
            const urlInputSection = document.getElementById('resource_url_input_section');
            const fileInput = document.getElementById('resource_file_input');
            const urlInput = document.getElementById('resource_url_input');
            
            // Check if elements exist before manipulating them
            if (!fileUploadSection || !urlInputSection) {
                return; // Elements not available yet
            }
            
            if (sourceFile && sourceFile.checked) {
                if (fileUploadSection) fileUploadSection.style.display = 'block';
                if (urlInputSection) urlInputSection.style.display = 'none';
                if (fileInput) fileInput.setAttribute('required', 'required');
                if (urlInput) urlInput.removeAttribute('required');
            } else if (sourceUrl && sourceUrl.checked) {
                if (fileUploadSection) fileUploadSection.style.display = 'none';
                if (urlInputSection) urlInputSection.style.display = 'block';
                if (fileInput) fileInput.removeAttribute('required');
                if (urlInput) urlInput.setAttribute('required', 'required');
            } else {
                // Default to file upload if nothing is checked
                if (sourceFile) {
                    sourceFile.checked = true;
                    if (fileUploadSection) fileUploadSection.style.display = 'block';
                    if (urlInputSection) urlInputSection.style.display = 'none';
                    if (fileInput) fileInput.setAttribute('required', 'required');
                    if (urlInput) urlInput.removeAttribute('required');
                }
            }
        }

        // Toggle existing resource selection
        function toggleExistingResource() {
            const existingSection = document.getElementById('existing_resource_section');
            const resourceSourceFile = document.getElementById('resource_source_file');
            const resourceSourceUrl = document.getElementById('resource_source_url');
            const fileUploadSection = document.getElementById('resource_file_upload_section');
            const urlInputSection = document.getElementById('resource_url_input_section');
            const resourceFileInput = document.getElementById('resource_file_input');
            const resourceUrlInput = document.getElementById('resource_url_input');
            const resourceTypeSelect = document.getElementById('resource_type');
            const existingResourceSelect = document.getElementById('modulable_id_resource');
            
            if (existingSection) {
                const isVisible = existingSection.style.display !== 'none';
                
                if (isVisible) {
                    // Hide existing resource section, show new resource options
                    existingSection.style.display = 'none';
                    if (existingResourceSelect) {
                        existingResourceSelect.value = '';
                        existingResourceSelect.removeAttribute('required');
                    }
                    
                    // Show and require resource source options
                    if (fileUploadSection) fileUploadSection.style.display = 'block';
                    if (resourceSourceFile) resourceSourceFile.checked = true;
                    toggleResourceSource();
                    if (resourceTypeSelect) resourceTypeSelect.setAttribute('required', 'required');
                } else {
                    // Show existing resource section, hide new resource options
                    existingSection.style.display = 'block';
                    if (existingResourceSelect) existingResourceSelect.setAttribute('required', 'required');
                    
                    // Hide and remove required from resource source options
                    if (fileUploadSection) fileUploadSection.style.display = 'none';
                    if (urlInputSection) urlInputSection.style.display = 'none';
                    if (resourceFileInput) resourceFileInput.removeAttribute('required');
                    if (resourceUrlInput) resourceUrlInput.removeAttribute('required');
                    if (resourceTypeSelect) resourceTypeSelect.removeAttribute('required');
                    if (resourceSourceFile) resourceSourceFile.checked = false;
                    if (resourceSourceUrl) resourceSourceUrl.checked = false;
                }
            }
        }

        // Format File Size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        document.addEventListener('DOMContentLoaded', function() {
            const moduleTypeSelect = document.getElementById('module_type');
            const existingContentSection = document.getElementById('existing_content_section');
            const lessonSelectField = document.getElementById('lesson_select_field');
            const videoSelectField = document.getElementById('video_select_field');
            const resourceSelectField = document.getElementById('resource_select_field');
            const moduleTitleInput = document.getElementById('module_title');
            const isGradedCheckbox = document.getElementById('is_graded');
            const maxScoreField = document.getElementById('max_score_field');

            // Show/hide content selection based on module type
            function handleModuleTypeChange() {
                const selectedType = moduleTypeSelect.value;

                // Hide all selection fields first
                if (existingContentSection) existingContentSection.style.display = 'none';
                if (lessonSelectField) lessonSelectField.style.display = 'none';
                if (videoSelectField) videoSelectField.style.display = 'none';
                if (resourceSelectField) resourceSelectField.style.display = 'none';

                // Show relevant field based on selected type
                if (selectedType === 'lesson') {
                    if (existingContentSection) existingContentSection.style.display = 'block';
                    if (lessonSelectField) lessonSelectField.style.display = 'block';
                } else if (selectedType === 'video') {
                    if (existingContentSection) existingContentSection.style.display = 'block';
                    if (videoSelectField) videoSelectField.style.display = 'block';
                } else if (selectedType === 'resource') {
                    // Show the existing content section
                    if (existingContentSection) {
                        existingContentSection.style.display = 'block';
                        existingContentSection.style.visibility = 'visible';
                    }
                    // Show the resource select field
                    if (resourceSelectField) {
                        resourceSelectField.style.display = 'block';
                        resourceSelectField.style.visibility = 'visible';
                    }
                    // Initialize resource source toggle when resource type is selected
                    setTimeout(() => {
                        toggleResourceSource();
                    }, 300);
                }
            }

            // Add event listener
            moduleTypeSelect.addEventListener('change', handleModuleTypeChange);
            
            // Also trigger on page load if resource is already selected
            if (moduleTypeSelect.value === 'resource') {
                handleModuleTypeChange();
            }


            // Toggle between existing video and new video creation
            const videoSourceExisting = document.getElementById('video_source_existing');
            const videoSourceNew = document.getElementById('video_source_new');
            const existingVideoSection = document.getElementById('existing_video_section');
            const newVideoSection = document.getElementById('new_video_section');
            const modulableIdVideo = document.getElementById('modulable_id_video');

            if (videoSourceExisting && videoSourceNew) {
                videoSourceExisting.addEventListener('change', function() {
                    if (this.checked) {
                        existingVideoSection.style.display = 'block';
                        newVideoSection.style.display = 'none';
                        modulableIdVideo.required = true;
                        document.getElementById('new_video_url').required = false;
                        document.getElementById('new_video_title').required = false;
                    }
                });

                videoSourceNew.addEventListener('change', function() {
                    if (this.checked) {
                        existingVideoSection.style.display = 'none';
                        newVideoSection.style.display = 'block';
                        modulableIdVideo.required = false;
                        document.getElementById('new_video_url').required = true;
                        document.getElementById('new_video_title').required = true;
                    }
                });
            }

            // Auto-detect video type from URL
            const newVideoUrl = document.getElementById('new_video_url');
            const newVideoType = document.getElementById('new_video_type');
            if (newVideoUrl && newVideoType) {
                newVideoUrl.addEventListener('input', function() {
                    const url = this.value.toLowerCase();
                    if (url.includes('youtube.com') || url.includes('youtu.be')) {
                        newVideoType.value = 'youtube';
                    } else if (url.includes('vimeo.com')) {
                        newVideoType.value = 'vimeo';
                    } else if (url) {
                        newVideoType.value = 'external';
                    }
                });
            }

            // Auto-fill title when content is selected
            function setupAutoFill(selectId, inputId) {
                const select = document.getElementById(selectId);
                if (select) {
                    select.addEventListener('change', function() {
                        const selectedOption = this.options[this.selectedIndex];
                        const title = selectedOption.getAttribute('data-title');
                        if (title && !moduleTitleInput.value) {
                            moduleTitleInput.value = title;
                        }
                    });
                }
            }

            setupAutoFill('modulable_id_lesson', 'module_title');
            setupAutoFill('modulable_id_video', 'module_title');
            setupAutoFill('modulable_id_resource', 'module_title');
            
            // Handle existing resource selection change - hide new resource options
            const modulableIdResource = document.getElementById('modulable_id_resource');
            if (modulableIdResource) {
                modulableIdResource.addEventListener('change', function() {
                    if (this.value) {
                        // Hide new resource creation options when existing resource is selected
                        const fileUploadSection = document.getElementById('resource_file_upload_section');
                        const urlInputSection = document.getElementById('resource_url_input_section');
                        const resourceSourceFile = document.getElementById('resource_source_file');
                        const resourceSourceUrl = document.getElementById('resource_source_url');
                        const resourceTypeSelect = document.getElementById('resource_type');
                        const resourceFileInput = document.getElementById('resource_file_input');
                        const resourceUrlInput = document.getElementById('resource_url_input');
                        
                        if (fileUploadSection) fileUploadSection.style.display = 'none';
                        if (urlInputSection) urlInputSection.style.display = 'none';
                        if (resourceFileInput) resourceFileInput.removeAttribute('required');
                        if (resourceUrlInput) resourceUrlInput.removeAttribute('required');
                        if (resourceTypeSelect) resourceTypeSelect.removeAttribute('required');
                        if (resourceSourceFile) resourceSourceFile.checked = false;
                        if (resourceSourceUrl) resourceSourceUrl.checked = false;
                    }
                });
            }
            
            // Handle existing resource selection change
            const modulableIdResource = document.getElementById('modulable_id_resource');
            if (modulableIdResource) {
                modulableIdResource.addEventListener('change', function() {
                    if (this.value) {
                        // Hide new resource creation options when existing resource is selected
                        const fileUploadSection = document.getElementById('resource_file_upload_section');
                        const urlInputSection = document.getElementById('resource_url_input_section');
                        const resourceSourceFile = document.getElementById('resource_source_file');
                        const resourceSourceUrl = document.getElementById('resource_source_url');
                        const resourceTypeSelect = document.getElementById('resource_type');
                        
                        if (fileUploadSection) fileUploadSection.style.display = 'none';
                        if (urlInputSection) urlInputSection.style.display = 'none';
                        if (resourceFileInput) resourceFileInput.removeAttribute('required');
                        if (resourceUrlInput) resourceUrlInput.removeAttribute('required');
                        if (resourceTypeSelect) resourceTypeSelect.removeAttribute('required');
                        if (resourceSourceFile) resourceSourceFile.checked = false;
                        if (resourceSourceUrl) resourceSourceUrl.checked = false;
                    }
                });
            }

            // Show/hide max score field
            isGradedCheckbox.addEventListener('change', function() {
                maxScoreField.style.display = this.checked ? 'block' : 'none';
            });

            // Form validation before submit
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const selectedType = moduleTypeSelect.value;

                // Only validate modulable selection for types that require it (lesson, video, resource)
                // Other types (quiz, assignment, etc.) don't need modulable
                const typesRequiringModulable = ['lesson', 'video', 'resource'];

                if (typesRequiringModulable.includes(selectedType)) {
                    if (selectedType === 'lesson') {
                        const lessonSelect = document.getElementById('modulable_id_lesson');
                        if (!lessonSelect.value) {
                            e.preventDefault();
                            alert('يجب اختيار درس نصي من القائمة');
                            lessonSelect.focus();
                            return false;
                        }
                    } else if (selectedType === 'video') {
                        const videoSourceType = document.querySelector('input[name="video_source_type"]:checked');
                        if (videoSourceType && videoSourceType.value === 'existing') {
                            const videoSelect = document.getElementById('modulable_id_video');
                            if (!videoSelect.value) {
                                e.preventDefault();
                                alert('يجب اختيار فيديو من القائمة');
                                videoSelect.focus();
                                return false;
                            }
                        } else if (videoSourceType && videoSourceType.value === 'new') {
                            const newVideoUrl = document.getElementById('new_video_url');
                            const newVideoTitle = document.getElementById('new_video_title');
                            if (!newVideoUrl.value || !newVideoTitle.value) {
                                e.preventDefault();
                                alert('يجب إدخال رابط الفيديو وعنوان الفيديو');
                                if (!newVideoUrl.value) {
                                    newVideoUrl.focus();
                                } else {
                                    newVideoTitle.focus();
                                }
                                return false;
                            }
                        }
                    } else if (selectedType === 'resource') {
                        const existingResourceSection = document.getElementById('existing_resource_section');
                        const existingResourceSelect = document.getElementById('modulable_id_resource');
                        
                        // Check if using existing resource
                        if (existingResourceSection && existingResourceSection.style.display !== 'none' && existingResourceSelect && existingResourceSelect.value) {
                            // Using existing resource, validation passed - no need to check file/url
                        } else {
                            // Creating new resource - validate source type
                            const resourceSourceType = document.querySelector('input[name="resource_source_type"]:checked');
                            
                            if (!resourceSourceType) {
                                e.preventDefault();
                                alert('يجب اختيار نوع المصدر (رفع ملف أو رابط خارجي) أو اختيار مورد موجود');
                                return false;
                            }
                            
                            if (resourceSourceType.value === 'file') {
                                const fileInput = document.getElementById('resource_file_input');
                                if (!fileInput || !fileInput.files || !fileInput.files[0]) {
                                    e.preventDefault();
                                    alert('يجب اختيار ملف للرفع');
                                    if (fileInput) fileInput.focus();
                                    return false;
                                }
                            } else if (resourceSourceType.value === 'url') {
                                const urlInput = document.getElementById('resource_url_input');
                                if (!urlInput || !urlInput.value) {
                                    e.preventDefault();
                                    alert('يجب إدخال رابط المورد');
                                    if (urlInput) urlInput.focus();
                                    return false;
                                }
                            }
                            
                            // Validate resource type only if creating new resource
                            const resourceType = document.getElementById('resource_type');
                            if (!resourceType || !resourceType.value) {
                                e.preventDefault();
                                alert('يجب اختيار نوع المورد');
                                if (resourceType) resourceType.focus();
                                return false;
                            }
                        }
                    }
                }
            });

            // Resource file input change
            const resourceFileInput = document.getElementById('resource_file_input');
            const resourceFilePreview = document.getElementById('resource_file_preview');
            
            if (resourceFileInput && resourceFilePreview) {
                resourceFileInput.addEventListener('change', function() {
                    const file = this.files[0];
                    if (!file) {
                        resourceFilePreview.style.display = 'none';
                        return;
                    }

                    // Validate file size (50MB)
                    if (file.size > 50 * 1024 * 1024) {
                        alert('حجم الملف كبير جداً. الحد الأقصى 50MB');
                        this.value = '';
                        resourceFilePreview.style.display = 'none';
                        return;
                    }

                    // Display file info
                    const fileName = document.getElementById('resource_file_name');
                    const fileType = document.getElementById('resource_file_type');
                    const fileSize = document.getElementById('resource_file_size');
                    
                    if (fileName) fileName.textContent = file.name;
                    if (fileType) fileType.textContent = file.type || 'غير معروف';
                    if (fileSize) fileSize.textContent = formatFileSize(file.size);
                    
                    resourceFilePreview.style.display = 'block';
                });
            }
        });
    </script>
    <style>
        .resource-source-option {
            position: relative;
            cursor: pointer;
            display: block;
        }
        
        .resource-source-option .form-check-input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .resource-source-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .resource-source-card:hover {
            border-color: #0d6efd !important;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1);
        }
        
        .resource-source-option input:checked ~ .resource-source-card {
            border-color: #0d6efd !important;
            background-color: rgba(13, 110, 253, 0.05);
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        }
        
        .resource-source-option input:checked ~ .resource-source-card .text-primary,
        .resource-source-option input:checked ~ .resource-source-card .text-success {
            color: #0d6efd !important;
        }
    </style>
@stop
