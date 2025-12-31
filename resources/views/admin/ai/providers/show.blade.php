@extends('admin.layouts.master')

@section('page-title')
    تفاصيل مقدم الخدمة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تفاصيل مقدم الخدمة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.ai.providers.index') }}">مقدمي الخدمة</a></li>
                            <li class="breadcrumb-item active">تفاصيل</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('admin.ai.providers.edit', $provider) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>تعديل
                    </a>
                </div>
            </div>

            <!-- Provider Info -->
            <div class="row">
                <div class="col-xl-8">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">معلومات مقدم الخدمة</div>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="200">الاسم:</th>
                                    <td>{{ $provider->name }}</td>
                                </tr>
                                <tr>
                                    <th>النوع:</th>
                                    <td><span class="badge bg-info">{{ $provider->type }}</span></td>
                                </tr>
                                <tr>
                                    <th>اسم النموذج:</th>
                                    <td>{{ $provider->model_name }}</td>
                                </tr>
                                <tr>
                                    <th>API URL:</th>
                                    <td>{{ $provider->api_url ?? 'افتراضي' }}</td>
                                </tr>
                                <tr>
                                    <th>الحالة:</th>
                                    <td>
                                        @if($provider->is_active)
                                            <span class="badge bg-success">نشط</span>
                                        @else
                                            <span class="badge bg-danger">غير نشط</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>افتراضي:</th>
                                    <td>
                                        @if($provider->is_default)
                                            <span class="badge bg-primary">نعم</span>
                                        @else
                                            <span class="badge bg-secondary">لا</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>الأولوية:</th>
                                    <td>{{ $provider->priority }}</td>
                                </tr>
                                <tr>
                                    <th>تاريخ الإنشاء:</th>
                                    <td>{{ $provider->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">الإحصائيات</div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <p class="mb-1 text-muted">إجمالي الطلبات</p>
                                <h4 class="mb-0">{{ $stats['total_requests'] ?? 0 }}</h4>
                            </div>
                            <div class="mb-3">
                                <p class="mb-1 text-muted">الطلبات المكتملة</p>
                                <h4 class="mb-0 text-success">{{ $stats['completed_requests'] ?? 0 }}</h4>
                            </div>
                            <div class="mb-3">
                                <p class="mb-1 text-muted">الطلبات الفاشلة</p>
                                <h4 class="mb-0 text-danger">{{ $stats['failed_requests'] ?? 0 }}</h4>
                            </div>
                            <div class="mb-3">
                                <p class="mb-1 text-muted">إجمالي Tokens</p>
                                <h4 class="mb-0">{{ number_format($stats['total_tokens'] ?? 0) }}</h4>
                            </div>
                            <div class="mb-3">
                                <p class="mb-1 text-muted">إجمالي التكلفة</p>
                                <h4 class="mb-0">${{ number_format($stats['total_cost'] ?? 0, 4) }}</h4>
                            </div>
                            <div class="mb-3">
                                <p class="mb-1 text-muted">متوسط وقت الاستجابة</p>
                                <h4 class="mb-0">{{ number_format($stats['average_response_time'] ?? 0, 2) }} ms</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

