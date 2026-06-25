@extends('admin.layouts.master')

@section('page-title')
    تعديل تحدي مشروع
@stop

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/project-challenge.css') }}">
@endpush

@section('content')
    <div class="main-content app-content pc-page">
        <div class="container-fluid">
            @include('admin.components.alerts')

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تعديل: {{ $challenge->title }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.project-challenges.index') }}">تحديات المشاريع</a></li>
                            <li class="breadcrumb-item active">تعديل</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0 d-flex gap-2">
                    <a href="{{ route('admin.project-challenges.manage-stages', $challenge->id) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fe fe-layers me-1"></i>المراحل
                    </a>
                    <a href="{{ route('admin.project-challenges.manage-teams', $challenge->id) }}" class="btn btn-outline-info btn-sm">
                        <i class="fe fe-users me-1"></i>الفرق
                    </a>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('admin.project-challenges.update', $challenge->id) }}" method="POST" id="projectChallengeForm">
                @csrf @method('PUT')
                @include('admin.pages.project-challenges._form-fields', ['challenge' => $challenge])
                <div class="text-end mt-3">
                    <a href="{{ route('admin.project-challenges.index') }}" class="btn btn-light me-2">إلغاء</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fe fe-save me-1"></i>حفظ التغييرات
                    </button>
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
@stop
