@extends('student.layouts.master')

@section('page-title')
    تقدمي في الكورسات
@stop

@section('content')
<div class="main-content app-content student-progress-overview-page">
    <div class="container-fluid">

        @include('student.components.alerts')

        <div class="my-4 student-my-courses-welcome">
            <h4 class="student-my-courses-welcome__title mb-1">تقدمي في الكورسات</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item active">تقدمي في الكورسات</li>
                </ol>
            </nav>
        </div>

        @include('student.progress.partials.overview-stats', ['stats' => $stats])

        @php
            $currentStatus = request('status', 'all');
            $filteredProgress = collect($coursesProgress)->when(
                $currentStatus !== 'all',
                fn ($items) => $items->where('status', $currentStatus)
            );
            $filters = [
                ['key' => 'all', 'label' => 'الكل', 'icon' => 'fe-grid', 'params' => []],
                ['key' => 'active', 'label' => 'نشطة', 'icon' => 'fe-play', 'params' => ['status' => 'active']],
                ['key' => 'completed', 'label' => 'مكتملة', 'icon' => 'fe-check-circle', 'params' => ['status' => 'completed']],
                ['key' => 'suspended', 'label' => 'معلقة', 'icon' => 'fe-pause', 'params' => ['status' => 'suspended']],
            ];
        @endphp

        <div class="card custom-card student-my-courses-panel">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="avatar avatar-sm bg-primary-transparent">
                        <i class="fe fe-trending-up text-primary"></i>
                    </span>
                    <h6 class="card-title mb-0">تقدم الكورسات</h6>
                </div>

                @if(count($coursesProgress) > 0)
                    <div class="student-my-courses-filters mb-4">
                        @foreach ($filters as $filter)
                            <a href="{{ route('student.progress.overview', $filter['params']) }}"
                               class="student-my-courses-filter {{ $currentStatus === $filter['key'] || ($filter['key'] === 'all' && !request('status')) ? 'is-active' : '' }}">
                                <i class="fe {{ $filter['icon'] }}"></i>
                                <span>{{ $filter['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif

                <div class="row g-4">
                    @forelse($filteredProgress as $index => $progress)
                        @include('student.progress.partials.overview-course-card', [
                            'progress' => $progress,
                            'index' => $index,
                        ])
                    @empty
                        <div class="col-12">
                            @if(count($coursesProgress) > 0)
                                <div class="student-my-courses-empty text-center py-5">
                                    <div class="student-my-courses-empty__icon mb-4">
                                        <i class="fe fe-filter"></i>
                                    </div>
                                    <h4 class="mb-2">لا توجد كورسات بهذا التصنيف</h4>
                                    <p class="text-muted mb-4">جرّب تصفية أخرى أو اعرض جميع الكورسات.</p>
                                    <a href="{{ route('student.progress.overview') }}" class="btn btn-outline-primary rounded-pill px-4">
                                        <i class="fe fe-grid me-2"></i>عرض الكل
                                    </a>
                                </div>
                            @else
                                <div class="student-my-courses-empty text-center py-5">
                                    <div class="student-my-courses-empty__icon mb-4">
                                        <i class="fe fe-book-open"></i>
                                    </div>
                                    <h4 class="mb-2">لم تسجل في أي كورس بعد</h4>
                                    <p class="text-muted mb-4">ابدأ رحلتك التعليمية بالتسجيل في كورس</p>
                                    <a href="{{ route('student.courses.index') }}" class="btn btn-primary rounded-pill px-4">
                                        <i class="fe fe-search me-2"></i>تصفح الكورسات
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>
@stop

@section('scripts')
<script>
    (function () {
        function formatNumber(value, decimals) {
            if (decimals) {
                return new Intl.NumberFormat('ar-EG', {
                    minimumFractionDigits: 1,
                    maximumFractionDigits: 1,
                }).format(value);
            }
            return new Intl.NumberFormat('ar-EG').format(Math.round(value));
        }

        document.querySelectorAll('[data-countup]').forEach(function (el) {
            var target = parseFloat(el.dataset.countup || '0');
            var isPercent = el.dataset.countupSuffix === '%';
            var decimals = el.dataset.countupDecimals === '1';
            var duration = 800;
            var start = performance.now();

            function step(now) {
                var progress = Math.min((now - start) / duration, 1);
                var eased = 1 - Math.pow(1 - progress, 3);
                var value = formatNumber(target * eased, decimals);
                el.textContent = isPercent ? value + '%' : value;
                if (progress < 1) requestAnimationFrame(step);
            }

            requestAnimationFrame(step);
        });
    })();
</script>
@stop
