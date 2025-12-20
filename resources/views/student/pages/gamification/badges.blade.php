@extends('student.layouts.master')

@section('page-title')
    شاراتي
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <h4 class="mb-0">شاراتي</h4>
            </div>

            <!-- الشارات المكتسبة -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-medal me-2 text-success"></i>الشارات المكتسبة ({{ count($earnedBadges ?? []) }})</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($earnedBadges ?? [] as $badge)
                            <div class="col-lg-3 col-md-4 col-6 mb-4">
                                <div class="card border-0 shadow-sm text-center h-100">
                                    <div class="card-body">
                                        <div class="fs-1 mb-2">{{ $badge->icon ?? '🏅' }}</div>
                                        <h6 class="fw-bold">{{ $badge->name }}</h6>
                                        <p class="small text-muted mb-2">{{ $badge->description }}</p>
                                        <span class="badge bg-success">+{{ $badge->points_reward }} نقطة</span>
                                        <p class="small text-muted mt-2 mb-0">
                                            <i class="fas fa-calendar-check me-1"></i>
                                            {{ $badge->pivot->awarded_at->format('Y/m/d') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <p class="text-muted text-center py-4">لم تحصل على شارات بعد. استمر في التعلم!</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- جميع الشارات -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-list me-2 text-muted"></i>جميع الشارات</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($allBadges ?? [] as $badge)
                            <div class="col-lg-3 col-md-4 col-6 mb-4">
                                <div class="card border-0 shadow-sm text-center h-100" style="opacity: {{ ($badge->is_earned ?? false) ? '1' : '0.7' }};">
                                    <div class="card-body">
                                        <div class="fs-1 mb-2" style="{{ ($badge->is_earned ?? false) ? '' : 'filter: grayscale(100%);' }}">{{ $badge->icon ?? '🏅' }}</div>
                                        <h6 class="fw-bold">{{ $badge->name ?? 'شارة' }}</h6>
                                        <p class="small text-muted mb-2">{{ $badge->description ?? '' }}</p>
                                        <span class="badge {{ ($badge->is_earned ?? false) ? 'bg-success' : 'bg-secondary' }}">+{{ $badge->points_value ?? 0 }} نقطة</span>
                                        @if($badge->is_earned ?? false)
                                            <p class="small text-success mt-2 mb-0">
                                                <i class="fas fa-check-circle me-1"></i>تم الحصول عليه
                                            </p>
                                        @else
                                            <p class="small text-muted mt-2 mb-0">
                                                <i class="fas fa-lock me-1"></i>غير مكتسب
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <p class="text-muted text-center py-4">لا توجد شارات متاحة حالياً</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
