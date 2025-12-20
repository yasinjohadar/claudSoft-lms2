@extends('student.layouts.master')

@section('title', 'المستويات')

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="page-title fw-semibold fs-18 mb-0">المستويات</h4>
                <p class="fw-normal text-muted fs-14 mb-0">تقدمك عبر المستويات</p>
            </div>
            <div class="ms-md-auto d-flex gap-2 mt-3 mt-md-0">
                <a href="{{ route('gamification.levels.all') }}" class="btn btn-primary btn-wave">
                    <i class="ri-list-check me-1"></i> جميع المستويات
                </a>
            </div>
        </div>

        <!-- Current Level Card -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center mb-3 mb-md-0">
                                <div class="avatar avatar-xxl avatar-rounded bg-primary-transparent">
                                    <span class="fs-1">🏆</span>
                                </div>
                                <h3 class="mt-3 mb-1">المستوى {{ $levelInfo['current_level'] }}</h3>
                                <p class="text-muted mb-0">{{ $levelInfo['level_data']->name ?? 'مبتدئ' }}</p>
                            </div>
                            <div class="col-md-9">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <span class="avatar avatar-md bg-success-transparent">
                                                    <i class="ri-star-fill fs-18"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <p class="mb-0 text-muted fs-12">XP الحالي</p>
                                                <h5 class="fw-semibold mb-0">{{ number_format($levelInfo['total_xp']) }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <span class="avatar avatar-md bg-info-transparent">
                                                    <i class="ri-arrow-up-circle-fill fs-18"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <p class="mb-0 text-muted fs-12">XP للمستوى التالي</p>
                                                <h5 class="fw-semibold mb-0">{{ number_format($levelInfo['xp_needed']) }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <span class="avatar avatar-md bg-warning-transparent">
                                                    <i class="ri-percent-fill fs-18"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <p class="mb-0 text-muted fs-12">التقدم</p>
                                                <h5 class="fw-semibold mb-0">{{ round($levelInfo['level_progress']) }}%</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Progress Bar -->
                                <div class="mt-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fs-13">التقدم نحو المستوى {{ $levelInfo['current_level'] + 1 }}</span>
                                        <span class="fs-13 fw-semibold">{{ round($levelInfo['level_progress']) }}%</span>
                                    </div>
                                    <div class="progress progress-lg" style="height: 30px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-gradient"
                                             role="progressbar"
                                             style="width: {{ $levelInfo['level_progress'] }}%;"
                                             aria-valuenow="{{ $levelInfo['level_progress'] }}"
                                             aria-valuemin="0"
                                             aria-valuemax="100">
                                            {{ number_format($levelInfo['current_level_xp']) }} / {{ number_format($levelInfo['next_level_xp']) }} XP
                                        </div>
                                    </div>
                                </div>

                                @if($timeToNextLevel)
                                <div class="alert alert-info mt-3 mb-0">
                                    <i class="ri-time-line me-2"></i>
                                    الوقت المتوقع للمستوى التالي: <strong>{{ $timeToNextLevel }}</strong>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nearby Levels -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">المستويات القريبة</div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($nearbyLevels as $level)
                            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-3">
                                <div class="card custom-card shadow-sm {{ $level->level == $levelInfo['current_level'] ? 'border-primary' : '' }}">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            @if($level->level < $levelInfo['current_level'])
                                                <span class="avatar avatar-xl bg-success-transparent">
                                                    <i class="ri-check-line fs-1"></i>
                                                </span>
                                            @elseif($level->level == $levelInfo['current_level'])
                                                <span class="avatar avatar-xl bg-primary-transparent">
                                                    <i class="ri-star-fill fs-1"></i>
                                                </span>
                                            @else
                                                <span class="avatar avatar-xl bg-secondary-transparent">
                                                    <i class="ri-lock-line fs-1"></i>
                                                </span>
                                            @endif
                                        </div>
                                        <h5 class="fw-semibold mb-1">المستوى {{ $level->level }}</h5>
                                        <p class="text-muted mb-2 fs-12">{{ $level->name }}</p>
                                        <p class="mb-2">
                                            <span class="badge bg-primary-transparent">
                                                {{ number_format($level->total_xp_required) }} XP
                                            </span>
                                        </p>
                                        @if($level->level == $levelInfo['current_level'])
                                            <span class="badge bg-primary">المستوى الحالي</span>
                                        @elseif($level->level < $levelInfo['current_level'])
                                            <span class="badge bg-success">مكتمل</span>
                                        @else
                                            <span class="badge bg-secondary">مقفل</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End::app-content -->

@push('styles')
<style>
.bg-gradient {
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
}
</style>
@endpush
@endsection
