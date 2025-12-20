@extends('admin.layouts.master')

@section('page-title')
    تعديل التصنيف: {{ $category->name }}
@stop

@section('css')
<style>
    .color-preview {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        border: 2px solid #dee2e6;
        display: inline-block;
        vertical-align: middle;
    }
    .image-preview {
        max-width: 200px;
        max-height: 200px;
        border-radius: 8px;
        border: 2px solid #dee2e6;
        margin-top: 10px;
    }
</style>
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-exclamation-circle me-2"></i>خطأ!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-exclamation-circle me-2"></i>يوجد أخطاء في النموذج:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تعديل التصنيف: {{ $category->name }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('course-categories.index') }}">التصنيفات</a></li>
                            <li class="breadcrumb-item active" aria-current="page">تعديل</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- Page Header Close -->

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">تعديل بيانات التصنيف</div>
                        </div>

                        <form action="{{ route('course-categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="card-body">
                                <div class="row g-3">

                                    <!-- المعلومات الأساسية -->
                                    <div class="col-12">
                                        <h6 class="text-primary mb-3">
                                            <i class="fas fa-info-circle me-2"></i>المعلومات الأساسية
                                        </h6>
                                    </div>

                                    <!-- الاسم -->
                                    <div class="col-md-6">
                                        <label class="form-label">اسم التصنيف <span class="text-danger">*</span></label>
                                        <input type="text"
                                               class="form-control @error('name') is-invalid @enderror"
                                               name="name"
                                               value="{{ old('name', $category->name) }}"
                                               placeholder="أدخل اسم التصنيف"
                                               required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- المعرف (Slug) -->
                                    <div class="col-md-6">
                                        <label class="form-label">المعرف (Slug) <small class="text-muted">(اختياري)</small></label>
                                        <input type="text"
                                               class="form-control @error('slug') is-invalid @enderror"
                                               name="slug"
                                               value="{{ old('slug', $category->slug) }}"
                                               placeholder="سيتم إنشاؤه تلقائياً">
                                        <small class="text-muted">سيتم إنشاؤه تلقائياً من الاسم إذا تركته فارغاً</small>
                                        @error('slug')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- الوصف -->
                                    <div class="col-12">
                                        <label class="form-label">الوصف</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror"
                                                  name="description"
                                                  rows="4"
                                                  placeholder="أدخل وصف التصنيف">{{ old('description', $category->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- التخصيص البصري -->
                                    <div class="col-12 mt-4">
                                        <h6 class="text-primary mb-3">
                                            <i class="fas fa-palette me-2"></i>التخصيص البصري
                                        </h6>
                                    </div>

                                    <!-- اللون -->
                                    <div class="col-md-4">
                                        <label class="form-label">اللون</label>
                                        <div class="input-group">
                                            <input type="color"
                                                   class="form-control form-control-color @error('color') is-invalid @enderror"
                                                   name="color"
                                                   value="{{ old('color', $category->color) }}"
                                                   id="colorPicker">
                                            <input type="text"
                                                   class="form-control"
                                                   id="colorValue"
                                                   value="{{ old('color', $category->color) }}"
                                                   readonly>
                                        </div>
                                        @error('color')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- الأيقونة -->
                                    <div class="col-md-4">
                                        <label class="form-label">الأيقونة <small class="text-muted">(FontAwesome)</small></label>
                                        <div class="input-group">
                                            <input type="text"
                                                   class="form-control @error('icon') is-invalid @enderror"
                                                   name="icon"
                                                   value="{{ old('icon', $category->icon) }}"
                                                   placeholder="مثال: fas fa-laptop-code">
                                            @if($category->icon)
                                                <span class="input-group-text">
                                                    <i class="{{ $category->icon }}"></i>
                                                </span>
                                            @endif
                                        </div>
                                        <small class="text-muted">
                                            <a href="https://fontawesome.com/icons" target="_blank">تصفح الأيقونات</a>
                                        </small>
                                        @error('icon')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- الترتيب -->
                                    <div class="col-md-4">
                                        <label class="form-label">الترتيب</label>
                                        <input type="number"
                                               class="form-control @error('order') is-invalid @enderror"
                                               name="order"
                                               value="{{ old('order', $category->order) }}"
                                               min="0">
                                        @error('order')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- صورة التصنيف -->
                                    <div class="col-md-6">
                                        <label class="form-label">صورة التصنيف</label>
                                        <input type="file"
                                               class="form-control @error('image') is-invalid @enderror"
                                               name="image"
                                               accept="image/*"
                                               id="imageInput">
                                        <small class="text-muted">يفضل صورة بحجم 300x300 بكسل</small>
                                        @error('image')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                        @if($category->image)
                                            <div class="mt-2">
                                                <img src="{{ asset('storage/' . $category->image) }}"
                                                     alt="{{ $category->name }}"
                                                     class="image-preview"
                                                     id="currentImage">
                                                <p class="text-muted mt-1"><small>الصورة الحالية</small></p>
                                            </div>
                                        @endif
                                        <div id="imagePreview"></div>
                                    </div>

                                    <!-- التصنيف الأب -->
                                    <div class="col-md-6">
                                        <label class="form-label">التصنيف الأب <small class="text-muted">(اختياري)</small></label>
                                        <select class="form-select @error('parent_id') is-invalid @enderror"
                                                name="parent_id">
                                            <option value="">بدون - تصنيف رئيسي</option>
                                            @foreach($parentCategories as $parent)
                                                <option value="{{ $parent->id }}"
                                                        {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                                                    {{ $parent->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('parent_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- الحالة -->
                                    <div class="col-12 mt-4">
                                        <h6 class="text-primary mb-3">
                                            <i class="fas fa-toggle-on me-2"></i>الإعدادات
                                        </h6>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   name="is_active"
                                                   id="is_active"
                                                   {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                تفعيل التصنيف
                                            </label>
                                        </div>
                                        <small class="text-muted">التصنيفات النشطة فقط تظهر للطلاب</small>
                                    </div>

                                </div>
                            </div>

                            <div class="card-footer text-end">
                                <a href="{{ route('course-categories.index') }}" class="btn btn-light me-2">
                                    <i class="fas fa-times me-2"></i>إلغاء
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>حفظ التعديلات
                                </button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

@section('script')
<script>
    // Color picker sync
    document.getElementById('colorPicker').addEventListener('input', function() {
        document.getElementById('colorValue').value = this.value;
    });

    // Image preview
    document.getElementById('imageInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imagePreview').innerHTML =
                    '<img src="' + e.target.result + '" class="image-preview mt-2"><p class="text-muted mt-1"><small>معاينة الصورة الجديدة</small></p>';
                // Hide current image
                const currentImage = document.getElementById('currentImage');
                if (currentImage) {
                    currentImage.style.display = 'none';
                }
            };
            reader.readAsDataURL(file);
        }
    });
</script>
@stop
