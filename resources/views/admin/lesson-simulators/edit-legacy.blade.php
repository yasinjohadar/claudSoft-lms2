@extends('admin.layouts.master')

@section('page-title')
    تعديل محاكاة (JSON)
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <h5 class="page-title fs-21 mb-0">تعديل (JSON): {{ $lessonSimulator->title }}</h5>
            <a href="{{ route('admin.lesson-simulators.index') }}" class="btn btn-secondary btn-sm">رجوع</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @include('admin.lesson-simulators.partials.form', [
            'action' => route('admin.lesson-simulators.update', $lessonSimulator),
            'method' => 'PUT',
            'simulator' => $lessonSimulator,
            'topics' => $topics,
            'courses' => $courses,
            'statuses' => $statuses,
        ])
    </div>
</div>
@endsection
