@extends('admin.layouts.master')

@section('page-title')
    قوالب رسائل واتساب
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">قوالب رسائل واتساب</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.whatsapp-messages.index') }}">رسائل WhatsApp</a></li>
                            <li class="breadcrumb-item active" aria-current="page">قوالب الرسائل</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('admin.whatsapp-templates.create') }}" class="btn btn-primary btn-wave">
                    <i class="fas fa-plus me-2"></i>إضافة قالب جديد
                </a>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">قائمة القوالب</div>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.whatsapp-templates.index') }}" class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <input type="text" name="search" class="form-control" placeholder="بحث بالاسم أو المحتوى..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2">
                                    <select name="type" class="form-select">
                                        <option value="">كل الأنواع</option>
                                        <option value="text" {{ request('type') == 'text' ? 'selected' : '' }}>نص</option>
                                        <option value="template" {{ request('type') == 'template' ? 'selected' : '' }}>قالب Meta</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="is_active" class="form-select">
                                        <option value="">كل الحالات</option>
                                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>نشط</option>
                                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>غير نشط</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">بحث</button>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle table-nowrap mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width: 50px;">#</th>
                                            <th scope="col">الاسم</th>
                                            <th scope="col">المعرّف (slug)</th>
                                            <th scope="col">النوع</th>
                                            <th scope="col">اللغة</th>
                                            <th scope="col">الحالة</th>
                                            <th scope="col">تاريخ الإنشاء</th>
                                            <th scope="col" style="min-width: 160px;">العمليات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($templates as $template)
                                            <tr>
                                                <td>{{ $loop->iteration + ($templates->currentPage() - 1) * $templates->perPage() }}</td>
                                                <td><strong>{{ $template->name }}</strong></td>
                                                <td><code>{{ $template->slug ?? '—' }}</code></td>
                                                <td>
                                                    @if($template->type === 'text')
                                                        <span class="badge bg-info">نص</span>
                                                    @else
                                                        <span class="badge bg-secondary">قالب Meta</span>
                                                    @endif
                                                </td>
                                                <td>{{ $template->language }}</td>
                                                <td>
                                                    @if($template->is_active)
                                                        <span class="badge bg-success">نشط</span>
                                                    @else
                                                        <span class="badge bg-danger">غير نشط</span>
                                                    @endif
                                                </td>
                                                <td>{{ $template->created_at->format('Y-m-d') }}</td>
                                                <td>
                                                    <div class="btn-list">
                                                        <button type="button"
                                                                class="btn btn-sm btn-success btn-wave js-wa-template-test-btn"
                                                                title="اختبار الإرسال"
                                                                data-template-id="{{ $template->id }}"
                                                                data-template-name="{{ $template->name }}"
                                                                data-template-type="{{ $template->type }}"
                                                                data-preview-url="{{ route('admin.whatsapp-templates.test.preview', $template) }}"
                                                                data-send-url="{{ route('admin.whatsapp-templates.test.send', $template) }}">
                                                            <i class="ri-send-plane-line"></i>
                                                        </button>
                                                        <a href="{{ route('admin.whatsapp-templates.edit', $template) }}" class="btn btn-sm btn-info btn-wave" title="تعديل">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('admin.whatsapp-templates.destroy', $template) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا القالب؟');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger btn-wave" title="حذف">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">لا توجد قوالب. <a href="{{ route('admin.whatsapp-templates.create') }}">إضافة قالب جديد</a></td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($templates->hasPages())
                                <div class="d-flex justify-content-center mt-3">
                                    {{ $templates->withQueryString()->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="waTemplateTestModal" tabindex="-1" aria-labelledby="waTemplateTestModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="waTemplateTestModalTitle">
                        <i class="ri-whatsapp-line me-2 text-success"></i>اختبار قالب واتساب
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="alert alert-light border py-2 mb-3">
                        <div class="small mb-1"><strong>القالب:</strong> <span id="waTemplateTestName">—</span></div>
                        <div class="small mb-0 text-muted">تُستخدم قيم تجريبية للمتغيرات (مثل student_name، payment_amount...).</div>
                    </div>

                    <div id="waTemplateTestAlert" class="alert d-none mb-3" role="alert"></div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="waTemplateTestPhone">رقم الواتساب <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="waTemplateTestPhone" dir="ltr"
                               placeholder="+9639xxxxxxxx" autocomplete="tel">
                        <small class="text-muted">أدخل الرقم مع رمز الدولة، مثل: +9639xxxxxxxx</small>
                    </div>

                    @if(($evolutionInstances ?? collect())->isNotEmpty())
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="waTemplateTestInstanceName">Instance Evolution</label>
                            <select id="waTemplateTestInstanceName" class="form-select">
                                <option value="">الافتراضي النشط</option>
                                @foreach($evolutionInstances as $instance)
                                    <option value="{{ $instance->instance_name }}"
                                        @selected(($defaultEvolutionInstance?->instance_name ?? null) === $instance->instance_name)>
                                        {{ $instance->instance_name }}
                                        @if($instance->profile_name)
                                            — {{ $instance->profile_name }}
                                        @endif
                                        @if($instance->is_default) (افتراضي) @endif
                                        @if($instance->connection_status === 'open') (متصل) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div id="waTemplateTestPreviewWrap" class="d-none">
                        <label class="form-label fw-semibold">معاينة الرسالة</label>
                        <div class="border rounded p-3 bg-light wa-template-test-preview" id="waTemplateTestPreviewBody"></div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-outline-success" id="waTemplateTestPreviewBtn">
                        <span class="wa-template-test-btn__label"><i class="fe fe-eye me-1"></i>معاينة</span>
                        <span class="wa-template-test-btn__spinner d-none"><span class="spinner-border spinner-border-sm me-1"></span>جاري التحميل...</span>
                    </button>
                    <button type="button" class="btn btn-success" id="waTemplateTestSendBtn">
                        <span class="wa-template-test-btn__label"><i class="ri-send-plane-line me-1"></i>إرسال الاختبار</span>
                        <span class="wa-template-test-btn__spinner d-none"><span class="spinner-border spinner-border-sm me-1"></span>جاري الإرسال...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<style>
    .wa-template-test-preview {
        white-space: pre-wrap;
        word-break: break-word;
        direction: rtl;
        font-size: 0.95rem;
        line-height: 1.6;
        min-height: 4rem;
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById('waTemplateTestModal');
    if (!modalEl || typeof window.bootstrap === 'undefined') return;

    var modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    var previewUrl = '';
    var sendUrl = '';
    var alertEl = document.getElementById('waTemplateTestAlert');
    var previewWrap = document.getElementById('waTemplateTestPreviewWrap');
    var previewBody = document.getElementById('waTemplateTestPreviewBody');
    var phoneInput = document.getElementById('waTemplateTestPhone');
    var instanceSelect = document.getElementById('waTemplateTestInstanceName');

    function showAlert(message, type) {
        if (!alertEl) return;
        alertEl.className = 'alert alert-' + (type === 'success' ? 'success' : 'danger') + ' mb-3';
        alertEl.textContent = message;
        alertEl.classList.remove('d-none');
    }

    function hideAlert() {
        if (alertEl) alertEl.classList.add('d-none');
    }

    function setBtnLoading(btn, loading) {
        if (!btn) return;
        btn.disabled = loading;
        btn.querySelector('.wa-template-test-btn__label')?.classList.toggle('d-none', loading);
        btn.querySelector('.wa-template-test-btn__spinner')?.classList.toggle('d-none', !loading);
    }

    function payload() {
        var data = new FormData();
        data.append('phone', phoneInput ? phoneInput.value.trim() : '');
        if (instanceSelect && instanceSelect.value) {
            data.append('evolution_instance_name', instanceSelect.value);
        }
        return data;
    }

    document.querySelectorAll('.js-wa-template-test-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            previewUrl = btn.getAttribute('data-preview-url') || '';
            sendUrl = btn.getAttribute('data-send-url') || '';
            var nameEl = document.getElementById('waTemplateTestName');
            if (nameEl) nameEl.textContent = btn.getAttribute('data-template-name') || '—';
            hideAlert();
            if (previewWrap) previewWrap.classList.add('d-none');
            if (previewBody) previewBody.textContent = '';
            modal.show();
        });
    });

    document.getElementById('waTemplateTestPreviewBtn')?.addEventListener('click', function () {
        if (!previewUrl) return;

        var btn = this;
        hideAlert();
        setBtnLoading(btn, true);

        fetch(previewUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: payload(),
        })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
            .then(function (result) {
                if (result.ok && result.data.success) {
                    if (previewBody) previewBody.textContent = result.data.body || '';
                    if (previewWrap) previewWrap.classList.remove('d-none');
                    return;
                }
                showAlert(result.data.message || 'تعذر تحميل المعاينة.', 'error');
            })
            .catch(function () {
                showAlert('تعذر تحميل المعاينة.', 'error');
            })
            .finally(function () {
                setBtnLoading(btn, false);
            });
    });

    document.getElementById('waTemplateTestSendBtn')?.addEventListener('click', function () {
        if (!sendUrl) return;
        if (!phoneInput || !phoneInput.value.trim()) {
            showAlert('يرجى إدخال رقم الواتساب.', 'error');
            return;
        }

        var btn = this;
        hideAlert();
        setBtnLoading(btn, true);

        fetch(sendUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: payload(),
        })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
            .then(function (result) {
                if (result.ok && result.data.success) {
                    showAlert(result.data.message || 'تم الإرسال بنجاح.', 'success');
                    return;
                }
                showAlert(result.data.message || 'فشل الإرسال.', 'error');
            })
            .catch(function () {
                showAlert('تعذر إرسال رسالة الاختبار.', 'error');
            })
            .finally(function () {
                setBtnLoading(btn, false);
            });
    });
});
</script>
@stop
