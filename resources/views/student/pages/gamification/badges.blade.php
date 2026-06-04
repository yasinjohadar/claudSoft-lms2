@extends('student.layouts.master')

@section('page-title')
    شاراتي
@stop

@section('content')
<div class="main-content app-content student-badges-page">
    <div class="container-fluid">

        @include('student.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4">
            <div>
                <h4 class="student-my-courses-welcome__title mb-1">شاراتي</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('gamification.dashboard') }}">التلعيب</a></li>
                        <li class="breadcrumb-item active">شاراتي</li>
                    </ol>
                </nav>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('gamification.dashboard') }}" class="btn btn-outline-primary rounded-pill">
                    <i class="fe fe-bar-chart-2 me-1"></i>لوحة التلعيب
                </a>
            </div>
        </div>

        @include('student.pages.gamification.partials.badges-stats', ['stats' => $stats])

        <div class="card custom-card student-quizzes-panel mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="avatar avatar-sm bg-success-transparent">
                        <i class="fe fe-award text-success"></i>
                    </span>
                    <h6 class="card-title mb-0">الشارات المكتسبة ({{ count($earnedBadges ?? []) }})</h6>
                </div>

                @if(count($earnedBadges ?? []) > 0)
                    <div class="row g-4">
                        @foreach($earnedBadges as $index => $item)
                            @php
                                $badge = $item->badge ?? $item;
                                $awardedAt = $item->awarded_at ?? optional($item->pivot)->awarded_at ?? null;
                            @endphp
                            @include('student.pages.gamification.partials.badge-card', [
                                'badge' => $badge,
                                'isEarned' => true,
                                'awardedAt' => $awardedAt,
                                'progress' => ['progress' => 100],
                                'index' => $index,
                            ])
                        @endforeach
                    </div>
                @else
                    @include('student.pages.gamification.partials.badges-empty', [
                        'title' => 'لم تحصل على شارات بعد',
                        'message' => 'استمر في التعلم لكسب أول شارة!',
                    ])
                @endif
            </div>
        </div>

        <div class="card custom-card student-quizzes-panel">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="avatar avatar-sm bg-primary-transparent">
                        <i class="fe fe-grid text-primary"></i>
                    </span>
                    <h6 class="card-title mb-0">جميع الشارات</h6>
                </div>

                @include('student.pages.gamification.partials.badges-filters')

                @if(count($allBadges ?? []) > 0)
                    <div class="row g-4">
                        @foreach($allBadges as $index => $badge)
                            @include('student.pages.gamification.partials.badge-card', [
                                'badge' => $badge,
                                'isEarned' => $badge->is_earned ?? false,
                                'progress' => $badge->progress ?? ['progress' => 0],
                                'index' => $index,
                            ])
                        @endforeach
                    </div>
                @else
                    @include('student.pages.gamification.partials.badges-empty', [
                        'title' => 'لا توجد شارات مطابقة',
                        'message' => 'جرّب تغيير الفلتر أو عد لاحقاً.',
                    ])
                @endif
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
