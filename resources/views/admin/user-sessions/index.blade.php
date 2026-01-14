@extends('admin.layouts.master')

@section('page-title')
    جلسات المستخدمين
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">جلسات المستخدمين</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active">جلسات المستخدمين</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex">
                <a href="{{ route('admin.user-sessions.statistics') }}" class="btn btn-primary me-2">
                    <i class="fas fa-chart-bar me-1"></i>الإحصائيات
                </a>
                <a href="{{ route('admin.user-sessions.active') }}" class="btn btn-success">
                    <i class="fas fa-circle me-1"></i>الجلسات النشطة
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">إجمالي الجلسات</p>
                                <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
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
                                <h4 class="mb-0 text-success">{{ number_format($stats['active']) }}</h4>
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
                                <h4 class="mb-0 text-info">{{ number_format($stats['completed']) }}</h4>
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

        <!-- Filters -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-filter me-2"></i>الفلاتر
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.user-sessions.index') }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">البحث</label>
                            <input type="text" name="search" class="form-control" 
                                   value="{{ request('search') }}" 
                                   placeholder="اسم المستخدم، البريد، IP...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">المستخدم</label>
                            <select name="user_id" class="form-select">
                                <option value="">الكل</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">الحالة</label>
                            <select name="status" class="form-select">
                                <option value="">الكل</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشطة</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتملة</option>
                                <option value="disconnected" {{ request('status') == 'disconnected' ? 'selected' : '' }}>منفصلة</option>
                                <option value="timeout" {{ request('status') == 'timeout' ? 'selected' : '' }}>انتهت</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">نوع الجهاز</label>
                            <select name="device_type" class="form-select">
                                <option value="">الكل</option>
                                @foreach($deviceTypes as $type)
                                    <option value="{{ $type }}" {{ request('device_type') == $type ? 'selected' : '' }}>
                                        {{ ucfirst($type) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">من تاريخ</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sessions Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">قائمة الجلسات</h5>
            </div>
            <div class="card-body">
                @if($sessions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المستخدم</th>
                                    <th>تاريخ البدء</th>
                                    <th>تاريخ الانتهاء</th>
                                    <th>المدة</th>
                                    <th>الجهاز</th>
                                    <th>الموقع</th>
                                    <th>الحالة</th>
                                    <th>الأنشطة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sessions as $session)
                                    <tr>
                                        <td>{{ $sessions->firstItem() + $loop->index }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($session->user)
                                                    @if($session->user->avatar)
                                                        <img src="{{ asset('storage/' . $session->user->avatar) }}" 
                                                             alt="{{ $session->user->name }}" 
                                                             class="avatar avatar-sm rounded-circle me-2">
                                                    @else
                                                        <div class="avatar avatar-sm rounded-circle bg-primary-transparent me-2">
                                                            <span class="fw-bold">{{ substr($session->user->name, 0, 1) }}</span>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <strong>{{ $session->user->name }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $session->user->email }}</small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            {{ $session->started_at->format('Y-m-d H:i') }}
                                        </td>
                                        <td>
                                            {{ $session->ended_at ? $session->ended_at->format('Y-m-d H:i') : '-' }}
                                        </td>
                                        <td>
                                            <span class="badge bg-info-transparent text-info">
                                                {{ $session->duration_formatted }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ $session->device_info }}</small>
                                            @if($session->ip_address)
                                                <br>
                                                <small class="text-muted">{{ $session->ip_address }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $session->location_formatted }}</small>
                                        </td>
                                        <td>
                                            @if($session->status == 'active')
                                                <span class="badge bg-success">نشطة</span>
                                            @elseif($session->status == 'completed')
                                                <span class="badge bg-info">مكتملة</span>
                                            @elseif($session->status == 'disconnected')
                                                <span class="badge bg-warning">منفصلة</span>
                                            @else
                                                <span class="badge bg-secondary">انتهت</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-transparent text-primary">
                                                {{ $session->activities_count }} نشاط
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.user-sessions.show', $session->id) }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="عرض التفاصيل">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $sessions->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">لا توجد جلسات</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
