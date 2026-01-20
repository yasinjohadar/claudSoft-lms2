@extends('student.layouts.master')

@section('page-title')
    طلبات الانضمام
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">
                        <i class="bi bi-list-ul me-2"></i>
                        طلبات الانضمام للمجموعات
                    </h5>
                </div>
                <div>
                    <a href="{{ route('student.groups.index') }}" class="btn btn-outline-primary">
                        <i class="bi bi-people me-2"></i>
                        المجموعات المتاحة
                    </a>
                </div>
            </div>

            <!-- Filter Form -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('student.groups.my-requests') }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <select name="status" class="form-select">
                                    <option value="">جميع الحالات</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد المراجعة</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>مقبول</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-funnel"></i> فلتر
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Requests Table -->
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المجموعة</th>
                                    <th>الكورسات</th>
                                    <th>تاريخ الطلب</th>
                                    <th>موعد تسديد الرسوم</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $request)
                                    <tr>
                                        <td>{{ $request->id }}</td>
                                        <td>
                                            <strong>{{ $request->group->name }}</strong>
                                            @if($request->message)
                                                <br>
                                                <small class="text-muted">{{ Str::limit($request->message, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @foreach($request->group->courses->take(2) as $course)
                                                <span class="badge bg-info">{{ $course->title }}</span>
                                            @endforeach
                                            @if($request->group->courses->count() > 2)
                                                <span class="badge bg-secondary">+{{ $request->group->courses->count() - 2 }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $request->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            @if($request->payment_date)
                                                {{ $request->payment_date->format('Y-m-d') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($request->status === 'pending')
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bi bi-clock-history"></i> قيد المراجعة
                                                </span>
                                            @elseif($request->status === 'approved')
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> مقبول
                                                </span>
                                                @if($request->approved_at)
                                                    <br><small class="text-muted">{{ $request->approved_at->format('Y-m-d') }}</small>
                                                @endif
                                            @elseif($request->status === 'rejected')
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-x-circle"></i> مرفوض
                                                </span>
                                                @if($request->rejected_at)
                                                    <br><small class="text-muted">{{ $request->rejected_at->format('Y-m-d') }}</small>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('student.groups.show', $request->group->id) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="bi bi-inbox display-4 text-muted mb-3 d-block"></i>
                                            <h5 class="text-muted">لا توجد طلبات</h5>
                                            <p class="text-muted">لم تقم بإرسال أي طلبات انضمام للمجموعات</p>
                                            <a href="{{ route('student.groups.index') }}" class="btn btn-primary">
                                                <i class="bi bi-people me-2"></i>
                                                تصفح المجموعات
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($requests->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $requests->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
