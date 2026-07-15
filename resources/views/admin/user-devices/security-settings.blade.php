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

                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="single_session_enabled" name="single_session_enabled" value="1"
                                       @checked(old('single_session_enabled', $settings['single_session_enabled'] ?? false))>
                                <label class="form-check-label fw-semibold" for="single_session_enabled">
                                    جلسة واحدة نشطة فقط
                                </label>
                                <p class="text-muted fs-12 mb-0 mt-1">
                                    عند تسجيل الدخول من مكان جديد تُنهى الجلسات الأخرى تلقائياً لمنع الاستخدام المتزامن لنفس الحساب.
                                    يُطبَّق أيضاً على أعضاء المجموعات المقيّدة.
                                </p>
                            </div>

                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="bind_session_to_device_enabled" name="bind_session_to_device_enabled" value="1"
                                       @checked(old('bind_session_to_device_enabled', $settings['bind_session_to_device_enabled'] ?? false))>
                                <label class="form-check-label fw-semibold" for="bind_session_to_device_enabled">
                                    ربط الجلسة بالجهاز
                                </label>
                                <p class="text-muted fs-12 mb-0 mt-1">
                                    تربط جلسة تسجيل الدخول بالجهاز الذي دخل منه المستخدم. نقل كوكي الجلسة إلى متصفح/جهاز آخر
                                    بدون نفس هوية الجهاز يُنهي الجلسة تلقائياً. مستقل عن خيار الجلسة الواحدة.
                                </p>
                            </div>

                            @php
                                $restrictedGroupIds = collect(old(
                                    'restricted_group_ids',
                                    $courseGroups->where('device_lock_enabled', true)->pluck('id')->all()
                                ))->map(fn ($id) => (int) $id)->all();
                            @endphp
                            <div class="border rounded-3 p-3 mb-4">
                                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                                    <div>
                                        <label class="form-label fw-semibold mb-1">
                                            <i class="fe fe-users me-1 text-primary"></i>
                                            المجموعات المقيّدة
                                        </label>
                                        <p class="text-muted fs-12 mb-0">
                                            يُطبّق تقييد الأجهزة على الطالب إذا كان عضواً في أي مجموعة محددة، حتى عند إيقاف التفعيل العام.
                                        </p>
                                    </div>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllRestrictedGroups">
                                            تحديد الكل
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clearRestrictedGroups">
                                            إلغاء التحديد
                                        </button>
                                    </div>
                                </div>

                                @if($courseGroups->isNotEmpty())
                                    <div class="input-group input-group-sm mb-3">
                                        <span class="input-group-text"><i class="fe fe-search"></i></span>
                                        <input type="search" class="form-control" id="restrictedGroupSearch"
                                               placeholder="ابحث عن مجموعة...">
                                    </div>
                                    <div id="restrictedGroupsList" class="row g-2 overflow-auto" style="max-height: 320px;">
                                        @foreach($courseGroups as $group)
                                            <div class="col-md-6 restricted-group-option" data-group-name="{{ mb_strtolower($group->name) }}">
                                                <label class="d-flex align-items-center gap-2 border rounded-3 p-3 h-100 cursor-pointer">
                                                    <input class="form-check-input mt-0 restricted-group-checkbox"
                                                           type="checkbox"
                                                           name="restricted_group_ids[]"
                                                           value="{{ $group->id }}"
                                                           @checked(in_array($group->id, $restrictedGroupIds, true))>
                                                    <span class="flex-grow-1 min-w-0">
                                                        <span class="d-block fw-semibold text-truncate">{{ $group->name }}</span>
                                                        <small class="text-muted">
                                                            {{ number_format($group->members_count) }} عضو
                                                            · {{ $group->is_active ? 'نشطة' : 'غير نشطة' }}
                                                        </small>
                                                    </span>
                                                    <i class="fe fe-shield text-warning"></i>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-light border mb-0">لا توجد مجموعات متاحة.</div>
                                @endif

                                @error('restricted_group_ids')
                                    <div class="text-danger fs-12 mt-2">{{ $message }}</div>
                                @enderror
                                @error('restricted_group_ids.*')
                                    <div class="text-danger fs-12 mt-2">{{ $message }}</div>
                                @enderror
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

@push('scripts')
<script>
(function () {
    'use strict';

    const options = Array.from(document.querySelectorAll('.restricted-group-option'));
    const checkboxes = Array.from(document.querySelectorAll('.restricted-group-checkbox'));
    const search = document.getElementById('restrictedGroupSearch');

    document.getElementById('selectAllRestrictedGroups')?.addEventListener('click', function () {
        options.forEach(function (option) {
            if (option.style.display !== 'none') {
                option.querySelector('.restricted-group-checkbox').checked = true;
            }
        });
    });

    document.getElementById('clearRestrictedGroups')?.addEventListener('click', function () {
        checkboxes.forEach(function (checkbox) {
            checkbox.checked = false;
        });
    });

    search?.addEventListener('input', function () {
        const term = search.value.trim().toLocaleLowerCase('ar');
        options.forEach(function (option) {
            option.style.display = option.dataset.groupName.includes(term) ? '' : 'none';
        });
    });
})();
</script>
@endpush
