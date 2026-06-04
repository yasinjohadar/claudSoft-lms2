@php
    $currentType = request('quiz_type', '');
    $typeFilters = [
        ['key' => '', 'label' => 'الكل', 'icon' => 'fe-grid'],
        ['key' => 'practice', 'label' => 'تدريبي', 'icon' => 'fe-edit-3'],
        ['key' => 'graded', 'label' => 'مُقيّم', 'icon' => 'fe-award'],
        ['key' => 'final_exam', 'label' => 'نهائي', 'icon' => 'fe-flag'],
    ];
    $courseParams = request()->only('course_id');
@endphp

<div class="card custom-card student-quizzes-filters-panel mb-4">
    <div class="card-header border-0 pb-0">
        <div class="d-flex align-items-center gap-2">
            <span class="avatar avatar-sm bg-primary-transparent"><i class="fe fe-filter text-primary"></i></span>
            <div>
                <h6 class="card-title mb-0">تصفية الاختبارات</h6>
                <p class="text-muted fs-12 mb-0">اختر الكورس أو نوع الاختبار</p>
            </div>
        </div>
    </div>
    <div class="card-body pt-3">
        <form method="GET" action="{{ route('student.quizzes.index') }}" id="quizFilterForm">
            @if(request('course_id'))
                <input type="hidden" name="course_id" value="{{ request('course_id') }}">
            @endif
            <div class="student-quizzes-type-filters">
                @foreach($typeFilters as $filter)
                    @php
                        $params = array_filter(array_merge($courseParams, ['quiz_type' => $filter['key'] ?: null]));
                    @endphp
                    <a href="{{ route('student.quizzes.index', $params) }}"
                       class="student-quizzes-type-filter {{ $currentType === $filter['key'] ? 'is-active' : '' }}">
                        <i class="fe {{ $filter['icon'] }}"></i>
                        <span>{{ $filter['label'] }}</span>
                    </a>
                @endforeach
            </div>
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fs-12 fw-semibold">اختر الكورس</label>
                    <select name="course_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">جميع الكورسات</option>
                        @foreach($courses ?? [] as $course)
                            <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                {{ $course->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    @if(request()->hasAny(['course_id', 'quiz_type']))
                        <a href="{{ route('student.quizzes.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill w-100">
                            <i class="fe fe-x me-1"></i>إعادة تعيين
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>
