@php
    $selectedCourseIds = old('course_ids', isset($group)
        ? $group->courses->pluck('id')->toArray()
        : (isset($course) ? [$course->id] : []));
@endphp

<div class="admin-group-course-picker" id="groupCoursePicker">
    @foreach($courses as $courseItem)
        @php
            $isSelected = in_array($courseItem->id, $selectedCourseIds);
            if (isset($group)) {
                $pivotData = $group->courses->where('id', $courseItem->id)->first();
                $isVisible = old("course_visibility.{$courseItem->id}") !== null
                    ? (bool) old("course_visibility.{$courseItem->id}")
                    : ($pivotData ? (bool) ($pivotData->pivot->is_visible ?? true) : true);
            } else {
                $isVisible = old("course_visibility.{$courseItem->id}") !== null
                    ? (bool) old("course_visibility.{$courseItem->id}")
                    : true;
            }
            $isPrimary = isset($course) && $courseItem->id == $course->id;
        @endphp
        <div class="admin-group-course-item {{ $isSelected ? 'is-selected' : '' }}" data-course-id="{{ $courseItem->id }}">
            <label class="admin-group-course-item__main" for="course_{{ $courseItem->id }}">
                <input class="admin-group-course-item__checkbox course-checkbox"
                       type="checkbox"
                       name="course_ids[]"
                       value="{{ $courseItem->id }}"
                       id="course_{{ $courseItem->id }}"
                       {{ $isSelected ? 'checked' : '' }}
                       onchange="toggleCourseVisibility({{ $courseItem->id }})">
                <span class="admin-group-course-item__icon">
                    <i class="fe fe-book-open"></i>
                </span>
                <span class="admin-group-course-item__text">
                    <span class="admin-group-course-item__title">{{ $courseItem->title }}</span>
                    @if($courseItem->code)
                        <span class="admin-group-course-item__code">{{ $courseItem->code }}</span>
                    @endif
                </span>
                @if($isPrimary)
                    <span class="group-show-chip group-show-chip--sm text-primary">الكورس الحالي</span>
                @endif
            </label>
            <div class="admin-group-course-item__visibility" style="{{ $isSelected ? '' : 'display:none;' }}">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input"
                           type="checkbox"
                           name="course_visibility[{{ $courseItem->id }}]"
                           value="1"
                           id="visibility_{{ $courseItem->id }}"
                           {{ $isVisible ? 'checked' : '' }}>
                    <label class="form-check-label fs-12" for="visibility_{{ $courseItem->id }}">
                        <i class="fe fe-eye me-1"></i>ظاهر للطلاب
                    </label>
                </div>
            </div>
        </div>
    @endforeach
</div>

<p class="admin-group-form-hint mb-0 mt-3">
    <i class="fe fe-info me-1"></i>
    حدد الكورسات المرتبطة بهذه المجموعة. يمكنك تحديد ما إذا كان كل كورس ظاهراً أم مخفياً للطلاب.
</p>
@error('course_ids')
    <div class="text-danger fs-12 mt-2">{{ $message }}</div>
@enderror
