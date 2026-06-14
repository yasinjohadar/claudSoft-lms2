@extends('student.layouts.master')

@section('page-title')
    {{ $achievement->name }}
@stop

@section('content')
@php
    $isCompleted = in_array($userAchievement->status, ['completed', 'claimed'], true);
    $progress = (float) $userAchievement->progress_percentage;
    $tier = $achievement->tier ?? 'bronze';
    $tierLabels = [
        'bronze' => 'برونزي', 'silver' => 'فضي', 'gold' => 'ذهبي',
        'platinum' => 'بلاتيني', 'diamond' => 'ماسي',
    ];
    $requirementText = \App\Support\Gamification\AchievementCriteriaMapper::formatForDisplay(
        $achievement->criteria,
        $achievement->target_value
    );
@endphp

<div class="main-content app-content student-achievements-page student-achievements-show-page">
    <div class="container-fluid">

        @include('student.components.alerts')

        <div class="my-4 page-header-breadcrumb">
            <nav><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('gamification.achievements.index') }}">إنجازاتي</a></li>
                <li class="breadcrumb-item active">{{ $achievement->name }}</li>
            </ol></nav>
        </div>

        <div class="student-achievement-show-card student-achievement-card--{{ $tier }} {{ $isCompleted ? 'is-completed' : 'is-active' }}">
            <div class="student-achievement-show-card__glow"></div>
            <div class="text-center position-relative">
                <div class="student-achievement-show-card__icon">{{ $achievement->icon ?? '🏆' }}</div>
                <span class="student-achievement-card__tier badge">{{ $tierLabels[$tier] ?? $tier }}</span>
                <h3 class="fw-bold mt-3 mb-2">{{ $achievement->name }}</h3>
                @if($achievement->description)
                    <p class="text-muted mb-4 mx-auto" style="max-width: 520px;">{{ $achievement->description }}</p>
                @endif
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="student-achievement-show-stat">
                        <span class="student-achievement-show-stat__label">المتطلب</span>
                        <strong>{{ $requirementText }}</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="student-achievement-show-stat">
                        <span class="student-achievement-show-stat__label">المكافأة</span>
                        <strong>{{ number_format($achievement->points_reward ?? 0) }} نقطة</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="student-achievement-show-stat">
                        <span class="student-achievement-show-stat__label">الحالة</span>
                        <strong>{{ $isCompleted ? 'مكتمل' : 'قيد التقدم' }}</strong>
                    </div>
                </div>
            </div>

            @if($isCompleted)
                <div class="text-center">
                    <span class="badge bg-success-transparent fs-13 px-3 py-2 mb-3"><i class="fe fe-check-circle me-1"></i>مكتمل</span>
                    @if($userAchievement->completed_at)
                        <p class="text-muted mb-3">اكتمل في {{ $userAchievement->completed_at->format('Y/m/d H:i') }}</p>
                    @endif
                    @if($userAchievement->status === 'completed' && ($achievement->points_reward ?? 0) > 0)
                        <form action="{{ route('gamification.achievements.claim', $userAchievement) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-warning rounded-pill px-4">
                                <i class="fe fe-package me-1"></i>المطالبة بالمكافأة
                            </button>
                        </form>
                    @elseif($userAchievement->status === 'claimed')
                        <span class="badge bg-info-transparent">تم المطالبة بالمكافأة</span>
                    @endif
                </div>
            @else
                <div class="student-achievement-show-progress mx-auto" style="max-width: 480px;">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">التقدم</span>
                        <span class="fw-bold">{{ number_format($progress, 0) }}%</span>
                    </div>
                    <div class="student-achievement-card__track">
                        <div class="student-achievement-card__bar" style="--progress: {{ max(0, min(100, $progress)) }}%; width: {{ max(0, min(100, $progress)) }}%;"></div>
                    </div>
                    <p class="text-center text-muted mt-2 mb-0">{{ $userAchievement->current_value }} / {{ $achievement->target_value }}</p>
                </div>
            @endif

            <div class="text-center mt-4 pt-2">
                <a href="{{ route('gamification.achievements.index') }}" class="btn btn-outline-primary rounded-pill">
                    <i class="fe fe-arrow-right me-1"></i>العودة للإنجازات
                </a>
            </div>
        </div>
    </div>
</div>
@stop
