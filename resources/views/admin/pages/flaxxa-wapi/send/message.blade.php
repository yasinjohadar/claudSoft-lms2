@extends('admin.layouts.master')

@section('page-title')
    إرسال رسالة Flaxxa
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        @include('admin.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إرسال رسالة (نص / مرفق)</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.flaxxa-wapi.messages.index') }}">Flaxxa</a></li>
                        <li class="breadcrumb-item active">إرسال نص</li>
                    </ol>
                </nav>
            </div>
        </div>

        @include('admin.pages.flaxxa-wapi._nav')

        <div class="card custom-card">
            <div class="card-body">
                <form action="{{ route('admin.flaxxa-wapi.send.message.store') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label">رقم الهاتف <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="مثال: 966501234567" required>
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">مرفق (اختياري)</label>
                        <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror">
                        @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">نص الرسالة</label>
                        <textarea name="message" rows="4" class="form-control @error('message') is-invalid @enderror" placeholder="النص أو اتركه فارغاً إذا كان المرفق كافياً">{{ old('message') }}</textarea>
                        @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">header</label>
                        <input type="text" name="header" class="form-control" value="{{ old('header') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">footer</label>
                        <input type="text" name="footer" class="form-control" value="{{ old('footer') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">buttons</label>
                        <input type="text" name="buttons" class="form-control" value="{{ old('buttons') }}">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary"><i class="ri-send-plane-2-line me-1"></i> جدولة الإرسال</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
