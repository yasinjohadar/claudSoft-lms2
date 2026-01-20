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
                            <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                                @foreach($courses as $courseItem)
                                    @php
                                        $isSelected = old('course_ids') 
                                            ? in_array($courseItem->id, old('course_ids')) 
                                            : $group->courses->contains($courseItem->id);
                                        $pivotData = $group->courses->where('id', $courseItem->id)->first();
                                        $isVisible = old("course_visibility.{$courseItem->id}") !== null 
                                            ? old("course_visibility.{$courseItem->id}") 
                                            : ($pivotData ? $pivotData->pivot->is_visible ?? true : true);
                                    @endphp
                                    <div class="form-check mb-3 p-2 border-bottom">
                                        <div class="d-flex align-items-center">
                                            <input class="form-check-input course-checkbox" 
                                                   type="checkbox" 
                                                   name="course_ids[]" 
                                                   value="{{ $courseItem->id }}" 
                                                   id="course_{{ $courseItem->id }}"
                                                   {{ $isSelected ? 'checked' : '' }}
                                                   onchange="toggleCourseVisibility({{ $courseItem->id }})">
                                            <label class="form-check-label flex-grow-1 ms-2" for="course_{{ $courseItem->id }}">
                                                <strong>{{ $courseItem->title }}</strong>
                                                @if($courseItem->code)
                                                    <span class="text-muted">({{ $courseItem->code }})</span>
                                                @endif
                                            </label>
                                        </div>
                                        @if($isSelected)
                                            <div class="mt-2 ms-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           name="course_visibility[{{ $courseItem->id }}]" 
                                                           value="1"
                                                           id="visibility_{{ $courseItem->id }}"
                                                           {{ $isVisible ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="visibility_{{ $courseItem->id }}">
                                                        <i class="fas fa-eye me-1"></i>ظاهر للطلاب
                                                    </label>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                حدد الكورسات المرتبطة بهذه المجموعة. يمكنك تحديد ما إذا كان كل كورس ظاهراً أم مخفياً للطلاب.
                            </small>
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

                        <!-- Allow Membership Requests -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="allow_membership_requests" id="allow_membership_requests"
                                       {{ old('allow_membership_requests', $group->allow_membership_requests ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="allow_membership_requests">
                                    <i class="fas fa-user-plus me-2"></i>
                                    <strong>تفعيل طلب الانضمام</strong>
                                </label>
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                عند تفعيل هذه الخيار، يمكن للطلاب طلب الانضمام لهذه المجموعة. يجب أن تكون المجموعة نشطة وظاهرة حتى يعمل هذا الخيار.
                            </small>
                        </div>

                        <!-- Visible for Students -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_visible_for_students" id="is_visible_for_students"
                                       {{ old('is_visible_for_students', $group->is_visible_for_students ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_visible_for_students">
                                    <i class="fas fa-eye me-2"></i>
                                    <strong>إظهار المجموعة للطلاب</strong>
                                </label>
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                عند تفعيل هذا الخيار، ستكون المجموعة ظاهرة للطلاب. يمكن تحديد شروط إضافية أدناه.
                            </small>
                        </div>

                        <!-- Visibility Requirements -->
                        <div class="mb-4">
                            <label class="form-label">المجموعات المطلوبة للظهور (اختياري)</label>
                            <select name="visibility_required_groups[]" 
                                    class="form-select @error('visibility_required_groups') is-invalid @enderror" 
                                    multiple
                                    size="5">
                                @php
                                    $selectedRequiredGroups = old('visibility_required_groups', $group->visibilityRequirements->pluck('required_group_id')->toArray());
                                @endphp
                                @foreach(\App\Models\CourseGroup::where('id', '!=', $group->id)->get() as $otherGroup)
                                    <option value="{{ $otherGroup->id }}" 
                                            {{ in_array($otherGroup->id, $selectedRequiredGroups) ? 'selected' : '' }}>
                                        {{ $otherGroup->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>مطلوب:</strong> حدد المجموعات التي يجب أن يكون الطالب عضواً فيها لرؤية هذه المجموعة. إذا لم تحدد أي مجموعة، لن تظهر المجموعة لأي طالب.
                            </small>
                            @error('visibility_required_groups')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
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

@section('script')
<script>
    function toggleCourseVisibility(courseId) {
        const checkbox = document.getElementById('course_' + courseId);
        const formCheck = checkbox.closest('.form-check');
        let visibilityDiv = formCheck.querySelector('.mt-2');
        
        if (checkbox.checked) {
            if (!visibilityDiv) {
                const newDiv = document.createElement('div');
                newDiv.className = 'mt-2 ms-4';
                newDiv.innerHTML = `
                    <div class="form-check form-switch">
                        <input class="form-check-input" 
                               type="checkbox" 
                               name="course_visibility[${courseId}]" 
                               value="1"
                               id="visibility_${courseId}"
                               checked>
                        <label class="form-check-label" for="visibility_${courseId}">
                            <i class="fas fa-eye me-1"></i>ظاهر للطلاب
                        </label>
                    </div>
                `;
                formCheck.appendChild(newDiv);
            } else {
                visibilityDiv.style.display = 'block';
            }
        } else {
            if (visibilityDiv) {
                visibilityDiv.style.display = 'none';
                const visibilityCheckbox = visibilityDiv.querySelector('input[type="checkbox"]');
                if (visibilityCheckbox) {
                    visibilityCheckbox.checked = false;
                }
            }
        }
    }
</script>
@stop
