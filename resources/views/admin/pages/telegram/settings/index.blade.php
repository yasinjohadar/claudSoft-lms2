@extends('admin.pages.telegram.layout')

@php
    $tgPageTitle = 'إعدادات Telegram';
    $tgTitle = 'إعدادات Telegram Bot';
    $tgSubtitle = 'Bot Token من BotFather، Webhook، فواصل زمنية، وMTProto Bridge الاختياري.';
    $breadcrumb = 'الإعدادات';
    $tgBadge = ($settings['telegram_enabled'] ?? false)
        ? '<i class="ri-checkbox-circle-line me-1"></i> مفعّل'
        : '<i class="ri-close-circle-line me-1"></i> معطّل';
@endphp

@section('tg-content')
<div class="row g-4">
    <div class="col-lg-8">
        <form method="POST" action="{{ route('admin.telegram.settings.update') }}">
            @csrf

            <div class="tg-form-section">
                <div class="tg-form-section__title">
                    <span class="tg-form-section__icon"><i class="ri-robot-line"></i></span>
                    Bot API
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="telegram_enabled" value="1" id="telegram_enabled" @checked(old('telegram_enabled', $settings['telegram_enabled']))>
                    <label class="form-check-label fw-semibold" for="telegram_enabled">تفعيل Telegram</label>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Bot Token</label>
                        <input type="password" class="form-control" name="bot_token" dir="ltr" placeholder="اتركه فارغاً للإبقاء على القيمة الحالية">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Bot Username</label>
                        <input type="text" class="form-control" name="bot_username" dir="ltr" value="{{ old('bot_username', $settings['bot_username']) }}" placeholder="@MyBot">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Webhook Secret</label>
                        <input type="password" class="form-control" name="webhook_secret" dir="ltr">
                    </div>
                </div>
            </div>

            <div class="tg-form-section">
                <div class="tg-form-section__title">
                    <span class="tg-form-section__icon"><i class="ri-chat-smile-2-line"></i></span>
                    الرد التلقائي
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="auto_reply" value="1" id="auto_reply" @checked(old('auto_reply', $settings['auto_reply']))>
                    <label class="form-check-label" for="auto_reply">تفعيل الرد على الرسائل الواردة</label>
                </div>
                <textarea class="form-control" name="auto_reply_message" rows="3">{{ old('auto_reply_message', $settings['auto_reply_message']) }}</textarea>
            </div>

            <div class="tg-form-section">
                <div class="tg-form-section__title">
                    <span class="tg-form-section__icon"><i class="ri-time-line"></i></span>
                    فواصل زمنية
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">بين الرسائل (ث)</label>
                        <input type="number" class="form-control" name="delay_between_messages" min="1" max="60" value="{{ old('delay_between_messages', $settings['delay_between_messages']) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">حد أدنى عشوائي</label>
                        <input type="number" class="form-control" name="min_delay" min="1" value="{{ old('min_delay', $settings['min_delay']) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">حد أقصى عشوائي</label>
                        <input type="number" class="form-control" name="max_delay" min="1" value="{{ old('max_delay', $settings['max_delay']) }}">
                    </div>
                </div>
                <div class="form-check form-switch mt-3">
                    <input class="form-check-input" type="checkbox" name="random_delay_enabled" value="1" @checked(old('random_delay_enabled', $settings['random_delay_enabled']))>
                    <label class="form-check-label">فواصل عشوائية</label>
                </div>
            </div>

            <div class="tg-form-section">
                <div class="tg-form-section__title">
                    <span class="tg-form-section__icon"><i class="ri-plug-line"></i></span>
                    MTProto Bridge (اختياري)
                </div>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Bridge Base URL</label>
                        <input type="url" class="form-control" name="bridge_base_url" dir="ltr" value="{{ old('bridge_base_url', $settings['bridge_base_url']) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">API Key</label>
                        <input type="password" class="form-control" name="bridge_api_key" dir="ltr">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-lg text-white px-4" style="background: linear-gradient(135deg, #229ED9, #0088cc);">
                <i class="ri-save-line me-2"></i>حفظ الإعدادات
            </button>
        </form>
    </div>

    <div class="col-lg-4">
        <div class="tg-form-section mb-3">
            <div class="tg-form-section__title">
                <span class="tg-form-section__icon"><i class="ri-webhook-line"></i></span>
                Webhook
            </div>
            <p class="small text-muted mb-2">URL للتسجيل في Telegram:</p>
            <code class="d-block small bg-light p-2 rounded mb-3 user-select-all" dir="ltr">{{ $webhookUrl }}</code>
            <form method="POST" action="{{ route('admin.telegram.settings.activate-webhook') }}">@csrf
                <button class="btn btn-outline-info w-100 mb-2"><i class="ri-link me-1"></i>تفعيل Webhook</button>
            </form>
            <button type="button" class="btn btn-outline-success w-100 mb-2" id="testBotBtn"><i class="ri-check-line me-1"></i>اختبار Bot</button>
            <button type="button" class="btn btn-outline-secondary w-100" id="testBridgeBtn"><i class="ri-server-line me-1"></i>اختبار Bridge</button>
            <div id="testResult" class="mt-3 small fw-semibold text-info"></div>
        </div>

        <div class="tg-guide-box">
            <h6 class="fw-bold mb-2">خطوات سريعة</h6>
            <ol class="small mb-0">
                <li>أنشئ بوت من <strong>@BotFather</strong></li>
                <li>الصق Token واحفظ</li>
                <li>فعّل Webhook</li>
                <li>اطلب من الطلاب الربط من ملفهم</li>
            </ol>
        </div>
    </div>
</div>
@endsection

@section('tg-scripts')
<script>
document.getElementById('testBotBtn')?.addEventListener('click', async () => {
    const r = await fetch(@json(route('admin.telegram.settings.test-connection')), { method: 'POST', headers: { 'X-CSRF-TOKEN': @json(csrf_token()) } });
    const j = await r.json();
    document.getElementById('testResult').textContent = j.message;
});
document.getElementById('testBridgeBtn')?.addEventListener('click', async () => {
    const r = await fetch(@json(route('admin.telegram.settings.test-bridge')), { method: 'POST', headers: { 'X-CSRF-TOKEN': @json(csrf_token()) } });
    const j = await r.json();
    document.getElementById('testResult').textContent = j.message;
});
</script>
@endsection
