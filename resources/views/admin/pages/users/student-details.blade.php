@extends('admin.layouts.master')

@section('page-title')
    تفاصيل الطالب - {{ $user->name }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">المستخدمون</a></li>
                        <li class="breadcrumb-item active">{{ $user->name }}</li>
                    </ol>
                </nav>
            </div>

            @include('admin.components.alerts')

            @php
                $initial = mb_strtoupper(mb_substr($user->name, 0, 1));
                $displayPhone = $user->full_phone
                    ?? (($user->country_code && $user->phone) ? $user->country_code . $user->phone : null)
                    ?? $user->phone;
                $groupCourseCount = $groupMemberships->sum(fn ($m) => $m->group->courses->count());
                $kpiCards = [
                    [
                        'variant' => 'blue',
                        'icon' => 'fe-layers',
                        'label' => 'المجموعات',
                        'value' => $groupMemberships->count(),
                        'sub' => 'مجموعات ينتمي إليها',
                    ],
                    [
                        'variant' => 'cyan',
                        'icon' => 'fe-book-open',
                        'label' => 'إجمالي التسجيلات',
                        'value' => $enrollments->count(),
                        'sub' => 'كورسات مسجّل فيها',
                    ],
                    [
                        'variant' => 'green',
                        'icon' => 'fe-book',
                        'label' => 'كورسات منفصلة',
                        'value' => $standaloneCourses->count(),
                        'sub' => 'خارج المجموعات',
                    ],
                    [
                        'variant' => 'orange',
                        'icon' => 'fe-link',
                        'label' => 'كورسات عبر المجموعات',
                        'value' => $groupCourseCount,
                        'sub' => 'مرتبطة بعضوياته',
                    ],
                ];
                $enrollmentStatusLabels = [
                    'active' => 'نشط',
                    'pending' => 'معلق',
                    'suspended' => 'معلق مؤقتاً',
                ];
            @endphp

            {{-- Hero --}}
            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-start gap-3">
                            <div class="admin-users-table__avatar flex-shrink-0" style="width: 72px; height: 72px; font-size: 1.5rem;">
                                @if($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}">
                                @elseif($user->photo)
                                    <img src="{{ asset('storage/' . $user->photo) }}" alt="{{ $user->name }}">
                                @else
                                    <span>{{ $initial }}</span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <span class="group-show-hero__eyebrow">
                                    <i class="fe fe-user me-1"></i>
                                    تفاصيل الطالب
                                </span>
                                <h2 class="group-show-hero__title mb-1">{{ $user->name }}</h2>
                                @if($user->name_ar)
                                    <p class="group-show-hero__desc mb-2">{{ $user->name_ar }}</p>
                                @endif
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    @if($user->email)
                                        <a href="mailto:{{ $user->email }}" class="group-show-chip group-show-chip--sm text-decoration-none">
                                            <i class="fe fe-mail me-1"></i>{{ $user->email }}
                                        </a>
                                    @endif
                                    @if($displayPhone)
                                        <span class="group-show-chip group-show-chip--sm">
                                            <i class="fe fe-phone me-1"></i>{{ $displayPhone }}
                                        </span>
                                    @endif
                                    @if($user->is_active)
                                        <span class="group-show-chip group-show-chip--sm text-success">
                                            <i class="fe fe-check-circle me-1"></i>حساب نشط
                                        </span>
                                    @else
                                        <span class="group-show-chip group-show-chip--sm text-muted">
                                            <i class="fe fe-slash me-1"></i>حساب غير نشط
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
                            <a href="{{ route('admin.users.courses', $user->id) }}" class="group-show-action group-show-action--success">
                                <span class="group-show-action__icon"><i class="fe fe-book-open"></i></span>
                                <span class="group-show-action__text">كورسات الطالب</span>
                            </a>
                            <a href="{{ route('users.show', $user->id) }}" class="group-show-action group-show-action--warning">
                                <span class="group-show-action__icon"><i class="fe fe-user"></i></span>
                                <span class="group-show-action__text">عرض الملف</span>
                            </a>
                            <a href="{{ route('users.edit', $user->id) }}" class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-edit-2"></i></span>
                                <span class="group-show-action__text">تعديل الحساب</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stats --}}
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

            {{-- Groups --}}
            <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                    <h6 class="group-show-members-card__title mb-0">
                        المجموعات المنتمي إليها
                        <span class="group-show-members-card__count">{{ $groupMemberships->count() }}</span>
                    </h6>
                </div>
                <div class="card-body pt-3">
                    @if($groupMemberships->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 admin-users-table">
                                <thead>
                                    <tr>
                                        <th>المجموعة</th>
                                        <th>الدور</th>
                                        <th>تاريخ الانضمام</th>
                                        <th>الكورسات</th>
                                        <th style="width: 100px;">إجراء</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($groupMemberships as $membership)
                                        <tr class="admin-users-table__row">
                                            <td>
                                                <div class="fw-semibold">{{ $membership->group->name }}</div>
                                                @if($membership->group->description)
                                                    <small class="text-muted d-block mt-1">{{ Str::limit($membership->group->description, 80) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($membership->role === 'leader')
                                                    <span class="group-show-chip group-show-chip--sm">قائد</span>
                                                @else
                                                    <span class="group-show-chip group-show-chip--sm">عضو</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="small text-muted">
                                                    <i class="fe fe-calendar me-1"></i>
                                                    {{ $membership->joined_at?->format('Y-m-d') }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($membership->group->courses->isNotEmpty())
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach($membership->group->courses as $course)
                                                            <span class="group-show-chip group-show-chip--sm" title="{{ $course->title }}">
                                                                {{ Str::limit($course->title, 28) }}
                                                                @if($course->code)
                                                                    <small class="opacity-75">({{ $course->code }})</small>
                                                                @endif
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button type="button"
                                                        class="btn btn-sm btn-danger-light"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#removeGroupModal{{ $membership->group->id }}"
                                                        title="إزالة من المجموعة">
                                                    <i class="fe fe-user-minus"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="group-show-empty py-4">
                            <i class="fe fe-layers group-show-empty__icon"></i>
                            <h5 class="group-show-empty__title">لا توجد مجموعات</h5>
                            <p class="group-show-empty__desc mb-0">الطالب غير منتمٍ لأي مجموعة حالياً. يمكنك إضافته من النموذج أدناه.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Add to group / course --}}
            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="card custom-card group-show-members-card dashboard-fade-in h-100">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title mb-1">إضافة إلى مجموعة</h4>
                            <p class="fs-12 text-muted mb-0">اختر مجموعة ودور العضوية — يُسجَّل الطالب تلقائياً في كورسات المجموعة.</p>
                        </div>
                        <div class="card-body pt-3">
                            @if($availableGroups->isNotEmpty())
                                <form action="{{ route('users.add-to-group', $user->id) }}" method="POST" class="group-show-filters mb-0">
                                    @csrf
                                    <div class="row g-3 align-items-end">
                                        <div class="col-12">
                                            <label class="form-label" for="group_id">المجموعة</label>
                                            <select name="group_id" id="group_id" class="form-select" required>
                                                <option value="">اختر مجموعة</option>
                                                @foreach($availableGroups as $group)
                                                    <option value="{{ $group->id }}">
                                                        {{ $group->name }} ({{ $group->courses->count() }} كورس)
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="group_role">الدور</label>
                                            <select name="role" id="group_role" class="form-select">
                                                <option value="member">عضو</option>
                                                <option value="leader">قائد</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="fe fe-user-plus me-1"></i>إضافة للمجموعة
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @else
                                <div class="group-show-empty py-3">
                                    <i class="fe fe-layers group-show-empty__icon" style="width: 64px; height: 64px; font-size: 1.5rem;"></i>
                                    <p class="group-show-empty__desc mb-0">لا توجد مجموعات متاحة لإضافة الطالب إليها.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card custom-card group-show-members-card dashboard-fade-in h-100">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title mb-1">إضافة إلى كورس</h4>
                            <p class="fs-12 text-muted mb-0">تسجيل مباشر في كورس دون ربطه بمجموعة.</p>
                        </div>
                        <div class="card-body pt-3">
                            @if($availableCourses->isNotEmpty())
                                <form action="{{ route('users.enroll-course', $user->id) }}" method="POST" class="group-show-filters mb-0">
                                    @csrf
                                    <div class="row g-3 align-items-end">
                                        <div class="col-12">
                                            <label class="form-label" for="course_id">الكورس</label>
                                            <select name="course_id" id="course_id" class="form-select" required>
                                                <option value="">اختر كورس</option>
                                                @foreach($availableCourses as $course)
                                                    <option value="{{ $course->id }}">
                                                        {{ $course->title }}
                                                        @if($course->code) ({{ $course->code }}) @endif
                                                        @if($course->category) — {{ $course->category->name }} @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="enrollment_status">حالة التسجيل</label>
                                            <select name="enrollment_status" id="enrollment_status" class="form-select">
                                                <option value="active">نشط</option>
                                                <option value="pending">معلق</option>
                                                <option value="suspended">معلق مؤقتاً</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="fe fe-book-open me-1"></i>تسجيل في الكورس
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @else
                                <div class="group-show-empty py-3">
                                    <i class="fe fe-book-open group-show-empty__icon" style="width: 64px; height: 64px; font-size: 1.5rem;"></i>
                                    <p class="group-show-empty__desc mb-0">لا توجد كورسات متاحة للتسجيل.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Standalone courses --}}
            <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                    <h6 class="group-show-members-card__title mb-0">
                        الكورسات المنفصلة
                        <span class="group-show-members-card__count">{{ $standaloneCourses->count() }}</span>
                    </h6>
                    <p class="fs-12 text-muted mb-0">كورسات مسجّل فيها الطالب مباشرة وليست عبر مجموعة.</p>
                </div>
                <div class="card-body pt-3">
                    @if($standaloneCourses->isNotEmpty())
                        <div class="row g-3">
                            @foreach($standaloneCourses as $course)
                                @php
                                    $enrollment = $enrollments->firstWhere('course_id', $course->id);
                                    $statusKey = $enrollment?->enrollment_status ?? 'active';
                                    $statusLabel = $enrollmentStatusLabels[$statusKey] ?? $statusKey;
                                @endphp
                                <div class="col-xl-4 col-lg-6">
                                    <div class="card border h-100 student-detail-course-card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                                <h6 class="fw-bold mb-0 flex-fill">{{ $course->title }}</h6>
                                                <button type="button"
                                                        class="btn btn-sm btn-danger-light flex-shrink-0"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#removeCourseModal{{ $course->id }}"
                                                        title="إزالة من الكورس">
                                                    <i class="fe fe-trash-2"></i>
                                                </button>
                                            </div>
                                            @if($course->code)
                                                <p class="text-muted small mb-2">{{ $course->code }}</p>
                                            @endif
                                            @if($course->category)
                                                <span class="group-show-chip group-show-chip--sm mb-2">{{ $course->category->name }}</span>
                                            @endif
                                            @if($enrollment)
                                                <div class="mt-3 pt-3 border-top">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="small text-muted">حالة التسجيل</span>
                                                        <span class="group-show-chip group-show-chip--sm {{ $statusKey === 'active' ? 'text-success' : '' }}">
                                                            {{ $statusLabel }}
                                                        </span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <span class="small text-muted">التقدم</span>
                                                        <span class="small fw-semibold">{{ $enrollment->completion_percentage }}%</span>
                                                    </div>
                                                    <div class="progress progress-sm" style="height: 6px;">
                                                        <div class="progress-bar bg-primary" role="progressbar"
                                                             style="width: {{ min(100, max(0, (float) $enrollment->completion_percentage)) }}%"
                                                             aria-valuenow="{{ $enrollment->completion_percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="group-show-empty py-4">
                            <i class="fe fe-book group-show-empty__icon"></i>
                            <h5 class="group-show-empty__title">لا توجد كورسات منفصلة</h5>
                            <p class="group-show-empty__desc mb-0">جميع تسجيلات الطالب مرتبطة بمجموعات، أو لم يُسجَّل بعد في كورسات مباشرة.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- Remove course modals --}}
    @foreach($standaloneCourses as $course)
        <div class="modal fade" id="removeCourseModal{{ $course->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fe fe-alert-triangle text-danger me-2"></i>
                            إزالة من الكورس
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">هل أنت متأكد من إزالة <strong>{{ $user->name }}</strong> من الكورس:</p>
                        <div class="group-show-filters mb-3">
                            <strong>{{ $course->title }}</strong>
                            @if($course->code)
                                <span class="text-muted">({{ $course->code }})</span>
                            @endif
                        </div>
                        <p class="text-muted small mb-0">
                            <i class="fe fe-info me-1"></i>
                            إذا كان التسجيل عبر مجموعة، لن تتم الإزالة من هنا.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                        <form action="{{ route('users.unenroll-course', $user->id) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="course_id" value="{{ $course->id }}">
                            <button type="submit" class="btn btn-danger">
                                <i class="fe fe-trash-2 me-1"></i>نعم، إزالة
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Remove group modals --}}
    @foreach($groupMemberships as $membership)
        <div class="modal fade" id="removeGroupModal{{ $membership->group->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fe fe-alert-triangle text-danger me-2"></i>
                            إزالة من المجموعة
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">هل أنت متأكد من إزالة <strong>{{ $user->name }}</strong> من المجموعة:</p>
                        <div class="group-show-filters mb-3">
                            <strong>{{ $membership->group->name }}</strong>
                        </div>
                        @if($membership->group->courses->isNotEmpty())
                            <p class="small text-muted mb-2">الكورسات المرتبطة:</p>
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                @foreach($membership->group->courses as $course)
                                    <span class="group-show-chip group-show-chip--sm">{{ $course->title }}</span>
                                @endforeach
                            </div>
                            <p class="text-muted small mb-0">
                                <i class="fe fe-info me-1"></i>
                                يُلغى التسجيل في هذه الكورسات إذا لم يكن الطالب مسجّلاً فيها عبر مجموعة أخرى.
                            </p>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                        <form action="{{ route('users.remove-from-group', $user->id) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="group_id" value="{{ $membership->group->id }}">
                            <button type="submit" class="btn btn-danger">
                                <i class="fe fe-user-minus me-1"></i>نعم، إزالة
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
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
