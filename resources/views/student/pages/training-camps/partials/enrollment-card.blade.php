@php
    $statusMap = [
        'pending' => ['class' => 'warning', 'icon' => 'fe-clock'],
        'approved' => ['class' => 'success', 'icon' => 'fe-check-circle'],
        'rejected' => ['class' => 'danger', 'icon' => 'fe-x-circle'],
        'cancelled' => ['class' => 'secondary', 'icon' => 'fe-slash'],
    ];
    $paymentMap = [
        'unpaid' => ['class' => 'warning', 'icon' => 'fe-alert-circle'],
        'paid' => ['class' => 'success', 'icon' => 'fe-check'],
        'refunded' => ['class' => 'secondary', 'icon' => 'fe-rotate-ccw'],
    ];
    $status = $statusMap[$enrollment->status] ?? ['class' => 'secondary', 'icon' => 'fe-help-circle'];
    $payment = $paymentMap[$enrollment->payment_status] ?? ['class' => 'secondary', 'icon' => 'fe-help-circle'];
    $camp = $enrollment->camp;
@endphp

<div class="col-lg-6 col-12 student-my-courses-stagger" style="--stagger-delay: {{ ($index ?? 0) * 50 }}ms">
    <article class="student-camp-enrollment-card h-100">
        <div class="student-camp-enrollment-card__header">
            <div class="min-w-0 flex-fill">
                <h5 class="student-camp-enrollment-card__title mb-1">{{ $camp?->name ?? 'معسكر غير متوفر' }}</h5>
                @if($camp?->category)
                    <span class="badge bg-info-transparent fs-11">{{ $camp->category->name }}</span>
                @endif
            </div>
            <span class="badge bg-{{ $status['class'] }}-transparent">
                <i class="fe {{ $status['icon'] }} me-1"></i>{{ $enrollment->status_label }}
            </span>
        </div>

        <div class="student-camp-enrollment-card__grid">
            <div class="student-camp-enrollment-card__stat">
                <span class="student-camp-enrollment-card__stat-label"><i class="fe fe-calendar me-1"></i>البداية</span>
                <span class="student-camp-enrollment-card__stat-value">{{ $camp?->start_date?->format('Y-m-d') ?? '—' }}</span>
            </div>
            <div class="student-camp-enrollment-card__stat">
                <span class="student-camp-enrollment-card__stat-label"><i class="fe fe-calendar me-1"></i>النهاية</span>
                <span class="student-camp-enrollment-card__stat-value">{{ $camp?->end_date?->format('Y-m-d') ?? '—' }}</span>
            </div>
            <div class="student-camp-enrollment-card__stat">
                <span class="student-camp-enrollment-card__stat-label"><i class="fe fe-dollar-sign me-1"></i>السعر</span>
                <span class="student-camp-enrollment-card__stat-value text-primary">${{ number_format($camp?->price ?? 0, 2) }}</span>
            </div>
            <div class="student-camp-enrollment-card__stat">
                <span class="student-camp-enrollment-card__stat-label"><i class="fe fe-credit-card me-1"></i>الدفع</span>
                <span class="badge bg-{{ $payment['class'] }}-transparent fs-11">
                    <i class="fe {{ $payment['icon'] }} me-1"></i>{{ $enrollment->payment_status_label }}
                </span>
            </div>
        </div>

        <div class="student-camp-enrollment-card__meta">
            @if($camp?->instructor_name)
                <span><i class="fe fe-user me-1"></i>{{ $camp->instructor_name }}</span>
            @endif
            @if($camp?->location)
                <span><i class="fe fe-map-pin me-1"></i>{{ $camp->location }}</span>
            @endif
            <span><i class="fe fe-clock me-1"></i>{{ $enrollment->created_at->diffForHumans() }}</span>
        </div>

        @if($enrollment->notes)
            <div class="student-camp-enrollment-card__notes">
                <i class="fe fe-info me-1"></i>{{ $enrollment->notes }}
            </div>
        @endif

        <div class="student-camp-enrollment-card__actions">
            @if($camp)
                <a href="{{ route('student.training-camps.show', $camp) }}" class="btn btn-primary btn-sm rounded-pill flex-fill">
                    <i class="fe fe-eye me-1"></i>عرض التفاصيل
                </a>
            @endif
            @if($enrollment->status !== 'approved' && $enrollment->payment_status !== 'paid' && $enrollment->status !== 'cancelled')
                <form action="{{ route('student.training-camps.cancel-enrollment', $enrollment->id) }}"
                      method="POST"
                      class="flex-fill"
                      onsubmit="return confirm('هل أنت متأكد من إلغاء التسجيل؟')">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill w-100">
                        <i class="fe fe-x-circle me-1"></i>إلغاء التسجيل
                    </button>
                </form>
            @endif
        </div>
    </article>
</div>
