@php
    $selectedCourses = old('course_ids', isset($simulator) ? $simulator->courses->pluck('id')->all() : []);
    $defaultSpec = [
        'meta' => [
            'topic_key' => old('topic_key', $simulator->topic_key ?? 'php.arrays'),
            'title' => old('title', $simulator->title ?? ''),
            'languages' => ['php', 'javascript'],
            'level' => 'beginner',
        ],
        'sections' => [
            ['type' => 'hero', 'title' => 'عنوان المحاكاة', 'summary' => 'ملخص قصير'],
        ],
    ];
    $specJson = old('spec_json', isset($simulator) ? json_encode($simulator->spec_json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : json_encode($defaultSpec, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<form action="{{ $action }}" method="POST">
    @csrf
    @if($method !== 'POST') @method($method) @endif

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">العنوان</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $simulator->title ?? '') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-control" value="{{ old('slug', $simulator->slug ?? '') }}" placeholder="يُولَّد تلقائياً إن تُرك فارغاً">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">الموضوع</label>
                    <select name="topic_key" class="form-select" required>
                        @foreach($topics as $group => $items)
                            <optgroup label="{{ $group }}">
                                @foreach($items as $key => $label)
                                    <option value="{{ $key }}" @selected(old('topic_key', $simulator->topic_key ?? '') === $key)>{{ $label }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">الحالة</label>
                    <select name="status" class="form-select" required>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" @selected(old('status', $simulator->status ?? 'draft') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">الوصف</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description', $simulator->description ?? '') }}</textarea>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">ربط بالكورسات</label>
                    <select name="course_ids[]" class="form-select" multiple size="5">
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" @selected(in_array($course->id, $selectedCourses))>{{ $course->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header"><strong>Spec JSON</strong></div>
        <div class="card-body">
            <textarea name="spec_json" class="form-control font-monospace" rows="24" required dir="ltr" style="text-align:left;">{{ $specJson }}</textarea>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">حفظ</button>
</form>
