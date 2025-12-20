@extends('admin.layouts.master')

@section('page-title')
    تعديل القسم
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تعديل القسم: {{ $section->title }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.show', $section->course_id) }}">{{ $section->course->title }}</a></li>
                            <li class="breadcrumb-item active">تعديل القسم</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Section Form -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">معلومات القسم</div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('courses.sections.update', [$section->course_id, $section->id]) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <!-- Basic Information -->
                                <div class="row gy-3">
                                    <!-- Title -->
                                    <div class="col-xl-12">
                                        <label class="form-label">عنوان القسم <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                               value="{{ old('title', $section->title) }}" required placeholder="أدخل عنوان القسم">
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Description -->
                                    <div class="col-xl-12">
                                        <label class="form-label">الوصف</label>
                                        <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror"
                                                  placeholder="أدخل وصف القسم">{{ old('description', $section->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Visibility Settings -->
                                    <div class="col-xl-12">
                                        <div class="card border">
                                            <div class="card-header">
                                                <h6 class="mb-0">إعدادات الظهور</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row gy-3">
                                                    <!-- Is Visible -->
                                                    <div class="col-xl-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="is_visible" id="is_visible"
                                                                   value="1" {{ old('is_visible', $section->is_visible) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="is_visible">
                                                                القسم مرئي للطلاب
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <!-- Is Locked -->
                                                    <div class="col-xl-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="is_locked" id="is_locked"
                                                                   value="1" {{ old('is_locked', $section->is_locked) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="is_locked">
                                                                قفل القسم (يتطلب شروط لفتحه)
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <!-- Show Unavailable -->
                                                    <div class="col-xl-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="show_unavailable" id="show_unavailable"
                                                                   value="1" {{ old('show_unavailable', $section->show_unavailable) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="show_unavailable">
                                                                إظهار المحتوى غير المتاح
                                                            </label>
                                                        </div>
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
                                                        <input type="datetime-local" name="available_from" class="form-control @error('available_from') is-invalid @enderror"
                                                               value="{{ old('available_from', $section->available_from ? $section->available_from->format('Y-m-d\TH:i') : '') }}">
                                                        @error('available_from')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                        <small class="text-muted">اتركه فارغاً للإتاحة الفورية</small>
                                                    </div>

                                                    <!-- Available Until -->
                                                    <div class="col-xl-6">
                                                        <label class="form-label">متاح حتى</label>
                                                        <input type="datetime-local" name="available_until" class="form-control @error('available_until') is-invalid @enderror"
                                                               value="{{ old('available_until', $section->available_until ? $section->available_until->format('Y-m-d\TH:i') : '') }}">
                                                        @error('available_until')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
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
                                                <i class="fas fa-save me-2"></i>حفظ التعديلات
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
@stop
