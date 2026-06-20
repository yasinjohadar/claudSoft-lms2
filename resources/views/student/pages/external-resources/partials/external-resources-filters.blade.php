<div class="card custom-card student-external-resources-filters dashboard-fade-in mb-4">
    <div class="card-body py-3">
        <form id="external-resources-filters" class="student-external-resources-filters__form">
            <div class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label fs-12 fw-semibold mb-1" for="erf-search">بحث</label>
                    <div class="input-group student-external-resources-filters__search">
                        <span class="input-group-text bg-transparent border-end-0"><i class="fe fe-search text-muted"></i></span>
                        <input type="search" name="search" id="erf-search" class="form-control border-start-0" placeholder="عنوان، وصف، اسم ملف…" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fs-12 fw-semibold mb-1" for="erf-type">نوع المورد</label>
                    <select name="resource_type" id="erf-type" class="form-select">
                        <option value="">الكل</option>
                        @foreach(\App\Models\Resource::resourceTypeOptions() as $key => $label)
                            <option value="{{ $key }}" {{ request('resource_type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fs-12 fw-semibold mb-1" for="erf-classification">التصنيف</label>
                    <select name="classification" id="erf-classification" class="form-select">
                        <option value="">الكل</option>
                        @foreach(\App\Models\Resource::classificationOptions() as $key => $label)
                            <option value="{{ $key }}" {{ request('classification') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fs-12 fw-semibold mb-1" for="erf-sort">الترتيب</label>
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
            </div>
        </form>
    </div>
</div>
