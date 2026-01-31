@extends('admin.layouts.master')

@section('page-title')
    إعدادات التسجيل - {{ $group->name }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">
                    <i class="fas fa-cog me-2"></i>
                    إعدادات التسجيل - {{ $group->name }}
                </h5>
            </div>
            <div class="ms-auto">
                <a href="{{ route('courses.groups.show', [$group->course_id ?? 1, $group->id]) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-right me-2"></i>
                    رجوع للمجموعة
                </a>
            </div>
        </div>

        @include('admin.components.alerts')

        <div class="row">
            <div class="col-lg-10">
                <div class="card custom-card">
                    <div class="card-body">
                        <form action="{{ route('admin.group-registration-settings.update', $group->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- تسمية الدبلوم -->
                            <div class="mb-4">
                                <label class="form-label">تسمية الدبلوم (عنوان صفحة التسجيل)</label>
                                <input type="text" name="diploma_name" class="form-control @error('diploma_name') is-invalid @enderror" 
                                       value="{{ old('diploma_name', $settings->diploma_name) }}" 
                                       placeholder="مثال: دبلوم البرمجة">
                                @error('diploma_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">يظهر في عنوان صفحة التسجيل: "التسجيل في [هذا الاسم] [اسم المجموعة]"</small>
                            </div>

                            <!-- تفعيل التسجيل -->
                            <div class="mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_registration_enabled" id="is_registration_enabled" 
                                           {{ old('is_registration_enabled', $settings->is_registration_enabled) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_registration_enabled">
                                        <strong>تفعيل التسجيل لهذه المجموعة</strong>
                                    </label>
                                </div>
                                <small class="text-muted">عند تفعيل هذا الخيار، سيظهر رابط التسجيل للطلاب</small>
                            </div>

                            <hr>

                            <!-- إعدادات المعالجة التلقائية -->
                            <h6 class="mb-3">المعالجة التلقائية</h6>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="auto_create_user" id="auto_create_user" 
                                           {{ old('auto_create_user', $settings->auto_create_user) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="auto_create_user">
                                        <strong>إنشاء حساب تلقائي للطالب</strong>
                                    </label>
                                </div>
                                <small class="text-muted">سيتم إنشاء حساب جديد للطالب تلقائياً عند التسجيل</small>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="auto_approve_membership" id="auto_approve_membership" 
                                           {{ old('auto_approve_membership', $settings->auto_approve_membership) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="auto_approve_membership">
                                        <strong>الموافقة التلقائية على الانضمام</strong>
                                    </label>
                                </div>
                                <small class="text-muted">
                                    <strong>عند تفعيله:</strong> سيتم إضافة المستخدم مباشرة للمجموعة بعد التسجيل.
                                    <br>
                                    <strong>عند إلغاء تفعيله:</strong> سيتم إنشاء طلب انضمام بحالة "قيد الانتظار" يحتاج موافقة الإدارة من صفحة <a href="{{ route('courses.groups.membership-requests', [$group->course_id ?? 1, $group->id]) }}" target="_blank">طلبات الانضمام</a>.
                                    <br>
                                    <strong>ملاحظة:</strong> إذا كان المستخدم موجود مسبقاً (من خلال الإيميل)، سيتم تطبيق نفس السلوك دون إنشاء مستخدم جديد.
                                </small>
                            </div>

                            <hr>

                            <!-- إعدادات الإرسال -->
                            <h6 class="mb-3">إعدادات الإرسال (إجبارية من الإدارة)</h6>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="send_welcome_email" id="send_welcome_email" 
                                           {{ old('send_welcome_email', $settings->send_welcome_email) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="send_welcome_email">
                                        <strong>إرسال بريد إلكتروني ترحيبي</strong>
                                    </label>
                                </div>
                                <small class="text-muted">سيتم إرسال بريد إلكتروني ترحيبي للطالب تلقائياً (إجباري من الإدارة)</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">قالب البريد الإلكتروني</label>
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

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="send_welcome_whatsapp" id="send_welcome_whatsapp" 
                                           {{ old('send_welcome_whatsapp', $settings->send_welcome_whatsapp) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="send_welcome_whatsapp">
                                        <strong>إرسال رسالة واتساب ترحيبية</strong>
                                    </label>
                                </div>
                                <small class="text-muted">سيتم إرسال رسالة واتساب ترحيبية للطالب تلقائياً (إجباري من الإدارة)</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">قالب واتساب</label>
                                <textarea name="whatsapp_template" class="form-control @error('whatsapp_template') is-invalid @enderror" rows="4">{{ old('whatsapp_template', $settings->whatsapp_template) }}</textarea>
                                @error('whatsapp_template')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">يمكنك استخدام المتغيرات: @{{student_name}}, @{{group_name}}, @{{email}}</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">رابط مجموعة الواتساب</label>
                                <input type="url" name="whatsapp_group_link" class="form-control @error('whatsapp_group_link') is-invalid @enderror" 
                                       value="{{ old('whatsapp_group_link', $settings->whatsapp_group_link) }}" 
                                       placeholder="https://chat.whatsapp.com/...">
                                @error('whatsapp_group_link')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    سيظهر هذا الرابط في صفحة النجاح بعد التسجيل. اتركه فارغاً إذا لم تريد عرضه.
                                </small>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="require_email_verification" id="require_email_verification" 
                                           {{ old('require_email_verification', $settings->require_email_verification) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="require_email_verification">
                                        <strong>التحقق من البريد الإلكتروني</strong>
                                    </label>
                                </div>
                                <small class="text-muted">يتطلب التحقق من البريد الإلكتروني قبل تفعيل الحساب</small>
                            </div>

                            <hr>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>
                                    حفظ الإعدادات
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
