@extends('admin.layouts.master')

@section('page-title')
    لوحة تحكم n8n Integration
@stop

@section('styles')
<style>
.border-left-primary {
    border-left: 4px solid #4e73df !important;
}
.border-left-success {
    border-left: 4px solid #1cc88a !important;
}
.border-left-info {
    border-left: 4px solid #36b9cc !important;
}
.border-left-danger {
    border-left: 4px solid #e74a3b !important;
}
.badge-secondary {
    background-color: #858796;
}
</style>
@endsection

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- Alerts -->
        @include('admin.components.alerts')

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">
                    <i class="fas fa-plug me-2"></i>لوحة تحكم n8n Integration
                </h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);">التكاملات</a></li>
                        <li class="breadcrumb-item active">n8n Integration</li>
                    </ol>
                </nav>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('admin.n8n.documentation') }}" class="btn btn-outline-info me-2">
                    <i class="fas fa-book me-1"></i>التوثيق
                </a>
                <a href="{{ route('admin.n8n.statistics') }}" class="btn btn-primary">
                    <i class="fas fa-chart-bar me-1"></i>الإحصائيات
                </a>
            </div>
        </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <!-- Endpoints Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                نقاط النهاية
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['endpoints']['total'] }}
                            </div>
                            <div class="mt-2 text-xs">
                                <span class="text-success">{{ $stats['endpoints']['active'] }} فعالة</span> |
                                <span class="text-secondary">{{ $stats['endpoints']['inactive'] }} معطلة</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-network-wired fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Logs Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                إجمالي السجلات
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['logs']['total']) }}
                            </div>
                            <div class="mt-2 text-xs">
                                <span class="text-info">اليوم: {{ $stats['recent']['today'] }}</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Successful Logs Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                نجحت
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['logs']['sent']) }}
                            </div>
                            @php
                                $successRate = $stats['logs']['total'] > 0
                                    ? round(($stats['logs']['sent'] / $stats['logs']['total']) * 100, 1)
                                    : 0;
                            @endphp
                            <div class="mt-2">
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                         style="width: {{ $successRate }}%"></div>
                                </div>
                                <small class="text-muted">{{ $successRate }}% معدل النجاح</small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Failed Logs Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                فشلت
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['logs']['failed']) }}
                            </div>
                            <div class="mt-2 text-xs">
                                <span class="text-warning">معلقة: {{ $stats['logs']['pending'] }}</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Logs -->
        <div class="col-xl-8 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">أحدث السجلات</h6>
                    <a href="{{ route('admin.n8n.logs.index') }}" class="btn btn-sm btn-primary">
                        عرض الكل
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>نقطة النهاية</th>
                                    <th>نوع الحدث</th>
                                    <th>الحالة</th>
                                    <th>التاريخ</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentLogs as $log)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.n8n.endpoints.show', $log->endpoint) }}">
                                            {{ $log->endpoint->name }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $log->event_type }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $log->status_color }}">
                                            {{ ucfirst($log->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ $log->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.n8n.logs.show', $log) }}"
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($log->status === 'failed' && $log->canRetry())
                                        <form action="{{ route('admin.n8n.logs.retry', $log) }}"
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        لا توجد سجلات بعد
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Endpoints -->
        <div class="col-xl-4 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">نقاط النهاية الفعالة</h6>
                    <a href="{{ route('admin.n8n.endpoints.create') }}" class="btn btn-sm btn-success">
                        <i class="fas fa-plus"></i> جديد
                    </a>
                </div>
                <div class="card-body">
                    @forelse($activeEndpoints as $endpoint)
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">
                                    <a href="{{ route('admin.n8n.endpoints.show', $endpoint) }}">
                                        {{ $endpoint->name }}
                                    </a>
                                </h6>
                                <small class="text-muted">{{ $endpoint->event_type }}</small>
                            </div>
                            <span class="badge badge-success">فعال</span>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-paper-plane"></i> {{ $endpoint->logs_count }} طلب
                            </small>
                        </div>
                    </div>
                    @empty
                    <p class="text-center text-muted">لا توجد نقاط نهاية فعالة</p>
                    @endforelse

                    <div class="text-center mt-3">
                        <a href="{{ route('admin.n8n.endpoints.index') }}" class="btn btn-sm btn-outline-primary">
                            عرض جميع النقاط
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">إجراءات سريعة</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.n8n.endpoints.index') }}" class="text-decoration-none">
                                <div class="p-3 bg-light rounded">
                                    <i class="fas fa-network-wired fa-3x text-primary mb-2"></i>
                                    <h6>إدارة النقاط</h6>
                                    <small class="text-muted">تكوين webhooks الصادرة</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.n8n.handlers.index') }}" class="text-decoration-none">
                                <div class="p-3 bg-light rounded">
                                    <i class="fas fa-cogs fa-3x text-success mb-2"></i>
                                    <h6>المعالجات</h6>
                                    <small class="text-muted">إدارة webhooks الواردة</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.n8n.logs.index') }}" class="text-decoration-none">
                                <div class="p-3 bg-light rounded">
                                    <i class="fas fa-clipboard-list fa-3x text-info mb-2"></i>
                                    <h6>السجلات</h6>
                                    <small class="text-muted">مراقبة جميع الطلبات</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.n8n.documentation') }}" class="text-decoration-none">
                                <div class="p-3 bg-light rounded">
                                    <i class="fas fa-book fa-3x text-warning mb-2"></i>
                                    <h6>التوثيق</h6>
                                    <small class="text-muted">دليل الاستخدام</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>
</div>
@endsection
