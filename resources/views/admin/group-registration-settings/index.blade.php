@extends('admin.layouts.master')

@section('page-title')
    إعدادات التسجيل - {{ $group->name }}
@stop

@section('content')
    @php
        $group->loadMissing('courses');
        $primaryCourse = $group->courses->first();
        $groupShowUrl = $primaryCourse
            ? route('courses.groups.show', [$primaryCourse->id, $group->id])
            : route('groups.show', $group->id);
        $membershipRequestsUrl = $primaryCourse
            ? route('courses.groups.membership-requests', [$primaryCourse->id, $group->id])
            : null;
        $registrationUrl = route('frontend.group-registration.create', $group->id);
        $registrationEnabled = old('is_registration_enabled', $settings->is_registration_enabled);
    @endphp

    <div class="main-content app-content admin-group-form-page admin-group-registration-settings-page">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('groups.all') }}">المجموعات</a></li>
                        @if($primaryCourse)
                            <li class="breadcrumb-item"><a href="{{ route('courses.show', $primaryCourse->id) }}">{{ Str::limit($primaryCourse->title, 28) }}</a></li>
                        @endif
                        <li class="breadcrumb-item"><a href="{{ $groupShowUrl }}">{{ Str::limit($group->name, 28) }}</a></li>
                        <li class="breadcrumb-item active">إعدادات التسجيل</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-start gap-3">
                            <span class="admin-group-form-page__icon">
                                <i class="fe fe-settings"></i>
                            </span>
                            <div class="min-w-0">
                                <span class="group-show-hero__eyebrow">
                                    <i class="fe fe-link me-1"></i>تسجيل خارجي للمجموعة
                                </span>
                                <h2 class="group-show-hero__title mb-2">إعدادات التسجيل</h2>
                                <p class="group-show-hero__desc mb-2">{{ $group->name }}</p>
                                <div class="d-flex flex-wrap gap-2">
                                    @if($registrationEnabled)
                                        <span class="group-show-chip group-show-chip--sm text-success">
                                            <i class="fe fe-check-circle me-1"></i>التسجيل مفعّل
                                        </span>
                                    @else
                                        <span class="group-show-chip group-show-chip--sm text-muted">
                                            <i class="fe fe-pause-circle me-1"></i>التسجيل متوقف
                                        </span>
                                    @endif
                                    @if($settings->auto_approve_membership)
                                        <span class="group-show-chip group-show-chip--sm text-primary">موافقة تلقائية</span>
                                    @else
                                        <span class="group-show-chip group-show-chip--sm text-warning">موافقة يدوية</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions">
                            <a href="{{ $groupShowUrl }}" class="group-show-action group-show-action--info">
                                <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                                <span class="group-show-action__text">رجوع للمجموعة</span>
                            </a>
                            @if($registrationEnabled)
                                <a href="{{ $registrationUrl }}" target="_blank" rel="noopener" class="group-show-action group-show-action--success">
                                    <span class="group-show-action__icon"><i class="fe fe-external-link"></i></span>
                                    <span class="group-show-action__text">معاينة صفحة التسجيل</span>
                                </a>
                            @endif
                            @if($membershipRequestsUrl)
                                <a href="{{ $membershipRequestsUrl }}" class="group-show-action group-show-action--warning">
                                    <span class="group-show-action__icon"><i class="fe fe-user-plus"></i></span>
                                    <span class="group-show-action__text">طلبات الانضمام</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.group-registration-settings.update', $group->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-4 dashboard-fade-in">
                    <div class="col-lg-8">
                        <div class="card custom-card group-show-members-card">
                            <div class="card-header border-0 pb-0">
                                <h6 class="group-show-members-card__title mb-1">الإعدادات العامة</h6>
                                <p class="fs-12 text-muted mb-0">عنوان صفحة التسجيل وتفعيل الرابط العام.</p>
                            </div>
                            <div class="card-body pt-3">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">تسمية الدبلوم</label>
                                    <input type="text" name="diploma_name" class="form-control @error('diploma_name') is-invalid @enderror"
                                           value="{{ old('diploma_name', $settings->diploma_name) }}"
                                           placeholder="مثال: دبلوم البرمجة">
                                    @error('diploma_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <p class="admin-group-form-hint mb-0 mt-2">
                                        يظهر في عنوان صفحة التسجيل: «التسجيل في [هذا الاسم] — {{ $group->name }}»
                                    </p>
                                </div>

                                <div class="admin-group-form-toggle">
                                    <div class="admin-group-form-toggle__info">
                                        <span class="admin-group-form-toggle__label">تفعيل التسجيل لهذه المجموعة</span>
                                        <span class="admin-group-form-toggle__hint">عند التفعيل يصبح رابط التسجيل متاحاً للطلاب</span>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_registration_enabled" id="is_registration_enabled"
                                               {{ $registrationEnabled ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card custom-card group-show-members-card mt-4">
                            <div class="card-header border-0 pb-0">
                                <h6 class="group-show-members-card__title mb-1">المعالجة التلقائية</h6>
                                <p class="fs-12 text-muted mb-0">ما يحدث فور إرسال الطالب لنموذج التسجيل.</p>
                            </div>
                            <div class="card-body pt-3">
                                <div class="admin-group-form-toggle">
                                    <div class="admin-group-form-toggle__info">
                                        <span class="admin-group-form-toggle__label">إنشاء حساب تلقائي للطالب</span>
                                        <span class="admin-group-form-toggle__hint">إنشاء حساب جديد تلقائياً عند التسجيل</span>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="auto_create_user" id="auto_create_user"
                                               {{ old('auto_create_user', $settings->auto_create_user) ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="admin-group-form-toggle">
                                    <div class="admin-group-form-toggle__info">
                                        <span class="admin-group-form-toggle__label">الموافقة التلقائية على الانضمام</span>
                                        <span class="admin-group-form-toggle__hint">إضافة مباشرة للمجموعة بدل طلب قيد الانتظار</span>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="auto_approve_membership" id="auto_approve_membership"
                                               {{ old('auto_approve_membership', $settings->auto_approve_membership) ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="admin-group-registration-settings-page__note">
                                    <i class="fe fe-info"></i>
                                    <div class="fs-12">
                                        <strong>عند إلغاء الموافقة التلقائية:</strong> يُنشأ طلب انضمام بحالة «قيد الانتظار» وتحتاج موافقة من
                                        @if($membershipRequestsUrl)
                                            <a href="{{ $membershipRequestsUrl }}" target="_blank" rel="noopener">صفحة طلبات الانضمام</a>.
                                        @else
                                            لوحة التحكم.
                                        @endif
                                        إذا كان البريد مسجلاً مسبقاً يُطبَّق نفس السلوك دون إنشاء مستخدم جديد.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card custom-card group-show-members-card mt-4">
                            <div class="card-header border-0 pb-0">
                                <h6 class="group-show-members-card__title mb-1">الإشعارات والرسائل</h6>
                                <p class="fs-12 text-muted mb-0">إعدادات البريد والواتساب بعد التسجيل (تُحدَّد من الإدارة).</p>
                            </div>
                            <div class="card-body pt-3">
                                <div class="admin-group-form-toggle">
                                    <div class="admin-group-form-toggle__info">
                                        <span class="admin-group-form-toggle__label">إرسال بريد إلكتروني ترحيبي</span>
                                        <span class="admin-group-form-toggle__hint">رسالة ترحيب تلقائية للطالب</span>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="send_welcome_email" id="send_welcome_email"
                                               {{ old('send_welcome_email', $settings->send_welcome_email) ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="mb-3" id="emailTemplateWrap">
                                    <label class="form-label fw-semibold">قالب البريد الإلكتروني</label>
                                    <select name="email_template_id" class="form-select @error('email_template_id') is-invalid @enderror">
                                        <option value="">استخدام القالب الافتراضي</option>
                                        @foreach($emailTemplates as $template)
                                            <option value="{{ $template->id }}" {{ old('email_template_id', $settings->email_template_id) == $template->id ? 'selected' : '' }}>
                                                {{ $template->name_ar ?? $template->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('email_template_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="admin-group-form-toggle">
                                    <div class="admin-group-form-toggle__info">
                                        <span class="admin-group-form-toggle__label">إرسال رسالة واتساب ترحيبية</span>
                                        <span class="admin-group-form-toggle__hint">رسالة واتساب تلقائية بعد التسجيل</span>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="send_welcome_whatsapp" id="send_welcome_whatsapp"
                                               {{ old('send_welcome_whatsapp', $settings->send_welcome_whatsapp) ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div id="whatsappFieldsWrap">
                                    @php
                                        $deliveryMode = old('whatsapp_delivery_mode', $settings->whatsapp_delivery_mode ?? 'evolution_text');
                                        $defaultBodyVars = implode("\n", old('wapi_body_variables_text', $settings->wapi_body_variables ?? ['{student_name}', '{group_name}']));
                                    @endphp

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">طريقة إرسال واتساب</label>
                                        <select name="whatsapp_delivery_mode" id="whatsapp_delivery_mode" class="form-select">
                                            <option value="evolution_text" @selected($deliveryMode === 'evolution_text')>
                                                نص حر — Evolution / WhatsApp Web (قوالب الرسائل)
                                            </option>
                                            <option value="flaxxa_template" @selected($deliveryMode === 'flaxxa_template')>
                                                Flaxxa — قالب Meta معتمد
                                            </option>
                                        </select>
                                        <p class="admin-group-form-hint mb-0 mt-2">
                                            قوالب Meta تُرسل عبر Flaxxa WAPI (خارج نافذة 24 ساعة). مسار النص الحر يستخدم المزود من
                                            <a href="{{ route('admin.whatsapp-settings.index') }}" target="_blank" rel="noopener">إعدادات WhatsApp</a>.
                                        </p>
                                    </div>

                                    <div class="mb-3" id="evolutionTemplateWrap">
                                        <label class="form-label fw-semibold">قالب واتساب (نص)</label>
                                        <select name="whatsapp_template_id" class="form-select @error('whatsapp_template_id') is-invalid @enderror">
                                            <option value="">استخدام القالب الافتراضي</option>
                                            @foreach($whatsappTemplates as $template)
                                                <option value="{{ $template->id }}" {{ old('whatsapp_template_id', $settings->whatsapp_template_id) == $template->id ? 'selected' : '' }}>
                                                    {{ $template->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('whatsapp_template_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <p class="admin-group-form-hint mb-0 mt-2">
                                            اختر قالباً من
                                            <a href="{{ route('admin.whatsapp-templates.index') }}" target="_blank" rel="noopener">قوالب الرسائل</a>.
                                            المتغيرات: @verbatim<code>{{student_name}}</code>، <code>{{group_name}}</code>، <code>{{email}}</code>@endverbatim
                                        </p>
                                    </div>

                                    <div class="mb-3" id="flaxxaTemplateWrap">
                                        <label class="form-label fw-semibold">قالب Flaxxa (Meta)</label>
                                        <select name="wapi_template_id" id="wapi_template_id" class="form-select @error('wapi_template_id') is-invalid @enderror">
                                            <option value="">— اختر قالب —</option>
                                            @foreach($wapiTemplates as $tpl)
                                                <option value="{{ $tpl->id }}" @selected(old('wapi_template_id', $settings->wapi_template_id) == $tpl->id)>
                                                    {{ $tpl->name }} ({{ $tpl->language }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('wapi_template_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <p class="admin-group-form-hint mb-0 mt-2">
                                            مزامنة القوالب من
                                            <a href="{{ route('admin.flaxxa-wapi.templates.index') }}" target="_blank" rel="noopener">قوالب Flaxxa</a>.
                                            يتطلب توكن Flaxxa في
                                            <a href="{{ route('admin.flaxxa-wapi.settings.index') }}" target="_blank" rel="noopener">إعدادات Flaxxa</a>.
                                        </p>
                                    </div>

                                    <div class="mb-3" id="flaxxaVarsWrap">
                                        <label class="form-label fw-semibold">متغيرات قالب Meta (سطر لكل @verbatim{{1}}@endverbatim، @verbatim{{2}}@endverbatim…)</label>
                                        <textarea name="wapi_body_variables_text" id="wapi_body_variables_text" class="form-control font-monospace" rows="4" placeholder="{student_name}&#10;{group_name}">{{ $defaultBodyVars }}</textarea>
                                        <p class="admin-group-form-hint mb-0 mt-2">
                                            المتغيرات المتاحة: @verbatim<code>{student_name}</code>، <code>{group_name}</code>، <code>{email}</code>، <code>{registration_id}</code>@endverbatim
                                        </p>
                                    </div>

                                    <div class="mb-3" id="flaxxaLangWrap">
                                        <label class="form-label fw-semibold">لغة القالب</label>
                                        <input type="text" name="wapi_template_language" class="form-control" value="{{ old('wapi_template_language', $settings->wapi_template_language ?? 'ar') }}" placeholder="ar">
                                    </div>

                                    <div class="mb-0">
                                        <label class="form-label fw-semibold">رابط مجموعة الواتساب</label>
                                        <input type="url" name="whatsapp_group_link" class="form-control @error('whatsapp_group_link') is-invalid @enderror"
                                               value="{{ old('whatsapp_group_link', $settings->whatsapp_group_link) }}"
                                               placeholder="https://chat.whatsapp.com/...">
                                        @error('whatsapp_group_link')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <p class="admin-group-form-hint mb-0 mt-2">يُعرض في صفحة النجاح بعد التسجيل. اتركه فارغاً لإخفائه.</p>
                                    </div>
                                </div>

                                <div class="admin-group-form-toggle mt-3 pt-3 border-top">
                                    <div class="admin-group-form-toggle__info">
                                        <span class="admin-group-form-toggle__label">التحقق من البريد الإلكتروني</span>
                                        <span class="admin-group-form-toggle__hint">يتطلب تأكيد البريد قبل تفعيل الحساب</span>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="require_email_verification" id="require_email_verification"
                                               {{ old('require_email_verification', $settings->require_email_verification) ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card custom-card group-show-members-card admin-group-form-page__sidebar">
                            <div class="card-header border-0 pb-0">
                                <h6 class="group-show-members-card__title mb-1">ملخص سريع</h6>
                            </div>
                            <div class="card-body pt-3">
                                <dl class="admin-module-show-page__meta mb-0">
                                    <div class="admin-module-show-page__meta-row">
                                        <dt>المجموعة</dt>
                                        <dd>{{ Str::limit($group->name, 32) }}</dd>
                                    </div>
                                    <div class="admin-module-show-page__meta-row">
                                        <dt>التسجيل</dt>
                                        <dd>{{ $registrationEnabled ? 'مفعّل' : 'متوقف' }}</dd>
                                    </div>
                                    <div class="admin-module-show-page__meta-row">
                                        <dt>الحساب</dt>
                                        <dd>{{ $settings->auto_create_user ? 'إنشاء تلقائي' : 'يدوي' }}</dd>
                                    </div>
                                    <div class="admin-module-show-page__meta-row">
                                        <dt>الانضمام</dt>
                                        <dd>{{ $settings->auto_approve_membership ? 'فوري' : 'بموافقة' }}</dd>
                                    </div>
                                </dl>

                                @if($registrationEnabled)
                                    <div class="admin-group-registration-settings-page__link-box mt-3">
                                        <small class="text-muted d-block mb-1">رابط التسجيل العام</small>
                                        <a href="{{ $registrationUrl }}" target="_blank" rel="noopener" class="admin-group-registration-settings-page__link">
                                            {{ $registrationUrl }}
                                        </a>
                                    </div>
                                @endif

                                <div class="admin-group-form-page__actions">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fe fe-save me-1"></i>حفظ الإعدادات
                                    </button>
                                    <a href="{{ $groupShowUrl }}" class="btn btn-outline-secondary w-100">إلغاء</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
@stop

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var emailToggle = document.getElementById('send_welcome_email');
        var emailWrap = document.getElementById('emailTemplateWrap');
        var whatsappToggle = document.getElementById('send_welcome_whatsapp');
        var whatsappWrap = document.getElementById('whatsappFieldsWrap');

        function syncVisibility(toggle, wrap) {
            if (!toggle || !wrap) {
                return;
            }
            wrap.style.display = toggle.checked ? '' : 'none';
        }

        if (emailToggle && emailWrap) {
            emailToggle.addEventListener('change', function () {
                syncVisibility(emailToggle, emailWrap);
            });
            syncVisibility(emailToggle, emailWrap);
        }

        if (whatsappToggle && whatsappWrap) {
            whatsappToggle.addEventListener('change', function () {
                syncVisibility(whatsappToggle, whatsappWrap);
            });
            syncVisibility(whatsappToggle, whatsappWrap);
        }

        var deliveryMode = document.getElementById('whatsapp_delivery_mode');
        var evolutionWrap = document.getElementById('evolutionTemplateWrap');
        var flaxxaWrap = document.getElementById('flaxxaTemplateWrap');
        var flaxxaVarsWrap = document.getElementById('flaxxaVarsWrap');
        var flaxxaLangWrap = document.getElementById('flaxxaLangWrap');

        function syncDeliveryMode() {
            if (!deliveryMode) {
                return;
            }
            var isFlaxxa = deliveryMode.value === 'flaxxa_template';
            if (evolutionWrap) evolutionWrap.style.display = isFlaxxa ? 'none' : '';
            if (flaxxaWrap) flaxxaWrap.style.display = isFlaxxa ? '' : 'none';
            if (flaxxaVarsWrap) flaxxaVarsWrap.style.display = isFlaxxa ? '' : 'none';
            if (flaxxaLangWrap) flaxxaLangWrap.style.display = isFlaxxa ? '' : 'none';
        }

        if (deliveryMode) {
            deliveryMode.addEventListener('change', syncDeliveryMode);
            syncDeliveryMode();
        }
    });
</script>
@stop
