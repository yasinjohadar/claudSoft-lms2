@extends('admin.layouts.master')

@section('page-title')
    تفاصيل قالب البريد الإلكتروني
@stop

@section('content')
    <div class="main-content app-content admin-email-templates-page">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.email-templates.index') }}">قوالب البريد</a></li>
                        <li class="breadcrumb-item active">{{ Str::limit($emailTemplate->name_ar ?: $emailTemplate->name, 30) }}</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-file-text me-1"></i>
                            تفاصيل القالب
                        </span>
                        <h2 class="group-show-hero__title mb-2">{{ $emailTemplate->name_ar ?: $emailTemplate->name }}</h2>
                        <p class="group-show-hero__desc mb-0">
                            @if ($emailTemplate->type === 'registration_welcome')
                                <span class="group-show-chip group-show-chip--sm text-info">ترحيب بالتسجيل</span>
                            @elseif ($emailTemplate->type === 'enrollment_confirmation')
                                <span class="group-show-chip group-show-chip--sm text-success">تأكيد التسجيل</span>
                            @else
                                <span class="group-show-chip group-show-chip--sm text-secondary">مخصص</span>
                            @endif
                            @if ($emailTemplate->is_active)
                                <span class="group-show-chip group-show-chip--sm text-success ms-2">
                                    <i class="fe fe-check-circle me-1"></i>نشط
                                </span>
                            @else
                                <span class="group-show-chip group-show-chip--sm text-danger ms-2">
                                    <i class="fe fe-slash me-1"></i>معطل
                                </span>
                            @endif
                            <span class="text-muted ms-2">{{ $emailTemplate->created_at->format('Y-m-d H:i') }}</span>
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions">
                            <a href="{{ route('admin.email-templates.edit', $emailTemplate) }}" class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-edit-2"></i></span>
                                <span class="group-show-action__text">تعديل القالب</span>
                            </a>
                            <a href="{{ route('admin.email-templates.preview', $emailTemplate) }}" class="group-show-action group-show-action--info" target="_blank">
                                <span class="group-show-action__icon"><i class="fe fe-maximize-2"></i></span>
                                <span class="group-show-action__text">معاينة كاملة</span>
                            </a>
                            <a href="{{ route('admin.email-templates.index') }}" class="group-show-action">
                                <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                                <span class="group-show-action__text">رجوع للقائمة</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title mb-0">معلومات القالب</h4>
                        </div>
                        <div class="card-body pt-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <p class="text-muted mb-1 fs-12">الاسم (إنجليزي)</p>
                                    <p class="fw-semibold mb-0">{{ $emailTemplate->name }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1 fs-12">الاسم (عربي)</p>
                                    <p class="fw-semibold mb-0">{{ $emailTemplate->name_ar ?? '—' }}</p>
                                </div>
                                <div class="col-12">
                                    <p class="text-muted mb-1 fs-12">الموضوع</p>
                                    <p class="mb-0">{{ $emailTemplate->subject }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1 fs-12">تاريخ الإنشاء</p>
                                    <p class="mb-0">{{ $emailTemplate->created_at->format('Y-m-d H:i') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1 fs-12">آخر تحديث</p>
                                    <p class="mb-0">{{ $emailTemplate->updated_at->format('Y-m-d H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card group-show-members-card dashboard-fade-in">
                        <div class="card-header border-0 pb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <h4 class="card-title mb-0">معاينة القالب</h4>
                            <span class="group-show-chip group-show-chip--sm text-muted">بيانات تجريبية</span>
                        </div>
                        <div class="card-body pt-3">
                            <div class="mb-4">
                                <p class="text-muted mb-2 fs-12">الموضوع بعد الاستبدال</p>
                                <div class="admin-email-templates-page__preview-subject">{{ $renderedSubject }}</div>
                            </div>
                            <div>
                                <p class="text-muted mb-2 fs-12">المحتوى بعد الاستبدال</p>
                                <div class="admin-email-templates-page__preview-body">
                                    {!! $renderedBody !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title mb-0">الإجراءات</h4>
                        </div>
                        <div class="card-body pt-3">
                            <div class="d-grid gap-2">
                                <a href="{{ route('admin.email-templates.edit', $emailTemplate) }}" class="btn btn-primary btn-sm">
                                    <i class="fe fe-edit-2 me-1"></i>تعديل القالب
                                </a>
                                <a href="{{ route('admin.email-templates.preview', $emailTemplate) }}"
                                   class="btn btn-outline-info btn-sm" target="_blank">
                                    <i class="fe fe-maximize-2 me-1"></i>معاينة كاملة
                                </a>
                                <form action="{{ route('admin.email-templates.duplicate', $emailTemplate) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                                        <i class="fe fe-copy me-1"></i>نسخ القالب
                                    </button>
                                </form>
                                <form action="{{ route('admin.email-templates.destroy', $emailTemplate) }}" method="POST"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا القالب؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                        <i class="fe fe-trash-2 me-1"></i>حذف القالب
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card group-show-members-card dashboard-fade-in">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title mb-0">البيانات التجريبية</h4>
                        </div>
                        <div class="card-body pt-3">
                            <ul class="list-unstyled mb-0 admin-email-templates-page__test-data">
                                <li><span class="text-muted">student_name</span> <strong>{{ $testData['student_name'] }}</strong></li>
                                <li><span class="text-muted">student_name_en</span> <strong>{{ $testData['student_name_en'] }}</strong></li>
                                <li><span class="text-muted">group_name</span> <strong>{{ $testData['group_name'] }}</strong></li>
                                <li><span class="text-muted">email</span> <strong>{{ $testData['email'] }}</strong></li>
                                <li><span class="text-muted">phone</span> <strong>{{ $testData['phone'] }}</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop
