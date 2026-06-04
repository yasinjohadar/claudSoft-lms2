<div class="card custom-card student-quizzes-filters-panel mb-4">
    <div class="card-header border-0 pb-0">
        <div class="d-flex align-items-center gap-2">
            <span class="avatar avatar-sm bg-primary-transparent">
                <i class="fe fe-filter text-primary"></i>
            </span>
            <div>
                <h6 class="card-title mb-0">تصفية النتائج</h6>
                <p class="text-muted fs-12 mb-0">ابحث أو صنّف الموارد الخارجية</p>
            </div>
        </div>
    </div>
    <div class="card-body pt-3">
        <form id="external-resources-filters" class="row g-3 align-items-end">
            <div class="col-lg-4 col-md-6">
                <label class="form-label fs-12 fw-semibold">بحث</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent"><i class="fe fe-search"></i></span>
                    <input type="search" name="search" id="erf-search" class="form-control" placeholder="عنوان، وصف، اسم ملف…" value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label fs-12 fw-semibold">نوع المورد</label>
                <select name="resource_type" id="erf-type" class="form-select">
                    <option value="">الكل</option>
                    @foreach(\App\Models\Resource::resourceTypeOptions() as $key => $label)
                        <option value="{{ $key }}" {{ request('resource_type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label fs-12 fw-semibold">التصنيف</label>
                <select name="classification" id="erf-classification" class="form-select">
                    <option value="">الكل</option>
                    @foreach(\App\Models\Resource::classificationOptions() as $key => $label)
                        <option value="{{ $key }}" {{ request('classification') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label fs-12 fw-semibold">الترتيب</label>
                <select name="sort" id="erf-sort" class="form-select">
                    <option value="latest" {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}>الأحدث</option>
                    <option value="title" {{ request('sort') === 'title' ? 'selected' : '' }}>العنوان (أ-ي)</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <button type="button" class="btn btn-outline-secondary rounded-pill w-100" id="erf-reset">
                    <i class="fe fe-rotate-ccw me-1"></i>إعادة تعيين
                </button>
            </div>
        </form>
    </div>
</div>
