@extends('admin.layouts.master')

@section('page-title')
    تفاصيل المعسكر: {{ $camp->name }}
@stop

@section('css')
<style>
    .info-card {
        border-left: 4px solid #0d6efd;
    }
    .stat-card {
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .camp-image-large {
        width: 100%;
        height: 300px;
        object-fit: cover;
        border-radius: 8px;
    }
</style>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-check-circle me-2"></i>نجح!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تفاصيل المعسكر</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('training-camps.index') }}">المعسكرات التدريبية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $camp->name }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('training-camps.edit', $camp->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>تعديل
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-fill">
                                    <p class="mb-1 text-muted">المشاركين الحاليين</p>
                                    <h3 class="mb-0">{{ $camp->current_participants }}</h3>
                                </div>
                                <div class="ms-3">
                                    <span class="avatar avatar-lg bg-primary-transparent">
                                        <i class="fas fa-users fs-4"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-fill">
                                    <p class="mb-1 text-muted">الحد الأقصى</p>
                                    <h3 class="mb-0">{{ $camp->max_participants ?? 'غير محدد' }}</h3>
                                </div>
                                <div class="ms-3">
                                    <span class="avatar avatar-lg bg-success-transparent">
                                        <i class="fas fa-user-check fs-4"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-fill">
                                    <p class="mb-1 text-muted">المدة</p>
                                    <h3 class="mb-0">{{ $camp->duration_days }} يوم</h3>
                                </div>
                                <div class="ms-3">
                                    <span class="avatar avatar-lg bg-info-transparent">
                                        <i class="fas fa-calendar fs-4"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-fill">
                                    <p class="mb-1 text-muted">السعر</p>
                                    <h3 class="mb-0">${{ number_format($camp->price, 2) }}</h3>
                                </div>
                                <div class="ms-3">
                                    <span class="avatar avatar-lg bg-warning-transparent">
                                        <i class="fas fa-dollar-sign fs-4"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- معلومات المعسكر -->
                <div class="col-xl-8">
                    <div class="card custom-card info-card">
                        <div class="card-header">
                            <div class="card-title">معلومات المعسكر</div>
                        </div>
                        <div class="card-body">
                            @if($camp->image)
                                <img src="{{ asset('storage/' . $camp->image) }}"
                                     alt="{{ $camp->name }}"
                                     class="camp-image-large mb-4">
                            @endif

                            <h4 class="mb-3">{{ $camp->name }}</h4>

                            @if($camp->description)
                                <div class="mb-4">
                                    <h6 class="text-muted mb-2">الوصف:</h6>
                                    <p class="text-muted">{{ $camp->description }}</p>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <h6 class="text-muted mb-1">التصنيف:</h6>
                                    @if($camp->category)
                                        <span class="badge" style="background-color: {{ $camp->category->color }}">
                                            {{ $camp->category->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>

                                <div class="col-md-6 mb-3">
                                    <h6 class="text-muted mb-1">المدرب:</h6>
                                    <p>{{ $camp->instructor_name ?? '-' }}</p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <h6 class="text-muted mb-1">الموقع:</h6>
                                    <p>{{ $camp->location ?? '-' }}</p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <h6 class="text-muted mb-1">تاريخ البداية:</h6>
                                    <p>{{ $camp->start_date->format('Y-m-d') }}</p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <h6 class="text-muted mb-1">تاريخ النهاية:</h6>
                                    <p>{{ $camp->end_date->format('Y-m-d') }}</p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <h6 class="text-muted mb-1">الحالة:</h6>
                                    @if($camp->isOngoing())
                                        <span class="badge bg-success">جاري الآن</span>
                                    @elseif($camp->hasEnded())
                                        <span class="badge bg-secondary">منتهي</span>
                                    @else
                                        <span class="badge bg-info">قادم</span>
                                    @endif

                                    @if($camp->is_active)
                                        <span class="badge bg-success ms-1">نشط</span>
                                    @else
                                        <span class="badge bg-danger ms-1">معطل</span>
                                    @endif

                                    @if($camp->is_featured)
                                        <span class="badge bg-warning ms-1">
                                            <i class="fas fa-star me-1"></i>مميز
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- قائمة المسجلين -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                المسجلين ({{ $camp->enrollments_count }})
                            </div>
                        </div>
                        <div class="card-body">
                            @if($camp->enrollments->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>الطالب</th>
                                                <th>البريد الإلكتروني</th>
                                                <th>تاريخ التسجيل</th>
                                                <th>الحالة</th>
                                                <th>حالة الدفع</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($camp->enrollments as $enrollment)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        <strong>{{ $enrollment->student->name }}</strong>
                                                    </td>
                                                    <td>{{ $enrollment->student->email }}</td>
                                                    <td>{{ $enrollment->enrollment_date->format('Y-m-d') }}</td>
                                                    <td>
                                                        @if($enrollment->status == 'approved')
                                                            <span class="badge bg-success">مقبول</span>
                                                        @elseif($enrollment->status == 'pending')
                                                            <span class="badge bg-warning">قيد المراجعة</span>
                                                        @elseif($enrollment->status == 'rejected')
                                                            <span class="badge bg-danger">مرفوض</span>
                                                        @else
                                                            <span class="badge bg-secondary">ملغي</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($enrollment->payment_status == 'paid')
                                                            <span class="badge bg-success">مدفوع</span>
                                                        @elseif($enrollment->payment_status == 'refunded')
                                                            <span class="badge bg-info">مسترجع</span>
                                                        @else
                                                            <span class="badge bg-danger">غير مدفوع</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-user-slash fa-3x mb-3"></i>
                                    <p>لا يوجد مسجلين حتى الآن</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- معلومات إضافية -->
                <div class="col-xl-4">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">معلومات إضافية</div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted">المعرف (Slug)</small>
                                <p class="mb-0"><code>{{ $camp->slug }}</code></p>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted">الترتيب</small>
                                <p class="mb-0">{{ $camp->order }}</p>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted">تاريخ الإنشاء</small>
                                <p class="mb-0">{{ $camp->created_at->format('Y-m-d H:i') }}</p>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted">آخر تحديث</small>
                                <p class="mb-0">{{ $camp->updated_at->format('Y-m-d H:i') }}</p>
                            </div>

                            @if($camp->max_participants)
                                <div class="mb-3">
                                    <small class="text-muted">المقاعد المتبقية</small>
                                    <p class="mb-0">
                                        <strong>{{ $camp->availableSeats() }}</strong> من {{ $camp->max_participants }}
                                    </p>
                                    @if($camp->isFull())
                                        <span class="badge bg-danger mt-1">المعسكر ممتلئ</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- إجراءات سريعة -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">إجراءات سريعة</div>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('training-camps.edit', $camp->id) }}" class="btn btn-primary">
                                    <i class="fas fa-edit me-2"></i>تعديل المعسكر
                                </a>

                                <a href="{{ route('training-camps.index') }}" class="btn btn-light">
                                    <i class="fas fa-arrow-left me-2"></i>العودة للقائمة
                                </a>

                                <form action="{{ route('training-camps.destroy', $camp->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا المعسكر؟\nسيتم حذف جميع التسجيلات المرتبطة به!');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="fas fa-trash me-2"></i>حذف المعسكر
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

@section('script')
<script>
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
</script>
@stop
