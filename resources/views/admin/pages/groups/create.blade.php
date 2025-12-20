@extends('admin.layouts.master')

@section('page-title')
    إنشاء مجموعة جديدة - {{ $course->title }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إنشاء مجموعة جديدة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.show', $course->id) }}">{{ $course->title }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.groups.index', $course->id) }}">المجموعات</a></li>
                            <li class="breadcrumb-item active">إنشاء مجموعة</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Main Card -->
            <div class="card custom-card">
                <div class="card-header">
                    <h6 class="card-title mb-0">معلومات المجموعة</h6>
                </div>

                <form action="{{ route('courses.groups.store', $course->id) }}" method="POST">
                    @csrf

                    <div class="card-body">
                        <!-- Group Name -->
                        <div class="mb-4">
                            <label class="form-label required">اسم المجموعة</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label class="form-label">الوصف</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                      rows="4">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Select Courses -->
                        <div class="mb-4">
                            <label class="form-label required">الكورسات المرتبطة</label>
                            <select name="course_ids[]" class="form-select @error('course_ids') is-invalid @enderror"
                                    multiple size="8" required>
                                @foreach($courses as $courseItem)
                                    <option value="{{ $courseItem->id }}"
                                            {{ (old('course_ids') && in_array($courseItem->id, old('course_ids'))) || $courseItem->id == $course->id ? 'selected' : '' }}>
                                        {{ $courseItem->title }}
                                        @if($courseItem->code)({{ $courseItem->code }})@endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">اضغط Ctrl/Cmd واضغط على الكورسات لتحديد عدة كورسات</small>
                            @error('course_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Active Status -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    المجموعة نشطة
                                </label>
                            </div>
                        </div>

                        <!-- Visibility -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_visible" id="is_visible"
                                       {{ old('is_visible', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_visible">
                                    مرئية للطلاب
                                </label>
                            </div>
                        </div>

                        <!-- Max Members -->
                        <div class="mb-4">
                            <label class="form-label">الحد الأقصى للأعضاء (اختياري)</label>
                            <input type="number" name="max_members" class="form-control @error('max_members') is-invalid @enderror"
                                   value="{{ old('max_members') }}" min="1">
                            <small class="text-muted">اترك فارغاً لعدم وجود حد أقصى</small>
                            @error('max_members')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('courses.groups.index', $course->id) }}" class="btn btn-light">
                                <i class="fas fa-arrow-right me-2"></i>رجوع
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>إنشاء المجموعة
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
@stop
