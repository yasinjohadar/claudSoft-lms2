@extends('student.layouts.master')

@section('page-title')
    تفاصيل الشهادة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">شهادة {{ $certificate->certificate_number }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('student.certificates.index') }}">شهاداتي</a></li>
                            <li class="breadcrumb-item active">التفاصيل</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('student.certificates.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-2"></i>العودة
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Certificate Preview -->
                <div class="col-xl-8">
                    <div class="card custom-card">
                        <div class="card-header bg-primary text-white">
                            <div class="card-title text-white">
                                <i class="fas fa-certificate me-2"></i>شهادة إتمام الكورس
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="certificate-preview bg-light p-5 rounded text-center">
                                <!-- Certificate Icon -->
                                <div class="mb-4">
                                    <i class="fas fa-award fs-80 text-primary"></i>
                                </div>

                                <!-- Certificate Header -->
                                <h2 class="fw-bold text-primary mb-4">شهادة إتمام</h2>
                                <h4 class="text-muted mb-4">Certificate of Completion</h4>

                                <!-- Student Name -->
                                <div class="mb-4">
                                    <p class="text-muted mb-2">تُمنح هذه الشهادة إلى</p>
                                    <h3 class="fw-bold text-dark border-bottom border-3 border-primary d-inline-block pb-2 px-4">
                                        {{ $certificate->student_name }}
                                    </h3>
                                </div>

                                <!-- Course Info -->
                                <div class="my-4">
                                    <p class="text-muted mb-2">لإتمامه بنجاح الكورس التدريبي</p>
                                    <h4 class="fw-semibold text-primary">{{ $certificate->course_name }}</h4>
                                </div>

                                <!-- Details -->
                                <div class="row justify-content-center mt-5">
                                    <div class="col-md-4">
                                        <div class="p-3">
                                            <p class="text-muted small mb-1">تاريخ الإصدار</p>
                                            <h6 class="fw-bold">{{ $certificate->issue_date->format('Y-m-d') }}</h6>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3">
                                            <p class="text-muted small mb-1">نسبة الإكمال</p>
                                            <h6 class="fw-bold text-success">{{ $certificate->completion_percentage ?? 0 }}%</h6>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3">
                                            <p class="text-muted small mb-1">رقم الشهادة</p>
                                            <h6 class="fw-bold text-primary">{{ $certificate->certificate_number }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">معلومات إضافية</div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-3 text-center">
                                        <i class="fas fa-user-check fs-30 text-info mb-2"></i>
                                        <h5 class="mb-1">{{ $certificate->attendance_percentage ?? '-' }}%</h5>
                                        <small class="text-muted">نسبة الحضور</small>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-3 text-center">
                                        <i class="fas fa-graduation-cap fs-30 text-warning mb-2"></i>
                                        <h5 class="mb-1">{{ $certificate->final_exam_score ?? '-' }}</h5>
                                        <small class="text-muted">درجة الاختبار النهائي</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-xl-4">
                    <!-- Status Card -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">حالة الشهادة</div>
                        </div>
                        <div class="card-body text-center">
                            @if($certificate->status == 'active')
                                <i class="fas fa-check-circle fs-60 text-success mb-3"></i>
                                <h5 class="text-success">شهادة صالحة</h5>
                                <p class="text-muted">يمكنك تحميل ومشاركة هذه الشهادة</p>
                            @elseif($certificate->status == 'revoked')
                                <i class="fas fa-ban fs-60 text-danger mb-3"></i>
                                <h5 class="text-danger">شهادة ملغاة</h5>
                                <p class="text-muted">{{ $certificate->revocation_reason }}</p>
                            @else
                                <i class="fas fa-clock fs-60 text-warning mb-3"></i>
                                <h5 class="text-warning">شهادة منتهية</h5>
                                <p class="text-muted">انتهت صلاحية هذه الشهادة</p>
                            @endif

                            @if($certificate->expiry_date)
                                <div class="mt-3 p-2 bg-light rounded">
                                    <small class="text-muted">
                                        تاريخ الانتهاء: {{ $certificate->expiry_date->format('Y-m-d') }}
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- QR Code -->
                    @if($certificate->qr_code_path)
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">رمز التحقق</div>
                            </div>
                            <div class="card-body text-center">
                                <img src="{{ asset('storage/' . $certificate->qr_code_path) }}"
                                     alt="QR Code" class="img-fluid mb-3" style="max-width: 180px;">
                                <p class="text-muted small mb-2">امسح الرمز للتحقق من الشهادة</p>
                                <a href="{{ $certificate->verification_url }}" target="_blank"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-external-link-alt me-1"></i>رابط التحقق
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- Download Button -->
                    @if($certificate->status == 'active' && $certificate->pdf_path)
                        <div class="card custom-card">
                            <div class="card-body">
                                <a href="{{ route('student.certificates.download', $certificate->id) }}"
                                   class="btn btn-success btn-lg w-100">
                                    <i class="fas fa-download me-2"></i>تحميل الشهادة (PDF)
                                </a>
                                <p class="text-center text-muted small mt-3 mb-0">
                                    <i class="fas fa-info-circle me-1"></i>
                                    تم التحميل {{ $certificate->download_count }} مرة
                                </p>
                            </div>
                        </div>
                    @endif

                    <!-- Share Card -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">مشاركة الشهادة</div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">شارك إنجازك مع الآخرين!</p>
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary" onclick="shareOnLinkedIn()">
                                    <i class="fab fa-linkedin me-2"></i>مشاركة على LinkedIn
                                </button>
                                <button class="btn btn-info" onclick="shareOnTwitter()">
                                    <i class="fab fa-twitter me-2"></i>مشاركة على Twitter
                                </button>
                                <button class="btn btn-success" onclick="copyLink()">
                                    <i class="fas fa-copy me-2"></i>نسخ رابط التحقق
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
<script>
function shareOnLinkedIn() {
    const url = '{{ $certificate->verification_url }}';
    const text = 'حصلت على شهادة في {{ $certificate->course_name }}';
    window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`, '_blank');
}

function shareOnTwitter() {
    const url = '{{ $certificate->verification_url }}';
    const text = 'حصلت على شهادة في {{ $certificate->course_name }} 🎓';
    window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`, '_blank');
}

function copyLink() {
    const url = '{{ $certificate->verification_url }}';
    navigator.clipboard.writeText(url).then(() => {
        alert('تم نسخ الرابط بنجاح!');
    });
}
</script>
@endsection
