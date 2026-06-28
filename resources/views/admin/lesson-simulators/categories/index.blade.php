@extends('admin.layouts.master')

@section('page-title')
    تصنيفات المحاكيات
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h5 class="page-title fs-21 mb-1">تصنيفات المحاكيات</h5>
                <p class="text-muted small mb-0">تصنيفات متعددة المستويات لتنظيم المحاكيات</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.lesson-simulators.index') }}" class="btn btn-secondary btn-sm">المحاكيات</a>
                <a href="{{ route('admin.lesson-simulators.categories.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> إضافة تصنيف
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>الاسم</th>
                            <th>المسار</th>
                            <th>الأب</th>
                            <th>فرعية</th>
                            <th>محاكيات</th>
                            <th>ترتيب</th>
                            <th>الحالة</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>
                                    @if(($category->tree_depth ?? 0) > 0)
                                        <span class="text-muted">{{ str_repeat('— ', $category->tree_depth) }}</span>
                                    @endif
                                    {{ $category->name }}
                                </td>
                                <td class="text-muted small">{{ $category->display_path ?? $category->full_path }}</td>
                                <td>
                                    @php
                                        $parentName = $category->parent_id
                                            ? ($categories->firstWhere('id', $category->parent_id)?->name ?? '—')
                                            : '—';
                                    @endphp
                                    {{ $parentName }}
                                </td>
                                <td>{{ $category->children_count }}</td>
                                <td>{{ $category->simulators_count }}</td>
                                <td>{{ $category->sort_order }}</td>
                                <td>
                                    <span class="badge bg-{{ $category->is_active ? 'success' : 'secondary' }}">
                                        {{ $category->is_active ? 'نشط' : 'معطّل' }}
                                    </span>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('admin.lesson-simulators.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">تعديل</a>
                                    <form action="{{ route('admin.lesson-simulators.categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف «{{ addslashes($category->name) }}»؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    لا توجد تصنيفات — <a href="{{ route('admin.lesson-simulators.categories.create') }}">أنشئ أول تصنيف</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
