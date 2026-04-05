@extends('admin.layouts.master')

@section('page-title')
    تقرير الدراسة #{{ $report->id }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تقرير الدراسة — {{ $report->student?->name }} / {{ $report->course?->title }}</h5>
            </div>
            <div>
                <a href="{{ route('admin.ai.student-progress-reports.index') }}" class="btn btn-secondary btn-sm">رجوع</a>
            </div>
        </div>

        <div class="alert alert-info">
            النتائج الرقمية من سجلات المنصة؛ النص التفسيري مولَّد آلياً وقد يحتاج مراجعة من المدرّس.
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header"><strong>النص التفسيري</strong></div>
            <div class="card-body">
                <div class="report-narrative" style="white-space: pre-wrap;">{{ $report->narrative }}</div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header"><strong>الحقائق المجمّعة (JSON)</strong></div>
            <div class="card-body">
                <pre class="small mb-0" style="max-height: 400px; overflow: auto;">{{ json_encode($report->facts, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
    </div>
</div>
@stop
