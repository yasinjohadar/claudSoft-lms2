@extends('admin.layouts.master')

@section('page-title')
    إرسال بريد جماعي
@stop

@section('content')
    <div class="main-content app-content admin-bulk-emails-page">
        <div class="container-fluid">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fe fe-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fe fe-alert-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fe fe-alert-triangle me-2"></i>
                    <strong>يرجى تصحيح الأخطاء التالية:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.bulk-emails.index') }}">سجل الإرسال</a></li>
                        <li class="breadcrumb-item active">إرسال بريد جماعي</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-mail me-1"></i>
                            البريد الجماعي
                        </span>
                        <h2 class="group-show-hero__title mb-2">إرسال بريد جماعي للطلاب</h2>
                        <p class="group-show-hero__desc mb-0">
                            اختر الجمهور، حدد قالباً أو محتوى مخصصاً، واختر إعداد SMTP ثم ابدأ الإرسال عبر قائمة الانتظار.
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions">
                            <a href="{{ route('admin.bulk-emails.index') }}" class="group-show-action">
                                <span class="group-show-action__icon"><i class="fe fe-list"></i></span>
                                <span class="group-show-action__text">سجل الإرسال</span>
                            </a>
                            <a href="{{ route('admin.bulk-emails.settings.index') }}" class="group-show-action group-show-action--info">
                                <span class="group-show-action__icon"><i class="fe fe-sliders"></i></span>
                                <span class="group-show-action__text">إعدادات الإرسال</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-1">إعداد الحملة</h4>
                    <p class="fs-12 text-muted mb-0">حدد المستلمين ومحتوى البريد وإعدادات الإرسال.</p>
                </div>
                <div class="card-body pt-3">
                    <form action="{{ route('admin.bulk-emails.store') }}" method="POST" id="bulkEmailForm">
                        @csrf

                        <div class="row g-4 mb-4">
                            <div class="col-12">
                                <label class="form-label d-block mb-2">نوع الجمهور <span class="text-danger">*</span></label>
                                <div class="btn-group flex-wrap w-100 admin-bulk-emails-audience-toggle" role="group">
                                    @foreach ([
                                        'individual' => 'طالب واحد',
                                        'selected' => 'طلاب محددون',
                                        'group' => 'مجموعة كاملة',
                                        'course' => 'كورس كامل',
                                        'course_group' => 'كورس + مجموعة',
                                    ] as $value => $label)
                                        <input type="radio" class="btn-check" name="audience_type" id="audience_{{ $value }}" value="{{ $value }}" {{ old('audience_type', 'individual') === $value ? 'checked' : '' }}>
                                        <label class="btn btn-outline-primary" for="audience_{{ $value }}">{{ $label }}</label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="col-12 admin-bulk-emails-audience-panels">
                                <div class="row g-3" data-audience-panel="individual">
                                    <div class="col-md-6">
                                        <label class="form-label" for="student_id_individual">الطالب <span class="text-danger">*</span></label>
                                        <select name="student_ids[]" id="student_id_individual" class="form-select admin-bulk-emails-student-select"></select>
                                        <small class="text-muted">اختر طالباً واحداً فقط.</small>
                                    </div>
                                </div>

                                <div class="row g-3 d-none" data-audience-panel="selected">
                                    <div class="col-md-8">
                                        <label class="form-label" for="student_ids_selected">الطلاب <span class="text-danger">*</span></label>
                                        <select name="student_ids[]" id="student_ids_selected" class="form-select admin-bulk-emails-student-select" multiple></select>
                                        <small class="text-muted">يمكنك اختيار عدة طلاب.</small>
                                    </div>
                                </div>

                                <div class="row g-3 d-none" data-audience-panel="group">
                                    <div class="col-md-6">
                                        <label class="form-label" for="group_id">المجموعة <span class="text-danger">*</span></label>
                                        <select name="group_id" id="group_id" class="form-select">
                                            <option value="">— اختر مجموعة —</option>
                                            @foreach ($groups as $group)
                                                <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row g-3 d-none" data-audience-panel="course">
                                    <div class="col-md-6">
                                        <label class="form-label" for="course_id">الكورس <span class="text-danger">*</span></label>
                                        <select name="course_id" id="course_id" class="form-select">
                                            <option value="">— اختر كورس —</option>
                                            @foreach ($courses as $course)
                                                <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row g-3 d-none" data-audience-panel="course_group">
                                    <div class="col-md-6">
                                        <label class="form-label" for="course_id_course_group">الكورس <span class="text-danger">*</span></label>
                                        <select name="course_id" id="course_id_course_group" class="form-select">
                                            <option value="">— اختر كورس —</option>
                                            @foreach ($courses as $course)
                                                <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="group_id_course_group">المجموعة <span class="text-danger">*</span></label>
                                        <select name="group_id" id="group_id_course_group" class="form-select">
                                            <option value="">— اختر مجموعة —</option>
                                            @foreach ($groups as $group)
                                                <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="admin-bulk-emails-count-badge alert alert-light border mb-0">
                                    <i class="fe fe-users me-1"></i>
                                    عدد المستلمين المتوقع: <strong id="recipientCountBadge">—</strong>
                                    <span class="admin-bulk-emails-duration-badge ms-2" id="estimatedDurationBadge"></span>
                                    <button type="button" class="btn btn-sm btn-outline-primary ms-2" id="refreshCountBtn">تحديث العدد</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary ms-1" id="previewRecipientsBtn">عرض المستلمين</button>
                                </div>
                            </div>

                            <div class="col-12 d-none" id="recipientsPreviewCard">
                                <div class="card admin-bulk-emails-preview border-0 bg-light">
                                    <div class="card-body">
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                            <h6 class="mb-0"><i class="fe fe-users me-1"></i>معاينة المستلمين</h6>
                                            <span class="text-muted fs-12" id="recipientsPreviewMeta"></span>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover mb-0 admin-bulk-emails-recipients-table">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>الاسم</th>
                                                        <th>البريد الإلكتروني</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="recipientsPreviewBody">
                                                    <tr>
                                                        <td colspan="3" class="text-muted text-center">—</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label" for="email_setting_id">إعداد SMTP</label>
                                <select name="email_setting_id" id="email_setting_id" class="form-select">
                                    <option value="">الإعداد النشط الافتراضي{{ $defaultSetting ? ' (' . ($defaultSetting->mail_from_address ?? '—') . ')' : '' }}</option>
                                    @foreach ($emailSettings as $setting)
                                        <option value="{{ $setting->id }}" {{ old('email_setting_id') == $setting->id ? 'selected' : '' }}>
                                            {{ $setting->mail_from_name ?? $setting->provider ?? 'SMTP' }} — {{ $setting->mail_from_address }}
                                            @if ($setting->is_active) (نشط) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label d-block mb-2">نوع المحتوى <span class="text-danger">*</span></label>
                                <div class="btn-group" role="group">
                                    <input type="radio" class="btn-check" name="content_mode" id="content_template" value="template" {{ old('content_mode', 'template') === 'template' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-secondary" for="content_template">قالب جاهز</label>
                                    <input type="radio" class="btn-check" name="content_mode" id="content_custom" value="custom" {{ old('content_mode') === 'custom' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-secondary" for="content_custom">محتوى مخصص</label>
                                </div>
                            </div>

                            <div class="col-12 content-field content-template">
                                <label class="form-label" for="email_template_id">قالب البريد <span class="text-danger">*</span></label>
                                <select name="email_template_id" id="email_template_id" class="form-select">
                                    <option value="">— اختر قالب —</option>
                                    @foreach ($templates as $template)
                                        <option value="{{ $template->id }}" {{ old('email_template_id') == $template->id ? 'selected' : '' }}>
                                            {{ $template->name_ar ?: $template->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 content-field content-custom d-none">
                                <div class="mb-3">
                                    <label class="form-label" for="custom_subject">موضوع البريد <span class="text-danger">*</span></label>
                                    <input type="text" name="subject" id="custom_subject" class="form-control" value="{{ old('subject') }}">
                                    <small class="text-muted">يمكنك استخدام المتغيرات: @{{student_name}}, @{{course_name}}, @{{group_name}}, @{{email}}, @{{phone}}</small>
                                </div>

                                <div class="mb-0">
                                    <label class="form-label" for="custom_body">محتوى البريد <span class="text-danger">*</span></label>
                                    <textarea name="body" id="custom_body" class="form-control" rows="12">{{ old('body') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="admin-bulk-emails-preview card bg-light border-0 mb-4 d-none" id="contentPreviewCard">
                            <div class="card-body">
                                <h6 class="mb-3"><i class="fe fe-eye me-1"></i>معاينة المحتوى</h6>
                                <p class="mb-2"><strong>عينة الطالب:</strong> <span id="previewSampleUser">—</span></p>
                                <p class="mb-2"><strong>الموضوع:</strong> <span id="previewSubject">—</span></p>
                                <div id="previewBody" class="border rounded p-3 bg-white"></div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-secondary" id="previewContentBtn">
                                <i class="fe fe-eye me-1"></i>معاينة المحتوى
                            </button>
                            <button type="submit" class="btn btn-primary" id="submitCampaignBtn">
                                <i class="fe fe-send me-1"></i>بدء الإرسال
                            </button>
                            <a href="{{ route('admin.bulk-emails.index') }}" class="btn btn-light">إلغاء</a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endsection

@section('script')
    @include('admin.pages.bulk-emails.partials.scripts')
@endsection
