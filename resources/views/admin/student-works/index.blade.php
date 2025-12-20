@extends('admin.layouts.master')

@section('page-title')
    إدارة أعمال الطلاب
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إدارة أعمال الطلاب</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">أعمال الطلاب</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('admin.student-works.create') }}" class="btn btn-primary">
                        <i class="ri-add-line me-2"></i>إضافة عمل جديد
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-lg bg-primary-transparent me-3">
                                    <i class="ri-folder-user-line fs-20 text-primary"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-0">إجمالي الأعمال</p>
                                    <h4 class="mb-0">{{ $stats['total'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-lg bg-warning-transparent me-3">
                                    <i class="ri-time-line fs-20 text-warning"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-0">قيد المراجعة</p>
                                    <h4 class="mb-0">{{ $stats['pending'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-lg bg-success-transparent me-3">
                                    <i class="ri-checkbox-circle-line fs-20 text-success"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-0">المعتمدة</p>
                                    <h4 class="mb-0">{{ $stats['approved'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-lg bg-danger-transparent me-3">
                                    <i class="ri-close-circle-line fs-20 text-danger"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-0">المرفوضة</p>
                                    <h4 class="mb-0">{{ $stats['rejected'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter & Search -->
            <div class="card custom-card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.student-works.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">البحث</label>
                                <input type="text" name="search" class="form-control"
                                       placeholder="ابحث في العنوان، الوصف..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">الطالب</label>
                                <select name="student_id" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">جميع الطلاب</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                            {{ $student->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">الحالة</label>
                                <select name="status" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">جميع الحالات</option>
                                    @foreach($statuses as $key => $status)
                                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                            {{ $status['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">التصنيف</label>
                                <select name="category" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">جميع التصنيفات</option>
                                    @foreach($categories as $key => $category)
                                        <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>
                                            {{ $category['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-fill">
                                        <i class="ri-search-line me-1"></i>بحث
                                    </button>
                                    @if(request()->hasAny(['search', 'student_id', 'status', 'category']))
                                        <a href="{{ route('admin.student-works.index') }}" class="btn btn-outline-secondary">
                                            <i class="ri-refresh-line"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Works Table -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        قائمة أعمال الطلاب ({{ $works->total() }})
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الصورة</th>
                                    <th>عنوان العمل</th>
                                    <th>الطالب</th>
                                    <th>التصنيف</th>
                                    <th>الدورة</th>
                                    <th>الحالة</th>
                                    <th>التقييم</th>
                                    <th>المشاهدات</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($works as $work)
                                    <tr>
                                        <td>{{ $loop->iteration + ($works->currentPage() - 1) * $works->perPage() }}</td>
                                        <td>
                                            @if($work->image)
                                                <img src="{{ $work->image_url }}" alt="{{ $work->title }}"
                                                     class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                            @else
                                                <div class="avatar avatar-md bg-light">
                                                    <i class="{{ $categories[$work->category]['icon'] }} text-muted"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.student-works.show', $work) }}" class="fw-semibold">
                                                {{ $work->title }}
                                            </a>
                                            @if($work->is_featured)
                                                <span class="badge bg-warning ms-1">
                                                    <i class="ri-star-fill"></i>
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <span class="avatar-text bg-primary-transparent">
                                                        {{ substr($work->student->name, 0, 2) }}
                                                    </span>
                                                </div>
                                                {{ $work->student->name }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $categories[$work->category]['color'] }}-transparent">
                                                <i class="{{ $categories[$work->category]['icon'] }} me-1"></i>
                                                {{ $categories[$work->category]['name'] }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($work->course)
                                                <small class="text-muted">{{ $work->course->title }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $statuses[$work->status]['color'] }}">
                                                <i class="{{ $statuses[$work->status]['icon'] }} me-1"></i>
                                                {{ $statuses[$work->status]['name'] }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($work->rating)
                                                <span class="badge bg-success-transparent">
                                                    <i class="ri-star-fill me-1"></i>{{ $work->rating }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                <i class="ri-eye-line me-1"></i>{{ $work->views_count }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('admin.student-works.show', $work) }}"
                                                   class="btn btn-sm btn-primary" title="عرض">
                                                    <i class="ri-eye-line"></i>
                                                </a>
                                                <a href="{{ route('admin.student-works.edit', $work) }}"
                                                   class="btn btn-sm btn-secondary" title="تعديل">
                                                    <i class="ri-edit-line"></i>
                                                </a>

                                                @if($work->status === 'pending')
                                                    <form action="{{ route('admin.student-works.approve', $work) }}"
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success"
                                                                title="اعتماد"
                                                                onclick="return confirm('هل أنت متأكد من اعتماد هذا العمل؟')">
                                                            <i class="ri-checkbox-circle-line"></i>
                                                        </button>
                                                    </form>

                                                    <button type="button" class="btn btn-sm btn-danger"
                                                            title="رفض"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#rejectModal{{ $work->id }}">
                                                        <i class="ri-close-circle-line"></i>
                                                    </button>
                                                @endif

                                                <form action="{{ route('admin.student-works.destroy', $work) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                            title="حذف"
                                                            onclick="return confirm('هل أنت متأكد من حذف هذا العمل؟')">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form>
                                            </div>

                                            <!-- Reject Modal -->
                                            @if($work->status === 'pending')
                                                <div class="modal fade" id="rejectModal{{ $work->id }}" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form action="{{ route('admin.student-works.reject', $work) }}" method="POST">
                                                                @csrf
                                                                <div class="modal-header">
                                                                    <h6 class="modal-title">رفض العمل</h6>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p class="text-muted">أدخل سبب الرفض (سيظهر للطالب)</p>
                                                                    <textarea name="admin_feedback" class="form-control" rows="4"
                                                                              placeholder="مثال: يجب تحسين الوصف وإضافة رابط GitHub..."
                                                                              required></textarea>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                                    <button type="submit" class="btn btn-danger">رفض العمل</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5">
                                            <i class="ri-folder-user-line fs-50 text-muted opacity-25"></i>
                                            <p class="text-muted mt-3">لا توجد أعمال متاحة</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($works->hasPages())
                    <div class="card-footer">
                        <div class="d-flex justify-content-center">
                            {{ $works->links() }}
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>
@stop

@section('script')
<script>
    // Auto-submit filters on change
    document.querySelectorAll('select[name="student_id"], select[name="status"], select[name="category"]').forEach(select => {
        select.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
</script>
@stop
