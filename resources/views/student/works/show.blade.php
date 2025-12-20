@extends('student.layouts.master')

@section('page-title')
    {{ $work->title }}
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
                    <h5 class="page-title fs-21 mb-1">{{ $work->title }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('student.works.index') }}">جدول أعمالي</a></li>
                            <li class="breadcrumb-item active">{{ $work->title }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content gap-2">
                    @can('update', $work)
                        <a href="{{ route('student.works.edit', $work) }}" class="btn btn-secondary">
                            <i class="ri-edit-line me-1"></i>تعديل
                        </a>
                    @endcan

                    @if($work->status === 'draft')
                        <form action="{{ route('student.works.submit', $work) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success" onclick="return confirm('هل أنت متأكد من تقديم هذا العمل للمراجعة؟')">
                                <i class="ri-send-plane-line me-1"></i>تقديم للمراجعة
                            </button>
                        </form>
                    @endif

                    @can('delete', $work)
                        <form action="{{ route('student.works.destroy', $work) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('هل أنت متأكد من حذف هذا العمل؟')">
                                <i class="ri-delete-bin-line me-1"></i>حذف
                            </button>
                        </form>
                    @endcan
                </div>
            </div>

            <div class="row">
                <!-- Main Content -->
                <div class="col-xl-8">
                    <!-- Main Image -->
                    @if($work->image)
                        <div class="card custom-card mb-4">
                            <div class="card-body p-0">
                                <img src="{{ $work->image_url }}" alt="{{ $work->title }}" class="img-fluid rounded" style="width: 100%; max-height: 400px; object-fit: cover;">
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
                            @if($work->description)
                                <p class="mb-0" style="white-space: pre-line;">{{ $work->description }}</p>
                            @else
                                <p class="text-muted mb-0">لا يوجد وصف متاح</p>
                            @endif
                        </div>
                    </div>

                    <!-- Technologies -->
                    @if($work->technologies)
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">
                                    <i class="ri-code-box-line me-2"></i>التقنيات المستخدمة
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach(explode(',', $work->technologies) as $tech)
                                        <span class="badge bg-primary-transparent">
                                            {{ trim($tech) }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Links -->
                    @if($work->github_url || $work->demo_url || $work->website_url || $work->video_url)
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">
                                    <i class="ri-links-line me-2"></i>الروابط والمصادر
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    @if($work->github_url)
                                        <a href="{{ $work->github_url }}" target="_blank" class="btn btn-outline-dark">
                                            <i class="ri-github-fill me-2"></i>عرض على GitHub
                                        </a>
                                    @endif

                                    @if($work->demo_url)
                                        <a href="{{ $work->demo_url }}" target="_blank" class="btn btn-outline-primary">
                                            <i class="ri-earth-line me-2"></i>التجربة الحية (Demo)
                                        </a>
                                    @endif

                                    @if($work->website_url)
                                        <a href="{{ $work->website_url }}" target="_blank" class="btn btn-outline-info">
                                            <i class="ri-global-line me-2"></i>زيارة الموقع
                                        </a>
                                    @endif

                                    @if($work->video_url)
                                        <a href="{{ $work->video_url }}" target="_blank" class="btn btn-outline-danger">
                                            <i class="ri-video-line me-2"></i>مشاهدة الفيديو
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Admin Feedback -->
                    @if($work->admin_feedback)
                        <div class="card custom-card {{ $work->status === 'approved' ? 'border-success' : 'border-danger' }}">
                            <div class="card-header {{ $work->status === 'approved' ? 'bg-success-transparent' : 'bg-danger-transparent' }}">
                                <div class="card-title">
                                    <i class="ri-feedback-line me-2"></i>ملاحظات المدرس
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="mb-0" style="white-space: pre-line;">{{ $work->admin_feedback }}</p>
                                @if($work->approver)
                                    <hr>
                                    <small class="text-muted">
                                        <i class="ri-user-line me-1"></i>{{ $work->approver->name }}
                                        @if($work->approved_at)
                                            - {{ $work->approved_at->format('Y/m/d h:i A') }}
                                        @endif
                                    </small>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="col-xl-4">
                    <!-- Status & Info -->
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
                                        $currentStatus = $statuses[$work->status];
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
                                        $currentCategory = $categories[$work->category];
                                    @endphp
                                    <span class="badge bg-{{ $currentCategory['color'] }}-transparent fs-14">
                                        <i class="{{ $currentCategory['icon'] }} me-1"></i>
                                        {{ $currentCategory['name'] }}
                                    </span>
                                </div>
                            </div>

                            <!-- Course -->
                            @if($work->course)
                                <div class="mb-3">
                                    <label class="text-muted mb-2">الدورة التدريبية</label>
                                    <div>
                                        <i class="ri-book-line me-1"></i>{{ $work->course->title }}
                                    </div>
                                </div>
                            @endif

                            <!-- Completion Date -->
                            @if($work->completion_date)
                                <div class="mb-3">
                                    <label class="text-muted mb-2">تاريخ الإنجاز</label>
                                    <div>
                                        <i class="ri-calendar-line me-1"></i>{{ $work->completion_date->format('Y/m/d') }}
                                    </div>
                                </div>
                            @endif

                            <!-- Rating -->
                            @if($work->rating)
                                <div class="mb-3">
                                    <label class="text-muted mb-2">التقييم</label>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="progress" style="height: 25px;">
                                                <div class="progress-bar bg-{{ $work->rating >= 7 ? 'success' : ($work->rating >= 5 ? 'warning' : 'danger') }}"
                                                     role="progressbar"
                                                     style="width: {{ $work->rating * 10 }}%">
                                                    {{ $work->rating }} / 10
                                                </div>
                                            </div>
                                        </div>
                                        <span class="ms-2 fs-18 fw-bold text-{{ $work->rating >= 7 ? 'success' : ($work->rating >= 5 ? 'warning' : 'danger') }}">
                                            {{ $work->rating }}
                                        </span>
                                    </div>
                                </div>
                            @endif

                            <!-- Created/Updated -->
                            <div class="mb-0">
                                <label class="text-muted mb-2">التواريخ</label>
                                <small class="d-block text-muted">
                                    <i class="ri-time-line me-1"></i>تم الإنشاء: {{ $work->created_at->format('Y/m/d') }}
                                </small>
                                <small class="d-block text-muted">
                                    <i class="ri-refresh-line me-1"></i>آخر تحديث: {{ $work->updated_at->format('Y/m/d') }}
                                </small>
                                @if($work->approved_at)
                                    <small class="d-block text-success">
                                        <i class="ri-checkbox-circle-line me-1"></i>تم الاعتماد: {{ $work->approved_at->format('Y/m/d') }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Tags -->
                    @if($work->tags && count($work->tags) > 0)
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">
                                    <i class="ri-price-tag-3-line me-2"></i>الوسوم
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($work->tags as $tag)
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
                                <span class="badge bg-primary-transparent">{{ $work->views_count }}</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="ri-heart-line text-danger me-1"></i>
                                    <span class="text-muted">الإعجابات</span>
                                </div>
                                <span class="badge bg-danger-transparent">{{ $work->likes_count }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Status Actions -->
                    @if($work->status === 'pending')
                        <div class="alert alert-warning">
                            <i class="ri-time-line me-2"></i>
                            <strong>قيد المراجعة</strong>
                            <p class="mb-0 mt-2" style="font-size: 13px;">
                                عملك الآن قيد المراجعة من قبل المدرس. سيتم إخطارك بالنتيجة قريباً.
                            </p>
                        </div>
                    @endif

                    @if($work->status === 'rejected')
                        <div class="alert alert-danger">
                            <i class="ri-close-circle-line me-2"></i>
                            <strong>تم الرفض</strong>
                            <p class="mb-0 mt-2" style="font-size: 13px;">
                                يمكنك مراجعة ملاحظات المدرس وإعادة التقديم بعد إجراء التعديلات المطلوبة.
                            </p>
                        </div>
                    @endif

                    @if($work->status === 'approved')
                        <div class="alert alert-success">
                            <i class="ri-checkbox-circle-line me-2"></i>
                            <strong>تم الاعتماد</strong>
                            <p class="mb-0 mt-2" style="font-size: 13px;">
                                تهانينا! عملك معتمد ويظهر الآن في البورتفوليو العام.
                            </p>
                            <a href="{{ route('student.works.portfolio') }}" class="btn btn-sm btn-success mt-2 w-100">
                                <i class="ri-gallery-line me-1"></i>عرض البورتفوليو
                            </a>
                        </div>
                    @endif
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
