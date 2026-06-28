@extends('admin.layouts.master')

@section('page-title', $category->name . ' — التوثيق')

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
                    <li class="breadcrumb-item"><a href="{{ route('admin.docs.categories.index') }}">أقسام التوثيق</a></li>
                    <li class="breadcrumb-item active">{{ $category->name }}</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in doc-cat-animate mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <div class="doc-cat-show-hero">
                        <div class="doc-cat-icon doc-cat-icon--lg @if($category->kind === 'technology') doc-cat-icon--tech @else doc-cat-icon--section @endif">
                            @if($category->icon)
                                <i class="{{ $category->icon }}"></i>
                            @elseif($category->kind === 'technology')
                                <i class="fe fe-code"></i>
                            @else
                                <i class="fe fe-book-open"></i>
                            @endif
                        </div>
                        <div class="flex-fill min-w-0">
                            <span class="group-show-hero__eyebrow">
                                @if($category->kind === 'technology')
                                    <i class="fe fe-cpu me-1"></i>لغة / تقنية
                                @else
                                    <i class="fe fe-layers me-1"></i>قسم موضوعي
                                @endif
                            </span>
                            <h2 class="group-show-hero__title mb-2">{{ $category->name }}</h2>
                            @if($category->description)
                                <p class="group-show-hero__desc mb-0">{{ $category->description }}</p>
                            @else
                                <p class="group-show-hero__desc mb-0">إدارة ومتابعة جميع مقالات التوثيق ضمن هذا القسم.</p>
                            @endif
                            <div class="doc-cat-show-hero__meta">
                                <code class="doc-cat-slug">{{ $category->slug }}</code>
                                @if($category->is_active)
                                    <span class="doc-cat-status doc-cat-status--active">
                                        <span class="doc-cat-status__dot"></span>مفعّل
                                    </span>
                                @else
                                    <span class="doc-cat-status doc-cat-status--inactive">
                                        <span class="doc-cat-status__dot"></span>معطّل
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="{{ route('admin.docs.pages.create', ['documentation_category_id' => $category->id]) }}" class="btn btn-primary">
                            <i class="fe fe-plus me-1"></i>مقال جديد
                        </a>
                        <a href="{{ route('admin.docs.categories.edit', $category) }}" class="btn btn-outline-primary">
                            <i class="fe fe-edit-2 me-1"></i>تعديل القسم
                        </a>
                        <a href="{{ route('admin.docs.categories.index') }}" class="btn btn-light border">
                            <i class="fe fe-arrow-right me-1"></i>رجوع
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @include('admin.docs.categories.partials.show-stats')

        <div class="card custom-card doc-cat-filter-card doc-cat-animate mb-4">
            <div class="card-header border-0 pb-0">
                <h6 class="card-title mb-0">
                    <i class="fe fe-filter me-2 text-primary"></i>تصفية المقالات
                </h6>
            </div>
            <div class="card-body pt-3">
                <form method="GET" action="{{ route('admin.docs.categories.show', $category) }}" class="row g-3 align-items-end">
                    <div class="col-lg-5 col-md-6">
                        <label class="form-label" for="doc-page-search">بحث</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent"><i class="fe fe-search"></i></span>
                            <input type="text"
                                   name="search"
                                   id="doc-page-search"
                                   class="form-control"
                                   placeholder="عنوان، slug، أو مقتطف..."
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="doc-page-status">الحالة</label>
                        <select name="status" id="doc-page-status" class="form-select">
                            <option value="">كل الحالات</option>
                            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>منشور</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>مسودة</option>
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-12">
                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fe fe-filter me-1"></i>تصفية
                            </button>
                            <a href="{{ route('admin.docs.categories.show', $category) }}" class="btn btn-light border">
                                <i class="fe fe-rotate-ccw"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card custom-card doc-cat-table-card doc-cat-animate">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h6 class="card-title mb-0">مقالات {{ $category->name }}</h6>
                <span class="doc-cat-results-meta">
                    {{ $stats['total'] }} مقال
                    @if(request()->hasAny(['search', 'status']))
                        <span class="text-primary">(نتائج مفلترة: {{ $filteredPages->count() }})</span>
                    @endif
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table doc-cat-table mb-0">
                        <thead>
                            <tr>
                                <th>العنوان</th>
                                <th>slug</th>
                                <th>الحالة</th>
                                <th>الترتيب</th>
                                <th>آخر تحديث</th>
                                <th width="320">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (empty($pageTree))
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="doc-cat-empty">
                                            <div class="doc-cat-empty__icon"><i class="fe fe-file-text"></i></div>
                                            <h6 class="mb-1">لا توجد مقالات في هذا القسم</h6>
                                            <p class="text-muted small mb-3">ابدأ بإنشاء أول مقال توثيق لـ {{ $category->name }}.</p>
                                            <a href="{{ route('admin.docs.pages.create', ['documentation_category_id' => $category->id]) }}" class="btn btn-primary btn-sm">
                                                <i class="fe fe-plus me-1"></i>إضافة مقال
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                @include('admin.docs.categories.partials.page-tree-rows', [
                                    'nodes' => $pageTree,
                                    'category' => $category,
                                    'depth' => 0,
                                ])
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.partials.documentation-link-modal', [
    'modalMode' => 'docs',
    'allCourses' => $allCourses ?? collect(),
])
@endsection

@section('scripts')
<script>
document.documentElement.classList.add('loaded');
</script>
@endsection
