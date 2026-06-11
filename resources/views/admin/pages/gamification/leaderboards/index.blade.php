@extends('admin.layouts.master')

@section('page-title')
    لوحات المتصدرين
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show"><i class="fe fe-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show"><i class="fe fe-alert-circle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="my-4 page-header-breadcrumb">
            <nav><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                <li class="breadcrumb-item active">لوحات المتصدرين</li>
            </ol></nav>
        </div>

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow"><i class="fe fe-award me-1"></i>التلعيب</span>
                    <h2 class="group-show-hero__title mb-2">لوحات المتصدرين</h2>
                    <p class="group-show-hero__desc mb-0">إدارة لوحات الترتيب، تحديث النتائج، ومنح المكافآت.</p>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions">
                        @include('admin.pages.gamification.partials.recalculate-button', ['modalId' => 'leaderboardsRecalculateModal'])
                        <form action="{{ route('admin.gamification.leaderboards.update-all') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="group-show-action" title="تحديث ترتيب اللوحات فقط">
                                <span class="group-show-action__icon"><i class="fe fe-trending-up"></i></span>
                                <span class="group-show-action__text">تحديث اللوحات فقط</span>
                            </button>
                        </form>
                        <a href="{{ route('admin.gamification.leaderboards.create') }}" class="group-show-action group-show-action--primary">
                            <span class="group-show-action__icon"><i class="fe fe-plus-circle"></i></span>
                            <span class="group-show-action__text">إضافة لوحة</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">@include('admin.pages.gamification.leaderboards.partials.stats', ['stats' => $stats])</div>

        <div class="card custom-card group-show-members-card dashboard-fade-in">
            <div class="card-header border-0 pb-0">
                <h6 class="group-show-members-card__title mb-0">اللوحات <span class="group-show-members-card__count">{{ $leaderboards->count() }}</span></h6>
            </div>
            <div class="card-body pt-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 group-show-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الاسم</th>
                                <th>النوع</th>
                                <th>المقياس</th>
                                <th>الفترة</th>
                                <th>المشاركون</th>
                                <th>الحالة</th>
                                <th class="text-center">عمليات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $catalog = app(\App\Services\Gamification\LeaderboardCatalog::class); @endphp
                            @forelse ($leaderboards as $board)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $board->icon }} {{ $board->name }}</div>
                                        @if ($board->last_updated_at)
                                            <small class="text-muted">آخر تحديث: {{ $board->last_updated_at->diffForHumans() }}</small>
                                        @endif
                                    </td>
                                    <td>@include('admin.pages.gamification.leaderboards.partials.type-badge', ['leaderboard' => $board])</td>
                                    <td><span class="badge bg-light text-dark">{{ $catalog->getMetricLabel($board->metric ?? 'total_points') }}</span></td>
                                    <td>{{ $catalog->getPeriodLabel($board->period) }}</td>
                                    <td>{{ number_format($board->entries_count) }}</td>
                                    <td>
                                        @if ($board->is_active)
                                            <span class="badge bg-success">نشط</span>
                                        @else
                                            <span class="badge bg-secondary">معطّل</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1 flex-wrap">
                                            <a href="{{ route('admin.gamification.leaderboards.show', $board) }}" class="btn btn-sm btn-icon btn-outline-primary" title="عرض"><i class="fe fe-eye"></i></a>
                                            <a href="{{ route('admin.gamification.leaderboards.edit', $board) }}" class="btn btn-sm btn-icon btn-outline-info" title="تعديل"><i class="fe fe-edit"></i></a>
                                            <form action="{{ route('admin.gamification.leaderboards.update-data', $board) }}" method="POST" class="d-inline">@csrf<button type="submit" class="btn btn-sm btn-icon btn-outline-success" title="تحديث"><i class="fe fe-refresh-cw"></i></button></form>
                                            <form action="{{ route('admin.gamification.leaderboards.toggle-active', $board) }}" method="POST" class="d-inline">@csrf<button type="submit" class="btn btn-sm btn-icon btn-outline-warning" title="تفعيل/تعطيل"><i class="fe fe-power"></i></button></form>
                                            <button type="button" class="btn btn-sm btn-icon btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteBoard{{ $board->id }}"><i class="fe fe-trash-2"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-5">لا توجد لوحات بعد</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@foreach ($leaderboards as $board)
<div class="modal fade" id="deleteBoard{{ $board->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">حذف اللوحة</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">هل تريد حذف «{{ $board->name }}»؟</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                <form action="{{ route('admin.gamification.leaderboards.destroy', $board) }}" method="POST">@csrf @method('DELETE')<button type="submit" class="btn btn-danger">حذف</button></form>
            </div>
        </div>
    </div>
</div>
@endforeach
@stop
