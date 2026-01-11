@extends('admin.layouts.master')

@section('page-title')
    إضافة أعضاء من الكروبات - {{ $camp->name }}
@stop

@section('css')
<style>
    .table-responsive {
        max-height: 400px;
        overflow-y: auto;
    }
    .table thead th {
        position: sticky;
        top: 0;
        background-color: #f8f9fa;
        z-index: 10;
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

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-exclamation-circle me-2"></i>يوجد أخطاء في النموذج:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إضافة أعضاء من الكروبات</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('training-camps.index') }}">المعسكرات التدريبية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('training-camps.show', $camp->id) }}">{{ $camp->name }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">إضافة من الكروبات</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Card -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        إضافة أعضاء من الكروبات - {{ $camp->name }}
                    </div>
                    <div class="ms-auto">
                        <a href="{{ route('training-camps.show', $camp->id) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-right me-1"></i>العودة
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('training-camps.enrollments.bulk-store', $camp->id) }}" method="POST" id="bulk-enrollment-form">
                        @csrf
                        <div class="mb-3">
                            <label for="group_id" class="form-label">اختر الكروب <span class="text-danger">*</span></label>
                            <select name="group_id" id="group_id" class="form-select" onchange="loadGroupStudents(this.value)" required>
                                <option value="">اختر الكروب</option>
                                @foreach($courseGroups as $group)
                                    <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                @endforeach
                            </select>
                            @error('group_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div id="group-students-container" style="display: none;">
                            <div class="mb-3 d-flex justify-content-between align-items-center">
                                <label class="form-label mb-0">الطلاب في الكروب</label>
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllGroupStudents()">
                                        <i class="fas fa-check-double me-1"></i>تحديد الكل
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllGroupStudents()">
                                        <i class="fas fa-times me-1"></i>إلغاء التحديد
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="50">
                                                <input type="checkbox" id="select-all-students" onchange="toggleAllStudents(this)">
                                            </th>
                                            <th>الاسم</th>
                                            <th>البريد الإلكتروني</th>
                                            <th>الهاتف</th>
                                        </tr>
                                    </thead>
                                    <tbody id="group-students-table">
                                        <!-- Students will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                            <div id="group-students-empty" class="text-center text-muted py-4" style="display: none;">
                                <i class="fas fa-users-slash fa-2x mb-2"></i>
                                <p>لا يوجد طلاب متاحين في هذا الكروب</p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">الحالة</label>
                            <select name="status" id="status" class="form-select" value="{{ old('status', 'pending') }}">
                                <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>مقبول</option>
                                <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                                <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="payment_status" class="form-label">حالة الدفع</label>
                            <select name="payment_status" id="payment_status" class="form-select" value="{{ old('payment_status', 'unpaid') }}">
                                <option value="unpaid" {{ old('payment_status', 'unpaid') == 'unpaid' ? 'selected' : '' }}>غير مدفوع</option>
                                <option value="paid" {{ old('payment_status') == 'paid' ? 'selected' : '' }}>مدفوع</option>
                                <option value="refunded" {{ old('payment_status') == 'refunded' ? 'selected' : '' }}>مسترد</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">ملاحظات</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('training-camps.show', $camp->id) }}" class="btn btn-secondary">إلغاء</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-users me-1"></i>إضافة المحددين
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
<script>
    // Load group students
    function loadGroupStudents(groupId) {
        if (!groupId) {
            document.getElementById('group-students-container').style.display = 'none';
            return;
        }

        const container = document.getElementById('group-students-container');
        const table = document.getElementById('group-students-table');
        const empty = document.getElementById('group-students-empty');
        
        container.style.display = 'block';
        table.innerHTML = '<tr><td colspan="4" class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> جاري التحميل...</td></tr>';
        empty.style.display = 'none';

        fetch(`{{ route('training-camps.enrollments.group-students', [$camp->id, ':id']) }}`.replace(':id', groupId), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.students.length > 0) {
                let html = '';
                data.students.forEach(student => {
                    html += `
                        <tr>
                            <td>
                                <input type="checkbox" name="student_ids[]" value="${student.id}" class="group-student-checkbox">
                            </td>
                            <td>${student.name}</td>
                            <td>${student.email}</td>
                            <td>${student.phone}</td>
                        </tr>
                    `;
                });
                table.innerHTML = html;
                empty.style.display = 'none';
            } else {
                table.innerHTML = '';
                empty.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            table.innerHTML = '<tr><td colspan="4" class="text-center text-danger">حدث خطأ أثناء تحميل الطلاب</td></tr>';
        });
    }

    // Select all group students
    function selectAllGroupStudents() {
        document.querySelectorAll('.group-student-checkbox').forEach(checkbox => {
            checkbox.checked = true;
        });
        document.getElementById('select-all-students').checked = true;
    }

    // Deselect all group students
    function deselectAllGroupStudents() {
        document.querySelectorAll('.group-student-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        document.getElementById('select-all-students').checked = false;
    }

    // Toggle all students
    function toggleAllStudents(checkbox) {
        document.querySelectorAll('.group-student-checkbox').forEach(cb => {
            cb.checked = checkbox.checked;
        });
    }

    // Form submission validation
    document.getElementById('bulk-enrollment-form').addEventListener('submit', function(e) {
        const selectedStudents = Array.from(document.querySelectorAll('.group-student-checkbox:checked'));
        if (selectedStudents.length === 0) {
            e.preventDefault();
            alert('يرجى اختيار طالب واحد على الأقل');
            return false;
        }
    });

    // Load group students if group_id is preselected (after validation error)
    @if(old('group_id'))
        document.addEventListener('DOMContentLoaded', function() {
            loadGroupStudents({{ old('group_id') }});
        });
    @endif
</script>
@stop

