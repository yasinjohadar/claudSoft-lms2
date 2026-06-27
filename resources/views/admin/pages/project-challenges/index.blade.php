@extends('admin.layouts.master')

@section('page-title')
    تحديات المشاريع
@stop

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/project-challenge.css') }}?v={{ filemtime(public_path('assets/css/project-challenge.css')) }}">
@endpush

@section('content')
    @php
        $typeLabels = [
            'team_project' => 'مشروع فريق',
            'open_challenge' => 'تحدي مفتوح',
            'hackathon' => 'هاكاثون',
            'capstone' => 'مشروع تخرج',
        ];
        $typeIcons = [
            'team_project' => 'fe-users',
            'open_challenge' => 'fe-target',
            'hackathon' => 'fe-code',
            'capstone' => 'fe-award',
        ];
        $diffLabels = ['easy' => 'سهل', 'medium' => 'متوسط', 'hard' => 'صعب', 'expert' => 'خبير'];
        $statusFilter = $statusFilter ?? null;
    @endphp

    <div class="main-content app-content pc-page">
        <div class="container-fluid">
            @include('admin.components.alerts')

            <div class="my-3 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item active">تحديات المشاريع</li>
                    </ol>
                </nav>
            </div>

            <div class="pc-form-hero dashboard-fade-in">
                <div class="pc-form-hero__inner d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                            <i class="fe fe-layers fa-lg"></i>
                            <span class="pc-form-hero__badge">Project Challenges</span>
                        </div>
                        <h1 class="pc-form-hero__title">تحديات المشاريع</h1>
                        <p class="pc-form-hero__desc">إدارة التحديات، الفرق، المراحل، وتقييم التسليمات من مكان واحد.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.project-grading.index') }}" class="btn btn-outline-warning btn-sm rounded-pill px-3">
                            <i class="fe fe-check-square me-1"></i> تقييم التسليمات
                        </a>
                        <a href="{{ route('admin.project-challenges.create') }}" class="btn btn-primary btn-sm rounded-pill px-3">
                            <i class="fe fe-plus me-1"></i> تحدي جديد
                        </a>
                    </div>
                </div>
            </div>

            @php
                $kpiCards = [
                    ['variant' => 'blue', 'icon' => 'fe-layers', 'label' => 'إجمالي التحديات', 'value' => $stats['total'], 'sub' => 'كل الحالات'],
                    ['variant' => 'green', 'icon' => 'fe-check-circle', 'label' => 'منشورة', 'value' => $stats['published'], 'sub' => 'نشطة للطلاب'],
                    ['variant' => 'orange', 'icon' => 'fe-edit', 'label' => 'مسودات', 'value' => $stats['draft'], 'sub' => 'بانتظار النشر'],
                    ['variant' => 'cyan', 'icon' => 'fe-users', 'label' => 'الفرق', 'value' => $stats['teams'], 'sub' => 'مسجّلة'],
                ];
            @endphp

            <div class="row g-3 dashboard-fade-in mb-4">
                @foreach ($kpiCards as $index => $card)
                    <div class="col-xl-3 col-lg-6 col-md-6 dashboard-stagger-item" style="--stagger-delay: {{ $index * 70 }}ms">
                        <div class="card pc-kpi pc-kpi--{{ $card['variant'] }}">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="pc-kpi__icon-wrap">
                                    <i class="fe {{ $card['icon'] }} pc-kpi__icon"></i>
                                </div>
                                <div class="flex-fill min-w-0">
                                    <p class="pc-kpi__label mb-1">{{ $card['label'] }}</p>
                                    <h3 class="pc-kpi__value mb-1" data-countup="{{ $card['value'] }}">0</h3>
                                    <p class="pc-kpi__sub mb-0">{{ $card['sub'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <div class="pc-index-filter">
                    <a href="{{ route('admin.project-challenges.index') }}"
                       class="pc-index-filter__btn {{ !$statusFilter ? 'active' : '' }}">الكل</a>
                    <a href="{{ route('admin.project-challenges.index', ['status' => 'published']) }}"
                       class="pc-index-filter__btn {{ $statusFilter === 'published' ? 'active' : '' }}">منشورة</a>
                    <a href="{{ route('admin.project-challenges.index', ['status' => 'draft']) }}"
                       class="pc-index-filter__btn {{ $statusFilter === 'draft' ? 'active' : '' }}">مسودات</a>
                    <a href="{{ route('admin.project-challenges.index', ['status' => 'archived']) }}"
                       class="pc-index-filter__btn {{ $statusFilter === 'archived' ? 'active' : '' }}">مؤرشفة</a>
                </div>
                <div class="pc-view-toggle" role="group" aria-label="طريقة العرض">
                    <button type="button" class="pc-view-toggle__btn active" data-view="grid" title="بطاقات">
                        <i class="fe fe-grid"></i>
                    </button>
                    <button type="button" class="pc-view-toggle__btn" data-view="table" title="جدول">
                        <i class="fe fe-list"></i>
                    </button>
                </div>
            </div>

            @if($challenges->isEmpty())
                <div class="pc-index-empty dashboard-fade-in">
                    <div class="pc-index-empty__icon"><i class="fe fe-layers"></i></div>
                    <h3 class="h5 fw-bold mb-2">لا توجد تحديات مشاريع بعد</h3>
                    <p class="text-muted mb-3 mx-auto" style="max-width:420px">
                        أنشئ أول تحدي مشروع وحدّد المراحل والفرق — سيظهر للطلاب بعد النشر.
                    </p>
                    <a href="{{ route('admin.project-challenges.create') }}" class="btn btn-primary rounded-pill px-4">
                        <i class="fe fe-plus me-1"></i> إنشاء تحدي جديد
                    </a>
                </div>
            @else
                {{-- Grid view --}}
                <div id="pcGridView" class="row g-3 dashboard-fade-in mb-3">
                    @foreach($challenges as $challenge)
                        <div class="col-xl-4 col-lg-6">
                            <article class="pc-index-card">
                                <div class="pc-index-card__top">
                                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                        <span class="pc-tag pc-tag--{{ $challenge->difficulty }}">
                                            {{ $diffLabels[$challenge->difficulty] ?? $challenge->difficulty }}
                                        </span>
                                        @if($challenge->isPublished())
                                            <span class="pc-status-badge pc-status-badge--published"><i class="fe fe-check"></i> منشور</span>
                                        @elseif($challenge->isDraft())
                                            <span class="pc-status-badge pc-status-badge--draft">مسودة</span>
                                        @elseif($challenge->isArchived())
                                            <span class="pc-status-badge pc-status-badge--archived">مؤرشف</span>
                                        @else
                                            <span class="pc-status-badge pc-status-badge--closed">مغلق</span>
                                        @endif
                                    </div>
                                    <h2 class="pc-index-card__title">
                                        <a href="{{ route('admin.project-challenges.edit', $challenge->id) }}">
                                            {{ $challenge->title }}
                                        </a>
                                        @if($challenge->is_featured)
                                            <span class="badge bg-warning-transparent ms-1">مميز</span>
                                        @endif
                                    </h2>
                                    <div class="d-flex flex-wrap gap-1">
                                        <span class="pc-tag">
                                            <i class="fe {{ $typeIcons[$challenge->project_type] ?? 'fe-folder' }} me-1"></i>
                                            {{ $typeLabels[$challenge->project_type] ?? $challenge->project_type }}
                                        </span>
                                    </div>
                                </div>
                                <div class="pc-index-card__body">
                                    @if($challenge->summary)
                                        <p class="text-muted small mb-0 pc-challenge-card__summary">
                                            {{ Str::limit(strip_tags($challenge->summary), 90) }}
                                        </p>
                                    @endif
                                    <div class="pc-index-card__stats">
                                        <div class="pc-index-card__stat">
                                            <span class="pc-index-card__stat-val">{{ $challenge->teams_count }}</span>
                                            <span class="pc-index-card__stat-lbl">فرق</span>
                                        </div>
                                        <div class="pc-index-card__stat">
                                            <span class="pc-index-card__stat-val">{{ $challenge->stages->count() }}</span>
                                            <span class="pc-index-card__stat-lbl">مراحل</span>
                                        </div>
                                        <div class="pc-index-card__stat">
                                            <span class="pc-index-card__stat-val">{{ $challenge->points_total ?? 0 }}</span>
                                            <span class="pc-index-card__stat-lbl">نقطة</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="pc-index-card__foot">
                                    <a href="{{ route('admin.project-challenges.edit', $challenge->id) }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fe fe-edit-2"></i>
                                    </a>
                                    <a href="{{ route('admin.project-challenges.manage-stages', $challenge->id) }}" class="btn btn-outline-primary btn-sm">مراحل</a>
                                    <a href="{{ route('admin.project-challenges.manage-teams', $challenge->id) }}" class="btn btn-outline-info btn-sm">فرق</a>
                                    @if($challenge->isDraft())
                                        <form action="{{ route('admin.project-challenges.publish', $challenge->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success btn-sm" onclick="return confirm('نشر هذا التحدي؟')">
                                                <i class="fe fe-upload"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.project-challenges.destroy', $challenge->id) }}" method="POST" class="d-inline ms-auto" onsubmit="return confirm('حذف التحدي؟')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fe fe-trash-2"></i></button>
                                    </form>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>

                {{-- Table view --}}
                <div id="pcTableView" class="pc-form-panel d-none mb-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>العنوان</th>
                                    <th>النوع</th>
                                    <th>الصعوبة</th>
                                    <th>الفرق</th>
                                    <th>المراحل</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($challenges as $challenge)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.project-challenges.edit', $challenge->id) }}" class="fw-semibold text-decoration-none">
                                                {{ $challenge->title }}
                                            </a>
                                            @if($challenge->is_featured)
                                                <span class="badge bg-warning-transparent ms-1">مميز</span>
                                            @endif
                                        </td>
                                        <td>{{ $typeLabels[$challenge->project_type] ?? $challenge->project_type }}</td>
                                        <td>
                                            <span class="pc-tag pc-tag--{{ $challenge->difficulty }}">
                                                {{ $diffLabels[$challenge->difficulty] ?? $challenge->difficulty }}
                                            </span>
                                        </td>
                                        <td>{{ $challenge->teams_count }}</td>
                                        <td>{{ $challenge->stages->count() }}</td>
                                        <td>
                                            @if($challenge->isPublished())
                                                <span class="pc-status-badge pc-status-badge--published">منشور</span>
                                            @elseif($challenge->isDraft())
                                                <span class="pc-status-badge pc-status-badge--draft">مسودة</span>
                                            @elseif($challenge->isArchived())
                                                <span class="pc-status-badge pc-status-badge--archived">مؤرشف</span>
                                            @else
                                                <span class="pc-status-badge pc-status-badge--closed">مغلق</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                <a href="{{ route('admin.project-challenges.edit', $challenge->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fe fe-edit-2"></i></a>
                                                <a href="{{ route('admin.project-challenges.manage-stages', $challenge->id) }}" class="btn btn-sm btn-outline-primary">مراحل</a>
                                                <a href="{{ route('admin.project-challenges.manage-teams', $challenge->id) }}" class="btn btn-sm btn-outline-info">فرق</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($challenges->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $challenges->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
@stop

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var gridView = document.getElementById('pcGridView');
    var tableView = document.getElementById('pcTableView');
    var toggleBtns = document.querySelectorAll('.pc-view-toggle__btn');
    var storageKey = 'pc_admin_index_view';

    function setView(mode) {
        if (!gridView || !tableView) return;
        var isGrid = mode === 'grid';
        gridView.classList.toggle('d-none', !isGrid);
        tableView.classList.toggle('d-none', isGrid);
        toggleBtns.forEach(function (btn) {
            btn.classList.toggle('active', btn.dataset.view === mode);
        });
        try { localStorage.setItem(storageKey, mode); } catch (e) {}
    }

    toggleBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            setView(btn.dataset.view);
        });
    });

    var saved = 'grid';
    try { saved = localStorage.getItem(storageKey) || 'grid'; } catch (e) {}
    setView(saved);
});
</script>
@stop
