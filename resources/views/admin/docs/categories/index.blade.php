@extends('admin.layouts.master')

@section('page-title', 'أقسام التوثيق')

@section('styles')
@include('admin.docs.categories.partials.styles')
@endsection

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb doc-cat-animate dashboard-fade-in">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.docs.pages.index') }}">التوثيق</a></li>
                    <li class="breadcrumb-item active">أقسام التوثيق</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in doc-cat-animate mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-book me-1"></i>
                        إدارة التوثيق
                    </span>
                    <h2 class="group-show-hero__title mb-2">أقسام التوثيق</h2>
                    <p class="group-show-hero__desc mb-0">
                        نظّم المقالات حسب اللغات البرمجية أو الموضوعات. كل قسم يحدد مسار URL ويُجمّع الصفحات ذات الصلة.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="{{ route('admin.docs.pages.index') }}" class="btn btn-outline-primary">
                            <i class="fe fe-file-text me-1"></i>صفحات التوثيق
                        </a>
                        <a href="{{ route('admin.docs.categories.create') }}" class="btn btn-primary">
                            <i class="fe fe-plus me-1"></i>إضافة قسم
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @include('admin.docs.categories.partials.stats')

        <div class="card custom-card doc-cat-filter-card doc-cat-animate mb-4">
            <div class="card-header border-0 pb-0">
                <h6 class="card-title mb-0">
                    <i class="fe fe-filter me-2 text-primary"></i>تصفية وبحث
                </h6>
            </div>
            <div class="card-body pt-3">
                <form method="GET" action="{{ route('admin.docs.categories.index') }}" class="row g-3 align-items-end">
                    <div class="col-lg-4 col-md-6">
                        <label class="form-label" for="doc-cat-search">بحث</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent"><i class="fe fe-search"></i></span>
                            <input type="text"
                                   name="search"
                                   id="doc-cat-search"
                                   class="form-control"
                                   placeholder="اسم القسم أو الوصف..."
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label" for="doc-cat-kind">النوع</label>
                        <select name="kind" id="doc-cat-kind" class="form-select">
                            <option value="">كل الأنواع</option>
                            <option value="section" {{ request('kind') === 'section' ? 'selected' : '' }}>قسم موضوعي</option>
                            <option value="technology" {{ request('kind') === 'technology' ? 'selected' : '' }}>لغة / تقنية</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="doc-cat-parent">التسلسل</label>
                        <select name="parent" id="doc-cat-parent" class="form-select">
                            <option value="">الكل</option>
                            <option value="root" {{ request('parent') === 'root' ? 'selected' : '' }}>أقسام رئيسية فقط</option>
                            @foreach($parentCategories as $parent)
                                <option value="{{ $parent->id }}" {{ (string) request('parent') === (string) $parent->id ? 'selected' : '' }}>
                                    تحت: {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fe fe-filter me-1"></i>تصفية
                            </button>
                            <a href="{{ route('admin.docs.categories.index') }}" class="btn btn-light border">
                                <i class="fe fe-rotate-ccw"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card custom-card doc-cat-table-card doc-cat-animate">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h6 class="card-title mb-0">قائمة الأقسام</h6>
                <span class="doc-cat-results-meta">
                    {{ $categories->total() }} قسم
                    @if(request()->hasAny(['search', 'kind', 'parent']))
                        <span class="text-primary">(مفلتر)</span>
                    @endif
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table doc-cat-table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الاسم</th>
                                <th>النوع</th>
                                <th>الرابط</th>
                                <th>الصفحات</th>
                                <th>الترتيب</th>
                                <th>الحالة</th>
                                <th width="140">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @include('admin.docs.categories.partials.table-rows', ['categories' => $categories])
                        </tbody>
                    </table>
                </div>
            </div>
            @if($categories->hasPages())
            <div class="card-footer border-top-0 pt-0">
                {{ $categories->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.documentElement.classList.add('loaded');
</script>
@endsection
