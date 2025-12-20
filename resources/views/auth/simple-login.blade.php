<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            direction: rtl;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #5568d3;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .alert-danger {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }
        .credentials {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .credentials strong {
            color: #667eea;
        }
        .copy-btn {
            background: #4caf50;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 5px;
        }
        .copy-btn:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>🔐 تسجيل الدخول</h1>

        <!-- Credentials Info -->
        <div class="credentials">
            <strong>📌 بيانات الدخول الافتراضية:</strong><br>
            <div style="margin-top: 10px;">
                البريد: <button class="copy-btn" onclick="copyEmail()">📋 نسخ</button>
                <code id="email-text" style="background: white; padding: 5px; border-radius: 3px;">admin@gmail.com</code>
            </div>
            <div style="margin-top: 5px;">
                كلمة المرور: <button class="copy-btn" onclick="copyPassword()">📋 نسخ</button>
                <code id="password-text" style="background: white; padding: 5px; border-radius: 3px;">password</code>
            </div>
        </div>

        @if ($errors->any())
        <div class="alert alert-danger">
            <strong>⚠️ خطأ!</strong>
            <ul style="margin-top: 10px; padding-right: 20px;">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if (session('success'))
        <div class="alert alert-success">
            ✅ {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf

            <div class="form-group">
                <label for="email">📧 البريد الإلكتروني</label>
                <input type="email"
                       name="email"
                       id="email"
                       value="admin@gmail.com"
                       required
                       autocomplete="email"
                       placeholder="admin@gmail.com">
            </div>

            <div class="form-group">
                <label for="password">🔑 كلمة المرور</label>
                <input type="password"
                       name="password"
                       id="password"
                       value="password"
                       required
                       autocomplete="current-password"
                       placeholder="أدخل كلمة المرور">
            </div>

            <button type="submit" class="btn">
                🚀 تسجيل الدخول
            </button>
        </form>

        <div style="margin-top: 20px; text-align: center; font-size: 12px; color: #888;">
            <a href="{{ route('login') }}" style="color: #667eea;">العودة للصفحة الأصلية</a>
        </div>
    </div>

    <script>
        console.log('✅ Simple Login Page Loaded');
        console.log('Form action:', document.getElementById('loginForm').action);

        function copyEmail() {
            navigator.clipboard.writeText('admin@gmail.com');
            alert('✅ تم نسخ البريد الإلكتروني');
        }

        function copyPassword() {
            navigator.clipboard.writeText('password');
            alert('✅ تم نسخ كلمة المرور');
        }

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            console.log('🔵 Submitting login...');
            console.log('Email:', email);
            console.log('Password length:', password.length);
            console.log('Form will submit to:', this.action);
        });
    </script>
</body>
</html>
