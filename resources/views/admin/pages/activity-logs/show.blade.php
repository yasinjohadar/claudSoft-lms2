@extends('admin.layouts.master')

@section('page-title')
    تفاصيل السجل #{{ $activity->id }}
@stop

@section('content')
@php
    $context = $activity->properties['context'] ?? [];
    $properties = $activity->properties?->toArray() ?? [];
@endphp
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="my-4 page-header-breadcrumb">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.activity-logs.index') }}">سجل النشاط</a></li>
                    <li class="breadcrumb-item active">#{{ $activity->id }}</li>
                </ol>
            </nav>
        </div>

        <div class="card custom-card group-show-members-card mb-4">
            <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">{{ $activity->description }}</h4>
                    <p class="text-muted fs-12 mb-0">{{ $activity->created_at?->format('Y-m-d H:i:s') }}</p>
                </div>
                <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fe fe-arrow-right me-1"></i>رجوع
                </a>
            </div>
            <div class="card-body pt-3">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <small class="text-muted d-block">المستخدم</small>
                        <strong>{{ $activity->causer?->name ?? '—' }}</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">النوع</small>
                        <strong>{{ $logNameLabels[$activity->log_name] ?? $activity->log_name }}</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">الحدث</small>
                        <strong>{{ $eventLabels[$activity->event] ?? ($activity->event ?? '—') }}</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">الكيان</small>
                        <strong>{{ app(\App\Services\Admin\ActivityLogQueryService::class)->subjectLabel($activity) }}</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">IP</small>
                        <strong>{{ $context['ip'] ?? '—' }}</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">المسار</small>
                        <strong>{{ $context['route'] ?? '—' }}</strong>
                    </div>
                    @if(!empty($context['impersonator_id']))
                        <div class="col-md-4">
                            <small class="text-muted d-block">Impersonator ID</small>
                            <strong>{{ $context['impersonator_id'] }}</strong>
                        </div>
                    @endif
                </div>

                @if($diffRows !== [])
                    <h6 class="fw-bold mb-3">التغييرات (قبل → بعد)</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>الحقل</th>
                                    <th>قبل</th>
                                    <th>بعد</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($diffRows as $row)
                                    <tr>
                                        <td><code>{{ $row['field'] }}</code></td>
                                        <td class="text-muted">{{ is_scalar($row['old']) || $row['old'] === null ? ($row['old'] ?? '—') : json_encode($row['old'], JSON_UNESCAPED_UNICODE) }}</td>
                                        <td>{{ is_scalar($row['new']) || $row['new'] === null ? ($row['new'] ?? '—') : json_encode($row['new'], JSON_UNESCAPED_UNICODE) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">لا توجد تغييرات حقلية مسجّلة لهذا الحدث.</p>
                @endif

                @if(!empty($properties) && empty($diffRows))
                    <hr class="my-4">
                    <h6 class="fw-bold mb-2">بيانات إضافية</h6>
                    <pre class="bg-light p-3 rounded fs-12 mb-0" dir="ltr">{{ json_encode(collect($properties)->except(['attributes', 'old', 'context'])->all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
