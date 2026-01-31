@extends('admin.layouts.master')

@section('page-title')
    إضافة قالب رسالة واتساب
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إضافة قالب رسالة واتساب</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.whatsapp-messages.index') }}">رسائل WhatsApp</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.whatsapp-templates.index') }}">قوالب الرسائل</a></li>
                            <li class="breadcrumb-item active" aria-current="page">إضافة قالب</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">بيانات القالب</div>
                        </div>
                        <form action="{{ route('admin.whatsapp-templates.store') }}" method="POST">
                            @csrf
                            <div class="card-body">
                                @include('admin.pages.whatsapp-templates._form', ['template' => null])
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary btn-wave"><i class="fas fa-save me-2"></i>حفظ</button>
                                <a href="{{ route('admin.whatsapp-templates.index') }}" class="btn btn-secondary btn-wave"><i class="fas fa-times me-2"></i>إلغاء</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
