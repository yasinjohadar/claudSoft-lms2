<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نتيجة التحقق من الشهادة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .result-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .result-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .success-header {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .error-header {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .status-icon {
            font-size: 100px;
            margin-bottom: 20px;
            animation: scaleIn 0.5s ease-out;
        }
        @keyframes scaleIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }
        .info-row {
            padding: 15px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .badge-custom {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="result-container">
            @if($verified)
                <!-- Success Result -->
                <div class="result-card">
                    <div class="success-header">
                        <div class="status-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h2 class="mb-0">شهادة صالحة ومعتمدة</h2>
                        <p class="mb-0 mt-2">تم التحقق من صحة هذه الشهادة بنجاح</p>
                    </div>

                    <div class="p-5">
                        <h4 class="fw-bold mb-4">معلومات الشهادة</h4>

                        <div class="info-row d-flex justify-content-between align-items-center">
                            <span class="text-muted">رقم الشهادة:</span>
                            <strong class="text-primary fs-18">{{ $certificate->certificate_number }}</strong>
                        </div>

                        <div class="info-row d-flex justify-content-between align-items-center">
                            <span class="text-muted">اسم الطالب:</span>
                            <strong>{{ $certificate->student_name }}</strong>
                        </div>

                        <div class="info-row d-flex justify-content-between align-items-center">
                            <span class="text-muted">اسم الكورس:</span>
                            <strong>{{ $certificate->course_name }}</strong>
                        </div>

                        <div class="info-row d-flex justify-content-between align-items-center">
                            <span class="text-muted">تاريخ الإصدار:</span>
                            <strong>{{ $certificate->issue_date->format('Y-m-d') }}</strong>
                        </div>

                        @if($certificate->completion_date)
                            <div class="info-row d-flex justify-content-between align-items-center">
                                <span class="text-muted">تاريخ الإكمال:</span>
                                <strong>{{ $certificate->completion_date->format('Y-m-d') }}</strong>
                            </div>
                        @endif

                        @if($certificate->expiry_date)
                            <div class="info-row d-flex justify-content-between align-items-center">
                                <span class="text-muted">تاريخ الانتهاء:</span>
                                <strong class="{{ $certificate->expiry_date->isPast() ? 'text-danger' : 'text-success' }}">
                                    {{ $certificate->expiry_date->format('Y-m-d') }}
                                    @if($certificate->expiry_date->isPast())
                                        <span class="badge bg-danger ms-2">منتهية</span>
                                    @endif
                                </strong>
                            </div>
                        @endif

                        <div class="info-row d-flex justify-content-between align-items-center">
                            <span class="text-muted">الحالة:</span>
                            <div>
                                @if($certificate->status == 'active')
                                    <span class="badge badge-custom bg-success">
                                        <i class="fas fa-check-circle me-1"></i>نشطة
                                    </span>
                                @elseif($certificate->status == 'revoked')
                                    <span class="badge badge-custom bg-danger">
                                        <i class="fas fa-ban me-1"></i>ملغاة
                                    </span>
                                @else
                                    <span class="badge badge-custom bg-warning">
                                        <i class="fas fa-clock me-1"></i>منتهية
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Performance Stats -->
                        <div class="mt-4 p-4 bg-light rounded">
                            <h6 class="fw-bold mb-3">أداء الطالب</h6>
                            <div class="row text-center">
                                @if($certificate->completion_percentage)
                                    <div class="col-md-4">
                                        <div class="mb-2">
                                            <i class="fas fa-check-circle fs-30 text-success"></i>
                                        </div>
                                        <h5 class="mb-0">{{ $certificate->completion_percentage }}%</h5>
                                        <small class="text-muted">نسبة الإكمال</small>
                                    </div>
                                @endif

                                @if($certificate->attendance_percentage)
                                    <div class="col-md-4">
                                        <div class="mb-2">
                                            <i class="fas fa-user-check fs-30 text-info"></i>
                                        </div>
                                        <h5 class="mb-0">{{ $certificate->attendance_percentage }}%</h5>
                                        <small class="text-muted">نسبة الحضور</small>
                                    </div>
                                @endif

                                @if($certificate->final_exam_score)
                                    <div class="col-md-4">
                                        <div class="mb-2">
                                            <i class="fas fa-graduation-cap fs-30 text-warning"></i>
                                        </div>
                                        <h5 class="mb-0">{{ $certificate->final_exam_score }}</h5>
                                        <small class="text-muted">درجة الاختبار</small>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Verification Info -->
                        <div class="mt-4 alert alert-success d-flex align-items-center">
                            <i class="fas fa-shield-alt fs-30 me-3"></i>
                            <div>
                                <strong>شهادة معتمدة وموثقة</strong>
                                <p class="mb-0 small">تم التحقق من هذه الشهادة بنجاح في {{ now()->format('Y-m-d H:i') }}</p>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <a href="{{ route('certificate.verify.index') }}" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-search me-2"></i>التحقق من شهادة أخرى
                            </a>
                            <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-lg ms-2">
                                <i class="fas fa-home me-2"></i>الصفحة الرئيسية
                            </a>
                        </div>
                    </div>
                </div>

            @else
                <!-- Error Result -->
                <div class="result-card">
                    <div class="error-header">
                        <div class="status-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <h2 class="mb-0">الشهادة غير صالحة</h2>
                        <p class="mb-0 mt-2">{{ $message ?? 'لم نتمكن من العثور على هذه الشهادة' }}</p>
                    </div>

                    <div class="p-5">
                        <div class="alert alert-danger d-flex align-items-start">
                            <i class="fas fa-exclamation-triangle fs-30 me-3 mt-1"></i>
                            <div>
                                <h6 class="fw-bold mb-2">أسباب محتملة:</h6>
                                <ul class="mb-0">
                                    <li>رمز التحقق غير صحيح</li>
                                    <li>الشهادة تم إلغاؤها</li>
                                    <li>الشهادة غير موجودة في النظام</li>
                                    <li>خطأ في إدخال الرمز</li>
                                </ul>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <a href="{{ route('certificate.verify.index') }}" class="btn btn-danger btn-lg">
                                <i class="fas fa-redo me-2"></i>حاول مرة أخرى
                            </a>
                            <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-lg ms-2">
                                <i class="fas fa-home me-2"></i>الصفحة الرئيسية
                            </a>
                        </div>

                        <div class="mt-4 p-4 bg-light rounded text-center">
                            <h6 class="fw-bold mb-2">تحتاج مساعدة؟</h6>
                            <p class="text-muted small mb-0">
                                تأكد من رمز التحقق أو تواصل مع الدعم الفني
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
