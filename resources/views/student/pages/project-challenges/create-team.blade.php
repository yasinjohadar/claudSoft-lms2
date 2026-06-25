@extends('student.layouts.master')

@section('page-title')
    إنشاء فريق — {{ $challenge->title }}
@stop

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/project-challenge.css') }}">
@endpush

@section('content')
    <div class="main-content app-content pc-page">
        <div class="container-fluid">
            @include('student.components.alerts')

            <div class="mb-3">
                <a href="{{ route('student.project-challenges.show', $challenge->id) }}" class="text-muted">
                    <i class="fe fe-arrow-right me-1"></i>العودة للتحدي
                </a>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">إنشاء فريق جديد</div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-4">التحدي: <strong>{{ $challenge->title }}</strong></p>

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                                </div>
                            @endif

                            <form action="{{ route('student.project-challenges.teams.store', $challenge->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">اسم الفريق <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required maxlength="255">
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">وصف الفريق</label>
                                    <textarea name="description" class="form-control" rows="4" maxlength="2000">{{ old('description') }}</textarea>
                                    <small class="text-muted">صف فكرة مشروعكم وأهداف الفريق</small>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('student.project-challenges.show', $challenge->id) }}" class="btn btn-light">إلغاء</a>
                                    <button type="submit" class="btn btn-primary flex-fill">
                                        <i class="fe fe-users me-1"></i>إنشاء الفريق
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
