@extends('admin.layouts.master')

@section('page-title')
    تعديل الكورس: {{ $course->title }}
@stop

@push('head')
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
@endpush

@section('content')
    <div class="main-content app-content admin-course-form-page">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                        <li class="breadcrumb-item active">تعديل الكورس</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-start gap-3">
                            <span class="admin-course-form-page__icon">
                                <i class="fe fe-edit-2"></i>
                            </span>
                            <div class="min-w-0">
                                <span class="group-show-hero__eyebrow">
                                    <i class="fe fe-layers me-1"></i>تعديل الكورس
                                </span>
                                <h2 class="group-show-hero__title mb-2">تعديل الكورس</h2>
                                <p class="group-show-hero__desc mb-2">حدّث بيانات الكورس والإعدادات والصورة المصغرة.</p>
                                <span class="group-show-chip group-show-chip--sm">
                                    <i class="fe fe-book-open me-1"></i>{{ Str::limit($course->title, 40) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions">
                            <a href="{{ route('courses.show', $course->id) }}" class="group-show-action group-show-action--success">
                                <span class="group-show-action__icon"><i class="fe fe-eye"></i></span>
                                <span class="group-show-action__text">معاينة الكورس</span>
                            </a>
                            <a href="{{ route('courses.index') }}" class="group-show-action group-show-action--info">
                                <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                                <span class="group-show-action__text">رجوع للكورسات</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('courses.update', $course->id) }}" method="POST" enctype="multipart/form-data" id="courseForm">
                @csrf
                @method('PUT')

                <div class="row g-4 dashboard-fade-in">
                    <div class="col-lg-8">
                        <div class="card custom-card group-show-members-card">
                            <div class="card-header border-0 pb-0">
                                <h6 class="group-show-members-card__title mb-1">المعلومات الأساسية</h6>
                                <p class="fs-12 text-muted mb-0">عنوان الكورس والرابط التعريفي (Slug).</p>
                            </div>
                            <div class="card-body pt-3">
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label fw-semibold">عنوان الكورس <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                               value="{{ old('title', $course->title) }}" required>
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">الكود (Slug) <span class="text-danger">*</span></label>
                                        <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                                               value="{{ old('slug', $course->slug) }}" required>
                                        @error('slug')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
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
                                        <label class="form-label fw-semibold">المستوى</label>
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
                        </div>

                        <div class="card custom-card group-show-members-card mt-4">
                            <div class="card-header border-0 pb-0">
                                <h6 class="group-show-members-card__title mb-1">الصورة المصغرة</h6>
                                <p class="fs-12 text-muted mb-0">انقر أو اسحب صورة جديدة لاستبدال الصورة الحالية.</p>
                            </div>
                            <div class="card-body pt-3">
                                @php
                                    $imageUrl = $course->image ? course_image_url($course->image) : null;
                                @endphp
                                @include('admin.pages.courses.partials.form-thumbnail', [
                                    'inputId' => 'thumbnailInput',
                                    'previewWrapId' => 'thumbnailPreview',
                                    'existingImageUrl' => $imageUrl,
                                    'altText' => $course->title,
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
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">مدة الكورس (بالساعات)</label>
                                    <input type="number" name="duration_in_hours" class="form-control @error('duration_in_hours') is-invalid @enderror"
                                           min="0" value="{{ old('duration_in_hours', $course->duration_in_hours) }}" placeholder="0">
                                    @error('duration_in_hours')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-0">
                                    <label class="form-label fw-semibold">الحد الأقصى للطلاب</label>
                                    <input type="number" name="max_students" class="form-control @error('max_students') is-invalid @enderror"
                                           min="0" value="{{ old('max_students', $course->max_students) }}" placeholder="غير محدود">
                                    @error('max_students')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="card custom-card group-show-members-card admin-course-form-page__sidebar mt-4">
                            <div class="card-header border-0 pb-0">
                                <h6 class="group-show-members-card__title mb-1">إعدادات التسجيل والنشر</h6>
                                <p class="fs-12 text-muted mb-0">نوع التسجيل وفترة التوفر وحالة النشر.</p>
                            </div>
                            <div class="card-body pt-3">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">نوع التسجيل <span class="text-danger">*</span></label>
                                    <select name="enrollment_type" class="form-select @error('enrollment_type') is-invalid @enderror" required>
                                        <option value="">اختر نوع التسجيل</option>
                                        <option value="open" {{ old('enrollment_type', $course->enrollment_type) == 'open' ? 'selected' : '' }}>مفتوح — التسجيل مباشر</option>
                                        <option value="by_approval" {{ old('enrollment_type', $course->enrollment_type) == 'by_approval' ? 'selected' : '' }}>يتطلب موافقة</option>
                                        <option value="invite_only" {{ old('enrollment_type', $course->enrollment_type) == 'invite_only' ? 'selected' : '' }}>بالدعوة فقط</option>
                                    </select>
                                    @error('enrollment_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="admin-course-form-page__enrollment-note">
                                        <i class="fe fe-info"></i>
                                        <div>
                                            <strong>مفتوح:</strong> انضمام مباشر —
                                            <strong>يتطلب موافقة:</strong> الطلب يبقى معلقاً —
                                            <strong>بالدعوة فقط:</strong> لا يمكن التسجيل
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">متاح من</label>
                                    <input type="datetime-local" name="available_from" class="form-control"
                                           value="{{ old('available_from', $course->available_from ? $course->available_from->format('Y-m-d\TH:i') : '') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">متاح حتى</label>
                                    <input type="datetime-local" name="available_until" class="form-control"
                                           value="{{ old('available_until', $course->available_until ? $course->available_until->format('Y-m-d\TH:i') : '') }}">
                                </div>

                                <div class="admin-group-form-toggle">
                                    <div class="admin-group-form-toggle__info">
                                        <span class="admin-group-form-toggle__label">منشور</span>
                                        <span class="admin-group-form-toggle__hint">ظاهر للطلاب</span>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_published" id="isPublished"
                                               {{ old('is_published', $course->is_published) ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="admin-group-form-toggle">
                                    <div class="admin-group-form-toggle__info">
                                        <span class="admin-group-form-toggle__label">كورس مميز</span>
                                        <span class="admin-group-form-toggle__hint">يظهر في قسم الكورسات المميزة</span>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured"
                                               {{ old('is_featured', $course->is_featured) ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="admin-course-form-page__actions">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fe fe-save me-1"></i>حفظ التعديلات
                                    </button>
                                    <a href="{{ route('courses.show', $course->id) }}" class="btn btn-outline-primary w-100">
                                        معاينة الكورس
                                    </a>
                                    <a href="{{ route('courses.index') }}" class="btn btn-outline-secondary w-100">
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
        initCourseThumbnail('thumbnailInput', 'thumbnailPreview');

        document.querySelector('input[name="title"]').addEventListener('input', function (e) {
            const title = e.target.value;
            const slug = title.toLowerCase()
                .replace(/[^\u0600-\u06FFa-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
            document.querySelector('input[name="slug"]').value = slug;
        });
    </script>
@stop
