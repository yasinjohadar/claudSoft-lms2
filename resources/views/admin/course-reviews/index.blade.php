@extends('admin.layouts.master')

@section('page-title')
    إدارة مراجعات الكورسات
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إدارة مراجعات الكورسات</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">مراجعات الكورسات</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-lg bg-primary-transparent me-3">
                                    <i class="ri-message-3-line fs-20 text-primary"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-0">إجمالي المراجعات</p>
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
                    <form method="GET" action="{{ route('admin.course-reviews.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">البحث</label>
                                <input type="text" name="search" class="form-control"
                                       placeholder="ابحث في العنوان، المراجعة، الطالب..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
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
                            <div class="col-md-2">
                                <label class="form-label">الحالة</label>
                                <select name="status" class="form-select">
                                    <option value="">جميع الحالات</option>
                                    @foreach($statuses as $key => $status)
                                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                            {{ $status['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">التقييم</label>
                                <select name="rating" class="form-select">
                                    <option value="">جميع التقييمات</option>
                                    <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>⭐⭐⭐⭐⭐</option>
                                    <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>⭐⭐⭐⭐</option>
                                    <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>⭐⭐⭐</option>
                                    <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>⭐⭐</option>
                                    <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>⭐</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-fill">
                                        <i class="ri-search-line me-1"></i>بحث
                                    </button>
                                    @if(request()->hasAny(['search', 'course_id', 'status', 'rating']))
                                        <a href="{{ route('admin.course-reviews.index') }}" class="btn btn-outline-secondary">
                                            <i class="ri-refresh-line"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Reviews Table -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        قائمة المراجعات ({{ $reviews->total() }})
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الكورس</th>
                                    <th>الطالب</th>
                                    <th>التقييم</th>
                                    <th>العنوان</th>
                                    <th>المراجعة</th>
                                    <th>الحالة</th>
                                    <th>مفيدة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reviews as $review)
                                    <tr>
                                        <td>{{ $loop->iteration + ($reviews->currentPage() - 1) * $reviews->perPage() }}</td>
                                        <td>
                                            <div>
                                                <strong>{{ Str::limit($review->course->title, 30) }}</strong>
                                                @if($review->is_featured)
                                                    <span class="badge bg-warning ms-1">
                                                        <i class="ri-star-fill"></i>
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <span class="avatar-text bg-primary-transparent">
                                                        {{ substr($review->student->name, 0, 2) }}
                                                    </span>
                                                </div>
                                                {{ $review->student->name }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning-transparent">
                                                {{ $review->stars }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($review->title)
                                                {{ Str::limit($review->title, 30) }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ Str::limit($review->review, 50) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $statuses[$review->status]['color'] }}">
                                                <i class="{{ $statuses[$review->status]['icon'] }} me-1"></i>
                                                {{ $statuses[$review->status]['name'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                <i class="ri-thumb-up-line me-1"></i>{{ $review->helpful_count }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('admin.course-reviews.show', $review) }}"
                                                   class="btn btn-sm btn-primary" title="عرض">
                                                    <i class="ri-eye-line"></i>
                                                </a>

                                                @if($review->status === 'pending')
                                                    <form action="{{ route('admin.course-reviews.approve', $review) }}"
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success"
                                                                title="اعتماد"
                                                                onclick="return confirm('هل أنت متأكد من اعتماد هذه المراجعة؟')">
                                                            <i class="ri-checkbox-circle-line"></i>
                                                        </button>
                                                    </form>

                                                    <button type="button" class="btn btn-sm btn-danger"
                                                            title="رفض"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#rejectModal{{ $review->id }}">
                                                        <i class="ri-close-circle-line"></i>
                                                    </button>
                                                @endif

                                                <form action="{{ route('admin.course-reviews.toggle-featured', $review) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-{{ $review->is_featured ? 'warning' : 'outline-warning' }}"
                                                            title="{{ $review->is_featured ? 'إلغاء الإبراز' : 'إبراز' }}">
                                                        <i class="ri-star-{{ $review->is_featured ? 'fill' : 'line' }}"></i>
                                                    </button>
                                                </form>

                                                <form action="{{ route('admin.course-reviews.destroy', $review) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                            title="حذف"
                                                            onclick="return confirm('هل أنت متأكد من حذف هذه المراجعة؟')">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form>
                                            </div>

                                            <!-- Reject Modal -->
                                            @if($review->status === 'pending')
                                                <div class="modal fade" id="rejectModal{{ $review->id }}" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form action="{{ route('admin.course-reviews.reject', $review) }}" method="POST">
                                                                @csrf
                                                                <div class="modal-header">
                                                                    <h6 class="modal-title">رفض المراجعة</h6>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p class="text-muted">أدخل سبب الرفض (سيظهر للطالب)</p>
                                                                    <textarea name="admin_feedback" class="form-control" rows="4"
                                                                              placeholder="مثال: المراجعة تحتوي على محتوى غير لائق..."
                                                                              required></textarea>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                                    <button type="submit" class="btn btn-danger">رفض المراجعة</button>
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
                                        <td colspan="9" class="text-center py-5">
                                            <i class="ri-message-3-line fs-50 text-muted opacity-25"></i>
                                            <p class="text-muted mt-3">لا توجد مراجعات متاحة</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($reviews->hasPages())
                    <div class="card-footer">
                        <div class="d-flex justify-content-center">
                            {{ $reviews->links() }}
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
    document.querySelectorAll('select[name="course_id"], select[name="status"], select[name="rating"]').forEach(select => {
        select.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
</script>
@stop
