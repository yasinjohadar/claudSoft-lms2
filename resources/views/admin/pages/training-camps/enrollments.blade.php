@extends('admin.layouts.master')

@section('page-title')
    طلبات التسجيل في المعسكرات
@stop

@section('css')
<style>
    .enrollment-card {
        transition: all 0.3s ease;
    }
    .enrollment-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
</style>
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-check-circle me-2"></i>نجح!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-exclamation-circle me-2"></i>خطأ!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">طلبات التسجيل في المعسكرات</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('training-camps.index') }}">المعسكرات التدريبية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">طلبات التسجيل</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Filters -->
            <div class="card custom-card mb-4">
                <div class="card-body">
                    <form action="{{ route('training-camps.enrollments') }}" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control"
                                   placeholder="بحث بالاسم أو البريد..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="">جميع الحالات</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>موافق عليه</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="payment_status" class="form-select">
                                <option value="">حالة الدفع</option>
                                <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>غير مدفوع</option>
                                <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>مدفوع</option>
                                <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>مسترد</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="camp_id" class="form-select">
                                <option value="">جميع المعسكرات</option>
                                @foreach($camps as $camp)
                                    <option value="{{ $camp->id }}" {{ request('camp_id') == $camp->id ? 'selected' : '' }}>
                                        {{ $camp->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i>بحث
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Enrollments Table -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">قائمة الطلبات</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">الطالب</th>
                                    <th scope="col">المعسكر</th>
                                    <th scope="col">تاريخ الطلب</th>
                                    <th scope="col">الحالة</th>
                                    <th scope="col">حالة الدفع</th>
                                    <th scope="col">العمليات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($enrollments as $enrollment)
                                    <tr>
                                        <td>{{ $loop->iteration + ($enrollments->currentPage() - 1) * $enrollments->perPage() }}</td>

                                        <td>
                                            <div>
                                                <strong>{{ $enrollment->student->name }}</strong>
                                                <br><small class="text-muted">{{ $enrollment->student->email }}</small>
                                            </div>
                                        </td>

                                        <td>
                                            <div>
                                                <strong>{{ $enrollment->camp->name }}</strong>
                                                <br><small class="text-muted">
                                                    {{ $enrollment->camp->start_date->format('Y-m-d') }}
                                                </small>
                                            </div>
                                        </td>

                                        <td>
                                            <small>{{ $enrollment->created_at->format('Y-m-d H:i') }}</small>
                                        </td>

                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'bg-warning text-dark',
                                                    'approved' => 'bg-success',
                                                    'rejected' => 'bg-danger',
                                                    'cancelled' => 'bg-secondary'
                                                ];
                                            @endphp
                                            <span class="badge {{ $statusColors[$enrollment->status] ?? 'bg-secondary' }}">
                                                {{ $enrollment->status_label }}
                                            </span>
                                        </td>

                                        <td>
                                            @php
                                                $paymentColors = [
                                                    'unpaid' => 'bg-warning text-dark',
                                                    'paid' => 'bg-success',
                                                    'refunded' => 'bg-secondary'
                                                ];
                                            @endphp
                                            <span class="badge {{ $paymentColors[$enrollment->payment_status] ?? 'bg-secondary' }}">
                                                {{ $enrollment->payment_status_label }}
                                            </span>
                                        </td>

                                        <td>
                                            <div class="btn-group" role="group">
                                                @if($enrollment->status === 'pending')
                                                    <form action="{{ route('training-camps.enrollments.approve', $enrollment->id) }}"
                                                          method="POST"
                                                          class="d-inline"
                                                          onsubmit="return confirm('هل أنت متأكد من الموافقة على هذا الطلب؟');">
                                                        @csrf
                                                        <button type="submit"
                                                                class="btn btn-sm btn-success"
                                                                title="موافقة">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>

                                                    <button type="button"
                                                            class="btn btn-sm btn-danger"
                                                            title="رفض"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#rejectModal{{ $enrollment->id }}">
                                                        <i class="fas fa-times"></i>
                                                    </button>

                                                    <!-- Reject Modal -->
                                                    <div class="modal fade" id="rejectModal{{ $enrollment->id }}" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">رفض الطلب</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form action="{{ route('training-camps.enrollments.reject', $enrollment->id) }}" method="POST">
                                                                    @csrf
                                                                    <div class="modal-body">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">سبب الرفض (اختياري)</label>
                                                                            <textarea class="form-control" name="notes" rows="3"
                                                                                      placeholder="أدخل سبب الرفض..."></textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                                        <button type="submit" class="btn btn-danger">رفض الطلب</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="badge bg-secondary">{{ $enrollment->status_label }}</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                                <h5>لا توجد طلبات تسجيل</h5>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($enrollments->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $enrollments->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@stop

@section('script')
<script>
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
</script>
@stop
