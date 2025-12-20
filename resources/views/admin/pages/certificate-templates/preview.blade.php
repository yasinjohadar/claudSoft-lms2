<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>معاينة القالب - {{ $template->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f5f6fa;
            padding: 20px;
        }
        .preview-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .preview-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .certificate-preview {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .preview-actions {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        @media print {
            .preview-header,
            .preview-actions {
                display: none;
            }
            .certificate-preview {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="preview-container">
        <!-- Header -->
        <div class="preview-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1"><i class="fas fa-eye me-2 text-primary"></i>معاينة القالب</h4>
                    <p class="text-muted mb-0">{{ $template->name }}</p>
                </div>
                <div>
                    <a href="{{ route('admin.certificate-templates.edit', $template->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i>تعديل
                    </a>
                    <a href="{{ route('admin.certificate-templates.show', $template->id) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-1"></i>رجوع
                    </a>
                </div>
            </div>
        </div>

        <!-- Certificate Preview -->
        <div class="certificate-preview">
            {!! replacePlaceholders($template->html_content ?? '', $sampleData) !!}
        </div>

        <!-- Actions -->
        <div class="preview-actions">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print me-2"></i>طباعة المعاينة
            </button>
            <a href="{{ route('admin.certificate-templates.edit', $template->id) }}" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>تعديل القالب
            </a>
        </div>

        <!-- Sample Data Info -->
        <div class="alert alert-info mt-3">
            <h6><i class="fas fa-info-circle me-2"></i>ملاحظة:</h6>
            <p class="mb-0 small">هذه معاينة بيانات تجريبية. عند إصدار الشهادات الفعلية، سيتم استبدال هذه البيانات ببيانات الطالب والكورس الحقيقية.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

@php
function replacePlaceholders($html, $data) {
    foreach ($data as $key => $value) {
        $html = str_replace('{' . $key . '}', $value, $html);
    }
    // إزالة QR Code في المعاينة
    $html = str_replace('{qr_code}', '<div class="text-center p-3 border"><i class="fas fa-qrcode fa-3x text-muted"></i><br><small class="text-muted">QR Code</small></div>', $html);
    return $html;
}
@endphp
