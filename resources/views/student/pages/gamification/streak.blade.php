@extends('student.layouts.master')

@section('page-title')
    السلسلة اليومية
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <h4 class="mb-0">السلسلة اليومية</h4>
                <div>
                    <a href="{{ route('gamification.streak.calendar') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-calendar me-1"></i>التقويم
                    </a>
                    <a href="{{ route('gamification.streak.history') }}" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-history me-1"></i>السجل
                    </a>
                </div>
            </div>

            <!-- إحصائيات السلسلة -->
            <div class="row mb-4">
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-danger mb-2"><i class="fas fa-fire fa-3x"></i></div>
                            <h2 class="fw-bold mb-1 text-danger">{{ $streakInfo['current_streak'] ?? 0 }}</h2>
                            <p class="text-muted mb-0">السلسلة الحالية</p>
                            @if(isset($streakInfo['current_streak']) && $streakInfo['current_streak'] > 0)
                                <small class="text-success">
                                    <i class="fas fa-check-circle me-1"></i>نشط
                                </small>
                            @else
                                <small class="text-muted">
                                    <i class="fas fa-times-circle me-1"></i>غير نشط
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-warning mb-2"><i class="fas fa-trophy fa-3x"></i></div>
                            <h2 class="fw-bold mb-1 text-warning">{{ $streakInfo['longest_streak'] ?? 0 }}</h2>
                            <p class="text-muted mb-0">أطول سلسلة</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-info mb-2"><i class="fas fa-calendar-check fa-3x"></i></div>
                            <h2 class="fw-bold mb-1 text-info">{{ $monthlyStats['active_days'] ?? 0 }}</h2>
                            <p class="text-muted mb-0">أيام نشطة هذا الشهر</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- معلومات السلسلة -->
            <div class="row mb-4">
                <div class="col-lg-8 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-info"></i>معلومات السلسلة</h5>
                        </div>
                        <div class="card-body">
                            @if(isset($streakInfo['current_streak']) && $streakInfo['current_streak'] > 0)
                                <div class="alert alert-success">
                                    <h6 class="alert-heading">
                                        <i class="fas fa-fire me-2"></i>سلسلة نشطة!
                                    </h6>
                                    <p class="mb-0">
                                        أنت في سلسلة من <strong>{{ $streakInfo['current_streak'] }}</strong> يوم متتالي.
                                        استمر في التعلم اليوم للحفاظ على السلسلة!
                                    </p>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <h6 class="alert-heading">
                                        <i class="fas fa-exclamation-triangle me-2"></i>لا توجد سلسلة نشطة
                                    </h6>
                                    <p class="mb-0">
                                        ابدأ سلسلة جديدة اليوم! قم بإكمال درس أو اختبار لبدء السلسلة.
                                    </p>
                                </div>
                            @endif

                            @if(isset($streakInfo['last_activity_date']))
                                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded mb-3">
                                    <div>
                                        <h6 class="mb-0">آخر نشاط</h6>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($streakInfo['last_activity_date'])->diffForHumans() }}</small>
                                    </div>
                                    <i class="fas fa-clock fa-2x text-muted"></i>
                                </div>
                            @endif

                            @if(isset($streakInfo['next_milestone']))
                                <div class="d-flex justify-content-between align-items-center p-3 bg-primary text-white rounded">
                                    <div>
                                        <h6 class="mb-0 text-white">الهدف القادم</h6>
                                        <small>{{ $streakInfo['next_milestone']['days'] }} يوم متتالي</small>
                                    </div>
                                    <div class="text-end">
                                        <h4 class="mb-0">+{{ $streakInfo['next_milestone']['points'] ?? 0 }} نقطة</h4>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-gift me-2 text-warning"></i>مكافآت السلسلة</h5>
                        </div>
                        <div class="card-body">
                            @if(isset($streakRewards) && count($streakRewards) > 0)
                                @foreach($streakRewards as $days => $points)
                                    <div class="d-flex justify-content-between align-items-center p-2 mb-2 bg-light rounded">
                                        <div>
                                            <i class="fas fa-fire text-danger me-2"></i>
                                            <strong>{{ $days }} يوم</strong>
                                        </div>
                                        <span class="badge bg-warning">+{{ $points }} نقطة</span>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted text-center mb-0">لا توجد مكافآت محددة</p>
                            @endif
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-bolt me-2 text-primary"></i>إجراءات سريعة</h5>
                        </div>
                        <div class="card-body">
                            <a href="{{ route('student.courses.my-courses') }}" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-book me-1"></i>ابدأ التعلم
                            </a>
                            <a href="{{ route('gamification.streak.calendar') }}" class="btn btn-outline-primary w-100 mb-2">
                                <i class="fas fa-calendar me-1"></i>عرض التقويم
                            </a>
                            <a href="{{ route('gamification.streak.history') }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-history me-1"></i>عرض السجل
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- إحصائيات الشهر الحالي -->
            @if(isset($monthlyStats))
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2 text-success"></i>إحصائيات الشهر الحالي</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h4 class="fw-bold text-success">{{ $monthlyStats['active_days'] ?? 0 }}</h4>
                                    <p class="text-muted mb-0">أيام نشطة</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h4 class="fw-bold text-primary">{{ number_format($monthlyStats['total_points'] ?? 0) }}</h4>
                                    <p class="text-muted mb-0">نقاط مكتسبة</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h4 class="fw-bold text-info">{{ number_format($monthlyStats['total_xp'] ?? 0) }}</h4>
                                    <p class="text-muted mb-0">XP مكتسب</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop



