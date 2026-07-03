@extends('admin.pages.telegram.layout')

@php
    $tgPageTitle = 'إرسال Telegram';
    $tgTitle = 'إرسال رسالة فردية';
    $tgSubtitle = 'أرسل إلى chat_id طالب أو مجموعة مباشرة.';
    $breadcrumb = 'إرسال';
@endphp

@section('tg-content')
<div class="row g-4">
    <div class="col-lg-7">
        <div class="tg-form-section">
            <form method="POST" action="{{ route('admin.telegram.send.store') }}">
                @csrf
                <div class="tg-form-section__title">
                    <span class="tg-form-section__icon"><i class="ri-send-plane-line"></i></span>
                    رسالة جديدة
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Chat ID</label>
                    <input type="text" name="chat_id" class="form-control" dir="ltr" required placeholder="-1001234567890">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">الرسالة</label>
                    <textarea name="message" class="form-control" rows="6" required></textarea>
                </div>
                <button type="submit" class="btn text-white px-4" style="background: linear-gradient(135deg, #229ED9, #0088cc);">
                    <i class="ri-send-plane-fill me-1"></i>إرسال
                </button>
            </form>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="tg-guide-box">
            <h6 class="fw-bold mb-2">Chat ID</h6>
            <p class="small mb-0">للطالب: يظهر بعد ربط Telegram. للمجموعات: رقم سالب يبدأ بـ <code>-100</code>.</p>
        </div>
    </div>
</div>
@endsection
