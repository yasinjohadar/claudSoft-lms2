@extends('admin.layouts.master')

@section('page-title')
    الإرسال الجماعي - التقارير
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تقارير الإرسال الجماعي</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.whatsapp-messages.index') }}">رسائل WhatsApp</a></li>
                        <li class="breadcrumb-item active">الإرسال الجماعي</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('admin.whatsapp-messages.create') }}" class="btn btn-primary">
                    <i class="ri-send-plane-line me-1"></i>إرسال رسالة جديدة
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        @if($broadcasts->isEmpty())
                            <p class="text-muted mb-0">لا توجد عمليات إرسال جماعي سابقة.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>التاريخ</th>
                                            <th>الكورس</th>
                                            <th>المجموعة</th>
                                            <th>الإجمالي</th>
                                            <th>تم الإرسال</th>
                                            <th>فشل</th>
                                            <th>الحالة</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($broadcasts as $b)
                                        <tr>
                                            <td>{{ $b->id }}</td>
                                            <td>{{ $b->created_at->format('Y-m-d H:i') }}</td>
                                            <td>{{ $b->course?->title ?? '—' }}</td>
                                            <td>{{ $b->group?->name ?? '—' }}</td>
                                            <td>{{ $b->total_recipients }}</td>
                                            <td><span class="text-success">{{ $b->sent_count }}</span></td>
                                            <td><span class="text-danger">{{ $b->failed_count ?? 0 }}</span></td>
                                            <td>
                                                @if($b->status === 'completed')
                                                    <span class="badge bg-success">مكتمل</span>
                                                @elseif($b->status === 'processing')
                                                    <span class="badge bg-info">جاري الإرسال</span>
                                                @elseif($b->status === 'pending')
                                                    <span class="badge bg-warning">قيد الانتظار</span>
                                                @elseif($b->status === 'failed')
                                                    <span class="badge bg-danger">فشل</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $b->status }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.whatsapp-messages.broadcasts.show', $b) }}" class="btn btn-sm btn-outline-primary">
                                                    عرض التقرير
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-center mt-3">
                                {{ $broadcasts->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
