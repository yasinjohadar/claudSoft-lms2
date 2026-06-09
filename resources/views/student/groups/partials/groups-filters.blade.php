<div class="card custom-card student-quizzes-filters-panel mb-4">
    <div class="card-header border-0 pb-0">
        <div class="d-flex align-items-center gap-2">
            <span class="avatar avatar-sm bg-primary-transparent">
                <i class="fe fe-filter text-primary"></i>
            </span>
            <div>
                <h6 class="card-title mb-0">تصفية المجموعات</h6>
                <p class="text-muted fs-12 mb-0">ابحث بالاسم أو صنّف حسب الكورس</p>
            </div>
        </div>
    </div>
    <div class="card-body pt-3">
        <form method="GET" action="{{ route('student.groups.index') }}" class="row g-3 align-items-end">
            <div class="col-lg-5 col-md-6">
                <label class="form-label fs-12 fw-semibold">بحث</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent"><i class="fe fe-search"></i></span>
                    <input type="search"
                           name="search"
                           class="form-control"
                           placeholder="البحث بالاسم أو الوصف..."
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <label class="form-label fs-12 fw-semibold">الكورس</label>
                <select name="course_id" class="form-select">
                    <option value="">جميع الكورسات</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ (string) request('course_id') === (string) $course->id ? 'selected' : '' }}>
                            {{ $course->title }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 col-md-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-pill flex-fill">
                    <i class="fe fe-search me-1"></i>بحث
                </button>
                <a href="{{ route('student.groups.index') }}" class="btn btn-outline-secondary rounded-pill">
                    <i class="fe fe-rotate-ccw"></i>
                </a>
            </div>
        </form>
    </div>
</div>
