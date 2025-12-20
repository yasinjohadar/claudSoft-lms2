@extends('admin.layouts.master')

@section('page-title')
    عرض تفاصيل المراجعة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">عرض تفاصيل المراجعة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.course-reviews.index') }}">مراجعات الكورسات</a></li>
                            <li class="breadcrumb-item active">عرض التفاصيل</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('admin.course-reviews.index') }}" class="btn btn-secondary">
                        <i class="ri-arrow-right-line me-2"></i>رجوع
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Review Details -->
                <div class="col-xl-8">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title">تفاصيل المراجعة</div>
                            <div>
                                @php
                                    $statuses = \App\Models\CourseReview::getStatuses();
                                @endphp
                                <span class="badge bg-{{ $statuses[$review->status]['color'] }}">
                                    <i class="{{ $statuses[$review->status]['icon'] }} me-1"></i>
                                    {{ $statuses[$review->status]['name'] }}
                                </span>
                                @if($review->is_featured)
                                    <span class="badge bg-warning ms-1">
                                        <i class="ri-star-fill me-1"></i>مراجعة مميزة
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Rating -->
                            <div class="mb-4">
                                <h6 class="mb-2">التقييم</h6>
                                <div class="fs-30">
                                    {{ $review->stars }}
                                    <span class="text-muted fs-16">({{ $review->rating }}/5)</span>
                                </div>
                            </div>

                            <!-- Title -->
                            @if($review->title)
                                <div class="mb-4">
                                    <h6 class="mb-2">عنوان المراجعة</h6>
                                    <h5>{{ $review->title }}</h5>
                                </div>
                            @endif

                            <!-- Review Content -->
                            <div class="mb-4">
                                <h6 class="mb-2">المراجعة</h6>
                                <p class="text-muted" style="white-space: pre-wrap;">{{ $review->review }}</p>
                            </div>

                            <!-- Helpful Count -->
                            <div class="mb-4">
                                <span class="badge bg-light text-dark">
                                    <i class="ri-thumb-up-line me-1"></i>
                                    {{ $review->helpful_count }} شخص وجدوا هذه المراجعة مفيدة
                                </span>
                            </div>

                            <!-- Admin Feedback -->
                            @if($review->admin_feedback)
                                <div class="alert alert-danger">
                                    <h6 class="alert-heading">ملاحظات الإدارة</h6>
                                    <p class="mb-0">{{ $review->admin_feedback }}</p>
                                </div>
                            @endif

                            <!-- Dates -->
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <small class="text-muted d-block">تاريخ الإنشاء</small>
                                    <span>{{ $review->created_at->format('Y-m-d H:i') }}</span>
                                </div>
                                @if($review->approved_at)
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">تاريخ الاعتماد</small>
                                        <span>{{ $review->approved_at->format('Y-m-d H:i') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Side Info -->
                <div class="col-xl-4">
                    <!-- Course Info -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">معلومات الكورس</div>
                        </div>
                        <div class="card-body">
                            <h6>{{ $review->course->title }}</h6>
                            <p class="text-muted mb-2">{{ Str::limit($review->course->description, 100) }}</p>
                            <a href="{{ route('admin.courses.show', $review->course) }}" class="btn btn-sm btn-outline-primary w-100">
                                <i class="ri-book-line me-1"></i>عرض الكورس
                            </a>
                        </div>
                    </div>

                    <!-- Student Info -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">معلومات الطالب</div>
                        </div>
                        <div class="card-body text-center">
                            <div class="avatar avatar-xl mx-auto mb-3">
                                <span class="avatar-text bg-primary-transparent fs-20">
                                    {{ substr($review->student->name, 0, 2) }}
                                </span>
                            </div>
                            <h6>{{ $review->student->name }}</h6>
                            <p class="text-muted mb-3">{{ $review->student->email }}</p>
                            <a href="{{ route('admin.users.show', $review->student) }}" class="btn btn-sm btn-outline-primary w-100">
                                <i class="ri-user-line me-1"></i>عرض الملف الشخصي
                            </a>
                        </div>
                    </div>

                    <!-- Approver Info -->
                    @if($review->approver)
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">معلومات المعتمد</div>
                            </div>
                            <div class="card-body">
                                <p class="mb-2">
                                    <strong>{{ $review->approver->name }}</strong>
                                </p>
                                <small class="text-muted">
                                    تم الاعتماد بتاريخ: {{ $review->approved_at->format('Y-m-d H:i') }}
                                </small>
                            </div>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">الإجراءات</div>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                @if($review->status === 'pending')
                                    <form action="{{ route('admin.course-reviews.approve', $review) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success w-100"
                                                onclick="return confirm('هل أنت متأكد من اعتماد هذه المراجعة؟')">
                                            <i class="ri-checkbox-circle-line me-2"></i>اعتماد المراجعة
                                        </button>
                                    </form>

                                    <button type="button" class="btn btn-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#rejectModal">
                                        <i class="ri-close-circle-line me-2"></i>رفض المراجعة
                                    </button>
                                @endif

                                <form action="{{ route('admin.course-reviews.toggle-featured', $review) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-{{ $review->is_featured ? 'warning' : 'outline-warning' }} w-100">
                                        <i class="ri-star-{{ $review->is_featured ? 'fill' : 'line' }} me-2"></i>
                                        {{ $review->is_featured ? 'إلغاء الإبراز' : 'إبراز المراجعة' }}
                                    </button>
                                </form>

                                <form action="{{ route('admin.course-reviews.destroy', $review) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger w-100"
                                            onclick="return confirm('هل أنت متأكد من حذف هذه المراجعة؟')">
                                        <i class="ri-delete-bin-line me-2"></i>حذف المراجعة
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Reject Modal -->
    @if($review->status === 'pending')
        <div class="modal fade" id="rejectModal" tabindex="-1">
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
@stop
