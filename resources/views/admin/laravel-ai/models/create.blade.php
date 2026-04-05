@extends('admin.layouts.master')

@section('page-title')
    إضافة موديل Laravel AI SDK
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إضافة موديل Laravel AI SDK</h5>
            </div>
            <div>
                <a href="{{ route('admin.ai-sdk.models.index') }}" class="btn btn-secondary btn-sm">رجوع</a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form action="{{ route('admin.ai-sdk.models.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">الاسم <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="lai_provider">المزود <span class="text-danger">*</span></label>
                                    <select name="provider" id="lai_provider" class="form-select" required>
                                        <option value="">—</option>
                                        @foreach($providers as $k => $label)
                                            <option value="{{ $k }}" @selected(old('provider') === $k)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">قيم متوافقة مع Laravel AI Lab (مثل gemini وليس google).</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="lai_model">معرف الموديل عند المزود <span class="text-danger">*</span></label>
                                    <input type="text" name="model" id="lai_model" class="form-control" value="{{ old('model') }}" required placeholder="gpt-4o-mini">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="lai_api_key">مفتاح API <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="api_key" id="lai_api_key" class="form-control" value="{{ old('api_key') }}" required autocomplete="new-password">
                                    <button type="button" class="btn btn-outline-primary" id="lai_test_connection_btn">
                                        <i class="fas fa-vial me-1"></i> اختبار الاتصال
                                    </button>
                                </div>
                                <div id="lai_test_result" class="mt-2"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="lai_base_url">Base URL (اختياري)</label>
                                <input type="text" name="base_url" id="lai_base_url" class="form-control" value="{{ old('base_url') }}" placeholder="https://...">
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">الأولوية</label>
                                    <input type="number" name="priority" class="form-control" value="{{ old('priority', 0) }}" min="0">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="lai_max_tokens">حد رموز الإكمال (max_tokens)</label>
                                    <input type="number" name="max_tokens" id="lai_max_tokens" class="form-control" value="{{ old('max_tokens', 8192) }}" min="1" max="200000" required>
                                    <small class="text-muted">يحدد أقصى ما يولّده الموديل في طلب واحد (مقالات/توثيق طويلة ↑).</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">temperature</label>
                                    <input type="number" name="temperature" class="form-control" value="{{ old('temperature', 0.7) }}" step="0.01" min="0" max="2" required>
                                </div>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" @checked(old('is_active', true))>
                                <label class="form-check-label" for="is_active">نشط</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">القدرات (اختياري)</label>
                                <div class="row">
                                    @foreach($capabilities as $key => $label)
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="capabilities[]" value="{{ $key }}" id="cap_{{ $key }}"
                                                    @checked(is_array(old('capabilities')) && in_array($key, old('capabilities'), true))>
                                                <label class="form-check-label" for="cap_{{ $key }}">{{ $label }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">حفظ</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('lai_test_connection_btn');
    const resultEl = document.getElementById('lai_test_result');
    if (!btn || !resultEl) return;

    btn.addEventListener('click', function() {
        const provider = document.getElementById('lai_provider')?.value || '';
        const model = document.getElementById('lai_model')?.value?.trim() || '';
        const apiKey = document.getElementById('lai_api_key')?.value || '';
        const baseUrl = document.getElementById('lai_base_url')?.value?.trim() || '';

        if (!apiKey.trim()) {
            resultEl.innerHTML = '<div class="alert alert-warning alert-dismissible fade show" role="alert"><strong>تنبيه:</strong> أدخل مفتاح API أولاً<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
            return;
        }
        if (!provider) {
            resultEl.innerHTML = '<div class="alert alert-warning alert-dismissible fade show" role="alert"><strong>تنبيه:</strong> اختر المزود<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
            return;
        }
        if (!model) {
            resultEl.innerHTML = '<div class="alert alert-warning alert-dismissible fade show" role="alert"><strong>تنبيه:</strong> أدخل معرف الموديل<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
            return;
        }

        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> جاري الاختبار...';
        resultEl.innerHTML = '';

        fetch(@json(route('admin.ai-sdk.models.test-temp')), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                provider: provider,
                model: model,
                api_key: apiKey,
                base_url: baseUrl || null
            })
        })
        .then(function(r) { return r.json().then(function(data) { return { ok: r.ok, status: r.status, data: data }; }); })
        .then(function(res) {
            btn.disabled = false;
            btn.innerHTML = original;
            const data = res.data;
            const ms = data.response_time_ms != null ? data.response_time_ms : data.latency_ms;
            if (res.ok && data.success) {
                resultEl.innerHTML = '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>نجح الاختبار</strong><br>' +
                    (data.message || '') + (ms != null ? '<br>وقت الاستجابة: ' + ms + ' ms' : '') +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
            } else {
                let msg = (data && data.message) ? data.message : 'فشل الطلب';
                if (data && data.errors) {
                    msg = Object.values(data.errors).flat().join(' ');
                }
                resultEl.innerHTML = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>فشل الاختبار</strong><br>' +
                    msg + (ms != null ? '<br>الزمن: ' + ms + ' ms' : '') +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
            }
        })
        .catch(function(e) {
            btn.disabled = false;
            btn.innerHTML = original;
            resultEl.innerHTML = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>خطأ شبكة</strong><br>' + (e.message || 'تعذر الاتصال') + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        });
    });
});
</script>
@endsection
