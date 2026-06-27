@extends('admin.layouts.master')

@section('page-title')
    إنشاء تحدي مشروع
@stop

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/project-challenge.css') }}?v={{ filemtime(public_path('assets/css/project-challenge.css')) }}">
@endpush

@section('content')
    <div class="main-content app-content pc-page">
        <div class="container-fluid">
            @include('admin.components.alerts')

            <div class="my-3 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.project-challenges.index') }}">تحديات المشاريع</a></li>
                        <li class="breadcrumb-item active">إنشاء</li>
                    </ol>
                </nav>
            </div>

            <div class="pc-form-hero dashboard-fade-in">
                <div class="pc-form-hero__inner d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                            <i class="fe fe-layers fa-lg"></i>
                            <span class="pc-form-hero__badge">Project Challenge</span>
                        </div>
                        <h1 class="pc-form-hero__title">إنشاء تحدي مشروع جديد</h1>
                        <p class="pc-form-hero__desc">
                            عرّف التحدي، حدّد الفرق والجدول الزمني، ثم انتقل لإعداد المراحل بعد الحفظ.
                        </p>
                    </div>
                    <a href="{{ route('admin.project-challenges.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="fe fe-arrow-right me-1"></i> العودة للقائمة
                    </a>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm rounded-3">
                    <div class="d-flex align-items-center gap-2 mb-2 fw-bold">
                        <i class="fe fe-alert-circle"></i> يرجى تصحيح الأخطاء التالية
                    </div>
                    <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('admin.project-challenges.store') }}" method="POST" id="projectChallengeForm">
                @csrf
                @include('admin.pages.project-challenges._form-fields')

                <div class="pc-form-sticky">
                    <span class="text-muted small">
                        <i class="fe fe-info me-1"></i>
                        بعد الإنشاء ستنتقل لإعداد مراحل التحدي
                    </span>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.project-challenges.index') }}" class="btn btn-light">إلغاء</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fe fe-save me-1"></i> إنشاء والمتابعة للمراحل
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('scripts')
    @include('admin.blog.partials.tinymce-config', [
        'formSelector' => '#projectChallengeForm',
        'editors' => [
            ['selector' => '#project_challenge_summary', 'height' => 280],
            ['selector' => '#project_challenge_description', 'height' => 520],
        ],
    ])
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-switch-row]').forEach(function (row) {
            var cb = row.querySelector('input[type="checkbox"]');
            if (!cb) return;
            cb.addEventListener('change', function () {
                row.classList.toggle('on', cb.checked);
            });
        });
    });
    </script>
@stop
