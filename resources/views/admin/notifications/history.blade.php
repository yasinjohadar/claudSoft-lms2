@extends('admin.layouts.master')

@section('page-title', 'سجل الإشعارات')

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h4 class="page-title fw-semibold fs-18 mb-0">سجل الإشعارات</h4>
            <p class="fw-normal text-muted fs-14 mb-0">جميع الإشعارات المرسلة</p>
        </div>
        <div class="ms-md-auto d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('admin.notifications.index') }}" class="btn btn-primary btn-wave">
                <i class="ri-send-plane-line me-1"></i> إرسال إشعار جديد
            </a>
            <a href="{{ route('admin.notifications.statistics') }}" class="btn btn-info btn-wave">
                <i class="ri-bar-chart-line me-1"></i> الإحصائيات
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title">
                        سجل الإشعارات
                        <span class="badge bg-primary-transparent ms-2">{{ $notifications->total() }}</span>
                    </div>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" style="width: 200px;" onchange="filterByType(this.value)">
                            <option value="">جميع الأنواع</option>
                            <option value="admin_announcement">إعلانات الإدارة</option>
                            <option value="custom_message">رسائل مخصصة</option>
                            <option value="system_maintenance">صيانة</option>
                        </select>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($notifications->count() > 0)
                        <div class="table-responsive">
                            <table class="table text-nowrap table-hover">
                                <thead>
                                    <tr>
                                        <th>الطالب</th>
                                        <th>النوع</th>
                                        <th>العنوان</th>
                                        <th>الحالة</th>
                                        <th>تاريخ الإرسال</th>
                                        <th>تاريخ القراءة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($notifications as $notification)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <strong>{{ $notification->user->name }}</strong><br>
                                                        <small class="text-muted">{{ $notification->user->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info-transparent">{{ $notification->type }}</span>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{!! $notification->icon_html !!} {{ $notification->title }}</strong><br>
                                                    <small class="text-muted">{{ Str::limit($notification->message, 50) }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @if($notification->is_read)
                                                    <span class="badge bg-success">مقروء</span>
                                                @else
                                                    <span class="badge bg-warning">غير مقروء</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ $notification->created_at->format('Y-m-d H:i') }}</small>
                                            </td>
                                            <td>
                                                @if($notification->read_at)
                                                    <small>{{ $notification->read_at->format('Y-m-d H:i') }}</small>
                                                @else
                                                    <small class="text-muted">-</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="card-footer">
                            {{ $notifications->links() }}
                        </div>
                    @else
                        <div class="text-center p-5">
                            <i class="ri-notification-off-line fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">لا توجد إشعارات</h5>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<!-- End::app-content -->

@push('scripts')
<script>
function filterByType(type) {
    const url = new URL(window.location.href);
    if (type === '') {
        url.searchParams.delete('type');
    } else {
        url.searchParams.set('type', type);
    }
    window.location.href = url.toString();
}
</script>
@endpush
@endsection
