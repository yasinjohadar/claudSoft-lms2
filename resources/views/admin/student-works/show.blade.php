@extends('admin.layouts.master')

@section('page-title')
    {{ $studentWork->title }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">{{ $studentWork->title }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.student-works.index') }}">أعمال الطلاب</a></li>
                            <li class="breadcrumb-item active">{{ $studentWork->title }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content gap-2">
                    <a href="{{ route('admin.student-works.edit', $studentWork) }}" class="btn btn-secondary">
                        <i class="ri-edit-line me-1"></i>تعديل
                    </a>

                    @if($studentWork->status === 'pending')
                        <form action="{{ route('admin.student-works.approve', $studentWork) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success" onclick="return confirm('هل أنت متأكد من اعتماد هذا العمل؟')">
                                <i class="ri-checkbox-circle-line me-1"></i>اعتماد
                            </button>
                        </form>

                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="ri-close-circle-line me-1"></i>رفض
                        </button>
                    @endif

                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="ri-more-line me-1"></i>المزيد
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <form action="{{ route('admin.student-works.toggle-featured', $studentWork) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="ri-star-line me-2"></i>{{ $studentWork->is_featured ? 'إلغاء الإبراز' : 'إبراز العمل' }}
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form action="{{ route('admin.student-works.toggle-active', $studentWork) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="ri-eye-line me-2"></i>{{ $studentWork->is_active ? 'إخفاء' : 'إظهار' }}
                                    </button>
                                </form>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('admin.student-works.destroy', $studentWork) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('هل أنت متأكد من حذف هذا العمل؟')">
                                        <i class="ri-delete-bin-line me-2"></i>حذف العمل
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Main Content -->
                <div class="col-xl-8">
                    <!-- Main Image -->
                    @if($studentWork->image)
                        <div class="card custom-card mb-4">
                            <div class="card-body p-0">
                                <img src="{{ $studentWork->image_url }}" alt="{{ $studentWork->title }}" class="img-fluid rounded" style="width: 100%; max-height: 400px; object-fit: cover;">
                            </div>
                        </div>
                    @endif

                    <!-- Description -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="ri-file-text-line me-2"></i>الوصف
                            </div>
                        </div>
                        <div class="card-body">
                            @if($studentWork->description)
                                <p class="mb-0" style="white-space: pre-line;">{{ $studentWork->description }}</p>
                            @else
                                <p class="text-muted mb-0">لا يوجد وصف متاح</p>
                            @endif
                        </div>
                    </div>

                    <!-- Technologies -->
                    @if($studentWork->technologies)
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">
                                    <i class="ri-code-box-line me-2"></i>التقنيات المستخدمة
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach(explode(',', $studentWork->technologies) as $tech)
                                        <span class="badge bg-primary-transparent">
                                            {{ trim($tech) }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Links -->
                    @if($studentWork->github_url || $studentWork->demo_url || $studentWork->website_url || $studentWork->video_url)
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">
                                    <i class="ri-links-line me-2"></i>الروابط والمصادر
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    @if($studentWork->github_url)
                                        <a href="{{ $studentWork->github_url }}" target="_blank" class="btn btn-outline-dark">
                                            <i class="ri-github-fill me-2"></i>عرض على GitHub
                                        </a>
                                    @endif

                                    @if($studentWork->demo_url)
                                        <a href="{{ $studentWork->demo_url }}" target="_blank" class="btn btn-outline-primary">
                                            <i class="ri-earth-line me-2"></i>التجربة الحية (Demo)
                                        </a>
                                    @endif

                                    @if($studentWork->website_url)
                                        <a href="{{ $studentWork->website_url }}" target="_blank" class="btn btn-outline-info">
                                            <i class="ri-global-line me-2"></i>زيارة الموقع
                                        </a>
                                    @endif

                                    @if($studentWork->video_url)
                                        <a href="{{ $studentWork->video_url }}" target="_blank" class="btn btn-outline-danger">
                                            <i class="ri-video-line me-2"></i>مشاهدة الفيديو
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Admin Feedback -->
                    @if($studentWork->admin_feedback)
                        <div class="card custom-card {{ $studentWork->status === 'approved' ? 'border-success' : 'border-danger' }}">
                            <div class="card-header {{ $studentWork->status === 'approved' ? 'bg-success-transparent' : 'bg-danger-transparent' }}">
                                <div class="card-title">
                                    <i class="ri-feedback-line me-2"></i>ملاحظات المدرس
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="mb-0" style="white-space: pre-line;">{{ $studentWork->admin_feedback }}</p>
                                @if($studentWork->approver)
                                    <hr>
                                    <small class="text-muted">
                                        <i class="ri-user-line me-1"></i>{{ $studentWork->approver->name }}
                                        @if($studentWork->approved_at)
                                            - {{ $studentWork->approved_at->format('Y/m/d h:i A') }}
                                        @endif
                                    </small>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="col-xl-4">
                    <!-- Student Info -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="ri-user-line me-2"></i>معلومات الطالب
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar avatar-lg bg-primary-transparent me-3">
                                    <span class="avatar-text">{{ substr($studentWork->student->name, 0, 2) }}</span>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $studentWork->student->name }}</h6>
                                    <small class="text-muted">{{ $studentWork->student->email }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Work Info -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="ri-information-line me-2"></i>معلومات العمل
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Status -->
                            <div class="mb-3">
                                <label class="text-muted mb-2">الحالة</label>
                                <div>
                                    @php
                                        $statuses = \App\Models\StudentWork::getStatuses();
                                        $currentStatus = $statuses[$studentWork->status];
                                    @endphp
                                    <span class="badge bg-{{ $currentStatus['color'] }} fs-14">
                                        <i class="{{ $currentStatus['icon'] }} me-1"></i>
                                        {{ $currentStatus['name'] }}
                                    </span>
                                </div>
                            </div>

                            <!-- Category -->
                            <div class="mb-3">
                                <label class="text-muted mb-2">التصنيف</label>
                                <div>
                                    @php
                                        $categories = \App\Models\StudentWork::getCategories();
                                        $currentCategory = $categories[$studentWork->category];
                                    @endphp
                                    <span class="badge bg-{{ $currentCategory['color'] }}-transparent fs-14">
                                        <i class="{{ $currentCategory['icon'] }} me-1"></i>
                                        {{ $currentCategory['name'] }}
                                    </span>
                                </div>
                            </div>

                            <!-- Course -->
                            @if($studentWork->course)
                                <div class="mb-3">
                                    <label class="text-muted mb-2">الدورة التدريبية</label>
                                    <div>
                                        <i class="ri-book-line me-1"></i>{{ $studentWork->course->title }}
                                    </div>
                                </div>
                            @endif

                            <!-- Completion Date -->
                            @if($studentWork->completion_date)
                                <div class="mb-3">
                                    <label class="text-muted mb-2">تاريخ الإنجاز</label>
                                    <div>
                                        <i class="ri-calendar-line me-1"></i>{{ $studentWork->completion_date->format('Y/m/d') }}
                                    </div>
                                </div>
                            @endif

                            <!-- Rating -->
                            @if($studentWork->rating)
                                <div class="mb-3">
                                    <label class="text-muted mb-2">التقييم</label>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="progress" style="height: 25px;">
                                                <div class="progress-bar bg-{{ $studentWork->rating >= 7 ? 'success' : ($studentWork->rating >= 5 ? 'warning' : 'danger') }}"
                                                     role="progressbar"
                                                     style="width: {{ $studentWork->rating * 10 }}%">
                                                    {{ $studentWork->rating }} / 10
                                                </div>
                                            </div>
                                        </div>
                                        <span class="ms-2 fs-18 fw-bold text-{{ $studentWork->rating >= 7 ? 'success' : ($studentWork->rating >= 5 ? 'warning' : 'danger') }}">
                                            {{ $studentWork->rating }}
                                        </span>
                                    </div>
                                </div>
                            @endif

                            <!-- Flags -->
                            <div class="mb-3">
                                <label class="text-muted mb-2">الحالة</label>
                                <div class="d-flex gap-2">
                                    @if($studentWork->is_active)
                                        <span class="badge bg-success-transparent">
                                            <i class="ri-eye-line me-1"></i>نشط
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-transparent">
                                            <i class="ri-eye-off-line me-1"></i>مخفي
                                        </span>
                                    @endif

                                    @if($studentWork->is_featured)
                                        <span class="badge bg-warning-transparent">
                                            <i class="ri-star-fill me-1"></i>مميز
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Created/Updated -->
                            <div class="mb-0">
                                <label class="text-muted mb-2">التواريخ</label>
                                <small class="d-block text-muted">
                                    <i class="ri-time-line me-1"></i>تم الإنشاء: {{ $studentWork->created_at->format('Y/m/d') }}
                                </small>
                                <small class="d-block text-muted">
                                    <i class="ri-refresh-line me-1"></i>آخر تحديث: {{ $studentWork->updated_at->format('Y/m/d') }}
                                </small>
                                @if($studentWork->approved_at)
                                    <small class="d-block text-success">
                                        <i class="ri-checkbox-circle-line me-1"></i>تم الاعتماد: {{ $studentWork->approved_at->format('Y/m/d') }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Tags -->
                    @if($studentWork->tags && count($studentWork->tags) > 0)
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">
                                    <i class="ri-price-tag-3-line me-2"></i>الوسوم
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($studentWork->tags as $tag)
                                        <span class="badge bg-light text-dark">
                                            #{{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Statistics -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="ri-bar-chart-box-line me-2"></i>الإحصائيات
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <i class="ri-eye-line text-primary me-1"></i>
                                    <span class="text-muted">المشاهدات</span>
                                </div>
                                <span class="badge bg-primary-transparent">{{ $studentWork->views_count }}</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="ri-heart-line text-danger me-1"></i>
                                    <span class="text-muted">الإعجابات</span>
                                </div>
                                <span class="badge bg-danger-transparent">{{ $studentWork->likes_count }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    @if($studentWork->status === 'pending')
                        <div class="card custom-card bg-warning-transparent">
                            <div class="card-body text-center">
                                <i class="ri-time-line fs-40 text-warning mb-3"></i>
                                <h6 class="mb-2">قيد المراجعة</h6>
                                <p class="text-muted mb-3" style="font-size: 13px;">
                                    هذا العمل بحاجة إلى مراجعتك
                                </p>
                                <div class="d-grid gap-2">
                                    <form action="{{ route('admin.student-works.approve', $studentWork) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('هل أنت متأكد من اعتماد هذا العمل؟')">
                                            <i class="ri-checkbox-circle-line me-1"></i>اعتماد العمل
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                        <i class="ri-close-circle-line me-1"></i>رفض العمل
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Reject Modal -->
            <div class="modal fade" id="rejectModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('admin.student-works.reject', $studentWork) }}" method="POST">
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

        </div>
    </div>
@stop

@section('script')
<script>
    // Add any additional JavaScript if needed
</script>
@stop
