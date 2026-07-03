@extends('admin.pages.telegram.layout')

@php
    $tgPageTitle = 'ربط مجموعة Telegram';
    $tgTitle = 'ربط مجموعة / قناة';
    $tgSubtitle = 'اربط مجموعة Telegram بمجموعة تسجيل في المنصة.';
    $breadcrumb = 'ربط مجموعة';
@endphp

@section('tg-content')
<div class="row g-4">
    <div class="col-lg-7">
        <div class="tg-form-section">
            <div class="tg-form-section__title">
                <span class="tg-form-section__icon"><i class="ri-link"></i></span>
                ربط يدوي
            </div>
            <form method="POST" action="{{ route('admin.telegram.groups.link.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">group_id</label>
                        <input type="number" name="group_id" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">نوع الربط</label>
                        <select name="link_type" class="form-select">
                            <option value="group">مجموعة</option>
                            <option value="channel">قناة</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Chat ID</label>
                        <input type="text" name="telegram_chat_id" class="form-control" dir="ltr" required placeholder="-100...">
                    </div>
                    <div class="col-12">
                        <label class="form-label">رابط دعوة</label>
                        <input type="url" name="invite_link" class="form-control" dir="ltr">
                    </div>
                </div>
                <button type="submit" class="btn text-white mt-3" style="background: linear-gradient(135deg, #229ED9, #0088cc);">ربط</button>
            </form>
        </div>

        <div class="tg-form-section">
            <div class="tg-form-section__title">
                <span class="tg-form-section__icon"><i class="ri-terminal-box-line"></i></span>
                ربط عبر /link_group
            </div>
            <form method="POST" action="{{ route('admin.telegram.groups.prepare-link') }}" class="mb-3">
                @csrf
                <div class="row g-2">
                    <div class="col-md-6"><input type="number" name="group_id" class="form-control" placeholder="group_id" required></div>
                    <div class="col-md-6"><input type="text" name="telegram_chat_id" class="form-control" dir="ltr" placeholder="chat_id"></div>
                </div>
                <button class="btn btn-outline-info mt-2 w-100">تحضير ثم أرسل /link_group في المجموعة</button>
            </form>
            <form method="POST" action="{{ route('admin.telegram.groups.auto-create') }}">
                @csrf
                <div class="input-group">
                    <input type="number" name="group_id" class="form-control" placeholder="group_id" required>
                    <button class="btn btn-success">إنشاء عبر MTProto</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="tg-guide-box">
            <h6 class="fw-bold mb-3">التعليمات</h6>
            <ol class="small mb-0">
                @foreach($instructions as $step)
                    <li class="mb-2">{{ $step }}</li>
                @endforeach
            </ol>
        </div>
    </div>
</div>
@endsection
