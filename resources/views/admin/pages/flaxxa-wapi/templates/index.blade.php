@extends('admin.layouts.master')

@section('page-title')
    قوالب Flaxxa المحلية
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        @include('admin.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">قوالب Flaxxa (للمتغيرات والمعاينة)</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.flaxxa-wapi.messages.index') }}">Flaxxa</a></li>
                        <li class="breadcrumb-item active">القوالب</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <form action="{{ route('admin.flaxxa-wapi.templates.sync') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="ri-refresh-line me-1"></i> مزامنة من Meta عبر Flaxxa
                    </button>
                </form>
                <a href="{{ route('admin.flaxxa-wapi.templates.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> إضافة قالب يدوي</a>
            </div>
        </div>

        @include('admin.pages.flaxxa-wapi._nav')

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" class="row g-2">
                    <div class="col-md-8">
                        <input type="text" name="search" class="form-control" placeholder="بحث بالاسم أو اللغة..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">بحث</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card custom-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>الاسم</th>
                                <th>اللغة</th>
                                <th>الحالة</th>
                                <th>التصنيف</th>
                                <th>متغيرات (رأس / نص)</th>
                                <th>رأس وسائط</th>
                                <th>المصدر</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($templates as $tpl)
                                @php
                                    $st = $tpl->structure ?? [];
                                    $source = $st['source'] ?? 'manual';
                                    $status = $st['status'] ?? null;
                                @endphp
                                <tr>
                                    <td>{{ $tpl->id }}</td>
                                    <td><strong>{{ $tpl->name }}</strong>
                                        @if($tpl->provider_template_id)
                                            <br><small class="text-muted"><code>{{ $tpl->provider_template_id }}</code></small>
                                        @endif
                                    </td>
                                    <td>{{ $tpl->language }}</td>
                                    <td>
                                        @if($status === 'APPROVED')
                                            <span class="badge bg-success">{{ $status }}</span>
                                        @elseif($status)
                                            <span class="badge bg-secondary">{{ $status }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $st['category'] ?? '—' }}</td>
                                    <td>
                                        {{ (int)($st['header_placeholders'] ?? 0) }} / {{ (int)($st['body_placeholders'] ?? 0) }}
                                    </td>
                                    <td>
                                        @if(!empty($st['has_media_header']))
                                            <span class="badge bg-info">{{ $st['header_format'] ?? 'MEDIA' }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($source === 'provider')
                                            <span class="badge bg-primary-transparent">مزامن من Meta</span>
                                        @else
                                            <span class="badge bg-warning-transparent">يدوي</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.flaxxa-wapi.templates.edit', $tpl) }}" class="btn btn-sm btn-outline-primary">تعديل</a>
                                        <form action="{{ route('admin.flaxxa-wapi.templates.destroy', $tpl) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف هذا القالب؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-muted py-4">لا توجد قوالب بعد. اضغط "مزامنة من Meta عبر Flaxxa" لجلب القوالب المعتمَدة.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3">{{ $templates->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
