@extends('admin.layouts.master')

@section('page-title')
    تسجيلات المجموعات
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">
                    <i class="fas fa-user-plus me-2"></i>
                    تسجيلات المجموعات
                </h5>
            </div>
            <div class="ms-auto">
                <a href="{{ route('admin.group-registrations.whatsapp-report') }}" class="btn btn-primary">
                    <i class="ri-whatsapp-line me-2"></i>تقارير رسائل الواتساب
                </a>
            </div>
        </div>

        @include('admin.components.alerts')

        <!-- Filters -->
        <div class="card custom-card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.group-registrations.index') }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">المجموعة</label>
                            <select name="group_id" class="form-select">
                                <option value="">جميع المجموعات</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>
                                        {{ $group->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">الحالة</label>
                            <select name="status" class="form-select">
                                <option value="">جميع الحالات</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>معلق</option>
                                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>قيد المعالجة</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتمل</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>فاشل</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">بحث</label>
                            <input type="text" name="search" class="form-control" placeholder="اسم، بريد، هاتف..." 
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>بحث
                            </button>
                            <a href="{{ route('admin.group-registrations.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>إعادة تعيين
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="card custom-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered text-nowrap">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الاسم</th>
                                <th>البريد الإلكتروني</th>
                                <th>الهاتف</th>
                                <th>المجموعة</th>
                                <th>هل تمتلك حاسوب</th>
                                <th>الحالة</th>
                                <th>تم إنشاء الحساب</th>
                                <th>تاريخ التسجيل</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registrations as $registration)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <strong>{{ $registration->name_ar ?? $registration->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $registration->name }}</small>
                                    </td>
                                    <td>{{ $registration->email }}</td>
                                    <td>{{ $registration->full_phone ?? $registration->phone }}</td>
                                    <td>
                                        <a href="{{ route('courses.groups.show', [$registration->group->course_id ?? 1, $registration->group_id]) }}" class="text-primary">
                                            {{ $registration->group->name }}
                                        </a>
                                    </td>
                                    <td>
                                        @if($registration->has_computer === 'yes')
                                            <span class="badge bg-success">نعم</span>
                                        @elseif($registration->has_computer === 'no')
                                            <span class="badge bg-secondary">لا</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($registration->status === 'pending')
                                            <span class="badge bg-warning">معلق</span>
                                        @elseif($registration->status === 'processing')
                                            <span class="badge bg-info">قيد المعالجة</span>
                                        @elseif($registration->status === 'completed')
                                            <span class="badge bg-success">مكتمل</span>
                                        @else
                                            <span class="badge bg-danger">فاشل</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($registration->user_created)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>نعم
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">لا</span>
                                        @endif
                                    </td>
                                    <td>{{ $registration->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('admin.group-registrations.show', $registration->id) }}" 
                                               class="btn btn-sm btn-info" title="عرض">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($registration->status === 'failed' || $registration->status === 'pending')
                                                <form action="{{ route('admin.group-registrations.reprocess', $registration->id) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning" title="إعادة المعالجة">
                                                        <i class="fas fa-redo"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('admin.group-registrations.destroy', $registration->id) }}" 
                                                  method="POST" class="d-inline" 
                                                  onsubmit="return confirm('هل أنت متأكد من حذف هذا التسجيل؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center">لا توجد تسجيلات</td>
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
