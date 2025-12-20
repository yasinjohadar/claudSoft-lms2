@extends('admin.layouts.master')

@section('page-title')
    مجموعات الأسئلة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">مجموعات الأسئلة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">مجموعات الأسئلة</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('question-pools.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>إضافة مجموعة جديدة
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
                                    <i class="fas fa-layer-group fs-24"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-muted fs-12">إجمالي المجموعات</p>
                                    <h4 class="mb-0 fw-bold">{{ $stats['total'] ?? 0 }}</h4>
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
                                    <i class="fas fa-check-circle fs-24"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-muted fs-12">المجموعات النشطة</p>
                                    <h4 class="mb-0 fw-bold">{{ $stats['active'] ?? 0 }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-lg bg-info-transparent me-3">
                                    <i class="fas fa-question-circle fs-24"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-muted fs-12">إجمالي الأسئلة</p>
                                    <h4 class="mb-0 fw-bold">{{ $stats['total_questions'] ?? 0 }}</h4>
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
                                    <i class="fas fa-book fs-24"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-muted fs-12">الكورسات</p>
                                    <h4 class="mb-0 fw-bold">{{ $stats['courses'] ?? 0 }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card custom-card mb-4">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-filter me-2"></i>الفلاتر
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('question-pools.index') }}" method="GET">
                        <div class="row g-3">
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
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">البحث</label>
                                <input type="text" name="search" class="form-control" placeholder="ابحث عن مجموعة..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label d-block">&nbsp;</label>
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search me-1"></i>بحث
                                </button>
                                <a href="{{ route('question-pools.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-redo me-1"></i>إعادة تعيين
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Question Pools Table -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-list me-2"></i>قائمة المجموعات
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($pools->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>اسم المجموعة</th>
                                        <th>الكورس</th>
                                        <th>الوصف</th>
                                        <th>عدد الأسئلة</th>
                                        <th>الحالة</th>
                                        <th>تاريخ الإنشاء</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pools as $pool)
                                        <tr>
                                            <td>{{ $pool->id }}</td>
                                            <td>
                                                <a href="{{ route('question-pools.show', $pool->id) }}" class="fw-semibold text-primary">
                                                    {{ $pool->name }}
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary-transparent">
                                                    {{ $pool->course->title }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted">
                                                    {{ Str::limit($pool->description, 50) ?? '-' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <i class="fas fa-question-circle me-1"></i>
                                                    {{ $pool->questions_count ?? 0 }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($pool->is_active)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle me-1"></i>نشط
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-times-circle me-1"></i>غير نشط
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $pool->created_at->format('Y-m-d') }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('question-pools.show', $pool->id) }}"
                                                       class="btn btn-sm btn-info"
                                                       data-bs-toggle="tooltip"
                                                       title="عرض">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('question-pools.edit', $pool->id) }}"
                                                       class="btn btn-sm btn-warning"
                                                       data-bs-toggle="tooltip"
                                                       title="تعديل">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('question-pools.destroy', $pool->id) }}"
                                                          method="POST"
                                                          class="d-inline"
                                                          onsubmit="return confirm('هل أنت متأكد من حذف هذه المجموعة؟')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="btn btn-sm btn-danger"
                                                                data-bs-toggle="tooltip"
                                                                title="حذف">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    عرض {{ $pools->firstItem() }} إلى {{ $pools->lastItem() }} من {{ $pools->total() }} مجموعة
                                </div>
                                <div>
                                    {{ $pools->links() }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card-body text-center py-5">
                            <div class="avatar avatar-xl bg-secondary-transparent mx-auto mb-3">
                                <i class="fas fa-layer-group fs-40"></i>
                            </div>
                            <h5 class="mb-2">لا توجد مجموعات أسئلة</h5>
                            <p class="text-muted mb-3">ابدأ بإنشاء أول مجموعة أسئلة</p>
                            <a href="{{ route('question-pools.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>إضافة مجموعة جديدة
                            </a>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@stop

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection
