@extends('student.layouts.master')

@section('page-title')
    {{ $leaderboard->name }} — الفئات
@stop

@section('content')
<div class="main-content app-content student-leaderboards-page">
    <div class="container-fluid">
        @php $catalog = app(\App\Services\Gamification\LeaderboardCatalog::class); @endphp

        <div class="d-md-flex d-block align-items-center justify-content-between my-4">
            <div>
                <h4 class="student-my-courses-welcome__title mb-1">فئة {{ $catalog->getDivisionLabel($division) }}</h4>
                <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('gamification.leaderboards.index') }}">المتصدرون</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('gamification.leaderboards.show', $leaderboard) }}">{{ $leaderboard->name }}</a></li>
                    <li class="breadcrumb-item active">{{ $catalog->getDivisionLabel($division) }}</li>
                </ol></nav>
            </div>
            <a href="{{ route('gamification.leaderboards.show', $leaderboard) }}" class="btn btn-outline-primary btn-sm mt-3 mt-md-0"><i class="ri ri-arrow-right-line me-1"></i>العودة</a>
        </div>

        <div class="student-leaderboard-division-tabs mb-4">
            @foreach (['bronze','silver','gold','platinum','diamond'] as $div)
                @include('student.pages.gamification.leaderboards.partials.division-tab', [
                    'division' => $div,
                    'catalog' => $catalog,
                    'tag' => 'a',
                    'href' => route('gamification.leaderboards.division', [$leaderboard, $div]),
                    'isActive' => $div === $division,
                ])
            @endforeach
        </div>

        <div class="card custom-card student-quizzes-panel">
            <div class="card-body">
                <div class="student-leaderboard-list">
                @forelse ($entries as $entry)
                    @include('student.pages.gamification.leaderboards.partials.list-entry', [
                        'entry' => $entry,
                        'catalog' => $catalog,
                        'leaderboard' => $leaderboard,
                    ])
                @empty
                    <p class="text-muted text-center py-5 mb-0">لا يوجد طلاب في هذه الفئة</p>
                @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@include('student.pages.gamification.leaderboards.partials.player-modal')
@include('student.pages.gamification.leaderboards.partials.interaction-scripts')
@stop
