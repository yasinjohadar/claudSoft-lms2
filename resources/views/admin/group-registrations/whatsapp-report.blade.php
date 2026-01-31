@extends('admin.layouts.master')

@section('page-title')
    تقرير رسائل الواتساب - التسجيل في المجموعات
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">
                    <i class="ri-whatsapp-line me-2"></i>
                    تقرير رسائل الواتساب (التسجيل في المجموعات)
                </h5>
                <p class="text-muted small mb-0">تسجيل كل رسالة واتساب ترحيبية تُرسل عند التسجيل في مجموعة: هل أُرسلت، الوقت، الرقم، الاسم، وسبب الفشل إن وُجد.</p>
            </div>
            <div class="ms-auto">
                <a href="{{ route('admin.group-registrations.index') }}" class="btn btn-secondary">
                    <i class="ri-arrow-right-line me-1"></i>تسجيلات المجموعات
                </a>
            </div>
        </div>

        @include('admin.components.alerts')

        <!-- Filters -->
        <div class="card custom-card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.group-registrations.whatsapp-report') }}">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">المجموعة</label>
                            <select name="group_id" class="form-select">
                                <option value="">جميع المجموعات</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">حالة الواتساب</label>
                            <select name="whatsapp_status" class="form-select">
                                <option value="">الكل</option>
                                <option value="sent" {{ request('whatsapp_status') == 'sent' ? 'selected' : '' }}>تم الإرسال</option>
                                <option value="not_sent" {{ request('whatsapp_status') == 'not_sent' ? 'selected' : '' }}>لم يُرسَل</option>
                                <option value="failed" {{ request('whatsapp_status') == 'failed' ? 'selected' : '' }}>فشل الإرسال</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">من تاريخ</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">إلى تاريخ</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">بحث (اسم، بريد، رقم)</label>
                            <input type="text" name="search" class="form-control" placeholder="بحث..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary"><i class="ri-search-line me-1"></i>بحث</button>
                            <a href="{{ route('admin.group-registrations.whatsapp-report') }}" class="btn btn-outline-secondary">إعادة تعيين</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Report Table -->
        <div class="card custom-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>تاريخ التسجيل</th>
                                <th>المجموعة</th>
                                <th>الاسم</th>
                                <th>البريد</th>
                                <th>رقم الهاتف</th>
                                <th>تم إرسال الواتساب</th>
                                <th>وقت الإرسال</th>
                                <th>سبب الفشل</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registrations as $reg)
                                <tr>
                                    <td>{{ $reg->created_at->format('Y-m-d H:i') }}</td>
                                    <td>{{ $reg->group->name ?? '—' }}</td>
                                    <td>
                                        <strong>{{ $reg->name_ar ?? $reg->name }}</strong>
                                        @if($reg->name !== ($reg->name_ar ?? ''))
                                            <br><small class="text-muted">{{ $reg->name }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $reg->email }}</td>
                                    <td>{{ $reg->full_phone ?? $reg->phone ?? '—' }}</td>
                                    <td>
                                        @if($reg->whatsapp_sent)
                                            <span class="badge bg-success">نعم</span>
                                        @else
                                            <span class="badge bg-secondary">لا</span>
                                        @endif
                                    </td>
                                    <td>{{ $reg->whatsapp_sent_at ? $reg->whatsapp_sent_at->format('Y-m-d H:i') : '—' }}</td>
                                    <td class="small">
                                        @if($reg->whatsapp_error)
                                            <span class="text-danger" title="{{ e($reg->whatsapp_error) }}">{{ Str::limit($reg->whatsapp_error, 50) }}</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.group-registrations.show', $reg->id) }}" class="btn btn-sm btn-outline-primary" title="عرض التفاصيل">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        @if(!$reg->whatsapp_sent)
                                            <form action="{{ route('admin.group-registrations.resend-whatsapp', $reg->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="إعادة إرسال الواتساب">
                                                    <i class="ri-send-plane-line"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">لا توجد تسجيلات مطابقة للفلاتر.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-4">
                    {{ $registrations->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@stop
