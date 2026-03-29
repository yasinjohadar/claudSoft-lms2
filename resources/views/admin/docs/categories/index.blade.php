@extends('admin.layouts.master')

@section('page-title', 'أقسام التوثيق')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">أقسام التوثيق</h4>
                <p class="mb-0 text-muted">تصنيفات أو لغات برمجية للتوثيق</p>
            </div>
            <div class="ms-auto">
                <a href="{{ route('admin.docs.categories.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>إضافة قسم
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card custom-card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="بحث..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="kind" class="form-select">
                            <option value="">كل الأنواع</option>
                            <option value="section" {{ request('kind') === 'section' ? 'selected' : '' }}>قسم موضوعي</option>
                            <option value="technology" {{ request('kind') === 'technology' ? 'selected' : '' }}>لغة / تقنية</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">تصفية</button>
                        <a href="{{ route('admin.docs.categories.index') }}" class="btn btn-secondary">إعادة</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card custom-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover text-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الاسم</th>
                                <th>النوع</th>
                                <th>الرابط</th>
                                <th>الصفحات</th>
                                <th>الترتيب</th>
                                <th>الحالة</th>
                                <th width="180">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                            <tr>
                                <td>{{ $loop->iteration + ($categories->currentPage() - 1) * $categories->perPage() }}</td>
                                <td>
                                    @if($category->icon)<i class="{{ $category->icon }} me-1"></i>@endif
                                    <strong>{{ $category->name }}</strong>
                                    @if($category->parent)
                                        <small class="text-muted d-block">تحت: {{ $category->parent->name }}</small>
                                    @endif
                                </td>
                                <td>{{ $category->kind === 'technology' ? 'تقنية' : 'قسم' }}</td>
                                <td><code>{{ $category->slug }}</code></td>
                                <td>{{ $category->pages_count }}</td>
                                <td>{{ $category->sort_order }}</td>
                                <td>
                                    @if($category->is_active)
                                        <span class="badge bg-success">مفعّل</span>
                                    @else
                                        <span class="badge bg-secondary">معطّل</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('admin.docs.categories.toggle-active', $category) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">تبديل</button>
                                    </form>
                                    <a href="{{ route('admin.docs.categories.edit', $category) }}" class="btn btn-sm btn-primary">تعديل</a>
                                    <form action="{{ route('admin.docs.categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف القسم؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="text-center py-4 text-muted">لا توجد أقسام بعد</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($categories->hasPages())
            <div class="card-footer">{{ $categories->withQueryString()->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
