@extends('student.layouts.master')

@section('page-title')
    التحديات
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <h4 class="mb-0">التحديات</h4>
            </div>

            <!-- التحديات اليومية -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-calendar-day me-2 text-primary"></i>التحديات اليومية</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($dailyChallenges ?? [] as $challenge)
                            <div class="col-lg-4 col-md-6 mb-3">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <span class="fs-2">{{ $challenge->icon ?? '🎯' }}</span>
                                            <span class="badge bg-primary">+{{ $challenge->points_reward }} نقطة</span>
                                        </div>
                                        <h6 class="fw-bold">{{ $challenge->name }}</h6>
                                        <p class="small text-muted mb-3">{{ $challenge->description }}</p>
                                        <div class="progress mb-2" style="height: 10px;">
                                            <div class="progress-bar bg-success" style="width: {{ $challenge->progress ?? 0 }}%;"></div>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <small class="text-muted">{{ $challenge->current ?? 0 }}/{{ $challenge->target_value }}</small>
                                            <small class="text-muted">{{ $challenge->progress ?? 0 }}%</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <p class="text-muted text-center py-4">لا توجد تحديات يومية حالياً</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- التحديات الأسبوعية -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-calendar-week me-2 text-success"></i>التحديات الأسبوعية</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($weeklyChallenges ?? [] as $challenge)
                            <div class="col-lg-4 col-md-6 mb-3">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <span class="fs-2">{{ $challenge->icon ?? '🎯' }}</span>
                                            <span class="badge bg-success">+{{ $challenge->points_reward }} نقطة</span>
                                        </div>
                                        <h6 class="fw-bold">{{ $challenge->name }}</h6>
                                        <p class="small text-muted mb-3">{{ $challenge->description }}</p>
                                        <div class="progress mb-2" style="height: 10px;">
                                            <div class="progress-bar bg-success" style="width: {{ $challenge->progress ?? 0 }}%;"></div>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <small class="text-muted">{{ $challenge->current ?? 0 }}/{{ $challenge->target_value }}</small>
                                            <small class="text-muted">{{ $challenge->progress ?? 0 }}%</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <p class="text-muted text-center py-4">لا توجد تحديات أسبوعية حالياً</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- التحديات المكتملة -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-check-circle me-2 text-muted"></i>التحديات المكتملة</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($completedChallenges ?? [] as $challenge)
                            <div class="col-lg-4 col-md-6 mb-3">
                                <div class="card border-0 shadow-sm h-100 bg-light">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <span class="fs-2">{{ $challenge->icon ?? '🎯' }}</span>
                                            <span class="badge bg-success"><i class="fas fa-check"></i></span>
                                        </div>
                                        <h6 class="fw-bold">{{ $challenge->name }}</h6>
                                        <p class="small text-muted mb-0">تم الإكمال</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <p class="text-muted text-center py-4">لم تكمل تحديات بعد</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
