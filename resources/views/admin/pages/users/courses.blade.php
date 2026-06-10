@extends('admin.layouts.master')

@section('page-title')
    كورسات الطالب - {{ $student->name }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">المستخدمون</a></li>
                        <li class="breadcrumb-item active">كورسات الطالب</li>
                    </ol>
                </nav>
            </div>

            @include('admin.components.alerts')

            @php
                $initial = mb_strtoupper(mb_substr($student->name, 0, 1));
                $displayPhone = $student->full_phone
                    ?? (($student->country_code && $student->phone) ? $student->country_code . $student->phone : null)
                    ?? $student->phone;
                $pendingTotal = $stats['pending_enrollments'] + $stats['suspended_enrollments'] + $stats['cancelled_enrollments'];
                $kpiCards = [
                    [
                        'variant' => 'blue',
                        'icon' => 'fe-book-open',
                        'label' => 'إجمالي الكورسات',
                        'value' => $stats['total_enrollments'],
                        'sub' => 'كل التسجيلات',
                    ],
                    [
                        'variant' => 'green',
                        'icon' => 'fe-play-circle',
                        'label' => 'كورسات نشطة',
                        'value' => $stats['active_enrollments'],
                        'sub' => 'قيد التعلم',
                    ],
                    [
                        'variant' => 'cyan',
                        'icon' => 'fe-check-circle',
                        'label' => 'كورسات مكتملة',
                        'value' => $stats['completed_enrollments'],
                        'sub' => 'منتهية بنجاح',
                    ],
                    [
                        'variant' => 'orange',
                        'icon' => 'fe-clock',
                        'label' => 'معلقة / ملغية',
                        'value' => $pendingTotal,
                        'sub' => 'قيد الانتظار أو موقوفة',
                    ],
                ];
                $statusLabels = [
                    'active' => ['label' => 'نشط', 'class' => 'text-success'],
                    'pending' => ['label' => 'قيد الانتظار', 'class' => 'text-warning'],
                    'completed' => ['label' => 'مكتمل', 'class' => 'text-info'],
                    'suspended' => ['label' => 'معلق', 'class' => 'text-danger'],
                    'cancelled' => ['label' => 'ملغي', 'class' => 'text-muted'],
                ];
            @endphp

            {{-- Hero --}}
            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-start gap-3">
                            <div class="admin-users-table__avatar flex-shrink-0" style="width: 72px; height: 72px; font-size: 1.5rem;">
                                @if($student->avatar)
                                    <img src="{{ asset('storage/' . $student->avatar) }}" alt="{{ $student->name }}">
                                @elseif($student->photo)
                                    <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->name }}">
                                @else
                                    <span>{{ $initial }}</span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <span class="group-show-hero__eyebrow">
                                    <i class="fe fe-book-open me-1"></i>
                                    كورسات الطالب
                                </span>
                                <h2 class="group-show-hero__title mb-1">{{ $student->name }}</h2>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    @if($student->email)
                                        <a href="mailto:{{ $student->email }}" class="group-show-chip group-show-chip--sm text-decoration-none">
                                            <i class="fe fe-mail me-1"></i>{{ $student->email }}
                                        </a>
                                    @endif
                                    @if($displayPhone)
                                        <span class="group-show-chip group-show-chip--sm">
                                            <i class="fe fe-phone me-1"></i>{{ $displayPhone }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions">
                            <a href="{{ route('users.index') }}" class="group-show-action group-show-action--info">
                                <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                                <span class="group-show-action__text">رجوع للمستخدمين</span>
                            </a>
                            <a href="{{ route('users.student-details', $student->id) }}" class="group-show-action group-show-action--success">
                                <span class="group-show-action__icon"><i class="fe fe-layers"></i></span>
                                <span class="group-show-action__text">المجموعات والتفاصيل</span>
                            </a>
                            <a href="{{ route('users.show', $student->id) }}" class="group-show-action group-show-action--warning">
                                <span class="group-show-action__icon"><i class="fe fe-user"></i></span>
                                <span class="group-show-action__text">عرض الملف</span>
                            </a>
                            <a href="{{ route('users.edit', $student->id) }}" class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-edit-2"></i></span>
                                <span class="group-show-action__text">تعديل الحساب</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KPI stats --}}
            <div class="row g-3 dashboard-fade-in mb-4">
                @foreach ($kpiCards as $index => $card)
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 dashboard-stagger-item" style="--stagger-delay: {{ $index * 70 }}ms">
                        <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="admin-stats-card__icon-wrap">
                                    <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                                </div>
                                <div class="admin-stats-card__content flex-fill min-w-0">
                                    <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                                    <h3 class="admin-stats-card__value mb-1" data-countup="{{ $card['value'] }}">0</h3>
                                    <p class="admin-stats-card__sub mb-0">{{ $card['sub'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Progress overview --}}
            <div class="row g-3 dashboard-fade-in mb-4">
                <div class="col-lg-6">
                    <div class="card custom-card group-show-members-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <p class="admin-stats-card__label mb-1">متوسط التقدم الإجمالي</p>
                                    <h3 class="admin-stats-card__value mb-0 text-primary">{{ number_format($stats['average_progress'], 1) }}%</h3>
                                </div>
                                <div class="admin-stats-card__icon-wrap">
                                    <i class="fe fe-trending-up admin-stats-card__icon text-primary"></i>
                                </div>
                            </div>
                            <div class="progress" style="height: 10px; border-radius: 999px;">
                                <div class="progress-bar bg-primary" role="progressbar"
                                     style="width: {{ min(100, max(0, (float) $stats['average_progress'])) }}%"
                                     aria-valuenow="{{ $stats['average_progress'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card custom-card group-show-members-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <p class="admin-stats-card__label mb-1">متوسط الدرجات</p>
                                    <h3 class="admin-stats-card__value mb-0 text-success">{{ number_format($stats['average_grade'], 1) }}%</h3>
                                </div>
                                <div class="admin-stats-card__icon-wrap">
                                    <i class="fe fe-award admin-stats-card__icon text-success"></i>
                                </div>
                            </div>
                            <div class="progress" style="height: 10px; border-radius: 999px;">
                                <div class="progress-bar bg-success" role="progressbar"
                                     style="width: {{ min(100, max(0, (float) $stats['average_grade'])) }}%"
                                     aria-valuenow="{{ $stats['average_grade'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Enrollments table --}}
            <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                    <h6 class="group-show-members-card__title mb-0">
                        قائمة الكورسات المسجلة
                        <span class="group-show-members-card__count">{{ $enrollments->count() }}</span>
                    </h6>
                </div>
                <div class="card-body pt-3">
                    @if($enrollments->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 admin-users-table">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;">#</th>
                                        <th style="min-width: 220px;">الكورس</th>
                                        <th>التصنيف</th>
                                        <th>المدرب</th>
                                        <th>تاريخ التسجيل</th>
                                        <th>الحالة</th>
                                        <th style="min-width: 130px;">التقدم</th>
                                        <th>الدرجة</th>
                                        <th>الشهادة</th>
                                        <th style="width: 100px;">إجراء</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($enrollments as $enrollment)
                                        @php
                                            $status = $statusLabels[$enrollment->enrollment_status] ?? ['label' => $enrollment->enrollment_status, 'class' => ''];
                                            $progress = (float) $enrollment->completion_percentage;
                                        @endphp
                                        <tr class="admin-users-table__row">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2 min-w-0">
                                                    @if($enrollment->course->image)
                                                        <img src="{{ course_image_url($enrollment->course->image) }}"
                                                             alt="{{ $enrollment->course->title }}"
                                                             class="rounded flex-shrink-0"
                                                             style="width: 42px; height: 42px; object-fit: cover;"
                                                             onerror="this.style.display='none';">
                                                    @endif
                                                    <div class="min-w-0">
                                                        <a href="{{ route('courses.show', $enrollment->course->id) }}"
                                                           class="fw-semibold text-decoration-none d-block text-truncate admin-users-table__name">
                                                            {{ $enrollment->course->title }}
                                                        </a>
                                                        @if($enrollment->course->course_code)
                                                            <small class="text-muted">{{ $enrollment->course->course_code }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($enrollment->course->category)
                                                    <span class="group-show-chip group-show-chip--sm">{{ $enrollment->course->category->name }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($enrollment->course->instructor)
                                                    <span class="small">{{ $enrollment->course->instructor->name }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="small">{{ $enrollment->enrollment_date->format('Y-m-d') }}</span>
                                                <br>
                                                <small class="text-muted">{{ $enrollment->enrollment_date->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <span class="group-show-chip group-show-chip--sm {{ $status['class'] }}">
                                                    {{ $status['label'] }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="small fw-semibold" style="min-width: 42px;">{{ number_format($progress, 1) }}%</span>
                                                    <div class="progress flex-fill" style="height: 6px; min-width: 60px;">
                                                        <div class="progress-bar {{ $progress >= 100 ? 'bg-success' : 'bg-primary' }}"
                                                             role="progressbar"
                                                             style="width: {{ min(100, max(0, $progress)) }}%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($enrollment->grade !== null)
                                                    <span class="group-show-chip group-show-chip--sm {{ $enrollment->grade >= 50 ? 'text-success' : 'text-danger' }}">
                                                        {{ number_format($enrollment->grade, 1) }}%
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($enrollment->certificate_issued)
                                                    <span class="group-show-chip group-show-chip--sm text-success">
                                                        <i class="fe fe-award me-1"></i>صدرت
                                                    </span>
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-1">
                                                    <a href="{{ route('courses.show', $enrollment->course->id) }}"
                                                       class="btn btn-sm btn-info-light"
                                                       title="عرض الكورس">
                                                        <i class="fe fe-eye"></i>
                                                    </a>
                                                    <a href="{{ route('courses.enrollments.index', $enrollment->course->id) }}"
                                                       class="btn btn-sm btn-primary-light"
                                                       title="إدارة التسجيلات">
                                                        <i class="fe fe-users"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="group-show-empty py-5">
                            <i class="fe fe-book-open group-show-empty__icon"></i>
                            <h5 class="group-show-empty__title">لا توجد كورسات مسجلة</h5>
                            <p class="group-show-empty__desc mb-3">هذا الطالب غير مسجّل في أي كورس حالياً.</p>
                            <a href="{{ route('users.student-details', $student->id) }}" class="btn btn-primary btn-sm">
                                <i class="fe fe-plus me-1"></i>إضافة إلى كورس أو مجموعة
                            </a>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@stop

@push('scripts')
<script>
    document.querySelectorAll('[data-countup]').forEach(function (el) {
        var target = parseInt(el.getAttribute('data-countup'), 10) || 0;
        if (target === 0) {
            el.textContent = '0';
            return;
        }
        var current = 0;
        var step = Math.max(1, Math.ceil(target / 20));
        var timer = setInterval(function () {
            current += step;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            el.textContent = current.toLocaleString('ar-EG');
        }, 30);
    });
</script>
@endpush
