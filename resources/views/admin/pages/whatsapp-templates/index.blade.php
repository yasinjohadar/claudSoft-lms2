@extends('admin.layouts.master')

@section('page-title')
    قوالب رسائل واتساب
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">قوالب رسائل واتساب</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.whatsapp-messages.index') }}">رسائل WhatsApp</a></li>
                            <li class="breadcrumb-item active" aria-current="page">قوالب الرسائل</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('admin.whatsapp-templates.create') }}" class="btn btn-primary btn-wave">
                    <i class="fas fa-plus me-2"></i>إضافة قالب جديد
                </a>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">قائمة القوالب</div>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.whatsapp-templates.index') }}" class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <input type="text" name="search" class="form-control" placeholder="بحث بالاسم أو المحتوى..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2">
                                    <select name="type" class="form-select">
                                        <option value="">كل الأنواع</option>
                                        <option value="text" {{ request('type') == 'text' ? 'selected' : '' }}>نص</option>
                                        <option value="template" {{ request('type') == 'template' ? 'selected' : '' }}>قالب Meta</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="is_active" class="form-select">
                                        <option value="">كل الحالات</option>
                                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>نشط</option>
                                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>غير نشط</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">بحث</button>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle table-nowrap mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width: 50px;">#</th>
                                            <th scope="col">الاسم</th>
                                            <th scope="col">المعرّف (slug)</th>
                                            <th scope="col">النوع</th>
                                            <th scope="col">اللغة</th>
                                            <th scope="col">الحالة</th>
                                            <th scope="col">تاريخ الإنشاء</th>
                                            <th scope="col" style="min-width: 120px;">العمليات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($templates as $template)
                                            <tr>
                                                <td>{{ $loop->iteration + ($templates->currentPage() - 1) * $templates->perPage() }}</td>
                                                <td><strong>{{ $template->name }}</strong></td>
                                                <td><code>{{ $template->slug ?? '—' }}</code></td>
                                                <td>
                                                    @if($template->type === 'text')
                                                        <span class="badge bg-info">نص</span>
                                                    @else
                                                        <span class="badge bg-secondary">قالب Meta</span>
                                                    @endif
                                                </td>
                                                <td>{{ $template->language }}</td>
                                                <td>
                                                    @if($template->is_active)
                                                        <span class="badge bg-success">نشط</span>
                                                    @else
                                                        <span class="badge bg-danger">غير نشط</span>
                                                    @endif
                                                </td>
                                                <td>{{ $template->created_at->format('Y-m-d') }}</td>
                                                <td>
                                                    <div class="btn-list">
                                                        <a href="{{ route('admin.whatsapp-templates.edit', $template) }}" class="btn btn-sm btn-info btn-wave" title="تعديل">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('admin.whatsapp-templates.destroy', $template) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا القالب؟');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger btn-wave" title="حذف">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">لا توجد قوالب. <a href="{{ route('admin.whatsapp-templates.create') }}">إضافة قالب جديد</a></td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($templates->hasPages())
                                <div class="d-flex justify-content-center mt-3">
                                    {{ $templates->withQueryString()->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
