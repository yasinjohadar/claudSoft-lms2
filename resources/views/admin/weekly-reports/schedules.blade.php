@extends('admin.layouts.master')

@section('page-title', 'جدولة التقارير الأسبوعية')

@section('content')
@php
    $weekdayLabels = [
        0 => 'الأحد',
        1 => 'الاثنين',
        2 => 'الثلاثاء',
        3 => 'الأربعاء',
        4 => 'الخميس',
        5 => 'الجمعة',
        6 => 'السبت',
    ];
    $activeSchedules = $scheduleStats['active'] ?? 0;
    $totalSchedules = $scheduleStats['total'] ?? $schedules->total();
@endphp
<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.components.alerts')

        @include('admin.weekly-reports.partials.breadcrumb', ['current' => 'جدولة التقارير'])

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-calendar me-1"></i>
                        التقارير الأسبوعية
                    </span>
                    <h2 class="group-show-hero__title mb-2">جدولة التقارير</h2>
                    <p class="group-show-hero__desc mb-0">
                        إعداد جداول تلقائية لإنشاء تقارير أسبوعية لمجموعات محددة في أيام ومواعيد ثابتة.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions group-show-actions--single">
                        <a href="{{ route('admin.weekly-reports.create') }}"
                           class="group-show-action group-show-action--primary">
                            <span class="group-show-action__icon"><i class="fe fe-plus"></i></span>
                            <span class="group-show-action__text">إنشاء تقرير يدوي</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @php
            $kpiCards = [
                [
                    'variant' => 'blue',
                    'icon' => 'fe-calendar',
                    'label' => 'إجمالي الجداول',
                    'value' => $totalSchedules,
                    'sub' => 'جدولات مسجّلة في النظام',
                ],
                [
                    'variant' => 'green',
                    'icon' => 'fe-play-circle',
                    'label' => 'جداول نشطة',
                    'value' => $activeSchedules,
                    'sub' => 'تعمل حالياً',
                ],
            ];
        @endphp

        <div id="weeklyReportsStats" class="mb-4">
            @include('admin.weekly-reports.partials.stats', ['kpiCards' => $kpiCards])
        </div>

        @include('admin.weekly-reports.partials.quick-nav', ['navActive' => 'schedules'])

        <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
            <div class="card-header border-0 pb-0">
                <h4 class="card-title mb-1">إنشاء جدولة جديدة</h4>
                <p class="fs-12 text-muted mb-0">حدّد الكورس والمجموعة ويوم ووقت استحقاق التقرير.</p>
            </div>
            <div class="card-body pt-3">
                <form method="POST" action="{{ route('admin.weekly-reports.schedules.store') }}" class="group-show-filters mb-0">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-xl-3 col-lg-4 col-md-6">
                            <label class="form-label">اسم الجدولة</label>
                            <input type="text" name="name" class="form-control" placeholder="مثال: تقرير أسبوعي - React" required>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <label class="form-label">اليوم</label>
                            <select name="weekday" class="form-select" required>
                                @foreach($weekdayLabels as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <label class="form-label">وقت الاستحقاق</label>
                            <input type="time" name="due_time" class="form-control" value="23:00" required>
                        </div>
                        <div class="col-xl-2 col-lg-4 col-md-6">
                            <label class="form-label">الكورس</label>
                            <select name="target_course_id" id="target_course_id" class="form-select" required>
                                <option value="">اختر الكورس</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-2 col-lg-4 col-md-6">
                            <label class="form-label">المجموعة</label>
                            <select name="target_group_id" id="target_group_id" class="form-select" required>
                                <option value="">اختر المجموعة</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" data-course-ids="{{ $group->courses->pluck('id')->implode(',') }}">
                                        {{ $group->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-1 col-lg-12 col-md-6">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fe fe-plus me-1"></i>إنشاء
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in">
            <div class="card-header border-0 pb-0">
                <h4 class="card-title mb-1">الجداول الحالية</h4>
                <p class="fs-12 text-muted mb-0">جميع جداول التقارير الأسبوعية ومواعيد التشغيل القادمة.</p>
            </div>
            <div class="card-body pt-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                        <tr>
                            <th>الاسم</th>
                            <th>الكورس</th>
                            <th>المجموعة</th>
                            <th>اليوم</th>
                            <th>التشغيل القادم</th>
                            <th>الحالة</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($schedules as $schedule)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $schedule->name }}</div>
                                </td>
                                <td>{{ $schedule->targetCourse?->title ?? '-' }}</td>
                                <td>{{ $schedule->targetGroup?->name ?? '-' }}</td>
                                <td>{{ $weekdayLabels[$schedule->weekday] ?? $schedule->weekday }}</td>
                                <td>{{ $schedule->next_run_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                <td>
                                    @if($schedule->is_active)
                                        <span class="badge bg-success-transparent text-success">نشطة</span>
                                    @else
                                        <span class="badge bg-secondary-transparent text-secondary">متوقفة</span>
                                    @endif
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.weekly-reports.schedules.toggle', $schedule) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            {{ $schedule->is_active ? 'إيقاف' : 'تفعيل' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    @include('admin.weekly-reports.partials.empty-state', [
                                        'icon' => 'fe-calendar',
                                        'title' => 'لا توجد جدولات',
                                        'description' => 'أنشئ جدولة جديدة لتوليد تقارير أسبوعية تلقائياً.',
                                    ])
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                @if($schedules->hasPages())
                    <div class="mt-3">
                        {{ $schedules->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const courseSelect = document.getElementById('target_course_id');
        const groupSelect = document.getElementById('target_group_id');
        if (!courseSelect || !groupSelect) {
            return;
        }

        const filterGroups = () => {
            const selectedCourseId = courseSelect.value;
            const options = Array.from(groupSelect.options);

            options.forEach((option, index) => {
                if (index === 0) {
                    option.hidden = false;
                    return;
                }

                const courseIds = (option.dataset.courseIds || '')
                    .split(',')
                    .map((id) => id.trim())
                    .filter(Boolean);

                const visible = selectedCourseId && courseIds.includes(selectedCourseId);
                option.hidden = !visible;

                if (!visible && option.selected) {
                    option.selected = false;
                }
            });
        };

        courseSelect.addEventListener('change', filterGroups);
        filterGroups();
    })();
</script>
@endpush

@include('admin.weekly-reports.partials.countup-script')
