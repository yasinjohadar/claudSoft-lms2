@extends('admin.layouts.master')

@section('page-title', 'إعدادات الموقع')

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="page-title fw-semibold fs-18 mb-0">إعدادات الموقع</h4>
                <p class="fw-normal text-muted fs-14 mb-0">إدارة الإعدادات العامة للموقع</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-check-line me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Settings Form -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="ri-settings-3-line me-2"></i>الإعدادات العامة
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.settings.site.update') }}" method="POST">
                            @csrf
                            @method('POST')

                            <!-- Registration Settings -->
                            <div class="card border mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="ri-user-add-line me-2"></i>إعدادات التسجيل
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <!-- Hidden input to ensure value is sent even when checkbox is unchecked -->
                                    <input type="hidden" name="registration_public_enabled" value="0">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" 
                                               name="registration_public_enabled" 
                                               id="registration_public_enabled"
                                               value="1"
                                               {{ $registrationEnabled ? 'checked' : '' }}>
                                        <label class="form-check-label" for="registration_public_enabled">
                                            <strong>تفعيل التسجيل العام</strong>
                                        </label>
                                    </div>
                                    <div class="alert alert-info mb-0">
                                        <i class="ri-information-line me-2"></i>
                                        <strong>ملاحظة:</strong> عند إيقاف هذا الخيار، سيتم منع الوصول إلى صفحة التسجيل العامة (<code>/register</code>) 
                                        وسيتم توجيه المستخدمين إلى صفحة تسجيل الدخول. 
                                        <strong>التسجيل من لوحة التحكم للأدمن يبقى متاحاً.</strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Student Profile Settings -->
                            <div class="card border mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="ri-user-settings-line me-2"></i>إعدادات الطلاب
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <input type="hidden" name="force_student_profile_completion" value="0">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox"
                                               name="force_student_profile_completion"
                                               id="force_student_profile_completion"
                                               value="1"
                                               {{ ($forceProfileCompletion ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="force_student_profile_completion">
                                            <strong>إجبار إكمال الملف الشخصي</strong>
                                        </label>
                                    </div>
                                    <div class="alert alert-info mb-0">
                                        <i class="ri-information-line me-2"></i>
                                        عند التفعيل، يُقيَّد الطالب بصفحة تعديل ملفه الشخصي حتى يصل اكتماله إلى 100%،
                                        ولا يمكنه تصفح بقية المنصة (الويب والتطبيق) حتى ذلك الحين.
                                        <strong>الدخول كطالب (Impersonation) من الأدمن يتخطى هذا القيد.</strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary btn-wave">
                                    <i class="ri-save-line me-1"></i>حفظ التغييرات
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End::app-content -->
@endsection

