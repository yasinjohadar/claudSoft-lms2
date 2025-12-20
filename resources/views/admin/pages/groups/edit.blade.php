@extends('admin.layouts.master')

@section('page-title')
    تعديل المجموعة - {{ $group->name }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تعديل المجموعة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.show', $course->id) }}">{{ $course->title }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.groups.index', $course->id) }}">المجموعات</a></li>
                            <li class="breadcrumb-item active">تعديل المجموعة</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Main Card -->
            <div class="card custom-card">
                <div class="card-header">
                    <h6 class="card-title mb-0">معلومات المجموعة</h6>
                </div>

                <form action="{{ route('courses.groups.update', [$course->id, $group->id]) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        <!-- Group Name -->
                        <div class="mb-4">
                            <label class="form-label required">اسم المجموعة</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $group->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label class="form-label">الوصف</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                      rows="4">{{ old('description', $group->description) }}</textarea>
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
                                            {{ (old('course_ids') ? in_array($courseItem->id, old('course_ids')) : $group->courses->contains($courseItem->id)) ? 'selected' : '' }}>
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
                                       {{ old('is_active', $group->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    المجموعة نشطة
                                </label>
                            </div>
                        </div>

                        <!-- Visibility -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_visible" id="is_visible"
                                       {{ old('is_visible', $group->is_visible) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_visible">
                                    مرئية للطلاب
                                </label>
                            </div>
                        </div>

                        <!-- Max Members -->
                        <div class="mb-4">
                            <label class="form-label">الحد الأقصى للأعضاء (اختياري)</label>
                            <input type="number" name="max_members" class="form-control @error('max_members') is-invalid @enderror"
                                   value="{{ old('max_members', $group->max_members) }}" min="1">
                            <small class="text-muted">اترك فارغاً لعدم وجود حد أقصى</small>
                            @error('max_members')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Current Members Count -->
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>عدد الأعضاء الحاليين:</strong> {{ $group->members_count ?? 0 }}
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('courses.groups.index', $course->id) }}" class="btn btn-light">
                                <i class="fas fa-arrow-right me-2"></i>رجوع
                            </a>
                            <div>
                                <a href="{{ route('courses.groups.show', [$course->id, $group->id]) }}" class="btn btn-secondary me-2">
                                    <i class="fas fa-eye me-2"></i>عرض التفاصيل
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>حفظ التعديلات
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
@stop
