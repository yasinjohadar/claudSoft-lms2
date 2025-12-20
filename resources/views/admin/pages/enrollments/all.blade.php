@extends('admin.layouts.master')

@section('page-title')
    جميع الانضمامات
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إدارة الانضمامات</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">الانضمامات</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#selectCourseModal">
                        <i class="fas fa-plus me-2"></i>إضافة انضمام جديد
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-primary-transparent">
                                        <i class="fas fa-user-graduate fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">إجمالي الانضمامات</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ $totalEnrollments }}</h4>
                                    <span class="badge bg-primary-transparent">في جميع الكورسات</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-success-transparent">
                                        <i class="fas fa-check-circle fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">انضمامات نشطة</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ $activeCount }}</h4>
                                    <span class="badge bg-success-transparent">نشطة حالياً</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-info-transparent">
                                        <i class="fas fa-trophy fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">انضمامات مكتملة</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ $completedCount }}</h4>
                                    <span class="badge bg-info-transparent">مكتملة</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-warning-transparent">
                                        <i class="fas fa-clock fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">طلبات معلقة</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ $pendingCount }}</h4>
                                    <span class="badge bg-warning-transparent">قيد الانتظار</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Requests Alert -->
            @if($pendingCount > 0)
                <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                    <i class="fas fa-exclamation-triangle me-2 fs-20"></i>
                    <div>
                        <strong>تنبيه:</strong> يوجد <strong>{{ $pendingCount }}</strong> طلب تسجيل في انتظار الموافقة
                    </div>
                </div>
            @endif

            <!-- Filters -->
            <div class="card custom-card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('enrollments.all') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">البحث</label>
                                <input type="text" name="search" class="form-control"
                                       value="{{ request('search') }}" placeholder="ابحث عن طالب أو كورس...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">الكورس</label>
                                <select name="course_id" class="form-select">
                                    <option value="">جميع الكورسات</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">الحالة</label>
                                <select name="status" class="form-select">
                                    <option value="">جميع الحالات</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتمل</option>
                                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>متوقف</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>بحث
                                    </button>
                                    <a href="{{ route('enrollments.all') }}" class="btn btn-secondary">
                                        <i class="fas fa-redo me-1"></i>إعادة تعيين
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Enrollments Table -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">قائمة الانضمامات</div>
                </div>
                <div class="card-body">
                    @if($enrollments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered text-nowrap">
                                <thead>
                                    <tr>
                                        <th>الطالب</th>
                                        <th>الكورس</th>
                                        <th>تاريخ الانضمام</th>
                                        <th>الحالة</th>
                                        <th>نسبة الإنجاز</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($enrollments as $enrollment)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($enrollment->student && $enrollment->student->avatar)
                                                        <img src="{{ asset('storage/' . $enrollment->student->avatar) }}"
                                                             alt="{{ $enrollment->student->name }}"
                                                             class="avatar avatar-sm rounded-circle me-2">
                                                    @else
                                                        <span class="avatar avatar-sm bg-primary-transparent me-2">
                                                            {{ $enrollment->student ? substr($enrollment->student->name, 0, 1) : '?' }}
                                                        </span>
                                                    @endif
                                                    <div>
                                                        @if($enrollment->student)
                                                            <span class="fw-semibold">{{ $enrollment->student->name }}</span>
                                                            <small class="d-block text-muted">{{ $enrollment->student->email }}</small>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($enrollment->course)
                                                    <a href="{{ route('courses.show', $enrollment->course_id) }}">
                                                        {{ $enrollment->course->title }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $enrollment->enrollment_date ? $enrollment->enrollment_date->format('Y-m-d') : '-' }}</td>
                                            <td>
                                                @if($enrollment->enrollment_status == 'active')
                                                    <span class="badge bg-success">نشط</span>
                                                @elseif($enrollment->enrollment_status == 'completed')
                                                    <span class="badge bg-primary">مكتمل</span>
                                                @elseif($enrollment->enrollment_status == 'suspended')
                                                    <span class="badge bg-danger">معلق</span>
                                                @elseif($enrollment->enrollment_status == 'pending')
                                                    <span class="badge bg-warning">قيد الانتظار</span>
                                                @elseif($enrollment->enrollment_status == 'cancelled')
                                                    <span class="badge bg-secondary">ملغي</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $enrollment->enrollment_status }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-fill me-2" style="height: 20px;">
                                                        <div class="progress-bar bg-primary" role="progressbar"
                                                             style="width: {{ $enrollment->completion_percentage ?? 0 }}%">
                                                            {{ number_format($enrollment->completion_percentage ?? 0, 0) }}%
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    @if($enrollment->enrollment_status == 'pending')
                                                        <button type="button" class="btn btn-sm btn-success" title="قبول الطلب"
                                                                data-bs-toggle="modal" data-bs-target="#approveModal{{ $enrollment->id }}">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-danger" title="رفض الطلب"
                                                                data-bs-toggle="modal" data-bs-target="#rejectModal{{ $enrollment->id }}">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    @endif
                                                    <a href="{{ route('courses.enrollments.index', $enrollment->course_id) }}"
                                                       class="btn btn-sm btn-info" title="عرض">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>

                                                <!-- Approve Modal -->
                                                <div class="modal fade" id="approveModal{{ $enrollment->id }}" tabindex="-1">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-success text-white">
                                                                <h6 class="modal-title">
                                                                    <i class="fas fa-check-circle me-2"></i>تأكيد قبول الطلب
                                                                </h6>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body text-center py-4">
                                                                <div class="avatar avatar-xl bg-success-transparent mb-3 mx-auto">
                                                                    <i class="fas fa-user-check fs-30 text-success"></i>
                                                                </div>
                                                                <h5 class="mb-3">قبول طلب التسجيل</h5>
                                                                <p class="text-muted mb-2">
                                                                    هل تريد قبول طلب تسجيل الطالب <strong>{{ $enrollment->student->name ?? 'غير معروف' }}</strong>
                                                                </p>
                                                                <p class="text-muted mb-0">
                                                                    في كورس: <strong>{{ $enrollment->course->title ?? 'غير معروف' }}</strong>
                                                                </p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                    <i class="fas fa-times me-1"></i>إلغاء
                                                                </button>
                                                                <form action="{{ route('enrollments.approve', $enrollment->id) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-success">
                                                                        <i class="fas fa-check me-1"></i>قبول الطلب
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Reject Modal -->
                                                <div class="modal fade" id="rejectModal{{ $enrollment->id }}" tabindex="-1">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-danger text-white">
                                                                <h6 class="modal-title">
                                                                    <i class="fas fa-times-circle me-2"></i>تأكيد رفض الطلب
                                                                </h6>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body text-center py-4">
                                                                <div class="avatar avatar-xl bg-danger-transparent mb-3 mx-auto">
                                                                    <i class="fas fa-user-times fs-30 text-danger"></i>
                                                                </div>
                                                                <h5 class="mb-3">رفض طلب التسجيل</h5>
                                                                <p class="text-muted mb-2">
                                                                    هل تريد رفض طلب تسجيل الطالب <strong>{{ $enrollment->student->name ?? 'غير معروف' }}</strong>
                                                                </p>
                                                                <p class="text-muted mb-0">
                                                                    في كورس: <strong>{{ $enrollment->course->title ?? 'غير معروف' }}</strong>
                                                                </p>
                                                                <div class="alert alert-warning d-flex align-items-center mt-3 text-start">
                                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                                    <small>سيتم إلغاء الطلب ولن يتمكن الطالب من الوصول للكورس</small>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                    <i class="fas fa-times me-1"></i>إلغاء
                                                                </button>
                                                                <form action="{{ route('enrollments.reject', $enrollment->id) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-danger">
                                                                        <i class="fas fa-ban me-1"></i>رفض الطلب
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $enrollments->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                            <p class="text-muted">لا توجد انضمامات</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- Select Course Modal -->
    <div class="modal fade" id="selectCourseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">اختر الكورس لإضافة انضمام</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">الكورس</label>
                        <select id="courseSelectEnrollment" class="form-select">
                            <option value="">-- اختر كورس --</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-primary" onclick="redirectToCreateEnrollment()">
                        <i class="fas fa-arrow-left me-2"></i>متابعة
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
<script>
    function redirectToCreateEnrollment() {
        const courseId = document.getElementById('courseSelectEnrollment').value;
        if (!courseId) {
            alert('الرجاء اختيار كورس أولاً');
            return;
        }
        window.location.href = `/admin/courses/${courseId}/enrollments/create`;
    }
</script>
@stop
