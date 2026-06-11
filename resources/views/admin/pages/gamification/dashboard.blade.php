@extends('admin.layouts.master')

@section('page-title')
    لوحة تحكم الـ Gamification
@endsection

@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <h1 class="page-title fw-semibold fs-18 mb-0">لوحة تحكم الـ Gamification</h1>
                <div class="ms-md-1 ms-0 d-flex align-items-center gap-2 flex-wrap">
                    @include('admin.pages.gamification.partials.recalculate-button', [
                        'modalId' => 'gamificationDashboardRecalculateModal',
                        'buttonClass' => 'btn btn-outline-primary btn-sm',
                        'label' => 'إعادة احتساب النقاط واللوحات',
                    ])
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Gamification</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- Page Header Close -->

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fe fe-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fe fe-alert-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <!-- إحصائيات سريعة -->
            <div class="row">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-primary-transparent text-primary">
                                        <i class="fas fa-users fs-18"></i>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-muted fs-12">إجمالي الطلاب</span>
                                    <h4 class="fw-semibold mb-0">{{ $stats['total_students'] ?? 0 }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-success-transparent text-success">
                                        <i class="fas fa-coins fs-18"></i>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-muted fs-12">إجمالي النقاط</span>
                                    <h4 class="fw-semibold mb-0">{{ number_format($stats['total_points'] ?? 0) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-warning-transparent text-warning">
                                        <i class="fas fa-medal fs-18"></i>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-muted fs-12">الشارات الممنوحة</span>
                                    <h4 class="fw-semibold mb-0">{{ $stats['total_badges_earned'] ?? 0 }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-info-transparent text-info">
                                        <i class="fas fa-trophy fs-18"></i>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-muted fs-12">الإنجازات المكتملة</span>
                                    <h4 class="fw-semibold mb-0">{{ $stats['total_achievements_unlocked'] ?? 0 }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- الصف الثاني -->
            <div class="row">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-danger-transparent text-danger">
                                        <i class="fas fa-bullseye fs-18"></i>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-muted fs-12">التحديات النشطة</span>
                                    <h4 class="fw-semibold mb-0">{{ $stats['active_challenges'] ?? 0 }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-secondary-transparent text-secondary">
                                        <i class="fas fa-layer-group fs-18"></i>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-muted fs-12">متوسط المستوى</span>
                                    <h4 class="fw-semibold mb-0">{{ number_format($stats['average_level'] ?? 0, 1) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-pink-transparent text-pink">
                                        <i class="fas fa-shopping-cart fs-18"></i>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-muted fs-12">المشتريات</span>
                                    <h4 class="fw-semibold mb-0">{{ $stats['total_purchases'] ?? 0 }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-teal-transparent text-teal">
                                        <i class="fas fa-fire fs-18"></i>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-muted fs-12">أعلى سلسلة</span>
                                    <h4 class="fw-semibold mb-0">{{ $stats['highest_streak'] ?? 0 }} يوم</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- روابط سريعة -->
            <div class="row">
                <div class="col-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">إدارة سريعة</div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                    <a href="{{ route('admin.gamification.levels.index') }}" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-layer-group me-2"></i>المستويات
                                    </a>
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                    <a href="{{ route('admin.gamification.badges.index') }}" class="btn btn-outline-warning w-100">
                                        <i class="fas fa-medal me-2"></i>الشارات
                                    </a>
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                    <a href="{{ route('admin.gamification.achievements.index') }}" class="btn btn-outline-success w-100">
                                        <i class="fas fa-trophy me-2"></i>الإنجازات
                                    </a>
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                    <a href="{{ route('admin.gamification.leaderboards.index') }}" class="btn btn-outline-info w-100">
                                        <i class="fas fa-crown me-2"></i>المتصدرين
                                    </a>
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                    <a href="{{ route('admin.gamification.challenges.index') }}" class="btn btn-outline-danger w-100">
                                        <i class="fas fa-bullseye me-2"></i>التحديات
                                    </a>
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                    <a href="{{ route('admin.gamification.shop.items.index') }}" class="btn btn-outline-secondary w-100">
                                        <i class="fas fa-store me-2"></i>المتجر
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- أفضل الطلاب -->
            <div class="row">
                <div class="col-xl-6">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">أفضل الطلاب (النقاط)</div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table text-nowrap">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>الطالب</th>
                                            <th>النقاط</th>
                                            <th>المستوى</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($topStudents ?? [] as $index => $student)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span class="avatar avatar-sm me-2">
                                                            <img src="{{ $student->avatar ?? asset('assets/images/faces/1.jpg') }}" alt="">
                                                        </span>
                                                        {{ $student->name }}
                                                    </div>
                                                </td>
                                                <td><span class="badge bg-success">{{ number_format($student->stats->total_points ?? 0) }}</span></td>
                                                <td><span class="badge bg-primary">{{ $student->stats->current_level ?? 1 }}</span></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">لا توجد بيانات</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">أحدث الشارات الممنوحة</div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table text-nowrap">
                                    <thead>
                                        <tr>
                                            <th>الطالب</th>
                                            <th>الشارة</th>
                                            <th>التاريخ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentBadges ?? [] as $userBadge)
                                            <tr>
                                                <td>{{ $userBadge->user->name ?? 'غير معروف' }}</td>
                                                <td>
                                                    <span class="badge bg-warning-transparent text-warning">
                                                        {{ $userBadge->badge->icon ?? '🏅' }} {{ $userBadge->badge->name ?? '' }}
                                                    </span>
                                                </td>
                                                <td>{{ $userBadge->awarded_at ? $userBadge->awarded_at->diffForHumans() : '' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">لا توجد بيانات</td>
                                            </tr>
                                        @endforelse
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
