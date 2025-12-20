@extends('student.layouts.master')

@section('page-title', 'إعدادات الإشعارات')

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h4 class="page-title fw-semibold fs-18 mb-0">إعدادات الإشعارات</h4>
            <p class="fw-normal text-muted fs-14 mb-0">تحكم في الإشعارات الداخلية والبريد الإلكتروني</p>
        </div>
        <div class="ms-md-auto d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('student.dashboard') }}" class="btn btn-secondary btn-wave">
                <i class="ri-arrow-right-line me-1"></i> رجوع
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ri-check-line me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('student.settings.notifications.update') }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="ri-notification-3-line me-2"></i>تفضيلات الإشعارات
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info border-0">
                            <div class="d-flex align-items-center">
                                <i class="ri-information-line fs-18 me-2"></i>
                                <div>
                                    <strong>ملاحظة:</strong>
                                    <p class="mb-0">يمكنك التحكم في الإشعارات التي تصلك داخل النظام وعبر البريد الإلكتروني. قم بتفعيل أو إلغاء تفعيل كل نوع حسب رغبتك.</p>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 40%;">نوع الإشعار</th>
                                        <th class="text-center" style="width: 30%;">
                                            <i class="ri-notification-3-line me-1"></i>
                                            إشعار داخلي
                                        </th>
                                        <th class="text-center" style="width: 30%;">
                                            <i class="ri-mail-line me-1"></i>
                                            بريد إلكتروني
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($notificationTypes as $key => $type)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="fs-20 me-3">{{ $type['icon'] }}</span>
                                                    <div>
                                                        <strong>{{ $type['name'] }}</strong>
                                                        <p class="text-muted fs-12 mb-0">{{ $type['description'] }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        name="notification_preferences[{{ $key }}]"
                                                        id="notification_{{ $key }}"
                                                        value="1"
                                                        {{ (auth()->user()->notification_preferences[$key] ?? true) ? 'checked' : '' }}
                                                    >
                                                    <label class="form-check-label" for="notification_{{ $key }}"></label>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        name="email_preferences[{{ $key }}]"
                                                        id="email_{{ $key }}"
                                                        value="1"
                                                        {{ (auth()->user()->email_preferences[$key] ?? false) ? 'checked' : '' }}
                                                    >
                                                    <label class="form-check-label" for="email_{{ $key }}"></label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card bg-primary-transparent border-0">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-lg bg-primary me-3">
                                                    <i class="ri-notification-3-line fs-20"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 fw-semibold">الإشعارات الداخلية</p>
                                                    <p class="text-muted fs-12 mb-0">تظهر في جرس الإشعارات داخل النظام</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-success-transparent border-0">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-lg bg-success me-3">
                                                    <i class="ri-mail-line fs-20"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 fw-semibold">البريد الإلكتروني</p>
                                                    <p class="text-muted fs-12 mb-0">تصلك على بريدك: {{ auth()->user()->email }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning border-0 mt-3">
                            <div class="d-flex align-items-start">
                                <i class="ri-lightbulb-line fs-18 me-2 mt-1"></i>
                                <div>
                                    <strong>نصيحة:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>يُنصح بتفعيل الإشعارات الداخلية لجميع الأنواع للبقاء على اطلاع مستمر</li>
                                        <li>فعّل البريد الإلكتروني للإشعارات المهمة فقط مثل: الشارات، الإنجازات، وترقية المستوى</li>
                                        <li>يمكنك تغيير إعداداتك في أي وقت</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-1"></i> حفظ الإعدادات
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="ri-flashlight-line me-2"></i>إجراءات سريعة
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <button type="button" class="btn btn-outline-primary w-100" onclick="enableAll('notification')">
                                    <i class="ri-checkbox-circle-line me-1"></i>
                                    تفعيل جميع الإشعارات الداخلية
                                </button>
                            </div>
                            <div class="col-md-4 mb-3">
                                <button type="button" class="btn btn-outline-success w-100" onclick="enableAll('email')">
                                    <i class="ri-checkbox-circle-line me-1"></i>
                                    تفعيل جميع رسائل البريد
                                </button>
                            </div>
                            <div class="col-md-4 mb-3">
                                <button type="button" class="btn btn-outline-danger w-100" onclick="disableAll()">
                                    <i class="ri-close-circle-line me-1"></i>
                                    إلغاء تفعيل الكل
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
</div>
<!-- End::app-content -->

@push('scripts')
<script>
function enableAll(type) {
    const checkboxes = document.querySelectorAll(`input[name^="${type}_preferences"]`);
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
}

function disableAll() {
    const allCheckboxes = document.querySelectorAll('input[type="checkbox"]');
    allCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
}
</script>
@endpush
@endsection
