@extends('admin.layouts.master')

@section('page-title')
    إعدادات Flaxxa (WAPI)
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        @include('admin.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إعدادات Flaxxa</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.flaxxa-wapi.messages.index') }}">Flaxxa</a></li>
                        <li class="breadcrumb-item active">الإعدادات</li>
                    </ol>
                </nav>
            </div>
        </div>

        @include('admin.pages.flaxxa-wapi._nav')

        <div class="row">
            <div class="col-xl-8">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">بيانات الاتصال بـ wapi.flaxxa.com</div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">
                            يتم تخزين التوكن بشكل <strong>مشفّر</strong> في قاعدة البيانات. إن وُجد توكن هنا، يُستخدم بدلاً من متغير البيئة
                            <code>WHATSAPP_TOKEN</code>. يترك حقل التوكن <strong>فارغاً</strong> إذا أردت الإبقاء على التوكن المحفوظ دون تغيير.
                        </p>
                        <p class="text-muted mb-4 small border-start border-primary border-3 ps-3">
                            <strong>هوية المرسل (Flaxxa WAPI):</strong> طلبات القوالب والرسائل تُرسل مع الحقل <code>token</code> فقط؛ أي أن الرقم أو حساب الواتساب الظاهر للمستلم هو
                            <strong>الجلسة المرتبطة بهذا التوكن</strong> في لوحة Flaxxa، وليس حقل «مرسل افتراضي» منفصل في هذه الشاشة (ذلك الحقل يخص مسار Meta آخر إن وُجد).
                        </p>

                        <form action="{{ route('admin.flaxxa-wapi.settings.update') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">توكن Flaxxa <span class="text-danger">*</span> (من لوحة المزود)</label>
                                <input type="password" name="wapi_token" id="flaxxa_wapi_token" class="form-control @error('wapi_token') is-invalid @enderror" autocomplete="new-password" placeholder="@if($hasToken) أدخل توكناً جديداً فقط لتغيير القيمة الحالية @else الصق التوكن هنا @endif">
                                @error('wapi_token')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="form-text">
                                    الحالة الحالية:
                                    @if($hasToken)
                                        <span class="text-success"><i class="ri-checkbox-circle-line"></i> توكن محفوظ — اترك الحقل فارغاً للإبقاء عليه</span>
                                    @else
                                        <span class="text-warning"><i class="ri-alert-line"></i> لم يُعرّف توكن بعد — أدخل التوكن أو عيّن <code>WHATSAPP_TOKEN</code> في .env</span>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">عنوان واجهة الـ API (اختياري)</label>
                                <input type="url" name="wapi_base_url" id="flaxxa_wapi_base_url" class="form-control @error('wapi_base_url') is-invalid @enderror" value="{{ old('wapi_base_url', $wapi_base_url) }}" placeholder="https://wapi.flaxxa.com/api/v1">
                                @error('wapi_base_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="form-text">
                                    اتركه فارغاً لاستخدام القيمة الافتراضية أو <code>WHATSAPP_WAPI_BASE_URL</code> في .env.
                                    <strong>لا</strong> تضع هنا رابط موقعك أو لوحة التحكم — فقط عنوان <strong>خادم Flaxxa wapi</strong> الذي يزوّدك به المزود (مثل <code>https://wapi.flaxxa.com</code> أو النطاق الذي يعطيك إياه Flaxxa). يُلحق النظام <code>/api/v1</code> تلقائياً عند الحاجة.
                                    <a href="https://documenter.getpostman.com/view/38526086/2sB3HgPiJx" target="_blank" rel="noopener">مرجع Postman</a>.
                                </div>
                            </div>

                            <div id="flaxxa-test-result" class="mb-3 d-none" role="alert"></div>

                            <button type="button" id="flaxxa-test-btn" class="btn btn-outline-info me-2">
                                <i class="ri-plug-line me-1"></i> اختبار الاتصال
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-1"></i> حفظ
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card border-info">
                    <div class="card-header bg-info-transparent">
                        <div class="card-title text-info mb-0"><i class="ri-play-circle-line me-1"></i> للبدء بالإرسال</div>
                    </div>
                    <div class="card-body small">
                        <ol class="ps-3 mb-0">
                            <li class="mb-2">عرّف التوكن هنا أو في <code>.env</code> كـ <code>WHATSAPP_TOKEN</code>.</li>
                            <li class="mb-2">شغّل عامل الطابور: <code>php artisan queue:work</code> (أو Supervisor في الإنتاج).</li>
                            <li class="mb-2">تأكد أن <code>QUEUE_CONNECTION</code> ليس <code>sync</code> إن أردت إرسالاً حقيقياً في الخلفية.</li>
                            <li>أرسل تجريبياً من <a href="{{ route('admin.flaxxa-wapi.send.message') }}">إرسال نص Flaxxa</a> ثم راجع <a href="{{ route('admin.flaxxa-wapi.messages.index') }}">السجل</a>.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const btn = document.getElementById('flaxxa-test-btn');
    const out = document.getElementById('flaxxa-test-result');
    if (!btn || !out) return;

    btn.addEventListener('click', async function () {
        const tokenEl = document.getElementById('flaxxa_wapi_token');
        const baseEl = document.getElementById('flaxxa_wapi_base_url');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrf) {
            alert('تعذّر قراءة رمز CSRF.');
            return;
        }

        btn.disabled = true;
        const orig = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> جاري الاختبار...';

        out.classList.add('d-none');

        try {
            const res = await fetch(@json(route('admin.flaxxa-wapi.settings.test-connection')), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    wapi_token: tokenEl ? tokenEl.value : '',
                    wapi_base_url: baseEl ? baseEl.value : '',
                }),
            });

            const raw = await res.text();
            let data = {};
            try {
                data = raw ? JSON.parse(raw) : {};
            } catch (e) {
                data = { success: false, message: raw.slice(0, 300) || 'استجابة غير متوقعة' };
            }

            const msg = data.message || (res.ok ? 'تم' : 'فشل الطلب');
            const ok = data.success === true;

            out.textContent = msg + (data.http_status ? ' — HTTP ' + data.http_status : '');
            out.classList.remove('d-none');
            out.className = 'mb-3 alert ' + (ok ? 'alert-success' : 'alert-danger');
        } catch (e) {
            out.textContent = 'خطأ: ' + (e && e.message ? e.message : 'غير معروف');
            out.classList.remove('d-none');
            out.className = 'mb-3 alert alert-danger';
        } finally {
            btn.disabled = false;
            btn.innerHTML = orig;
        }
    });
})();
</script>
@endsection
