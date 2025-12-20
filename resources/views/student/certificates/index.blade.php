@extends('student.layouts.master')

@section('page-title')
    شهاداتي
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">شهاداتي</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">الشهادات</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Certificates Grid -->
            <div class="row">
                @forelse($certificates as $certificate)
                    <div class="col-xl-4 col-lg-6 col-md-6">
                        <div class="card custom-card certificate-card">
                            <div class="card-body">
                                <!-- Certificate Icon/Badge -->
                                <div class="text-center mb-3">
                                    <div class="avatar avatar-xxl bg-primary-transparent rounded-circle mx-auto mb-3">
                                        <i class="fas fa-certificate fs-40 text-primary"></i>
                                    </div>
                                    <h5 class="fw-semibold">{{ $certificate->course_name }}</h5>
                                    @if($certificate->status == 'active')
                                        <span class="badge bg-success-transparent">
                                            <i class="fas fa-check-circle me-1"></i>صالحة
                                        </span>
                                    @elseif($certificate->status == 'revoked')
                                        <span class="badge bg-danger-transparent">
                                            <i class="fas fa-ban me-1"></i>ملغاة
                                        </span>
                                    @else
                                        <span class="badge bg-warning-transparent">
                                            <i class="fas fa-clock me-1"></i>منتهية
                                        </span>
                                    @endif
                                </div>

                                <!-- Certificate Details -->
                                <div class="certificate-details">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">رقم الشهادة:</span>
                                        <strong class="text-primary">{{ $certificate->certificate_number }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">تاريخ الإصدار:</span>
                                        <strong>{{ $certificate->issue_date->format('Y-m-d') }}</strong>
                                    </div>
                                    @if($certificate->expiry_date)
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">تاريخ الانتهاء:</span>
                                            <strong class="{{ $certificate->expiry_date->isPast() ? 'text-danger' : '' }}">
                                                {{ $certificate->expiry_date->format('Y-m-d') }}
                                            </strong>
                                        </div>
                                    @endif
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">نسبة الإكمال:</span>
                                        <strong class="text-success">{{ $certificate->completion_percentage ?? 0 }}%</strong>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="mt-4 d-grid gap-2">
                                    <a href="{{ route('student.certificates.show', $certificate->id) }}"
                                       class="btn btn-primary">
                                        <i class="fas fa-eye me-2"></i>عرض التفاصيل
                                    </a>
                                    @if($certificate->status == 'active' && $certificate->pdf_path)
                                        <a href="{{ route('student.certificates.download', $certificate->id) }}"
                                           class="btn btn-success">
                                            <i class="fas fa-download me-2"></i>تحميل الشهادة
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card custom-card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-certificate fs-80 text-muted mb-4"></i>
                                <h4 class="text-muted">لا توجد شهادات حالياً</h4>
                                <p class="text-muted">أكمل كورساتك للحصول على شهادات معتمدة</p>
                                <a href="{{ route('student.courses.index') }}" class="btn btn-primary mt-3">
                                    <i class="fas fa-book me-2"></i>تصفح الكورسات
                                </a>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($certificates->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $certificates->links() }}
                </div>
            @endif

        </div>
    </div>
@endsection

@section('styles')
<style>
.certificate-card {
    transition: transform 0.3s, box-shadow 0.3s;
}
.certificate-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}
</style>
@endsection
