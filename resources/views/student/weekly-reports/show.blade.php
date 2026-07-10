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
            <div class="d-flex gap-2 align-items-center">
                @if($report->status === \App\Models\StudentWeeklyReport::STATUS_REVIEWED)
                    <span class="badge bg-success-transparent text-success">تمت المراجعة</span>
                @elseif($report->status === \App\Models\StudentWeeklyReport::STATUS_SUBMITTED)
                    <span class="badge bg-info-transparent text-info">مرسل</span>
                @elseif($report->status === \App\Models\StudentWeeklyReport::STATUS_CLOSED)
                    <span class="badge bg-danger-transparent text-danger">مغلق</span>
                @else
                    <span class="badge bg-warning-transparent text-warning">مسودة</span>
                @endif
            </div>
        </div>

        @if(!$canEdit && $report->status === \App\Models\StudentWeeklyReport::STATUS_CLOSED)
            <div class="alert alert-danger">هذا التقرير مغلق لانتهاء الموعد النهائي.</div>
        @endif

        @if($wasSubmitted && $canEdit === false && $report->status !== \App\Models\StudentWeeklyReport::STATUS_CLOSED)
            <div class="alert alert-info d-flex align-items-center gap-2">
                <i class="ri-information-line fs-18"></i>
                <span>تم إرسال هذا التقرير ولا يمكن تعديله أو إرساله مرة أخرى.</span>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        @if($errors->has('report'))
            <div class="alert alert-danger">{{ $errors->first('report') }}</div>
        @endif

        @if($report->admin_feedback)
            @include('student.weekly-reports.partials.admin-feedback-card')
        @endif

        <div class="card custom-card">
            <div class="card-body">
                <form id="weekly-report-form" method="POST" action="{{ route('student.weekly-reports.save', $report) }}">
                    @csrf
                    @method('PUT')
                    <div id="flattened-lessons-container"></div>

                    <div class="mb-3">
                        <label class="form-label" for="student-details-editor">التقرير التفصيلي</label>
                        @if($canEdit)
                            <div class="weekly-report-editor-wrap">
                                <textarea name="student_details" id="student-details-editor" class="form-control" rows="8">{{ old('student_details', $report->student_details) }}</textarea>
                            </div>
                        @else
                            <div class="p-3 rounded border bg-light weekly-report-html-content">
                                {!! $report->student_details ?: '<p class="text-muted mb-0">لا يوجد</p>' !!}
                            </div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="student_notes">ملاحظات إضافية</label>
                        @if($canEdit)
                            <textarea name="student_notes" id="student_notes" class="form-control" rows="4">{{ old('student_notes', $report->student_notes) }}</textarea>
                        @else
                            <div class="p-3 rounded border bg-light">
                                {{ $report->student_notes ?: 'لا يوجد' }}
                            </div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label">الدروس التي تمت دراستها</label>
                        @if($canEdit)
                            @php
                                $fixedCourse = !empty($report->target_course_id) && $courses->count() === 1
                                    ? $courses->first()
                                    : null;
                            @endphp
                            @if($fixedCourse)
                                <p class="text-muted fs-12 mb-2">حدّد الدروس التي درستها ضمن الكورس المحدد لهذا التقرير.</p>
                            @else
                                <p class="text-muted fs-12 mb-2">اختر الكورس ثم حدّد عدة دروس معاً من نفس الكورس.</p>
                            @endif
                        @endif
                        <div id="lesson-selections">
                            @if($canEdit)
                                @php
                                    $rows = $groupedSelections->isNotEmpty()
                                        ? $groupedSelections
                                        : collect([['course_id' => $fixedCourse?->id ?? ($courses->count() === 1 ? ($courses->first()?->id) : null), 'module_ids' => []]]);
                                @endphp
                                @foreach($rows as $rowIndex => $row)
                                    <div class="row g-2 mb-3 lesson-row" data-row-index="{{ $rowIndex }}">
                                        <div class="col-md-5">
                                            <label class="form-label fs-12">الكورس</label>
                                            @if($fixedCourse)
                                                <input type="hidden" class="course-id-fixed" value="{{ $fixedCourse->id }}">
                                                <div class="form-control bg-light text-body">{{ $fixedCourse->title }}</div>
                                            @else
                                                <select class="form-select course-select">
                                                    <option value="">اختر الكورس</option>
                                                    @foreach($courses as $course)
                                                        <option value="{{ $course->id }}" @selected((int) ($row['course_id'] ?? 0) === (int) $course->id)>{{ $course->title }}</option>
                                                    @endforeach
                                                </select>
                                            @endif
                                        </div>
                                        <div class="col-md-7">
                                            <label class="form-label fs-12">الدروس</label>
                                            <div class="weekly-report-module-select-wrap">
                                                <select class="form-select module-multi-select weekly-report-module-select" multiple data-selected-modules='@json($row['module_ids'] ?? [])'>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                @include('admin.weekly-reports.partials.selected-lessons-grouped', ['selectedLessonGroups' => $selectedLessonGroups ?? collect()])
                            @endif
                        </div>
                        @if($canEdit && !$fixedCourse && $courses->count() > 1)
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="add-course-row">
                                <i class="ri-add-line me-1"></i>إضافة كورس آخر
                            </button>
                        @endif
                    </div>

                    @if($canEdit)
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">حفظ مسودة</button>
                            <button type="button" class="btn btn-success" id="submit-report-btn">إرسال التقرير</button>
                        </div>
                    @endif
                </form>

                @if($canEdit)
                    <form id="weekly-report-submit-form" method="POST" action="{{ route('student.weekly-reports.submit', $report) }}" class="d-none">
                        @csrf
                        @method('PUT')
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if($canEdit)
    @include('student.weekly-reports.partials.tinymce-editor')
