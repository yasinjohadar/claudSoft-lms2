@extends('admin.layouts.master')

@section('page-title')
    تعديل مورد
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Error Messages -->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-exclamation-triangle me-2"></i>هناك أخطاء في النموذج:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-exclamation-triangle me-2"></i>خطأ:</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-check-circle me-2"></i>نجح:</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تعديل مورد: {{ $resource->title }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('resources.index') }}">الموارد</a></li>
                            <li class="breadcrumb-item active">تعديل</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <form action="{{ route('resources.update', $resource->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

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
                                <label class="form-label">عنوان المورد <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                       placeholder="مثال: كتاب مقدمة في البرمجة"
                                       value="{{ old('title', $resource->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">نوع المورد <span class="text-danger">*</span></label>
                                <select name="resource_type" class="form-select @error('resource_type') is-invalid @enderror" required>
                                    <option value="">اختر النوع</option>
                                    @foreach($resourceTypes as $type)
                                        <option value="{{ $type }}" {{ old('resource_type', $resource->resource_type) == $type ? 'selected' : '' }}>
                                            @if($type == 'pdf') PDF
                                            @elseif($type == 'doc') DOC/DOCX
                                            @elseif($type == 'ppt') PPT/PPTX
                                            @elseif($type == 'excel') Excel
                                            @elseif($type == 'image') صورة
                                            @elseif($type == 'audio') صوت
                                            @elseif($type == 'archive') أرشيف
                                            @else أخرى
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('resource_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">الكورس</label>
                                <select name="course_id" class="form-select @error('course_id') is-invalid @enderror">
                                    <option value="">اختر الكورس</option>
                                    @foreach($courses ?? [] as $course)
                                        <option value="{{ $course->id }}" {{ old('course_id', $resource->course_id ?? '') == $course->id ? 'selected' : '' }}>
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('course_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">الوصف</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                          rows="3"
                                          placeholder="اكتب وصفاً للمورد التعليمي...">{{ old('description', $resource->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resource Source Selection -->
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-link me-2"></i>مصدر المورد <span class="text-danger">*</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label mb-3">اختر نوع المصدر</label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="resource-source-option" for="sourceFile">
                                        <input class="form-check-input" type="radio" name="resource_source" 
                                               id="sourceFile" value="file" 
                                               {{ old('resource_source', $resource->resource_source ?? 'file') === 'file' ? 'checked' : '' }}
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
                                    <label class="resource-source-option" for="sourceUrl">
                                        <input class="form-check-input" type="radio" name="resource_source" 
                                               id="sourceUrl" value="url"
                                               {{ old('resource_source', $resource->resource_source ?? 'file') === 'url' ? 'checked' : '' }}
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
                            @error('resource_source')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- File Upload Section -->
                        <div id="fileUploadSection">
                            @if($resource->resource_source === 'file' && $resource->file_path)
                                <div class="alert alert-info mb-3">
                                    <strong>الملف الحالي:</strong> 
                                    <a href="{{ asset('storage/' . $resource->file_path) }}" target="_blank">
                                        {{ $resource->file_name }}
                                    </a>
                                    <span class="badge bg-info ms-2">{{ $resource->getFormattedFileSize() }}</span>
                                </div>
                            @endif
                            <div class="mb-3">
                                <label class="form-label">اختر ملف جديد (اختياري)</label>
                                <input type="file" id="fileInput" name="file"
                                       class="form-control @error('file') is-invalid @enderror"
                                       accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.txt">
                                @error('file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">الحد الأقصى لحجم الملف: 50 MB. إذا لم تختر ملفاً جديداً، سيتم الاحتفاظ بالملف الحالي.</small>
                            </div>

                            <!-- File Preview -->
                            <div id="filePreview" class="alert alert-info" style="display: none;">
                                <h6 class="mb-2"><i class="fas fa-file me-2"></i>معاينة الملف الجديد</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>اسم الملف:</strong>
                                        <span id="fileName" class="d-block text-muted"></span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>نوع الملف:</strong>
                                        <span id="fileType" class="d-block text-muted"></span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>حجم الملف:</strong>
                                        <span id="fileSize" class="d-block text-muted"></span>
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
                        <div id="urlInputSection" style="display: none;">
                            @if($resource->resource_source === 'url' && $resource->resource_url)
                                <div class="alert alert-info mb-3">
                                    <strong>الرابط الحالي:</strong> 
                                    <a href="{{ $resource->resource_url }}" target="_blank">
                                        {{ Str::limit($resource->resource_url, 50) }}
                                    </a>
                                </div>
                            @endif
                            <div class="mb-3">
                                <label class="form-label">رابط المورد <span class="text-danger">*</span></label>
                                <input type="url" id="resourceUrlInput" name="resource_url" 
                                       class="form-control @error('resource_url') is-invalid @enderror"
                                       placeholder="https://example.com/resource.pdf"
                                       value="{{ old('resource_url', $resource->resource_url) }}">
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
                    </div>
                </div>

                <!-- Settings -->
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-cog me-2"></i>الإعدادات
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_published" id="isPublished"
                                           {{ old('is_published', $resource->is_published) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isPublished">
                                        منشور
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_visible" id="isVisible"
                                           {{ old('is_visible', $resource->is_visible) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isVisible">
                                        مرئي للطلاب
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="allow_download" id="allowDownload"
                                           {{ old('allow_download', $resource->allow_download) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allowDownload">
                                        السماح بالتحميل
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('resources.index') }}" class="btn btn-light">
                                <i class="fas fa-times me-2"></i>إلغاء
                            </a>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>حفظ التغييرات
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </form>

        </div>
    </div>
@stop

@section('script')
<script>
    const fileInput = document.getElementById('fileInput');
    const filePreview = document.getElementById('filePreview');
    const fileUploadSection = document.getElementById('fileUploadSection');
    const urlInputSection = document.getElementById('urlInputSection');
    const resourceUrlInput = document.getElementById('resourceUrlInput');

    // Toggle between file and URL
    function toggleResourceSource() {
        const sourceFile = document.getElementById('sourceFile');
        const sourceUrl = document.getElementById('sourceUrl');
        
        if (sourceFile.checked) {
            fileUploadSection.style.display = 'block';
            urlInputSection.style.display = 'none';
            fileInput.removeAttribute('required');
            resourceUrlInput.removeAttribute('required');
        } else if (sourceUrl.checked) {
            fileUploadSection.style.display = 'none';
            urlInputSection.style.display = 'block';
            fileInput.removeAttribute('required');
            resourceUrlInput.setAttribute('required', 'required');
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleResourceSource();
    });

    // File Input Change
    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) {
            filePreview.style.display = 'none';
            return;
        }

        // Validate file size (50MB)
        if (file.size > 50 * 1024 * 1024) {
            alert('حجم الملف كبير جداً. الحد الأقصى 50MB');
            this.value = '';
            filePreview.style.display = 'none';
            return;
        }

        // Display file info
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileType').textContent = file.type || 'غير معروف';
        document.getElementById('fileSize').textContent = formatFileSize(file.size);
        filePreview.style.display = 'block';
    });

    // Format File Size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
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

