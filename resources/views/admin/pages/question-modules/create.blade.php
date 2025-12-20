@extends('admin.layouts.master')

@section('page-title')
    إنشاء وحدة أسئلة جديدة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إنشاء وحدة أسئلة جديدة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            @if($section)
                                <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('courses.show', $section->course->id) }}">{{ $section->course->title }}</a></li>
                                <li class="breadcrumb-item active">إنشاء وحدة أسئلة</li>
                            @else
                                <li class="breadcrumb-item"><a href="{{ route('question-modules.index') }}">وحدات الأسئلة</a></li>
                                <li class="breadcrumb-item active">إنشاء وحدة جديدة</li>
                            @endif
                        </ol>
                    </nav>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Create Form -->
            <form action="{{ route('question-modules.store') }}" method="POST">
                @csrf

                @if($section)
                    <input type="hidden" name="section_id" value="{{ $section->id }}">
                @endif

                <div class="row">
                    <!-- Main Content -->
                    <div class="col-xl-8">
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">
                                    معلومات وحدة الأسئلة
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Title -->
                                <div class="mb-3">
                                    <label for="title" class="form-label">العنوان <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title"
                                           value="{{ old('title') }}" required>
                                </div>

                                <!-- Description -->
                                <div class="mb-3">
                                    <label for="description" class="form-label">الوصف</label>
                                    <textarea class="form-control" id="description" name="description"
                                              rows="3">{{ old('description') }}</textarea>
                                    <small class="text-muted">وصف مختصر لوحدة الأسئلة</small>
                                </div>

                                <!-- Instructions -->
                                <div class="mb-3">
                                    <label for="instructions" class="form-label">التعليمات</label>
                                    <textarea class="form-control" id="instructions" name="instructions"
                                              rows="4">{{ old('instructions') }}</textarea>
                                    <small class="text-muted">تعليمات للطلاب قبل البدء في الإجابة على الأسئلة</small>
                                </div>

                                @unless($section)
                                    <!-- Section Selection (only if not coming from course page) -->
                                    <div class="mb-3">
                                        <label for="section_id" class="form-label">القسم <span class="text-danger">*</span></label>
                                        <select class="form-select" id="section_id" name="section_id" required>
                                            <option value="">اختر القسم</option>
                                            @foreach(\App\Models\Course::with('sections')->get() as $course)
                                                <optgroup label="{{ $course->title }}">
                                                    @foreach($course->sections as $sec)
                                                        <option value="{{ $sec->id }}" {{ old('section_id') == $sec->id ? 'selected' : '' }}>
                                                            {{ $sec->title }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                    </div>
                                @endunless
                            </div>
                        </div>

                        <!-- Settings Card -->
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">
                                    إعدادات الوحدة
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Time Limit -->
                                    <div class="col-md-6 mb-3">
                                        <label for="time_limit" class="form-label">الوقت المحدد (بالدقائق)</label>
                                        <input type="number" class="form-control" id="time_limit" name="time_limit"
                                               value="{{ old('time_limit', 0) }}" min="0">
                                        <small class="text-muted">اترك 0 لعدم تحديد وقت</small>
                                    </div>

                                    <!-- Attempts Allowed -->
                                    <div class="col-md-6 mb-3">
                                        <label for="attempts_allowed" class="form-label">عدد المحاولات المسموح بها</label>
                                        <input type="number" class="form-control" id="attempts_allowed" name="attempts_allowed"
                                               value="{{ old('attempts_allowed', 1) }}" min="1">
                                    </div>

                                    <!-- Pass Percentage -->
                                    <div class="col-md-6 mb-3">
                                        <label for="pass_percentage" class="form-label">نسبة النجاح (%)</label>
                                        <input type="number" class="form-control" id="pass_percentage" name="pass_percentage"
                                               value="{{ old('pass_percentage', 50) }}" min="0" max="100" step="0.01">
                                    </div>

                                    <!-- Sort Order -->
                                    <div class="col-md-6 mb-3">
                                        <label for="sort_order" class="form-label">ترتيب العرض</label>
                                        <input type="number" class="form-control" id="sort_order" name="sort_order"
                                               value="{{ old('sort_order', 0) }}" min="0">
                                    </div>
                                </div>

                                <!-- Checkboxes -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="shuffle_questions"
                                                   name="shuffle_questions" {{ old('shuffle_questions') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="shuffle_questions">
                                                خلط ترتيب الأسئلة
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="show_results"
                                                   name="show_results" {{ old('show_results', true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="show_results">
                                                عرض النتائج للطلاب
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-xl-4">
                        <!-- Status Card -->
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">
                                    الحالة والنشر
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Published -->
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_published"
                                               name="is_published" {{ old('is_published', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_published">
                                            نشر الوحدة
                                        </label>
                                    </div>
                                </div>

                                <!-- Visible -->
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_visible"
                                               name="is_visible" {{ old('is_visible', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_visible">
                                            إظهار للطلاب
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Availability Card -->
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">
                                    التوفر
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Available From -->
                                <div class="mb-3">
                                    <label for="available_from" class="form-label">متاح من</label>
                                    <input type="datetime-local" class="form-control" id="available_from"
                                           name="available_from" value="{{ old('available_from') }}">
                                </div>

                                <!-- Available Until -->
                                <div class="mb-3">
                                    <label for="available_until" class="form-label">متاح حتى</label>
                                    <input type="datetime-local" class="form-control" id="available_until"
                                           name="available_until" value="{{ old('available_until') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="card custom-card">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary w-100 mb-2">
                                    <i class="fas fa-save me-2"></i>حفظ وإضافة الأسئلة
                                </button>
                                <a href="{{ $section ? route('courses.show', $section->course->id) : route('question-modules.index') }}"
                                   class="btn btn-secondary w-100">
                                    <i class="fas fa-times me-2"></i>إلغاء
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
@stop
