@extends('admin.layouts.master')

@section('page-title', 'أبحاث التوثيق')

@section('styles')
@include('admin.docs.categories.partials.styles')
@include('admin.docs.researches.partials.styles')
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
                    <li class="breadcrumb-item active">الأبحاث</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in doc-cat-animate mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow"><i class="fe fe-search me-1"></i>التوثيق</span>
                    <h2 class="group-show-hero__title mb-2">أبحاث التوثيق</h2>
                    <p class="group-show-hero__desc mb-0">
                        البحث يجمع توثيقات موضوع واحد داخل قسم — مثل «الدوال» داخل php.
                        افتح أي بحث لترى توثيقاته وترتّبها بالسحب.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="{{ route('admin.docs.pages.index') }}" class="btn btn-light border">
                            <i class="fe fe-file-text me-1"></i>صفحات التوثيق
                        </a>
                        <a href="{{ route('admin.docs.researches.create') }}" class="btn btn-primary">
                            <i class="fe fe-plus-circle me-1"></i>إضافة بحث
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @include('admin.docs.researches.partials.stats')

        @if(($stats['unassigned_pages'] ?? 0) > 0)
            <div class="alert alert-info border-0 d-flex flex-wrap align-items-center gap-2 doc-cat-animate">
                <span class="flex-grow-1">
                    <i class="fe fe-info me-1"></i>
                    لديك <strong>{{ number_format($stats['unassigned_pages']) }}</strong> صفحة بلا بحث.
                    يمكنك إسنادها دفعة واحدة من قائمة الصفحات.
                </span>
                <a href="{{ route('admin.docs.pages.index', ['documentation_research_id' => 'none']) }}" class="btn btn-sm btn-info">
                    <i class="fe fe-layers me-1"></i>إسناد جماعي
                </a>
            </div>
        @endif

        <div class="card custom-card doc-cat-filter-card doc-cat-animate mb-4">
            <div class="card-header border-0 pb-0">
                <h6 class="card-title mb-0"><i class="fe fe-filter me-2 text-primary"></i>تصفية وبحث</h6>
            </div>
            <div class="card-body pt-3">
                <form method="GET" action="{{ route('admin.docs.researches.index') }}" class="row g-3 align-items-end">
                    <div class="col-lg-4 col-md-6">
                        <label class="form-label" for="research-search">بحث</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent"><i class="fe fe-search"></i></span>
                            <input type="text" name="search" id="research-search" class="form-control"
                                   placeholder="اسم أو slug..." value="{{ request('search') }}" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="research-category">القسم</label>
                        <select name="documentation_category_id" id="research-category" class="form-select">
                            <option value="">كل الأقسام</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ (string) request('documentation_category_id') === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label" for="research-active">الحالة</label>
                        <select name="is_active" id="research-active" class="form-select">
                            <option value="">الكل</option>
                            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>مفعّل</option>
                            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>معطّل</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary flex-fill"><i class="fe fe-filter me-1"></i>تصفية</button>
                            <a href="{{ route('admin.docs.researches.index') }}" class="btn btn-light border"><i class="fe fe-rotate-ccw"></i></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card custom-card doc-cat-table-card doc-cat-animate">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h6 class="card-title mb-0">قائمة الأبحاث</h6>
                <span class="doc-cat-results-meta">
                    {{ $researches->total() }} بحث
                    @if(request()->hasAny(['search', 'documentation_category_id', 'is_active']))
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
                                <th>البحث</th>
                                <th>القسم</th>
                                <th>slug</th>
                                <th>الصفحات</th>
                                <th>الترتيب</th>
                                <th>الحالة</th>
                                <th width="200">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @include('admin.docs.researches.partials.table-rows', ['researches' => $researches])
                        </tbody>
                    </table>
                </div>
            </div>
            @if($researches->hasPages())
                <div class="card-footer border-top-0 pt-0">
                    {{ $researches->withQueryString()->links() }}
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
