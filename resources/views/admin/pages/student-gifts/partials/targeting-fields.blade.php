@php
    $targetType = old('target_type', $gift->target_type ?? 'single');
    $payload = $gift->target_payload ?? [];
@endphp

<div class="mb-4">
    <label class="form-label d-block">نوع الاستهداف <span class="text-danger">*</span></label>
    @foreach([
        'single' => 'طالب واحد',
        'multiple' => 'عدة طلاب',
        'group' => 'مجموعة كاملة',
        'course' => 'كورس كامل',
        'course_group' => 'كورس + مجموعة',
    ] as $value => $label)
        <div class="form-check form-check-inline">
            <input class="form-check-input target-type-radio" type="radio" name="target_type" id="target_{{ $value }}" value="{{ $value }}" {{ $targetType === $value ? 'checked' : '' }}>
            <label class="form-check-label" for="target_{{ $value }}">{{ $label }}</label>
        </div>
    @endforeach
</div>

<div class="gift-targeting-select2 target-panel mb-3 {{ $targetType === 'single' ? '' : 'd-none' }}" data-target="single">
    <label for="user_id" class="form-label">الطالب</label>
    <select name="user_id" id="user_id" class="form-select student-search-select" style="width:100%"></select>
</div>

<div class="gift-targeting-select2 target-panel mb-3 {{ $targetType === 'multiple' ? '' : 'd-none' }}" data-target="multiple">
    <label for="user_ids" class="form-label">الطلاب</label>
    <select name="user_ids[]" id="user_ids" class="form-select student-search-select-multiple" multiple style="width:100%"></select>
</div>

<div class="target-panel mb-3 {{ $targetType === 'group' ? '' : 'd-none' }}" data-target="group">
    <label for="group_id_only" class="form-label">المجموعة</label>
    <select name="group_id" id="group_id_only" class="form-select">
        <option value="">اختر المجموعة</option>
        @foreach($groups as $group)
            <option value="{{ $group->id }}" {{ old('group_id', $payload['group_id'] ?? '') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
        @endforeach
    </select>
</div>

<div class="target-panel mb-3 {{ $targetType === 'course' ? '' : 'd-none' }}" data-target="course">
    <label for="course_id_only" class="form-label">الكورس</label>
    <select name="course_id" id="course_id_only" class="form-select">
        <option value="">اختر الكورس</option>
        @foreach($courses as $course)
            <option value="{{ $course->id }}" {{ old('course_id', $payload['course_id'] ?? '') == $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
        @endforeach
    </select>
</div>

<div class="target-panel mb-3 {{ $targetType === 'course_group' ? '' : 'd-none' }}" data-target="course_group">
    <label for="course_id_grouped" class="form-label">الكورس</label>
    <select id="course_id_grouped" class="form-select mb-2">
        <option value="">اختر الكورس</option>
        @foreach($courses as $course)
            <option value="{{ $course->id }}" {{ old('course_id', $payload['course_id'] ?? '') == $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
        @endforeach
    </select>
    <label for="group_id_course" class="form-label">المجموعة</label>
    <select name="group_id" id="group_id_course" class="form-select">
        <option value="">اختر المجموعة</option>
        @foreach($groups as $group)
            <option value="{{ $group->id }}"
                data-course-ids="{{ $group->courses->pluck('id')->join(',') }}"
                {{ old('group_id', $payload['group_id'] ?? '') == $group->id ? 'selected' : '' }}
                {{ $targetType === 'course_group' ? '' : 'disabled hidden' }}>
                {{ $group->name }}
            </option>
        @endforeach
    </select>
    <input type="hidden" name="course_id" id="course_id_hidden" value="{{ old('course_id', $payload['course_id'] ?? '') }}">
    <p class="text-muted small mt-2 d-none" id="group-empty-hint">لا توجد مجموعات مرتبطة بهذا الكورس.</p>
</div>

<div class="mb-0">
    <button type="button" class="btn btn-outline-primary btn-sm" id="preview-recipients-btn">
        <i class="fe fe-users me-1"></i>معاينة المستلمين
    </button>
    <div class="alert alert-light border mt-3 mb-0 d-none" id="preview-recipients-box">
        <i class="fe fe-info me-1 text-primary"></i>
        <span id="preview-recipients-text"></span>
    </div>
</div>
