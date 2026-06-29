@extends('admin.layouts.master')

@section('page-title')
    إعدادات أمان الأجهزة
@stop

@section('styles')
    @include('admin.user-devices.partials.page-styles')
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb ud-page-animate dashboard-fade-in">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.user-devices.index') }}">أجهزة المستخدمين</a></li>
                    <li class="breadcrumb-item active">إعدادات الأمان</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in ud-page-animate mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow"><i class="fe fe-shield me-1"></i>أمان الأجهزة</span>
                    <h2 class="group-show-hero__title mb-2">الأجهزة الموثوقة فقط</h2>
                    <p class="group-show-hero__desc mb-0">
                        عند التفعيل، يُسمح بتسجيل الدخول فقط من الأجهزة الموثوقة. الأجهزة الجديدة تُسجَّل وتنتظر موافقة الإدارة.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions">
                        <a href="{{ route('admin.user-devices.index') }}" class="group-show-action">
                            <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                            <span class="group-show-action__text">قائمة الأجهزة</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-10">
                <div class="card custom-card group-show-members-card dashboard-fade-in ud-page-animate">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-1">إعدادات النظام</h4>
                        <p class="fs-12 text-muted mb-0">يمكن تجاوز هذه الإعدادات لكل مستخدم من صفحة تعديل المستخدم.</p>
                    </div>
                    <div class="card-body pt-3">
                        <form method="POST" action="{{ route('admin.user-devices.security-settings.update') }}">
                            @csrf

                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="trusted_devices_only_enabled" name="trusted_devices_only_enabled" value="1"
                                       @checked(old('trusted_devices_only_enabled', $settings['trusted_devices_only_enabled']))>
                                <label class="form-check-label fw-semibold" for="trusted_devices_only_enabled">
                                    تفعيل الدخول من الأجهزة الموثوقة فقط
                                </label>
                                <p class="text-muted fs-12 mb-0 mt-1">
                                    يرفض تسجيل الدخول من أي جهاز غير موثوق ويسجّله كـ «بانتظار الموافقة».
                                </p>
                            </div>

                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="auto_trust_first_device" name="auto_trust_first_device" value="1"
                                       @checked(old('auto_trust_first_device', $settings['auto_trust_first_device']))>
                                <label class="form-check-label fw-semibold" for="auto_trust_first_device">
                                    توثيق أول جهاز تلقائياً
                                </label>
                                <p class="text-muted fs-12 mb-0 mt-1">
                                    إذا لم يكن للمستخدم أي جهاز موثوق، يُوثَّق أول جهاز يسجّل الدخول منه تلقائياً.
                                </p>
                            </div>

                            <div class="alert alert-info mb-4">
                                <i class="fe fe-info me-2"></i>
                                لاعتماد جهاز جديد: افتح تفاصيل الجهاز واضغط «تعيين كموثوق»، أو استخدم فلتر «بانتظار الموافقة» في قائمة الأجهزة.
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fe fe-save me-1"></i>حفظ الإعدادات
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
