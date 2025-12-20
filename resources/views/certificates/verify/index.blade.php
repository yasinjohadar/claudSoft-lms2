<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التحقق من الشهادة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .verify-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .verify-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .certificate-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .verify-input {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px 20px;
            font-size: 16px;
            transition: all 0.3s;
        }
        .verify-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .verify-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 15px 40px;
            font-size: 18px;
            font-weight: 600;
            color: white;
            transition: transform 0.3s;
        }
        .verify-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .info-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="verify-container">
            <div class="verify-card">
                <div class="card-header-custom">
                    <div class="certificate-icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h2 class="mb-0">التحقق من الشهادة</h2>
                    <p class="mb-0 mt-2">تحقق من صحة الشهادة باستخدام رمز التحقق</p>
                </div>

                <div class="p-5">
                    <form action="{{ route('certificate.verify.verify') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-qrcode me-2"></i>رمز التحقق
                            </label>
                            <input type="text" name="code" class="form-control verify-input"
                                   placeholder="أدخل رمز التحقق هنا" required autofocus>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                يمكنك العثور على رمز التحقق في الشهادة أو مسح رمز QR
                            </small>
                        </div>

                        <button type="submit" class="btn verify-btn w-100">
                            <i class="fas fa-search me-2"></i>التحقق من الشهادة
                        </button>
                    </form>

                    <div class="info-box">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-lightbulb me-2 text-warning"></i>
                            كيفية التحقق:
                        </h6>
                        <ol class="mb-0 small">
                            <li class="mb-2">احصل على رمز التحقق من الشهادة</li>
                            <li class="mb-2">أدخل الرمز في الحقل أعلاه</li>
                            <li class="mb-2">اضغط على زر "التحقق من الشهادة"</li>
                            <li>ستظهر لك معلومات الشهادة إذا كانت صالحة</li>
                        </ol>
                    </div>

                    <div class="text-center mt-4">
                        <a href="{{ url('/') }}" class="text-decoration-none">
                            <i class="fas fa-home me-2"></i>العودة للصفحة الرئيسية
                        </a>
                    </div>
                </div>
            </div>

            <!-- Features -->
            <div class="row mt-4 text-white">
                <div class="col-md-4 text-center mb-3">
                    <i class="fas fa-shield-alt fs-40 mb-2"></i>
                    <p class="mb-0 small">تحقق آمن</p>
                </div>
                <div class="col-md-4 text-center mb-3">
                    <i class="fas fa-bolt fs-40 mb-2"></i>
                    <p class="mb-0 small">نتيجة فورية</p>
                </div>
                <div class="col-md-4 text-center mb-3">
                    <i class="fas fa-check-circle fs-40 mb-2"></i>
                    <p class="mb-0 small">موثوق 100%</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
