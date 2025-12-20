<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'نظام إدارة التعلم')</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            direction: rtl;
            text-align: right;
        }

        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }

        .email-header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .email-header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .email-body {
            padding: 30px 20px;
        }

        .greeting {
            font-size: 18px;
            color: #333;
            margin-bottom: 15px;
        }

        .content {
            color: #555;
            line-height: 1.6;
            font-size: 15px;
        }

        .highlight-box {
            background-color: #f8f9fa;
            border-right: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .highlight-box h3 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 18px;
        }

        .icon {
            font-size: 48px;
            text-align: center;
            margin: 20px 0;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #667eea;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }

        .btn:hover {
            background-color: #5568d3;
        }

        .email-footer {
            background-color: #f8f9fa;
            color: #666;
            padding: 20px;
            text-align: center;
            font-size: 13px;
            border-top: 1px solid #e0e0e0;
        }

        .email-footer a {
            color: #667eea;
            text-decoration: none;
        }

        .stats {
            display: table;
            width: 100%;
            margin: 20px 0;
        }

        .stat-item {
            display: table-cell;
            text-align: center;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }

        .stat-item + .stat-item {
            border-right: 2px solid #fff;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>@yield('header-title', 'نظام إدارة التعلم')</h1>
            <p>@yield('header-subtitle', 'منصتك التعليمية المتكاملة')</p>
        </div>

        <div class="email-body">
            @yield('content')
        </div>

        <div class="email-footer">
            <p>هذا البريد الإلكتروني تم إرساله تلقائياً من نظام إدارة التعلم</p>
            <p style="margin-top: 10px;">
                إذا كنت لا ترغب في استلام هذه الرسائل، يمكنك
                <a href="{{ url('/student/settings/notifications') }}">تعديل إعدادات الإشعارات</a>
            </p>
            <p style="margin-top: 15px; color: #999;">
                © {{ date('Y') }} نظام إدارة التعلم. جميع الحقوق محفوظة.
            </p>
        </div>
    </div>
</body>
</html>
