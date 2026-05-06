@extends('admin.layouts.master')

@section('page-title', 'إنشاء تقرير أسبوعي')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="my-4">
            <h5>إنشاء تقرير أسبوعي يدوي</h5>
        </div>

        <div class="card custom-card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.weekly-reports.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">الكورس</label>
                        <select class="form-select" name="course_id" id="course_id" required>
                            <option value="">اختر الكورس</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ (string) old('course_id') === (string) $course->id ? 'selected' : '' }}>
                                    {{ $course->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">المجموعة</label>
                        <select class="form-select" name="group_id" id="group_id" required>
                            <option value="">اختر المجموعة</option>
                            @foreach($groups as $group)
                                <option
                                    value="{{ $group->id }}"
                                    data-course-ids="{{ $group->courses->pluck('id')->implode(',') }}"
                                    {{ (string) old('group_id') === (string) $group->id ? 'selected' : '' }}
                                >
                                    {{ $group->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">عنوان التقرير</label>
                        <input class="form-control" type="text" name="report_title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الموعد النهائي</label>
                        <input class="form-control" type="datetime-local" name="due_at">
                    </div>
                    <button class="btn btn-primary">إنشاء التقارير</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const courseSelect = document.getElementById('course_id');
        const groupSelect = document.getElementById('group_id');
        if (!courseSelect || !groupSelect) {
            return;
        }

        const applyGroupFilter = () => {
            const selectedCourseId = courseSelect.value;
            const options = Array.from(groupSelect.options);
            let hasVisibleOptions = false;

            options.forEach((option, index) => {
                if (index === 0) {
                    option.hidden = false;
                    return;
                }

                const courseIds = (option.dataset.courseIds || '')
                    .split(',')
                    .map((id) => id.trim())
                    .filter(Boolean);

                const isVisible = selectedCourseId && courseIds.includes(selectedCourseId);
                option.hidden = !isVisible;

                if (isVisible) {
                    hasVisibleOptions = true;
                } else if (option.selected) {
                    option.selected = false;
                }
            });

            if (!hasVisibleOptions) {
                groupSelect.value = '';
            }
        };

        courseSelect.addEventListener('change', applyGroupFilter);
        applyGroupFilter();
    })();
</script>
@endpush

