@extends('admin.layouts.master')

@section('page-title')
    المعالجات - n8n Integration
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- Alerts -->
        @include('admin.components.alerts')

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">
                    <i class="fas fa-cogs me-2"></i>المعالجات (Handlers)
                </h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.n8n.index') }}">n8n Integration</a></li>
                        <li class="breadcrumb-item active">المعالجات</li>
                    </ol>
                </nav>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('admin.n8n.index') }}" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-arrow-right me-1"></i>رجوع
                </a>
                <a href="{{ route('admin.n8n.handlers.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>إضافة معالج جديد
                </a>
            </div>
        </div>

        <!-- Handlers Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">جميع المعالجات</h5>
                        <span class="badge bg-primary">{{ $handlers->total() }} معالج</span>
                    </div>
                    <div class="card-body">
                        @if($handlers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="20%">نوع المعالج</th>
                                        <th width="30%">فئة المعالج</th>
                                        <th width="25%">الوصف</th>
                                        <th width="10%">الحالة</th>
                                        <th width="10%">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($handlers as $handler)
                                    <tr>
                                        <td>{{ $handler->id }}</td>
                                        <td><span class="badge bg-info">{{ $handler->handler_type }}</span></td>
                                        <td><code>{{ $handler->handler_class }}</code></td>
                                        <td><small>{{ Str::limit($handler->description, 60) }}</small></td>
                                        <td>
                                            @if($handler->is_active)
                                                <span class="badge bg-success">فعال</span>
                                            @else
                                                <span class="badge bg-secondary">معطل</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.n8n.handlers.show', $handler) }}"
                                                   class="btn btn-info" title="عرض">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.n8n.handlers.edit', $handler) }}"
                                                   class="btn btn-warning" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.n8n.handlers.toggle', $handler) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn {{ $handler->is_active ? 'btn-secondary' : 'btn-success' }}"
                                                            title="{{ $handler->is_active ? 'تعطيل' : 'تفعيل' }}">
                                                        <i class="fas fa-power-off"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-3">
                            {{ $handlers->links() }}
                        </div>
                        @else
                        <div class="text-center py-5">
                            <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">لا توجد معالجات</h5>
                            <p class="text-muted">قم بإضافة معالج جديد لاستقبال webhooks من n8n</p>
                            <a href="{{ route('admin.n8n.handlers.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>إضافة معالج
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
