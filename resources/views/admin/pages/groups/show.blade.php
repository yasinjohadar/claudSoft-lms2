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
    /* Remove Member Modal Styles */
    [id^="removeMemberModal"] .modal-content {
        border: none;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    }
    [id^="removeMemberModal"] .avatar-xl {
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    [id^="removeMemberModal"] .fs-24 {
        font-size: 2rem;
    }
    [id^="removeMemberModal"] .bg-danger-transparent {
        background-color: rgba(220, 53, 69, 0.1);
    }
</style>
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            
            <!-- Alerts -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4 mt-3" role="alert" style="font-size: 1.1rem; box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3); border-left: 4px solid #28a745; background-color: #d4edda !important; color: #155724 !important;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle me-3 fs-4" style="color: #28a745;"></i>
                        <div class="flex-grow-1">
                            <strong class="d-block mb-1" style="color: #155724;">نجح!</strong>
                            <span style="color: #155724;">{{ session('success') }}</span>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4 mt-3" role="alert" style="font-size: 1.1rem; box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3); border-left: 4px solid #dc3545; background-color: #f8d7da !important; color: #721c24 !important;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-3 fs-4" style="color: #dc3545;"></i>
                        <div class="flex-grow-1">
                            <strong class="d-block mb-1" style="color: #721c24;">خطأ!</strong>
                            <span style="color: #721c24;">{{ session('error') }}</span>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif

            @if (session('warning'))
                <div class="alert alert-warning alert-dismissible fade show mb-4 mt-3" role="alert" style="font-size: 1.1rem; box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3); border-left: 4px solid #ffc107; background-color: #fff3cd !important; color: #856404 !important;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-3 fs-4" style="color: #ffc107;"></i>
                        <div class="flex-grow-1">
                            <strong class="d-block mb-1" style="color: #856404;">تحذير!</strong>
                            <span style="color: #856404;">{{ session('warning') }}</span>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تفاصيل المجموعة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('groups.all') }}">المجموعات</a></li>
                            @if($course)
                                <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('courses.show', $course->id) }}">{{ $course->title }}</a></li>
                            @endif
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
                        @if($course)
                            <a href="{{ route('courses.groups.edit', [$course->id, $group->id]) }}" class="btn btn-light me-2">
                                <i class="fas fa-edit me-2"></i>تعديل
                            </a>
                        @else
                            <a href="{{ route('groups.edit', $group->id) }}" class="btn btn-light me-2">
                                <i class="fas fa-edit me-2"></i>تعديل
                            </a>
                        @endif
                        <form action="{{ route('groups.delete', $group->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه المجموعة؟');">
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
                        
                        <!-- Bulk Actions Bar -->
                        <div class="card-body border-bottom bg-light" id="bulkActionsBar" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold text-primary" id="selectedCount">0</span>
                                    <span class="text-muted">عضو محدد</span>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-danger btn-sm" id="bulkRemoveBtn" disabled>
                                        <i class="fas fa-user-times me-2"></i>فك الارتباط (<span id="bulkRemoveCount">0</span>)
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm ms-2" id="clearSelectionBtn">
                                        <i class="fas fa-times me-2"></i>إلغاء التحديد
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <!-- Search and Filter Form -->
                            <form method="GET" action="{{ $course ? route('courses.groups.show', [$course->id, $group->id]) : route('groups.show', $group->id) }}" class="mb-4">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label">البحث</label>
                                        <input type="text" name="search" class="form-control"
                                               placeholder="ابحث بالاسم، الإيميل أو الهاتف..."
                                               value="{{ request('search') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">الدور</label>
                                        <select name="role" class="form-select">
                                            <option value="">جميع الأدوار</option>
                                            <option value="leader" {{ request('role') == 'leader' ? 'selected' : '' }}>قائد</option>
                                            <option value="member" {{ request('role') == 'member' ? 'selected' : '' }}>عضو</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">الترتيب</label>
                                        <select name="sort" class="form-select">
                                            <option value="joined_at" {{ request('sort', 'joined_at') == 'joined_at' ? 'selected' : '' }}>تاريخ الانضمام</option>
                                            <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>تاريخ التسجيل</option>
                                        </select>
                                        <input type="hidden" name="order" value="{{ request('order', 'desc') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary flex-fill">
                                                <i class="fas fa-search me-1"></i>بحث
                                            </button>
                                            <a href="{{ $course ? route('courses.groups.show', [$course->id, $group->id]) : route('groups.show', $group->id) }}" class="btn btn-outline-secondary" title="إعادة تعيين">
                                                <i class="fas fa-redo me-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            @if($members && $members->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-hover text-nowrap">
                                        <thead>
                                            <tr>
                                                <th width="50">
                                                    <input type="checkbox" id="selectAllMembers" title="تحديد الكل">
                                                </th>
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
                                                        <td>
                                                            <input type="checkbox" class="member-checkbox" value="{{ $memberRecord->student_id }}" data-member-name="{{ $memberRecord->student->name }}">
                                                        </td>
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
                                                                <button type="button" 
                                                                        class="btn btn-sm btn-outline-danger" 
                                                                        title="إزالة"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#removeMemberModal{{ $memberRecord->student_id }}"
                                                                        data-member-name="{{ $memberRecord->student->name }}"
                                                                        data-member-id="{{ $memberRecord->student_id }}">
                                                                    <i class="fas fa-user-times"></i>
                                                                </button>
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
                            <select name="student_id" id="singleStudentSelect" class="form-select" required>
                                <option value="">-- اختر طالب --</option>
                                @foreach($availableStudents ?? [] as $student)
                                    @php
                                        $displayName = $student->name;
                                        if ($student->name_ar) {
                                            $displayName .= ' (' . $student->name_ar . ')';
                                        }
                                        $displayName .= ' - ' . $student->email;
                                    @endphp
                                    <option value="{{ $student->id }}">{{ $displayName }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">
                                <i class="fas fa-search me-1"></i>
                                ابدأ بالكتابة للبحث عن الطالب بالاسم (عربي/إنجليزي) أو البريد الإلكتروني
                            </small>
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
                            <select name="student_ids[]" id="bulkStudentSelect" class="form-select" multiple required>
                                @foreach($availableStudents ?? [] as $student)
                                    @php
                                        $displayName = $student->name;
                                        if ($student->name_ar) {
                                            $displayName .= ' (' . $student->name_ar . ')';
                                        }
                                        $displayName .= ' - ' . $student->email;
                                    @endphp
                                    <option value="{{ $student->id }}">{{ $displayName }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">
                                <i class="fas fa-search me-1"></i>
                                ابدأ بالكتابة للبحث عن الطلاب بالاسم (عربي/إنجليزي) أو البريد الإلكتروني. يمكنك اختيار عدة طلاب.
                            </small>
                            <div id="bulkSelectedCount" class="mt-2 text-primary" style="display: none;">
                                <i class="fas fa-users me-1"></i>
                                <span id="bulkSelectedCountText">0</span> طالب محدد
                            </div>
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

    <!-- Remove Member Modals -->
    @foreach($members as $memberRecord)
        @if($memberRecord->student)
            <div class="modal fade" id="removeMemberModal{{ $memberRecord->student_id }}" tabindex="-1" aria-labelledby="removeMemberModalLabel{{ $memberRecord->student_id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow-lg rounded-4">
                        <div class="modal-header border-0 pb-0">
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center py-4">
                            <div class="mb-4">
                                <div class="avatar avatar-xl mx-auto mb-3 bg-danger-transparent">
                                    <i class="fas fa-user-times fs-24 text-danger"></i>
                                </div>
                                <h5 class="mb-2" id="removeMemberModalLabel{{ $memberRecord->student_id }}">إزالة العضو من المجموعة</h5>
                                <p class="text-muted mb-0">
                                    هل أنت متأكد من إزالة <strong>{{ $memberRecord->student->name }}</strong> من هذه المجموعة؟
                                </p>
                                <p class="text-danger small mt-2 mb-0">
                                    <i class="fas fa-exclamation-circle me-1"></i>
                                    لا يمكن التراجع عن هذا الإجراء!
                                </p>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0 justify-content-center">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i>إلغاء
                            </button>
                            <form action="{{ route('groups.remove-member', [$group->id, $memberRecord->student_id]) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-user-times me-2"></i>نعم، إزالة العضو
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@stop

@section('script')
<script>
    // Scroll to top to show alert message
    if (document.querySelector('.alert')) {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Auto-hide alerts after 10 seconds (longer for bulk operations)
    setTimeout(function() {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 10000);

    // Bulk Selection and Actions
    (function() {
        const selectAllCheckbox = document.getElementById('selectAllMembers');
        const memberCheckboxes = document.querySelectorAll('.member-checkbox');
        const bulkActionsBar = document.getElementById('bulkActionsBar');
        const selectedCountSpan = document.getElementById('selectedCount');
        const bulkRemoveBtn = document.getElementById('bulkRemoveBtn');
        const bulkRemoveCountSpan = document.getElementById('bulkRemoveCount');
        const clearSelectionBtn = document.getElementById('clearSelectionBtn');

        // Update bulk actions bar visibility and counts
        function updateBulkActions() {
            const selected = document.querySelectorAll('.member-checkbox:checked');
            const count = selected.length;
            
            if (count > 0) {
                bulkActionsBar.style.display = 'block';
                selectedCountSpan.textContent = count;
                bulkRemoveCountSpan.textContent = count;
                bulkRemoveBtn.disabled = false;
            } else {
                bulkActionsBar.style.display = 'none';
                bulkRemoveBtn.disabled = true;
            }

            // Update select all checkbox state
            if (selectAllCheckbox) {
                selectAllCheckbox.indeterminate = count > 0 && count < memberCheckboxes.length;
                selectAllCheckbox.checked = count === memberCheckboxes.length && count > 0;
            }
        }

        // Select all checkbox
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                memberCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateBulkActions();
            });
        }

        // Individual checkboxes
        memberCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkActions);
        });

        // Clear selection
        if (clearSelectionBtn) {
            clearSelectionBtn.addEventListener('click', function() {
                memberCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = false;
                }
                updateBulkActions();
            });
        }

        // Bulk remove
        if (bulkRemoveBtn) {
            bulkRemoveBtn.addEventListener('click', function() {
                const selected = Array.from(document.querySelectorAll('.member-checkbox:checked'));
                const selectedIds = selected.map(cb => cb.value);
                const selectedNames = selected.map(cb => cb.getAttribute('data-member-name'));

                if (selectedIds.length === 0) {
                    alert('يرجى تحديد عضو واحد على الأقل');
                    return;
                }

                const confirmMessage = `هل أنت متأكد من إزالة ${selectedIds.length} عضو من المجموعة؟\n\nالأعضاء:\n${selectedNames.join('\n')}\n\nسيتم أيضاً إلغاء تسجيلهم من الكورسات المرتبطة بهذه المجموعة.`;

                if (confirm(confirmMessage)) {
                    // Create form and submit
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("groups.bulk-remove-members", $group->id) }}';
                    
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    form.appendChild(csrfToken);

                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);

                    selectedIds.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'member_ids[]';
                        input.value = id;
                        form.appendChild(input);
                    });

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Initial update
        updateBulkActions();
    })();

    // Initialize Choices.js for single student select
    let singleChoicesInstance = null;
    const singleMemberModal = document.getElementById('addMemberModal');
    if (singleMemberModal) {
        singleMemberModal.addEventListener('shown.bs.modal', function() {
            const singleStudentSelect = document.getElementById('singleStudentSelect');
            
            if (singleStudentSelect && !singleChoicesInstance) {
                const initSingleChoices = function() {
                    if (typeof Choices !== 'undefined' || typeof window.Choices !== 'undefined') {
                        const ChoicesClass = typeof Choices !== 'undefined' ? Choices : window.Choices;
                        
                        if (singleStudentSelect._choicesjs) {
                            singleStudentSelect._choicesjs.destroy();
                        }
                        
                        singleChoicesInstance = new ChoicesClass(singleStudentSelect, {
                            searchEnabled: true,
                            searchChoices: true,
                            placeholder: true,
                            placeholderValue: '-- اختر طالب --',
                            searchPlaceholderValue: 'ابحث بالاسم (عربي/إنجليزي) أو البريد الإلكتروني...',
                            itemSelectText: '',
                            shouldSort: false,
                            allowHTML: true,
                            fuseOptions: {
                                threshold: 0.4,
                                minMatchCharLength: 1,
                                includeScore: false
                            },
                        });
                    } else {
                        setTimeout(initSingleChoices, 100);
                    }
                };
                
                initSingleChoices();
            }
        });
    }

    // Initialize Choices.js for bulk student select
    let bulkChoicesInstance = null;
    
    // Initialize when modal is shown
    const bulkMembersModal = document.getElementById('addBulkMembersModal');
    if (bulkMembersModal) {
        bulkMembersModal.addEventListener('shown.bs.modal', function() {
            const bulkStudentSelect = document.getElementById('bulkStudentSelect');
            
            if (bulkStudentSelect && !bulkChoicesInstance) {
                // Wait for Choices.js to be available
                const initBulkChoices = function() {
                    if (typeof Choices !== 'undefined' || typeof window.Choices !== 'undefined') {
                        const ChoicesClass = typeof Choices !== 'undefined' ? Choices : window.Choices;
                        
                        // Destroy existing instance if any
                        if (bulkStudentSelect._choicesjs) {
                            bulkStudentSelect._choicesjs.destroy();
                        }
                        
                        bulkChoicesInstance = new ChoicesClass(bulkStudentSelect, {
                            removeItemButton: true,
                            searchEnabled: true,
                            searchChoices: true,
                            placeholder: true,
                            placeholderValue: 'اختر طالب أو أكثر',
                            searchPlaceholderValue: 'ابحث بالاسم (عربي/إنجليزي) أو البريد الإلكتروني...',
                            itemSelectText: '',
                            shouldSort: false,
                            allowHTML: true,
                            fuseOptions: {
                                threshold: 0.4,
                                minMatchCharLength: 1,
                                includeScore: false
                            },
                            classNames: {
                                containerOuter: 'choices',
                                containerInner: 'choices__inner',
                                input: 'choices__input',
                                inputCloned: 'choices__input--cloned',
                                list: 'choices__list',
                                listItems: 'choices__list--multiple',
                                listSingle: 'choices__list--single',
                                listDropdown: 'choices__list--dropdown',
                                item: 'choices__item',
                                itemSelectable: 'choices__item--selectable',
                                itemDisabled: 'choices__item--disabled',
                                itemChoice: 'choices__item--choice',
                                placeholder: 'choices__placeholder',
                                group: 'choices__group',
                                groupHeading: 'choices__heading',
                                button: 'choices__button',
                                activeState: 'is-active',
                                focusState: 'is-focused',
                                openState: 'is-open',
                                disabledState: 'is-disabled',
                                highlightedState: 'is-highlighted',
                                selectedState: 'is-selected',
                                flippedState: 'is-flipped',
                                loadingState: 'is-loading',
                                noResults: 'has-no-results',
                                noChoices: 'has-no-choices'
                            }
                        });

                        // Update selected count when choices change
                        bulkStudentSelect.addEventListener('change', function() {
                            updateBulkSelectedCount();
                        });

                        bulkStudentSelect.addEventListener('addItem', function() {
                            updateBulkSelectedCount();
                        });

                        bulkStudentSelect.addEventListener('removeItem', function() {
                            updateBulkSelectedCount();
                        });

                        // Initial count update
                        updateBulkSelectedCount();
                    } else {
                        // Retry after a short delay if Choices.js is not loaded yet
                        setTimeout(initBulkChoices, 100);
                    }
                };
                
                initBulkChoices();
            }
        });

        // Clean up when modal is hidden
        bulkMembersModal.addEventListener('hidden.bs.modal', function() {
            // Don't destroy, just keep it for next time
        });
    }

    // Update selected count display
    function updateBulkSelectedCount() {
        const selectedCountDiv = document.getElementById('bulkSelectedCount');
        const selectedCountText = document.getElementById('bulkSelectedCountText');
        const bulkStudentSelect = document.getElementById('bulkStudentSelect');
        
        if (bulkStudentSelect) {
            const selectedOptions = Array.from(bulkStudentSelect.selectedOptions);
            const count = selectedOptions.length;
            
            if (count > 0) {
                if (selectedCountDiv) selectedCountDiv.style.display = 'block';
                if (selectedCountText) selectedCountText.textContent = count;
            } else {
                if (selectedCountDiv) selectedCountDiv.style.display = 'none';
            }
        }
    }
</script>
@stop
