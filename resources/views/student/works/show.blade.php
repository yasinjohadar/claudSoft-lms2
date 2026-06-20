@php
    $statuses = \App\Models\StudentWork::getStatuses();
    $categories = \App\Models\StudentWork::getCategories();
    $currentStatus = $statuses[$work->status] ?? ['name' => $work->status, 'color' => 'secondary'];
    $currentCategory = $categories[$work->category] ?? ['name' => $work->category, 'color' => 'secondary'];

    $categoryIcons = [
        'project' => 'fe-code',
        'assignment' => 'fe-file-text',
        'creative' => 'fe-image',
        'research' => 'fe-search',
        'other' => 'fe-folder',
    ];
    $catIcon = $categoryIcons[$work->category] ?? 'fe-folder';

    $statusIcon = match ($work->status) {
        'approved' => 'fe-check-circle',
        'pending' => 'fe-clock',
        'rejected' => 'fe-x-circle',
        default => 'fe-edit-3',
    };
@endphp

@extends('student.layouts.master')

@section('page-title')
    {{ $work->title }}
@stop

@section('content')
<div class="main-content app-content student-work-show-page">
    <div class="container-fluid">

        @include('student.components.alerts')

        <div class="my-4 page-header-breadcrumb dashboard-fade-in">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.works.index') }}">جدول أعمالي</a></li>
                    <li class="breadcrumb-item active">{{ Str::limit($work->title, 40) }}</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe {{ $catIcon }} me-1"></i>
                        تفاصيل العمل
                    </span>
                    <h2 class="group-show-hero__title mb-2">{{ $work->title }}</h2>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <span class="badge bg-{{ $currentStatus['color'] }}-transparent text-{{ $currentStatus['color'] }}">
                            <i class="fe {{ $statusIcon }} me-1"></i>{{ $currentStatus['name'] }}
                        </span>
                        <span class="badge bg-{{ $currentCategory['color'] }}-transparent text-{{ $currentCategory['color'] }}">
                            <i class="fe {{ $catIcon }} me-1"></i>{{ $currentCategory['name'] }}
                        </span>
                        @if($work->is_featured)
                            <span class="badge bg-warning-transparent text-warning">
                                <i class="fe fe-star me-1"></i>مميز
                            </span>
                        @endif
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @if($work->course)
                            <span class="group-show-chip group-show-chip--sm">
                                <i class="fe fe-book me-1"></i>{{ $work->course->title }}
                            </span>
                        @endif
                        @if($work->completion_date)
                            <span class="group-show-chip group-show-chip--sm">
                                <i class="fe fe-calendar me-1"></i>{{ $work->completion_date->format('Y/m/d') }}
                            </span>
                        @endif
                        <span class="group-show-chip group-show-chip--sm">
                            <i class="fe fe-eye me-1"></i>{{ $work->views_count }} مشاهدة
                        </span>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions group-show-actions--single">
                        <a href="{{ route('student.works.index') }}"
                           class="group-show-action group-show-action--info">
                            <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                            <span class="group-show-action__text">رجوع للأعمال</span>
                        </a>
                        @can('update', $work)
                            <a href="{{ route('student.works.edit', $work) }}"
                               class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-edit"></i></span>
                                <span class="group-show-action__text">تعديل العمل</span>
                            </a>
                        @endcan
                        @if($work->status === 'draft')
                            <form action="{{ route('student.works.submit', $work) }}" method="POST" class="w-100">
                                @csrf
                                <button type="submit" class="group-show-action group-show-action--success w-100"
                                        onclick="return confirm('هل أنت متأكد من تقديم هذا العمل للمراجعة؟')">
                                    <span class="group-show-action__icon"><i class="fe fe-send"></i></span>
                                    <span class="group-show-action__text">تقديم للمراجعة</span>
                                </button>
                            </form>
                        @endif
                        @can('delete', $work)
                            <form action="{{ route('student.works.destroy', $work) }}" method="POST" class="w-100">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="group-show-action group-show-action--danger w-100"
                                        onclick="return confirm('هل أنت متأكد من حذف هذا العمل؟')">
                                    <span class="group-show-action__icon"><i class="fe fe-trash-2"></i></span>
                                    <span class="group-show-action__text">حذف العمل</span>
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        @include('student.works.partials.show-stats', ['work' => $work])

        <div class="row g-4">
            <div class="col-xl-8">
                @include('student.works.partials.show-content', ['work' => $work])
            </div>
            <div class="col-xl-4">
                @include('student.works.partials.show-sidebar', [
                    'work' => $work,
                    'currentStatus' => $currentStatus,
                    'currentCategory' => $currentCategory,
                    'catIcon' => $catIcon,
                    'statusIcon' => $statusIcon,
                ])
            </div>
        </div>

    </div>
</div>
@stop

@push('scripts')
<script>
(function () {
    function formatNumber(value, decimals) {
        if (decimals) {
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 1,
                maximumFractionDigits: 1,
            }).format(value);
        }
        return new Intl.NumberFormat('ar-EG').format(Math.round(value));
    }

    document.querySelectorAll('.student-work-show-stats [data-countup]').forEach(function (el) {
        var target = parseFloat(el.dataset.countup || '0');
        var decimals = el.dataset.countupDecimals === '1';
        var duration = 700;
        var start = performance.now();

        function step(now) {
            var progress = Math.min((now - start) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = formatNumber(target * eased, decimals);
            if (progress < 1) requestAnimationFrame(step);
        }

        requestAnimationFrame(step);
    });
})();
</script>
@endpush
