@extends('student.layouts.master')

@section('page-title')
    ترتيبي
@stop

@section('content')
<div class="main-content app-content student-leaderboards-page">
    <div class="container-fluid">
        @php $catalog = app(\App\Services\Gamification\LeaderboardCatalog::class); @endphp

        <div class="d-md-flex d-block align-items-center justify-content-between my-4">
            <div>
                <h4 class="student-my-courses-welcome__title mb-1">ترتيبي في اللوحات</h4>
                <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('gamification.leaderboards.index') }}">المتصدرون</a></li>
                    <li class="breadcrumb-item active">ترتيبي</li>
                </ol></nav>
            </div>
            <a href="{{ route('gamification.leaderboards.index') }}" class="btn btn-outline-primary btn-sm mt-3 mt-md-0"><i class="ri ri-arrow-right-line me-1"></i>كل اللوحات</a>
        </div>

        <div class="row g-3">
            @forelse ($rankings as $item)
                @php $board = $item['leaderboard']; $rank = $item['rank']; @endphp
                <div class="col-lg-6">
                    <div class="card custom-card student-quizzes-panel h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-semibold mb-0">{{ $board->icon }} {{ $board->name }}</h6>
                                <span class="badge bg-primary fs-14">#{{ $rank['rank'] }}</span>
                            </div>
                            <p class="text-muted fs-12 mb-3">{{ $catalog->getMetricLabel($board->metric ?? 'total_points') }} — {{ $catalog->getPeriodLabel($board->period) }}</p>
                            <div class="row g-2 text-center">
                                <div class="col-4"><div class="p-2 rounded bg-light"><div class="fw-bold">{{ number_format($rank['score']) }}</div><small class="text-muted">النتيجة</small></div></div>
                                <div class="col-4"><div class="p-2 rounded bg-light"><div class="fw-bold">{{ $rank['percentile'] }}%</div><small class="text-muted">النسبة</small></div></div>
                                <div class="col-4">
                                    <div class="p-2 rounded bg-light d-flex flex-column align-items-center gap-1">
                                        @include('student.pages.gamification.leaderboards.partials.division-badge', [
                                            'division' => $rank['division'],
                                            'catalog' => $catalog,
                                            'size' => 'sm',
                                        ])
                                        <small class="text-muted">الفئة</small>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('gamification.leaderboards.show', $board) }}" class="btn btn-outline-primary btn-sm w-100 mt-3">عرض اللوحة</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12"><div class="card custom-card student-quizzes-panel"><div class="card-body text-center py-5 text-muted">لم تدخل أي لوحة بعد — ابدأ النشاط لكسب نقاط!</div></div></div>
            @endforelse
        </div>
    </div>
</div>
@stop
