@php
    $isEdit = isset($achievement);
    $reqType = old('requirement_type', $formCriteria['requirement_type'] ?? '');
    $reqValue = old('requirement_value', $formCriteria['requirement_value'] ?? 1);
    $tierOptions = [
        'bronze' => 'برونزي',
        'silver' => 'فضي',
        'gold' => 'ذهبي',
        'platinum' => 'بلاتيني',
        'diamond' => 'ماسي',
    ];
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label">اسم الإنجاز <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $achievement->name ?? '') }}" required>
    </div>
    <div class="col-md-3">
        <label for="slug" class="form-label">Slug</label>
        <input type="text" class="form-control" id="slug" name="slug" value="{{ old('slug', $achievement->slug ?? '') }}" placeholder="يُولَّد تلقائياً">
    </div>
    <div class="col-md-3">
        <label for="icon" class="form-label">الأيقونة</label>
        <input type="text" class="form-control" id="icon" name="icon" value="{{ old('icon', $achievement->icon ?? '🏆') }}" placeholder="🏆">
    </div>
</div>

<div class="mt-3">
    <label for="description" class="form-label">الوصف</label>
    <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $achievement->description ?? '') }}</textarea>
</div>

<div class="row g-3 mt-1">
    <div class="col-md-4">
        <label for="tier" class="form-label">المستوى <span class="text-danger">*</span></label>
        <select class="form-select" id="tier" name="tier" required>
            @foreach ($tierOptions as $value => $label)
                <option value="{{ $value }}" @selected(old('tier', $achievement->tier ?? 'bronze') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label for="requirement_type" class="form-label">نوع المتطلب <span class="text-danger">*</span></label>
        <select class="form-select" id="requirement_type" name="requirement_type" required>
            @foreach ($requirementTypes as $value => $label)
                <option value="{{ $value }}" @selected($reqType === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label for="requirement_value" class="form-label">قيمة المتطلب <span class="text-danger">*</span></label>
        <input type="number" class="form-control" id="requirement_value" name="requirement_value" value="{{ $reqValue }}" min="1" required>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-md-4">
        <label for="points_reward" class="form-label">مكافأة النقاط</label>
        <input type="number" class="form-control" id="points_reward" name="points_reward" value="{{ old('points_reward', $achievement->points_reward ?? 0) }}" min="0">
    </div>
    <div class="col-md-4">
        <label for="badge_id" class="form-label">شارة مرتبطة</label>
        <select class="form-select" id="badge_id" name="badge_id">
            <option value="">— بدون —</option>
            @foreach ($badges as $badge)
                <option value="{{ $badge->id }}" @selected(old('badge_id', $achievement->badge_id ?? '') == $badge->id)>{{ $badge->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label for="sort_order" class="form-label">ترتيب العرض</label>
        <input type="number" class="form-control" id="sort_order" name="sort_order" value="{{ old('sort_order', $achievement->sort_order ?? 0) }}">
    </div>
</div>

<div class="mt-3">
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $achievement->is_active ?? true))>
        <label class="form-check-label" for="is_active">نشط ومتاح للطلاب</label>
    </div>
</div>
