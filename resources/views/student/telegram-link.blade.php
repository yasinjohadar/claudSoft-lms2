@extends('student.layouts.master')

@section('page-title', 'ربط Telegram')

@section('content')
<div class="main-content app-content student-telegram-link-page">
    <div class="container-fluid">

        @include('student.components.alerts')

        <div class="card custom-card group-show-hero dashboard-fade-in mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <span class="group-show-hero__eyebrow">
                        <i class="ri-telegram-line me-1"></i>ربط سريع
                    </span>
                    <h4 class="group-show-hero__title mb-2">ربط حساب Telegram</h4>
                    <p class="group-show-hero__desc mb-2">
                        اربط حسابك لتصلك إشعارات المنصة ودعوات المجموعات مباشرة على Telegram — خطوة واحدة فقط.
                    </p>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active">ربط Telegram</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary rounded-pill">
                        <i class="fe fe-arrow-right me-1"></i>رجوع
                    </a>
                </div>
            </div>
        </div>

        @if(!($studentTelegram['enabled'] ?? false))
            <div class="card custom-card">
                <div class="card-body text-center py-5">
                    <i class="ri-telegram-line fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="mb-2">Telegram غير مفعّل حالياً</h5>
                    <p class="text-muted mb-0">خدمة Telegram غير متاحة على المنصة في الوقت الحالي. تواصل مع الإدارة عند تفعيلها.</p>
                </div>
            </div>
        @elseif($user->telegram_chat_id)
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card custom-card student-quizzes-panel">
                        <div class="card-body">
                            <div class="alert alert-success d-flex align-items-start gap-2 mb-4">
                                <i class="fe fe-check-circle fs-20 mt-1"></i>
                                <div>
                                    <strong>حسابك مربوط بنجاح</strong>
                                    <p class="mb-0 mt-1 fs-13">
                                        @if($user->telegram_username)
                                            {{ '@'.$user->telegram_username }}
                                        @else
                                            حساب Telegram
                                        @endif
                                        @if($user->telegram_linked_at)
                                            — منذ {{ $user->telegram_linked_at->format('Y-m-d h:i A') }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <h6 class="mb-3">ماذا يمكنك الآن؟</h6>
                            <ul class="list-unstyled mb-4">
                                <li class="d-flex align-items-start gap-2 mb-2 fs-13">
                                    <i class="fe fe-bell text-primary mt-1"></i>
                                    <span>استلام إشعارات المنصة على Telegram (حسب إعداداتك)</span>
                                </li>
                                <li class="d-flex align-items-start gap-2 mb-2 fs-13">
                                    <i class="fe fe-users text-info mt-1"></i>
                                    <span>تلقي دعوات مجموعات الكورسات على حسابك المربوط</span>
                                </li>
                                <li class="d-flex align-items-start gap-2 fs-13">
                                    <i class="fe fe-settings text-secondary mt-1"></i>
                                    <span>تخصيص تفضيلات الإشعارات من <a href="{{ route('student.settings.notifications') }}">إعدادات الإشعارات</a></span>
                                </li>
                            </ul>

                            <form method="POST" action="{{ route('student.telegram.unlink') }}" onsubmit="return confirm('هل أنت متأكد من فك ربط Telegram؟');">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger rounded-pill">
                                    <i class="fe fe-link-2 me-1"></i>فك الربط
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    @include('student.components.telegram-connect-card', ['compact' => true])
                </div>
            </div>
        @else
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card custom-card student-quizzes-panel mb-4">
                        <div class="card-body">
                            <h5 class="mb-3">خطوات الربط</h5>
                            <div class="student-telegram-steps">
                                <div class="student-telegram-step">
                                    <span class="student-telegram-step__num">1</span>
                                    <div>
                                        <strong>اضغط زر «فتح البوت في Telegram»</strong>
                                        <p class="text-muted fs-13 mb-0">سيفتح تطبيق Telegram أو نسخة الويب تلقائياً.</p>
                                    </div>
                                </div>
                                <div class="student-telegram-step">
                                    <span class="student-telegram-step__num">2</span>
                                    <div>
                                        <strong>اضغط Start / بدء</strong>
                                        <p class="text-muted fs-13 mb-0">سيتم ربط حسابك تلقائياً دون إدخال أي بيانات.</p>
                                    </div>
                                </div>
                                <div class="student-telegram-step">
                                    <span class="student-telegram-step__num">3</span>
                                    <div>
                                        <strong>ارجع لهذه الصفحة وحدّثها</strong>
                                        <p class="text-muted fs-13 mb-0">ستظهر حالة «مرتبط» فور اكتمال الربط.</p>
                                    </div>
                                </div>
                            </div>

                            @if($linkUrl)
                                <div class="d-flex flex-wrap gap-2 mt-4">
                                    <a href="{{ $linkUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary rounded-pill px-4" style="background: linear-gradient(135deg, #229ED9, #0088cc); border: none;">
                                        <i class="ri-telegram-line me-1"></i>فتح البوت في Telegram
                                    </a>
                                    <button type="button" class="btn btn-outline-secondary rounded-pill" onclick="window.location.reload()">
                                        <i class="fe fe-refresh-cw me-1"></i>تحديث الحالة
                                    </button>
                                </div>
                            @else
                                <div class="alert alert-warning mt-4 mb-0">
                                    Bot Username غير مُعرّف — تواصل مع الإدارة لإكمال إعداد Telegram.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if($linkUrl)
                    <div class="col-lg-5">
                        <div class="card custom-card student-quizzes-panel text-center">
                            <div class="card-body">
                                <h6 class="mb-3">أو امسح رمز QR</h6>
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($linkUrl) }}"
                                     alt="QR code لربط Telegram"
                                     class="img-fluid rounded mb-3"
                                     width="220"
                                     height="220">
                                <p class="text-muted fs-12 mb-0">امسح الرمز من هاتفك لفتح البوت مباشرة</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

    </div>
</div>
@endsection

@push('styles')
<style>
    .student-telegram-step {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px dashed rgba(0,0,0,.08);
    }
    .student-telegram-step:last-child { border-bottom: 0; }
    .student-telegram-step__num {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #229ED9, #0088cc);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        flex-shrink: 0;
    }
</style>
@endpush
