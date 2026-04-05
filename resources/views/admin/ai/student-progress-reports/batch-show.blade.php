@extends('admin.layouts.master')

@section('page-title')
    دفعة #{{ $batch->id }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">دفعة تقارير الدراسة #{{ $batch->id }}</h5>
                <p class="mb-0 text-muted small">{{ $batch->course?->title }} — {{ $batch->humanScopeSummary() }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.ai.student-progress-reports.batches.index') }}" class="btn btn-secondary btn-sm">كل الدفعات</a>
                <a href="{{ route('admin.ai.student-progress-reports.create') }}" class="btn btn-primary btn-sm">دفعة جديدة</a>
            </div>
        </div>

        @include('admin.components.alerts')

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="row g-3 small">
                    <div class="col-md-3"><strong>الحالة:</strong>
                        @if($batch->status === 'completed')
                            <span class="badge bg-success">مكتمل</span>
                        @elseif($batch->status === 'partial_failed')
                            <span class="badge bg-warning text-dark">مكتمل مع أخطاء</span>
                        @else
                            <span class="badge bg-primary">قيد التنفيذ</span>
                        @endif
                    </div>
                    <div class="col-md-3"><strong>المهام:</strong> {{ $batch->items_total }}</div>
                    <div class="col-md-3"><strong>نجاح:</strong> {{ $batch->items_succeeded }}</div>
                    <div class="col-md-3"><strong>فشل / تخطٍ:</strong> {{ $batch->items_failed }} / {{ $batch->items_skipped }}</div>
                    <div class="col-md-3"><strong>استراتيجية المحاولة:</strong> {{ $batch->attempt_strategy === 'best' ? 'أفضل نتيجة' : 'آخر محاولة' }}</div>
                    <div class="col-md-3"><strong>من تاريخ:</strong> {{ $batch->since?->format('Y-m-d') ?? '—' }}</div>
                    <div class="col-md-3"><strong>أنشأها:</strong> {{ $batch->creator?->name ?? '—' }}</div>
                    <div class="col-md-3"><strong>انتهاء:</strong> {{ $batch->finished_at?->format('Y-m-d H:i') ?? '—' }}</div>
                </div>
            </div>
        </div>

        @if($itemsByGroup->isEmpty())
            <div class="alert alert-info">لا توجد عناصر في هذه الدفعة.</div>
        @endif
        @foreach($itemsByGroup as $groupId => $items)
            @php $groupName = $items->first()?->courseGroup?->name ?? ('مجموعة #'.$groupId); @endphp
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header">{{ $groupName }}</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>الطالب</th>
                                    <th>الحالة</th>
                                    <th>تقرير</th>
                                    <th>الجمل / الفقرات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                    <tr>
                                        <td>{{ $item->student?->name ?? '—' }}<br><span class="text-muted small">{{ $item->student?->email }}</span></td>
                                        <td>
                                            @if($item->status === 'succeeded')
                                                <span class="badge bg-success">نجاح</span>
                                            @elseif($item->status === 'failed')
                                                <span class="badge bg-danger">فشل</span>
                                            @elseif($item->status === 'skipped')
                                                <span class="badge bg-secondary">تخطٍ</span>
                                            @else
                                                <span class="badge bg-info text-dark">{{ $item->status }}</span>
                                            @endif
                                            @if($item->error_message)
                                                <div class="text-danger small mt-1">{{ \Illuminate\Support\Str::limit($item->error_message, 200) }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->report)
                                                <a href="{{ route('admin.ai.student-progress-reports.show', $item->report) }}" class="btn btn-sm btn-outline-primary">عرض التقرير</a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="small" style="max-width: 28rem;">
                                            @if(!empty($item->narrative_segments) && is_array($item->narrative_segments))
                                                <details>
                                                    <summary class="text-primary" style="cursor:pointer;">عرض {{ count($item->narrative_segments) }} جزءاً</summary>
                                                    <ol class="mb-0 mt-2 ps-3">
                                                        @foreach($item->narrative_segments as $seg)
                                                            <li class="mb-1">{{ $seg }}</li>
                                                        @endforeach
                                                    </ol>
                                                </details>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@stop
