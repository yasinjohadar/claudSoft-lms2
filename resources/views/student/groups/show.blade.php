@extends('student.layouts.master')

@section('page-title')
    تفاصيل المجموعة - {{ $group->name }}
@stop

@section('css')
<style>
    .group-header-image {
        height: 300px;
        object-fit: cover;
        width: 100%;
        border-radius: 12px;
    }
    .group-placeholder-header {
        height: 300px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 5rem;
        border-radius: 12px;
    }
</style>
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">
                        <i class="bi bi-people me-2"></i>
                        تفاصيل المجموعة
                    </h5>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.groups.index') }}">المجموعات</a></li>
                            <li class="breadcrumb-item active">{{ $group->name }}</li>
                        </ol>
                    </nav>
                </div>
            </div>

            @if (\Session::has('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i>
                    {!! \Session::get('success') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (\Session::has('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {!! \Session::get('error') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <!-- Group Details -->
                <div class="col-lg-8">
                    <!-- Group Header Image -->
                    <div class="card shadow-sm border-0 mb-4">
                        @if($group->image)
                            <img src="{{ asset('storage/' . $group->image) }}"
                                 alt="{{ $group->name }}"
                                 class="group-header-image">
                        @else
                            <div class="group-placeholder-header">
                                <i class="bi bi-people"></i>
                            </div>
                        @endif
                    </div>

                    <!-- Group Information -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                معلومات المجموعة
                            </h5>
                        </div>
                        <div class="card-body">
                            <h3 class="mb-3">{{ $group->name }}</h3>
                            
                            @if($group->description)
                                <div class="mb-4">
                                    <h6 class="text-muted mb-2">الوصف:</h6>
                                    <p>{{ $group->description }}</p>
                                </div>
                            @endif

                            <!-- Associated Courses -->
                            @if($group->courses->count() > 0)
                                <div class="mb-4">
                                    <h6 class="text-muted mb-2">الكورسات المرتبطة:</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($group->courses as $course)
                                            <span class="badge bg-primary fs-6">{{ $course->title }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Stats Card -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <h6 class="text-muted mb-3">إحصائيات المجموعة</h6>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>عدد الأعضاء:</span>
                                    <strong>{{ $group->members_count ?? 0 }}
                                        @if($group->max_members)
                                            / {{ $group->max_members }}
                                        @endif
                                    </strong>
                                </div>
                            </div>
                            @if($group->max_members)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>المقاعد المتاحة:</span>
                                        <strong class="text-success">
                                            {{ $group->getAvailableSlots() ?? 'غير محدود' }}
                                        </strong>
                                    </div>
                                </div>
                            @endif
                            <div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>تاريخ الإنشاء:</span>
                                    <small class="text-muted">{{ $group->created_at->format('Y-m-d') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Membership Request Card -->
                    @if($canRequest)
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <i class="bi bi-person-plus me-2"></i>
                                    طلب الانضمام
                                </h6>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('student.groups.request', $group->id) }}" method="POST">
                                    @csrf
                                    
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input @error('terms_accepted') is-invalid @enderror" 
                                                   type="checkbox" 
                                                   name="terms_accepted" 
                                                   value="1" 
                                                   id="terms_accepted" 
                                                   required>
                                            <label class="form-check-label" for="terms_accepted">
                                                أوافق على شروط المعسكر
                                            </label>
                                            @error('terms_accepted')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">متى يمكنك تسديد رسوم المعسكر؟</label>
                                        <input type="date" 
                                               name="payment_date" 
                                               class="form-control @error('payment_date') is-invalid @enderror"
                                               min="{{ date('Y-m-d') }}">
                                        @error('payment_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle me-1"></i>
                                            تاريخ تقديري تقريبي (اختياري)
                                        </small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">رسالة للإدارة <span class="text-danger">*</span></label>
                                        <textarea name="message" 
                                                  class="form-control @error('message') is-invalid @enderror" 
                                                  rows="5"
                                                  placeholder="يرجى كتابة:
- وسيلة الدفع التي تفضل استخدامها
- أي ملاحظات أو معلومات إضافية للإدارة"
                                                  required></textarea>
                                        @error('message')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle me-1"></i>
                                            يرجى كتابة وسيلة الدفع المفضلة لديك وأي ملاحظات إضافية للإدارة. التاريخ المحدد أعلاه هو تقديري تقريبي.
                                        </small>
                                    </div>

                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="bi bi-send me-2"></i>
                                        إرسال طلب الانضمام
                                    </button>
                                </form>
                            </div>
                        </div>
                    @elseif($hasPendingRequest)
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0">
                                    <i class="bi bi-clock-history me-2"></i>
                                    طلب قيد المراجعة
                                </h6>
                            </div>
                            <div class="card-body text-center">
                                <i class="bi bi-hourglass-split display-4 text-warning mb-3"></i>
                                <p class="mb-0">لديك طلب انضمام قيد المراجعة لهذه المجموعة</p>
                                <a href="{{ route('student.groups.my-requests') }}" class="btn btn-outline-primary mt-3">
                                    <i class="bi bi-list-ul me-2"></i>
                                    عرض طلباتي
                                </a>
                            </div>
                        </div>
                    @elseif($group->hasMember(auth()->user()))
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="bi bi-check-circle me-2"></i>
                                    عضو في المجموعة
                                </h6>
                            </div>
                            <div class="card-body text-center">
                                <i class="bi bi-check-circle-fill display-4 text-success mb-3"></i>
                                <p class="mb-0">أنت عضو في هذه المجموعة</p>
                            </div>
                        </div>
                    @else
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0">
                                    <i class="bi bi-x-circle me-2"></i>
                                    غير متاح
                                </h6>
                            </div>
                            <div class="card-body text-center">
                                <p class="mb-0">طلب الانضمام غير متاح لهذه المجموعة حالياً</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
