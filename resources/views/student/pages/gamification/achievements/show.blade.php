@extends('student.layouts.master')

@section('page-title')
    {{ $achievement->name }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @include('student.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4">
            <div>
                <h4 class="mb-1">{{ $achievement->name }}</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('gamification.achievements.index') }}">إنجازاتي</a></li>
                        <li class="breadcrumb-item active">{{ $achievement->name }}</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card custom-card border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="fs-1 mb-3">{{ $achievement->icon ?? '🏆' }}</div>
                        <h3 class="fw-bold">{{ $achievement->name }}</h3>
                        <p class="text-muted">{{ $achievement->description }}</p>

                        <div class="row g-3 my-4">
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded">
                                    <small class="text-muted d-block">المستوى</small>
                                    <strong>{{ $achievement->tier }}</strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded">
                                    <small class="text-muted d-block">المتطلب</small>
                                    <strong>{{ \App\Support\Gamification\AchievementCriteriaMapper::formatForDisplay($achievement->criteria, $achievement->target_value) }}</strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded">
                                    <small class="text-muted d-block">المكافأة</small>
                                    <strong>{{ $achievement->points_reward ?? 0 }} نقطة</strong>
                                </div>
                            </div>
                        </div>

                        @php
                            $isCompleted = in_array($userAchievement->status, ['completed', 'claimed'], true);
                            $progress = (float) $userAchievement->progress_percentage;
                        @endphp

                        @if($isCompleted)
                            <span class="badge bg-success fs-14 mb-3">مكتمل</span>
                            @if($userAchievement->completed_at)
                                <p class="text-muted">اكتمل في {{ $userAchievement->completed_at->format('Y/m/d H:i') }}</p>
                            @endif
                            @if($userAchievement->status === 'completed' && ($achievement->points_reward ?? 0) > 0)
                                <form action="{{ route('gamification.achievements.claim', $userAchievement) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-warning rounded-pill">
                                        <i class="fe fe-gift me-1"></i> المطالبة بالمكافأة
                                    </button>
                                </form>
                            @elseif($userAchievement->status === 'claimed')
                                <span class="badge bg-info">تم المطالبة بالمكافأة</span>
                            @endif
                        @else
                            <div class="progress mb-2" style="height: 12px;">
                                <div class="progress-bar" style="width: {{ $progress }}%;"></div>
                            </div>
                            <p class="mb-0">{{ $userAchievement->current_value }} / {{ $achievement->target_value }}</p>
                        @endif

                        <div class="mt-4">
                            <a href="{{ route('gamification.achievements.index') }}" class="btn btn-outline-primary rounded-pill">
                                <i class="fe fe-arrow-right me-1"></i> العودة للإنجازات
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
