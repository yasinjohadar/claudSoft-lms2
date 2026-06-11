@php
    $leaderboard = $leaderboard ?? null;
    $catalog = app(\App\Services\Gamification\LeaderboardCatalog::class);
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">اسم اللوحة <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $leaderboard?->name) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Slug</label>
        <input type="text" name="slug" class="form-control" value="{{ old('slug', $leaderboard?->slug) }}" placeholder="يُولّد تلقائياً">
    </div>
    <div class="col-12">
        <label class="form-label">الوصف</label>
        <textarea name="description" class="form-control" rows="2">{{ old('description', $leaderboard?->description) }}</textarea>
    </div>
    <div class="col-md-4">
        <label class="form-label">النوع <span class="text-danger">*</span></label>
        <select name="type" class="form-select" required>
            @foreach ($typeOptions as $value => $label)
                <option value="{{ $value }}" {{ old('type', $leaderboard?->type) === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">المقياس <span class="text-danger">*</span></label>
        <select name="metric" class="form-select" required>
            @foreach ($metricOptions as $value => $label)
                <option value="{{ $value }}" {{ old('metric', $leaderboard?->metric ?? 'total_points') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">الفترة <span class="text-danger">*</span></label>
        <select name="period" class="form-select" required>
            @foreach ($periodOptions as $value => $label)
                <option value="{{ $value }}" {{ old('period', $leaderboard?->period ?? 'all_time') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">الكورس (اختياري)</label>
        <select name="course_id" class="form-select">
            <option value="">—</option>
            @foreach ($courses as $course)
                <option value="{{ $course->id }}" {{ old('course_id', $leaderboard?->course_id) == $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">الأيقونة</label>
        <input type="text" name="icon" class="form-control" value="{{ old('icon', $leaderboard?->icon ?? '🏆') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">ترتيب العرض</label>
        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $leaderboard?->sort_order ?? 0) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">أقصى عدد</label>
        <input type="number" name="max_entries" class="form-control" value="{{ old('max_entries', $leaderboard?->max_entries ?? 100) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">حد أدنى للظهور</label>
        <input type="number" name="min_score" class="form-control" value="{{ old('min_score', $leaderboard?->min_score ?? 0) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">من تاريخ</label>
        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', optional($leaderboard?->start_date)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">إلى تاريخ</label>
        <input type="date" name="end_date" class="form-control" value="{{ old('end_date', optional($leaderboard?->end_date)->format('Y-m-d')) }}">
    </div>
    <div class="col-12">
        <label class="form-label">مكافآت (JSON)</label>
        <textarea name="rewards_json" class="form-control font-monospace" rows="4" placeholder='{"1":{"points":5000},"top_10":{"points":500}}'>{{ old('rewards_json', $leaderboard?->rewards ? json_encode($leaderboard->rewards, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : '') }}</textarea>
        <small class="text-muted">مثال: المركز 1، 2، 3 أو top_10</small>
    </div>
    <div class="col-md-4">
        <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" name="has_divisions" id="has_divisions" value="1" {{ old('has_divisions', $leaderboard?->has_divisions ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="has_divisions">تفعيل الفئات (Divisions)</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $leaderboard?->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">نشطة</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" name="is_visible" id="is_visible" value="1" {{ old('is_visible', $leaderboard?->is_visible ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_visible">مرئية للطلاب</label>
        </div>
    </div>
</div>
