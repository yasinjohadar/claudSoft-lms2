@extends('admin.layouts.master')

@section('page-title')
    الإحصائيات - n8n Integration
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
                    <i class="fas fa-chart-bar me-2"></i>إحصائيات n8n Integration
                </h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.n8n.index') }}">n8n Integration</a></li>
                        <li class="breadcrumb-item active">الإحصائيات</li>
                    </ol>
                </nav>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('admin.n8n.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right me-1"></i>رجوع
                </a>
            </div>
        </div>

        <!-- Overall Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-network-wired fa-2x text-primary mb-2"></i>
                        <h3 class="mb-0">{{ $stats['endpoints']['total'] }}</h3>
                        <p class="text-muted mb-0">نقاط النهاية</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-paper-plane fa-2x text-info mb-2"></i>
                        <h3 class="mb-0">{{ $stats['logs']['total'] }}</h3>
                        <p class="text-muted mb-0">إجمالي السجلات</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <h3 class="mb-0">{{ $stats['logs']['sent'] }}</h3>
                        <p class="text-muted mb-0">نجحت</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                        <h3 class="mb-0">{{ $stats['logs']['failed'] }}</h3>
                        <p class="text-muted mb-0">فشلت</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Performing Endpoints -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">أكثر نقاط النهاية استخداماً</h5>
                    </div>
                    <div class="card-body">
                        @if($topEndpoints->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>نقطة النهاية</th>
                                        <th>إجمالي الطلبات</th>
                                        <th>نجحت</th>
                                        <th>فشلت</th>
                                        <th>معدل النجاح</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topEndpoints as $endpoint)
                                    @php
                                        $successRate = $endpoint->logs_count > 0
                                            ? round(($endpoint->successful_logs / $endpoint->logs_count) * 100, 1)
                                            : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.n8n.endpoints.show', $endpoint) }}">
                                                {{ $endpoint->name }}
                                            </a>
                                        </td>
                                        <td><span class="badge bg-primary">{{ $endpoint->logs_count }}</span></td>
                                        <td><span class="badge bg-success">{{ $endpoint->successful_logs }}</span></td>
                                        <td><span class="badge bg-danger">{{ $endpoint->failed_logs }}</span></td>
                                        <td>
                                            <div class="progress" style="height: 20px; min-width: 100px;">
                                                <div class="progress-bar bg-success" role="progressbar"
                                                     style="width: {{ $successRate }}%">
                                                    {{ $successRate }}%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <p class="text-muted text-center py-3">لا توجد بيانات كافية</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
