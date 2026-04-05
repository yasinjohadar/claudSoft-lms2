@extends('admin.layouts.master')

@section('page-title')
    تقارير الدراسة (AI)
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تقارير الدراسة (Laravel AI)</h5>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.ai.student-progress-reports.batches.index') }}" class="btn btn-outline-primary btn-sm">دفعات الجدولة</a>
                <a href="{{ route('admin.ai.student-progress-reports.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> إنشاء / جدولة
                </a>
            </div>
        </div>

        @include('admin.components.alerts')

        @if(isset($recentBatches) && $recentBatches->isNotEmpty())
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>آخر دفعات الجدولة</span>
                <a href="{{ route('admin.ai.student-progress-reports.batches.index') }}" class="btn btn-sm btn-link">الكل</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الكورس</th>
                                <th>النطاق</th>
                                <th>الحالة</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentBatches as $b)
                                <tr>
                                    <td>{{ $b->id }}</td>
                                    <td>{{ $b->course?->title }}</td>
                                    <td>{{ $b->humanScopeSummary() }}</td>
                                    <td>
                                        @if($b->status === 'completed')
                                            <span class="badge bg-success">مكتمل</span>
                                        @elseif($b->status === 'partial_failed')
                                            <span class="badge bg-warning text-dark">مع أخطاء</span>
                                        @else
                                            <span class="badge bg-primary">جاري</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.ai.student-progress-reports.batches.show', $b) }}" class="btn btn-sm btn-outline-primary">تفاصيل</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الطالب</th>
                                <th>الكورس</th>
                                <th>أنشأه</th>
                                <th>التاريخ</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $report)
                                <tr>
                                    <td>{{ $report->id }}</td>
                                    <td>{{ $report->student?->name }}</td>
                                    <td>{{ $report->course?->title }}</td>
                                    <td>{{ $report->creator?->name ?? '—' }}</td>
                                    <td>{{ $report->created_at?->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <a href="{{ route('admin.ai.student-progress-reports.show', $report) }}" class="btn btn-sm btn-outline-primary">
                                            عرض
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">لا توجد تقارير بعد.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $reports->links() }}
            </div>
        </div>
    </div>
</div>
@stop
