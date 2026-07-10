@extends('admin.layouts.master')

@section('page-title', 'عرض تقرير أسبوعي')

@section('content')
@php
    $statusLabels = [
        'draft' => ['label' => 'مسودة', 'class' => 'bg-secondary-transparent text-secondary'],
        'submitted' => ['label' => 'مرسل', 'class' => 'bg-primary-transparent text-primary'],
        'reviewed' => ['label' => 'مراجع', 'class' => 'bg-info-transparent text-info'],
        'closed' => ['label' => 'مغلق', 'class' => 'bg-warning-transparent text-warning'],
    ];
    $status = $statusLabels[$report->status] ?? ['label' => $report->status, 'class' => 'bg-secondary-transparent text-secondary'];
@endphp
<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.weekly-reports.all') }}">كافة التقارير</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($report->report_title, 40) }}</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-file-text me-1"></i>
                        تقرير أسبوعي
                    </span>
                    <h2 class="group-show-hero__title mb-2">{{ $report->report_title }}</h2>
                    <p class="group-show-hero__desc mb-2">
                        الطالب:
                        <strong>{{ $report->student->name_ar ?? $report->student->name ?? '-' }}</strong>
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge {{ $status['class'] }}">{{ $status['label'] }}</span>
                        @if($report->submitted_at)
                            <span class="badge bg-light text-muted">
                                <i class="fe fe-clock me-1"></i>
                                {{ $report->submitted_at->format('Y-m-d H:i') }}
                            </span>
                        @endif
                        @if($report->due_at)
                            <span class="badge bg-light text-muted">
                                <i class="fe fe-flag me-1"></i>
                                الموعد: {{ $report->due_at->format('Y-m-d H:i') }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions group-show-actions--single">
                        <a href="{{ route('admin.weekly-reports.all') }}"
                           class="group-show-action group-show-action--info">
                            <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                            <span class="group-show-action__text">عودة لكافة التقارير</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @include('admin.weekly-reports.partials.report-meta-cards', ['report' => $report])

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card custom-card group-show-members-card dashboard-fade-in h-100">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-1">محتوى التقرير</h4>
                        <p class="fs-12 text-muted mb-0">تفاصيل الطالب والدروس المختارة.</p>
                    </div>
                    <div class="card-body pt-3">
                        <div class="mb-4">
                            <h6 class="text-muted fs-12 text-uppercase mb-2">تفاصيل الطالب</h6>
                            <div class="p-3 rounded border bg-light weekly-report-html-content">
                                @if($report->student_details)
                                    {!! $report->student_details !!}
                                @else
                                    <span class="text-muted">لا يوجد</span>
                                @endif
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-muted fs-12 text-uppercase mb-2">ملاحظات الطالب</h6>
                            <div class="p-3 rounded border bg-light">
                                {{ $report->student_notes ?: 'لا يوجد' }}
                            </div>
                        </div>

                        <div>
                            <h6 class="text-muted fs-12 text-uppercase mb-2">الدروس المختارة</h6>
                            @include('admin.weekly-reports.partials.selected-lessons-grouped', ['selectedLessonGroups' => $selectedLessonGroups ?? collect()])
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card custom-card group-show-members-card dashboard-fade-in h-100">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-1">تعليق الإدارة</h4>
                        <p class="fs-12 text-muted mb-0">أضف أو عدّل الملاحظات النهائية على التقرير.</p>
                    </div>
                    <div class="card-body pt-3">
                        <form method="POST" action="{{ route('admin.weekly-reports.feedback', $report) }}">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label">تعليق الأدمن النهائي</label>
                                <textarea class="form-control @error('admin_feedback') is-invalid @enderror" name="admin_feedback" rows="8" required>{{ old('admin_feedback', $report->admin_feedback) }}</textarea>
                                @error('admin_feedback')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fe fe-save me-1"></i>حفظ التعليق
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
