@extends('admin.layouts.master')

@section('page-title')
    {{ $leaderboard->name }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        @include('admin.components.alerts')
        @php $catalog = app(\App\Services\Gamification\LeaderboardCatalog::class); @endphp

        <div class="my-4 page-header-breadcrumb">
            <nav><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.gamification.leaderboards.index') }}">لوحات المتصدرين</a></li>
                <li class="breadcrumb-item active">{{ $leaderboard->name }}</li>
            </ol></nav>
        </div>

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">{{ $leaderboard->icon }} لوحة متصدرين</span>
                    <h2 class="group-show-hero__title mb-2">{{ $leaderboard->name }}</h2>
                    <p class="group-show-hero__desc mb-0">{{ $leaderboard->description ?: '—' }}</p>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions flex-wrap">
                        <form action="{{ route('admin.gamification.leaderboards.update-data', $leaderboard) }}" method="POST" class="d-inline">@csrf
                            <button type="submit" class="group-show-action"><span class="group-show-action__icon"><i class="fe fe-refresh-cw"></i></span><span class="group-show-action__text">تحديث الترتيب</span></button>
                        </form>
                        @if ($leaderboard->rewards)
                        <form action="{{ route('admin.gamification.leaderboards.award-rewards', $leaderboard) }}" method="POST" class="d-inline">@csrf
                            <button type="submit" class="group-show-action group-show-action--primary"><span class="group-show-action__icon"><i class="fe fe-package"></i></span><span class="group-show-action__text">منح الجوائز</span></button>
                        </form>
                        @endif
                        <a href="{{ route('admin.gamification.leaderboards.edit', $leaderboard) }}" class="group-show-action"><span class="group-show-action__icon"><i class="fe fe-edit"></i></span><span class="group-show-action__text">تعديل</span></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3"><div class="card admin-stats-card admin-stats-card--blue"><div class="card-body"><p class="admin-stats-card__label">المشاركون</p><h3 class="admin-stats-card__value">{{ number_format($stats['total_participants'] ?? 0) }}</h3></div></div></div>
            <div class="col-md-3"><div class="card admin-stats-card admin-stats-card--green"><div class="card-body"><p class="admin-stats-card__label">أعلى نتيجة</p><h3 class="admin-stats-card__value">{{ number_format($stats['highest_score'] ?? 0) }}</h3></div></div></div>
            <div class="col-md-3"><div class="card admin-stats-card admin-stats-card--cyan"><div class="card-body"><p class="admin-stats-card__label">متوسط النتيجة</p><h3 class="admin-stats-card__value">{{ number_format($stats['average_score'] ?? 0, 1) }}</h3></div></div></div>
            <div class="col-md-3"><div class="card admin-stats-card admin-stats-card--orange"><div class="card-body"><p class="admin-stats-card__label">آخر تحديث</p><h3 class="admin-stats-card__value fs-16">{{ $stats['last_updated']?->format('m/d H:i') ?? '—' }}</h3></div></div></div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-4">
                <div class="card custom-card h-100"><div class="card-body">
                    <h6 class="mb-3">تفاصيل اللوحة</h6>
                    <ul class="list-unstyled mb-0 fs-13">
                        <li class="mb-2"><strong>النوع:</strong> {{ $catalog->getTypeLabel($leaderboard->type) }}</li>
                        <li class="mb-2"><strong>المقياس:</strong> {{ $catalog->getMetricLabel($leaderboard->metric ?? 'total_points') }}</li>
                        <li class="mb-2"><strong>الفترة:</strong> {{ $catalog->getPeriodLabel($leaderboard->period) }}</li>
                        <li class="mb-2"><strong>الحد الأقصى:</strong> {{ $leaderboard->max_entries }}</li>
                        <li><strong>الحالة:</strong> {{ $leaderboard->is_active ? 'نشط' : 'معطّل' }} / {{ $leaderboard->is_visible ? 'مرئي' : 'مخفي' }}</li>
                    </ul>
                </div></div>
            </div>
            <div class="col-lg-8">
                <div class="card custom-card h-100"><div class="card-body">
                    <h6 class="mb-3">توزيع الفئات</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @forelse ($stats['by_division'] ?? [] as $division => $count)
                            <span class="badge bg-{{ $catalog->getDivisionColor($division) }}-transparent">{{ $catalog->getDivisionLabel($division) }}: {{ $count }}</span>
                        @empty
                            <span class="text-muted">لا توجد بيانات</span>
                        @endforelse
                    </div>
                </div></div>
            </div>
        </div>

        <div class="card custom-card group-show-members-card">
            <div class="card-header border-0"><h6 class="mb-0">المتصدرون <span class="group-show-members-card__count">{{ $entries->count() }}</span></h6></div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>#</th><th>الطالب</th><th>النتيجة</th><th>الفئة</th><th>تغيّر</th></tr></thead>
                        <tbody>
                            @forelse ($entries as $entry)
                                <tr>
                                    <td>
                                        @if ($entry->rank === 1) 🥇
                                        @elseif ($entry->rank === 2) 🥈
                                        @elseif ($entry->rank === 3) 🥉
                                        @else {{ $entry->rank }}
                                        @endif
                                    </td>
                                    <td>{{ $entry->user->name ?? '—' }}</td>
                                    <td><strong>{{ number_format($entry->score) }}</strong></td>
                                    <td><span class="badge bg-{{ $catalog->getDivisionColor($entry->division) }}">{{ $catalog->getDivisionLabel($entry->division) }}</span></td>
                                    <td>
                                        @if ($entry->rank_change > 0)<span class="text-success">↑{{ $entry->rank_change }}</span>
                                        @elseif ($entry->rank_change < 0)<span class="text-danger">↓{{ abs($entry->rank_change) }}</span>
                                        @else <span class="text-muted">—</span>@endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">لا يوجد مشاركون — اضغط «تحديث الترتيب»</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
