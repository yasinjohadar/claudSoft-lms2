@extends('admin.layouts.master')

@section('page-title')
    طلبات الانضمام - {{ $group->name }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">
                        <i class="bi bi-person-plus me-2"></i>
                        طلبات الانضمام للمجموعة
                    </h5>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.groups.index', $course->id) }}">المجموعات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.groups.show', [$course->id, $group->id]) }}">{{ $group->name }}</a></li>
                            <li class="breadcrumb-item active">طلبات الانضمام</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('courses.groups.show', [$course->id, $group->id]) }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-right me-2"></i>
                        رجوع
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Group Info Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-2">{{ $group->name }}</h5>
                            <p class="text-muted mb-0">{{ Str::limit($group->description, 150) }}</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="mb-2">
                                <span class="badge bg-primary fs-6">عدد الأعضاء: {{ $group->members_count ?? 0 }}</span>
                                @if($group->max_members)
                                    <span class="badge bg-info fs-6">الحد الأقصى: {{ $group->max_members }}</span>
                                @endif
                            </div>
                            <div>
                                @if($group->allow_membership_requests)
                                    <span class="badge bg-success">طلب الانضمام مفعل</span>
                                @else
                                    <span class="badge bg-secondary">طلب الانضمام معطل</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Form -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('courses.groups.membership-requests', [$course->id, $group->id]) }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <input type="text"
                                       name="search"
                                       class="form-control"
                                       placeholder="البحث بالاسم، الإيميل أو الهاتف..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select">
                                    <option value="">جميع الحالات</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد المراجعة</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>مقبول</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i> بحث
                                </button>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('courses.groups.membership-requests', [$course->id, $group->id]) }}" class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-arrow-clockwise"></i> إعادة تعيين
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Requests Table -->
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-list-ul me-2"></i>
                        طلبات الانضمام ({{ $requests->total() }})
                    </h6>
                    <div class="d-flex gap-2">
                        <div id="approve-selected-container" style="display: none;">
                        <form id="approve-selected-form" action="{{ route('courses.groups.membership-requests.approve-multiple', [$course->id, $group->id]) }}" method="POST" class="d-inline">
                            @csrf
                            <div id="selected-request-ids-container"></div>
                            <button type="button" class="btn btn-sm btn-success" id="approve-selected-btn" data-bs-toggle="modal" data-bs-target="#approveSelectedModal">
                                <i class="bi bi-check-circle me-1"></i>
                                قبول المحدد
                            </button>
                        </form>
                        </div>
                        <div id="delete-selected-container" style="display: none;">
                        <form id="delete-selected-form" action="{{ route('courses.groups.membership-requests.delete-multiple', [$course->id, $group->id]) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <div id="delete-request-ids-container"></div>
                            <button type="button" class="btn btn-sm btn-danger" id="delete-selected-btn" data-bs-toggle="modal" data-bs-target="#deleteSelectedModal">
                                <i class="bi bi-trash me-1"></i>
                                حذف المحدد
                            </button>
                        </form>
                        </div>
                        @if(isset($pendingCount) && $pendingCount > 0)
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#approveAllModal">
                                <i class="bi bi-check-all me-1"></i>
                                قبول الكل ({{ $pendingCount }})
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="select-all-checkbox" title="تحديد الكل">
                                    </th>
                                    <th>#</th>
                                    <th>اسم الطالب</th>
                                    <th>البريد الإلكتروني</th>
                                    <th>رقم الهاتف</th>
                                    <th>تاريخ الطلب</th>
                                    <th>موعد تسديد الرسوم</th>
                                    <th>الرسالة</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $request)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="request-checkbox" name="request_ids[]" value="{{ $request->id }}" data-status="{{ $request->status }}">
                                        </td>
                                        <td>{{ $request->id }}</td>
                                        <td>
                                            <strong>{{ $request->student->name }}</strong>
                                            @if($request->student->name_ar)
                                                <br><small class="text-muted">{{ $request->student->name_ar }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $request->student->email }}</td>
                                        <td>
                                            @if($request->student->phone)
                                                {{ $request->student->phone }}
                                                @if($request->student->country_code)
                                                    ({{ $request->student->country_code }})
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $request->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            @if($request->payment_date)
                                                <span class="badge bg-info">{{ $request->payment_date->format('Y-m-d') }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($request->message)
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-info" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#messageModal{{ $request->id }}">
                                                    <i class="bi bi-envelope"></i> عرض
                                                </button>
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
                                            @if($request->status === 'pending')
                                                <div class="btn-group" role="group">
                                                    <form action="{{ route('courses.groups.membership-requests.approve', [$course->id, $group->id, $request->id]) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('هل أنت متأكد من قبول طلب الانضمام؟');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" title="قبول">
                                                            <i class="bi bi-check-circle"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#rejectModal{{ $request->id }}"
                                                            title="رفض">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </div>
                                            @elseif($request->status === 'rejected')
                                                <div class="btn-group" role="group">
                                                    <form action="{{ route('courses.groups.membership-requests.approve', [$course->id, $group->id, $request->id]) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('هل أنت متأكد من قبول طلب الانضمام المرفوض؟');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" title="قبول">
                                                            <i class="bi bi-check-circle"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('courses.groups.membership-requests.delete', [$course->id, $group->id, $request->id]) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('هل أنت متأكد من حذف طلب الانضمام نهائياً؟ سيتم الاحتفاظ بالتسجيل المرتبط.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="حذف نهائي">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            @elseif($request->status === 'approved')
                                                <div class="btn-group" role="group">
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#rejectModal{{ $request->id }}"
                                                            title="رفض">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                    <form action="{{ route('courses.groups.membership-requests.delete', [$course->id, $group->id, $request->id]) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('هل أنت متأكد من حذف سجل طلب الانضمام نهائياً؟ لن يؤثر ذلك على انضمام الطالب للمجموعة.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف نهائي">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- Message Modal -->
                                    @if($request->message)
                                        <div class="modal fade" id="messageModal{{ $request->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">رسالة من الطالب</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>{{ $request->message }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Reject Modal -->
                                    <div class="modal fade" id="rejectModal{{ $request->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('courses.groups.membership-requests.reject', [$course->id, $group->id, $request->id]) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">رفض طلب الانضمام</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>هل أنت متأكد من رفض طلب انضمام <strong>{{ $request->student->name }}</strong>؟</p>
                                                        <div class="mb-3">
                                                            <label class="form-label">ملاحظات (اختياري)</label>
                                                            <textarea name="admin_notes" 
                                                                      class="form-control" 
                                                                      rows="3"
                                                                      placeholder="أضف ملاحظات حول سبب الرفض..."></textarea>
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
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5">
                                            <i class="bi bi-inbox display-4 text-muted mb-3 d-block"></i>
                                            <h5 class="text-muted">لا توجد طلبات</h5>
                                            <p class="text-muted">لا توجد طلبات انضمام لهذه المجموعة</p>
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

    <!-- Approve Selected Modal -->
    <div class="modal fade" id="approveSelectedModal" tabindex="-1" aria-labelledby="approveSelectedModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approveSelectedModalLabel">
                        <i class="bi bi-check-circle me-2"></i>
                        قبول الطلبات المحددة
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>هل أنت متأكد من قبول <strong id="selected-count">0</strong> طلب انضمام محدد؟</p>
                    <p class="text-muted small mb-0">سيتم إضافة الطلاب للمجموعة تلقائياً بعد الموافقة.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-success" id="confirm-approve-selected">
                        <i class="bi bi-check-circle me-1"></i>
                        نعم، قبول المحدد
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve All Modal -->
    @if(isset($pendingCount) && $pendingCount > 0)
    <div class="modal fade" id="approveAllModal" tabindex="-1" aria-labelledby="approveAllModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approveAllModalLabel">
                        <i class="bi bi-check-all me-2"></i>
                        قبول جميع الطلبات المعلقة
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>هل أنت متأكد من قبول جميع الطلبات المعلقة (<strong>{{ $pendingCount }}</strong>)؟</p>
                    <p class="text-muted small mb-0">سيتم إضافة جميع الطلاب للمجموعة تلقائياً بعد الموافقة.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <form id="approve-all-form" action="{{ route('courses.groups.membership-requests.approve-all', [$course->id, $group->id]) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-all me-1"></i>
                            نعم، قبول الكل
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Delete Selected Modal -->
    <div class="modal fade" id="deleteSelectedModal" tabindex="-1" aria-labelledby="deleteSelectedModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteSelectedModalLabel">
                        <i class="bi bi-trash me-2"></i>
                        حذف الطلبات المحددة
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>هل أنت متأكد من حذف <strong id="delete-selected-count">0</strong> طلب انضمام نهائياً؟</p>
                    <p class="text-muted small mb-0">سيتم حذف سجلات الطلبات فقط ولن يؤثر ذلك على انضمام من تم قبولهم للمجموعة.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-danger" id="confirm-delete-selected">
                        <i class="bi bi-trash me-1"></i>
                        نعم، حذف المحدد
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    const requestCheckboxes = document.querySelectorAll('.request-checkbox');
    const approveSelectedContainer = document.getElementById('approve-selected-container');
    const approveSelectedForm = document.getElementById('approve-selected-form');
    const selectedRequestIdsContainer = document.getElementById('selected-request-ids-container');

    // Select/Deselect All
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            requestCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });
    }

    // Update bulk actions visibility
    const deleteSelectedContainer = document.getElementById('delete-selected-container');
    const deleteSelectedForm = document.getElementById('delete-selected-form');
    const deleteRequestIdsContainer = document.getElementById('delete-request-ids-container');

    function updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.request-checkbox:checked');
        const hasPending = Array.from(checkedBoxes).some(cb => cb.getAttribute('data-status') === 'pending');
        if (checkedBoxes.length > 0 && approveSelectedContainer && hasPending) {
            approveSelectedContainer.style.display = 'inline-block';
        } else if (approveSelectedContainer) {
            approveSelectedContainer.style.display = 'none';
        }
        if (checkedBoxes.length > 0 && deleteSelectedContainer) {
            deleteSelectedContainer.style.display = 'inline-block';
        } else if (deleteSelectedContainer) {
            deleteSelectedContainer.style.display = 'none';
        }
    }

    // Update select all checkbox state
    function updateSelectAll() {
        if (selectAllCheckbox && requestCheckboxes.length > 0) {
            const allChecked = Array.from(requestCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(requestCheckboxes).some(cb => cb.checked);
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = someChecked && !allChecked;
        }
    }

    // Listen to individual checkbox changes
    requestCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkActions();
            updateSelectAll();
        });
    });

    // Update selected count in modal when opening
    const approveSelectedBtn = document.getElementById('approve-selected-btn');
    const approveSelectedModal = document.getElementById('approveSelectedModal');
    const selectedCountSpan = document.getElementById('selected-count');
    const confirmApproveSelectedBtn = document.getElementById('confirm-approve-selected');

    if (approveSelectedModal && selectedCountSpan && confirmApproveSelectedBtn) {
        approveSelectedModal.addEventListener('show.bs.modal', function() {
            const checkedBoxes = document.querySelectorAll('.request-checkbox:checked');
            selectedCountSpan.textContent = checkedBoxes.length;
        });

        // Handle confirm button click
        confirmApproveSelectedBtn.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.request-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('يرجى تحديد طلب واحد على الأقل');
                return;
            }

            // Clear existing hidden inputs
            selectedRequestIdsContainer.innerHTML = '';
            
            // Create hidden inputs for each selected ID
            checkedBoxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'request_ids[]';
                input.value = checkbox.value;
                selectedRequestIdsContainer.appendChild(input);
            });

            // Submit the form
            if (approveSelectedForm) {
                approveSelectedForm.submit();
            }
        });
    }

    // Delete selected: update count in modal and confirm
    const deleteSelectedModal = document.getElementById('deleteSelectedModal');
    const deleteSelectedCountSpan = document.getElementById('delete-selected-count');
    const confirmDeleteSelectedBtn = document.getElementById('confirm-delete-selected');

    if (deleteSelectedModal && deleteSelectedCountSpan && confirmDeleteSelectedBtn) {
        deleteSelectedModal.addEventListener('show.bs.modal', function() {
            const checkedBoxes = document.querySelectorAll('.request-checkbox:checked');
            deleteSelectedCountSpan.textContent = checkedBoxes.length;
        });

        confirmDeleteSelectedBtn.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.request-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('يرجى تحديد طلب واحد على الأقل');
                return;
            }
            deleteRequestIdsContainer.innerHTML = '';
            checkedBoxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'request_ids[]';
                input.value = checkbox.value;
                deleteRequestIdsContainer.appendChild(input);
            });
            if (deleteSelectedForm) {
                deleteSelectedForm.submit();
            }
        });
    }

    // Initial update
    updateBulkActions();
    updateSelectAll();
});
</script>
@stop
