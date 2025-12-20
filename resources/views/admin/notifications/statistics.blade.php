@extends('admin.layouts.master')

@section('page-title', 'إحصائيات الإشعارات')

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h4 class="page-title fw-semibold fs-18 mb-0">إحصائيات الإشعارات</h4>
            <p class="fw-normal text-muted fs-14 mb-0">إحصائيات مفصلة للإشعارات المرسلة</p>
        </div>
        <div class="ms-md-auto d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('admin.notifications.index') }}" class="btn btn-primary btn-wave">
                <i class="ri-send-plane-line me-1"></i> إرسال إشعار
            </a>
            <a href="{{ route('admin.notifications.history') }}" class="btn btn-secondary btn-wave">
                <i class="ri-history-line me-1"></i> سجل الإشعارات
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <span class="d-block mb-1">إجمالي الإشعارات</span>
                            <h4 class="fw-semibold mb-0">{{ number_format($stats['total_sent']) }}</h4>
                        </div>
                        <div>
                            <span class="avatar avatar-md bg-primary-transparent">
                                <i class="ri-notification-line fs-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <span class="d-block mb-1">الإشعارات المقروءة</span>
                            <h4 class="fw-semibold mb-0">{{ number_format($stats['total_read']) }}</h4>
                            <small class="text-success">
                                {{ $stats['total_sent'] > 0 ? round(($stats['total_read'] / $stats['total_sent']) * 100, 1) : 0 }}%
                            </small>
                        </div>
                        <div>
                            <span class="avatar avatar-md bg-success-transparent">
                                <i class="ri-check-double-line fs-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <span class="d-block mb-1">الإشعارات غير المقروءة</span>
                            <h4 class="fw-semibold mb-0">{{ number_format($stats['total_unread']) }}</h4>
                            <small class="text-warning">
                                {{ $stats['total_sent'] > 0 ? round(($stats['total_unread'] / $stats['total_sent']) * 100, 1) : 0 }}%
                            </small>
                        </div>
                        <div>
                            <span class="avatar avatar-md bg-warning-transparent">
                                <i class="ri-notification-badge-line fs-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <span class="d-block mb-1">الإشعارات اليوم</span>
                            <h4 class="fw-semibold mb-0">{{ number_format($stats['sent_today']) }}</h4>
                        </div>
                        <div>
                            <span class="avatar avatar-md bg-info-transparent">
                                <i class="ri-calendar-todo-line fs-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Time-based Statistics -->
    <div class="row">
        <div class="col-xl-4">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">هذا الأسبوع</div>
                </div>
                <div class="card-body text-center">
                    <h2 class="fw-bold text-primary">{{ number_format($stats['sent_this_week']) }}</h2>
                    <p class="text-muted mb-0">إشعار مرسل</p>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">هذا الشهر</div>
                </div>
                <div class="card-body text-center">
                    <h2 class="fw-bold text-success">{{ number_format($stats['sent_this_month']) }}</h2>
                    <p class="text-muted mb-0">إشعار مرسل</p>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">معدل القراءة</div>
                </div>
                <div class="card-body text-center">
                    <h2 class="fw-bold text-info">
                        {{ $stats['total_sent'] > 0 ? round(($stats['total_read'] / $stats['total_sent']) * 100, 1) : 0 }}%
                    </h2>
                    <p class="text-muted mb-0">من الإشعارات المرسلة</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications by Type -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">الإشعارات حسب النوع</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap">
                            <thead>
                                <tr>
                                    <th>النوع</th>
                                    <th>العدد</th>
                                    <th>النسبة المئوية</th>
                                    <th>الرسم البياني</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($byType as $item)
                                    <tr>
                                        <td><span class="badge bg-primary-transparent">{{ $item->type }}</span></td>
                                        <td><strong>{{ number_format($item->count) }}</strong></td>
                                        <td>
                                            {{ $stats['total_sent'] > 0 ? round(($item->count / $stats['total_sent']) * 100, 1) : 0 }}%
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar" role="progressbar"
                                                     style="width: {{ $stats['total_sent'] > 0 ? round(($item->count / $stats['total_sent']) * 100, 1) : 0 }}%">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<!-- End::app-content -->
@endsection
