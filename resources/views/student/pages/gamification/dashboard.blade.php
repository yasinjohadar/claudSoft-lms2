@extends('student.layouts.master')

@section('page-title')
    لوحة التلعيب
@stop

@section('content')
<div class="main-content app-content student-gamification-dashboard">
    <div class="container-fluid pb-3">

        @include('student.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4">
            <div class="min-w-0">
                <h4 class="student-my-courses-welcome__title mb-1">لوحة التلعيب</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item active">التلعيب</li>
                    </ol>
                </nav>
                <p class="text-muted fs-13 mb-0 mt-2">تابع نقاطك ومستواك وتحدياتك وترتيبك</p>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
                <a href="{{ route('gamification.profile') }}" class="btn btn-outline-primary rounded-pill">
                    <i class="fe fe-user me-1"></i>ملفي
                </a>
                <a href="{{ route('gamification.statistics') }}" class="btn btn-primary rounded-pill">
                    <i class="fe fe-bar-chart me-1"></i>إحصائياتي
                </a>
            </div>
        </div>

        @include('student.pages.gamification.partials.dashboard-kpi', compact(
            'dashboard', 'levelInfo', 'streakInfo', 'userStats'
        ))

        <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
            <div class="card-header border-0 pb-0">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar avatar-sm bg-success-transparent">
                            <i class="fe fe-trending-up text-success"></i>
                        </span>
                        <div>
                            <h4 class="card-title mb-1">تقدم المستوى</h4>
                            <p class="fs-12 text-muted mb-0">من المستوى {{ $levelInfo['current_level'] ?? 1 }} إلى {{ ($levelInfo['current_level'] ?? 1) + 1 }}</p>
                        </div>
                    </div>
                    <a href="{{ route('gamification.levels.index') }}" class="btn btn-sm btn-success-light rounded-pill">
                        <i class="fe fe-layers me-1"></i>كل المستويات
                    </a>
                </div>
            </div>
            <div class="card-body pt-3">
                @php $progress = min(100, max(0, (float) ($levelInfo['level_progress'] ?? 0))); @endphp
                <div class="d-flex justify-content-between align-items-center mb-2 fs-13">
                    <span class="fw-semibold">المستوى {{ $levelInfo['current_level'] ?? 1 }}</span>
                    <span class="text-muted">{{ number_format($progress, 0) }}%</span>
                    <span class="fw-semibold">المستوى {{ ($levelInfo['current_level'] ?? 1) + 1 }}</span>
                </div>
                <div class="progress progress-style progress-sm mb-2">
                    <div class="progress-bar bg-success" style="width: {{ $progress }}%"></div>
                </div>
                <p class="text-muted fs-13 mb-0 text-center">
                    @if($levelInfo['is_max_level'] ?? false)
                        <i class="fe fe-award me-1"></i>وصلت إلى أعلى مستوى!
                    @else
                        تحتاج <strong class="text-primary">{{ $levelInfo['xp_needed'] ?? 0 }} XP</strong> للمستوى التالي
                    @endif
                </p>
            </div>
        </div>

        @include('student.pages.gamification.partials.dashboard-quick-links')

        <div class="row align-items-start g-3 student-gamification-dashboard__row">
            <div class="col-lg-6 d-flex flex-column gap-3">
                <div class="card custom-card group-show-members-card dashboard-fade-in">
                    <div class="card-header border-0 pb-0">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm bg-warning-transparent">
                                <i class="fe fe-award text-warning"></i>
                            </span>
                            <div>
                                <h4 class="card-title mb-1">آخر الشارات</h4>
                                <p class="fs-12 text-muted mb-0">أحدث إنجازاتك المكتسبة.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        @forelse($latestBadges as $userBadge)
                            <div class="dashboard-stat-row dashboard-stagger-item d-flex align-items-center gap-3 mb-2"
                                 style="--stagger-delay: {{ $loop->index * 40 }}ms">
                                <span class="avatar avatar-md bg-warning-transparent rounded-circle flex-shrink-0 fs-20">
                                    {{ $userBadge->badge->icon ?? '🏅' }}
                                </span>
                                <div class="min-w-0 flex-fill">
                                    <p class="fw-semibold mb-0 fs-13">{{ $userBadge->badge->name ?? 'شارة' }}</p>
                                    <small class="text-muted">
                                        <i class="fe fe-clock me-1"></i>{{ $userBadge->awarded_at?->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                        @empty
                            <div class="group-show-empty py-4">
                                <i class="fe fe-award group-show-empty__icon"></i>
                                <h5 class="group-show-empty__title">لم تحصل على شارات بعد</h5>
                                <p class="group-show-empty__desc mb-3">أكمل الأنشطة والتحديات لكسب الشارات.</p>
                                <a href="{{ route('gamification.badges.index') }}" class="btn btn-sm btn-warning-light rounded-pill">
                                    استكشف الشارات
                                </a>
                            </div>
                        @endforelse
                        @if($latestBadges->count() > 0)
                            <a href="{{ route('gamification.badges.index') }}" class="btn btn-outline-primary rounded-pill w-100 mt-2">
                                <i class="fe fe-arrow-left me-1"></i>عرض الكل
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6 d-flex flex-column gap-3">
                <div class="card custom-card group-show-members-card dashboard-fade-in">
                    <div class="card-header border-0 pb-0">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm bg-primary-transparent">
                                <i class="fe fe-target text-primary"></i>
                            </span>
                            <div>
                                <h4 class="card-title mb-1">التحديات النشطة</h4>
                                <p class="fs-12 text-muted mb-0">تابع تقدمك في التحديات الحالية.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        @forelse($activeChallenges as $userChallenge)
                            @php
                                $challenge = $userChallenge->challenge;
                                $progressPct = min(100, max(0, (float) ($userChallenge->progress_percentage ?? 0)));
                            @endphp
                            <div class="dashboard-stat-row dashboard-stagger-item mb-3" style="--stagger-delay: {{ $loop->index * 40 }}ms">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        <span class="fs-18 flex-shrink-0">{{ $challenge->icon ?? '🎯' }}</span>
                                        <span class="fw-semibold fs-13 text-truncate">{{ $challenge->name ?? 'تحدي' }}</span>
                                    </div>
                                    @if($challenge?->points_reward)
                                        <span class="badge bg-primary-transparent text-primary flex-shrink-0">
                                            +{{ $challenge->points_reward }} نقطة
                                        </span>
                                    @endif
                                </div>
                                <div class="progress progress-xs mb-1">
                                    <div class="progress-bar bg-primary" style="width: {{ $progressPct }}%"></div>
                                </div>
                                <small class="text-muted">{{ number_format($progressPct, 0) }}% مكتمل</small>
                            </div>
                        @empty
                            <div class="group-show-empty py-4">
                                <i class="fe fe-target group-show-empty__icon"></i>
                                <h5 class="group-show-empty__title">لا توجد تحديات نشطة</h5>
                                <p class="group-show-empty__desc mb-3">انضم لتحدي جديد وابدأ جمع النقاط.</p>
                                <a href="{{ route('gamification.challenges.index') }}" class="btn btn-sm btn-primary-light rounded-pill">
                                    تصفح التحديات
                                </a>
                            </div>
                        @endforelse
                        @if($activeChallenges->count() > 0)
                            <a href="{{ route('gamification.challenges.index') }}" class="btn btn-outline-primary rounded-pill w-100 mt-2">
                                <i class="fe fe-arrow-left me-1"></i>عرض الكل
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in mt-3 student-gamification-leaderboard-card">
            <div class="card-body text-center py-4">
                <span class="avatar avatar-lg bg-primary-transparent mb-3">
                    <i class="fe fe-award text-primary fs-24"></i>
                </span>
                <h4 class="card-title mb-1">ترتيبك في لوحة المتصدرين</h4>
                <p class="fs-12 text-muted mb-3">مقارنة أدائك مع بقية الطلاب</p>
                <h2 class="student-gamification-leaderboard-card__rank mb-2">
                    @if($leaderboardRank)
                        #{{ $leaderboardRank }}
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </h2>
                <p class="text-muted mb-3">من بين {{ number_format($leaderboardTotal) }} طالب</p>
                <a href="{{ route('gamification.leaderboards.index') }}" class="btn btn-primary rounded-pill">
                    <i class="fe fe-bar-chart-2 me-1"></i>عرض لوحة المتصدرين
                </a>
            </div>
        </div>

    </div>
</div>
@stop

@section('scripts')
<script>
(function () {
    function formatNumber(value, decimals) {
        if (decimals) {
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 1,
                maximumFractionDigits: 1,
            }).format(value);
        }
        return new Intl.NumberFormat('ar-EG').format(Math.round(value));
    }

    document.querySelectorAll('[data-countup]').forEach(function (el) {
        var target = parseFloat(el.dataset.countup || '0');
        var prefix = el.dataset.countupPrefix || '';
        var suffix = el.dataset.countupSuffix || '';
        var decimals = el.dataset.countupDecimals === '1';
        var duration = 800;
        var start = performance.now();

        function step(now) {
            var progress = Math.min((now - start) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            var value = formatNumber(target * eased, decimals);
            el.textContent = prefix + value + suffix;
            if (progress < 1) requestAnimationFrame(step);
        }

        requestAnimationFrame(step);
    });
})();
</script>
@stop
