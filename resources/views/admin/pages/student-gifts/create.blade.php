@extends('admin.layouts.master')

@section('page-title')
    إنشاء هدية
@stop

@section('styles')
    @include('admin.pages.student-gifts.partials.select2-styles')
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li class="small">{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.gifts.index') }}">هدايا الطلاب</a></li>
                        <li class="breadcrumb-item active">إنشاء هدية</li>
                    </ol>
                </nav>
            </div>

            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <h5 class="page-title mb-0">إنشاء هدية جديدة</h5>
                <a href="{{ route('admin.gifts.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fe fe-arrow-right me-1"></i>رجوع للقائمة
                </a>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-1">بيانات الهدية</h4>
                    <p class="fs-12 text-muted mb-0">أكمل المعلومات والمحتوى والاستهداف ثم احفظ كمسودة.</p>
                </div>
                <div class="card-body pt-3">
                    <form method="POST" action="{{ route('admin.gifts.store') }}" enctype="multipart/form-data" id="gift-form">
                        @csrf
                        @include('admin.pages.student-gifts.partials.form-fields', ['gift' => null])
                        <div class="d-flex flex-wrap gap-2 mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary">
                                <i class="fe fe-save me-1"></i>حفظ كمسودة
                            </button>
                            <a href="{{ route('admin.gifts.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@include('admin.pages.student-gifts.partials.form-scripts', ['gift' => null])
