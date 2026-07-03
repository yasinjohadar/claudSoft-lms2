@extends('admin.pages.telegram.layout')

@php
    $tgPageTitle = 'نشر في Telegram';
    $tgTitle = 'نشر في مجموعة / قناة';
    $tgSubtitle = 'أرسل إعلاناً إلى مجموعة مربوطة.';
    $breadcrumb = 'نشر';
@endphp

@section('tg-content')
<div class="row g-4">
    <div class="col-lg-7">
        <div class="tg-form-section">
            <form method="POST" action="{{ route('admin.telegram.groups.post.store') }}">
                @csrf
                <div class="tg-form-section__title">
                    <span class="tg-form-section__icon"><i class="ri-chat-upload-line"></i></span>
                    رسالة للمجموعة
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">الوجهة</label>
                    <select name="telegram_chat_id" class="form-select" required>
                        @forelse($links as $link)
                            <option value="{{ $link->telegram_chat_id }}">{{ $link->title }} ({{ $link->link_type }})</option>
                        @empty
                            <option value="" disabled>لا توجد مجموعات مربوطة — <a href="{{ route('admin.telegram.groups.link') }}">اربط أولاً</a></option>
                        @endforelse
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">الرسالة</label>
                    <textarea name="message" class="form-control" rows="6" required></textarea>
                </div>
                <button type="submit" class="btn text-white px-4" style="background: linear-gradient(135deg, #229ED9, #0088cc);" @disabled($links->isEmpty())>
                    <i class="ri-send-plane-fill me-1"></i>نشر
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
