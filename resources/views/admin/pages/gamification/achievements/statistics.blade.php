@extends('admin.layouts.master')

@section('page-title')
    إحصائيات الإنجازات
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb">
            <nav><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.gamification.achievements.index') }}">الإنجازات</a></li>
                <li class="breadcrumb-item active">إحصائيات</li>
            </ol></nav>
        </div>

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow"><i class="fe fe-bar-chart-2 me-1"></i>تحليلات</span>
                    <h2 class="group-show-hero__title mb-2">إحصائيات الإنجازات</h2>
                    <p class="group-show-hero__desc mb-0">نظرة عامة على توزيع الإنجازات وأكثرها و أقلها إكمالاً.</p>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions">
                        <a href="{{ route('admin.gamification.achievements.index') }}" class="group-show-action">
                            <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                            <span class="group-show-action__text">العودة للقائمة</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card admin-stats-card admin-stats-card--blue"><div class="card-body">
                    <p class="admin-stats-card__label">إجمالي الإنجازات</p>
                    <h3 class="admin-stats-card__value">{{ number_format($totalAchievements) }}</h3>
                </div></div>
            </div>
            <div class="col-md-4">
                <div class="card admin-stats-card admin-stats-card--green"><div class="card-body">
                    <p class="admin-stats-card__label">إنجازات نشطة</p>
                    <h3 class="admin-stats-card__value">{{ number_format($activeAchievements) }}</h3>
                </div></div>
            </div>
            <div class="col-md-4">
                <div class="card admin-stats-card admin-stats-card--cyan"><div class="card-body">
                    <p class="admin-stats-card__label">إكمالات الطلاب</p>
                    <h3 class="admin-stats-card__value">{{ number_format($totalCompletions) }}</h3>
                </div></div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-4">
                <div class="card custom-card h-100"><div class="card-body">
                    <h6 class="mb-3">التوزيع حسب المستوى</h6>
                    <div class="d-flex flex-column gap-2">
                        @forelse ($byTier as $row)
                            <div class="d-flex justify-content-between align-items-center">
                                @include('admin.pages.gamification.achievements.partials.tier-badge', ['tier' => $row->tier])
                                <span class="fw-semibold">{{ $row->count }}</span>
                            </div>
                        @empty
                            <span class="text-muted">لا توجد بيانات</span>
                        @endforelse
                    </div>
                </div></div>
            </div>
            <div class="col-lg-4">
                <div class="card custom-card h-100"><div class="card-body">
                    <h6 class="mb-3">الأكثر إكمالاً</h6>
                    <ul class="list-unstyled mb-0 fs-13">
                        @forelse ($mostCompleted as $achievement)
                            <li class="mb-2 d-flex justify-content-between gap-2">
                                <a href="{{ route('admin.gamification.achievements.show', $achievement) }}" class="text-truncate">{{ $achievement->icon }} {{ $achievement->name }}</a>
                                <span class="badge bg-success-transparent">{{ $achievement->completions_count }}</span>
                            </li>
                        @empty
                            <li class="text-muted">—</li>
                        @endforelse
                    </ul>
                </div></div>
            </div>
            <div class="col-lg-4">
                <div class="card custom-card h-100"><div class="card-body">
                    <h6 class="mb-3">الأقل إكمالاً (مع إكمالات)</h6>
                    <ul class="list-unstyled mb-0 fs-13">
                        @forelse ($leastCompleted as $achievement)
                            <li class="mb-2 d-flex justify-content-between gap-2">
                                <a href="{{ route('admin.gamification.achievements.show', $achievement) }}" class="text-truncate">{{ $achievement->icon }} {{ $achievement->name }}</a>
                                <span class="badge bg-warning-transparent">{{ $achievement->completions_count }}</span>
                            </li>
                        @empty
                            <li class="text-muted">—</li>
                        @endforelse
                    </ul>
                </div></div>
            </div>
        </div>
    </div>
</div>
@stop
