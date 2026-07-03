@extends('admin.pages.evolution-api.layout')

@php
    $evoPageTitle = 'Evolution Instances';
    $evoTitle = 'Instances';
    $evoSubtitle = 'إنشاء وربط وإدارة جلسات WhatsApp';
    $evoBreadcrumb = 'Instances';
@endphp

@section('evo-content')
@if($error)
    <div class="alert alert-danger border-0 shadow-sm evo-inline-result mb-3" role="alert">
        <div class="d-flex align-items-start gap-2">
            <span class="avatar avatar-sm bg-danger-transparent rounded-circle flex-shrink-0">
                <i class="ri-wifi-off-line fs-18 text-danger"></i>
            </span>
            <div class="flex-grow-1">
                <strong class="d-block mb-1">تعذر الاتصال بـ Evolution API</strong>
                <span>{{ $error }}</span>
                <span class="text-muted small d-block mt-2">
                    تحقق من
                    <a href="{{ route('admin.evolution-api.settings.index') }}" class="alert-link">إعدادات الاتصال</a>
                    (Base URL و apikey)، ثم أعد المحاولة عبر «مزامنة».
                </span>
            </div>
        </div>
    </div>
@endif

@if(($rotationPoolCount ?? 0) > 0)
    <div class="alert alert-info border-0 shadow-sm mb-3">
        <i class="ri-shuffle-line me-2"></i>
        <strong>التبديل التلقائي:</strong>
        {{ $rotationPoolCount }} رقم/جلسة نشطة في مجموعة الإرسال — يُستخدم رقم مختلف مع كل رسالة.
    </div>
@endif

<div class="card custom-card group-show-members-card border-0 shadow-sm mb-4">
    <div class="card-header border-0 pb-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="card-title mb-0"><i class="ri-smartphone-line me-2 text-success"></i>قائمة Instances</div>
        <div class="d-flex gap-2">
            <form action="{{ route('admin.evolution-api.instances.sync') }}" method="POST" class="d-inline">
                @csrf
                <button class="btn btn-sm btn-outline-success"><i class="ri-refresh-line me-1"></i> مزامنة</button>
            </form>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="p-3 border rounded bg-light h-100">
                    <h6 class="fw-semibold mb-2"><i class="ri-link me-1 text-primary"></i> ربط instance موجود</h6>
                    <p class="text-muted small mb-3">أنشأته في Evolution Manager؟ أدخل اسمه <strong>كما هو</strong> ثم اربطه.</p>
                    <form action="{{ route('admin.evolution-api.instances.link') }}" method="POST" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-12">
                            <label class="form-label small fw-semibold mb-1">اسم Instance</label>
                            <input type="text" name="instanceName" class="form-control" placeholder="whatsapp ClaudSoft" required maxlength="150" list="evo-link-suggestions">
                            <datalist id="evo-link-suggestions">
                                @foreach($instances as $inst)
                                    <option value="{{ $inst->instance_name }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="set_as_default" value="1" id="link_set_default">
                                <label class="form-check-label small" for="link_set_default">تعيين كـ Instance افتراضي</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-outline-primary w-100"><i class="ri-plug-line me-1"></i> ربط + مزامنة</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="p-3 border rounded h-100">
                    <h6 class="fw-semibold mb-2"><i class="ri-add-circle-line me-1 text-success"></i> إنشاء instance جديد</h6>
                    <p class="text-muted small mb-3">أدخل الاسم يدوياً (يمكن أن يحتوي مسافات) كما تريد ظهوره في Evolution.</p>
                    <form action="{{ route('admin.evolution-api.instances.store') }}" method="POST" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-12">
                            <label class="form-label small fw-semibold mb-1">اسم Instance الجديد</label>
                            <input type="text" name="instanceName" class="form-control" placeholder="whatsapp ClaudSoft" required maxlength="150">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="set_as_default" value="1" id="create_set_default">
                                <label class="form-check-label small" for="create_set_default">تعيين كـ Instance افتراضي</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-success w-100"><i class="ri-add-line me-1"></i> إنشاء + QR</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if($defaultInstanceName ?? '')
            <div class="alert alert-light border small py-2 mb-3">
                <i class="ri-star-line text-warning me-1"></i>
                الافتراضي الحالي: <strong>{{ $defaultInstanceName }}</strong>
                — يمكن تغييره من <a href="{{ route('admin.evolution-api.settings.index') }}">الإعدادات</a> أو عبر «تعيين افتراضي» في الجدول.
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>الاسم</th>
                        <th>الحالة</th>
                        <th>الرقم</th>
                        <th>الحساب</th>
                        <th>التبديل</th>
                        <th>آخر استخدام</th>
                        <th class="text-end">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($instances as $instance)
                    <tr>
                        <td>
                            <span class="fw-semibold">{{ $instance->instance_name }}</span>
                            @if($instance->is_default)<span class="badge bg-success-transparent text-success ms-1">افتراضي</span>@endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $instance->isConnected() ? 'success' : 'secondary' }}-transparent text-{{ $instance->isConnected() ? 'success' : 'secondary' }}">
                                <i class="ri-{{ $instance->isConnected() ? 'checkbox-circle' : 'close-circle' }}-line me-1"></i>{{ $instance->connection_status }}
                            </span>
                        </td>
                        <td>{{ $instance->phone_number ?? '—' }}</td>
                        <td>{{ $instance->profile_name ?? '—' }}</td>
                        <td>
                            @if($instance->isConnected())
                                <form action="{{ route('admin.evolution-api.instances.toggle-rotation', $instance->instance_name) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $instance->rotation_enabled ? 'btn-success' : 'btn-outline-secondary' }}" title="مشاركة في التبديل">
                                        <i class="ri-shuffle-line me-1"></i>{{ $instance->rotation_enabled ? 'مفعّل' : 'معطّل' }}
                                    </button>
                                </form>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $instance->last_used_at?->diffForHumans() ?? '—' }}</td>
                        <td class="text-end text-nowrap">
                            @if(($defaultInstanceName ?? '') !== $instance->instance_name)
                                <form action="{{ route('admin.evolution-api.instances.set-default', $instance->instance_name) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success" title="تعيين افتراضي"><i class="ri-star-line"></i></button>
                                </form>
                            @endif
                            <a href="{{ route('admin.evolution-api.instances.connect', $instance->instance_name) }}" class="btn btn-sm btn-outline-primary"><i class="ri-qr-code-line"></i></a>
                            <form action="{{ route('admin.evolution-api.instances.restart', $instance->instance_name) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-outline-warning" title="Restart"><i class="ri-restart-line"></i></button></form>
                            <form action="{{ route('admin.evolution-api.instances.logout', $instance->instance_name) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-outline-secondary" title="Logout"><i class="ri-logout-box-r-line"></i></button></form>
                            <form action="{{ route('admin.evolution-api.instances.destroy', $instance->instance_name) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف Instance؟')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" title="حذف"><i class="ri-delete-bin-line"></i></button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-5"><i class="ri-inbox-line fs-24 d-block mb-2"></i>لا توجد instances. احفظ الإعدادات ثم «مزامنة».</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
