@extends('admin.layouts.master')

@section('page-title')
    أجهزة المستخدم
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
                <h5 class="page-title fs-21 mb-1">أجهزة {{ $user->name }}</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.user-devices.index') }}">أجهزة المستخدمين</a></li>
                        <li class="breadcrumb-item active">أجهزة المستخدم</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('admin.user-devices.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right me-1"></i>العودة
                </a>
            </div>
        </div>

        <!-- User Info Card -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" 
                             alt="{{ $user->name }}" 
                             class="avatar avatar-lg rounded-circle me-3">
                    @else
                        <div class="avatar avatar-lg rounded-circle bg-primary-transparent me-3">
                            <span class="fw-bold fs-24">{{ substr($user->name, 0, 1) }}</span>
                        </div>
                    @endif
                    <div class="flex-grow-1">
                        <h4 class="mb-1">{{ $user->name }}</h4>
                        <p class="text-muted mb-0">{{ $user->email }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">إجمالي الأجهزة</p>
                                <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-md bg-primary-transparent rounded-circle">
                                    <i class="fas fa-mobile-alt fs-18"></i>
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
                                <p class="text-muted mb-1">الأجهزة الموثوقة</p>
                                <h4 class="mb-0 text-success">{{ number_format($stats['trusted']) }}</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-md bg-success-transparent rounded-circle">
                                    <i class="fas fa-shield-check fs-18"></i>
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
                                <p class="text-muted mb-1">الأجهزة المحظورة</p>
                                <h4 class="mb-0 text-danger">{{ number_format($stats['blocked']) }}</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-md bg-danger-transparent rounded-circle">
                                    <i class="fas fa-ban fs-18"></i>
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
                                <p class="text-muted mb-1">آخر جهاز مستخدم</p>
                                <h4 class="mb-0">
                                    @if($stats['last_used'])
                                        <small class="text-muted">{{ $stats['last_used']->last_used_at->diffForHumans() }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-md bg-info-transparent rounded-circle">
                                    <i class="fas fa-clock fs-18"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-filter me-2"></i>الفلاتر
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.user-devices.user', $user->id) }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">نوع الجهاز</label>
                            <select name="device_type" class="form-select">
                                <option value="">الكل</option>
                                <option value="mobile" {{ request('device_type') == 'mobile' ? 'selected' : '' }}>جوال</option>
                                <option value="tablet" {{ request('device_type') == 'tablet' ? 'selected' : '' }}>تابلت</option>
                                <option value="desktop" {{ request('device_type') == 'desktop' ? 'selected' : '' }}>سطح مكتب</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">الحالة</label>
                            <select name="status" class="form-select">
                                <option value="">الكل</option>
                                <option value="trusted" {{ request('status') == 'trusted' ? 'selected' : '' }}>موثوق</option>
                                <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>محظور</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i>بحث
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Devices Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">قائمة الأجهزة</h5>
            </div>
            <div class="card-body">
                @if($devices->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>معلومات الجهاز</th>
                                    <th>عدد مرات الدخول</th>
                                    <th>أول استخدام</th>
                                    <th>آخر استخدام</th>
                                    <th>آخر IP</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($devices as $device)
                                    <tr>
                                        <td>{{ $devices->firstItem() + $loop->index }}</td>
                                        <td>
                                            <small>{{ $device->device_info }}</small>
                                            @if($device->device_name)
                                                <br>
                                                <strong class="text-primary">{{ $device->device_name }}</strong>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info-transparent text-info">
                                                {{ number_format($device->total_logins) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ $device->first_used_human }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $device->last_used_human }}</small>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $device->last_ip_address ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <span class="{{ $device->status_badge['class'] }}">
                                                <i class="fas {{ $device->status_badge['icon'] }} me-1"></i>
                                                {{ $device->status_badge['text'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.user-devices.show', $device->id) }}" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="عرض التفاصيل">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($device->is_blocked)
                                                    <form action="{{ route('admin.user-devices.unblock', $device->id) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('هل أنت متأكد من إلغاء حظر هذا الجهاز؟');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success" title="إلغاء الحظر">
                                                            <i class="fas fa-unlock"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('admin.user-devices.block', $device->id) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('هل أنت متأكد من حظر هذا الجهاز؟');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="حظر">
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $devices->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-mobile-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">لا توجد أجهزة لهذا المستخدم</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
