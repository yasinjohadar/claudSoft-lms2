@extends('admin.layouts.master')

@section('page-title')
    سجل إرسال Flaxxa (WhatsApp)
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        @include('admin.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">Flaxxa — سجل الإرسال</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.whatsapp-settings.index') }}">WhatsApp</a></li>
                        <li class="breadcrumb-item active">سجل Flaxxa</li>
                    </ol>
                </nav>
            </div>
        </div>

        @include('admin.pages.flaxxa-wapi._nav')

        <div class="alert alert-info border-0" role="alert">
            <i class="ri-information-line me-2"></i>
            تأكد من تعيين <code>WHATSAPP_TOKEN</code> في ملف البيئة وتشغيل <strong>queue worker</strong> لمعالجة الإرسال.
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.flaxxa-wapi.messages.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">النوع</label>
                        <select name="type" class="form-select">
                            <option value="">الكل</option>
                            <option value="message" {{ request('type') === 'message' ? 'selected' : '' }}>رسالة</option>
                            <option value="template" {{ request('type') === 'template' ? 'selected' : '' }}>قالب</option>
                            <option value="campaign" {{ request('type') === 'campaign' ? 'selected' : '' }}>حملة</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select">
                            <option value="">الكل</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                            <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>مرسل</option>
                            <option value="sent_pending_confirmation" {{ request('status') === 'sent_pending_confirmation' ? 'selected' : '' }}>مرسل (بانتظار تأكيد)</option>
                            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>فشل</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">الهاتف / campaign</label>
                        <input type="text" class="form-control" name="phone" value="{{ request('phone') }}" placeholder="بحث">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">من</label>
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">إلى</label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> تصفية</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title mb-0">آخر السجلات</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>الهاتف / المرجع</th>
                                <th>النوع</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($messages as $m)
                                <tr>
                                    <td>{{ $m->id }}</td>
                                    <td><code>{{ $m->phone }}</code></td>
                                    <td>
                                        @if($m->type?->value === 'message')
                                            <span class="badge bg-info">رسالة</span>
                                        @elseif($m->type?->value === 'template')
                                            <span class="badge bg-primary">قالب</span>
                                        @else
                                            <span class="badge bg-secondary">حملة</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php $s = $m->status?->value ?? ''; @endphp
                                        @if($s === 'failed')
                                            <span class="badge bg-danger">فشل</span>
                                        @elseif($s === 'sent')
                                            <span class="badge bg-success">مرسل</span>
                                        @elseif($s === 'sent_pending_confirmation')
                                            <span class="badge bg-warning text-dark">بانتظار تأكيد</span>
                                        @elseif($s === 'pending')
                                            <span class="badge bg-secondary">قيد الانتظار</span>
                                        @else
                                            <span class="badge bg-light text-dark">{{ $s }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $m->created_at?->format('Y-m-d H:i') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.flaxxa-wapi.messages.show', $m) }}" class="btn btn-sm btn-outline-primary">تفاصيل</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">لا توجد سجلات بعد.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $messages->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
