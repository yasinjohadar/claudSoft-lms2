@extends('student.layouts.master')

@section('page-title')
    لوحات المتصدرين
@stop

@section('content')
<div class="main-content app-content student-leaderboards-page student-leaderboards-index-page">
    <div class="container-fluid">
        @include('student.components.alerts')

        @php $catalog = app(\App\Services\Gamification\LeaderboardCatalog::class); @endphp

        <div class="student-leaderboard-show-hero mb-4">
            <div class="d-md-flex align-items-start justify-content-between gap-3">
                <div>
                    <span class="student-leaderboard-show-hero__icon">🏆</span>
                    <h4 class="student-my-courses-welcome__title mb-1">لوحات المتصدرين</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active">المتصدرون</li>
                        </ol>
                    </nav>
                    <p class="text-muted fs-13 mb-0 mt-2">تنافس مع زملائك، تابع ترتيبك، واكتشف من يتصدر كل لوحة</p>
                </div>
                <div class="d-flex flex-wrap gap-2 shrink-0">
                    <a href="{{ route('gamification.leaderboards.my-rank') }}" class="btn btn-primary btn-sm">
                        <i class="ri ri-user-star-line me-1"></i>ترتيبي
                    </a>
                    <a href="{{ route('gamification.dashboard') }}" class="btn btn-light btn-sm">
                        <i class="ri ri-arrow-right-line me-1"></i>التلعيب
                    </a>
                </div>
            </div>
        </div>

        @include('student.pages.gamification.leaderboards.partials.index-stats', ['indexStats' => $indexStats ?? []])

        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="avatar avatar-sm bg-primary-transparent"><i class="ri ri-trophy-line text-primary"></i></span>
            <h6 class="mb-0 fw-semibold">اللوحات النشطة <span class="text-muted fw-normal">({{ $leaderboards->count() }})</span></h6>
        </div>

        <div class="row g-4">
            @forelse ($leaderboards as $board)
                <div class="col-xl-6 col-lg-6">
                    @include('student.pages.gamification.leaderboards.partials.board-card', [
                        'board' => $board,
                        'catalog' => $catalog,
                    ])
                </div>
            @empty
                <div class="col-12">
                    <div class="card custom-card student-quizzes-panel">
                        <div class="card-body text-center py-5">
                            <span class="fs-1 d-block mb-3">🏆</span>
                            <h6 class="fw-semibold mb-2">لا توجد لوحات متاحة حالياً</h6>
                            <p class="text-muted mb-0 fs-13">ستظهر لوحات المتصدرين هنا عند تفعيلها من الإدارة</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@stop
