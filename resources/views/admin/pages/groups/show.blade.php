@extends('admin.layouts.master')

@section('page-title')
    {{ $group->name }} - تفاصيل المجموعة
@stop

@section('css')
<style>
    .member-card {
        border-radius: 10px;
        border: 1px solid #e9ecef;
        padding: 1rem;
        margin-bottom: 1rem;
        transition: all 0.3s;
    }
    .member-card:hover {
        border-color: #667eea;
        box-shadow: 0 3px 10px rgba(102, 126, 234, 0.1);
    }
    .member-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
    }
    .group-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
</style>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-check-circle me-2"></i>نجح!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تفاصيل المجموعة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.show', $course->id) }}">{{ $course->title }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.groups.index', $course->id) }}">المجموعات</a></li>
                            <li class="breadcrumb-item active">{{ $group->name }}</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Group Header -->
            <div class="group-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="mb-2">{{ $group->name }}</h3>
                        @if($group->description)
                            <p class="mb-0" style="opacity: 0.9;">{{ $group->description }}</p>
                        @endif
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="{{ route('courses.groups.edit', [$course->id, $group->id]) }}" class="btn btn-light me-2">
                            <i class="fas fa-edit me-2"></i>تعديل
                        </a>
                        <form action="{{ route('courses.groups.destroy', [$course->id, $group->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه المجموعة؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash me-2"></i>حذف
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="mb-1 text-muted">عدد الأعضاء</p>
                                    <h3 class="mb-0">{{ $stats['total_members'] ?? 0 }}@if($group->max_members) / {{ $group->max_members }}@endif</h3>
                                </div>
                                <div class="avatar avatar-lg bg-primary-transparent">
                                    <i class="fas fa-users fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="mb-1 text-muted">الكورسات المرتبطة</p>
                                    <h3 class="mb-0">{{ $group->courses->count() }}</h3>
                                </div>
                                <div class="avatar avatar-lg bg-success-transparent">
                                    <i class="fas fa-book fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="mb-1 text-muted">الحالة</p>
                                    <span class="badge {{ $group->is_active ? 'bg-success' : 'bg-secondary' }} fs-6">
                                        {{ $group->is_active ? 'نشطة' : 'غير نشطة' }}
                                    </span>
                                </div>
                                <div class="avatar avatar-lg bg-info-transparent">
                                    <i class="fas fa-power-off fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="mb-1 text-muted">الرؤية</p>
                                    <span class="badge {{ $group->is_visible ? 'bg-info' : 'bg-secondary' }} fs-6">
                                        {{ $group->is_visible ? 'مرئية' : 'مخفية' }}
                                    </span>
                                </div>
                                <div class="avatar avatar-lg bg-warning-transparent">
                                    <i class="fas fa-eye fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Members List -->
                <div class="col-lg-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="card-title mb-0">أعضاء المجموعة ({{ $stats['total_members'] ?? 0 }})</h6>
                            <div>
                                <button type="button" class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                                    <i class="fas fa-user-plus me-2"></i>إضافة عضو
                                </button>
                                <button type="button" class="btn btn-success btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addBulkMembersModal">
                                    <i class="fas fa-users me-2"></i>إضافة سريعة
                                </button>
                                <a href="{{ route('groups.bulk-enroll-page', $group->id) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-filter me-2"></i>إضافة متقدمة
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
                            @if($members && $members->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-hover text-nowrap">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>اسم الطالب</th>
                                                <th>البريد الإلكتروني</th>
                                                <th>رقم الهاتف</th>
                                                <th>الدور</th>
                                                <th>تاريخ الانضمام</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($members as $index => $memberRecord)
                                                @if($memberRecord->student)
                                                    <tr>
                                                        <td>{{ ($members->currentPage() - 1) * $members->perPage() + $index + 1 }}</td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                @if($memberRecord->student->avatar)
                                                                    <img src="{{ asset('storage/' . $memberRecord->student->avatar) }}" alt="{{ $memberRecord->student->name }}" class="avatar avatar-sm rounded-circle me-2">
                                                                @else
                                                                    <div class="avatar avatar-sm rounded-circle bg-primary-transparent me-2">
                                                                        <span class="fw-bold">{{ substr($memberRecord->student->name, 0, 1) }}</span>
                                                                    </div>
                                                                @endif
                                                                <a href="{{ route('users.show', $memberRecord->student_id) }}" class="text-decoration-none">
                                                                    <strong>{{ $memberRecord->student->name }}</strong>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td>{{ $memberRecord->student->email }}</td>
                                                        <td>
                                                            @if($memberRecord->student->phone)
                                                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $memberRecord->student->phone) }}" target="_blank" class="text-success" title="مراسلة عبر واتساب">
                                                                    <i class="fab fa-whatsapp me-1"></i>{{ $memberRecord->student->phone }}
                                                                </a>
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($memberRecord->role == 'leader')
                                                                <span class="badge bg-warning">قائد</span>
                                                            @else
                                                                <span class="badge bg-info">عضو</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $memberRecord->joined_at ? $memberRecord->joined_at->format('Y-m-d') : '-' }}</td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                <button type="button" class="btn btn-sm btn-outline-primary" title="تغيير الدور">
                                                                    <i class="fas fa-user-tag"></i>
                                                                </button>
                                                                <form action="{{ route('groups.remove-member', [$group->id, $memberRecord->student_id]) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من إزالة هذا العضو؟');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="إزالة">
                                                                        <i class="fas fa-user-times"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <div class="mt-4 d-flex justify-content-center">
                                    {{ $members->links() }}
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-users fa-5x text-muted mb-4 opacity-25"></i>
                                    <h4 class="text-muted mb-3">لا يوجد أعضاء</h4>
                                    <p class="text-muted">ابدأ بإضافة أعضاء إلى هذه المجموعة</p>
                                    <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                                        <i class="fas fa-user-plus me-2"></i>إضافة عضو
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Add Member Modal -->
    <div class="modal fade" id="addMemberModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('groups.add-member', $group->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">إضافة عضو جديد</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">اختر الطالب</label>
                            <select name="student_id" class="form-select" required>
                                <option value="">-- اختر طالب --</option>
                                @foreach($availableStudents ?? [] as $student)
                                    <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">الدور</label>
                            <select name="role" class="form-select" required>
                                <option value="member">عضو</option>
                                <option value="leader">قائد</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>إضافة
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Bulk Members Modal -->
    <div class="modal fade" id="addBulkMembersModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('groups.add-bulk-members', $group->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">إضافة أعضاء جماعياً</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">اختر الطلاب</label>
                            <select name="student_ids[]" id="bulkStudentSelect" class="form-select" multiple size="10" required>
                                @foreach($availableStudents ?? [] as $student)
                                    <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->email }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted">اضغط Ctrl/Cmd لتحديد عدة طلاب</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">الدور الافتراضي</label>
                            <select name="default_role" class="form-select" required>
                                <option value="member" selected>عضو</option>
                                <option value="leader">قائد</option>
                            </select>
                            <small class="text-muted">سيتم تعيين هذا الدور لجميع الطلاب المحددين</small>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            سيتم إضافة جميع الطلاب المحددين بنفس الدور
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-users me-2"></i>إضافة الكل
                        </button>
                    </div>
                </form>
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
