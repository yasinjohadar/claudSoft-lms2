@extends('admin.layouts.master')

@section('page-title')
    {{ $endpoint->name }} - نقطة النهاية
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
                    <i class="fas fa-network-wired me-2"></i>{{ $endpoint->name }}
                </h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.n8n.index') }}">n8n Integration</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.n8n.endpoints.index') }}">نقاط النهاية</a></li>
                        <li class="breadcrumb-item active">{{ $endpoint->name }}</li>
                    </ol>
                </nav>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('admin.n8n.endpoints.index') }}" class="btn btn-outline-secondary me-1">
                    <i class="fas fa-arrow-right me-1"></i>رجوع
                </a>
                <a href="{{ route('admin.n8n.endpoints.edit', $endpoint) }}" class="btn btn-warning me-1">
                    <i class="fas fa-edit me-1"></i>تعديل
                </a>
                <form action="{{ route('admin.n8n.endpoints.test', $endpoint) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-vial me-1"></i>اختبار
                    </button>
                </form>
            </div>
        </div>

        <div class="row">
            <!-- Endpoint Details -->
            <div class="col-xl-8">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">تفاصيل نقطة النهاية</h5>
                        @if($endpoint->is_active)
                            <span class="badge bg-success">فعال</span>
                        @else
                            <span class="badge bg-secondary">معطل</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">الاسم:</th>
                                <td>{{ $endpoint->name }}</td>
                            </tr>
                            <tr>
                                <th>نوع الحدث:</th>
                                <td><span class="badge bg-info">{{ $endpoint->event_type }}</span></td>
                            </tr>
                            <tr>
                                <th>URL:</th>
                                <td><code class="text-break">{{ $endpoint->url }}</code></td>
                            </tr>
                            @if($endpoint->description)
                            <tr>
                                <th>الوصف:</th>
                                <td>{{ $endpoint->description }}</td>
                            </tr>
                            @endif
                            <tr>
                                <th>عدد محاولات الإعادة:</th>
                                <td>{{ $endpoint->retry_attempts }}</td>
                            </tr>
                            <tr>
                                <th>المهلة (Timeout):</th>
                                <td>{{ $endpoint->timeout }} ثانية</td>
                            </tr>
                            @if($endpoint->headers)
                            <tr>
                                <th>Custom Headers:</th>
                                <td><pre class="mb-0">{{ json_encode($endpoint->headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></td>
                            </tr>
                            @endif
                            @if($endpoint->metadata)
                            <tr>
                                <th>Metadata:</th>
                                <td><pre class="mb-0">{{ json_encode($endpoint->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></td>
                            </tr>
                            @endif
                            <tr>
                                <th>تاريخ الإنشاء:</th>
                                <td>{{ $endpoint->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>آخر تحديث:</th>
                                <td>{{ $endpoint->updated_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Recent Logs -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">آخر السجلات</h5>
                    </div>
                    <div class="card-body">
                        @if($endpoint->logs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>التاريخ</th>
                                        <th>الحالة</th>
                                        <th>HTTP Code</th>
                                        <th>المحاولة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($endpoint->logs as $log)
                                    <tr>
                                        <td><small>{{ $log->created_at->diffForHumans() }}</small></td>
                                        <td>
                                            <span class="badge bg-{{ $log->status_color }}">
                                                {{ ucfirst($log->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $log->response_status ?? '-' }}</td>
                                        <td>{{ $log->attempt_number }}</td>
                                        <td>
                                            <a href="{{ route('admin.n8n.logs.show', $log) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <a href="{{ route('admin.n8n.logs.index', ['endpoint_id' => $endpoint->id]) }}" class="btn btn-sm btn-outline-primary">
                            عرض جميع السجلات
                        </a>
                        @else
                        <p class="text-muted text-center py-3">لا توجد سجلات بعد</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="col-xl-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">الإحصائيات</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>إجمالي السجلات:</span>
                                <strong class="badge bg-primary">{{ $stats['total'] ?? 0 }}</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>نجحت:</span>
                                <strong class="badge bg-success">{{ $stats['sent'] ?? 0 }}</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>فشلت:</span>
                                <strong class="badge bg-danger">{{ $stats['failed'] ?? 0 }}</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>معلقة:</span>
                                <strong class="badge bg-warning">{{ $stats['pending'] ?? 0 }}</strong>
                            </div>
                        </div>

                        @php
                            $successRate = isset($stats['total']) && $stats['total'] > 0
                                ? round(($stats['sent'] / $stats['total']) * 100, 1)
                                : 0;
                        @endphp

                        <div class="mt-3">
                            <label class="mb-2">معدل النجاح: <strong>{{ $successRate }}%</strong></label>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-success" role="progressbar"
                                     style="width: {{ $successRate }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">الإجراءات</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.n8n.endpoints.toggle', $endpoint) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-{{ $endpoint->is_active ? 'secondary' : 'success' }} w-100">
                                <i class="fas fa-power-off me-1"></i>
                                {{ $endpoint->is_active ? 'تعطيل' : 'تفعيل' }} نقطة النهاية
                            </button>
                        </form>

                        <form action="{{ route('admin.n8n.endpoints.destroy', $endpoint) }}" method="POST"
                              onsubmit="return confirm('هل أنت متأكد من حذف هذه نقطة النهاية؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash me-1"></i>حذف نقطة النهاية
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
