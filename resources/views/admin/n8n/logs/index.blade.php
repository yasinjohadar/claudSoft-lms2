@extends('admin.layouts.master')

@section('page-title')
    السجلات - n8n Integration
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- Alerts -->
        @include('admin.components.alerts')

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">
                    <i class="fas fa-clipboard-list me-2"></i>سجلات Webhooks
                </h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.n8n.index') }}">n8n Integration</a></li>
                        <li class="breadcrumb-item active">السجلات</li>
                    </ol>
                </nav>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('admin.n8n.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right me-1"></i>رجوع
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-filter me-1"></i>فلترة السجلات</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.n8n.logs.index') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">الحالة</label>
                            <select name="status" class="form-select">
                                <option value="">الكل</option>
                                <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>نجحت</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>فشلت</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>معلقة</option>
                                <option value="retrying" {{ request('status') == 'retrying' ? 'selected' : '' }}>إعادة محاولة</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">نقطة النهاية</label>
                            <select name="endpoint_id" class="form-select">
                                <option value="">الكل</option>
                                @foreach($endpoints as $endpoint)
                                    <option value="{{ $endpoint->id }}" {{ request('endpoint_id') == $endpoint->id ? 'selected' : '' }}>
                                        {{ $endpoint->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">من تاريخ</label>
                            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">إلى تاريخ</label>
                            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i>بحث
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">السجلات</h5>
                <span class="badge bg-primary">{{ $logs->total() }} سجل</span>
            </div>
            <div class="card-body">
                @if($logs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>نقطة النهاية</th>
                                <th>نوع الحدث</th>
                                <th>الحالة</th>
                                <th>HTTP Status</th>
                                <th>المحاولة</th>
                                <th>التاريخ</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td>
                                    <a href="{{ route('admin.n8n.endpoints.show', $log->endpoint) }}">
                                        {{ $log->endpoint->name }}
                                    </a>
                                </td>
                                <td><span class="badge bg-secondary">{{ $log->event_type }}</span></td>
                                <td><span class="badge bg-{{ $log->status_color }}">{{ ucfirst($log->status) }}</span></td>
                                <td>{{ $log->response_status ?? '-' }}</td>
                                <td>{{ $log->attempt_number }}</td>
                                <td><small>{{ $log->created_at->format('Y-m-d H:i:s') }}</small></td>
                                <td>
                                    <a href="{{ route('admin.n8n.logs.show', $log) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($log->status === 'failed' && $log->canRetry())
                                    <form action="{{ route('admin.n8n.logs.retry', $log) }}" method="POST" class="d-inline">
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

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $logs->appends(request()->query())->links() }}
                </div>
                @else
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">لا توجد سجلات</h5>
                    <p class="text-muted">لم يتم إرسال أي webhooks بعد</p>
                </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
