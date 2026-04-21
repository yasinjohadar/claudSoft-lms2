@extends('admin.layouts.master')

@section('page-title')
    تعديل قاعدة أتمتة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        @include('admin.components.alerts')
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تعديل قاعدة #{{ $rule->id }}</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.flaxxa-wapi.automation.index') }}">أتمتة Flaxxa</a></li>
                        <li class="breadcrumb-item active">تعديل</li>
                    </ol>
                </nav>
            </div>
        </div>
        @include('admin.pages.flaxxa-wapi._nav')
        <div class="card custom-card mb-3">
            <div class="card-body">
                <h6 class="mb-3">إرسال تجريبي</h6>
                <form action="{{ route('admin.flaxxa-wapi.automation.test', $rule) }}" method="POST" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label">رقم (E.164 بدون + في العرض)</label>
                        <input type="text" name="test_phone" class="form-control" required placeholder="9665xxxxxxxx">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">طالب للمتغيرات (اختياري)</label>
                        <input type="number" name="test_student_id" class="form-control" placeholder="معرّف مستخدم">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-outline-primary w-100">جدولة اختبار</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card custom-card">
            <div class="card-body">
                <form action="{{ route('admin.flaxxa-wapi.automation.update', $rule) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('admin.pages.flaxxa-wapi.automation._form', ['rule' => $rule])
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">تحديث</button>
                        <a href="{{ route('admin.flaxxa-wapi.automation.index') }}" class="btn btn-secondary">رجوع</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
