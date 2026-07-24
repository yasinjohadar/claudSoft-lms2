<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعذّر تصدير PDF</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: "Segoe UI", Tahoma, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            padding: 24px;
            box-sizing: border-box;
        }
        .box {
            max-width: 480px;
            width: 100%;
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 28px 24px;
            text-align: center;
        }
        h1 {
            margin: 0 0 12px;
            font-size: 1.25rem;
            color: #f8fafc;
        }
        p {
            margin: 0 0 10px;
            line-height: 1.7;
            color: #cbd5e1;
            font-size: 0.95rem;
        }
        .detail {
            margin-top: 16px;
            padding: 12px;
            border-radius: 10px;
            background: #0f172a;
            color: #94a3b8;
            font-size: 0.8rem;
            text-align: right;
            direction: ltr;
            word-break: break-word;
        }
        a {
            display: inline-block;
            margin-top: 18px;
            padding: 10px 18px;
            border-radius: 10px;
            background: #2563eb;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="box">
        <h1>تعذّر تصدير ملف PDF</h1>
        <p>{{ $message }}</p>
        <p>إن استمرّت المشكلة، تواصل مع الدعم الفني للأكاديمية.</p>
        @if(!empty($detail))
            <div class="detail">{{ $detail }}</div>
        @endif
        <a href="javascript:history.back()">العودة</a>
    </div>
</body>
</html>
