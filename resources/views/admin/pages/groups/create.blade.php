@extends('admin.layouts.master')

@section('page-title')
    إنشاء مجموعة جديدة - {{ $course->title }}
@stop

@section('content')
    <div class="main-content app-content admin-group-form-page">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('courses.show', $course->id) }}">{{ Str::limit($course->title, 32) }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('courses.groups.index', $course->id) }}">المجموعات</a></li>
                        <li class="breadcrumb-item active">إنشاء مجموعة</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-start gap-3">
                            <span class="admin-group-form-page__icon">
                                <i class="fe fe-users"></i>
                            </span>
                            <div class="min-w-0">
                                <span class="group-show-hero__eyebrow">
                                    <i class="fe fe-plus-circle me-1"></i>مجموعة تعليمية جديدة
                                </span>
                                <h2 class="group-show-hero__title mb-2">إنشاء مجموعة</h2>
                                <p class="group-show-hero__desc mb-2">أنشئ مجموعة واربطها بالكورسات المناسبة، ثم أضف الطلاب لاحقاً من صفحة التفاصيل.</p>
                                <span class="group-show-chip group-show-chip--sm">
                                    <i class="fe fe-book-open me-1"></i>{{ Str::limit($course->title, 40) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions">
                            <a href="{{ route('courses.groups.index', $course->id) }}" class="group-show-action group-show-action--info">
                                <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                                <span class="group-show-action__text">رجوع للمجموعات</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <form id="groupForm" action="{{ route('courses.groups.store', $course->id) }}" method="POST">
                @csrf

                <div class="row g-4 dashboard-fade-in">
                    <div class="col-lg-8">
                        <div class="card custom-card group-show-members-card">
                            <div class="card-header border-0 pb-0">
                                <h6 class="group-show-members-card__title mb-1">المعلومات الأساسية</h6>
                                <p class="fs-12 text-muted mb-0">اسم المجموعة ووصف مختصر يظهر في لوحة التحكم.</p>
                            </div>
                            <div class="card-body pt-3">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">اسم المجموعة <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name') }}" placeholder="مثال: مجموعة المبتدئين — دفعة 2026" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">الوصف المختصر</label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                              rows="3" placeholder="وصف اختياري للمجموعة...">{{ old('description') }}</textarea>
                                    <small class="text-muted fs-12">يظهر كنص مختصر في القوائم ولوحة التحكم.</small>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-semibold" for="group_details">التفاصيل</label>
                                    <textarea name="details" id="group_details"
                                              class="form-control @error('details') is-invalid @enderror"
                                              rows="10">{{ old('details') }}</textarea>
                                    <small class="text-muted fs-12">تظهر للطالب في صفحة تفاصيل المجموعة / المعسكر.</small>
                                    @error('details')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-0">
                                    <label class="form-label fw-semibold" for="group_terms">الشروط</label>
                                    <textarea name="terms" id="group_terms"
                                              class="form-control @error('terms') is-invalid @enderror"
                                              rows="10">{{ old('terms') }}</textarea>
                                    <small class="text-muted fs-12">تظهر للطالب ويرتبط بها مربع الموافقة عند طلب الانضمام / التسجيل.</small>
                                    @error('terms')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="card custom-card group-show-members-card mt-4">
                            <div class="card-header border-0 pb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div>
                                    <h6 class="group-show-members-card__title mb-1">الكورسات المرتبطة</h6>
                                    <p class="fs-12 text-muted mb-0">اختر الكورسات التي تنتمي إليها هذه المجموعة.</p>
                                </div>
                                <div class="admin-group-form-page__search-wrap">
                                    <i class="fe fe-search"></i>
                                    <input type="search" id="groupCourseSearch" class="form-control form-control-sm"
                                           placeholder="بحث في الكورسات...">
                                </div>
                            </div>
                            <div class="card-body pt-3">
                                @include('admin.pages.groups.partials.form-course-picker', ['courses' => $courses, 'course' => $course])
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card custom-card group-show-members-card admin-group-form-page__sidebar">
                            <div class="card-header border-0 pb-0">
                                <h6 class="group-show-members-card__title mb-1">إعدادات المجموعة</h6>
                                <p class="fs-12 text-muted mb-0">الحالة والظهور والحد الأقصى للأعضاء.</p>
                            </div>
                            <div class="card-body pt-3">
                                <div class="admin-group-form-toggle">
                                    <div class="admin-group-form-toggle__info">
                                        <span class="admin-group-form-toggle__label">المجموعة نشطة</span>
                                        <span class="admin-group-form-toggle__hint">تفعيل الوصول والإدارة</span>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="admin-group-form-toggle">
                                    <div class="admin-group-form-toggle__info">
                                        <span class="admin-group-form-toggle__label">مرئية للطلاب</span>
                                        <span class="admin-group-form-toggle__hint">إظهار المجموعة في واجهة الطالب</span>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_visible" id="is_visible"
                                               {{ old('is_visible', true) ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="admin-group-form-toggle">
                                    <div class="admin-group-form-toggle__info">
                                        <span class="admin-group-form-toggle__label">تقييد الأجهزة الموثوقة</span>
                                        <span class="admin-group-form-toggle__hint">إلزام أعضاء المجموعة بالدخول من الأجهزة الموثوقة فقط</span>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="device_lock_enabled" id="device_lock_enabled"
                                               {{ old('device_lock_enabled', false) ? 'checked' : '' }}>
                                    </div>
                                </div>

                                @include('admin.pages.groups.partials.camp-fields')

                                <div class="mb-0">
                                    <label class="form-label fw-semibold">الحد الأقصى للأعضاء</label>
                                    <input type="number" name="max_members" class="form-control @error('max_members') is-invalid @enderror"
                                           value="{{ old('max_members') }}" min="1" placeholder="بدون حد">
                                    <small class="text-muted fs-12">اتركه فارغاً لعدم وجود حد أقصى</small>
                                    @error('max_members')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="admin-group-form-page__actions">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fe fe-save me-1"></i>إنشاء المجموعة
                                    </button>
                                    <a href="{{ route('courses.groups.index', $course->id) }}" class="btn btn-outline-secondary w-100">
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
    @include('admin.pages.groups.partials.form-scripts')
    @include('admin.blog.partials.tinymce-config', [
        'editors' => [
            ['selector' => '#group_details', 'height' => 420],
            ['selector' => '#group_terms', 'height' => 360],
        ],
        'formSelector' => '#groupForm',
    ])
@stop
