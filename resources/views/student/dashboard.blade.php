@extends('student.layouts.master')

@section('page-title')
    لوحة التحكم
@stop

@section('content')
<div class="main-content app-content student-dashboard-page">
    <div class="container-fluid pb-3">

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <i class="fe fe-alert-circle me-2"></i>{{ session('error') }}
                @if(session('error_source') === 'question_module_start')
                    <br><small class="text-muted">يرجى المحاولة مرة أخرى أو التواصل مع الدعم الفني</small>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="fe fe-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        @include('student.dashboard.partials.header-welcome')

        @include('student.dashboard.partials.kpi-cards', [
            'courseStats' => $courseStats,
            'questionModuleStats' => $questionModuleStats,
            'accountTier' => $accountTier,
        ])

        @include('student.dashboard.partials.camp-widgets-panel')

        @include('student.dashboard.partials.quick-access')

        <div class="row mt-2 align-items-start dashboard-content-row">
            <div class="col-xl-8 col-lg-7 mb-3 d-flex flex-column gap-3">
                @include('student.dashboard.partials.courses-progress', [
                    'inProgressCourses' => $inProgressCourses ?? collect(),
                ])

                @include('student.dashboard.partials.recent-activities', [
                    'questionModuleStats' => $questionModuleStats,
                ])
            </div>

            <div class="col-xl-4 col-lg-5 mb-3 d-flex flex-column gap-3">
                @include('student.dashboard.partials.sidebar-panels', [
                    'courseStats' => $courseStats,
                    'questionModuleStats' => $questionModuleStats,
                ])
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    function formatNumber(value, decimals) {
        if (decimals) {
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 1,
                maximumFractionDigits: 1,
            }).format(value);
        }
        return new Intl.NumberFormat('ar-EG').format(Math.round(value));
    }

    function initCountUp() {
        document.querySelectorAll('[data-countup]').forEach(function (el) {
            if (el.dataset.countupAnimated === 'true') {
                return;
            }

            const target = parseFloat(el.dataset.countup || '0');
            const suffix = el.dataset.countupSuffix || '';
            const decimals = el.dataset.countupDecimals === '1' || el.dataset.countupDecimals === '2';
            const duration = 900;
            const start = performance.now();

            const animate = function () {
                el.dataset.countupAnimated = 'true';

                function step(now) {
                    const progress = Math.min((now - start) / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    el.textContent = formatNumber(target * eased, decimals) + suffix;
                    if (progress < 1) {
                        requestAnimationFrame(step);
                    }
                }

                requestAnimationFrame(step);
            };

            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            animate();
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.2 });
                observer.observe(el);
            } else {
                animate();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCountUp);
    } else {
        initCountUp();
    }
})();
</script>
@endpush
