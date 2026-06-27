@extends('admin.layouts.master')

@section('page-title')
    تعديل تحدي مشروع
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
                        <li class="breadcrumb-item active">تعديل</li>
                    </ol>
                </nav>
            </div>

            <div class="pc-form-hero dashboard-fade-in">
                <div class="pc-form-hero__inner d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                            <i class="fe fe-edit fa-lg"></i>
                            <span class="pc-form-hero__badge">تعديل التحدي</span>
                        </div>
                        <h1 class="pc-form-hero__title">{{ $challenge->title }}</h1>
                        <p class="pc-form-hero__desc">عدّل معلومات التحدي، الإعدادات، والمهارات المرتبطة.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.project-challenges.manage-stages', $challenge->id) }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                            <i class="fe fe-layers me-1"></i> المراحل
                        </a>
                        <a href="{{ route('admin.project-challenges.manage-teams', $challenge->id) }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                            <i class="fe fe-users me-1"></i> الفرق
                        </a>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm rounded-3">
                    <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('admin.project-challenges.update', $challenge->id) }}" method="POST" id="projectChallengeForm">
                @csrf @method('PUT')
                @include('admin.pages.project-challenges._form-fields', ['challenge' => $challenge])

                <div class="pc-form-sticky">
                    <span class="text-muted small"><i class="fe fe-save me-1"></i> احفظ التغييرات قبل مغادرة الصفحة</span>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.project-challenges.index') }}" class="btn btn-light">إلغاء</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fe fe-save me-1"></i> حفظ التغييرات
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
