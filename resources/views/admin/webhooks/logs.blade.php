@extends('admin.layouts.master')

@section('page-title')
سجلات Webhooks
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">سجلات Webhooks</h1>
            <div>
                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#cleanupModal">
                    <i class="fas fa-trash"></i> تنظيف السجلات القديمة
                </button>
                <a href="{{ route('admin.webhooks.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-right"></i> العودة للوحة التحكم
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">فلترة السجلات</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.webhooks.logs') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="status">الحالة</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">الكل</option>
                                    <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>مستلم</option>
                                    <option value="processed" {{ request('status') === 'processed' ? 'selected' : '' }}>معالج</option>
                                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>فاشل</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="source">المصدر</label>
                                <select name="source" id="source" class="form-control">
                                    <option value="">الكل</option>
                                    <option value="wpforms" {{ request('source') === 'wpforms' ? 'selected' : '' }}>WPForms</option>
                                    <option value="stripe" {{ request('source') === 'stripe' ? 'selected' : '' }}>Stripe</option>
                                    <option value="paypal" {{ request('source') === 'paypal' ? 'selected' : '' }}>PayPal</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-search"></i> بحث
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(request()->hasAny(['status', 'source']))
                        <div class="text-right">
                            <a href="{{ route('admin.webhooks.logs') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-times"></i> إزالة الفلاتر
                            </a>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    السجلات ({{ $logs->total() }})
                </h6>
            </div>
            <div class="card-body">
                @if($logs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th width="60">ID</th>
                                    <th width="100">المصدر</th>
                                    <th width="100">الحالة</th>
                                    <th>الرسالة</th>
                                    <th width="120">IP Address</th>
                                    <th width="150">التاريخ</th>
                                    <th width="80">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                    <tr class="{{ $log->status === 'failed' ? 'table-danger' : '' }}">
                                        <td>{{ $log->id }}</td>
                                        <td>
                                            <span class="badge badge-secondary">
                                                {{ strtoupper($log->source) }}
                                            </span>
                                        </td>
                                        <td>
                                            @switch($log->status)
                                                @case('received')
                                                    <span class="badge badge-info">
                                                        <i class="fas fa-inbox"></i> مستلم
                                                    </span>
                                                @break
                                                @case('processed')
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check"></i> معالج
                                                    </span>
                                                @break
                                                @case('failed')
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-times"></i> فاشل
                                                    </span>
                                                @break
                                            @endswitch
                                        </td>
                                        <td>
                                            <span title="{{ $log->message }}">
                                                {{ Str::limit($log->message, 60) }}
                                            </span>
                                            @if($log->status === 'failed' && $log->error_message)
                                                <br>
                                                <small class="text-danger">
                                                    <i class="fas fa-exclamation-circle"></i>
                                                    {{ Str::limit($log->error_message, 50) }}
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-monospace">{{ $log->ip_address }}</small>
                                        </td>
                                        <td>
                                            <span title="{{ $log->created_at->format('Y-m-d H:i:s') }}">
                                                {{ $log->created_at->diffForHumans() }}
                                            </span>
                                            <br>
                                            <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.webhooks.log.show', $log) }}"
                                               class="btn btn-sm btn-info" title="عرض التفاصيل">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            عرض {{ $logs->firstItem() }} إلى {{ $logs->lastItem() }} من أصل {{ $logs->total() }} سجل
                        </div>
                        <div>
                            {{ $logs->links() }}
                        </div>
                    </div>
                @else
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-file-alt fa-4x mb-3"></i>
                        <h5>لا توجد سجلات</h5>
                        @if(request()->hasAny(['status', 'source']))
                            <p>جرب تغيير معايير البحث أو <a href="{{ route('admin.webhooks.logs') }}">عرض جميع السجلات</a></p>
                        @else
                            <p>لم يتم استلام أي طلبات webhook بعد</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Statistics Card -->
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    مجموع السجلات
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $logs->total() }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-database fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    معالجة بنجاح
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ \App\Models\WebhookLog::processed()->count() }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    فاشلة
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ \App\Models\WebhookLog::failed()->count() }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    آخر 24 ساعة
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ \App\Models\WebhookLog::where('created_at', '>=', now()->subDay())->count() }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End::app-content -->

<!-- Cleanup Modal -->
<div class="modal fade" id="cleanupModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تنظيف السجلات القديمة</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.webhooks.cleanup') }}" method="POST"
                  onsubmit="return confirm('هل أنت متأكد من حذف السجلات القديمة؟ لا يمكن التراجع عن هذا الإجراء!')">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="days">حذف السجلات الأقدم من:</label>
                        <select name="days" id="days" class="form-control">
                            <option value="7">7 أيام</option>
                            <option value="14">14 يوم</option>
                            <option value="30" selected>30 يوم</option>
                            <option value="60">60 يوم</option>
                            <option value="90">90 يوم</option>
                            <option value="180">180 يوم (6 أشهر)</option>
                        </select>
                    </div>
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>تحذير:</strong> لا يمكن التراجع عن هذا الإجراء! سيتم حذف جميع السجلات الأقدم من الفترة المحددة نهائياً.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> حذف السجلات القديمة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
