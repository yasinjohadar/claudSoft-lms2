@extends('student.layouts.master')

@section('page-title')
التقارير الأسبوعية
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">التقارير الأسبوعية</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active">التقارير الأسبوعية</li>
                    </ol>
                </nav>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card custom-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>العنوان</th>
                            <th>الموعد النهائي</th>
                            <th>الحالة</th>
                            <th>إجراء</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($reports as $report)
                            <tr>
                                <td>{{ $report->report_title }}</td>
                                <td>{{ $report->due_at?->format('Y-m-d H:i') ?? 'غير محدد' }}</td>
                                <td>
                                    @if($report->status === \App\Models\StudentWeeklyReport::STATUS_CLOSED)
                                        <span class="badge bg-danger">مغلق</span>
                                    @elseif($report->status === \App\Models\StudentWeeklyReport::STATUS_REVIEWED)
                                        <span class="badge bg-success">تمت المراجعة</span>
                                    @elseif($report->status === \App\Models\StudentWeeklyReport::STATUS_SUBMITTED)
                                        <span class="badge bg-info">مرسل</span>
                                    @else
                                        <span class="badge bg-warning">مسودة</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('student.weekly-reports.show', $report) }}" class="btn btn-sm btn-primary">فتح التقرير</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">لا توجد تقارير أسبوعية حالياً.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $reports->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

