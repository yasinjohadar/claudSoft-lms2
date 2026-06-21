@extends('admin.layouts.master')

@section('page-title')
    تقرير الإرسال الجماعي
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تقرير الإرسال الجماعي</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.whatsapp-messages.index') }}">رسائل WhatsApp</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.whatsapp-messages.broadcasts.index') }}">الإرسال الجماعي</a></li>
                        <li class="breadcrumb-item active">تقرير البث #{{ $broadcast->id }}</li>
                    </ol>
                </nav>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header">
                        <h5 class="card-title mb-0">ملخص عملية الإرسال</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="border rounded p-3 text-center">
                                    <h4 class="text-primary mb-0">{{ $broadcast->total_recipients }}</h4>
                                    <small class="text-muted">إجمالي المستلمين</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 text-center">
                                    <h4 class="text-success mb-0">{{ $broadcast->sent_count }}</h4>
                                    <small class="text-muted">تم الإرسال بنجاح</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 text-center">
                                    <h4 class="text-danger mb-0">{{ $broadcast->failed_count ?? 0 }}</h4>
                                    <small class="text-muted">فشل الإرسال</small>
                                </div>
                            </div>
                        </div>

                        <table class="table table-bordered">
                            <tr>
                                <th width="200">الحالة</th>
                                <td>
                                    @if($broadcast->status === 'completed')
                                        <span class="badge bg-success">مكتمل</span>
                                    @elseif($broadcast->status === 'processing')
                                        <span class="badge bg-info">جاري الإرسال</span>
                                    @elseif($broadcast->status === 'pending')
                                        <span class="badge bg-warning">قيد الانتظار</span>
                                    @elseif($broadcast->status === 'failed')
                                        <span class="badge bg-danger">فشل</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $broadcast->status }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>نوع الرسالة</th>
                                <td>{{ $broadcast->send_type === 'text' ? 'نص' : 'قالب' }}</td>
                            </tr>
                            <tr>
                                <th>نص الرسالة / القالب</th>
                                <td><pre class="mb-0 small">{{ Str::limit($broadcast->message_template, 500) }}</pre></td>
                            </tr>
                            <tr>
                                <th>الكورس</th>
                                <td>{{ $broadcast->course?->title ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>المجموعة</th>
                                <td>{{ $broadcast->group?->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>تاريخ البدء</th>
                                <td>{{ $broadcast->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                            @if($broadcast->creator)
                            <tr>
                                <th>أرسل بواسطة</th>
                                <td>{{ $broadcast->creator->name }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- قائمة المستلمين: من استلم ومن لم يستلم --}}
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header">
                        <h5 class="card-title mb-0">تفاصيل المستلمين</h5>
                    </div>
                    <div class="card-body">
                        @if($broadcast->recipients->isEmpty())
                            <p class="text-muted mb-0">تفاصيل المستلمين غير متوفرة لهذا البث (بث قديم).</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>الاسم</th>
                                            <th>رقم الهاتف</th>
                                            <th>الحالة</th>
                                            <th>تاريخ الإرسال</th>
                                            <th>سبب الفشل</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($broadcast->recipients as $rec)
                                        <tr>
                                            <td>{{ $rec->user?->name ?? '—' }}</td>
                                            <td>{{ $rec->user ? ($rec->user->full_phone ?? trim(($rec->user->country_code ?? '') . ($rec->user->phone ?? '')) ?: $rec->user->phone ?? '—') : '—' }}</td>
                                            <td>
                                                @if($rec->status === 'sent')
                                                    <span class="badge bg-success">تم الإرسال</span>
                                                @elseif($rec->status === 'failed')
                                                    <span class="badge bg-danger">فشل</span>
                                                @else
                                                    <span class="badge bg-warning">قيد الانتظار</span>
                                                @endif
                                            </td>
                                            <td>{{ $rec->sent_at ? $rec->sent_at->format('Y-m-d H:i') : '—' }}</td>
                                            <td class="small">{{ $rec->error_message ? e(Str::limit($rec->error_message, 120)) : '—' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <a href="{{ route('admin.whatsapp-messages.broadcasts.index') }}" class="btn btn-secondary">
                <i class="ri-arrow-right-line me-1"></i>قائمة البثوات
            </a>
            <a href="{{ route('admin.whatsapp-messages.create') }}" class="btn btn-primary">
                <i class="ri-send-plane-line me-1"></i>إرسال رسالة جديدة
            </a>
        </div>
    </div>
</div>
@endsection

@if($broadcast->status === 'processing')
@section('scripts')
<script>
(function () {
    const refreshMs = 5000;
    setTimeout(function () { window.location.reload(); }, refreshMs);
})();
</script>
@endsection
@endif
