@extends('admin.pages.evolution-api.layout')

@php
    $evoPageTitle = 'Evolution Instances';
    $evoTitle = 'مجموعة Instances';
    $evoSubtitle = 'أضف عدة أرقام يدوياً، اختر الافتراضي، وفعّل التبديل بينهم';
    $evoBreadcrumb = 'Instances';
@endphp

@section('evo-content')
@if($error ?? null)
    <div class="alert alert-danger border-0 shadow-sm mb-3">{{ $error }}</div>
@endif

@if(($rotationPoolCount ?? 0) > 0)
    <div class="alert alert-info border-0 shadow-sm mb-3">
        <i class="ri-shuffle-line me-2"></i>
        <strong>التبديل التلقائي:</strong> {{ $rotationPoolCount }} جلسة نشطة في مجموعة الإرسال.
    </div>
@endif

{{-- 1) الاتصال العام --}}
<div class="card custom-card border-0 shadow-sm mb-4">
    <div class="card-header border-0 pb-0">
        <div class="card-title mb-0"><i class="ri-plug-line me-2 text-success"></i>① بيانات الاتصال العامة</div>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            نفس بيانات صفحة <a href="{{ route('admin.evolution-api.settings.index') }}">الإعدادات</a>.
            تُستخدم لكل الـ instances ما لم تُحدّد بيانات خاصة لكل واحد.
        </p>
        <form action="{{ route('admin.evolution-api.instances.connection') }}" method="POST" class="row g-3">
            @csrf
            <div class="col-md-6">
                <label class="form-label fw-semibold">رابط Evolution API</label>
                <input type="url" name="evolution_base_url" class="form-control" required
                       value="{{ old('evolution_base_url', $settings['evolution_base_url'] ?? '') }}"
                       placeholder="https://evo.example.com">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">API Key</label>
                <input type="password" name="evolution_api_key" class="form-control"
                       placeholder="@if($hasApiKey) اتركه فارغاً للإبقاء على المفتاح @else الصق المفتاح @endif">
                @if($hasApiKey)<div class="form-text text-success">المفتاح العام محفوظ</div>@endif
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-outline-success"><i class="ri-save-line me-1"></i> حفظ الاتصال العام</button>
            </div>
        </form>
    </div>
</div>

{{-- 2) إضافة يدوية --}}
<div class="card custom-card border-0 shadow-sm mb-4">
    <div class="card-header border-0 pb-0">
        <div class="card-title mb-0"><i class="ri-add-circle-line me-2 text-primary"></i>② إضافة Instance يدوياً (نسخ ولصق)</div>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-lg-6">
                <h6 class="fw-semibold">instance واحد</h6>
                <p class="text-muted small">انسخ الاسم من Evolution Manager كما هو (مع المسافات).</p>
                <form action="{{ route('admin.evolution-api.instances.register-manual') }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-12">
                        <label class="form-label small">اسم Instance <span class="text-danger">*</span></label>
                        <input type="text" name="instance_name" class="form-control" required maxlength="150"
                               placeholder="whatsapp ClaudSoft 2" list="evo-names-list">
                    </div>
                    <div class="col-12">
                        <label class="form-label small">ملاحظة (اختياري)</label>
                        <input type="text" name="label" class="form-control" maxlength="150" placeholder="رقم المبيعات">
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Base URL خاص (اختياري)</label>
                        <input type="url" name="evolution_base_url" class="form-control" placeholder="اتركه فارغاً لاستخدام العام">
                    </div>
                    <div class="col-12">
                        <label class="form-label small">API Key خاص (اختياري)</label>
                        <input type="password" name="evolution_api_key" class="form-control" placeholder="اتركه فارغاً لاستخدام العام">
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="verify_connection" value="1" id="verify_one">
                            <label class="form-check-label small" for="verify_one">تحقق من الاتصال الآن (إن وُجدت بيانات API)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="set_as_default" value="1" id="default_one">
                            <label class="form-check-label small" for="default_one">تعيين كـ Instance افتراضي</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary w-100"><i class="ri-add-line me-1"></i> إضافة للقائمة</button>
                    </div>
                </form>
            </div>
            <div class="col-lg-6">
                <h6 class="fw-semibold">عدة instances دفعة واحدة</h6>
                <p class="text-muted small">سطر واحد لكل اسم — يُضافون جميعاً دون حذف الموجود.</p>
                <form action="{{ route('admin.evolution-api.instances.register-bulk') }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <textarea name="instance_names" class="form-control font-monospace" rows="7" required
                                  placeholder="whatsapp ClaudSoft&#10;whatsapp ClaudSoft 2&#10;whatsapp Sales"></textarea>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="set_as_default_first" value="1" id="default_first">
                        <label class="form-check-label small" for="default_first">تعيين الأول في القائمة كافتراضي</label>
                    </div>
                    <button class="btn btn-outline-primary w-100"><i class="ri-file-list-3-line me-1"></i> إضافة الكل</button>
                </form>
            </div>
        </div>
        <datalist id="evo-names-list">
            @foreach($instances as $inst)
                <option value="{{ $inst->instance_name }}"></option>
            @endforeach
        </datalist>
    </div>
