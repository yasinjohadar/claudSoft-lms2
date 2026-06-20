<div class="card custom-card student-quizzes-filters-panel dashboard-fade-in mb-4">
    <div class="card-header border-0 pb-0">
        <div class="d-flex align-items-center gap-2">
            <span class="avatar avatar-sm bg-primary-transparent">
                <i class="fe fe-filter text-primary"></i>
            </span>
            <div>
                <h6 class="card-title mb-0">تصفية الأعمال</h6>
                <p class="text-muted fs-12 mb-0">ابحث بالعنوان أو الوصف أو التقنيات، أو فلتر حسب الحالة والتصنيف</p>
            </div>
        </div>
    </div>
    <div class="card-body pt-3">
        <form id="student-works-filters" class="row g-3 align-items-end">
            <div class="col-xl-4 col-lg-5 col-md-6">
                <label class="form-label fs-12 fw-semibold" for="sw-search">بحث</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent"><i class="fe fe-search"></i></span>
                    <input type="search" name="search" id="sw-search" class="form-control"
                           placeholder="البحث في العنوان، الوصف، التقنيات..."
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6">
                <label class="form-label fs-12 fw-semibold" for="sw-status">الحالة</label>
                <select name="status" id="sw-status" class="form-select">
                    <option value="">جميع الحالات</option>
                    @foreach($statuses as $key => $status)
                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                            {{ $status['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-xl-3 col-lg-2 col-md-6">
                <label class="form-label fs-12 fw-semibold" for="sw-category">التصنيف</label>
                <select name="category" id="sw-category" class="form-select">
                    <option value="">جميع التصنيفات</option>
                    @foreach($categories as $key => $category)
                        <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>
                            {{ $category['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-xl-2 col-lg-2 col-md-12">
                <button type="button" class="btn btn-outline-secondary rounded-pill w-100" id="sw-reset">
                    <i class="fe fe-rotate-ccw me-1"></i>إعادة تعيين
                </button>
            </div>
        </form>
    </div>
</div>