@endif
@if($canEdit)
<style>
    .weekly-report-module-select-wrap .choices__list--dropdown .choices__heading.weekly-report-section-heading {
        color: #0b5ed7;
        background: rgba(13, 110, 253, 0.1);
        border-radius: 0.35rem;
        font-weight: 700;
        font-size: 0.82rem;
        margin: 0.35rem 0.4rem 0.2rem;
        padding: 0.45rem 0.65rem;
    }

    .weekly-report-module-select-wrap .choices__list--dropdown .choices__item--choice {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .weekly-report-module-select-wrap .choices__list--dropdown .choices__item--choice.weekly-report-module-choice--completed {
        background: rgba(25, 135, 84, 0.08);
    }

    .weekly-report-module-select-wrap .weekly-report-module-completed {
        color: #198754;
        font-weight: 700;
        font-size: 0.78rem;
        margin-inline-start: auto;
        white-space: nowrap;
    }

    .weekly-report-module-select-wrap .choices__list--multiple .choices__item.weekly-report-selected-item--completed {
        background: rgba(25, 135, 84, 0.14);
        border-color: rgba(25, 135, 84, 0.35);
        color: #146c43;
    }
</style>
<script>
(() => {
    const reportId = @json($report->id);
    const fixedCourseId = @json($fixedCourse->id ?? null);
    const coursesOptionsHtml = @json($courses->map(fn ($c) => ['id' => $c->id, 'title' => $c->title])->values());
    const lessonsUrlTemplate = @json(route('student.weekly-reports.lessons', ['course' => '__COURSE_ID__'])) + '?report_id=' + reportId;

    const container = document.getElementById('lesson-selections');
    const addBtn = document.getElementById('add-course-row');
    const saveForm = document.getElementById('weekly-report-form');
    const submitForm = document.getElementById('weekly-report-submit-form');
    const submitBtn = document.getElementById('submit-report-btn');
    const flattenedContainer = document.getElementById('flattened-lessons-container');
    const rowChoices = new WeakMap();

    function syncEditor() {
        const editor = typeof tinymce !== 'undefined' ? tinymce.get('student-details-editor') : null;
        if (editor) {
            editor.save();
        }
    }

    function getEditorContent() {
        const editor = typeof tinymce !== 'undefined' ? tinymce.get('student-details-editor') : null;
        if (editor) {
            return editor.getContent();
        }
        return document.getElementById('student-details-editor')?.value || '';
    }

    function buildCoursesOptions(selectedId) {
        let html = '<option value="">اختر الكورس</option>';
        coursesOptionsHtml.forEach((course) => {
            const selected = String(course.id) === String(selectedId) ? ' selected' : '';
            html += `<option value="${course.id}"${selected}>${course.title}</option>`;
        });
        return html;
    }

    async function fetchModules(courseId) {
        const url = lessonsUrlTemplate.replace('__COURSE_ID__', courseId);
        const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!response.ok) {
            throw new Error('failed');
        }
        return response.json();
    }

    function destroyChoices(selectEl) {
        const instance = rowChoices.get(selectEl);
        if (instance) {
            instance.destroy();
            rowChoices.delete(selectEl);
        }
    }

    function buildCompletedMap(moduleGroups) {
        const completedMap = new Map();
        moduleGroups.forEach((group) => {
            (group.modules || []).forEach((module) => {
                if (module.is_completed) {
                    completedMap.set(String(module.id), true);
                }
            });
        });
        return completedMap;
    }

    function decorateWeeklyReportChoices(selectEl, completedMap) {
        const wrap = selectEl.closest('.weekly-report-module-select-wrap');
        const choicesRoot = wrap?.querySelector('.choices');
        if (!choicesRoot || !completedMap) {
            return;
        }

        choicesRoot.querySelectorAll('.choices__heading').forEach((heading) => {
            heading.classList.add('weekly-report-section-heading');
        });

        choicesRoot.querySelectorAll('.choices__list--dropdown .choices__item--choice').forEach((item) => {
            const value = String(item.dataset.value || '');
            const isCompleted = completedMap.has(value);
            item.classList.toggle('weekly-report-module-choice--completed', isCompleted);

            let badge = item.querySelector('.weekly-report-module-completed');
            if (isCompleted) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'weekly-report-module-completed';
                    badge.textContent = 'مكتمل';
                    item.appendChild(badge);
                }
            } else if (badge) {
                badge.remove();
            }
        });

        choicesRoot.querySelectorAll('.choices__list--multiple .choices__item').forEach((item) => {
            const value = String(item.dataset.value || '');
            item.classList.toggle('weekly-report-selected-item--completed', completedMap.has(value));
        });
    }

    function initModuleChoices(selectEl, moduleGroups, selectedIds) {
        destroyChoices(selectEl);
        selectEl.innerHTML = '';

        const completedMap = buildCompletedMap(moduleGroups);

        moduleGroups.forEach((group) => {
            const optgroup = document.createElement('optgroup');
            optgroup.label = group.section_title || 'بدون قسم';

            (group.modules || []).forEach((module) => {
                const opt = document.createElement('option');
                opt.value = module.id;
                const typePrefix = module.type_label ? `${module.type_label}: ` : '';
                opt.textContent = `${typePrefix}${module.title}`;
                if (module.is_completed) {
                    opt.dataset.completed = '1';
                }
                if (selectedIds.map(String).includes(String(module.id))) {
                    opt.selected = true;
                }
                optgroup.appendChild(opt);
            });

            if (optgroup.children.length > 0) {
                selectEl.appendChild(optgroup);
            }
        });

        if (selectEl.options.length === 0) {
            return;
        }

        if (typeof Choices === 'undefined') {
            return;
        }

        const instance = new Choices(selectEl, {
            removeItemButton: true,
            placeholder: true,
            placeholderValue: 'اختر الدروس',
            searchPlaceholderValue: 'ابحث...',
            noResultsText: 'لا توجد نتائج',
            noChoicesText: 'لا توجد دروس',
            itemSelectText: '',
            shouldSort: false,
        });
        rowChoices.set(selectEl, instance);

        const refreshDecoration = () => decorateWeeklyReportChoices(selectEl, completedMap);
        refreshDecoration();
        selectEl.addEventListener('showDropdown', refreshDecoration);
        selectEl.addEventListener('addItem', refreshDecoration);
        selectEl.addEventListener('removeItem', refreshDecoration);
    }

    function getRowCourseId(row) {
        const fixedInput = row.querySelector('.course-id-fixed');
        if (fixedInput) {
            return fixedInput.value;
        }

        return row.querySelector('.course-select')?.value || '';
    }

    async function loadModulesForRow(row, preserveSelected) {
        const moduleSelect = row.querySelector('.module-multi-select');
        const courseId = getRowCourseId(row);
        const selectedFromData = preserveSelected
            ? JSON.parse(moduleSelect.dataset.selectedModules || '[]')
            : [];

        destroyChoices(moduleSelect);
        moduleSelect.innerHTML = '';

        if (!courseId) {
            return;
        }

        try {
            const payload = await fetchModules(courseId);
            const moduleGroups = Array.isArray(payload?.groups)
                ? payload.groups
                : (Array.isArray(payload) ? [{ section_title: 'الدروس', modules: payload }] : []);

            const totalModules = moduleGroups.reduce(
                (count, group) => count + (group.modules?.length || 0),
                0
            );

            if (totalModules === 0) {
                moduleSelect.innerHTML = '<option value="" disabled>لا توجد دروس متاحة</option>';
                return;
            }

            initModuleChoices(moduleSelect, moduleGroups, selectedFromData);
            moduleSelect.dataset.selectedModules = '[]';
        } catch (e) {
            moduleSelect.innerHTML = '<option value="" disabled>تعذر تحميل الدروس</option>';
        }
    }

    function bindCourseSelect(row) {
        const courseSelect = row.querySelector('.course-select');
        if (!courseSelect) {
            return;
        }

        courseSelect.addEventListener('change', () => loadModulesForRow(row, false));
    }

    function prepareFlattenedLessons() {
        flattenedContainer.innerHTML = '';
        let index = 0;

        container.querySelectorAll('.lesson-row').forEach((row) => {
            const courseId = getRowCourseId(row);
            const moduleSelect = row.querySelector('.module-multi-select');
            if (!courseId || !moduleSelect) {
                return;
            }

            const instance = rowChoices.get(moduleSelect);
            const selectedValues = instance
                ? instance.getValue(true)
                : Array.from(moduleSelect.selectedOptions).map((opt) => opt.value);

            selectedValues.forEach((moduleId) => {
                if (!moduleId) {
                    return;
                }

                const courseInput = document.createElement('input');
                courseInput.type = 'hidden';
                courseInput.name = `lessons[${index}][course_id]`;
                courseInput.value = courseId;
                flattenedContainer.appendChild(courseInput);

                const moduleInput = document.createElement('input');
                moduleInput.type = 'hidden';
                moduleInput.name = `lessons[${index}][module_id]`;
                moduleInput.value = moduleId;
                flattenedContainer.appendChild(moduleInput);

                index++;
            });
        });
    }

    function prepareReportForm() {
        syncEditor();
        prepareFlattenedLessons();
    }

    container.querySelectorAll('.lesson-row').forEach((row) => {
        bindCourseSelect(row);
        loadModulesForRow(row, true);
    });

    if (addBtn) {
        addBtn.addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'row g-2 mb-3 lesson-row';
            row.innerHTML = `
                <div class="col-md-5">
                    <label class="form-label fs-12">الكورس</label>
                    <select class="form-select course-select">${buildCoursesOptions('')}</select>
                </div>
                <div class="col-md-7">
                    <label class="form-label fs-12">الدروس</label>
                    <div class="weekly-report-module-select-wrap">
                        <select class="form-select module-multi-select weekly-report-module-select" multiple data-selected-modules="[]"></select>
                    </div>
                </div>
            `;
            container.appendChild(row);
            bindCourseSelect(row);
        });
    }

    if (saveForm) {
        saveForm.addEventListener('submit', () => {
            prepareReportForm();
        });
    }

    if (submitBtn) {
        submitBtn.addEventListener('click', () => {
            if (!window.confirm('هل أنت متأكد من إرسال التقرير؟ لن تتمكن من تعديله أو إرساله مرة أخرى.')) {
                return;
            }

            prepareReportForm();
            submitForm.innerHTML = '';
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = @json(csrf_token());
            submitForm.appendChild(csrf);

            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'PUT';
            submitForm.appendChild(method);

            Array.from(flattenedContainer.querySelectorAll('input')).forEach((input) => {
                submitForm.appendChild(input.cloneNode(true));
            });

            const details = document.createElement('input');
            details.type = 'hidden';
            details.name = 'student_details';
            details.value = getEditorContent();
            submitForm.appendChild(details);

            const notes = document.createElement('input');
            notes.type = 'hidden';
            notes.name = 'student_notes';
            notes.value = document.getElementById('student_notes')?.value || '';
            submitForm.appendChild(notes);

            submitForm.submit();
        });
    }
})();
</script>
@endif
@endsection
