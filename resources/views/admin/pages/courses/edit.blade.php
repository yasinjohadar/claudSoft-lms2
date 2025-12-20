@extends('admin.layouts.master')

@section('page-title')
    تعديل الكورس: {{ $course->title }}
@stop

@push('head')
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
@endpush

@section('css')
<style data-version="2.0">
    /* Enhanced Card Design v2.0 - {{ now()->timestamp }} */
    .form-section {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border: 2px solid #e9ecef;
        position: relative;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .form-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        opacity: 0;
        transition: opacity 0.3s;
    }

    .form-section:hover {
        border-color: #667eea;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.15);
        transform: translateY(-3px);
    }

    .form-section:hover::before {
        opacity: 1;
    }

    .form-section-header {
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 1rem;
        margin-bottom: 1.5rem;
        position: relative;
    }

    .form-section-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .form-section-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        transition: all 0.3s;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .form-section:hover .form-section-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .thumbnail-preview {
        width: 100%;
        max-width: 500px;
        height: 280px;
        border-radius: 16px;
        border: 3px dashed #ddd;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.02) 0%, rgba(118, 75, 162, 0.02) 100%);
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    .thumbnail-preview::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        opacity: 0;
        transition: opacity 0.3s;
    }

    .thumbnail-preview:hover {
        border-color: #667eea;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.08) 0%, rgba(118, 75, 162, 0.08) 100%);
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
        transform: translateY(-3px);
    }

    .thumbnail-preview:hover::before {
        opacity: 1;
    }

    .thumbnail-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }

    .thumbnail-preview:hover img {
        transform: scale(1.05);
    }

    .thumbnail-placeholder {
        text-align: center;
        color: #6c757d;
        transition: all 0.3s;
    }

    .thumbnail-preview:hover .thumbnail-placeholder {
        color: #667eea;
        transform: scale(1.05);
    }


    .required-asterisk {
        color: #dc3545;
        margin-right: 3px;
        font-weight: 700;
    }

    .sticky-actions {
        position: sticky;
        bottom: 0;
        background: white;
        padding: 1.5rem 2rem;
        box-shadow: 0 -8px 30px rgba(0, 0, 0, 0.1);
        border-radius: 16px 16px 0 0;
        margin: 2rem -2rem -2rem;
        border-top: 3px solid #667eea;
        z-index: 100;
    }

    .form-control, .form-select {
        border-radius: 10px;
        border: 2px solid #e9ecef;
        padding: 0.625rem 1rem;
        transition: all 0.3s;
    }

    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
    }

    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
    }

    .form-check-label {
        font-weight: 600;
        color: #495057;
    }

    .page-header-breadcrumb {
        background: white;
        padding: 1.5rem;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border: 2px solid #e9ecef;
    }

    .btn-outline-primary {
        border-radius: 10px;
        border: 2px solid #667eea;
        color: #667eea;
        font-weight: 600;
        padding: 0.5rem 1.25rem;
        transition: all 0.3s;
    }

    .btn-outline-primary:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: transparent;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 10px;
        padding: 0.625rem 2rem;
        font-weight: 600;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }

    .btn-light {
        border-radius: 10px;
        padding: 0.625rem 1.5rem;
        font-weight: 600;
        border: 2px solid #e9ecef;
        transition: all 0.3s;
    }

    .btn-light:hover {
        border-color: #667eea;
        color: #667eea;
        transform: translateY(-2px);
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .form-section {
        animation: fadeInUp 0.5s ease-out;
    }

    .form-section:nth-child(1) { animation-delay: 0.1s; }
    .form-section:nth-child(2) { animation-delay: 0.2s; }
    .form-section:nth-child(3) { animation-delay: 0.3s; }
    .form-section:nth-child(4) { animation-delay: 0.4s; }
    .form-section:nth-child(5) { animation-delay: 0.5s; }
    .form-section:nth-child(6) { animation-delay: 0.6s; }
    .form-section:nth-child(7) { animation-delay: 0.7s; }
</style>
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تعديل الكورس</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                            <li class="breadcrumb-item active" aria-current="page">تعديل</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('courses.show', $course->id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-eye me-2"></i>معاينة
                    </a>
                </div>
            </div>

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

            <form action="{{ route('courses.update', $course->id) }}" method="POST" enctype="multipart/form-data" id="courseForm">
                @csrf
                @method('PUT')

                <!-- Basic Information -->
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-title">
                            <div class="form-section-icon bg-primary-transparent text-primary">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            المعلومات الأساسية
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">
                                <span class="required-asterisk">*</span>
                                عنوان الكورس
                            </label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title', $course->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                <span class="required-asterisk">*</span>
                                الكود (Slug)
                            </label>
                            <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                                   value="{{ old('slug', $course->slug) }}" required>
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>

                <!-- Classification & Level -->
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-title">
                            <div class="form-section-icon bg-success-transparent text-success">
                                <i class="fas fa-tags"></i>
                            </div>
                            التصنيف والمستوى
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                <span class="required-asterisk">*</span>
                                التصنيف
                            </label>
                            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">اختر التصنيف</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id', $course->course_category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">المستوى</label>
                            <select name="level" class="form-select @error('level') is-invalid @enderror">
                                <option value="">اختر المستوى</option>
                                <option value="beginner" {{ old('level', $course->level) == 'beginner' ? 'selected' : '' }}>مبتدئ</option>
                                <option value="intermediate" {{ old('level', $course->level) == 'intermediate' ? 'selected' : '' }}>متوسط</option>
                                <option value="advanced" {{ old('level', $course->level) == 'advanced' ? 'selected' : '' }}>متقدم</option>
                            </select>
                            @error('level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Instructor & Course Details -->
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-title">
                            <div class="form-section-icon bg-warning-transparent text-warning">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            المدرب وتفاصيل الكورس
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">
                                <i class="fas fa-chalkboard-teacher me-1 text-warning"></i>
                                المدرب
                            </label>
                            <select name="instructor_id" class="form-select @error('instructor_id') is-invalid @enderror">
                                <option value="">اختر المدرب</option>
                                @foreach($instructors as $instructor)
                                    <option value="{{ $instructor->id }}"
                                        {{ old('instructor_id', $course->instructor_id) == $instructor->id ? 'selected' : '' }}>
                                        {{ $instructor->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('instructor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                <i class="fas fa-clock me-1 text-info"></i>
                                مدة الكورس (بالساعات)
                            </label>
                            <input type="number" name="duration_hours" class="form-control @error('duration_hours') is-invalid @enderror"
                                   min="0" value="{{ old('duration_hours', $course->duration_hours) }}" placeholder="0">
                            @error('duration_hours')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                <i class="fas fa-users me-1 text-success"></i>
                                الحد الأقصى للطلاب
                            </label>
                            <input type="number" name="max_students" class="form-control @error('max_students') is-invalid @enderror"
                                   min="0" value="{{ old('max_students', $course->max_students) }}" placeholder="غير محدود">
                            @error('max_students')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Thumbnail -->
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-title">
                            <div class="form-section-icon bg-info-transparent text-info">
                                <i class="fas fa-image"></i>
                            </div>
                            الصورة المصغرة
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">تغيير صورة الكورس</label>
                            <input type="file" name="image" class="form-control @error('image') is-invalid @enderror"
                                   id="thumbnailInput" accept="image/*">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <div class="thumbnail-preview mt-3" id="thumbnailPreview" onclick="document.getElementById('thumbnailInput').click()">
                                @if($course->image)
                                    @php
                                        $imagePath = $course->image;
                                        // Check if image path starts with 'http' (external URL)
                                        if (strpos($imagePath, 'http') === 0) {
                                            $imageUrl = $imagePath;
                                        } 
                                        // Use Storage::url() which works correctly with/without public in URL
                                        else {
                                            $imageUrl = \Storage::disk('public')->url($imagePath);
                                        }
                                    @endphp
                                    <img src="{{ $imageUrl }}" alt="{{ $course->title }}" id="currentThumbnail" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" style="width: 100%; height: 100%; object-fit: cover;">
                                    <div class="thumbnail-placeholder" style="display: none;">
                                        <i class="fas fa-cloud-upload-alt fa-3x mb-3 text-muted opacity-50"></i>
                                        <p class="mb-0">انقر لرفع صورة</p>
                                    </div>
                                @else
                                    <div class="thumbnail-placeholder">
                                        <i class="fas fa-cloud-upload-alt fa-3x mb-3 text-muted opacity-50"></i>
                                        <p class="mb-0">انقر لرفع صورة</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Enrollment Settings -->
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-title">
                            <div class="form-section-icon bg-info-transparent text-info">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            إعدادات التسجيل
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">
                                <i class="fas fa-door-open me-1 text-primary"></i>
                                نوع التسجيل <span class="text-danger">*</span>
                            </label>
                            <select name="enrollment_type" class="form-select @error('enrollment_type') is-invalid @enderror" required>
                                <option value="">اختر نوع التسجيل</option>
                                <option value="open" {{ old('enrollment_type', $course->enrollment_type) == 'open' ? 'selected' : '' }}>
                                    <i class="fas fa-unlock me-2"></i>مفتوح - التسجيل مباشر
                                </option>
                                <option value="by_approval" {{ old('enrollment_type', $course->enrollment_type) == 'by_approval' ? 'selected' : '' }}>
                                    <i class="fas fa-user-check me-2"></i>يتطلب موافقة - ينتظر موافقة الأدمن
                                </option>
                                <option value="invite_only" {{ old('enrollment_type', $course->enrollment_type) == 'invite_only' ? 'selected' : '' }}>
                                    <i class="fas fa-lock me-2"></i>بالدعوة فقط - لا يمكن التسجيل
                                </option>
                            </select>
                            @error('enrollment_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="alert alert-info mt-2 mb-0" style="font-size: 0.9rem;">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>مفتوح:</strong> الطالب ينضم للكورس مباشرة بدون انتظار |
                                <strong>يتطلب موافقة:</strong> الطالب يرسل طلب ويبقى في حالة pending حتى توافق عليه |
                                <strong>بالدعوة فقط:</strong> لا يمكن للطالب التسجيل نهائياً
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Availability & Visibility -->
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-title">
                            <div class="form-section-icon bg-secondary-transparent text-secondary">
                                <i class="fas fa-cog"></i>
                            </div>
                            التوفر والظهور
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-calendar-alt me-1 text-success"></i>
                                متاح من
                            </label>
                            <input type="datetime-local" name="available_from" class="form-control"
                                   value="{{ old('available_from', $course->available_from ? $course->available_from->format('Y-m-d\TH:i') : '') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-calendar-times me-1 text-danger"></i>
                                متاح حتى
                            </label>
                            <input type="datetime-local" name="available_until" class="form-control"
                                   value="{{ old('available_until', $course->available_until ? $course->available_until->format('Y-m-d\TH:i') : '') }}">
                        </div>

                        <div class="col-md-6">
                            <div class="card border-success" style="padding: 1rem;">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_published" id="isPublished"
                                           {{ old('is_published', $course->is_published) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="isPublished">
                                        <i class="fas fa-eye me-1 text-success"></i>
                                        منشور (ظاهر للطلاب)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-warning" style="padding: 1rem;">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured"
                                           {{ old('is_featured', $course->is_featured) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="isFeatured">
                                        <i class="fas fa-star me-1 text-warning"></i>
                                        كورس مميز (يظهر أولاً)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sticky Action Buttons -->
                <div class="sticky-actions">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('courses.index') }}" class="btn btn-light">
                            <i class="fas fa-times me-2"></i>إلغاء
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>حفظ التعديلات
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </div>
@stop

@section('script')
<script>
    // Auto-generate slug from title
    document.querySelector('input[name="title"]').addEventListener('input', function(e) {
        const title = e.target.value;
        const slug = title.toLowerCase()
            .replace(/[^\u0600-\u06FFa-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
        document.querySelector('input[name="slug"]').value = slug;
    });

    // Thumbnail Preview
    const thumbnailInput = document.getElementById('thumbnailInput');
    const thumbnailPreview = document.getElementById('thumbnailPreview');
    
    if (thumbnailInput) {
        thumbnailInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Remove placeholder if exists
                    const placeholder = thumbnailPreview.querySelector('.thumbnail-placeholder');
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                    
                    // Update or create image
                    let img = thumbnailPreview.querySelector('img');
                    if (img) {
                        img.src = e.target.result;
                        img.style.display = 'block';
                    } else {
                        thumbnailPreview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">`;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

</script>
@stop
