@extends('admin.layouts.master')

@section('page-title')
    {{ $achievement->name }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb">
            <nav><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.gamification.achievements.index') }}">الإنجازات</a></li>
                <li class="breadcrumb-item active">{{ $achievement->name }}</li>
            </ol></nav>
        </div>

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">{{ $achievement->icon ?? '🏆' }} إنجاز</span>
                    <h2 class="group-show-hero__title mb-2">{{ $achievement->name }}</h2>
                    <p class="group-show-hero__desc mb-0">{{ $achievement->description ?: '—' }}</p>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions flex-wrap">
                        <a href="{{ route('admin.gamification.achievements.edit', $achievement) }}" class="group-show-action">
                            <span class="group-show-action__icon"><i class="fe fe-edit"></i></span>
                            <span class="group-show-action__text">تعديل</span>
                        </a>
                        <form action="{{ route('admin.gamification.achievements.toggle-active', $achievement) }}" method="POST" class="d-inline">@csrf
                            <button type="submit" class="group-show-action">
                                <span class="group-show-action__icon"><i class="fe fe-power"></i></span>
                                <span class="group-show-action__text">{{ $achievement->is_active ? 'تعطيل' : 'تفعيل' }}</span>
                            </button>
                        </form>
                        <a href="{{ route('admin.gamification.achievements.index') }}" class="group-show-action">
                            <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                            <span class="group-show-action__text">رجوع</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card admin-stats-card admin-stats-card--blue"><div class="card-body">
                    <p class="admin-stats-card__label">بدأوا التتبع</p>
                    <h3 class="admin-stats-card__value">{{ number_format($stats['total_started']) }}</h3>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="card admin-stats-card admin-stats-card--cyan"><div class="card-body">
                    <p class="admin-stats-card__label">قيد التقدم</p>
                    <h3 class="admin-stats-card__value">{{ number_format($stats['in_progress']) }}</h3>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="card admin-stats-card admin-stats-card--green"><div class="card-body">
                    <p class="admin-stats-card__label">مكتمل</p>
                    <h3 class="admin-stats-card__value">{{ number_format($stats['completed']) }}</h3>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="card admin-stats-card admin-stats-card--orange"><div class="card-body">
                    <p class="admin-stats-card__label">نسبة الإكمال</p>
                    <h3 class="admin-stats-card__value">{{ $stats['completion_rate'] }}%</h3>
                </div></div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-4">
                <div class="card custom-card h-100"><div class="card-body">
                    <h6 class="mb-3">تفاصيل الإنجاز</h6>
                    <ul class="list-unstyled mb-0 fs-13">
                        <li class="mb-2"><strong>Slug:</strong> <code>{{ $achievement->slug }}</code></li>
                        <li class="mb-2"><strong>المستوى:</strong> @include('admin.pages.gamification.achievements.partials.tier-badge', ['tier' => $achievement->tier])</li>
                        <li class="mb-2"><strong>المتطلب:</strong> {{ \App\Support\Gamification\AchievementCriteriaMapper::formatForDisplay($achievement->criteria, $achievement->target_value) }}</li>
                        <li class="mb-2"><strong>مكافأة النقاط:</strong> {{ number_format($achievement->points_reward ?? 0) }}</li>
                        <li class="mb-2"><strong>الشارة المرتبطة:</strong> {{ $achievement->badge?->name ?? '—' }}</li>
                        <li><strong>الحالة:</strong> {{ $achievement->is_active ? 'نشط' : 'غير نشط' }}</li>
                    </ul>
                </div></div>
            </div>
            <div class="col-lg-8">
                <div class="card custom-card h-100"><div class="card-body">
                    <h6 class="mb-3">توزيع التقدم</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-primary-transparent">بدأوا: {{ $stats['total_started'] }}</span>
                        <span class="badge bg-warning-transparent">قيد التقدم: {{ $stats['in_progress'] }}</span>
                        <span class="badge bg-success-transparent">مكتمل: {{ $stats['completed'] }}</span>
                    </div>
                </div></div>
            </div>
        </div>

        <div class="card custom-card group-show-members-card">
            <div class="card-header border-0">
                <h6 class="mb-0">آخر المكمّلين <span class="group-show-members-card__count">{{ $recentCompletions->count() }}</span></h6>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 group-show-table">
                        <thead>
                            <tr>
                                <th>الطالب</th>
                                <th>التقدم</th>
                                <th>تاريخ الإكمال</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentCompletions as $userAchievement)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $userAchievement->user->name ?? '—' }}</div>
                                        <small class="text-muted">{{ $userAchievement->user->email ?? '' }}</small>
                                    </td>
                                    <td>{{ $userAchievement->progress_percentage }}%</td>
                                    <td>{{ $userAchievement->completed_at?->format('Y/m/d H:i') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">لا يوجد مكمّلون بعد</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
