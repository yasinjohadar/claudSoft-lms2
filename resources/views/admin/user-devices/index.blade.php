@extends('admin.layouts.master')

@section('page-title')
    أجهزة المستخدمين
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">أجهزة المستخدمين</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active">أجهزة المستخدمين</li>
                    </ol>
                </nav>
            </div>
            <div class="btn-list mt-3 mt-md-0">
                <button type="button" class="btn btn-danger btn-wave" data-bs-toggle="modal" data-bs-target="#deleteAllModal">
                    <i class="fas fa-trash-alt me-2"></i>حذف الكل
                </button>
                <button type="button" class="btn btn-warning btn-wave" data-bs-toggle="modal" data-bs-target="#deleteOldModal">
                    <i class="fas fa-clock me-2"></i>حذف الأجهزة القديمة
                </button>
                <button type="button" class="btn btn-info btn-wave" data-bs-toggle="modal" data-bs-target="#deleteInactiveModal">
                    <i class="fas fa-power-off me-2"></i>حذف الأجهزة غير النشطة
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">إجمالي الأجهزة</p>
                                <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-md bg-primary-transparent rounded-circle">
                                    <i class="fas fa-mobile-alt fs-18"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">الأجهزة الموثوقة</p>
                                <h4 class="mb-0 text-success">{{ number_format($stats['trusted']) }}</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-md bg-success-transparent rounded-circle">
                                    <i class="fas fa-shield-check fs-18"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">الأجهزة المحظورة</p>
                                <h4 class="mb-0 text-danger">{{ number_format($stats['blocked']) }}</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-md bg-danger-transparent rounded-circle">
                                    <i class="fas fa-ban fs-18"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">الأجهزة النشطة</p>
                                <h4 class="mb-0 text-info">{{ number_format($stats['active']) }}</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-md bg-info-transparent rounded-circle">
                                    <i class="fas fa-circle fs-18"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-filter me-2"></i>الفلاتر
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.user-devices.index') }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">البحث</label>
                            <input type="text" name="search" class="form-control" 
                                   value="{{ request('search') }}" 
                                   placeholder="اسم المستخدم، البريد، IP...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">المستخدم</label>
                            <select name="user_id" class="form-select">
                                <option value="">الكل</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">نوع الجهاز</label>
                            <select name="device_type" class="form-select">
                                <option value="">الكل</option>
                                <option value="mobile" {{ request('device_type') == 'mobile' ? 'selected' : '' }}>جوال</option>
                                <option value="tablet" {{ request('device_type') == 'tablet' ? 'selected' : '' }}>تابلت</option>
                                <option value="desktop" {{ request('device_type') == 'desktop' ? 'selected' : '' }}>سطح مكتب</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">الحالة</label>
                            <select name="status" class="form-select">
                                <option value="">الكل</option>
                                <option value="trusted" {{ request('status') == 'trusted' ? 'selected' : '' }}>موثوق</option>
                                <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>محظور</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">من تاريخ</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bulk Action Bar -->
        <div class="card d-none" id="bulkActionBar">
            <div class="card-body py-2">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <span class="me-3 fw-semibold">
                            <span id="selectedCount">0</span> جهاز محدد
                        </span>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary btn-wave" onclick="selectAll()">
                                <i class="fas fa-check-double me-1"></i>تحديد الكل
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-wave" onclick="deselectAll()">
                                <i class="fas fa-times me-1"></i>إلغاء التحديد
                            </button>
                        </div>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-danger btn-wave" onclick="bulkDeleteSelected()">
                            <i class="fas fa-trash-alt me-1"></i>حذف المحدد
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Devices Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">قائمة الأجهزة</h5>
            </div>
            <div class="card-body">
                @if($devices->count() > 0)
                    <form id="bulkDeleteForm" action="{{ route('admin.user-devices.bulk-delete') }}" method="POST">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th width="40">
                                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onchange="toggleSelectAll(this)">
                                        </th>
                                        <th>#</th>
                                        <th>المستخدم</th>
                                        <th>معلومات الجهاز</th>
                                        <th>عدد مرات الدخول</th>
                                        <th>أول استخدام</th>
                                        <th>آخر استخدام</th>
                                        <th>الموقع</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($devices as $device)
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="form-check-input device-checkbox" value="{{ $device->id }}" onchange="updateBulkActionBar()">
                                            </td>
                                            <td>{{ $devices->firstItem() + $loop->index }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($device->user)
                                                        @if($device->user->avatar)
                                                            <img src="{{ asset('storage/' . $device->user->avatar) }}" 
                                                                 alt="{{ $device->user->name }}" 
                                                                 class="avatar avatar-sm rounded-circle me-2">
                                                        @else
                                                            <div class="avatar avatar-sm rounded-circle bg-primary-transparent me-2">
                                                                <span class="fw-bold">{{ substr($device->user->name, 0, 1) }}</span>
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <strong>{{ $device->user->name }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ $device->user->email }}</small>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <small>{{ $device->device_info }}</small>
                                                @if($device->device_name)
                                                    <br>
                                                    <strong class="text-primary">{{ $device->device_name }}</strong>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info-transparent text-info">
                                                    {{ number_format($device->total_logins) }}
                                                </span>
                                            </td>
                                            <td>
                                                <small>{{ $device->first_used_human }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $device->last_used_human }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $device->location_formatted }}</small>
                                                @if($device->last_ip_address)
                                                    <br>
                                                    <small class="text-muted">{{ $device->last_ip_address }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="{{ $device->status_badge['class'] }}">
                                                    <i class="fas {{ $device->status_badge['icon'] }} me-1"></i>
                                                    {{ $device->status_badge['text'] }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.user-devices.show', $device->id) }}" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="عرض التفاصيل">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($device->is_blocked)
                                                        <form action="{{ route('admin.user-devices.unblock', $device->id) }}" 
                                                              method="POST" 
                                                              class="d-inline"
                                                              onsubmit="return confirm('هل أنت متأكد من إلغاء حظر هذا الجهاز؟');">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-success" title="إلغاء الحظر">
                                                                <i class="fas fa-unlock"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form action="{{ route('admin.user-devices.block', $device->id) }}" 
                                                              method="POST" 
                                                              class="d-inline"
                                                              onsubmit="return confirm('هل أنت متأكد من حظر هذا الجهاز؟');">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="حظر">
                                                                <i class="fas fa-ban"></i>
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
                    </form>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $devices->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-mobile-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">لا توجد أجهزة</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Delete All Modal -->
<div class="modal fade" id="deleteAllModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>تحذير: حذف جميع الأجهزة</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.user-devices.delete-all') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>هذا الإجراء لا يمكن التراجع عنه!</strong>
                    </div>
                    <p>سيتم حذف <strong>جميع الأجهزة</strong> المسجلة في النظام ({{ number_format($stats['total']) }} جهاز).</p>
                    <p class="mb-0">هل أنت متأكد من هذا الإجراء؟</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('هل أنت متأكد تماماً؟ سيتم حذف جميع الأجهزة نهائياً!')">
                        <i class="fas fa-trash-alt me-1"></i>نعم، حذف الكل
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Old Devices Modal -->
<div class="modal fade" id="deleteOldModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-clock me-2"></i>حذف الأجهزة القديمة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.user-devices.delete-old') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>حذف الأجهزة التي لم تُستخدم منذ فترة محددة.</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">حذف الأجهزة غير المستخدمة منذ (بالأيام):</label>
                        <input type="number" name="days" class="form-control" value="90" min="1" max="365" required>
                        <small class="text-muted">مثال: 90 يوم = حذف الأجهزة التي لم تُستخدم منذ 3 أشهر</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning" onclick="return confirm('هل أنت متأكد من حذف الأجهزة القديمة؟')">
                        <i class="fas fa-trash-alt me-1"></i>حذف الأجهزة القديمة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Inactive Devices Modal -->
<div class="modal fade" id="deleteInactiveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title"><i class="fas fa-power-off me-2"></i>حذف الأجهزة غير النشطة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.user-devices.delete-inactive') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>حذف الأجهزة ذات النشاط المنخفض جداً.</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">الحد الأقصى لعدد تسجيلات الدخول:</label>
                        <input type="number" name="max_logins" class="form-control" value="1" min="0" max="100" required>
                        <small class="text-muted">سيتم حذف الأجهزة التي سجلت دخولها هذا العدد أو أقل</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-info" onclick="return confirm('هل أنت متأكد من حذف الأجهزة غير النشطة؟')">
                        <i class="fas fa-trash-alt me-1"></i>حذف الأجهزة غير النشطة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function toggleSelectAll(source) {
    const checkboxes = document.querySelectorAll('.device-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = source.checked;
    });
    updateBulkActionBar();
}

function selectAll() {
    const checkboxes = document.querySelectorAll('.device-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    document.getElementById('selectAllCheckbox').checked = true;
    updateBulkActionBar();
}

function deselectAll() {
    const checkboxes = document.querySelectorAll('.device-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('selectAllCheckbox').checked = false;
    updateBulkActionBar();
}

function updateBulkActionBar() {
    const checkboxes = document.querySelectorAll('.device-checkbox:checked');
    const count = checkboxes.length;
    const bulkActionBar = document.getElementById('bulkActionBar');
    const selectedCount = document.getElementById('selectedCount');
    
    selectedCount.textContent = count;
    
    if (count > 0) {
        bulkActionBar.classList.remove('d-none');
    } else {
        bulkActionBar.classList.add('d-none');
    }
}

function bulkDeleteSelected() {
    const checkboxes = document.querySelectorAll('.device-checkbox:checked');
    const count = checkboxes.length;
    
    if (count === 0) {
        alert('يرجى تحديد جهاز واحد على الأقل');
        return;
    }
    
    if (!confirm(`هل أنت متأكد من حذف ${count} جهاز؟`)) {
        return;
    }
    
    const form = document.getElementById('bulkDeleteForm');
    checkboxes.forEach(checkbox => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'device_ids[]';
        input.value = checkbox.value;
        form.appendChild(input);
    });
    
    form.submit();
}
</script>
@endpush
