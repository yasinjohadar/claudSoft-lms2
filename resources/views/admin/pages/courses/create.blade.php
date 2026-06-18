@extends('admin.layouts.master')

@section('page-title')
    إضافة كورس جديد
@stop

@section('content')
    <div class="main-content app-content admin-course-form-page">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                        <li class="breadcrumb-item active">إضافة كورس جديد</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-start gap-3">
                            <span class="admin-course-form-page__icon">
                                <i class="fe fe-book-open"></i>
                            </span>
                            <div class="min-w-0">
                                <span class="group-show-hero__eyebrow">
                                    <i class="fe fe-plus-circle me-1"></i>كورس تعليمي جديد
                                </span>
                                <h2 class="group-show-hero__title mb-2">إضافة كورس جديد</h2>
                                <p class="group-show-hero__desc mb-0">أدخل المعلومات الأساسية والتصنيف، ثم اختر إعدادات التسجيل والنشر قبل الحفظ.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions">
                            <a href="{{ route('courses.index') }}" class="group-show-action group-show-action--info">
                                <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                                <span class="group-show-action__text">رجوع للكورسات</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('courses.store') }}" method="POST" enctype="multipart/form-data" id="courseForm">
                @csrf

                <div class="row g-4 dashboard-fade-in">
                    <div class="col-lg-8">
                        <div class="card custom-card group-show-members-card">
                            <div class="card-header border-0 pb-0">
                                <h6 class="group-show-members-card__title mb-1">المعلومات الأساسية</h6>
                                <p class="fs-12 text-muted mb-0">عنوان الكورس والكود التعريفي الاختياري.</p>
                            </div>
                            <div class="card-body pt-3">
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label fw-semibold">عنوان الكورس <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                               placeholder="أدخل عنوان الكورس..."
                                               value="{{ old('title') }}" required>
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">الكود (Code)</label>
                                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                               placeholder="course-code"
                                               value="{{ old('code') }}">
                                        @error('code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted fs-12">اختياري — يمكن استخدامه كمعرف فريد</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card custom-card group-show-members-card mt-4">
                            <div class="card-header border-0 pb-0">
                                <h6 class="group-show-members-card__title mb-1">التصنيف والمستوى</h6>
                                <p class="fs-12 text-muted mb-0">صنّف الكورس وحدد مستوى الصعوبة المناسب.</p>
                            </div>
                            <div class="card-body pt-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">التصنيف <span class="text-danger">*</span></label>
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
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">المستوى</label>
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

                        <div class="card custom-card group-show-members-card mt-4">
                            <div class="card-header border-0 pb-0">
                                <h6 class="group-show-members-card__title mb-1">الصورة المصغرة</h6>
                                <p class="fs-12 text-muted mb-0">صورة جذابة تظهر في قائمة الكورسات وصفحة التفاصيل.</p>
                            </div>
                            <div class="card-body pt-3">
                                @include('admin.pages.courses.partials.form-thumbnail', [
                                    'inputId' => 'imageInput',
                                    'previewWrapId' => 'thumbnailPreview',
                                ])
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card custom-card group-show-members-card admin-course-form-page__sidebar">
                            <div class="card-header border-0 pb-0">
                                <h6 class="group-show-members-card__title mb-1">المدرب والمدة</h6>
                                <p class="fs-12 text-muted mb-0">تعيين المدرب وتحديد مدة الكورس والحد الأقصى للطلاب.</p>
                            </div>
                            <div class="card-body pt-3">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">المدرب</label>
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
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">مدة الكورس (بالساعات)</label>
                                    <input type="number" name="duration_in_hours" class="form-control @error('duration_in_hours') is-invalid @enderror"
                                           placeholder="0" min="0" step="0.5"
                                           value="{{ old('duration_in_hours') }}">
                                    @error('duration_in_hours')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-0">
                                    <label class="form-label fw-semibold">الحد الأقصى للطلاب</label>
                                    <input type="number" name="max_students" class="form-control @error('max_students') is-invalid @enderror"
                                           placeholder="غير محدود" min="0"
                                           value="{{ old('max_students') }}">
                                    @error('max_students')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted fs-12">اتركه فارغاً لعدم التحديد</small>
                                </div>
                            </div>
                        </div>

                        <div class="card custom-card group-show-members-card admin-course-form-page__sidebar mt-4">
                            <div class="card-header border-0 pb-0">
                                <h6 class="group-show-members-card__title mb-1">إعدادات التسجيل والنشر</h6>
                                <p class="fs-12 text-muted mb-0">نوع التسجيل وحالة النشر والظهور.</p>
                            </div>
                            <div class="card-body pt-3">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">نوع التسجيل <span class="text-danger">*</span></label>
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

                                <div class="admin-group-form-toggle">
                                    <div class="admin-group-form-toggle__info">
                                        <span class="admin-group-form-toggle__label">نشر الكورس</span>
                                        <span class="admin-group-form-toggle__hint">إظهار الكورس للطلاب مباشرة</span>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_published" id="isPublished"
                                               {{ old('is_published') ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="admin-group-form-toggle">
                                    <div class="admin-group-form-toggle__info">
                                        <span class="admin-group-form-toggle__label">كورس مميز</span>
                                        <span class="admin-group-form-toggle__hint">يظهر في قسم الكورسات المميزة</span>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured"
                                               {{ old('is_featured') ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="admin-course-form-page__actions">
                                    <button type="submit" name="action" value="publish" class="btn btn-primary w-100">
                                        <i class="fe fe-send me-1"></i>حفظ ونشر
                                    </button>
                                    <button type="submit" name="action" value="save" class="btn btn-outline-secondary w-100">
                                        <i class="fe fe-save me-1"></i>حفظ كمسودة
                                    </button>
                                    <a href="{{ route('courses.index') }}" class="btn btn-light w-100">
                                        إلغاء
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
    @include('admin.pages.courses.partials.form-scripts')
    <script>
        initCourseThumbnail('imageInput', 'thumbnailPreview');

        document.getElementById('courseForm').addEventListener('submit', function () {
            const action = event.submitter?.value;
            if (action === 'publish') {
                document.getElementById('isPublished').checked = true;
            }
        });
    </script>
@stop
