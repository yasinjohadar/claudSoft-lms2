@extends('student.layouts.master')

@section('page-title')
    إنجازاتي
@stop

@section('content')
<div class="main-content app-content student-achievements-page">
    <div class="container-fluid">

        @include('student.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4">
            <div>
                <h4 class="student-my-courses-welcome__title mb-1">إنجازاتي</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('gamification.dashboard') }}">التلعيب</a></li>
                        <li class="breadcrumb-item active">إنجازاتي</li>
                    </ol>
                </nav>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('gamification.dashboard') }}" class="btn btn-outline-primary rounded-pill">
                    <i class="fe fe-bar-chart-2 me-1"></i>لوحة التلعيب
                </a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card custom-card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <span class="avatar avatar-md bg-success-transparent mb-2">
                            <i class="fe fe-award text-success fs-20"></i>
                        </span>
                        <h3 class="mb-1">{{ $stats['completed'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">إنجازات مكتملة</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card custom-card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <span class="avatar avatar-md bg-primary-transparent mb-2">
                            <i class="fe fe-trending-up text-primary fs-20"></i>
                        </span>
                        <h3 class="mb-1">{{ $stats['in_progress'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">قيد التقدم</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card custom-card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <span class="avatar avatar-md bg-warning-transparent mb-2">
                            <i class="fe fe-percent text-warning fs-20"></i>
                        </span>
                        <h3 class="mb-1">{{ $stats['completion_rate'] ?? 0 }}%</h3>
                        <p class="text-muted mb-0">نسبة الإكمال</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card custom-card student-quizzes-panel mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="avatar avatar-sm bg-success-transparent">
                        <i class="fe fe-check-circle text-success"></i>
                    </span>
                    <h6 class="card-title mb-0">الإنجازات المكتملة ({{ $completedAchievements->count() }})</h6>
                </div>

                @if($completedAchievements->isNotEmpty())
                    <div class="row g-4">
                        @foreach($completedAchievements as $userAchievement)
                            @php $achievement = $userAchievement->achievement; @endphp
                            @include('student.pages.gamification.partials.achievement-card', [
                                'achievement' => $achievement,
                                'userAchievement' => $userAchievement,
                                'isCompleted' => true,
                            ])
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center py-4 mb-0">لم تكمل أي إنجاز بعد. استمر في التعلم!</p>
                @endif
            </div>
        </div>

        @if($inProgressAchievements->isNotEmpty())
            <div class="card custom-card student-quizzes-panel mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <span class="avatar avatar-sm bg-primary-transparent">
                            <i class="fe fe-loader text-primary"></i>
                        </span>
                        <h6 class="card-title mb-0">قيد التقدم ({{ $inProgressAchievements->count() }})</h6>
                    </div>
                    <div class="row g-4">
                        @foreach($inProgressAchievements as $userAchievement)
                            @php $achievement = $userAchievement->achievement; @endphp
                            @include('student.pages.gamification.partials.achievement-card', [
                                'achievement' => $achievement,
                                'userAchievement' => $userAchievement,
                                'isCompleted' => false,
                            ])
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if($notStartedAchievements->isNotEmpty())
            <div class="card custom-card student-quizzes-panel">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <span class="avatar avatar-sm bg-secondary-transparent">
                            <i class="fe fe-lock text-muted"></i>
                        </span>
                        <h6 class="card-title mb-0">لم تبدأ بعد ({{ $notStartedAchievements->count() }})</h6>
                    </div>
                    <div class="row g-4">
                        @foreach($notStartedAchievements as $userAchievement)
                            @php $achievement = $userAchievement->achievement; @endphp
                            @include('student.pages.gamification.partials.achievement-card', [
                                'achievement' => $achievement,
                                'userAchievement' => $userAchievement,
                                'isCompleted' => false,
                                'isLocked' => true,
                            ])
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
@stop
