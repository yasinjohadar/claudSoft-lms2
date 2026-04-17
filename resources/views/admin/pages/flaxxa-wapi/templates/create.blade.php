@extends('admin.layouts.master')

@section('page-title')
    إضافة قالب Flaxxa
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        @include('admin.components.alerts')
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">قالب جديد</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.flaxxa-wapi.templates.index') }}">قوالب Flaxxa</a></li>
                        <li class="breadcrumb-item active">إضافة</li>
                    </ol>
                </nav>
            </div>
        </div>
        @include('admin.pages.flaxxa-wapi._nav')
        <div class="card custom-card">
            <div class="card-body">
                <form action="{{ route('admin.flaxxa-wapi.templates.store') }}" method="POST">
                    @csrf
                    @include('admin.pages.flaxxa-wapi.templates._form', ['template' => $template, 'headerPlaceholders' => $headerPlaceholders, 'bodyPlaceholders' => $bodyPlaceholders, 'previewTemplate' => $previewTemplate])
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">حفظ</button>
                        <a href="{{ route('admin.flaxxa-wapi.templates.index') }}" class="btn btn-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
