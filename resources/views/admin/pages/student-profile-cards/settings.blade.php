@extends('admin.layouts.master')

@section('page-title')
    إعدادات البطاقة التعريفية
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb">
            <h5 class="page-title fs-21 mb-1">إعدادات البطاقة التعريفية</h5>
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.student-profile-cards.index') }}">البطاقات</a></li>
                    <li class="breadcrumb-item active">الإعدادات</li>
                </ol>
            </nav>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card custom-card">
                    <div class="card-header"><div class="card-title mb-0">تفعيل الميزة حسب فئة الحساب</div></div>
                    <div class="card-body">
                        <p class="text-muted fs-13 mb-4">
                            <strong>ذهبي</strong> = طالب لديه انضمام معتمد في معسكر تدريبي.
                            <strong>فضي</strong> = باقي الطلاب.
                        </p>
                        <form method="POST" action="{{ route('admin.student-profile-cards.settings.update') }}">
                            @csrf
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="profile_card_enabled_gold" value="1" id="goldEnabled" @checked($goldEnabled)>
                                <label class="form-check-label" for="goldEnabled">تفعيل البطاقة للحسابات الذهبية</label>
                            </div>
                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input" type="checkbox" name="profile_card_enabled_silver" value="1" id="silverEnabled" @checked($silverEnabled)>
                                <label class="form-check-label" for="silverEnabled">تفعيل البطاقة للحسابات الفضية</label>
                            </div>
                            <button type="submit" class="btn btn-primary">حفظ الإعدادات</button>
                            <a href="{{ route('admin.student-profile-cards.index') }}" class="btn btn-light border">رجوع</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
