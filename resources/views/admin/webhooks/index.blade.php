@extends('admin.layouts.master')

@section('page-title')
إدارة Webhooks
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">إدارة Webhooks</h1>
        <div class="btn-group" role="group">
            <a href="{{ route('admin.webhooks.submissions') }}" class="btn btn-primary">
                <i class="fas fa-list"></i> جميع الإرساليات
            </a>
            <a href="{{ route('admin.webhooks.logs') }}" class="btn btn-info">
                <i class="fas fa-file-alt"></i> السجلات
            </a>
            <a href="{{ route('admin.webhooks.export') }}" class="btn btn-success">
                <i class="fas fa-download"></i> تصدير CSV
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <!-- Total Submissions -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                إجمالي الإرساليات
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_submissions']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-inbox fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                قيد المعالجة
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['pending']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Processed -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                تمت المعالجة
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['processed']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Failed -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                فاشلة
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['failed']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Statistics -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">إحصائيات الفترة</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-sm">اليوم</span>
                            <span class="badge badge-primary badge-pill">{{ number_format($stats['today']) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-sm">هذا الأسبوع</span>
                            <span class="badge badge-info badge-pill">{{ number_format($stats['this_week']) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">الإرساليات حسب النوع</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($byType as $type)
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-sm">
                                        @switch($type->submission_type)
                                            @case('enrollment') <i class="fas fa-user-plus text-primary"></i> تسجيل @break
                                            @case('contact') <i class="fas fa-envelope text-info"></i> تواصل @break
                                            @case('review') <i class="fas fa-star text-warning"></i> مراجعة @break
                                            @case('payment') <i class="fas fa-money-bill text-success"></i> دفع @break
                                            @case('application') <i class="fas fa-file-alt text-secondary"></i> طلب @break
                                            @case('survey') <i class="fas fa-poll text-dark"></i> استبيان @break
                                            @default <i class="fas fa-question text-muted"></i> {{ $type->submission_type }} @break
                                        @endswitch
                                    </span>
                                    <span class="badge badge-secondary badge-pill">{{ number_format($type->count) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Submissions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">أحدث الإرساليات</h6>
                    <a href="{{ route('admin.webhooks.submissions') }}" class="btn btn-sm btn-primary">
                        عرض الكل
                    </a>
                </div>
                <div class="card-body">
                    @if($recentSubmissions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>النوع</th>
                                        <th>Form ID</th>
                                        <th>الطالب</th>
                                        <th>الكورس</th>
                                        <th>الحالة</th>
                                        <th>التاريخ</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentSubmissions as $submission)
                                        <tr>
                                            <td>{{ $submission->id }}</td>
                                            <td>
                                                @switch($submission->submission_type)
                                                    @case('enrollment') <span class="badge badge-primary">تسجيل</span> @break
                                                    @case('contact') <span class="badge badge-info">تواصل</span> @break
                                                    @case('review') <span class="badge badge-warning">مراجعة</span> @break
                                                    @case('payment') <span class="badge badge-success">دفع</span> @break
                                                    @case('application') <span class="badge badge-secondary">طلب</span> @break
                                                    @case('survey') <span class="badge badge-dark">استبيان</span> @break
                                                    @default <span class="badge badge-light">{{ $submission->submission_type }}</span> @break
                                                @endswitch
                                            </td>
                                            <td>{{ $submission->form_id }}</td>
                                            <td>{{ $submission->user?->name ?? '-' }}</td>
                                            <td>{{ $submission->course?->title ?? '-' }}</td>
                                            <td>
                                                @switch($submission->status)
                                                    @case('pending') <span class="badge badge-warning">معلق</span> @break
                                                    @case('processed') <span class="badge badge-success">تمت المعالجة</span> @break
                                                    @case('failed') <span class="badge badge-danger">فاشل</span> @break
                                                @endswitch
                                            </td>
                                            <td>{{ $submission->created_at->diffForHumans() }}</td>
                                            <td>
                                                <a href="{{ route('admin.webhooks.submission.show', $submission) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($submission->status === 'failed')
                                                    <form action="{{ route('admin.webhooks.submission.retry', $submission) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-warning" title="إعادة المحاولة">
                                                            <i class="fas fa-redo"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>لا توجد إرساليات حتى الآن</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Webhook Logs -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">أحدث السجلات</h6>
                    <div>
                        <a href="{{ route('admin.webhooks.logs') }}" class="btn btn-sm btn-primary">
                            عرض الكل
                        </a>
                        <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#cleanupModal">
                            <i class="fas fa-trash"></i> تنظيف السجلات
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($recentLogs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>المصدر</th>
                                        <th>الحالة</th>
                                        <th>الرسالة</th>
                                        <th>IP</th>
                                        <th>التاريخ</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentLogs as $log)
                                        <tr>
                                            <td>{{ $log->id }}</td>
                                            <td><span class="badge badge-secondary">{{ $log->source }}</span></td>
                                            <td>
                                                @if($log->status === 'received')
                                                    <span class="badge badge-info">مستلم</span>
                                                @elseif($log->status === 'processed')
                                                    <span class="badge badge-success">معالج</span>
                                                @else
                                                    <span class="badge badge-danger">فاشل</span>
                                                @endif
                                            </td>
                                            <td>{{ Str::limit($log->message, 50) }}</td>
                                            <td><small class="text-muted">{{ $log->ip_address }}</small></td>
                                            <td>{{ $log->created_at->diffForHumans() }}</td>
                                            <td>
                                                <a href="{{ route('admin.webhooks.log.show', $log) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-file-alt fa-3x mb-3"></i>
                            <p>لا توجد سجلات حتى الآن</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

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
            <form action="{{ route('admin.webhooks.cleanup') }}" method="POST">
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
                        </select>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        تحذير: لا يمكن التراجع عن هذا الإجراء!
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger">حذف السجلات</button>
                </div>
            </form>
        </div>
    </div>
    </div>
</div>
<!-- End::app-content -->
@endsection
