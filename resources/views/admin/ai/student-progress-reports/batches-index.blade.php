@extends('admin.layouts.master')

@section('page-title')
    دفعات تقارير الدراسة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">دفعات جدولة تقارير الدراسة</h5>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.ai.student-progress-reports.create') }}" class="btn btn-primary btn-sm">إنشاء دفعة</a>
                <a href="{{ route('admin.ai.student-progress-reports.index') }}" class="btn btn-secondary btn-sm">تقارير الدراسة</a>
            </div>
        </div>

        @include('admin.components.alerts')

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الكورس</th>
                                <th>نطاق الإرسال</th>
                                <th>الحالة</th>
                                <th>التقدم</th>
                                <th>التاريخ</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($batches as $batch)
                                <tr>
                                    <td>{{ $batch->id }}</td>
                                    <td>{{ $batch->course?->title ?? '—' }}</td>
                                    <td>{{ $batch->humanScopeSummary() }}</td>
                                    <td>
                                        @if($batch->status === 'completed')
                                            <span class="badge bg-success">مكتمل</span>
                                        @elseif($batch->status === 'partial_failed')
                                            <span class="badge bg-warning text-dark">مكتمل مع أخطاء</span>
                                        @else
                                            <span class="badge bg-primary">قيد التنفيذ</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">
                                        {{ $batch->items_succeeded }} نجاح /
                                        {{ $batch->items_failed }} فشل /
                                        {{ $batch->items_skipped }} تخطٍ
                                        <span class="text-nowrap">(من {{ $batch->items_total }})</span>
                                    </td>
                                    <td>{{ $batch->created_at?->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <a href="{{ route('admin.ai.student-progress-reports.batches.show', $batch) }}" class="btn btn-sm btn-outline-primary">تفاصيل</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">لا توجد دفعات بعد.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $batches->links() }}
            </div>
        </div>
    </div>
</div>
@stop
