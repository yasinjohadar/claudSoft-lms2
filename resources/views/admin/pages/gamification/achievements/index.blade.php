@extends('admin.layouts.master')

@section('page-title')
    الإنجازات
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb">
            <nav><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                <li class="breadcrumb-item active">الإنجازات</li>
            </ol></nav>
        </div>

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow"><i class="fe fe-award me-1"></i>التلعيب</span>
                    <h2 class="group-show-hero__title mb-2">إنجازات الطلاب</h2>
                    <p class="group-show-hero__desc mb-0">إدارة معايير الإنجاز، مكافآت النقاط، وإعادة التحقق للطلاب المستحقين.</p>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions">
                        @include('admin.pages.gamification.partials.achievements-recalculate-button', [
                            'modalId' => 'achievementsRecalculateModal',
                            'useGroupAction' => true,
                        ])
                        <a href="{{ route('admin.gamification.achievements.statistics') }}" class="group-show-action">
                            <span class="group-show-action__icon"><i class="fe fe-bar-chart-2"></i></span>
                            <span class="group-show-action__text">إحصائيات</span>
                        </a>
                        <a href="{{ route('admin.gamification.achievements.create') }}" class="group-show-action group-show-action--primary">
                            <span class="group-show-action__icon"><i class="fe fe-plus-circle"></i></span>
                            <span class="group-show-action__text">إضافة إنجاز</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            @include('admin.pages.gamification.achievements.partials.stats', ['stats' => $stats])
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
            <div class="card-header border-0 pb-0">
                <h4 class="card-title mb-1">تصفية الإنجازات</h4>
                <p class="fs-12 text-muted mb-0">ابحث بالاسم أو فلتر حسب المستوى والمتطلب والحالة.</p>
            </div>
            <div class="card-body pt-3">
                <form action="{{ route('admin.gamification.achievements.index') }}" method="GET" class="group-show-filters mb-0">
                    <div class="row g-3 align-items-end">
                        <div class="col-xl-3 col-lg-4 col-md-6">
                            <label class="form-label" for="achievementSearch">بحث</label>
                            <input id="achievementSearch" type="text" name="search" class="form-control"
                                placeholder="الاسم أو الوصف..." value="{{ request('search') }}">
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <label class="form-label" for="achievementTier">المستوى</label>
                            <select name="tier" id="achievementTier" class="form-select">
                                <option value="">كل المستويات</option>
                                @foreach ($tierOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(request('tier') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6">
                            <label class="form-label" for="achievementRequirement">نوع المتطلب</label>
                            <select name="requirement_type" id="achievementRequirement" class="form-select">
                                <option value="">كل المتطلبات</option>
                                @foreach ($requirementTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(request('requirement_type') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <label class="form-label" for="achievementStatus">الحالة</label>
                            <select name="is_active" id="achievementStatus" class="form-select">
                                <option value="">الكل</option>
                                <option value="1" @selected(request('is_active') === '1')>نشط</option>
                                <option value="0" @selected(request('is_active') === '0')>غير نشط</option>
                            </select>
                        </div>
                        <div class="col-xl-12">
                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary btn-sm"><i class="fe fe-search me-1"></i>بحث</button>
                                <a href="{{ route('admin.gamification.achievements.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fe fe-rotate-cw me-1"></i>مسح</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in">
            <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="group-show-members-card__title mb-0">
                    الإنجازات
                    <span class="group-show-members-card__count">{{ $achievements->total() }}</span>
                </h6>
                @if(request()->hasAny(['search', 'tier', 'requirement_type', 'is_active']))
                    <span class="badge bg-primary-transparent">نتائج مفلترة</span>
                @endif
            </div>
            <div class="card-body pt-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 group-show-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الإنجاز</th>
                                <th>المستوى</th>
                                <th>المتطلب</th>
                                <th>النقاط</th>
                                <th>المكمّلون</th>
                                <th>الحالة</th>
                                <th class="text-center">عمليات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($achievements as $achievement)
                                <tr>
                                    <td>{{ $achievements->firstItem() + $loop->index }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fs-20">{{ $achievement->icon ?? '🏆' }}</span>
                                            <div>
                                                <a href="{{ route('admin.gamification.achievements.show', $achievement) }}" class="fw-semibold text-decoration-none">
                                                    {{ $achievement->name }}
                                                </a>
                                                @if($achievement->description)
                                                    <div class="text-muted fs-12">{{ Str::limit($achievement->description, 50) }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>@include('admin.pages.gamification.achievements.partials.tier-badge', ['tier' => $achievement->tier])</td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ \App\Support\Gamification\AchievementCriteriaMapper::formatForDisplay($achievement->criteria, $achievement->target_value) }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($achievement->points_reward ?? 0) }}</td>
                                    <td>{{ number_format($achievement->completions_count ?? 0) }}</td>
                                    <td>
                                        @if($achievement->is_active)
                                            <span class="badge bg-success-transparent text-success">نشط</span>
                                        @else
                                            <span class="badge bg-danger-transparent text-danger">غير نشط</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1 flex-wrap">
                                            <a href="{{ route('admin.gamification.achievements.show', $achievement) }}" class="btn btn-sm btn-icon btn-outline-primary" title="عرض"><i class="fe fe-eye"></i></a>
                                            <a href="{{ route('admin.gamification.achievements.edit', $achievement) }}" class="btn btn-sm btn-icon btn-outline-info" title="تعديل"><i class="fe fe-edit"></i></a>
                                            <form action="{{ route('admin.gamification.achievements.toggle-active', $achievement) }}" method="POST" class="d-inline">@csrf
                                                <button type="submit" class="btn btn-sm btn-icon btn-outline-warning" title="تفعيل/تعطيل"><i class="fe fe-power"></i></button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-icon btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAchievement{{ $achievement->id }}"><i class="fe fe-trash-2"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-5">لا توجد إنجازات مطابقة</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($achievements->hasPages())
                    <div class="mt-4 d-flex justify-content-center">
                        {{ $achievements->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@foreach ($achievements as $achievement)
<div class="modal fade" id="deleteAchievement{{ $achievement->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">حذف الإنجاز</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">هل تريد حذف «{{ $achievement->name }}»؟</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                <form action="{{ route('admin.gamification.achievements.destroy', $achievement) }}" method="POST">@csrf @method('DELETE')<button type="submit" class="btn btn-danger">حذف</button></form>
            </div>
        </div>
    </div>
</div>
@endforeach
@stop

@section('scripts')
<script>
    document.querySelectorAll('[data-countup]').forEach(function (el) {
        var target = parseInt(el.dataset.countup || '0', 10);
        var start = performance.now();
        var duration = 700;
        function tick(now) {
            var p = Math.min((now - start) / duration, 1);
            el.textContent = Math.round(target * p).toLocaleString('ar-EG');
            if (p < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
    });
</script>
@stop
