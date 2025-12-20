@extends('admin.layouts.master')

@section('page-title')
    نقاط النهاية - n8n Integration
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
                    <i class="fas fa-network-wired me-2"></i>نقاط النهاية (Endpoints)
                </h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.n8n.index') }}">n8n Integration</a></li>
                        <li class="breadcrumb-item active">نقاط النهاية</li>
                    </ol>
                </nav>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('admin.n8n.index') }}" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-arrow-right me-1"></i>رجوع
                </a>
                <a href="{{ route('admin.n8n.endpoints.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>إضافة نقطة نهاية جديدة
                </a>
            </div>
        </div>

        <!-- Endpoints Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">جميع نقاط النهاية</h5>
                        <span class="badge bg-primary">{{ $endpoints->total() }} نقطة</span>
                    </div>
                    <div class="card-body">
                        @if($endpoints->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="20%">الاسم</th>
                                        <th width="15%">نوع الحدث</th>
                                        <th width="25%">URL</th>
                                        <th width="10%">الحالة</th>
                                        <th width="10%">عدد السجلات</th>
                                        <th width="15%">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($endpoints as $endpoint)
                                    <tr>
                                        <td>{{ $endpoint->id }}</td>
                                        <td>
                                            <strong>{{ $endpoint->name }}</strong>
                                            @if($endpoint->description)
                                            <br><small class="text-muted">{{ Str::limit($endpoint->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $endpoint->event_type }}</span>
                                        </td>
                                        <td>
                                            <small class="text-muted" style="word-break: break-all;">
                                                {{ Str::limit($endpoint->url, 40) }}
                                            </small>
                                        </td>
                                        <td>
                                            @if($endpoint->is_active)
                                                <span class="badge bg-success">فعال</span>
                                            @else
                                                <span class="badge bg-secondary">معطل</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $endpoint->logs_count }}</span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.n8n.endpoints.show', $endpoint) }}"
                                                   class="btn btn-info" title="عرض">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.n8n.endpoints.edit', $endpoint) }}"
                                                   class="btn btn-warning" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.n8n.endpoints.toggle', $endpoint) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn {{ $endpoint->is_active ? 'btn-secondary' : 'btn-success' }}"
                                                            title="{{ $endpoint->is_active ? 'تعطيل' : 'تفعيل' }}">
                                                        <i class="fas fa-power-off"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.n8n.endpoints.test', $endpoint) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary" title="اختبار">
                                                        <i class="fas fa-vial"></i>
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
                            {{ $endpoints->links() }}
                        </div>
                        @else
                        <div class="text-center py-5">
                            <i class="fas fa-network-wired fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">لا توجد نقاط نهاية</h5>
                            <p class="text-muted">قم بإضافة نقطة نهاية جديدة للبدء في إرسال webhooks إلى n8n</p>
                            <a href="{{ route('admin.n8n.endpoints.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>إضافة نقطة نهاية
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Events Info -->
        @if(count($availableEvents) > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">أنواع الأحداث المتاحة</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($availableEvents as $key => $label)
                            <div class="col-md-4 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-circle text-primary me-2" style="font-size: 8px;"></i>
                                    <div>
                                        <strong>{{ $key }}</strong>
                                        <br><small class="text-muted">{{ $label }}</small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
