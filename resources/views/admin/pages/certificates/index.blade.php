@extends('admin.layouts.master')

@section('page-title')
    إدارة الشهادات
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إدارة الشهادات</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">الشهادات</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('admin.certificates.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>إصدار شهادة جديدة
                    </a>
                    <a href="{{ route('admin.certificate-templates.index') }}" class="btn btn-info">
                        <i class="fas fa-file-alt me-2"></i>إدارة القوالب
                    </a>
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
                                        <i class="fas fa-certificate fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">إجمالي الشهادات</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ $statistics['total'] ?? 0 }}</h4>
                                    <span class="badge bg-primary-transparent">شهادة صادرة</span>
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
                                        <p class="fw-semibold mb-1">شهادات نشطة</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ $statistics['active'] ?? 0 }}</h4>
                                    <span class="badge bg-success-transparent">صالحة حالياً</span>
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
                                        <i class="fas fa-calendar-alt fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">هذا الشهر</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ $statistics['issued_this_month'] ?? 0 }}</h4>
                                    <span class="badge bg-warning-transparent">صادرة مؤخراً</span>
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
                                    <span class="avatar avatar-md bg-danger-transparent">
                                        <i class="fas fa-ban fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">ملغاة/منتهية</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ ($statistics['revoked'] ?? 0) + ($statistics['expired'] ?? 0) }}</h4>
                                    <span class="badge bg-danger-transparent">غير صالحة</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Table -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="card-title">قائمة الشهادات</div>
                        </div>
                        <div class="card-body">
                            <!-- Filters -->
                            <form method="GET" action="{{ route('admin.certificates.index') }}" class="mb-4">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <input type="text" name="search" class="form-control"
                                               placeholder="البحث (رقم الشهادة، الطالب، الكورس)"
                                               value="{{ request('search') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <select name="status" class="form-select">
                                            <option value="">جميع الحالات</option>
                                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشطة</option>
                                            <option value="revoked" {{ request('status') == 'revoked' ? 'selected' : '' }}>ملغاة</option>
                                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>منتهية</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select name="course_id" class="form-select">
                                            <option value="">جميع الكورسات</option>
                                            @foreach($courses as $course)
                                                <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                                    {{ $course->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-search me-2"></i>بحث
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <!-- Table -->
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap">
                                    <thead>
                                        <tr>
                                            <th>رقم الشهادة</th>
                                            <th>الطالب</th>
                                            <th>الكورس</th>
                                            <th>تاريخ الإصدار</th>
                                            <th>الحالة</th>
                                            <th>التحميلات</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($certificates as $certificate)
                                            <tr>
                                                <td>
                                                    <strong>{{ $certificate->certificate_number }}</strong>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span class="avatar avatar-sm me-2">
                                                            <i class="fas fa-user-graduate"></i>
                                                        </span>
                                                        {{ $certificate->student_name }}
                                                    </div>
                                                </td>
                                                <td>{{ $certificate->course_name }}</td>
                                                <td>{{ $certificate->issue_date->format('Y-m-d') }}</td>
                                                <td>
                                                    @if($certificate->status == 'active')
                                                        <span class="badge bg-success">نشطة</span>
                                                    @elseif($certificate->status == 'revoked')
                                                        <span class="badge bg-danger">ملغاة</span>
                                                    @else
                                                        <span class="badge bg-warning">منتهية</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-download me-1"></i>{{ $certificate->download_count }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="{{ route('admin.certificates.show', $certificate->id) }}"
                                                           class="btn btn-sm btn-info" title="عرض">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        @if($certificate->status == 'active')
                                                            <a href="{{ route('admin.certificates.download', $certificate->id) }}"
                                                               class="btn btn-sm btn-success" title="تحميل">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                        @endif
                                                        <button type="button" class="btn btn-sm btn-danger"
                                                                onclick="confirmDelete({{ $certificate->id }})" title="حذف">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <i class="fas fa-certificate fs-48 text-muted mb-3"></i>
                                                    <p class="text-muted">لا توجد شهادات مطابقة</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center mt-3">
                                {{ $certificates->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Delete Modal -->
    <form id="delete-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@section('scripts')
<script>
function confirmDelete(id) {
    if (confirm('هل أنت متأكد من حذف هذه الشهادة؟')) {
        const form = document.getElementById('delete-form');
        form.action = '{{ url("admin/certificates") }}/' + id;
        form.submit();
    }
}
</script>
@endsection
