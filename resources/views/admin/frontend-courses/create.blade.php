@extends('admin.layouts.master')

@section('page-title')
إضافة كورس جديد
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">إضافة كورس جديد</h4>
                <p class="mb-0 text-muted">إضافة كورس جديد للواجهة الأمامية</p>
            </div>
            <div class="ms-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.frontend-courses.index') }}">الكورسات</a></li>
                        <li class="breadcrumb-item active" aria-current="page">إضافة كورس</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- Page Header Close -->

        <!-- Error Messages -->
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>يوجد أخطاء في النموذج:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Form -->
        <form action="{{ route('admin.frontend-courses.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('admin.frontend-courses._form')
        </form>

    </div>
</div>
<!-- End::app-content -->
@endsection
