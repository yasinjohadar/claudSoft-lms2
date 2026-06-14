<div class="row g-3 mb-4">
    <div class="col-md-6">
        <label for="default_course_id" class="form-label">الكورس الافتراضي</label>
        <select class="form-select" id="default_course_id" name="default_course_id">
            <option value="">اختر الكورس (اختياري)</option>
            @foreach($courses as $course)
                <option value="{{ $course->id }}">{{ $course->title }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label for="default_programming_language_id" class="form-label">اللغة البرمجية الافتراضية</label>
        <select class="form-select" id="default_programming_language_id" name="default_programming_language_id">
            <option value="">اختر اللغة البرمجية (اختياري)</option>
            @foreach($programmingLanguages as $lang)
                <option value="{{ $lang->id }}">{{ $lang->display_name ?? $lang->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <p class="form-text mb-0">
            <i class="fe fe-info me-1"></i>
            تُستخدم هذه القيم عند غيابها في الملف. إن وُجدت في الملف تُطبَّق قيمة الملف.
        </p>
    </div>
</div>
