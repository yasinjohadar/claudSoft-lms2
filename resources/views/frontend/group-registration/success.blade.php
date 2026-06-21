@extends('frontend.layouts.standalone')

@section('page-title')
    تم إرسال طلب التسجيل بنجاح
@endsection

@section('content')
<div class="main-content">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- بطاقة النجاح الرئيسية -->
                <div class="card shadow-lg border-success mb-4">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 100px;"></i>
                        </div>
                        <h2 class="text-success mb-4 fw-bold">تم إرسال طلب التسجيل بنجاح</h2>

                        <!-- ملاحظة هامة -->
                        <div class="alert alert-warning border-warning border-2 mb-4 mx-auto shadow-sm text-center" style="max-width: 600px;" role="alert">
                            <i class="fas fa-info-circle text-warning mb-2" style="font-size: 1.75rem;"></i>
                            <h5 class="alert-heading text-dark mb-2">ملاحظة هامة</h5>
                            <p class="mb-2">يرجى <strong>قراءة هذه الصفحة بالكامل</strong> لمتابعة خطواتك القادمة.</p>
                            <p class="mb-0 fw-bold">أهم خطوة من أجل إكمال انضمامك للدبلوم هي <strong>الانضمام لكروب الواتساب المخصص للدبلوم</strong> عبر الزر أدناه، لمتابعة الأخبار والتحديثات.</p>
                        </div>
                        
                        <!-- معلومات الطالب -->
                        <div class="card bg-light mb-4 mx-auto" style="max-width: 600px;">
                            <div class="card-body text-center">
                                <h5 class="card-title mb-3">
                                    <i class="fas fa-user-circle text-primary me-2"></i>
                                    معلومات التسجيل
                                </h5>
                                <div class="registration-info-stack text-center">
                                    <div class="registration-info-item">
                                        <p class="mb-1">
                                            <strong><i class="fas fa-user me-2 text-muted"></i>الاسم:</strong>
                                        </p>
                                        <p class="text-primary fw-bold fs-5 mb-0">
                                            {{ $registration->name_ar ?? $registration->name }}
                                        </p>
                                    </div>
                                    <div class="registration-info-item">
                                        <p class="mb-1">
                                            <strong><i class="fas fa-envelope me-2 text-muted"></i>البريد الإلكتروني:</strong>
                                        </p>
                                        <p class="text-primary fw-bold fs-5 mb-0 text-break">
                                            {{ $registration->email }}
                                        </p>
                                    </div>
                                    <div class="registration-info-item mb-0">
                                        <p class="mb-1">
                                            <strong><i class="fas fa-building me-2 text-muted"></i>المؤسسة:</strong>
                                        </p>
                                        <p class="text-success fw-bold fs-5 mb-0">
                                            كلاودسوفت التعليمية
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- رسالة النجاح -->
                        <div class="alert alert-success mb-4 mx-auto" style="max-width: 600px;" role="alert">
                            <div class="text-center">
                                <i class="fas fa-check-circle mb-3" style="font-size: 2.5rem;"></i>
                                <h5 class="alert-heading mb-2">تم استلام طلبك بنجاح!</h5>
                                <p class="mb-0">
                                    عزيزي/عزيزتي <strong>{{ $registration->name_ar ?? $registration->name }}</strong>،
                                    <br>
                                    تم استلام طلب تسجيلك في <strong>كلاودسوفت التعليمية</strong> بنجاح.
                                    @if($registration->user_created)
                                        <br><strong>تم إنشاء حساب لك بنجاح.</strong>
                                    @endif
                                    <br>
                                    سيتم مراجعة طلبك قريباً وإشعارك بالنتيجة.
                                </p>
                            </div>
                        </div>

                        <!-- تنبيه البريد الإلكتروني -->
                        @if($settings && $settings->send_welcome_email)
                        <div class="alert alert-info mb-4 mx-auto d-flex flex-column align-items-center" style="max-width: 600px;" role="alert">
                            <div class="mb-3">
                                <i class="fas fa-envelope text-info" style="font-size: 80px;"></i>
                            </div>
                            <div class="text-center">
                                <h5 class="alert-heading mb-2">تم إرسال بريد إلكتروني لك!</h5>
                                <p class="mb-0">
                                    تم إرسال بريد إلكتروني إلى <strong>{{ $registration->email }}</strong> يحتوي على تفاصيل تسجيلك.
                                    <br>
                                    يرجى التحقق من صندوق الوارد (والبريد المزعج) لمتابعة التفاصيل.
                                </p>
                            </div>
                        </div>
                        @endif

                        <!-- تنبيه مجموعة الواتساب -->
                        @if($settings && $settings->whatsapp_group_link)
                        <div class="alert alert-danger mb-4 mx-auto d-flex flex-column align-items-center" style="max-width: 600px;" role="alert">
                            <div class="mb-3">
                                <i class="fas fa-exclamation-triangle" style="font-size: 3rem;"></i>
                            </div>
                            <div class="text-center">
                                <h5 class="alert-heading mb-2">تنبيه مهم!</h5>
                                <p class="mb-3">
                                    يرجى الانضمام لمجموعة الواتساب الخاصة بالكورس لمتابعة أخبار وتقدم الكورس بشكل صحيح ومزامن.
                                </p>
                                <a href="{{ $settings->whatsapp_group_link }}" target="_blank" rel="noopener noreferrer" class="btn btn-danger btn-lg">
                                    <i class="fab fa-whatsapp me-2"></i>
                                    انضم لمجموعة الواتساب الآن
                                </a>
                            </div>
                        </div>
                        @endif

                        <!-- أزرار الإجراءات -->
                        <div class="d-flex gap-3 justify-content-center flex-wrap mt-4">
                            <a href="{{ route('frontend.home') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-home me-2"></i>
                                العودة للصفحة الرئيسية
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .main-content {
        min-height: 70vh;
        display: flex;
        align-items: center;
    }
    
    .card-body {
        text-align: center;
    }
    
    .card-body .card {
        text-align: center;
    }
    
    .card-body .card .card-body {
        text-align: center;
    }
    
    .registration-info-stack {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }

    .registration-info-item {
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
    }

    .registration-info-item:last-child {
        padding-bottom: 0;
        border-bottom: none;
    }

    @media (max-width: 768px) {
        .card-body {
            padding: 2rem 1rem !important;
        }
        .fs-5 {
            font-size: 1.1rem !important;
        }
        .alert .fas {
            font-size: 50px !important;
        }
    }
</style>
@endsection
