@extends('admin.layouts.master')

@section('page-title', 'إنشاء تقرير أسبوعي')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.weekly-reports.partials.breadcrumb', ['current' => 'إنشاء تقرير يدوي'])

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-plus-circle me-1"></i>
                        التقارير الأسبوعية
                    </span>
                    <h2 class="group-show-hero__title mb-2">إنشاء تقرير يدوي</h2>
                    <p class="group-show-hero__desc mb-0">
                        اختر الكورس ثم المجموعة المرتبطة به. سيتم إنشاء تقرير لكل طالب نشط في المجموعة.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions group-show-actions--single">
                        <a href="{{ route('admin.weekly-reports.groups-overview') }}"
                           class="group-show-action group-show-action--info">
                            <span class="group-show-action__icon"><i class="fe fe-layers"></i></span>
                            <span class="group-show-action__text">عودة لتقارير المجموعات</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @include('admin.weekly-reports.partials.quick-nav', ['navActive' => 'create'])

        @include('admin.components.alerts')

        <div id="client-validation-alert" class="alert alert-danger d-none" role="alert"></div>

        <div class="card custom-card group-show-members-card dashboard-fade-in">
            <div class="card-header border-0 pb-0">
                <h4 class="card-title mb-1">بيانات التقرير</h4>
                <p class="fs-12 text-muted mb-0">حدّد الكورس والمجموعة وعنوان التقرير والموعد النهائي.</p>
            </div>
            <div class="card-body pt-3">
                <form method="POST" action="{{ route('admin.weekly-reports.store') }}" id="weekly-report-create-form" class="group-show-filters mb-0" novalidate>
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="course_id">الكورس <span class="text-danger">*</span></label>
                            <select class="form-select @error('course_id') is-invalid @enderror" name="course_id" id="course_id" required>
                                <option value="">اختر الكورس</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ (string) old('course_id') === (string) $course->id ? 'selected' : '' }}>
                                        {{ $course->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('course_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="group_id">المجموعة <span class="text-danger">*</span></label>
                            <select class="form-select @error('group_id') is-invalid @enderror" name="group_id" id="group_id" required>
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
                            <div id="group-empty-hint" class="form-text text-warning d-none">
                                لا توجد مجموعات مرتبطة بهذا الكورس. اختر كورساً آخر أو اربط مجموعة بالكورس من إدارة المجموعات.
                            </div>
                            @error('group_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-8">
                            <label class="form-label" for="report_title">عنوان التقرير <span class="text-danger">*</span></label>
                            <input
                                class="form-control @error('report_title') is-invalid @enderror"
                                type="text"
                                name="report_title"
                                id="report_title"
                                value="{{ old('report_title') }}"
                                placeholder="مثال: تقرير الأسبوع الأول"
                                required
                            >
                            @error('report_title')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="due_at">الموعد النهائي</label>
                            <input
                                class="form-control @error('due_at') is-invalid @enderror"
                                type="datetime-local"
                                name="due_at"
                                id="due_at"
                                value="{{ old('due_at') }}"
                            >
                            @error('due_at')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4 pt-2 border-top">
                        <button type="submit" class="btn btn-primary" id="submit-weekly-reports" disabled>
                            <i class="fe fe-plus me-1"></i>إنشاء التقارير
                        </button>
                        <a href="{{ route('admin.weekly-reports.groups-overview') }}" class="btn btn-light">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('weekly-report-create-form');
        const courseSelect = document.getElementById('course_id');
        const groupSelect = document.getElementById('group_id');
        const titleInput = document.getElementById('report_title');
        const submitBtn = document.getElementById('submit-weekly-reports');
        const groupEmptyHint = document.getElementById('group-empty-hint');
        const clientAlert = document.getElementById('client-validation-alert');

        if (!form || !courseSelect || !groupSelect || !titleInput || !submitBtn) {
            return;
        }

        const hideClientAlert = () => {
            if (clientAlert) {
                clientAlert.classList.add('d-none');
                clientAlert.textContent = '';
            }
        };

        const showClientAlert = (message) => {
            if (!clientAlert) {
                window.alert(message);
                return;
            }
            clientAlert.textContent = message;
            clientAlert.classList.remove('d-none');
            clientAlert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        };

        const applyGroupFilter = () => {
            const selectedCourseId = courseSelect.value;
            const options = Array.from(groupSelect.options);
            let hasMatchingGroups = false;

            options.forEach((option, index) => {
                if (index === 0) {
                    option.disabled = false;
                    option.hidden = false;
                    return;
                }

                const courseIds = (option.dataset.courseIds || '')
                    .split(',')
                    .map((id) => id.trim())
                    .filter(Boolean);

                const isMatch = selectedCourseId && courseIds.includes(selectedCourseId);
                option.disabled = !isMatch;
                option.hidden = !isMatch;

                if (isMatch) {
                    hasMatchingGroups = true;
                } else if (option.selected) {
                    option.selected = false;
                }
            });

            if (!selectedCourseId) {
                groupSelect.value = '';
                groupEmptyHint?.classList.add('d-none');
            } else if (!hasMatchingGroups) {
                groupSelect.value = '';
                groupEmptyHint?.classList.remove('d-none');
            } else {
                groupEmptyHint?.classList.add('d-none');
            }

            updateSubmitState();
        };

        const selectedCourseHasGroups = () => {
            if (!courseSelect.value) {
                return false;
            }

            return Array.from(groupSelect.options).some((option, index) => {
                if (index === 0) {
                    return false;
                }

                const courseIds = (option.dataset.courseIds || '')
                    .split(',')
                    .map((id) => id.trim())
                    .filter(Boolean);

                return courseIds.includes(courseSelect.value) && !option.disabled;
            });
        };

        const updateSubmitState = () => {
            const courseSelected = courseSelect.value !== '';
            const groupSelected = groupSelect.value !== '';
            const titleFilled = titleInput.value.trim() !== '';
            const hasMatchingGroups = selectedCourseHasGroups();

            submitBtn.disabled = !(courseSelected && groupSelected && titleFilled && hasMatchingGroups);
        };

        form.addEventListener('submit', function (event) {
            hideClientAlert();

            if (!courseSelect.value) {
                event.preventDefault();
                showClientAlert('يرجى اختيار الكورس.');
                courseSelect.focus();
                return;
            }

            if (!selectedCourseHasGroups()) {
                event.preventDefault();
                showClientAlert('لا توجد مجموعات مرتبطة بالكورس المحدد.');
                return;
            }

            if (!groupSelect.value) {
                event.preventDefault();
                showClientAlert('يرجى اختيار المجموعة.');
                groupSelect.focus();
                return;
            }

            if (!titleInput.value.trim()) {
                event.preventDefault();
                showClientAlert('يرجى إدخال عنوان التقرير.');
                titleInput.focus();
                return;
            }
        });

        courseSelect.addEventListener('change', () => {
            hideClientAlert();
            applyGroupFilter();
        });

        groupSelect.addEventListener('change', () => {
            hideClientAlert();
            updateSubmitState();
        });

        titleInput.addEventListener('input', updateSubmitState);

        applyGroupFilter();
    })();
</script>
@endpush
