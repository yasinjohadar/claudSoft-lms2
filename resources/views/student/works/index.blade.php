@extends('student.layouts.master')

@section('page-title')
    جدول أعمالي
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">جدول أعمالي</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">جدول أعمالي</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content">
                    <div class="pe-1 mb-xl-0">
                        <a href="{{ route('student.works.create') }}" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i>إضافة عمل جديد
                        </a>
                    </div>
                    <div class="mb-xl-0">
                        <a href="{{ route('student.works.portfolio') }}" class="btn btn-secondary">
                            <i class="ri-gallery-line me-1"></i>عرض البورتفوليو
                        </a>
                    </div>
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
                                <div class="avatar avatar-lg bg-secondary-transparent me-3">
                                    <i class="ri-draft-line fs-20 text-secondary"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-0">المسودات</p>
                                    <h4 class="mb-0">{{ $stats['draft'] }}</h4>
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
            </div>

            <!-- Filter Bar -->
            <div class="card custom-card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('student.works.index') }}">
                        <div class="row g-3">
                            <div class="col-lg-4">
                                <label class="form-label">البحث</label>
                                <input type="text" name="search" class="form-control" placeholder="ابحث في العنوان، الوصف، التقنيات..." value="{{ request('search') }}">
                            </div>
                            <div class="col-lg-3">
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
                            <div class="col-lg-3">
                                <label class="form-label">التصنيف</label>
                                <select name="category" class="form-select">
                                    <option value="">جميع التصنيفات</option>
                                    @foreach($categories as $key => $category)
                                        <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>
                                            {{ $category['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ri-search-line me-1"></i>بحث
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Works Grid -->
            @if($works->count() > 0)
                <div class="row">
                    @foreach($works as $work)
                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                            <div class="card custom-card">
                                @if($work->image)
                                    <img src="{{ $work->image_url }}" class="card-img-top" alt="{{ $work->title }}" style="height: 200px; object-fit: cover;">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <i class="{{ $categories[$work->category]['icon'] }} fs-50 text-muted opacity-25"></i>
                                    </div>
                                @endif

                                <div class="card-body">
                                    <!-- Category & Status Badges -->
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="badge bg-{{ $categories[$work->category]['color'] }}-transparent">
                                            <i class="{{ $categories[$work->category]['icon'] }} me-1"></i>
                                            {{ $categories[$work->category]['name'] }}
                                        </span>
                                        <span class="badge bg-{{ $statuses[$work->status]['color'] }}">
                                            <i class="{{ $statuses[$work->status]['icon'] }} me-1"></i>
                                            {{ $statuses[$work->status]['name'] }}
                                        </span>
                                    </div>

                                    <!-- Title -->
                                    <h6 class="card-title mb-2">
                                        <a href="{{ route('student.works.show', $work) }}" class="text-dark">
                                            {{ $work->title }}
                                        </a>
                                    </h6>

                                    <!-- Course -->
                                    @if($work->course)
                                        <p class="text-muted mb-2">
                                            <i class="ri-book-line me-1"></i>{{ $work->course->title }}
                                        </p>
                                    @endif

                                    <!-- Description -->
                                    @if($work->description)
                                        <p class="text-muted mb-3" style="font-size: 13px;">
                                            {{ Str::limit($work->description, 100) }}
                                        </p>
                                    @endif

                                    <!-- Meta Info -->
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="text-muted" style="font-size: 12px;">
                                            @if($work->completion_date)
                                                <i class="ri-calendar-line me-1"></i>{{ $work->completion_date->format('Y/m/d') }}
                                            @endif
                                        </div>
                                        <div>
                                            <span class="text-muted me-2" style="font-size: 12px;">
                                                <i class="ri-eye-line"></i> {{ $work->views_count }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Rating (if approved) -->
                                    @if($work->isApproved() && $work->rating)
                                        <div class="alert alert-success-transparent mb-3 py-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span><i class="ri-star-fill me-1"></i>التقييم</span>
                                                <strong>{{ $work->rating }} / 10</strong>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Admin Feedback (if rejected) -->
                                    @if($work->status === 'rejected' && $work->admin_feedback)
                                        <div class="alert alert-danger-transparent mb-3 py-2">
                                            <small><i class="ri-feedback-line me-1"></i>{{ Str::limit($work->admin_feedback, 80) }}</small>
                                        </div>
                                    @endif

                                    <!-- Tags -->
                                    @if($work->tags && count($work->tags) > 0)
                                        <div class="mb-3">
                                            @foreach(array_slice($work->tags, 0, 3) as $tag)
                                                <span class="badge bg-light text-dark me-1" style="font-size: 11px;">
                                                    #{{ $tag }}
                                                </span>
                                            @endforeach
                                            @if(count($work->tags) > 3)
                                                <span class="text-muted" style="font-size: 11px;">+{{ count($work->tags) - 3 }}</span>
                                            @endif
                                        </div>
                                    @endif

                                    <!-- Action Buttons -->
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('student.works.show', $work) }}" class="btn btn-sm btn-outline-primary flex-fill">
                                            <i class="ri-eye-line me-1"></i>عرض
                                        </a>

                                        @can('update', $work)
                                            <a href="{{ route('student.works.edit', $work) }}" class="btn btn-sm btn-outline-secondary flex-fill">
                                                <i class="ri-edit-line me-1"></i>تعديل
                                            </a>
                                        @endcan

                                        @if($work->status === 'draft')
                                            <form action="{{ route('student.works.submit', $work) }}" method="POST" class="d-inline flex-fill">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success w-100" onclick="return confirm('هل أنت متأكد من تقديم هذا العمل للمراجعة؟')">
                                                    <i class="ri-send-plane-line me-1"></i>تقديم
                                                </button>
                                            </form>
                                        @endif

                                        @can('delete', $work)
                                            <form action="{{ route('student.works.destroy', $work) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنت متأكد من حذف هذا العمل؟')">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $works->links() }}
                </div>
            @else
                <div class="card custom-card">
                    <div class="card-body text-center py-5">
                        <i class="ri-folder-user-line fs-50 text-muted mb-3 opacity-25"></i>
                        <h5 class="mb-2">لا توجد أعمال متاحة</h5>
                        <p class="text-muted mb-4">ابدأ بإضافة أعمالك ومشاريعك لبناء بورتفوليو احترافي</p>
                        <a href="{{ route('student.works.create') }}" class="btn btn-primary">
                            <i class="ri-add-line me-2"></i>إضافة عمل جديد
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </div>
@stop

@section('script')
<script>
    // Auto-submit form on filter change (optional)
    document.querySelectorAll('select[name="status"], select[name="category"]').forEach(select => {
        select.addEventListener('change', function() {
            this.closest('form').submit();
        });
    });
</script>
@stop
