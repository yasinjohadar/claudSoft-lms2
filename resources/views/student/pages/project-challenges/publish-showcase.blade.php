@extends('student.layouts.master')

@section('page-title')
    نشر العرض — {{ $team->name }}
@stop

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/project-challenge.css') }}">
@endpush

@section('content')
    <div class="main-content app-content pc-page">
        <div class="container-fluid">
            @include('student.components.alerts')

            <div class="mb-3">
                <a href="{{ route('student.project-teams.workspace', $team->id) }}" class="text-muted">
                    <i class="fe fe-arrow-right me-1"></i>العودة لمساحة العمل
                </a>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">نشر مشروعكم في المعرض</div>
                        </div>
                        <div class="card-body">
                            @unless($canPublish)
                                <div class="alert alert-warning">
                                    <i class="fe fe-alert-triangle me-1"></i>
                                    لم يستوفِ فريقكم شروط النشر بعد. أكملوا المراحل الإلزامية وحققوا نسبة التقدم المطلوبة ({{ $team->challenge->showcase_threshold }}%).
                                </div>
                            @endunless

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                                </div>
                            @endif

                            <form action="{{ route('student.project-teams.showcase.publish', $team->id) }}" method="POST" @unless($canPublish) class="opacity-50" @endunless>
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">عنوان العرض <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" required maxlength="255"
                                           value="{{ old('title', $team->showcase->title ?? $team->name) }}" @disabled(!$canPublish)>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">الملخص</label>
                                    <textarea name="summary" class="form-control" rows="4" maxlength="2000" @disabled(!$canPublish)>{{ old('summary', $team->showcase->summary ?? $team->description) }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">رابط GitHub</label>
                                    <input type="url" name="github_url" class="form-control" placeholder="https://github.com/..."
                                           value="{{ old('github_url', $team->showcase->github_url ?? '') }}" @disabled(!$canPublish)>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">رابط العرض التجريبي</label>
                                    <input type="url" name="demo_url" class="form-control" placeholder="https://"
                                           value="{{ old('demo_url', $team->showcase->demo_url ?? '') }}" @disabled(!$canPublish)>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">رابط الفيديو</label>
                                    <input type="url" name="video_url" class="form-control" placeholder="https://youtube.com/..."
                                           value="{{ old('video_url', $team->showcase->video_url ?? '') }}" @disabled(!$canPublish)>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">صورة الغلاف (رابط)</label>
                                    <input type="text" name="cover_image" class="form-control" placeholder="https://..."
                                           value="{{ old('cover_image', $team->showcase->cover_image ?? '') }}" @disabled(!$canPublish)>
                                </div>
                                <button type="submit" class="btn btn-warning w-100" @disabled(!$canPublish)>
                                    <i class="fe fe-upload me-1"></i>نشر في معرض المشاريع
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
