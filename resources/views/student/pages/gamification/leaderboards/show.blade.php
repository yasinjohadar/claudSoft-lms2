@extends('student.layouts.master')

@section('page-title')
    {{ $leaderboard->name }}
@stop

@section('content')
<div class="main-content app-content student-leaderboards-page">
    <div class="container-fluid">
        @php
            $catalog = app(\App\Services\Gamification\LeaderboardCatalog::class);
            $topByRank = $topThree->keyBy('rank');
        @endphp

        <div class="student-leaderboard-show-hero mb-4">
            <div class="d-md-flex align-items-start justify-content-between gap-3">
                <div>
                    <span class="student-leaderboard-show-hero__icon">{{ $leaderboard->icon ?? '🏆' }}</span>
                    <h4 class="student-my-courses-welcome__title mb-1">{{ $leaderboard->name }}</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('gamification.leaderboards.index') }}">المتصدرون</a></li>
                            <li class="breadcrumb-item active">{{ $leaderboard->name }}</li>
                        </ol>
                    </nav>
                    @if ($leaderboard->description)
                        <p class="text-muted fs-13 mb-0 mt-2">{{ $leaderboard->description }}</p>
                    @endif
                </div>
                <a href="{{ route('gamification.leaderboards.index') }}" class="btn btn-light btn-sm shrink-0">
                    <i class="ri ri-arrow-right-line me-1"></i>العودة
                </a>
            </div>
        </div>

        @if ($userRank)
            <div class="student-leaderboard-my-rank mb-4">
                <div class="student-leaderboard-my-rank__item">
                    <span class="student-leaderboard-my-rank__label">ترتيبك</span>
                    <strong>#{{ $userRank['rank'] }}</strong>
                    <small>من {{ number_format($userRank['total_participants']) }}</small>
                </div>
                <div class="student-leaderboard-my-rank__item">
                    <span class="student-leaderboard-my-rank__label">نتيجتك</span>
                    <strong class="text-primary">{{ number_format($userRank['score']) }}</strong>
                </div>
                <div class="student-leaderboard-my-rank__item">
                    <span class="student-leaderboard-my-rank__label">الفئة</span>
                    @include('student.pages.gamification.leaderboards.partials.division-badge', [
                        'division' => $userRank['division'],
                        'catalog' => $catalog,
                        'size' => 'sm',
                    ])
                </div>
                @if (($userRank['rank_change'] ?? 0) !== 0)
                    <div class="student-leaderboard-my-rank__item">
                        <span class="student-leaderboard-my-rank__label">التغيّر</span>
                        <strong class="{{ $userRank['rank_change'] > 0 ? 'text-success' : 'text-danger' }}">
                            {{ $userRank['rank_change'] > 0 ? '↑' : '↓' }}{{ abs($userRank['rank_change']) }}
                        </strong>
                    </div>
                @endif
            </div>
        @endif

        @if ($topThree->isNotEmpty())
            <div class="student-leaderboard-podium-wrap mb-4">
                <div class="student-leaderboard-podium">
                    @foreach ([2, 1, 3] as $rank)
                        @php $entry = $topByRank->get($rank); @endphp
                        <div class="student-leaderboard-podium__slot student-leaderboard-podium__slot--rank-{{ $rank }}">
                            @if ($entry)
                                @include('student.pages.gamification.leaderboards.partials.podium-entry', [
                                    'entry' => $entry,
                                    'catalog' => $catalog,
                                    'leaderboard' => $leaderboard,
                                    'currentUser' => $user,
                                ])
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="card custom-card student-quizzes-panel mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar avatar-sm bg-primary-transparent"><i class="ri ri-trophy-line text-primary"></i></span>
                        <h6 class="card-title mb-0">قائمة المتصدرين</h6>
                    </div>
                    <div class="student-leaderboard-division-tabs">
                        <button type="button" class="student-leaderboard-division-tab is-active" data-division-filter="all">
                            <span class="student-leaderboard-division-tab__icon student-leaderboard-division-tab__icon--all" aria-hidden="true"><i class="ri ri-apps-line"></i></span>
                            <span>الكل</span>
                        </button>
                        @foreach (['bronze','silver','gold','platinum','diamond'] as $div)
                            @include('student.pages.gamification.leaderboards.partials.division-tab', [
                                'division' => $div,
                                'catalog' => $catalog,
                            ])
                        @endforeach
                    </div>
                </div>

                <div class="student-leaderboard-list">
                    @forelse ($entries as $entry)
                        @include('student.pages.gamification.leaderboards.partials.list-entry', [
                            'entry' => $entry,
                            'catalog' => $catalog,
                            'leaderboard' => $leaderboard,
                            'currentUser' => $user,
                        ])
                    @empty
                        <p class="text-muted mb-0 text-center py-4">لا يوجد مشاركون بعد</p>
                    @endforelse
                    <p class="text-muted mb-0 text-center py-4 js-leaderboard-filter-empty" hidden>لا يوجد طلاب في هذه الفئة</p>
                </div>
            </div>
        </div>

        @if ($surroundingUsers->isNotEmpty())
            <div class="card custom-card student-quizzes-panel">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="avatar avatar-sm bg-info-transparent"><i class="ri ri-group-line text-info"></i></span>
                        <h6 class="card-title mb-0">محيطك في الترتيب</h6>
                    </div>
                    <div class="student-leaderboard-list">
                        @foreach ($surroundingUsers as $entry)
                            @include('student.pages.gamification.leaderboards.partials.list-entry', [
                                'entry' => $entry,
                                'catalog' => $catalog,
                                'leaderboard' => $leaderboard,
                                'currentUser' => $user,
                                'compact' => true,
                            ])
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@include('student.pages.gamification.leaderboards.partials.player-modal')
@include('student.pages.gamification.leaderboards.partials.interaction-scripts')
@stop
