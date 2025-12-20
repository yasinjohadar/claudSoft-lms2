@extends('student.layouts.master')

@section('page-title')
    ملفي الشخصي - التلعيب
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <h4 class="mb-0">ملفي الشخصي - التلعيب</h4>
                <a href="{{ route('gamification.dashboard') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i>العودة
                </a>
            </div>

            <!-- معلومات المستخدم -->
            <div class="row mb-4">
                <div class="col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm text-center">
                        <div class="card-body">
                            <div class="avatar avatar-xl mx-auto mb-3">
                                <img src="{{ auth()->user()->avatar ?? asset('assets/images/default-avatar.png') }}" class="rounded-circle" width="120">
                            </div>
                            <h4 class="fw-bold mb-1">{{ auth()->user()->name }}</h4>
                            <p class="text-muted mb-3">{{ auth()->user()->email }}</p>
                            <a href="{{ route('student.profile.edit') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-edit me-1"></i>تعديل الملف الشخصي
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-chart-bar me-2 text-primary"></i>الإحصائيات العامة</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <div class="me-3">
                                            <i class="fas fa-star fa-2x text-warning"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 text-muted">النقاط</h6>
                                            <h4 class="fw-bold mb-0">{{ number_format($stats['total_points'] ?? 0) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <div class="me-3">
                                            <i class="fas fa-arrow-up fa-2x text-success"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 text-muted">المستوى</h6>
                                            <h4 class="fw-bold mb-0">{{ $stats['current_level'] ?? 1 }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <div class="me-3">
                                            <i class="fas fa-gem fa-2x text-info"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 text-muted">الجواهر</h6>
                                            <h4 class="fw-bold mb-0">{{ $stats['gems'] ?? 0 }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <div class="me-3">
                                            <i class="fas fa-fire fa-2x text-danger"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 text-muted">السلسلة</h6>
                                            <h4 class="fw-bold mb-0">{{ $stats['current_streak'] ?? 0 }} يوم</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- الإنجازات -->
            @if(isset($achievements) && count($achievements) > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-trophy me-2 text-warning"></i>آخر الإنجازات</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($achievements->take(6) as $achievement)
                                <div class="col-lg-4 col-md-6 mb-3">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <div class="me-3 fs-2">{{ $achievement->icon ?? '🏆' }}</div>
                                        <div>
                                            <h6 class="fw-bold mb-0">{{ $achievement->name ?? 'إنجاز' }}</h6>
                                            <small class="text-muted">{{ $achievement->description ?? '' }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('gamification.achievements.index') }}" class="btn btn-outline-primary">
                                <i class="fas fa-list me-1"></i>عرض جميع الإنجازات
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- الشارات -->
            @if(isset($badges) && count($badges) > 0)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-medal me-2 text-success"></i>آخر الشارات</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($badges->take(8) as $badge)
                                <div class="col-lg-3 col-md-4 col-6 mb-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <div class="fs-2 mb-2">{{ $badge->icon ?? '🏅' }}</div>
                                        <h6 class="fw-bold mb-0">{{ $badge->name ?? 'شارة' }}</h6>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('gamification.badges.index') }}" class="btn btn-outline-success">
                                <i class="fas fa-list me-1"></i>عرض جميع الشارات
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop



