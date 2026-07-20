@extends('admin.layouts.master')

@section('page-title')
    تعديل مكتبة Bunny
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تعديل: {{ $library->library_name }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('bunny-stream-libraries.index') }}">مكتبات Bunny</a></li>
                            <li class="breadcrumb-item active">تعديل</li>
                        </ol>
                    </nav>
                </div>
            </div>

            @include('admin.components.alerts')

            <div class="row">
                <div class="col-xl-8">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">بيانات المكتبة</div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('bunny-stream-libraries.update', $library) }}" method="POST">
                                @csrf
                                @method('PUT')
                                @include('admin.pages.bunny-stream-libraries.partials.form', ['library' => $library])
                                <div class="d-flex gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>حفظ التعديلات
                                    </button>
                                    <a href="{{ route('bunny-stream-libraries.index') }}" class="btn btn-light">إلغاء</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop
