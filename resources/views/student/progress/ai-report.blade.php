@extends('student.layouts.master')

@section('page-title')
    تقرير الدراسة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تقرير الدراسة — {{ $report->course?->title }}</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('student.study-reports.index') }}">تقارير الدراسة</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('student.progress.ai-reports.index', $report->course) }}">{{ $report->course?->title ?? 'الكورس' }}</a></li>
                        <li class="breadcrumb-item active">تقرير #{{ $report->id }}</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="alert alert-info">
            النتائج الرقمية مستمدة من سجلات المنصة؛ النص التفسيري مولَّد آلياً وقد يحتاج مراجعة من المدرّس وليس قراراً أكاديمياً نهائياً.
        </div>

        <div class="card custom-card mb-3">
            <div class="card-body">
                <p class="text-muted small mb-2">تاريخ الإصدار: {{ $report->created_at?->format('Y-m-d H:i') }}</p>
                <div class="report-body" style="white-space: pre-wrap;">{{ $report->narrative }}</div>
            </div>
        </div>
    </div>
</div>
@stop
