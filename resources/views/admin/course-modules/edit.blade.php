@extends('admin.layouts.master')

@section('page-title')
    تعديل الوحدة: {{ $module->title }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تعديل الوحدة: {{ $module->title }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.show', $module->course_id) }}">{{ $module->course->title }}</a></li>
                            <li class="breadcrumb-item active">تعديل الوحدة</li>
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
                            <div class="card-title">معلومات الوحدة</div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('sections.modules.update', [$module->section_id, $module->id]) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <!-- Hidden Fields -->
                                <input type="hidden" name="course_id" value="{{ $module->course_id }}">
                                <input type="hidden" name="section_id" value="{{ $module->section_id }}">
                                <input type="hidden" name="module_type" value="{{ $module->module_type }}">
                                @if($module->modulable_id)
                                    <input type="hidden" name="modulable_id" value="{{ $module->modulable_id }}">
                                @elseif(in_array($module->module_type, ['quiz', 'assignment', 'question_module']))
                                    <input type="hidden" name="modulable_id" value="0">
                                @else
                                    <input type="hidden" name="modulable_id" value="{{ $module->id }}">
                                @endif

                                <!-- Basic Information -->
                                <div class="row gy-3">

                                    <!-- Module Type (Read-only) -->
                                    <div class="col-xl-12">
                                        <label class="form-label">نوع الوحدة</label>
                                        <input type="text" class="form-control" value="@if($module->module_type == 'lesson') درس نصي @elseif($module->module_type == 'video') فيديو @elseif($module->module_type == 'resource') ملف/مورد @elseif($module->module_type == 'quiz') اختبار @elseif($module->module_type == 'assignment') واجب @elseif($module->module_type == 'question_module') وحدة أسئلة @endif" readonly>
                                        <small class="text-muted">لا يمكن تغيير نوع الوحدة بعد الإنشاء</small>
                                    </div>

                                    <!-- Title -->
                                    <div class="col-xl-12">
                                        <label class="form-label">عنوان الوحدة <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                               value="{{ old('title', $module->title) }}" required placeholder="أدخل عنوان الوحدة">
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Description -->
                                    <div class="col-xl-12">
                                        <label class="form-label">الوصف</label>
                                        <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror"
                                                  placeholder="أدخل وصف الوحدة">{{ old('description', $module->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Settings -->
                                    <div class="col-xl-12">
                                        <div class="card border">
                                            <div class="card-header">
                                                <h6 class="mb-0">إعدادات الوحدة</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row gy-3">

                                                    <!-- Is Visible -->
                                                    <div class="col-xl-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="is_visible" id="is_visible"
                                                                   value="1" {{ old('is_visible', $module->is_visible) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="is_visible">
                                                                الوحدة مرئية للطلاب
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <!-- Is Required -->
                                                    <div class="col-xl-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="is_required" id="is_required"
                                                                   value="1" {{ old('is_required', $module->is_required) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="is_required">
                                                                الوحدة مطلوبة للإكمال
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <!-- Is Graded -->
                                                    <div class="col-xl-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="is_graded" id="is_graded"
                                                                   value="1" {{ old('is_graded', $module->is_graded) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="is_graded">
                                                                الوحدة لها درجة
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <!-- Max Score (appears if is_graded) -->
                                                    <div class="col-xl-6" id="max_score_field" style="display: {{ old('is_graded', $module->is_graded) ? 'block' : 'none' }};">
                                                        <label class="form-label">الدرجة القصوى</label>
                                                        <input type="number" name="max_score" class="form-control"
                                                               value="{{ old('max_score', $module->max_score ?? 100) }}" min="0" step="0.01">
                                                    </div>

                                                    <!-- Completion Type -->
                                                    <div class="col-xl-6">
                                                        <label class="form-label">نوع الإكمال</label>
                                                        <select name="completion_type" class="form-select">
                                                            @foreach($completionTypes as $type)
                                                                <option value="{{ $type }}" {{ old('completion_type', $module->completion_type ?? 'auto') == $type ? 'selected' : '' }}>
                                                                    @if($type == 'auto') تلقائي
                                                                    @elseif($type == 'manual') يدوي
                                                                    @elseif($type == 'grade') بناءً على الدرجة
                                                                    @endif
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <!-- Estimated Duration -->
                                                    <div class="col-xl-6">
                                                        <label class="form-label">المدة المقدرة (بالدقائق)</label>
                                                        <input type="number" name="estimated_duration" class="form-control"
                                                               value="{{ old('estimated_duration', $module->estimated_duration) }}" min="0" placeholder="مثال: 30">
                                                    </div>

                                                    <!-- Sort Order -->
                                                    <div class="col-xl-6">
                                                        <label class="form-label">ترتيب العرض</label>
                                                        <input type="number" name="sort_order" class="form-control"
                                                               value="{{ old('sort_order', $module->sort_order) }}" min="0" placeholder="0">
                                                        <small class="text-muted">الرقم الأصغر يظهر أولاً</small>
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
                                                               value="{{ old('available_from', $module->available_from ? $module->available_from->format('Y-m-d\TH:i') : '') }}">
                                                        <small class="text-muted">اتركه فارغاً للإتاحة الفورية</small>
                                                    </div>

                                                    <!-- Available Until -->
                                                    <div class="col-xl-6">
                                                        <label class="form-label">متاح حتى</label>
                                                        <input type="datetime-local" name="available_until" class="form-control"
                                                               value="{{ old('available_until', $module->available_until ? $module->available_until->format('Y-m-d\TH:i') : '') }}">
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
                                            <a href="{{ route('courses.show', $module->course_id) }}" class="btn btn-light">
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
        document.addEventListener('DOMContentLoaded', function() {
            const isGradedCheckbox = document.getElementById('is_graded');
            const maxScoreField = document.getElementById('max_score_field');

            // Show/hide max score field
            if (isGradedCheckbox && maxScoreField) {
                isGradedCheckbox.addEventListener('change', function() {
                    maxScoreField.style.display = this.checked ? 'block' : 'none';
                });
            }
        });
    </script>
@stop

