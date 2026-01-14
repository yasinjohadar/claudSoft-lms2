@extends('admin.layouts.master')

@section('page-title')
    الجلسات النشطة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">الجلسات النشطة</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.user-sessions.index') }}">جلسات المستخدمين</a></li>
                        <li class="breadcrumb-item active">الجلسات النشطة</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('admin.user-sessions.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right me-1"></i>العودة
                </a>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">إجمالي الجلسات النشطة</p>
                                <h4 class="mb-0 text-success">{{ number_format($stats['total_active']) }}</h4>
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
            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">أطول جلسة نشطة</p>
                                <h4 class="mb-0">
                                    @if($stats['longest_active'])
                                        {{ $stats['longest_active']->started_at->diffForHumans() }}
                                    @else
                                        -
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

        <!-- Active Sessions Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">الجلسات النشطة حالياً</h5>
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
                                    <th>المدة</th>
                                    <th>الجهاز</th>
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
                                            <br>
                                            <small class="text-muted">{{ $session->started_at->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-success-transparent text-success">
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
                        <p class="text-muted">لا توجد جلسات نشطة حالياً</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
