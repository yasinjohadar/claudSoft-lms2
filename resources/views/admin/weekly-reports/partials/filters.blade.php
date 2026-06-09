@php
    $filterAction = $filterAction ?? request()->url();
    $resetRoute = $resetRoute ?? request()->route()->getName();
    $selectedCourseId = (string) ($filters['course_id'] ?? '');
    $selectedGroupId = (string) ($filters['group_id'] ?? '');
    $selectedStatus = (string) ($filters['status'] ?? '');
    $showStatusFilter = $showStatusFilter ?? false;
    $courses = $filterOptions['courses'] ?? collect();
    $groups = $filterOptions['groups'] ?? collect();
    $statusOptions = [
        '' => 'كل الحالات',
        'draft' => 'مسودة',
        'submitted' => 'مرسل',
        'reviewed' => 'مراجع',
        'closed' => 'مغلق',
    ];
    $courseColClass = $showStatusFilter ? 'col-md-4' : 'col-md-5';
    $groupColClass = $showStatusFilter ? 'col-md-4' : 'col-md-5';
    $actionsColClass = $showStatusFilter ? 'col-md-4' : 'col-md-2';
@endphp

<div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
    <div class="card-header border-0 pb-0">
        <div class="d-flex align-items-start gap-2">
            <span class="avatar avatar-sm bg-primary-transparent">
                <i class="fe fe-filter text-primary"></i>
            </span>
            <div>
                <h5 class="card-title mb-1">تصفية النتائج</h5>
                <p class="text-muted fs-12 mb-0">
                    @if($showStatusFilter)
                        اختر الكورس والمجموعة والحالة لعرض التقارير المطابقة فقط
                    @else
                        اختر الكورس والمجموعة لعرض التقارير المطابقة فقط
                    @endif
                </p>
            </div>
        </div>
    </div>
    <div class="card-body pt-3">
        <form method="GET" action="{{ $filterAction }}" id="weekly-reports-filter-form" class="group-show-filters mb-0">
            <div class="row g-3 align-items-end">
                <div class="{{ $courseColClass }}">
                    <label class="form-label" for="filter_course_id">الكورس</label>
                    <select class="form-select" name="course_id" id="filter_course_id">
                        <option value="">كل الكورسات</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ $selectedCourseId === (string) $course->id ? 'selected' : '' }}>
                                {{ $course->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="{{ $groupColClass }}">
                    <label class="form-label" for="filter_group_id">المجموعة</label>
                    <select class="form-select" name="group_id" id="filter_group_id">
                        <option value="">كل المجموعات</option>
                        @foreach($groups as $group)
                            <option
                                value="{{ $group->id }}"
                                data-course-ids="{{ $group->courses->pluck('id')->implode(',') }}"
                                {{ $selectedGroupId === (string) $group->id ? 'selected' : '' }}
                            >
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                    <div id="filter-group-empty-hint" class="form-text text-warning d-none">
                        لا توجد مجموعات مرتبطة بالكورس المحدد.
                    </div>
                </div>

                @if($showStatusFilter)
                    <div class="{{ $actionsColClass }}">
                        <label class="form-label" for="filter_status">الحالة</label>
                        <select class="form-select" name="status" id="filter_status">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ $selectedStatus === (string) $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="{{ $actionsColClass }}">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="fe fe-search me-1"></i>تطبيق
                        </button>
                        <a href="{{ route($resetRoute) }}" class="btn btn-light" title="إعادة تعيين">
                            <i class="fe fe-rotate-ccw"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const courseSelect = document.getElementById('filter_course_id');
        const groupSelect = document.getElementById('filter_group_id');
        const groupEmptyHint = document.getElementById('filter-group-empty-hint');

        if (!courseSelect || !groupSelect) {
            return;
        }

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

                const isMatch = !selectedCourseId || courseIds.includes(selectedCourseId);
                option.disabled = !isMatch;
                option.hidden = !isMatch;

                if (isMatch) {
                    hasMatchingGroups = true;
                } else if (option.selected) {
                    option.selected = false;
                }
            });

            if (!selectedCourseId) {
                groupEmptyHint?.classList.add('d-none');
            } else if (!hasMatchingGroups) {
                groupSelect.value = '';
                groupEmptyHint?.classList.remove('d-none');
            } else {
                groupEmptyHint?.classList.add('d-none');
            }
        };

        courseSelect.addEventListener('change', applyGroupFilter);
        applyGroupFilter();
    })();
</script>
@endpush
