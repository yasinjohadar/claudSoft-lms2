@extends('student.layouts.master')

@section('page-title')
تقرير أسبوعي
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">{{ $report->report_title }}</h5>
                <p class="text-muted mb-0">الموعد النهائي: {{ $report->due_at?->format('Y-m-d H:i') ?? 'غير محدد' }}</p>
            </div>
        </div>

        @if($report->status === \App\Models\StudentWeeklyReport::STATUS_CLOSED)
            <div class="alert alert-danger">هذا التقرير مغلق لانتهاء الموعد النهائي.</div>
        @endif

        @if($report->admin_feedback)
            <div class="alert alert-info">
                <strong>تعليق الأدمن:</strong><br>
                {{ $report->admin_feedback }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card custom-card">
            <div class="card-body">
                <form id="weekly-report-form" method="POST" action="{{ route('student.weekly-reports.save', $report) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">التقرير التفصيلي</label>
                        <textarea name="student_details" class="form-control" rows="5" @disabled($report->status === \App\Models\StudentWeeklyReport::STATUS_CLOSED)>{{ old('student_details', $report->student_details) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ملاحظات إضافية</label>
                        <textarea name="student_notes" class="form-control" rows="4" @disabled($report->status === \App\Models\StudentWeeklyReport::STATUS_CLOSED)>{{ old('student_notes', $report->student_notes) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">الدروس التي تمت دراستها</label>
                        <div id="lesson-selections">
                            @php $selected = $report->selectedLessons; @endphp
                            @forelse($selected as $idx => $sel)
                                <div class="row g-2 mb-2 lesson-row">
                                    <div class="col-md-6">
                                        <select class="form-select course-select" name="lessons[{{ $idx }}][course_id]" @disabled($report->status === \App\Models\StudentWeeklyReport::STATUS_CLOSED)>
                                            <option value="">اختر الكورس</option>
                                            @foreach($courses as $course)
                                                <option value="{{ $course->id }}" @selected($course->id === $sel->course_id)>{{ $course->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <select class="form-select lesson-select" name="lessons[{{ $idx }}][module_id]" @disabled($report->status === \App\Models\StudentWeeklyReport::STATUS_CLOSED)>
                                            <option value="{{ $sel->module_id }}">{{ $sel->module->title ?? $sel->lesson->title ?? 'الدرس الحالي' }}</option>
                                        </select>
                                    </div>
                                </div>
                            @empty
                                <div class="row g-2 mb-2 lesson-row">
                                    <div class="col-md-6">
                                        <select class="form-select course-select" name="lessons[0][course_id]" @disabled($report->status === \App\Models\StudentWeeklyReport::STATUS_CLOSED)>
                                            <option value="">اختر الكورس</option>
                                            @foreach($courses as $course)
                                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <select class="form-select lesson-select" name="lessons[0][module_id]" @disabled($report->status === \App\Models\StudentWeeklyReport::STATUS_CLOSED)>
                                            <option value="">اختر الدرس</option>
                                        </select>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                        @if($report->status !== \App\Models\StudentWeeklyReport::STATUS_CLOSED)
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="add-lesson-row">إضافة درس آخر</button>
                        @endif
                    </div>

                    @if($report->status !== \App\Models\StudentWeeklyReport::STATUS_CLOSED)
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">حفظ مسودة</button>
                            <button type="button" class="btn btn-success" id="submit-report-btn">إرسال التقرير</button>
                        </div>
                    @endif
                </form>

                <form id="weekly-report-submit-form" method="POST" action="{{ route('student.weekly-reports.submit', $report) }}" class="d-none">
                    @csrf
                    @method('PUT')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const lessonsUrlTemplate = @json(route('student.weekly-reports.lessons', ['course' => '__COURSE_ID__']));
    const container = document.getElementById('lesson-selections');
    const addBtn = document.getElementById('add-lesson-row');
    const saveForm = document.getElementById('weekly-report-form');
    const submitForm = document.getElementById('weekly-report-submit-form');
    const submitBtn = document.getElementById('submit-report-btn');
    let rowIndex = container.querySelectorAll('.lesson-row').length;

    function bindCourseSelect(selectEl) {
        selectEl.addEventListener('change', async () => {
            const row = selectEl.closest('.lesson-row');
            const lessonSelect = row.querySelector('.lesson-select');
            const courseId = selectEl.value;
            lessonSelect.innerHTML = '<option value="">اختر الدرس</option>';
            if (!courseId) return;

            const url = lessonsUrlTemplate.replace('__COURSE_ID__', courseId);
            try {
                const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                if (!response.ok) {
                    lessonSelect.innerHTML = '<option value="">تعذر تحميل الدروس</option>';
                    return;
                }
                const lessons = await response.json();
                if (!Array.isArray(lessons) || lessons.length === 0) {
                    lessonSelect.innerHTML = '<option value="">لا توجد دروس متاحة</option>';
                    return;
                }
                lessons.forEach((lesson) => {
                    const opt = document.createElement('option');
                    opt.value = lesson.id;
                    opt.textContent = lesson.title;
                    lessonSelect.appendChild(opt);
                });
            } catch (e) {
                lessonSelect.innerHTML = '<option value="">تعذر تحميل الدروس</option>';
            }
        });
    }

    container.querySelectorAll('.course-select').forEach(bindCourseSelect);

    if (addBtn) {
        addBtn.addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'row g-2 mb-2 lesson-row';
            row.innerHTML = `
                <div class="col-md-6">
                    <select class="form-select course-select" name="lessons[${rowIndex}][course_id]">
                        <option value="">اختر الكورس</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <select class="form-select lesson-select" name="lessons[${rowIndex}][module_id]">
                        <option value="">اختر الدرس</option>
                    </select>
                </div>
            `;
            container.appendChild(row);
            bindCourseSelect(row.querySelector('.course-select'));
            rowIndex++;
        });
    }

    if (submitBtn) {
        submitBtn.addEventListener('click', () => {
            const formData = new FormData(saveForm);
            for (const [key, value] of formData.entries()) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                submitForm.appendChild(input);
            }
            submitForm.submit();
        });
    }
})();
</script>
@endpush

