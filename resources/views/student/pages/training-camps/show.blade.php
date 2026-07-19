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

        $seatPercent = $trainingCamp->max_members
            ? min(100, round(($trainingCamp->current_participants / max(1, $trainingCamp->max_members)) * 100))
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
                            <span class="badge bg-{{ $campStatusClass }}-transparent text-{{ $campStatusClass }}">
                                {{ $campStatusLabel }}
                            </span>
                            @if($isEnrolled)
                                <span class="badge bg-success-transparent text-success">
                                    <i class="fe fe-check me-1"></i>مسجّل
                                </span>
                            @endif
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @if($trainingCamp->start_date || $trainingCamp->end_date)
                                <span class="group-show-chip group-show-chip--sm">
                                    <i class="fe fe-calendar me-1"></i>
                                    {{ $trainingCamp->start_date?->format('Y-m-d') ?? '—' }}
                                    —
                                    {{ $trainingCamp->end_date?->format('Y-m-d') ?? '—' }}
                                </span>
                            @endif
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
                            @if($trainingCamp->description)
                                <p class="mb-4">{{ $trainingCamp->description }}</p>
                            @elseif(! $trainingCamp->details)
                                <p class="mb-4 text-muted">لا يوجد وصف متاح لهذا المعسكر.</p>
                            @endif

                            @if($trainingCamp->details)
                                <div class="mb-4 student-group-details-content">
                                    {!! $trainingCamp->details !!}
                                </div>
                            @endif

                            @if($trainingCamp->terms)
                                <div class="mb-4">
                                    <h6 class="text-muted fs-13 fw-semibold mb-2">شروط المعسكر</h6>
                                    <div class="student-group-details-content">
                                        {!! $trainingCamp->terms !!}
                                    </div>
                                </div>
                            @endif

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="dashboard-stat-row p-3 h-100">
                                        <span class="d-flex align-items-center gap-2 text-muted fs-13 mb-1">
                                            <i class="fe fe-calendar"></i>تاريخ البداية
                                        </span>
                                        <strong>{{ $trainingCamp->start_date?->format('Y-m-d') ?? 'غير محدد' }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="dashboard-stat-row p-3 h-100">
                                        <span class="d-flex align-items-center gap-2 text-muted fs-13 mb-1">
                                            <i class="fe fe-calendar"></i>تاريخ النهاية
                                        </span>
                                        <strong>{{ $trainingCamp->end_date?->format('Y-m-d') ?? 'غير محدد' }}</strong>
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
                                <h2 class="fw-bold text-primary mb-0" data-countup="{{ round((float) $trainingCamp->price, 2) }}" data-countup-prefix="$" data-countup-decimals="2">0</h2>
                            </div>

                            @if($trainingCamp->max_members)
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-2 fs-13">
                                        <span class="text-muted">المقاعد المتاحة</span>
                                        <strong>{{ $trainingCamp->availableSeats() }} / {{ $trainingCamp->max_members }}</strong>
                                    </div>
                                    <div class="progress progress-xs">
                                        <div class="progress-bar bg-primary" style="width: {{ $seatPercent }}%"></div>
                                    </div>
                                    <small class="text-muted d-block mt-1">{{ $seatPercent }}% من المقاعد محجوزة</small>
                                </div>
                            @endif

                            @if($isEnrolled)
                                <div class="alert {{ ($enrollment->status ?? '') === 'pending' ? 'alert-warning' : 'alert-info' }} mb-3">
                                    <i class="fe fe-info me-2"></i>
                                    أنت مسجّل في هذا المعسكر
                                    @if($enrollment)
                                        <br><small>الحالة: <strong>{{ $enrollment->status_label ?? $enrollment->status }}</strong></small>
                                        @if(!empty($enrollment->has_receipt))
                                            <br><small class="text-success"><i class="fe fe-paperclip me-1"></i>تم إرفاق إيصال الدفع</small>
                                        @endif
                                    @endif
                                </div>

                                @if($enrollment && !empty($enrollment->cancel_id) && $enrollment->status === 'pending')
                                    <form action="{{ route('student.training-camps.cancel-enrollment', $enrollment->cancel_id) }}" method="POST">
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
                                    <form action="{{ route('student.training-camps.enroll', $trainingCamp->id) }}"
                                          method="POST"
                                          enctype="multipart/form-data"
                                          class="student-camp-enroll-form">
                                        @csrf

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold" for="campEnrollNotes">ملاحظات (اختياري)</label>
                                            <textarea name="notes" id="campEnrollNotes" rows="2"
                                                      class="form-control @error('notes') is-invalid @enderror"
                                                      placeholder="أي ملاحظة للإدارة...">{{ old('notes') }}</textarea>
                                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        @if($trainingCamp->require_payment_receipt ?? true)
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold" for="campEnrollReceipt">
                                                    إيصال الدفع المالي <span class="text-danger">*</span>
                                                </label>
                                                <input type="file"
                                                       name="receipt"
                                                       id="campEnrollReceipt"
                                                       class="form-control @error('receipt') is-invalid @enderror"
                                                       accept=".jpg,.jpeg,.png,.webp,.pdf,image/*,application/pdf"
                                                       required>
                                                <small class="text-muted d-block mt-1">صورة أو PDF — بحد أقصى 5MB.</small>
                                                @error('receipt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        @endif

                                        <div class="mb-3">
                                            @if($trainingCamp->terms)
                                                <div class="border rounded p-3 mb-3 bg-light student-group-details-content" style="max-height: 220px; overflow-y: auto;">
                                                    <h6 class="fs-12 fw-semibold text-muted mb-2">شروط المعسكر</h6>
                                                    {!! $trainingCamp->terms !!}
                                                </div>
                                            @endif
                                            <div class="form-check">
                                                <input class="form-check-input @error('terms_accepted') is-invalid @enderror"
                                                       type="checkbox"
                                                       name="terms_accepted"
                                                       value="1"
                                                       id="camp_terms_accepted"
                                                       {{ old('terms_accepted') ? 'checked' : '' }}
                                                       required>
                                                <label class="form-check-label" for="camp_terms_accepted">
                                                    أوافق على شروط المعسكر
                                                </label>
                                                @error('terms_accepted')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-lg w-100">
                                            <i class="fe fe-send me-2"></i>إرسال طلب التسجيل
                                        </button>
                                        <p class="fs-12 text-muted text-center mt-2 mb-0">
                                            سيصبح الطلب <strong>قيد المراجعة</strong> حتى موافقة الإدارة
                                            @if($trainingCamp->require_payment_receipt ?? true)
                                                بعد التأكد من دفع قيمة المعسكر
                                            @endif.
                                        </p>
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
