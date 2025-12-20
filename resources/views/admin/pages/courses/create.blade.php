@extends('admin.layouts.master')

@section('page-title')
    إضافة كورس جديد
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إضافة كورس جديد</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                            <li class="breadcrumb-item active">إضافة جديد</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Alerts -->
            @include('admin.components.alerts')

            <form action="{{ route('courses.store') }}" method="POST" enctype="multipart/form-data" id="courseForm">
                @csrf

                <!-- Basic Information -->
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-info-circle me-2"></i>المعلومات الأساسية
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">عنوان الكورس <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                       placeholder="أدخل عنوان الكورس..."
                                       value="{{ old('title') }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">الكود (Code)</label>
                                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                       placeholder="course-code"
                                       value="{{ old('code') }}">
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">اختياري - يمكن استخدامه كمعرف فريد</small>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Classification -->
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-tags me-2"></i>التصنيف والمستوى
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">التصنيف <span class="text-danger">*</span></label>
                                <select name="course_category_id" class="form-select @error('course_category_id') is-invalid @enderror" required>
                                    <option value="">اختر التصنيف</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('course_category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('course_category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">المستوى</label>
                                <select name="level" class="form-select @error('level') is-invalid @enderror">
                                    <option value="">اختر المستوى</option>
                                    <option value="beginner" {{ old('level') == 'beginner' ? 'selected' : '' }}>مبتدئ</option>
                                    <option value="intermediate" {{ old('level') == 'intermediate' ? 'selected' : '' }}>متوسط</option>
                                    <option value="advanced" {{ old('level') == 'advanced' ? 'selected' : '' }}>متقدم</option>
                                </select>
                                @error('level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Instructor -->
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-user-tie me-2"></i>المدرب والإعدادات
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">المدرب</label>
                                <select name="instructor_id" class="form-select @error('instructor_id') is-invalid @enderror">
                                    <option value="">اختر المدرب</option>
                                    @foreach($instructors as $instructor)
                                        <option value="{{ $instructor->id }}" {{ old('instructor_id') == $instructor->id ? 'selected' : '' }}>
                                            {{ $instructor->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('instructor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">مدة الكورس (بالساعات)</label>
                                <input type="number" name="duration_in_hours" class="form-control @error('duration_in_hours') is-invalid @enderror"
                                       placeholder="0" min="0" step="0.5"
                                       value="{{ old('duration_in_hours') }}">
                                @error('duration_in_hours')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">الحد الأقصى للطلاب</label>
                                <input type="number" name="max_students" class="form-control @error('max_students') is-invalid @enderror"
                                       placeholder="غير محدود" min="0"
                                       value="{{ old('max_students') }}">
                                @error('max_students')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">اترك فارغاً لعدم التحديد</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thumbnail -->
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-image me-2"></i>الصورة المصغرة
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">رفع صورة الكورس</label>
                                <input type="file" name="image" class="form-control @error('image') is-invalid @enderror"
                                       id="imageInput" accept="image/*">
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted d-block mt-1">الأبعاد الموصى بها: 1280x720</small>

                                <div class="mt-3" id="imagePreview" style="display: none;">
                                    <img src="" alt="Preview" class="img-thumbnail" style="max-width: 400px; max-height: 225px;">
                                </div>
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
                            <div class="col-md-12">
                                <label class="form-label">نوع التسجيل <span class="text-danger">*</span></label>
                                <select name="enrollment_type" class="form-select @error('enrollment_type') is-invalid @enderror" required>
                                    <option value="">اختر نوع التسجيل</option>
                                    <option value="open" {{ old('enrollment_type') == 'open' ? 'selected' : '' }}>مفتوح</option>
                                    <option value="invite_only" {{ old('enrollment_type') == 'invite_only' ? 'selected' : '' }}>بالدعوة فقط</option>
                                    <option value="by_approval" {{ old('enrollment_type') == 'by_approval' ? 'selected' : '' }}>يتطلب موافقة</option>
                                </select>
                                @error('enrollment_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_published" id="isPublished"
                                           {{ old('is_published') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isPublished">نشر الكورس مباشرة</label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured"
                                           {{ old('is_featured') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isFeatured">كورس مميز</label>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('courses.index') }}" class="btn btn-light">
                                <i class="fas fa-times me-2"></i>إلغاء
                            </a>
                            <div class="d-flex gap-2">
                                <button type="submit" name="action" value="save" class="btn btn-secondary">
                                    <i class="fas fa-save me-2"></i>حفظ كمسودة
                                </button>
                                <button type="submit" name="action" value="publish" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>حفظ ونشر
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
    // Removed auto-generate slug - now using code field instead

    // Image Preview
    const imageInput = document.getElementById('imageInput');
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.querySelector('img').src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        });
    }


    // Form Validation
    document.getElementById('courseForm').addEventListener('submit', function(e) {
        const action = event.submitter.value;
        if (action === 'publish') {
            document.getElementById('isPublished').checked = true;
        }
    });
</script>
@stop
