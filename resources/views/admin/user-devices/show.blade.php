@extends('admin.layouts.master')

@section('page-title')
    تفاصيل الجهاز
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تفاصيل الجهاز</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.user-devices.index') }}">أجهزة المستخدمين</a></li>
                        <li class="breadcrumb-item active">تفاصيل الجهاز</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('admin.user-devices.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right me-1"></i>العودة
                </a>
            </div>
        </div>

        <!-- Device Info Card -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>معلومات الجهاز
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>المستخدم:</strong>
                                <div class="d-flex align-items-center mt-2">
                                    @if($device->user)
                                        @if($device->user->avatar)
                                            <img src="{{ asset('storage/' . $device->user->avatar) }}" 
                                                 alt="{{ $device->user->name }}" 
                                                 class="avatar avatar-md rounded-circle me-2">
                                        @else
                                            <div class="avatar avatar-md rounded-circle bg-primary-transparent me-2">
                                                <span class="fw-bold">{{ substr($device->user->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                        <div>
                                            <strong>{{ $device->user->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $device->user->email }}</small>
                                            <br>
                                            <a href="{{ route('admin.user-devices.user', $device->user->id) }}" class="btn btn-sm btn-outline-primary mt-1">
                                                <i class="fas fa-list me-1"></i>عرض جميع أجهزة المستخدم
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <strong>بصمة الجهاز:</strong>
                                <p class="mb-0 mt-2">
                                    <code class="small">{{ $device->device_fingerprint }}</code>
                                </p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>اسم الجهاز:</strong>
                                <div class="d-flex align-items-center mt-2">
                                    @if($device->device_name)
                                        <span>{{ $device->device_name }}</span>
                                    @else
                                        <span class="text-muted">غير محدد</span>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-outline-secondary ms-2" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editDeviceNameModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <strong>نوع الجهاز:</strong>
                                <p class="mb-0 mt-2">
                                    @php
                                        $deviceTypeNames = [
                                            'mobile' => 'جوال',
                                            'tablet' => 'تابلت',
                                            'desktop' => 'سطح مكتب',
                                        ];
                                    @endphp
                                    <span class="badge bg-primary-transparent text-primary">
                                        {{ $deviceTypeNames[$device->device_type] ?? ucfirst($device->device_type) }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>المتصفح:</strong>
                                <p class="mb-0 mt-2">
                                    {{ $device->browser }}
                                    @if($device->browser_version)
                                        <span class="text-muted">({{ $device->browser_version }})</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <strong>المنصة:</strong>
                                <p class="mb-0 mt-2">
                                    {{ $device->platform }}
                                    @if($device->platform_version)
                                        <span class="text-muted">({{ $device->platform_version }})</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>عنوان IP الحالي:</strong>
                                <p class="mb-0 mt-2">
                                    <code>{{ $device->ip_address ?? '-' }}</code>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <strong>آخر عنوان IP:</strong>
                                <p class="mb-0 mt-2">
                                    <code>{{ $device->last_ip_address ?? '-' }}</code>
                                </p>
                            </div>
                        </div>

                        @if($device->user_agent)
                            <div class="row mb-3">
                                <div class="col-12">
                                    <strong>User Agent:</strong>
                                    <p class="mb-0 mt-2">
                                        <code class="small">{{ $device->user_agent }}</code>
                                    </p>
                                </div>
                            </div>
                        @endif

                        @if($device->meta)
                            <div class="row mb-3">
                                <div class="col-12">
                                    <strong>معلومات إضافية:</strong>
                                    <pre class="bg-light p-2 rounded mt-2 small">{{ json_encode($device->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Statistics Card -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-bar me-2"></i>إحصائيات الاستخدام
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>عدد مرات الدخول:</strong>
                                <p class="mb-0 mt-2">
                                    <span class="badge bg-info-transparent text-info fs-14">
                                        {{ number_format($device->total_logins) }} مرة
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>أول استخدام:</strong>
                                <p class="mb-0 mt-2">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $device->first_used_at->format('Y-m-d H:i:s') }}
                                    <br>
                                    <small class="text-muted">({{ $device->first_used_at->diffForHumans() }})</small>
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>آخر استخدام:</strong>
                                <p class="mb-0 mt-2">
                                    @if($device->last_used_at)
                                        <i class="fas fa-calendar me-1"></i>
                                        {{ $device->last_used_at->format('Y-m-d H:i:s') }}
                                        <br>
                                        <small class="text-muted">({{ $device->last_used_human }})</small>
                                    @else
                                        <span class="text-muted">لم يُستخدم</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>تاريخ التسجيل:</strong>
                                <p class="mb-0 mt-2">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $device->created_at->format('Y-m-d H:i:s') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status & Actions Card -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cog me-2"></i>الحالة والإجراءات
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <strong>الحالة الحالية:</strong>
                            <div class="mt-2">
                                <span class="{{ $device->status_badge['class'] }} fs-14">
                                    <i class="fas {{ $device->status_badge['icon'] }} me-1"></i>
                                    {{ $device->status_badge['text'] }}
                                </span>
                            </div>
                        </div>

                        <hr>

                        <div class="d-grid gap-2">
                            @if($device->is_blocked)
                                <form action="{{ route('admin.user-devices.unblock', $device->id) }}" 
                                      method="POST"
                                      onsubmit="return confirm('هل أنت متأكد من إلغاء حظر هذا الجهاز؟');">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-unlock me-1"></i>إلغاء الحظر
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.user-devices.block', $device->id) }}" 
                                      method="POST"
                                      onsubmit="return confirm('هل أنت متأكد من حظر هذا الجهاز؟ سيتم منع المستخدم من الوصول من هذا الجهاز.');">
                                    @csrf
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="fas fa-ban me-1"></i>حظر الجهاز
                                    </button>
                                </form>
                            @endif

                            @if($device->is_trusted)
                                <form action="{{ route('admin.user-devices.untrust', $device->id) }}" 
                                      method="POST"
                                      onsubmit="return confirm('هل أنت متأكد من إلغاء الثقة من هذا الجهاز؟');">
                                    @csrf
                                    <button type="submit" class="btn btn-warning w-100">
                                        <i class="fas fa-shield-slash me-1"></i>إلغاء الثقة
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.user-devices.trust', $device->id) }}" 
                                      method="POST"
                                      onsubmit="return confirm('هل أنت متأكد من تعيين هذا الجهاز كموثوق؟');">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-shield-check me-1"></i>تعيين كموثوق
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Device Name Modal -->
<div class="modal fade" id="editDeviceNameModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تعديل اسم الجهاز</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.user-devices.update-name', $device->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اسم الجهاز</label>
                        <input type="text" name="device_name" class="form-control" 
                               value="{{ $device->device_name }}" 
                               placeholder="مثال: جهاز العمل، جهاز المنزل...">
                        <small class="text-muted">يمكنك ترك هذا الحقل فارغاً</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
