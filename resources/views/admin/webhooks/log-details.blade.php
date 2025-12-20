@extends('admin.layouts.master')

@section('page-title')
تفاصيل السجل #{{ $log->id }}
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">تفاصيل السجل #{{ $log->id }}</h1>
            <a href="{{ route('admin.webhooks.logs') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-right"></i> العودة للسجلات
            </a>
        </div>

        <div class="row">
            <!-- Main Information -->
            <div class="col-lg-8 mb-4">
                <!-- Basic Info Card -->
                <div class="card shadow mb-4 border-left-{{ $log->status === 'failed' ? 'danger' : ($log->status === 'processed' ? 'success' : 'info') }}">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">المعلومات الأساسية</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>المصدر:</strong>
                                <div class="mt-2">
                                    <span class="badge badge-secondary badge-lg">{{ strtoupper($log->source) }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <strong>الحالة:</strong>
                                <div class="mt-2">
                                    @switch($log->status)
                                        @case('received')
                                            <span class="badge badge-info badge-lg">
                                                <i class="fas fa-inbox"></i> مستلم
                                            </span>
                                        @break
                                        @case('processed')
                                            <span class="badge badge-success badge-lg">
                                                <i class="fas fa-check-circle"></i> معالج بنجاح
                                            </span>
                                        @break
                                        @case('failed')
                                            <span class="badge badge-danger badge-lg">
                                                <i class="fas fa-exclamation-triangle"></i> فشل
                                            </span>
                                        @break
                                    @endswitch
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong><i class="fas fa-network-wired text-muted"></i> IP Address:</strong>
                                <div class="mt-1">
                                    <code class="bg-light p-2 d-inline-block">{{ $log->ip_address }}</code>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong><i class="fas fa-globe text-muted"></i> User Agent:</strong>
                                <div class="mt-1">
                                    <small class="text-muted">{{ $log->user_agent ?? '-' }}</small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong><i class="fas fa-calendar text-muted"></i> تاريخ الاستلام:</strong>
                                <div class="mt-1">
                                    {{ $log->created_at->format('Y-m-d H:i:s') }}
                                    <br>
                                    <small class="text-muted">({{ $log->created_at->diffForHumans() }})</small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong><i class="fas fa-fingerprint text-muted"></i> رقم السجل:</strong>
                                <div class="mt-1">
                                    <code class="bg-light p-2 d-inline-block">#{{ $log->id }}</code>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-2">
                            <strong><i class="fas fa-comment text-muted"></i> الرسالة:</strong>
                            <div class="mt-2 alert alert-{{ $log->status === 'failed' ? 'danger' : 'info' }}">
                                {{ $log->message }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error Message Card (if failed) -->
                @if($log->status === 'failed' && $log->error_message)
                    <div class="card shadow mb-4 border-left-danger">
                        <div class="card-header py-3 bg-danger text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-exclamation-triangle"></i> رسالة الخطأ
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-danger mb-0">
                                <pre class="mb-0">{{ $log->error_message }}</pre>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Payload Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-file-code"></i> البيانات المرسلة (Payload)
                        </h6>
                        <button class="btn btn-sm btn-secondary" onclick="copyToClipboard('payload')">
                            <i class="fas fa-copy"></i> نسخ
                        </button>
                    </div>
                    <div class="card-body">
                        @if(!empty($log->payload))
                            <pre id="payload" class="bg-light p-3 rounded mb-0" style="max-height: 400px; overflow-y: auto;"><code>{{ json_encode($log->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</code></pre>
                        @else
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle"></i> لا توجد بيانات Payload
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Headers Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-server"></i> HTTP Headers
                        </h6>
                        <button class="btn btn-sm btn-secondary" onclick="copyToClipboard('headers')">
                            <i class="fas fa-copy"></i> نسخ
                        </button>
                    </div>
                    <div class="card-body">
                        @if(!empty($log->headers))
                            <pre id="headers" class="bg-light p-3 rounded mb-0" style="max-height: 300px; overflow-y: auto;"><code>{{ json_encode($log->headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</code></pre>
                        @else
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle"></i> لا توجد Headers محفوظة
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Response Card (if exists) -->
                @if($log->response_data)
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-reply"></i> الاستجابة (Response)
                            </h6>
                        </div>
                        <div class="card-body">
                            <pre class="bg-light p-3 rounded mb-0" style="max-height: 300px; overflow-y: auto;"><code>{{ json_encode($log->response_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</code></pre>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Quick Stats Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle"></i> معلومات سريعة
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-sm font-weight-bold">رقم السجل:</span>
                                <span class="badge badge-primary">#{{ $log->id }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-sm font-weight-bold">المصدر:</span>
                                <span class="badge badge-secondary">{{ strtoupper($log->source) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-sm font-weight-bold">الحالة:</span>
                                @if($log->status === 'failed')
                                    <span class="badge badge-danger">فشل</span>
                                @elseif($log->status === 'processed')
                                    <span class="badge badge-success">معالج</span>
                                @else
                                    <span class="badge badge-info">مستلم</span>
                                @endif
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-sm font-weight-bold">التاريخ:</span>
                                <span class="text-muted small">{{ $log->created_at->format('Y-m-d') }}</span>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <strong class="text-sm">IP Address:</strong>
                            <div class="mt-1">
                                <code class="small">{{ $log->ip_address }}</code>
                            </div>
                        </div>

                        @if($log->user_agent)
                            <div class="mb-3">
                                <strong class="text-sm">User Agent:</strong>
                                <div class="mt-1">
                                    <small class="text-muted" style="word-break: break-all;">{{ Str::limit($log->user_agent, 80) }}</small>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Timeline Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-clock"></i> المخطط الزمني
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">استلام الطلب</h6>
                                    <small class="text-muted">{{ $log->created_at->format('Y-m-d H:i:s') }}</small>
                                </div>
                            </div>
                            @if($log->status === 'processed')
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">معالجة ناجحة</h6>
                                        <small class="text-muted">{{ $log->updated_at->format('Y-m-d H:i:s') }}</small>
                                    </div>
                                </div>
                            @elseif($log->status === 'failed')
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-danger"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">فشل المعالجة</h6>
                                        <small class="text-muted">{{ $log->updated_at->format('Y-m-d H:i:s') }}</small>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-cog"></i> إجراءات
                        </h6>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('admin.webhooks.logs') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-list"></i> جميع السجلات
                        </a>
                        <a href="{{ route('admin.webhooks.logs', ['source' => $log->source]) }}" class="btn btn-info btn-block">
                            <i class="fas fa-filter"></i> سجلات {{ strtoupper($log->source) }}
                        </a>
                        <a href="{{ route('admin.webhooks.index') }}" class="btn btn-primary btn-block">
                            <i class="fas fa-tachometer-alt"></i> لوحة التحكم
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End::app-content -->
@endsection

@push('styles')
<style>
    .badge-lg {
        font-size: 0.9rem;
        padding: 0.5rem 0.75rem;
    }

    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }

    .timeline-item:not(:last-child):before {
        content: '';
        position: absolute;
        left: -23px;
        top: 15px;
        width: 2px;
        height: 100%;
        background: #e3e6f0;
    }

    .timeline-marker {
        position: absolute;
        left: -28px;
        top: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid #fff;
    }

    .timeline-content h6 {
        font-size: 0.9rem;
        color: #5a5c69;
    }
</style>
@endpush

@push('scripts')
<script>
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    const text = element.textContent;

    navigator.clipboard.writeText(text).then(function() {
        // Show success message
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> تم النسخ!';
        btn.classList.remove('btn-secondary');
        btn.classList.add('btn-success');

        setTimeout(function() {
            btn.innerHTML = originalHtml;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-secondary');
        }, 2000);
    }, function(err) {
        console.error('Failed to copy: ', err);
        alert('فشل النسخ إلى الحافظة');
    });
}
</script>
@endpush
