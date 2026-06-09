@php
    $isEdit = isset($badge);
    $requirementType = old('requirement_type', $requirementType ?? '');
    $requirementValue = old('requirement_value', $requirementValue ?? '');
@endphp

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="name" class="form-label">اسم الشارة <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $badge->name ?? '') }}" required>
    </div>

    <div class="col-md-6 mb-3">
        <label for="slug" class="form-label">الاسم المختصر (Slug)</label>
        <input type="text" class="form-control" id="slug" name="slug" value="{{ old('slug', $badge->slug ?? '') }}">
    </div>
</div>

<div class="mb-3">
    <label for="description" class="form-label">الوصف <span class="text-danger">*</span></label>
    <textarea class="form-control" id="description" name="description" rows="3" required>{{ old('description', $badge->description ?? '') }}</textarea>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="icon" class="form-label">الأيقونة (Emoji)</label>
        <input type="text" class="form-control" id="icon" name="icon" value="{{ old('icon', $badge->icon ?? '') }}" placeholder="🏅">
    </div>

    <div class="col-md-4 mb-3">
        <label for="type" class="form-label">النوع <span class="text-danger">*</span></label>
        <select class="form-select" id="type" name="type" required>
            @foreach (['progress' => 'تقدم', 'achievement' => 'إنجاز', 'performance' => 'أداء', 'engagement' => 'تفاعل', 'special' => 'خاص', 'event' => 'حدث', 'social' => 'اجتماعي'] as $value => $label)
                <option value="{{ $value }}" {{ old('type', $badge->type ?? 'progress') == $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4 mb-3">
        <label for="rarity" class="form-label">الندرة <span class="text-danger">*</span></label>
        <select class="form-select" id="rarity" name="rarity" required>
            @foreach (['common' => 'عادي', 'rare' => 'نادر', 'epic' => 'ملحمي', 'legendary' => 'أسطوري', 'mythic' => 'أسطوري+'] as $value => $label)
                <option value="{{ $value }}" {{ old('rarity', $badge->rarity ?? 'common') == $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="category" class="form-label">الفئة</label>
        <input type="text" class="form-control" id="category" name="category" value="{{ old('category', $badge->category ?? '') }}" placeholder="lessons, courses, streak...">
    </div>

    <div class="col-md-4 mb-3">
        <label for="points_reward" class="form-label">مكافأة النقاط</label>
        <input type="number" class="form-control" id="points_reward" name="points_reward" value="{{ old('points_reward', $badge->points_value ?? 0) }}" min="0">
    </div>

    <div class="col-md-4 mb-3">
        <label for="sort_order" class="form-label">ترتيب العرض</label>
        <input type="number" class="form-control" id="sort_order" name="sort_order" value="{{ old('sort_order', $badge->sort_order ?? 0) }}" min="0">
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="requirement_type" class="form-label">نوع المتطلب</label>
        <select class="form-select" id="requirement_type" name="requirement_type">
            <option value="">شارة يدوية (بدون معيار تلقائي)</option>
            <option value="lessons_completed" {{ $requirementType == 'lessons_completed' ? 'selected' : '' }}>دروس مكتملة</option>
            <option value="quizzes_passed" {{ $requirementType == 'quizzes_passed' ? 'selected' : '' }}>اختبارات مكتملة</option>
            <option value="points_earned" {{ $requirementType == 'points_earned' ? 'selected' : '' }}>نقاط مكتسبة</option>
            <option value="streak_days" {{ $requirementType == 'streak_days' ? 'selected' : '' }}>أيام متتالية</option>
            <option value="courses_completed" {{ $requirementType == 'courses_completed' ? 'selected' : '' }}>كورسات مكتملة</option>
        </select>
    </div>

    <div class="col-md-6 mb-3">
        <label for="requirement_value" class="form-label">قيمة المتطلب</label>
        <input type="number" class="form-control" id="requirement_value" name="requirement_value" value="{{ $requirementValue }}" min="0">
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $badge->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">نشط</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="is_visible" name="is_visible" value="1" {{ old('is_visible', $badge->is_visible ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_visible">ظاهر للطلاب</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="is_hidden" name="is_hidden" value="1" {{ old('is_hidden', $badge->is_hidden ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_hidden">شارة مخفية حتى المنح</label>
        </div>
    </div>
</div>
