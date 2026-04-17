@extends('admin.layouts.master')

@section('page-title')
    تفاصيل إرسال Flaxxa #{{ $message->id }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        @include('admin.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تفاصيل السجل #{{ $message->id }}</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.flaxxa-wapi.messages.index') }}">سجل Flaxxa</a></li>
                        <li class="breadcrumb-item active">#{{ $message->id }}</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.flaxxa-wapi.messages.index') }}" class="btn btn-outline-secondary">رجوع</a>
        </div>

        @include('admin.pages.flaxxa-wapi._nav')

        <div class="row">
            <div class="col-lg-6">
                <div class="card custom-card mb-3">
                    <div class="card-header"><div class="card-title">بيانات عامة</div></div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">الهاتف / المرجع</dt>
                            <dd class="col-sm-8"><code>{{ $message->phone }}</code></dd>
                            <dt class="col-sm-4">النوع</dt>
                            <dd class="col-sm-8">{{ $message->type?->value }}</dd>
                            <dt class="col-sm-4">الحالة</dt>
                            <dd class="col-sm-8"><strong>{{ $message->status?->value }}</strong></dd>
                            <dt class="col-sm-4">أنشئ في</dt>
                            <dd class="col-sm-8">{{ $message->created_at }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card custom-card mb-3">
                    <div class="card-header"><div class="card-title">المحتوى (طلب الإرسال)</div></div>
                    <div class="card-body">
                        <pre class="bg-light p-3 rounded small mb-0" style="max-height: 320px; overflow:auto; direction:ltr; text-align:left;">{{ json_encode($message->content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
            </div>
        </div>

        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">الاستجابة / السجلات</div>
                @php($jsonResp = is_array($message->response['json'] ?? null) ? $message->response['json'] : [])
                @if(($jsonResp['message_id'] ?? null))
                    <button type="button" id="flaxxa-check-status-btn" class="btn btn-sm btn-outline-primary">
                        <i class="ri-refresh-line me-1"></i> تحقّق من حالة التوصيل
                    </button>
                @endif
            </div>
            <div class="card-body">
                <div id="flaxxa-check-status-result" class="alert d-none mb-3" role="alert"></div>

                @if(empty($message->response))
                    <p class="text-muted mb-0">لا توجد استجابة مسجّلة بعد (قد يكون الطلب ما زال في الطابور).</p>
                @else
                    <pre class="bg-light p-3 rounded small mb-0" style="max-height: 400px; overflow:auto; direction:ltr; text-align:left;">{{ json_encode($message->response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                @endif
            </div>
        </div>

        @if(($message->type?->value ?? null) === 'message')
            <div class="alert alert-warning mt-3 small">
                <i class="ri-information-line me-1"></i>
                ملاحظة مهمة: إرسال <strong>نصّ حر</strong> عبر واتساب الرسمي يصل فقط إذا كان الرقم المستقبِل قد <strong>راسلك خلال آخر 24 ساعة</strong>.
                للرسائل الأولى أو خارج هذه النافذة استخدم <a href="{{ route('admin.flaxxa-wapi.send.template') }}">إرسال قالب معتمد (Template)</a>.
                راجع <a href="https://documenter.getpostman.com/view/38526086/2sB3HgPiJx" target="_blank" rel="noopener">توثيق Flaxxa WAPI</a>.
            </div>
        @endif
    </div>
</div>

@php($jsonRespForJs = is_array($message->response['json'] ?? null) ? $message->response['json'] : [])
@if(($jsonRespForJs['message_id'] ?? null))
<script>
(function () {
    const btn = document.getElementById('flaxxa-check-status-btn');
    const out = document.getElementById('flaxxa-check-status-result');
    if (!btn || !out) return;

    btn.addEventListener('click', async function () {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrf) {
            alert('تعذّر قراءة رمز CSRF.');
            return;
        }

        btn.disabled = true;
        const orig = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> جاري الاستعلام...';
        out.classList.add('d-none');

        try {
            const res = await fetch(@json(route('admin.flaxxa-wapi.messages.check-status', $message)), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            });
            const raw = await res.text();
            let data;
            try { data = JSON.parse(raw); } catch { data = { success: false, message: raw.slice(0, 300) }; }

            const ok = data.success === true;
            const pretty = JSON.stringify(data.data || data, null, 2);
            out.className = 'alert mb-3 ' + (ok ? 'alert-info' : 'alert-danger');
            out.classList.remove('d-none');
            out.innerHTML = (data.message ? '<div class="mb-2">'+data.message+'</div>' : '')
                + '<pre class="mb-0 small" style="direction:ltr;text-align:left;">'+pretty.replace(/[&<>]/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;'}[s]))+'</pre>';
        } catch (e) {
            out.className = 'alert alert-danger mb-3';
            out.classList.remove('d-none');
            out.textContent = 'خطأ: ' + (e && e.message ? e.message : 'غير معروف');
        } finally {
            btn.disabled = false;
            btn.innerHTML = orig;
        }
    });
})();
</script>
@endif
@endsection
