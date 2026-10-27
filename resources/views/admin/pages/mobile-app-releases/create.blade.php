@extends('admin.layouts.master')

@section('page-title')
    نشر إصدار جديد
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">نشر إصدار جديد</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.mobile-app-releases.index') }}">تحديثات تطبيق الجوال</a></li>
                            <li class="breadcrumb-item active" aria-current="page">نشر إصدار</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">بيانات الإصدار</div>
                        </div>
                        <form action="{{ route('admin.mobile-app-releases.store') }}" method="POST">
                            @csrf
                            @include('admin.pages.mobile-app-releases._form')
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary btn-wave"><i class="fas fa-rocket me-2"></i>نشر الإصدار</button>
                                <a href="{{ route('admin.mobile-app-releases.index') }}" class="btn btn-secondary btn-wave"><i class="fas fa-times me-2"></i>إلغاء</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
