@extends('student.layouts.master')

@section('page-title')
    لوحة التلعيب
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <h4 class="mb-0">لوحة التلعيب</h4>
            </div>

            <!-- إحصائيات سريعة -->
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-primary mb-2"><i class="fas fa-star fa-2x"></i></div>
                            <h3 class="fw-bold mb-1">{{ $stats->total_points ?? 0 }}</h3>
                            <p class="text-muted mb-0">النقاط</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-success mb-2"><i class="fas fa-arrow-up fa-2x"></i></div>
                            <h3 class="fw-bold mb-1">{{ $stats->current_level ?? 1 }}</h3>
                            <p class="text-muted mb-0">المستوى</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-warning mb-2"><i class="fas fa-gem fa-2x"></i></div>
                            <h3 class="fw-bold mb-1">{{ $stats->gems ?? 0 }}</h3>
                            <p class="text-muted mb-0">الجواهر</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-danger mb-2"><i class="fas fa-fire fa-2x"></i></div>
                            <h3 class="fw-bold mb-1">{{ $stats->current_streak ?? 0 }}</h3>
                            <p class="text-muted mb-0">السلسلة</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- شريط التقدم للمستوى التالي -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>المستوى {{ $stats->current_level ?? 1 }}</span>
                                <span>المستوى {{ ($stats->current_level ?? 1) + 1 }}</span>
                            </div>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-gradient" role="progressbar"
                                     style="width: {{ $levelProgress ?? 50 }}%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"
                                     aria-valuenow="{{ $levelProgress ?? 50 }}" aria-valuemin="0" aria-valuemax="100">
                                    {{ $levelProgress ?? 50 }}%
                                </div>
                            </div>
                            <p class="text-muted mt-2 mb-0 text-center">
                                تحتاج {{ $xpToNextLevel ?? 0 }} XP للمستوى التالي
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- آخر الشارات -->
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-medal me-2"></i>آخر الشارات</h5>
                        </div>
                        <div class="card-body">
                            @forelse($recentBadges ?? [] as $badge)
                                <div class="d-flex align-items-center mb-3 p-2 bg-light rounded">
                                    <div class="me-3 fs-3">{{ $badge->icon ?? '🏅' }}</div>
                                    <div>
                                        <h6 class="mb-0">{{ $badge->name }}</h6>
                                        <small class="text-muted">{{ $badge->pivot->awarded_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted text-center">لم تحصل على شارات بعد</p>
                            @endforelse
                            <a href="{{ route('gamification.badges.index') }}" class="btn btn-sm btn-outline-primary w-100">عرض الكل</a>
                        </div>
                    </div>
                </div>

                <!-- التحديات النشطة -->
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-bullseye me-2"></i>التحديات النشطة</h5>
                        </div>
                        <div class="card-body">
                            @forelse($activeChallenges ?? [] as $challenge)
                                <div class="mb-3 p-2 bg-light rounded">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span>{{ $challenge->icon ?? '🎯' }} {{ $challenge->name }}</span>
                                        <span class="badge bg-primary">+{{ $challenge->points_reward }} نقطة</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar" style="width: {{ $challenge->progress ?? 0 }}%;"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted text-center">لا توجد تحديات نشطة</p>
                            @endforelse
                            <a href="{{ route('gamification.challenges.index') }}" class="btn btn-sm btn-outline-primary w-100">عرض الكل</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ترتيبك في لوحة المتصدرين -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-crown me-2"></i>ترتيبك في لوحة المتصدرين</h5>
                        </div>
                        <div class="card-body text-center">
                            <h2 class="display-4 fw-bold text-primary">#{{ $rank ?? '-' }}</h2>
                            <p class="text-muted">من بين {{ $totalUsers ?? 0 }} طالب</p>
                            <a href="{{ route('gamification.leaderboards.index') }}" class="btn btn-primary">عرض لوحة المتصدرين</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
