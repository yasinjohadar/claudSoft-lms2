@extends('admin.layouts.master')

@section('page-title')
    إنشاء حملة Flaxxa
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        @include('admin.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">حملة (Create_Campaign)</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.flaxxa-wapi.messages.index') }}">Flaxxa</a></li>
                        <li class="breadcrumb-item active">حملة</li>
                    </ol>
                </nav>
            </div>
        </div>

        @include('admin.pages.flaxxa-wapi._nav')

        <div class="card custom-card">
            <div class="card-body">
                <form action="{{ route('admin.flaxxa-wapi.send.campaign.store') }}" method="POST" class="row g-3">
                    @csrf
                    <div class="col-12">
                        <label class="form-label">اسم الحملة <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">معرّف القالب لدى المزود (template_id) <span class="text-danger">*</span></label>
                        <input type="text" name="template_id" class="form-control @error('template_id') is-invalid @enderror" value="{{ old('template_id') }}" required>
                        @error('template_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">معرّف المجموعة (group_id) <span class="text-danger">*</span></label>
                        <input type="text" name="group_id" class="form-control @error('group_id') is-invalid @enderror" value="{{ old('group_id') }}" required>
                        @error('group_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">قالب محفوظ للتحقق من عدد المتغيرات (اختياري)</label>
                        <select name="wapi_template_id" class="form-select">
                            <option value="">— بدون —</option>
                            @foreach($templates as $t)
                                <option value="{{ $t->id }}" @selected(old('wapi_template_id') == $t->id)>{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">متغيرات الرأس (سطر لكل متغير)</label>
                        <textarea name="header_variables_text" rows="3" class="form-control @error('header_variables_text') is-invalid @enderror">{{ old('header_variables_text') }}</textarea>
                        @error('header_variables_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">متغيرات النص (سطر لكل متغير)</label>
                        <textarea name="body_variables_text" rows="4" class="form-control @error('body_variables_text') is-invalid @enderror">{{ old('body_variables_text') }}</textarea>
                        @error('body_variables_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary"><i class="ri-mail-send-line me-1"></i> جدولة الحملة</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
