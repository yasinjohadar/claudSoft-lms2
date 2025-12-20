@extends('student.layouts.master')

@section('page-title')
    إنجازاتي
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <h4 class="mb-0">إنجازاتي</h4>
            </div>

            <!-- الإنجازات المفتوحة -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-trophy me-2 text-warning"></i>الإنجازات المفتوحة ({{ count($unlockedAchievements ?? []) }})</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($unlockedAchievements ?? [] as $achievement)
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <div class="fs-1 mb-2">{{ $achievement->icon ?? '🏆' }}</div>
                                        <h5 class="fw-bold">{{ $achievement->name }}</h5>
                                        <span class="badge bg-{{ $achievement->tier == 'diamond' ? 'info' : ($achievement->tier == 'gold' ? 'warning' : ($achievement->tier == 'silver' ? 'secondary' : 'danger')) }} mb-2">
                                            {{ __('gamification.tier.'.$achievement->tier) }}
                                        </span>
                                        <p class="small text-muted mb-2">{{ $achievement->description }}</p>
                                        <span class="badge bg-success">+{{ $achievement->points_reward }} نقطة</span>
                                        <p class="small text-muted mt-2 mb-0">
                                            <i class="fas fa-unlock me-1"></i>
                                            {{ $achievement->pivot->unlocked_at->format('Y/m/d') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <p class="text-muted text-center py-4">لم تفتح إنجازات بعد</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- الإنجازات المقفلة -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-lock me-2 text-muted"></i>الإنجازات المقفلة</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($lockedAchievements ?? [] as $achievement)
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card border-0 shadow-sm h-100" style="opacity: 0.6;">
                                    <div class="card-body text-center">
                                        <div class="fs-1 mb-2" style="filter: grayscale(100%);">{{ $achievement->icon ?? '🏆' }}</div>
                                        <h5 class="fw-bold">{{ $achievement->name }}</h5>
                                        <span class="badge bg-secondary mb-2">{{ __('gamification.tier.'.$achievement->tier) }}</span>
                                        <p class="small text-muted mb-2">{{ $achievement->description }}</p>
                                        <div class="progress mb-2" style="height: 6px;">
                                            <div class="progress-bar" style="width: {{ $achievement->progress ?? 0 }}%;"></div>
                                        </div>
                                        <small class="text-muted">{{ $achievement->current ?? 0 }}/{{ $achievement->requirement_value }}</small>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <p class="text-muted text-center py-4">فتحت جميع الإنجازات!</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
