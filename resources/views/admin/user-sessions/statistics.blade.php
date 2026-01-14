@extends('admin.layouts.master')

@section('page-title')
    إحصائيات الجلسات
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إحصائيات الجلسات</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.user-sessions.index') }}">جلسات المستخدمين</a></li>
                        <li class="breadcrumb-item active">الإحصائيات</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('admin.user-sessions.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right me-1"></i>العودة
                </a>
            </div>
        </div>

        <!-- Date Range Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.user-sessions.statistics') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">من تاريخ</label>
                            <input type="date" name="date_from" class="form-control" 
                                   value="{{ $dateFrom->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">إلى تاريخ</label>
                            <input type="date" name="date_to" class="form-control" 
                                   value="{{ $dateTo->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i>تطبيق
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- General Statistics -->
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">إجمالي الجلسات</p>
                                <h4 class="mb-0">{{ number_format($stats['total_sessions']) }}</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-md bg-primary-transparent rounded-circle">
                                    <i class="fas fa-list fs-18"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">الجلسات النشطة</p>
                                <h4 class="mb-0 text-success">{{ number_format($stats['active_sessions']) }}</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-md bg-success-transparent rounded-circle">
                                    <i class="fas fa-circle fs-18"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">الجلسات المكتملة</p>
                                <h4 class="mb-0 text-info">{{ number_format($stats['completed_sessions']) }}</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-md bg-info-transparent rounded-circle">
                                    <i class="fas fa-check-circle fs-18"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">متوسط المدة</p>
                                <h4 class="mb-0">
                                    @if($stats['avg_duration'])
                                        {{ gmdate('H:i:s', (int)$stats['avg_duration']) }}
                                    @else
                                        -
                                    @endif
                                </h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-md bg-warning-transparent rounded-circle">
                                    <i class="fas fa-clock fs-18"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Distribution Charts -->
        <div class="row">
            <!-- Device Statistics -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-mobile-alt me-2"></i>توزيع الأجهزة
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($deviceStats->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>نوع الجهاز</th>
                                            <th>العدد</th>
                                            <th>النسبة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($deviceStats as $device)
                                            @php
                                                $percentage = ($device->count / $stats['total_sessions']) * 100;
                                            @endphp
                                            <tr>
                                                <td>{{ ucfirst($device->device_type) }}</td>
                                                <td>{{ $device->count }}</td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar" role="progressbar" 
                                                             style="width: {{ $percentage }}%">
                                                            {{ number_format($percentage, 1) }}%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted text-center">لا توجد بيانات</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Browser Statistics -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-globe me-2"></i>توزيع المتصفحات
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($browserStats->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>المتصفح</th>
                                            <th>العدد</th>
                                            <th>النسبة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($browserStats as $browser)
                                            @php
                                                $percentage = ($browser->count / $stats['total_sessions']) * 100;
                                            @endphp
                                            <tr>
                                                <td>{{ $browser->browser }}</td>
                                                <td>{{ $browser->count }}</td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-info" role="progressbar" 
                                                             style="width: {{ $percentage }}%">
                                                            {{ number_format($percentage, 1) }}%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted text-center">لا توجد بيانات</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Connection Type Statistics -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-wifi me-2"></i>أنواع الاتصال
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($connectionStats->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>نوع الاتصال</th>
                                            <th>العدد</th>
                                            <th>النسبة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($connectionStats as $connection)
                                            @php
                                                $percentage = ($connection->count / $stats['total_sessions']) * 100;
                                            @endphp
                                            <tr>
                                                <td>{{ ucfirst($connection->connection_type) }}</td>
                                                <td>{{ $connection->count }}</td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-success" role="progressbar" 
                                                             style="width: {{ $percentage }}%">
                                                            {{ number_format($percentage, 1) }}%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted text-center">لا توجد بيانات</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Status Distribution -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-pie me-2"></i>توزيع الحالات
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($statusStats->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>الحالة</th>
                                            <th>العدد</th>
                                            <th>النسبة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($statusStats as $status => $count)
                                            @php
                                                $percentage = ($count / $stats['total_sessions']) * 100;
                                                $statusLabels = [
                                                    'active' => 'نشطة',
                                                    'completed' => 'مكتملة',
                                                    'disconnected' => 'منفصلة',
                                                    'timeout' => 'انتهت',
                                                ];
                                            @endphp
                                            <tr>
                                                <td>{{ $statusLabels[$status] ?? $status }}</td>
                                                <td>{{ $count }}</td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-warning" role="progressbar" 
                                                             style="width: {{ $percentage }}%">
                                                            {{ number_format($percentage, 1) }}%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted text-center">لا توجد بيانات</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Users -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users me-2"></i>أكثر المستخدمين نشاطاً
                </h5>
            </div>
            <div class="card-body">
                @if($topUsers->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المستخدم</th>
                                    <th>عدد الجلسات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topUsers as $index => $topUser)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($topUser->user)
                                                    @if($topUser->user->avatar)
                                                        <img src="{{ asset('storage/' . $topUser->user->avatar) }}" 
                                                             alt="{{ $topUser->user->name }}" 
                                                             class="avatar avatar-sm rounded-circle me-2">
                                                    @else
                                                        <div class="avatar avatar-sm rounded-circle bg-primary-transparent me-2">
                                                            <span class="fw-bold">{{ substr($topUser->user->name, 0, 1) }}</span>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <strong>{{ $topUser->user->name }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $topUser->user->email }}</small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-transparent text-primary">
                                                {{ $topUser->session_count }} جلسة
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center">لا توجد بيانات</p>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
