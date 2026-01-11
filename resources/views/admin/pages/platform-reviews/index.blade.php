@extends('admin.layouts.master')

@section('page-title')
    تقييمات المنصة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تقييمات المنصة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">تقييمات المنصة</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="mb-1 text-muted">إجمالي التقييمات</p>
                                    <h3 class="mb-0 fw-semibold">{{ $stats['total'] }}</h3>
                                </div>
                                <div>
                                    <span class="avatar avatar-md bg-primary-transparent">
                                        <i class="fas fa-star fs-18"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="mb-1 text-muted">مقبولة</p>
                                    <h3 class="mb-0 fw-semibold">{{ $stats['active'] }}</h3>
                                </div>
                                <div>
                                    <span class="avatar avatar-md bg-success-transparent">
                                        <i class="fas fa-check-circle fs-18"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="mb-1 text-muted">في الانتظار</p>
                                    <h3 class="mb-0 fw-semibold">{{ $stats['inactive'] }}</h3>
                                </div>
                                <div>
                                    <span class="avatar avatar-md bg-warning-transparent">
                                        <i class="fas fa-clock fs-18"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="mb-1 text-muted">مميزة</p>
                                    <h3 class="mb-0 fw-semibold">{{ $stats['featured'] }}</h3>
                                </div>
                                <div>
                                    <span class="avatar avatar-md bg-info-transparent">
                                        <i class="fas fa-heart fs-18"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter & Search -->
            <div class="card custom-card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.platform-reviews.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">البحث</label>
                                <input type="text" name="search" class="form-control"
                                       placeholder="ابحث بالاسم، البريد الإلكتروني أو النص..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">الحالة</label>
                                <select name="status" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">الكل</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>مقبولة</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>في الانتظار</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">التقييم</label>
                                <select name="rating" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">الكل</option>
                                    @for($i = 5; $i >= 1; $i--)
                                        <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>
                                            {{ $i }} نجوم
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">التميز</label>
                                <select name="featured" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">الكل</option>
                                    <option value="yes" {{ request('featured') == 'yes' ? 'selected' : '' }}>مميزة</option>
                                    <option value="no" {{ request('featured') == 'no' ? 'selected' : '' }}>غير مميزة</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>بحث
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Reviews Table -->
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title mb-0">
                        قائمة التقييمات ({{ $reviews->total() }})
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($reviews->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="15%">اسم الطالب</th>
                                        <th width="12%">المنصب</th>
                                        <th width="10%">التقييم</th>
                                        <th width="25%">النص</th>
                                        <th width="8%">الحالة</th>
                                        <th width="8%">التميز</th>
                                        <th width="12%">التاريخ</th>
                                        <th width="15%">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reviews as $review)
                                        <tr>
                                            <td>{{ $loop->iteration + ($reviews->currentPage() - 1) * $reviews->perPage() }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($review->student_image)
                                                        <img src="{{ asset('storage/' . $review->student_image) }}" alt="{{ $review->student_name }}" class="avatar avatar-sm rounded-circle me-2">
                                                    @else
                                                        <div class="avatar avatar-sm rounded-circle bg-primary-transparent me-2">
                                                            <span class="fw-bold">{{ substr($review->student_name, 0, 1) }}</span>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <strong>{{ $review->student_name }}</strong>
                                                        @if($review->user)
                                                            <br>
                                                            <small class="text-muted">{{ $review->user->email }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($review->student_position)
                                                    <span class="badge bg-info-transparent">{{ $review->student_position }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        @if($i <= $review->rating)
                                                            <i class="fas fa-star text-warning"></i>
                                                        @else
                                                            <i class="far fa-star text-warning"></i>
                                                        @endif
                                                    @endfor
                                                    <span class="ms-1">({{ $review->rating }})</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 300px;" title="{{ $review->review_text }}">
                                                    {{ \Illuminate\Support\Str::limit($review->review_text, 100) }}
                                                </div>
                                            </td>
                                            <td>
                                                @if($review->is_active)
                                                    <span class="badge bg-success">مقبول</span>
                                                @else
                                                    <span class="badge bg-warning">في الانتظار</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($review->is_featured)
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-heart me-1"></i>مميز
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary-transparent">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $review->created_at->format('Y-m-d') }}
                                                    <br>
                                                    {{ $review->created_at->format('H:i') }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.platform-reviews.show', $review->id) }}" class="btn btn-sm btn-outline-primary" title="عرض">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if(!$review->is_active)
                                                        <form action="{{ route('admin.platform-reviews.approve', $review->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-success" title="موافقة" onclick="return confirm('هل أنت متأكد من الموافقة على هذا التقييم؟');">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form action="{{ route('admin.platform-reviews.reject', $review->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="رفض" onclick="return confirm('هل أنت متأكد من رفض هذا التقييم؟');">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    <form action="{{ route('admin.platform-reviews.toggle-featured', $review->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-info" title="{{ $review->is_featured ? 'إلغاء التميز' : 'تمييز' }}">
                                                            <i class="fas fa-heart"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.platform-reviews.destroy', $review->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا التقييم؟');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
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

                        <!-- Pagination -->
                        <div class="card-footer">
                            <div class="d-flex justify-content-center">
                                {{ $reviews->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-star fa-5x text-muted mb-4 opacity-25"></i>
                            <h4 class="text-muted mb-3">لا توجد تقييمات</h4>
                            <p class="text-muted">لا توجد تقييمات للمنصة في الوقت الحالي</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection

