@extends('admin.layouts.master')

@section('page-title', 'تقارير المجموعات')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center my-4">
            <h5 class="mb-0">تقارير المجموعات (المنشأة والمسلّمة)</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.weekly-reports.index') }}" class="btn btn-outline-secondary">التقارير المسلّمة</a>
                <a href="{{ route('admin.weekly-reports.create') }}" class="btn btn-primary">إنشاء تقرير يدوي</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="alert alert-info d-flex justify-content-between align-items-center">
            <span>
                تم إنشاء <strong>{{ $totalCreatedReports }}</strong> تقريرًا للمجموعات،
                والمسلّم منها <strong>{{ $totalSubmittedReports }}</strong>.
            </span>
            <a href="{{ route('admin.weekly-reports.schedules.index') }}" class="btn btn-sm btn-outline-dark">جدولة التقارير</a>
        </div>

        @forelse($groupsData as $item)
            @php
                /** @var \App\Models\CourseGroup $group */
                $group = $item['group'];
                $submittedReports = $item['submitted_reports'];
            @endphp
            <div class="card custom-card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ $group->name }}</h6>
                    <div class="d-flex gap-2">
                        <span class="badge bg-secondary">{{ $item['total_reports_count'] }} تقارير منشأة</span>
                        <span class="badge bg-success">{{ $item['submitted_reports_count'] }} تقارير مسلّمة</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($submittedReports->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>الطالب</th>
                                    <th>عنوان التقرير</th>
                                    <th>الحالة</th>
                                    <th>وقت الإرسال</th>
                                    <th>الموعد النهائي</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($submittedReports as $report)
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
                    @else
                        <div class="alert alert-light mb-0">
                            لا يوجد طلاب مسلّمين لهذه المجموعة حتى الآن، رغم وجود تقارير منشأة لها.
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="card custom-card">
                <div class="card-body text-center py-4">
                    <h6 class="mb-1">لا توجد مجموعات لديها تقارير منشأة</h6>
                    <p class="text-muted mb-0">أنشئ تقارير للمجموعات أولًا وستظهر هنا تلقائيًا.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
