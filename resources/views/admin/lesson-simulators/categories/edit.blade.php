@extends('admin.layouts.master')

@section('page-title')
    تعديل تصنيف محاكاة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <h5 class="page-title fs-21 mb-0">تعديل: {{ $category->name }}</h5>
            <a href="{{ route('admin.lesson-simulators.categories.index') }}" class="btn btn-secondary btn-sm">رجوع</a>
        </div>

        @include('admin.lesson-simulators.categories.partials.form', [
            'action' => route('admin.lesson-simulators.categories.update', $category),
            'method' => 'PUT',
            'category' => $category,
            'parentOptions' => $parentOptions,
        ])
    </div>
</div>
@endsection
