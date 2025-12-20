<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شهادة إتمام الكورس</title>
    <style>
        @page {
            margin: 0;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px;
            direction: rtl;
        }
        .certificate {
            background: white;
            padding: 60px;
            border: 15px solid #667eea;
            border-radius: 20px;
            box-shadow: 0 10px 50px rgba(0,0,0,0.3);
            text-align: center;
            min-height: 800px;
            position: relative;
        }
        .header {
            margin-bottom: 40px;
        }
        .logo {
            font-size: 48px;
            color: #667eea;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .title {
            font-size: 56px;
            color: #333;
            font-weight: bold;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        .subtitle {
            font-size: 24px;
            color: #666;
            margin-bottom: 50px;
        }
        .recipient {
            margin: 40px 0;
        }
        .recipient-label {
            font-size: 20px;
            color: #888;
            margin-bottom: 10px;
        }
        .recipient-name {
            font-size: 48px;
            color: #667eea;
            font-weight: bold;
            margin-bottom: 30px;
            border-bottom: 3px solid #667eea;
            display: inline-block;
            padding-bottom: 10px;
        }
        .course-info {
            margin: 40px 0;
            font-size: 22px;
            color: #555;
            line-height: 1.8;
        }
        .course-name {
            font-size: 32px;
            color: #764ba2;
            font-weight: bold;
            margin: 20px 0;
        }
        .details {
            display: flex;
            justify-content: space-around;
            margin: 50px 0;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .detail-item {
            text-align: center;
        }
        .detail-label {
            font-size: 16px;
            color: #888;
            margin-bottom: 10px;
        }
        .detail-value {
            font-size: 24px;
            color: #333;
            font-weight: bold;
        }
        .footer {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 2px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .qr-code {
            text-align: center;
        }
        .qr-code img {
            width: 120px;
            height: 120px;
        }
        .signature {
            text-align: center;
        }
        .signature-line {
            border-top: 2px solid #333;
            width: 200px;
            margin: 20px auto 10px;
        }
        .certificate-number {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 14px;
            color: #888;
            background: #f8f9fa;
            padding: 10px 20px;
            border-radius: 5px;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            color: rgba(102, 126, 234, 0.05);
            font-weight: bold;
            z-index: 0;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="watermark">شهادة معتمدة</div>

        <div class="certificate-number">
            رقم الشهادة: {certificate_number}
        </div>

        <div class="header">
            <div class="logo">🎓</div>
            <div class="title">شهادة إتمام</div>
            <div class="subtitle">Certificate of Completion</div>
        </div>

        <div class="recipient">
            <div class="recipient-label">تُمنح هذه الشهادة إلى</div>
            <div class="recipient-name">{student_name}</div>
        </div>

        <div class="course-info">
            <p>لإتمامه بنجاح الكورس التدريبي</p>
            <div class="course-name">{course_name}</div>
            <p>وتحقيقه لجميع متطلبات البرنامج التدريبي</p>
        </div>

        <div class="details">
            <div class="detail-item">
                <div class="detail-label">تاريخ الإصدار</div>
                <div class="detail-value">{issue_date_ar}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">نسبة الإكمال</div>
                <div class="detail-value">{completion_percentage}%</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">عدد الساعات</div>
                <div class="detail-value">{course_hours} ساعة</div>
            </div>
        </div>

        <div class="footer">
            <div class="qr-code">
                {qr_code}
                <div style="font-size: 12px; color: #888; margin-top: 10px;">
                    رمز التحقق
                </div>
            </div>

            <div class="signature">
                <div class="signature-line"></div>
                <div style="font-size: 16px; color: #555;">
                    مدير المنصة
                </div>
            </div>
        </div>
    </div>
</body>
</html>
