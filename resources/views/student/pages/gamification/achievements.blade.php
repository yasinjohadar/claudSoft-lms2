@extends('student.layouts.master')

@section('page-title')
    إنجازاتي
@stop

@section('content')
<div class="main-content app-content student-achievements-page">
    <div class="container-fluid">

        @include('student.components.alerts')

        <div class="student-achievements-hero mb-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <span class="student-achievements-hero__eyebrow"><i class="fe fe-award me-1"></i>التلعيب</span>
                    <h4 class="student-my-courses-welcome__title mb-2">إنجازاتي</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('gamification.dashboard') }}">التلعيب</a></li>
                            <li class="breadcrumb-item active">إنجازاتي</li>
                        </ol>
                    </nav>
                    <p class="student-achievements-hero__desc mt-3 mb-0">تابع تقدّمك، افتح الإنجازات، واجمع نقاط المكافآت مع كل خطوة في رحلة التعلم.</p>
                </div>
                <div class="col-lg-5">
                    <div class="student-achievements-ring-wrap">
                        <div class="student-achievements-ring" style="--ring-pct: {{ min(100, $stats['completion_rate'] ?? 0) }}%">
                            <div class="student-achievements-ring__inner">
                                <span class="student-achievements-ring__value" data-countup="{{ round($stats['completion_rate'] ?? 0, 1) }}" data-countup-suffix="%" data-countup-decimals="1">0</span>
                                <span class="student-achievements-ring__label">نسبة الإكمال</span>
                            </div>
                        </div>
                        <div class="student-achievements-hero__actions">
                            <a href="{{ route('gamification.dashboard') }}" class="btn btn-light btn-sm rounded-pill">
                                <i class="fe fe-bar-chart-2 me-1"></i>لوحة التلعيب
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('student.pages.gamification.partials.achievements-stats', ['stats' => $stats])

        @if(($recommended ?? collect())->isNotEmpty())
            @php $spotlightIds = $recommended->pluck('id')->all(); @endphp
            <div class="student-achievements-spotlight mb-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="avatar avatar-sm bg-warning-transparent"><i class="fe fe-zap text-warning"></i></span>
                    <h6 class="mb-0 fw-semibold">أقرب للإكمال</h6>
                </div>
                <div class="row g-3">
                    @foreach($recommended as $index => $userAchievement)
                        @include('student.pages.gamification.partials.achievement-card', [
                            'userAchievement' => $userAchievement,
                            'isCompleted' => false,
                            'index' => $index,
                        ])
                    @endforeach
                </div>
            </div>
        @else
            @php $spotlightIds = []; @endphp
        @endif

        @include('student.pages.gamification.partials.achievements-filters')

        @php
            $gridAchievements = isset($spotlightIds) && count($spotlightIds)
                ? $userAchievements->reject(fn ($ua) => in_array($ua->id, $spotlightIds))
                : $userAchievements;
        @endphp

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div class="d-flex align-items-center gap-2">
                <span class="avatar avatar-sm bg-primary-transparent"><i class="fe fe-grid text-primary"></i></span>
                <h6 class="mb-0 fw-semibold">جميع الإنجازات <span class="text-muted fw-normal" id="achievementVisibleCount">({{ $gridAchievements->count() }})</span></h6>
            </div>
        </div>

        <div class="row g-4" id="achievementGrid">
            @forelse($gridAchievements as $index => $userAchievement)
                @php
                    $isCompleted = in_array($userAchievement->status, ['completed', 'claimed'], true);
                    $isLocked = !$isCompleted && ($userAchievement->progress_percentage ?? 0) <= 0;
                @endphp
                @include('student.pages.gamification.partials.achievement-card', [
                    'userAchievement' => $userAchievement,
                    'isCompleted' => $isCompleted,
                    'isLocked' => $isLocked,
                    'index' => $index,
                ])
            @empty
                <div class="col-12">
                    @include('student.pages.gamification.partials.badges-empty', [
                        'title' => 'لا توجد إنجازات',
                        'message' => 'ستظهر الإنجازات هنا عند تفعيلها من الإدارة.',
                    ])
                </div>
            @endforelse
        </div>

        <div id="achievementEmptyFiltered" class="d-none">
            <div class="student-my-courses-empty text-center py-5">
                <div class="student-my-courses-empty__icon mb-4"><i class="fe fe-filter"></i></div>
                <h4 class="mb-2">لا توجد إنجازات مطابقة</h4>
                <p class="text-muted mb-3">جرّب تغيير الفلتر أو اختر «الكل» لعرض جميع الإنجازات.</p>
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" id="achievementResetFilters">
                    <i class="fe fe-rotate-cw me-1"></i>إظهار الكل
                </button>
            </div>
        </div>

    </div>
</div>

@include('student.pages.gamification.partials.achievement-modal')
@stop

@section('scripts')
@include('student.pages.gamification.partials.achievement-interaction-scripts')
@stop
