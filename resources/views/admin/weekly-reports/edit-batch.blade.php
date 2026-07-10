@extends('admin.layouts.master')

@section('page-title', 'تعديل دفعة التقرير')

@section('css')
<style>
    .weekly-report-editor-wrap .tox-tinymce {
        border-radius: 0.375rem;
    }
</style>
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.weekly-reports.created') }}">التقارير المنشأة</a></li>
                    <li class="breadcrumb-item active" aria-current="page">تعديل التقرير</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-edit-2 me-1"></i>
                        تعديل دفعة تقرير
                    </span>
                    <h2 class="group-show-hero__title mb-2">{{ $batch['report_title'] }}</h2>
                    <p class="group-show-hero__desc mb-0">
                        التعديلات تُطبَّق على جميع تقارير الطلاب في هذه الدفعة ({{ $batch['students_count'] }} طالب).
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions group-show-actions--single">
                        <a href="{{ route('admin.weekly-reports.created.batch', ['batch' => $batchKey]) }}"
                           class="group-show-action group-show-action--info">
                            <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                            <span class="group-show-action__text">عودة لتفاصيل الدفعة</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card custom-card group-show-members-card dashboard-fade-in h-100">
                    <div class="card-body">
                        <p class="text-muted fs-12 mb-1">الكورس</p>
                        <p class="fw-semibold mb-0">{{ $batch['target_course']?->title ?? '—' }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card custom-card group-show-members-card dashboard-fade-in h-100">
                    <div class="card-body">
                        <p class="text-muted fs-12 mb-1">المجموعة</p>
                        <p class="fw-semibold mb-0">{{ $batch['target_group']?->name ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in">
            <div class="card-header border-0 pb-0">
                <h4 class="card-title mb-1">بيانات التقرير</h4>
                <p class="fs-12 text-muted mb-0">عدّل العنوان والوصف والموعد النهائي. لا يمكن تغيير الكورس أو المجموعة بعد الإنشاء.</p>
            </div>
            <div class="card-body pt-3">
                <form method="POST" action="{{ route('admin.weekly-reports.created.batch.update') }}" id="weekly-report-edit-batch-form" class="group-show-filters mb-0">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="batch" value="{{ $batchKey }}">

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label" for="report_title">عنوان التقرير <span class="text-danger">*</span></label>
                            <input
                                class="form-control @error('report_title') is-invalid @enderror"
                                type="text"
                                name="report_title"
                                id="report_title"
                                value="{{ old('report_title', $batch['report_title']) }}"
                                required
                            >
                            @error('report_title')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="due_at">الموعد النهائي</label>
                            <input
                                class="form-control @error('due_at') is-invalid @enderror"
                                type="datetime-local"
                                name="due_at"
                                id="due_at"
                                value="{{ old('due_at', $batch['due_at']?->format('Y-m-d\TH:i')) }}"
                            >
                            @error('due_at')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="report_description">وصف التقرير / المطلوب من الطالب</label>
                            <div class="weekly-report-editor-wrap">
                                <textarea
                                    class="form-control @error('report_description') is-invalid @enderror"
                                    name="report_description"
                                    id="report_description"
                                    rows="8"
                                >{{ old('report_description', $batch['report_description'] ?? '') }}</textarea>
                            </div>
                            <div class="form-text">يظهر للطالب عند فتح التقرير. يمكنك استخدام القوائم والتنسيق مثل صفحة إنشاء التدوينة.</div>
                            @error('report_description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4 pt-2 border-top">
                        <button type="submit" class="btn btn-primary">
                            <i class="fe fe-save me-1"></i>حفظ التعديلات
                        </button>
                        <a href="{{ route('admin.weekly-reports.created.batch', ['batch' => $batchKey]) }}" class="btn btn-light">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@include('admin.blog.partials.tinymce-config', [
    'editors' => [['selector' => '#report_description', 'height' => 420]],
    'formSelector' => '#weekly-report-edit-batch-form',
])
@endpush
