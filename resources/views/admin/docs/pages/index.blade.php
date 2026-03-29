@extends('admin.layouts.master')

@section('page-title', 'صفحات التوثيق')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">صفحات التوثيق</h4>
                <p class="mb-0 text-muted">إدارة محتوى التوثيق من لوحة التحكم</p>
            </div>
            <div class="ms-auto d-flex flex-wrap gap-2">
                <a href="{{ route('admin.docs.ai-pages.improve') }}" class="btn btn-outline-primary">
                    <i class="bi bi-stars me-2"></i>فحص وتعديل بالذكاء الاصطناعي
                </a>
                <a href="{{ route('admin.docs.pages.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>إضافة صفحة
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
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="بحث (عنوان أو slug)..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="documentation_category_id" class="form-select">
                            <option value="">كل الأقسام</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (string) request('documentation_category_id') === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">كل الحالات</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>مسودة</option>
                            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>منشور</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">تصفية</button>
                        <a href="{{ route('admin.docs.pages.index') }}" class="btn btn-secondary">إعادة</a>
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
                                <th>العنوان</th>
                                <th>القسم</th>
                                <th>الأب</th>
                                <th>slug</th>
                                <th>الحالة</th>
                                <th>آخر تحديث</th>
                                <th width="340">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pages as $page)
                            <tr>
                                <td>{{ $loop->iteration + ($pages->currentPage() - 1) * $pages->perPage() }}</td>
                                <td><strong>{{ $page->title }}</strong></td>
                                <td>{{ $page->category->name ?? '—' }}</td>
                                <td>{{ $page->parent->title ?? '—' }}</td>
                                <td><code>{{ $page->slug }}</code></td>
                                <td>
                                    @if($page->status === 'published')
                                        <span class="badge bg-success">منشور</span>
                                    @else
                                        <span class="badge bg-secondary">مسودة</span>
                                    @endif
                                </td>
                                <td><small class="text-muted">{{ $page->updated_at?->diffForHumans() }}</small></td>
                                <td class="d-flex flex-wrap gap-1 align-items-center">
                                    <a href="{{ route('admin.docs.ai-pages.improve', ['documentation_page_id' => $page->id]) }}"
                                        class="btn btn-sm btn-outline-primary"
                                        title="فتح هذه الصفحة في أداة الفحص والتعديل بالتعليمات">فحص بالذكاء</a>
                                    @if($page->isPublished() && $page->category && $page->category->is_active)
                                        <a href="{{ route('frontend.docs.show', ['categorySlug' => $page->category->slug, 'pagePath' => $page->slugPathUnderCategory()]) }}"
                                            class="btn btn-sm btn-outline-info" target="_blank" rel="noopener noreferrer" title="فتح الصفحة على الموقع">عرض المقال</a>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="يتطلب نشر الصفحة وقسمًا نشطًا">عرض المقال</button>
                                    @endif
                                    <form action="{{ route('admin.docs.pages.toggle-publish', $page) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="نشر / إلغاء نشر">نشر</button>
                                    </form>
                                    <a href="{{ route('admin.docs.pages.edit', $page) }}" class="btn btn-sm btn-primary">تعديل</a>
                                    <form action="{{ route('admin.docs.pages.destroy', $page) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف هذه الصفحة؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="text-center py-4 text-muted">لا توجد صفحات بعد</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($pages->hasPages())
            <div class="card-footer">{{ $pages->withQueryString()->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
