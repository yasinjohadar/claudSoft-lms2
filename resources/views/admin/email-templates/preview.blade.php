<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>معاينة قالب البريد الإلكتروني</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f5f5f5;
            padding: 20px;
        }
        .email-preview {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .email-header {
            background: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .email-body {
            padding: 30px;
            direction: rtl;
            text-align: right;
        }
        .email-footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #dee2e6;
        }
        .test-data {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="email-preview">
            <!-- Header -->
            <div class="email-header">
                <h4 class="mb-0">
                    <i class="fas fa-envelope me-2"></i>
                    معاينة قالب البريد الإلكتروني
                </h4>
            </div>

            <!-- Test Data Info -->
            <div class="test-data m-3">
                <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>البيانات التجريبية المستخدمة:</h6>
                <div class="row">
                    <div class="col-md-6">
                        <small><strong>student_name:</strong> {{ $testData['student_name'] }}</small><br>
                        <small><strong>student_name_en:</strong> {{ $testData['student_name_en'] }}</small><br>
                        <small><strong>group_name:</strong> {{ $testData['group_name'] }}</small>
                    </div>
                    <div class="col-md-6">
                        <small><strong>email:</strong> {{ $testData['email'] }}</small><br>
                        <small><strong>phone:</strong> {{ $testData['phone'] }}</small>
                    </div>
                </div>
            </div>

            <!-- Email Subject -->
            <div class="p-3 border-bottom">
                <strong>الموضوع:</strong>
                <div class="alert alert-info mb-0 mt-2">{{ $renderedSubject }}</div>
            </div>

            <!-- Email Body -->
            <div class="email-body">
                {!! $renderedBody !!}
            </div>

            <!-- Footer -->
            <div class="email-footer">
                <p class="text-muted mb-2">
                    <small>هذه معاينة للقالب مع بيانات تجريبية</small>
                </p>
                <div class="mt-3">
                    <form action="{{ route('admin.email-templates.send-test', $emailTemplate) }}" method="POST" class="d-inline">
                        @csrf
                        <div class="input-group mb-2">
                            <input type="email" name="test_email" class="form-control" 
                                   placeholder="أدخل بريدك الإلكتروني لإرسال بريد تجريبي" 
                                   value="{{ auth()->user()->email ?? '' }}" required>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i>
                                إرسال تجريبي
                            </button>
                        </div>
                    </form>
                    <a href="{{ route('admin.email-templates.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-right me-1"></i>
                        رجوع للقائمة
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
