@extends('admin.layouts.master')

@section('page-title', 'التقارير الأسبوعية للطلاب')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center my-4">
            <h5 class="mb-0">التقارير الأسبوعية للطلاب</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.weekly-reports.groups-overview') }}" class="btn btn-outline-info">تقارير المجموعات</a>
                <a href="{{ route('admin.weekly-reports.create') }}" class="btn btn-primary">إنشاء تقرير يدوي</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($totalSubmittedReports > 0)
            <div class="alert alert-warning d-flex justify-content-between align-items-center">
                <span>
                    يوجد <strong>{{ $totalSubmittedReports }}</strong> تقريرًا مسلّمًا ضمن
                    <strong>{{ $groupsWithSubmissionsCount }}</strong> مجموعة.
                </span>
                <a href="{{ route('admin.weekly-reports.schedules.index') }}" class="btn btn-sm btn-outline-dark">جدولة التقارير</a>
            </div>
        @endif

        @forelse($groupsWithSubmittedReports as $group)
            <div class="card custom-card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ $group['group_name'] }}</h6>
                    <span class="badge bg-success">{{ $group['submissions_count'] }} تسليم</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                            <tr>
                                <th>الطالب</th>
                                <th>العنوان</th>
                                <th>الحالة</th>
                                <th>وقت الإرسال</th>
                                <th>الموعد النهائي</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($group['reports'] as $report)
                                <tr>
                                    <td>{{ $report->student->name_ar ?? $report->student->name ?? '-' }}</td>
                                    <td>{{ $report->report_title }}</td>
                                    <td>
                                        @if($report->status === 'reviewed')
                                            <span class="badge bg-info">مراجع</span>
                                        @else
                                            <span class="badge bg-primary">مرسل</span>
                                        @endif
                                    </td>
                                    <td>{{ $report->submitted_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                    <td>{{ $report->due_at?->format('Y-m-d H:i') ?? 'غير محدد' }}</td>
                                    <td>
                                        <a class="btn btn-sm btn-primary" href="{{ route('admin.weekly-reports.show', $report) }}">عرض</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="card custom-card">
                <div class="card-body text-center py-4">
                    <h6 class="mb-1">لا توجد تقارير مسلّمة حاليًا</h6>
                    <p class="text-muted mb-0">ستظهر هنا المجموعات التي سلّم طلابها التقارير (مرسل/مراجع) فقط.</p>
                </div>
            </div>
        @endforelse

        <div class="mt-3">
            <a href="{{ route('admin.weekly-reports.schedules.index') }}" class="btn btn-outline-secondary">جدولة التقارير</a>
        </div>
    </div>
</div>
@endsection

