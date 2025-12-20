@extends('admin.layouts.master')

@section('page-title')
جميع الإرساليات - Webhooks
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">جميع الإرساليات</h1>
            <a href="{{ route('admin.webhooks.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-right"></i> العودة للوحة التحكم
            </a>
        </div>

        <!-- Filters -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">فلترة النتائج</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.webhooks.submissions') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status">الحالة</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">الكل</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>معلق</option>
                                    <option value="processed" {{ request('status') === 'processed' ? 'selected' : '' }}>تمت المعالجة</option>
                                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>فاشل</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="type">النوع</label>
                                <select name="type" id="type" class="form-control">
                                    <option value="">الكل</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>
                                            @switch($type)
                                                @case('enrollment') تسجيل @break
                                                @case('contact') تواصل @break
                                                @case('review') مراجعة @break
                                                @case('payment') دفع @break
                                                @case('application') طلب التحاق @break
                                                @case('survey') استبيان @break
                                                @default {{ $type }} @break
                                            @endswitch
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="search">البحث (Form ID أو Entry ID)</label>
                                <input type="text" name="search" id="search" class="form-control"
                                       value="{{ request('search') }}" placeholder="أدخل Form ID أو Entry ID">
                            </div>
                        </div>

                        <div class="col-md-2">
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

                    @if(request()->hasAny(['status', 'type', 'search']))
                        <div class="text-right">
                            <a href="{{ route('admin.webhooks.submissions') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-times"></i> إزالة الفلاتر
                            </a>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        <!-- Submissions Table -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    الإرساليات ({{ $submissions->total() }})
                </h6>
                <a href="{{ route('admin.webhooks.export', request()->query()) }}" class="btn btn-sm btn-success">
                    <i class="fas fa-download"></i> تصدير النتائج
                </a>
            </div>
            <div class="card-body">
                @if($submissions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th width="60">ID</th>
                                    <th>النوع</th>
                                    <th>Form ID</th>
                                    <th>Entry ID</th>
                                    <th>الطالب</th>
                                    <th>الكورس</th>
                                    <th>الحالة</th>
                                    <th>التاريخ</th>
                                    <th width="150">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($submissions as $submission)
                                    <tr>
                                        <td>{{ $submission->id }}</td>
                                        <td>
                                            @switch($submission->submission_type)
                                                @case('enrollment') <span class="badge badge-primary"><i class="fas fa-user-plus"></i> تسجيل</span> @break
                                                @case('contact') <span class="badge badge-info"><i class="fas fa-envelope"></i> تواصل</span> @break
                                                @case('review') <span class="badge badge-warning"><i class="fas fa-star"></i> مراجعة</span> @break
                                                @case('payment') <span class="badge badge-success"><i class="fas fa-money-bill"></i> دفع</span> @break
                                                @case('application') <span class="badge badge-secondary"><i class="fas fa-file-alt"></i> طلب</span> @break
                                                @case('survey') <span class="badge badge-dark"><i class="fas fa-poll"></i> استبيان</span> @break
                                                @default <span class="badge badge-light">{{ $submission->submission_type }}</span> @break
                                            @endswitch
                                        </td>
                                        <td><code>{{ $submission->form_id }}</code></td>
                                        <td><code>{{ $submission->entry_id ?? '-' }}</code></td>
                                        <td>
                                            @if($submission->user)
                                                <a href="#" class="text-decoration-none">
                                                    {{ $submission->user->name }}
                                                </a>
                                                <br>
                                                <small class="text-muted">{{ $submission->user->email }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($submission->course)
                                                <a href="#" class="text-decoration-none">
                                                    {{ Str::limit($submission->course->title, 30) }}
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @switch($submission->status)
                                                @case('pending')
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-clock"></i> معلق
                                                    </span>
                                                @break
                                                @case('processed')
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check-circle"></i> تمت المعالجة
                                                    </span>
                                                @break
                                                @case('failed')
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-exclamation-triangle"></i> فاشل
                                                    </span>
                                                @break
                                            @endswitch
                                        </td>
                                        <td>
                                            <span title="{{ $submission->created_at->format('Y-m-d H:i:s') }}">
                                                {{ $submission->created_at->diffForHumans() }}
                                            </span>
                                            <br>
                                            <small class="text-muted">{{ $submission->created_at->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.webhooks.submission.show', $submission) }}"
                                                   class="btn btn-sm btn-info" title="عرض التفاصيل">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($submission->status === 'failed')
                                                    <form action="{{ route('admin.webhooks.submission.retry', $submission) }}"
                                                          method="POST" class="d-inline"
                                                          onsubmit="return confirm('هل أنت متأكد من إعادة محاولة معالجة هذه الإرسالية؟')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-warning" title="إعادة المحاولة">
                                                            <i class="fas fa-redo"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            عرض {{ $submissions->firstItem() }} إلى {{ $submissions->lastItem() }} من أصل {{ $submissions->total() }} إرسالية
                        </div>
                        <div>
                            {{ $submissions->links() }}
                        </div>
                    </div>
                @else
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-inbox fa-4x mb-3"></i>
                        <h5>لا توجد إرساليات</h5>
                        @if(request()->hasAny(['status', 'type', 'search']))
                            <p>جرب تغيير معايير البحث أو <a href="{{ route('admin.webhooks.submissions') }}">عرض جميع الإرساليات</a></p>
                        @else
                            <p>لم يتم استلام أي إرساليات بعد</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
<!-- End::app-content -->
@endsection
