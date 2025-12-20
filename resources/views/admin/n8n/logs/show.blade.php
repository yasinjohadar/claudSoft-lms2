@extends('admin.layouts.master')

@section('page-title')
    تفاصيل السجل #{{ $log->id }}
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
                    <i class="fas fa-file-alt me-2"></i>سجل Webhook #{{ $log->id }}
                </h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.n8n.index') }}">n8n Integration</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.n8n.logs.index') }}">السجلات</a></li>
                        <li class="breadcrumb-item active">{{ $log->id }}</li>
                    </ol>
                </nav>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('admin.n8n.logs.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right me-1"></i>رجوع
                </a>
                @if($log->status === 'failed' && $log->canRetry())
                <form action="{{ route('admin.n8n.logs.retry', $log) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-redo me-1"></i>إعادة المحاولة
                    </button>
                </form>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Log Details -->
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">تفاصيل السجل</h5>
                        <span class="badge bg-{{ $log->status_color }}">{{ ucfirst($log->status) }}</span>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">نقطة النهاية:</th>
                                <td>
                                    <a href="{{ route('admin.n8n.endpoints.show', $log->endpoint) }}">
                                        {{ $log->endpoint->name }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>نوع الحدث:</th>
                                <td><code>{{ $log->event_type }}</code></td>
                            </tr>
                            <tr>
                                <th>الحالة:</th>
                                <td><span class="badge bg-{{ $log->status_color }}">{{ $log->status }}</span></td>
                            </tr>
                            <tr>
                                <th>HTTP Status Code:</th>
                                <td>
                                    @if($log->response_status)
                                        <span class="badge bg-{{ $log->response_status >= 200 && $log->response_status < 300 ? 'success' : 'danger' }}">
                                            {{ $log->response_status }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>رقم المحاولة:</th>
                                <td>{{ $log->attempt_number }} / {{ $log->endpoint->retry_attempts }}</td>
                            </tr>
                            <tr>
                                <th>تاريخ الإنشاء:</th>
                                <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>آخر تحديث:</th>
                                <td>{{ $log->updated_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Payload -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">البيانات المُرسلة (Payload)</h5>
                    </div>
                    <div class="card-body">
                        <pre class="mb-0">{{ json_encode($log->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>

                <!-- Response -->
                @if($log->response_body)
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">الاستجابة (Response)</h5>
                    </div>
                    <div class="card-body">
                        <pre class="mb-0">{{ is_array($log->response_body) ? json_encode($log->response_body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $log->response_body }}</pre>
                    </div>
                </div>
                @endif

                <!-- Error Message -->
                @if($log->error_message)
                <div class="card mb-3">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title mb-0">رسالة الخطأ</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0 text-danger">{{ $log->error_message }}</p>
                    </div>
                </div>
                @endif
            </div>

            <div class="col-lg-4">
                <!-- Quick Info -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">معلومات سريعة</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted">نوع الحدث</small>
                            <p class="mb-0"><code>{{ $log->event_type }}</code></p>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">الحالة</small>
                            <p class="mb-0"><span class="badge bg-{{ $log->status_color }}">{{ $log->status }}</span></p>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">محاولات الإرسال</small>
                            <p class="mb-0">{{ $log->attempt_number }} / {{ $log->endpoint->retry_attempts }}</p>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">منذ</small>
                            <p class="mb-0">{{ $log->created_at->diffForHumans() }}</p>
                        </div>
                        @if($log->canRetry())
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            يمكن إعادة محاولة إرسال هذا الطلب
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
