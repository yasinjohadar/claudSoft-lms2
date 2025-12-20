@extends('admin.layouts.master')

@section('page-title')
    تعديل المعسكر: {{ $camp->name }}
@stop

@section('css')
<style>
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

    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تعديل المعسكر: {{ $camp->name }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('training-camps.index') }}">المعسكرات التدريبية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">تعديل</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <form action="{{ route('training-camps.update', $camp->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- البيانات الأساسية -->
                    <div class="col-xl-8">
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">
                                    <i class="fas fa-info-circle me-2"></i>البيانات الأساسية
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">

                                    <!-- اسم المعسكر -->
                                    <div class="col-md-12">
                                        <label class="form-label">اسم المعسكر <span class="text-danger">*</span></label>
                                        <input type="text"
                                               class="form-control @error('name') is-invalid @enderror"
                                               name="name"
                                               value="{{ old('name', $camp->name) }}"
                                               placeholder="مثال: معسكر البرمجة المكثف"
                                               required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- المعرف (Slug) -->
                                    <div class="col-md-12">
                                        <label class="form-label">المعرف (Slug) <small class="text-muted">(اختياري)</small></label>
                                        <input type="text"
                                               class="form-control @error('slug') is-invalid @enderror"
                                               name="slug"
                                               value="{{ old('slug', $camp->slug) }}"
                                               placeholder="سيتم إنشاؤه تلقائياً">
                                        <small class="text-muted">سيتم إنشاؤه تلقائياً من الاسم إذا تركته فارغاً</small>
                                        @error('slug')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- الوصف -->
                                    <div class="col-12">
                                        <label class="form-label">وصف المعسكر</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror"
                                                  name="description"
                                                  rows="5"
                                                  placeholder="أدخل وصفاً تفصيلياً للمعسكر التدريبي...">{{ old('description', $camp->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- اسم المدرب -->
                                    <div class="col-md-6">
                                        <label class="form-label">اسم المدرب</label>
                                        <input type="text"
                                               class="form-control @error('instructor_name') is-invalid @enderror"
                                               name="instructor_name"
                                               value="{{ old('instructor_name', $camp->instructor_name) }}"
                                               placeholder="أدخل اسم المدرب">
                                        @error('instructor_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- الموقع -->
                                    <div class="col-md-6">
                                        <label class="form-label">الموقع / نوع المعسكر</label>
                                        <input type="text"
                                               class="form-control @error('location') is-invalid @enderror"
                                               name="location"
                                               value="{{ old('location', $camp->location) }}"
                                               placeholder="مثال: أونلاين / حضوري - الرياض">
                                        @error('location')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- التواريخ والأسعار -->
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">
                                    <i class="fas fa-calendar-alt me-2"></i>التواريخ والأسعار
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">

                                    <!-- تاريخ البداية -->
                                    <div class="col-md-6">
                                        <label class="form-label">تاريخ البداية <span class="text-danger">*</span></label>
                                        <input type="date"
                                               class="form-control @error('start_date') is-invalid @enderror"
                                               name="start_date"
                                               value="{{ old('start_date', $camp->start_date->format('Y-m-d')) }}"
                                               required>
                                        @error('start_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- تاريخ النهاية -->
                                    <div class="col-md-6">
                                        <label class="form-label">تاريخ النهاية <span class="text-danger">*</span></label>
                                        <input type="date"
                                               class="form-control @error('end_date') is-invalid @enderror"
                                               name="end_date"
                                               value="{{ old('end_date', $camp->end_date->format('Y-m-d')) }}"
                                               required>
                                        @error('end_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- السعر -->
                                    <div class="col-md-6">
                                        <label class="form-label">السعر ($) <span class="text-danger">*</span></label>
                                        <input type="number"
                                               class="form-control @error('price') is-invalid @enderror"
                                               name="price"
                                               value="{{ old('price', $camp->price) }}"
                                               min="0"
                                               step="0.01"
                                               placeholder="0.00"
                                               required>
                                        @error('price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- الحد الأقصى للمشاركين -->
                                    <div class="col-md-6">
                                        <label class="form-label">الحد الأقصى للمشاركين</label>
                                        <input type="number"
                                               class="form-control @error('max_participants') is-invalid @enderror"
                                               name="max_participants"
                                               value="{{ old('max_participants', $camp->max_participants) }}"
                                               min="1"
                                               placeholder="اتركه فارغاً لعدم وجود حد">
                                        <small class="text-muted">
                                            المسجلين حالياً: <strong>{{ $camp->current_participants }}</strong>
                                        </small>
                                        @error('max_participants')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- الإعدادات الجانبية -->
                    <div class="col-xl-4">

                        <!-- التصنيف والصورة -->
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">
                                    <i class="fas fa-cog me-2"></i>الإعدادات
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">

                                    <!-- التصنيف -->
                                    <div class="col-12">
                                        <label class="form-label">التصنيف</label>
                                        <select class="form-select @error('category_id') is-invalid @enderror"
                                                name="category_id">
                                            <option value="">اختر التصنيف</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}"
                                                        {{ old('category_id', $camp->category_id) == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('category_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- الترتيب -->
                                    <div class="col-12">
                                        <label class="form-label">الترتيب</label>
                                        <input type="number"
                                               class="form-control @error('order') is-invalid @enderror"
                                               name="order"
                                               value="{{ old('order', $camp->order) }}"
                                               min="0">
                                        <small class="text-muted">يستخدم لترتيب عرض المعسكرات</small>
                                        @error('order')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- صورة المعسكر -->
                                    <div class="col-12">
                                        <label class="form-label">صورة المعسكر</label>
                                        <input type="file"
                                               class="form-control @error('image') is-invalid @enderror"
                                               name="image"
                                               accept="image/*"
                                               id="imageInput">
                                        <small class="text-muted">يفضل صورة بحجم 1200x600 بكسل</small>
                                        @error('image')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                        @if($camp->image)
                                            <div class="mt-2">
                                                <img src="{{ asset('storage/' . $camp->image) }}"
                                                     alt="{{ $camp->name }}"
                                                     class="image-preview"
                                                     id="currentImage">
                                                <p class="text-muted mt-1"><small>الصورة الحالية</small></p>
                                            </div>
                                        @endif
                                        <div id="imagePreview"></div>
                                    </div>

                                    <!-- الحالة -->
                                    <div class="col-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   name="is_active"
                                                   id="is_active"
                                                   {{ old('is_active', $camp->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                <strong>تفعيل المعسكر</strong>
                                            </label>
                                        </div>
                                        <small class="text-muted">المعسكرات النشطة فقط تظهر للطلاب</small>
                                    </div>

                                    <!-- مميز -->
                                    <div class="col-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   name="is_featured"
                                                   id="is_featured"
                                                   {{ old('is_featured', $camp->is_featured) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_featured">
                                                <strong>معسكر مميز</strong>
                                            </label>
                                        </div>
                                        <small class="text-muted">سيظهر في القسم المميز بالصفحة الرئيسية</small>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- أزرار الحفظ -->
                        <div class="card custom-card">
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save me-2"></i>حفظ التعديلات
                                    </button>
                                    <a href="{{ route('training-camps.index') }}" class="btn btn-light">
                                        <i class="fas fa-times me-2"></i>إلغاء
                                    </a>
                                    <a href="{{ route('training-camps.show', $camp->id) }}" class="btn btn-info">
                                        <i class="fas fa-eye me-2"></i>عرض المعسكر
                                    </a>
                                </div>
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
    // Image preview
    document.getElementById('imageInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imagePreview').innerHTML =
                    '<img src="' + e.target.result + '" class="image-preview mt-2"><p class="text-muted mt-1"><small>معاينة الصورة الجديدة</small></p>';
                const currentImage = document.getElementById('currentImage');
                if (currentImage) {
                    currentImage.style.display = 'none';
                }
            };
            reader.readAsDataURL(file);
        }
    });

    // Auto calculate duration
    const startDate = document.querySelector('input[name="start_date"]');
    const endDate = document.querySelector('input[name="end_date"]');

    function updateEndDateMin() {
        if (startDate.value) {
            endDate.min = startDate.value;
        }
    }

    startDate.addEventListener('change', updateEndDateMin);
    updateEndDateMin(); // Call on page load
</script>
@stop
