@extends('admin.layouts.master')

@section('page-title')
    اختيار متعدد - {{ $course->title }}
@stop

@section('css')
<style>
    .student-card {
        border: 2px solid #e9ecef;
        border-radius: 14px;
        padding: 1.25rem;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        background: #fff;
        overflow: hidden;
    }

    .student-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        opacity: 0;
        transition: opacity 0.3s;
    }

    .student-card:hover {
        border-color: #667eea;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.15);
        transform: translateY(-3px);
    }

    .student-card:hover::before {
        opacity: 1;
    }

    .student-card.selected {
        border-color: #667eea;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.12) 0%, rgba(118, 75, 162, 0.12) 100%);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.2);
        transform: translateY(-3px);
    }

    .student-card.selected::before {
        opacity: 1;
        height: 5px;
    }

    .student-card input[type="checkbox"] {
        width: 22px;
        height: 22px;
        cursor: pointer;
        accent-color: #667eea;
        border-radius: 6px;
    }

    .student-avatar-sm {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        object-fit: cover;
        border: 2px solid #e9ecef;
        transition: all 0.3s;
    }

    .student-card:hover .student-avatar-sm,
    .student-card.selected .student-avatar-sm {
        border-color: #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        transform: scale(1.05);
    }

    .student-info h6 {
        font-size: 1rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.25rem;
    }

    .student-info small {
        font-size: 0.8rem;
        color: #6c757d;
    }

    .selection-summary {
        position: sticky;
        bottom: 0;
        background: white;
        border-top: 4px solid #667eea;
        padding: 1.75rem 2rem;
        box-shadow: 0 -8px 30px rgba(0,0,0,0.12);
        z-index: 100;
        animation: slideUp 0.4s ease-out;
    }

    @keyframes slideUp {
        from {
            transform: translateY(100%);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .filter-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }

    .filter-card h6 {
        font-weight: 700;
        margin-bottom: 1.25rem;
        font-size: 1.1rem;
    }

    .filter-card .form-control,
    .filter-card .form-select {
        border: none;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
    }

    .filter-card .form-check-input {
        width: 50px;
        height: 26px;
        cursor: pointer;
    }

    .filter-card .form-check-label {
        font-weight: 600;
        margin-top: 2px;
    }

    .badge {
        padding: 0.4rem 0.75rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.8rem;
    }

    .student-avatar-placeholder {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        font-weight: 700;
        color: white;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: 2px solid #e9ecef;
        transition: all 0.3s;
    }

    .student-card:hover .student-avatar-placeholder,
    .student-card.selected .student-avatar-placeholder {
        border-color: #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        transform: scale(1.05);
    }

    #selectedCount {
        font-size: 1.8rem;
        font-weight: 800;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
</style>
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">اختيار متعدد للتسجيل</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.show', $course->id) }}">{{ $course->title }}</a></li>
                            <li class="breadcrumb-item active">اختيار متعدد</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Main Card -->
            <div class="card custom-card">
                <form action="{{ route('courses.enrollments.select-multiple.process', $course->id) }}" method="POST" id="selectForm">
                    @csrf

                    <!-- Filters -->
                    <div class="card-body" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px 12px 0 0;">
                        <h6 class="mb-3">فلترة الطلاب</h6>
                        <div class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="searchInput" placeholder="بحث بالاسم أو البريد...">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="departmentFilter">
                                <option value="">جميع الأقسام</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="statusFilter">
                                <option value="">جميع الحالات</option>
                                <option value="active">نشط</option>
                                <option value="inactive">غير نشط</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="selectAllCheckbox">
                                <label class="form-check-label" for="selectAllCheckbox">
                                    تحديد الكل
                                </label>
                            </div>
                        </div>
                        </div>
                    </div>

                    <!-- Students Grid -->
                    <div class="card-body">
                        <div class="row" id="studentsContainer">
                    @forelse($students as $student)
                        <div class="col-md-6 col-lg-4 mb-3 student-item"
                             data-name="{{ strtolower($student->name) }}"
                             data-email="{{ strtolower($student->email) }}"
                             data-department="{{ $student->department_id }}"
                             data-status="{{ $student->is_active ? 'active' : 'inactive' }}">
                            <div class="student-card" onclick="toggleStudent(this)">
                                <div class="d-flex align-items-center">
                                    <input type="checkbox"
                                           name="student_ids[]"
                                           value="{{ $student->id }}"
                                           class="student-checkbox me-3"
                                           onclick="event.stopPropagation()">

                                    @if($student->avatar)
                                        <img src="{{ asset('storage/' . $student->avatar) }}"
                                             alt="{{ $student->name }}"
                                             class="student-avatar-sm me-3">
                                    @else
                                        <div class="student-avatar-placeholder me-3">
                                            {{ substr($student->name, 0, 1) }}
                                        </div>
                                    @endif

                                    <div class="flex-grow-1 student-info">
                                        <h6 class="mb-0">{{ $student->name }}</h6>
                                        <small class="text-muted d-block">{{ $student->email }}</small>
                                        @if($student->student_id)
                                            <small class="text-muted">
                                                <i class="fas fa-id-card me-1"></i>{{ $student->student_id }}
                                            </small>
                                        @endif
                                    </div>

                                    @if($student->is_active)
                                        <span class="badge bg-success">نشط</span>
                                    @else
                                        <span class="badge bg-secondary">غير نشط</span>
                                    @endif
                                </div>

                                @if($student->department)
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <i class="fas fa-building me-1"></i>{{ $student->department->name }}
                                        </small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-users fa-5x text-muted mb-4 opacity-25"></i>
                            <h4 class="text-muted">لا يوجد طلاب متاحون</h4>
                            <p class="text-muted">جميع الطلاب مسجلون في هذا الكورس بالفعل</p>
                        </div>
                    @endforelse
                        </div>

                        <!-- No Results Message -->
                        <div class="col-12 d-none" id="noResults">
                            <div class="text-center py-5">
                                <i class="fas fa-search fa-3x text-muted mb-3 opacity-25"></i>
                                <h5 class="text-muted">لا توجد نتائج</h5>
                                <p class="text-muted">جرب تغيير معايير البحث</p>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

                <!-- Selection Summary (Sticky Bottom) -->
                <div class="selection-summary" id="selectionSummary" style="display: none;">
                    <div class="container-fluid">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">
                                    تم اختيار <span id="selectedCount">0</span> طالب
                                </h6>
                                <small class="text-muted" id="selectedNames"></small>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-light" onclick="clearSelection()">
                                    <i class="fas fa-times me-2"></i>إلغاء التحديد
                                </button>
                                <a href="{{ route('courses.enrollments.index', $course->id) }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-right me-2"></i>رجوع
                                </a>
                                <button type="button" class="btn btn-primary btn-lg" onclick="submitSelection()">
                                    <i class="fas fa-user-plus me-2"></i>تسجيل المحددين
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

        </div>
    </div>
@stop

@section('script')
<script>
    // Toggle Student Selection
    function toggleStudent(card) {
        const checkbox = card.querySelector('.student-checkbox');
        checkbox.checked = !checkbox.checked;
        updateCardState(card, checkbox.checked);
        updateSelectionSummary();
    }

    // Update Card State
    function updateCardState(card, selected) {
        if (selected) {
            card.classList.add('selected');
        } else {
            card.classList.remove('selected');
        }
    }

    // Checkbox Change Event
    document.querySelectorAll('.student-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateCardState(this.closest('.student-card'), this.checked);
            updateSelectionSummary();
        });
    });

    // Select All
    document.getElementById('selectAllCheckbox').addEventListener('change', function() {
        const visible = document.querySelectorAll('.student-item:not(.d-none) .student-checkbox');
        visible.forEach(checkbox => {
            checkbox.checked = this.checked;
            updateCardState(checkbox.closest('.student-card'), this.checked);
        });
        updateSelectionSummary();
    });

    // Update Selection Summary
    function updateSelectionSummary() {
        const selected = document.querySelectorAll('.student-checkbox:checked');
        const count = selected.length;
        const summary = document.getElementById('selectionSummary');
        const countEl = document.getElementById('selectedCount');
        const namesEl = document.getElementById('selectedNames');

        countEl.textContent = count;

        if (count > 0) {
            summary.style.display = 'block';

            // Show first 3 names
            const names = Array.from(selected).slice(0, 3).map(cb => {
                return cb.closest('.student-item').querySelector('h6').textContent;
            });

            if (count > 3) {
                namesEl.textContent = names.join(', ') + ' و ' + (count - 3) + ' آخرين...';
            } else {
                namesEl.textContent = names.join(', ');
            }
        } else {
            summary.style.display = 'none';
        }
    }

    // Clear Selection
    function clearSelection() {
        document.querySelectorAll('.student-checkbox').forEach(checkbox => {
            checkbox.checked = false;
            updateCardState(checkbox.closest('.student-card'), false);
        });
        document.getElementById('selectAllCheckbox').checked = false;
        updateSelectionSummary();
    }

    // Search Filter
    document.getElementById('searchInput').addEventListener('input', filterStudents);
    document.getElementById('departmentFilter').addEventListener('change', filterStudents);
    document.getElementById('statusFilter').addEventListener('change', filterStudents);

    function filterStudents() {
        const search = document.getElementById('searchInput').value.toLowerCase();
        const department = document.getElementById('departmentFilter').value;
        const status = document.getElementById('statusFilter').value;

        let visibleCount = 0;

        document.querySelectorAll('.student-item').forEach(item => {
            const name = item.dataset.name;
            const email = item.dataset.email;
            const dept = item.dataset.department;
            const stat = item.dataset.status;

            const matchSearch = !search || name.includes(search) || email.includes(search);
            const matchDept = !department || dept === department;
            const matchStatus = !status || stat === status;

            if (matchSearch && matchDept && matchStatus) {
                item.classList.remove('d-none');
                visibleCount++;
            } else {
                item.classList.add('d-none');
            }
        });

        // Show/hide no results message
        const noResults = document.getElementById('noResults');
        if (visibleCount === 0) {
            noResults.classList.remove('d-none');
        } else {
            noResults.classList.add('d-none');
        }

        // Uncheck "Select All" if filters change
        document.getElementById('selectAllCheckbox').checked = false;
    }

    // Submit selection (with validation)
    function submitSelection() {
        const form = document.getElementById('selectForm');
        const selected = document.querySelectorAll('.student-checkbox:checked');

        if (selected.length === 0) {
            alert('يرجى اختيار طالب واحد على الأقل');
            return;
        }

        form.submit();
    }
</script>
@stop
