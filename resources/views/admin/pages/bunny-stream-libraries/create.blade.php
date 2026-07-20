@extends('admin.layouts.master')

@section('page-title')
    إضافة مكتبة Bunny
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إضافة مكتبة Bunny</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('bunny-stream-libraries.index') }}">مكتبات Bunny</a></li>
                            <li class="breadcrumb-item active">إضافة</li>
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
                            <form action="{{ route('bunny-stream-libraries.store') }}" method="POST">
                                @csrf
                                @include('admin.pages.bunny-stream-libraries.partials.form')
                                <div class="d-flex gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>حفظ المكتبة
                                    </button>
                                    <a href="{{ route('bunny-stream-libraries.index') }}" class="btn btn-light">إلغاء</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">من أين أحصل على البيانات؟</div>
                        </div>
                        <div class="card-body">
                            <ol class="mb-0 ps-3">
                                <li class="mb-2">افتح Bunny Stream → اختر المكتبة (Collection)</li>
                                <li class="mb-2">اذهب إلى <strong>Security → General</strong></li>
                                <li class="mb-2">انسخ <strong>Library ID</strong> من رابط الفيديو أو من إعدادات المكتبة</li>
                                <li class="mb-2">انسخ <strong>Token authentication key</strong></li>
                                <li>فعّل <strong>Embed view token authentication</strong> و DRM كما هو عندكم</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop
