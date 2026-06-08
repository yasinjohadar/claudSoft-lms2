@extends('student.layouts.master')

@section('page-title')
    {{ $trainingCamp->name }}
@stop

@section('content')
    @php
        $campStatus = 'upcoming';
        $campStatusLabel = 'قادم';
        $campStatusClass = 'info';

        if ($trainingCamp->hasEnded()) {
            $campStatus = 'completed';
            $campStatusLabel = 'منتهي';
            $campStatusClass = 'secondary';
        } elseif ($trainingCamp->isOngoing()) {
            $campStatus = 'ongoing';
            $campStatusLabel = 'جاري';
            $campStatusClass = 'success';
        }

        $seatPercent = $trainingCamp->max_participants
            ? min(100, round(($trainingCamp->current_participants / $trainingCamp->max_participants) * 100))
            : 0;
    @endphp

    <div class="main-content app-content student-training-camp-show-page">
        <div class="container-fluid">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fe fe-check-circle me-2"></i>{!! session('success') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fe fe-alert-circle me-2"></i>{!! session('error') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('student.training-camps.index') }}">المعسكرات</a></li>
                        <li class="breadcrumb-item active">{{ $trainingCamp->name }}</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-flag me-1"></i>
                            تفاصيل المعسكر
                        </span>
                        <h2 class="group-show-hero__title mb-2">{{ $trainingCamp->name }}</h2>
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            @if($trainingCamp->category)
                                <span class="group-show-chip group-show-chip--sm">{{ $trainingCamp->category->name }}</span>
                            @endif
                            <span class="badge bg-{{ $campStatusClass }}-transparent text-{{ $campStatusClass }}">
                                {{ $campStatusLabel }}
                            </span>
                            @if($trainingCamp->is_featured)
                                <span class="badge bg-warning-transparent text-warning">
                                    <i class="fe fe-star me-1"></i>مميز
                                </span>
                            @endif
                            @if($isEnrolled)
                                <span class="badge bg-success-transparent text-success">
                                    <i class="fe fe-check me-1"></i>مسجّل
                                </span>
                            @endif
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @if($trainingCamp->instructor_name)
                                <span class="group-show-chip group-show-chip--sm">
                                    <i class="fe fe-user me-1"></i>{{ $trainingCamp->instructor_name }}
                                </span>
                            @endif
                            @if($trainingCamp->location)
                                <span class="group-show-chip group-show-chip--sm">
                                    <i class="fe fe-map-pin me-1"></i>{{ $trainingCamp->location }}
                                </span>
                            @endif
                            <span class="group-show-chip group-show-chip--sm">
                                <i class="fe fe-calendar me-1"></i>{{ $trainingCamp->start_date->format('Y-m-d') }}
                                —
                                {{ $trainingCamp->end_date->format('Y-m-d') }}
                            </span>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions group-show-actions--single">
                            <a href="{{ route('student.training-camps.index') }}"
                               class="group-show-action group-show-action--info">
                                <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                                <span class="group-show-action__text">العودة للمعسكرات</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @include('student.pages.training-camps.partials.show-stats', [
                'trainingCamp' => $trainingCamp,
                'campStatus' => $campStatus,
                'campStatusLabel' => $campStatusLabel,
            ])

            <div class="row g-4">
                <div class="col-xl-8">
                    @if($trainingCamp->image)
                        <div class="card custom-card group-show-members-card dashboard-fade-in mb-4 overflow-hidden">
                            <img src="{{ asset('storage/' . $trainingCamp->image) }}"
                                 alt="{{ $trainingCamp->name }}"
                                 class="w-100 student-camp-show-cover"
                                 onerror="this.closest('.card').style.display='none'">
                        </div>
                    @endif

                    <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title mb-1">عن المعسكر</h4>
                            <p class="fs-12 text-muted mb-0">الوصف والتفاصيل الأساسية.</p>
                        </div>
                        <div class="card-body pt-3">
                            <p class="mb-4 {{ $trainingCamp->description ? '' : 'text-muted' }}">
                                {{ $trainingCamp->description ?? 'لا يوجد وصف متاح لهذا المعسكر.' }}
                            </p>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="dashboard-stat-row p-3 h-100">
                                        <span class="d-flex align-items-center gap-2 text-muted fs-13 mb-1">
                                            <i class="fe fe-user"></i>المدرب
                                        </span>
                                        <strong>{{ $trainingCamp->instructor_name ?? 'غير محدد' }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="dashboard-stat-row p-3 h-100">
                                        <span class="d-flex align-items-center gap-2 text-muted fs-13 mb-1">
                                            <i class="fe fe-map-pin"></i>الموقع
                                        </span>
                                        <strong>{{ $trainingCamp->location ?? 'غير محدد' }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="dashboard-stat-row p-3 h-100">
                                        <span class="d-flex align-items-center gap-2 text-muted fs-13 mb-1">
                                            <i class="fe fe-calendar"></i>تاريخ البداية
                                        </span>
                                        <strong>{{ $trainingCamp->start_date->format('Y-m-d') }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="dashboard-stat-row p-3 h-100">
                                        <span class="d-flex align-items-center gap-2 text-muted fs-13 mb-1">
                                            <i class="fe fe-calendar"></i>تاريخ النهاية
                                        </span>
                                        <strong>{{ $trainingCamp->end_date->format('Y-m-d') }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card custom-card group-show-members-card dashboard-fade-in sticky-top student-camp-show-sidebar">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title mb-1">التسجيل في المعسكر</h4>
                            <p class="fs-12 text-muted mb-0">احجز مقعدك في هذا البرنامج.</p>
                        </div>
                        <div class="card-body pt-3">
                            <div class="text-center mb-4 py-2">
                                <p class="text-muted fs-12 mb-1">سعر المعسكر</p>
                                <h2 class="fw-bold text-primary mb-0" data-countup="{{ round($trainingCamp->price, 2) }}" data-countup-prefix="$" data-countup-decimals="2">0</h2>
                            </div>

                            @if($trainingCamp->max_participants)
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-2 fs-13">
                                        <span class="text-muted">المقاعد المتاحة</span>
                                        <strong>{{ $trainingCamp->availableSeats() }} / {{ $trainingCamp->max_participants }}</strong>
                                    </div>
                                    <div class="progress progress-xs">
                                        <div class="progress-bar bg-primary" style="width: {{ $seatPercent }}%"></div>
                                    </div>
                                    <small class="text-muted d-block mt-1">{{ $seatPercent }}% من المقاعد محجوزة</small>
                                </div>
                            @endif

                            @if($isEnrolled)
                                <div class="alert alert-info mb-3">
                                    <i class="fe fe-info me-2"></i>
                                    أنت مسجّل في هذا المعسكر
                                    @if($enrollment)
                                        <br><small>الحالة: <strong>{{ $enrollment->status_label ?? $enrollment->status }}</strong></small>
                                    @endif
                                </div>

                                @if($enrollment && $enrollment->status !== 'approved' && $enrollment->payment_status !== 'paid')
                                    <form action="{{ route('student.training-camps.cancel-enrollment', $trainingCamp->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-danger w-100"
                                                onclick="return confirm('هل أنت متأكد من إلغاء التسجيل؟')">
                                            <i class="fe fe-x me-2"></i>إلغاء التسجيل
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('student.training-camps.my-enrollments') }}"
                                   class="btn btn-success-light w-100 mt-3">
                                    <i class="fe fe-layers me-2"></i>عرض تسجيلاتي
                                </a>
                            @else
                                @if($trainingCamp->hasAvailableSeats())
                                    <form action="{{ route('student.training-camps.enroll', $trainingCamp->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-lg w-100">
                                            <i class="fe fe-check-circle me-2"></i>سجّل الآن
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-secondary btn-lg w-100" disabled>
                                        <i class="fe fe-slash me-2"></i>المقاعد ممتلئة
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    function formatNumber(value, decimals) {
        if (decimals) {
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }).format(value);
        }
        return new Intl.NumberFormat('ar-EG').format(Math.round(value));
    }

    document.querySelectorAll('[data-countup]').forEach(function (el) {
        const target = parseFloat(el.dataset.countup || '0');
        const prefix = el.dataset.countupPrefix || '';
        const suffix = el.dataset.countupSuffix || '';
        const decimals = el.dataset.countupDecimals === '2';
        const duration = 900;
        const start = performance.now();

        function step(now) {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = prefix + formatNumber(target * eased, decimals) + suffix;
            if (progress < 1) requestAnimationFrame(step);
        }

        requestAnimationFrame(step);
    });
})();
</script>
@endpush