</div>

{{-- 3) إنشاء عبر API --}}
<div class="card custom-card border-0 shadow-sm mb-4">
    <div class="card-header border-0 pb-0">
        <div class="card-title mb-0"><i class="ri-qr-code-line me-2 text-success"></i>③ إنشاء جديد على Evolution + QR</div>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">يتطلب اتصال API صحيحاً. يُنشئ الجلسة على السيرفر ثم يفتح صفحة QR.</p>
        <form action="{{ route('admin.evolution-api.instances.store') }}" method="POST" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-8">
                <label class="form-label small">اسم Instance الجديد</label>
                <input type="text" name="instanceName" class="form-control" required maxlength="150" placeholder="whatsapp ClaudSoft 3">
            </div>
            <div class="col-md-4">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="set_as_default" value="1" id="create_default">
                    <label class="form-check-label small" for="create_default">افتراضي</label>
                </div>
                <button class="btn btn-success w-100"><i class="ri-add-line me-1"></i> إنشاء + QR</button>
            </div>
        </form>
    </div>
</div>

{{-- 4) القائمة --}}
<div class="card custom-card group-show-members-card border-0 shadow-sm mb-4">
    <div class="card-header border-0 pb-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="card-title mb-0"><i class="ri-list-check me-2 text-success"></i>④ قائمة المجموعة ({{ $instances->count() }})</div>
        <form action="{{ route('admin.evolution-api.instances.sync') }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-sm btn-outline-success"><i class="ri-refresh-line me-1"></i> مزامنة من API (لا تحذف اليدوي)</button>
        </form>
    </div>
    <div class="card-body">
        @if($defaultInstanceName ?? '')
            <div class="alert alert-light border small py-2 mb-3">
                <i class="ri-star-line text-warning me-1"></i>
                الافتراضي: <strong>{{ $defaultInstanceName }}</strong>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>الاسم</th>
                        <th>ملاحظة</th>
                        <th>المصدر</th>
                        <th>الاتصال</th>
                        <th>الرقم</th>
                        <th>التبديل</th>
                        <th class="text-end">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($instances as $instance)
                    <tr class="{{ $instance->is_default ? 'table-success bg-success-transparent' : '' }}">
                        <td>
                            <span class="fw-semibold">{{ $instance->instance_name }}</span>
                            @if($instance->is_default)
                                <span class="badge bg-success ms-1">افتراضي</span>
                            @endif
                            @if($instance->hasCustomCredentials())
                                <span class="badge bg-info-transparent text-info ms-1" title="Base URL أو API خاص">API خاص</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $instance->label ?? '—' }}</td>
                        <td>
                            @if($instance->is_manual)
                                <span class="badge bg-primary-transparent text-primary">يدوي</span>
                            @else
                                <span class="badge bg-secondary-transparent text-secondary">API</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $instance->isConnected() ? 'success' : 'secondary' }}-transparent text-{{ $instance->isConnected() ? 'success' : 'secondary' }}">
                                {{ $instance->connection_status }}
                            </span>
                        </td>
                        <td>{{ $instance->phone_number ?? '—' }}</td>
                        <td>
                            @if($instance->isConnected())
                                <form action="{{ route('admin.evolution-api.instances.toggle-rotation', $instance->instance_name) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $instance->rotation_enabled ? 'btn-success' : 'btn-outline-secondary' }}">
                                        <i class="ri-shuffle-line"></i> {{ $instance->rotation_enabled ? 'مفعّل' : 'معطّل' }}
                                    </button>
                                </form>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            @if(!$instance->is_default)
                                <form action="{{ route('admin.evolution-api.instances.set-default', $instance->instance_name) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success" title="تعيين افتراضي"><i class="ri-star-line"></i></button>
                                </form>
                            @endif
                            <a href="{{ route('admin.evolution-api.instances.connect', $instance->instance_name) }}" class="btn btn-sm btn-outline-primary" title="QR"><i class="ri-qr-code-line"></i></a>
                            <form action="{{ route('admin.evolution-api.instances.destroy', $instance->instance_name) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف من القائمة؟')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="حذف"><i class="ri-delete-bin-line"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="ri-inbox-line fs-24 d-block mb-2"></i>
                            لا توجد instances. أضفها يدوياً في القسم ② أعلاه.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
