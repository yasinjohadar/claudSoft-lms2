@extends('admin.pages.evolution-api.layout')

@php
    $evoPageTitle = 'Evolution Instances';
    $evoTitle = 'Instances';
    $evoSubtitle = 'إنشاء وربط وإدارة جلسات WhatsApp';
    $evoBreadcrumb = 'Instances';
@endphp

@section('evo-content')
@if($error)
    <div class="alert alert-warning border-0 shadow-sm evo-inline-result alert-warning mb-3">
        <i class="ri-alert-line me-2"></i>{{ $error }}
        <span class="text-muted small d-block mt-1">تحقق من الإعدادات أو نفّذ المزامنة بعد حفظ بيانات الاتصال.</span>
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
        <form action="{{ route('admin.evolution-api.instances.store') }}" method="POST" class="row g-2 align-items-end mb-4 p-3 bg-light rounded">
            @csrf
            <div class="col-md-5">
                <label class="form-label small fw-semibold mb-1">إنشاء Instance جديد</label>
                <input type="text" name="instanceName" class="form-control" placeholder="my-instance" required pattern="[a-zA-Z0-9_-]+">
            </div>
            <div class="col-md-3">
                <button class="btn btn-success w-100"><i class="ri-add-line me-1"></i> إنشاء + QR</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>الاسم</th>
                        <th>الحالة</th>
                        <th>الرقم</th>
                        <th>الحساب</th>
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
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.evolution-api.instances.connect', $instance->instance_name) }}" class="btn btn-sm btn-outline-primary"><i class="ri-qr-code-line"></i></a>
                            <form action="{{ route('admin.evolution-api.instances.restart', $instance->instance_name) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-outline-warning" title="Restart"><i class="ri-restart-line"></i></button></form>
                            <form action="{{ route('admin.evolution-api.instances.logout', $instance->instance_name) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-outline-secondary" title="Logout"><i class="ri-logout-box-r-line"></i></button></form>
                            <form action="{{ route('admin.evolution-api.instances.destroy', $instance->instance_name) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف Instance؟')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" title="حذف"><i class="ri-delete-bin-line"></i></button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-5"><i class="ri-inbox-line fs-24 d-block mb-2"></i>لا توجد instances. احفظ الإعدادات ثم «مزامنة».</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
