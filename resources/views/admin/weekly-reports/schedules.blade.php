@extends('admin.layouts.master')

@section('page-title', 'جدولة التقارير الأسبوعية')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="my-4">
            <h5>جدولة التقارير الأسبوعية</h5>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card custom-card mb-3">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.weekly-reports.schedules.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">اسم الجدولة</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">اليوم</label>
                            <select name="weekday" class="form-select" required>
                                <option value="0">الأحد</option>
                                <option value="1">الاثنين</option>
                                <option value="2">الثلاثاء</option>
                                <option value="3">الأربعاء</option>
                                <option value="4">الخميس</option>
                                <option value="5">الجمعة</option>
                                <option value="6">السبت</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">وقت الاستحقاق</label>
                            <input type="time" name="due_time" class="form-control" value="23:00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الكورس</label>
                            <select name="target_course_id" id="target_course_id" class="form-select" required>
                                <option value="">اختر الكورس</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
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
                    </div>
                    <button class="btn btn-primary mt-3">إنشاء جدولة</button>
                </form>
            </div>
        </div>

        <div class="card custom-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>الاسم</th>
                            <th>الكورس</th>
                            <th>المجموعة</th>
                            <th>اليوم</th>
                            <th>next_run_at</th>
                            <th>الحالة</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($schedules as $schedule)
                            <tr>
                                <td>{{ $schedule->name }}</td>
                                <td>{{ $schedule->targetCourse?->title ?? '-' }}</td>
                                <td>{{ $schedule->targetGroup?->name ?? '-' }}</td>
                                <td>{{ $schedule->weekday }}</td>
                                <td>{{ $schedule->next_run_at?->format('Y-m-d H:i') }}</td>
                                <td>{{ $schedule->is_active ? 'نشطة' : 'متوقفة' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.weekly-reports.schedules.toggle', $schedule) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-primary">تبديل</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center">لا توجد جدولات.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $schedules->links() }}
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

