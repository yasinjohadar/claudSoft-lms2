@extends('admin.layouts.master')

@section('page-title', 'عرض تقرير أسبوعي')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="my-4">
            <h5>{{ $report->report_title }}</h5>
            <p class="text-muted mb-0">الطالب: {{ $report->student->name_ar ?? $report->student->name }}</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card custom-card mb-3">
            <div class="card-body">
                <h6>تفاصيل الطالب</h6>
                <p>{{ $report->student_details ?: 'لا يوجد' }}</p>
                <h6>ملاحظات الطالب</h6>
                <p>{{ $report->student_notes ?: 'لا يوجد' }}</p>
                <h6>الدروس المختارة</h6>
                <ul class="mb-0">
                    @forelse($report->selectedLessons as $item)
                        <li>{{ $item->course->title ?? '-' }} - {{ $item->module->title ?? $item->lesson->title ?? '-' }}</li>
                    @empty
                        <li>لا توجد دروس محددة.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="card custom-card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.weekly-reports.feedback', $report) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">تعليق الأدمن النهائي</label>
                        <textarea class="form-control" name="admin_feedback" rows="5" required>{{ old('admin_feedback', $report->admin_feedback) }}</textarea>
                    </div>
                    <button class="btn btn-primary">حفظ التعليق</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

