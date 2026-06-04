@extends('student.layouts.master')

@section('page-title')
    كورساتي
@stop

@section('content')
<div class="main-content app-content student-my-courses-page">
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="my-4 student-my-courses-welcome">
            <h4 class="student-my-courses-welcome__title mb-1">كورساتي التعليمية</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item active">كورساتي</li>
                </ol>
            </nav>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @include('student.pages.courses.partials.my-courses-stats', ['stats' => $stats])

        <div class="card custom-card student-my-courses-panel">
            <div class="card-body">
                @php
                    $currentStatus = request('status', 'all');
                    $filters = [
                        ['key' => 'all', 'label' => 'الكل', 'icon' => 'fe-grid', 'params' => []],
                        ['key' => 'active', 'label' => 'قيد الدراسة', 'icon' => 'fe-play', 'params' => ['status' => 'active']],
                        ['key' => 'completed', 'label' => 'مكتملة', 'icon' => 'fe-check-circle', 'params' => ['status' => 'completed']],
                        ['key' => 'suspended', 'label' => 'متوقفة', 'icon' => 'fe-pause', 'params' => ['status' => 'suspended']],
                    ];
                @endphp

                <div class="student-my-courses-filters mb-4">
                    @foreach ($filters as $filter)
                        <a href="{{ route('student.courses.my-courses', $filter['params']) }}"
                           class="student-my-courses-filter {{ $currentStatus === $filter['key'] || ($filter['key'] === 'all' && !request('status')) ? 'is-active' : '' }}">
                            <i class="fe {{ $filter['icon'] }}"></i>
                            <span>{{ $filter['label'] }}</span>
                        </a>
                    @endforeach
                </div>

                <div class="row g-4">
                    @forelse($enrollments as $index => $enrollment)
                        @include('student.pages.courses.partials.my-courses-card', [
                            'enrollment' => $enrollment,
                            'index' => $index,
                        ])
                    @empty
                        <div class="col-12">
                            <div class="student-my-courses-empty text-center py-5">
                                <div class="student-my-courses-empty__icon mb-4">
                                    <i class="fe fe-book-open"></i>
                                </div>
                                <h4 class="mb-2">لا توجد كورسات مسجلة</h4>
                                <p class="text-muted mb-4">ابدأ رحلتك التعليمية الآن واستكشف الكورسات المتاحة!</p>
                                <a href="{{ route('student.courses.index') }}" class="btn btn-primary rounded-pill px-4">
                                    <i class="fe fe-search me-2"></i>تصفح الكورسات المتاحة
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if($enrollments->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $enrollments->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@stop

@section('scripts')
<script>
    (function () {
        setTimeout(function () {
            document.querySelectorAll('.alert').forEach(function (el) {
                el.classList.remove('show');
            });
        }, 5000);

        function formatNumber(value) {
            return new Intl.NumberFormat('ar-EG').format(Math.round(value));
        }

        document.querySelectorAll('[data-countup]').forEach(function (el) {
            var target = parseFloat(el.dataset.countup || '0');
            var isPercent = el.dataset.countupSuffix === '%';
            var duration = 800;
            var start = performance.now();

            function step(now) {
                var progress = Math.min((now - start) / duration, 1);
                var eased = 1 - Math.pow(1 - progress, 3);
                var value = formatNumber(target * eased);
                el.textContent = isPercent ? value + '%' : value;
                if (progress < 1) requestAnimationFrame(step);
            }

            requestAnimationFrame(step);
        });
    })();
</script>
@stop
