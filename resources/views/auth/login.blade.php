<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>تسجيل الدخول - أكاديمية كلاودسوفت</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Cairo', sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            direction: rtl;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
            padding: 40px;
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo-container img {
            max-width: 120px;
            height: auto;
            margin-bottom: 8px;
        }
        
        .logo-container .logo-title {
            color: #0555a2;
            font-size: 18px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 0;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .login-header h1 {
            color: #333;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: #666;
            font-size: 16px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            font-family: 'Cairo', sans-serif;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #0555a2;
            background: white;
            box-shadow: 0 0 0 3px rgba(5, 85, 162, 0.1);
        }
        
        .form-control.is-invalid {
            border-color: #dc3545;
        }
        
        .password-input-wrap {
            position: relative;
        }
        
        .password-input-wrap .form-control {
            padding-inline-end: 3rem;
        }
        
        .password-toggle-btn {
            position: absolute;
            inset-inline-end: 8px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            padding: 0;
            border: none;
            border-radius: 8px;
            background: transparent;
            color: #0555a2;
            cursor: pointer;
            transition: color 0.2s ease, transform 0.2s ease, background 0.2s ease;
        }
        
        .password-toggle-btn:hover {
            color: #044080;
            background: rgba(5, 85, 162, 0.08);
            transform: translateY(-50%) scale(1.06);
        }
        
        .password-toggle-btn:focus {
            outline: none;
        }
        
        .password-toggle-btn:focus-visible {
            outline: 2px solid #0555a2;
            outline-offset: 2px;
            background: rgba(5, 85, 162, 0.1);
        }
        
        .password-toggle-btn svg {
            width: 22px;
            height: 22px;
            flex-shrink: 0;
        }
        
        .password-toggle-btn .icon-password-hide {
            display: none;
        }
        
        .password-toggle-btn.is-password-revealed .icon-password-show {
            display: none;
        }
        
        .password-toggle-btn.is-password-revealed .icon-password-hide {
            display: block;
        }
        
        .invalid-feedback {
            display: block;
            color: #dc3545;
            font-size: 13px;
            margin-top: 5px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-left: 8px;
            cursor: pointer;
        }
        
        .remember-me label {
            color: #666;
            font-size: 14px;
            cursor: pointer;
            margin: 0;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: #0555a2;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            font-family: 'Cairo', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .btn-login:hover {
            background: #044080;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(5, 85, 162, 0.3);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .forgot-password {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .forgot-password a {
            color: #0555a2;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }
        
        .forgot-password a:hover {
            color: #044080;
            text-decoration: underline;
        }
        
        .register-link {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        
        .register-link p {
            color: #666;
            font-size: 14px;
            margin: 0;
        }
        
        .register-link a {
            color: #0555a2;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .register-link a:hover {
            color: #044080;
            text-decoration: underline;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-danger {
            background-color: #fee;
            border: 1px solid #fcc;
            color: #c33;
        }
        
        .alert-success {
            background-color: #efe;
            border: 1px solid #cfc;
            color: #3c3;
        }
        
        .alert ul {
            margin: 5px 0 0 20px;
            padding: 0;
        }
        
        @media (max-width: 576px) {
            .login-container {
                padding: 30px 20px;
            }
            
            .login-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <img src="{{ asset('assets/logo/logo.png') }}" alt="Logo">
            <div class="logo-title">أكاديمية كلاودسوفت</div>
        </div>
        
        <div class="login-header">
            <h1>أهلاً بعودتك</h1>
            <p>يرجى تسجيل الدخول للمتابعة</p>
        </div>

                                            @if ($errors->any())
            <div class="alert alert-danger">
                <strong>خطأ!</strong>
                <ul>
                                                        @foreach ($errors->all() as $error)
                                                            <li>{{ $error }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif

                                            @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
                                                </div>
                                            @endif

                                            @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
                                                </div>
                                            @endif

                                            <form method="POST" action="{{ route('login') }}">
                                                @csrf
            
            <div class="form-group">
                <label for="email">البريد الإلكتروني</label>
                <input 
                    id="email" 
                    type="email" 
                    name="email" 
                    class="form-control @error('email') is-invalid @enderror" 
                    value="{{ old('email') }}" 
                    required 
                    autofocus 
                    autocomplete="username"
                    placeholder="أدخل بريدك الإلكتروني"
                >
                                                    @error('email')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <div class="password-input-wrap">
                    <input 
                        id="password" 
                        type="password" 
                        name="password" 
                        class="form-control @error('password') is-invalid @enderror" 
                        required 
                        autocomplete="current-password"
                        placeholder="أدخل كلمة المرور"
                    >
                    <button
                        type="button"
                        id="password-toggle"
                        class="password-toggle-btn"
                        aria-pressed="false"
                        aria-label="إظهار كلمة المرور"
                        title="إظهار كلمة المرور"
                    >
                        <!-- عين مغلقة + رموش فوق الجفن: النص مخفي -->
                        <svg class="icon-password-show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path stroke-width="1.65" d="M3 10.5c3.2-2.3 6.8-3.3 9.5-3.3s6.3 1 9.5 3.3" />
                            <path stroke-width="1.65" d="M3 13.5c3.2 2.3 6.8 3.3 9.5 3.3s6.3-1 9.5-3.3" />
                            <path stroke-width="1.2" opacity="0.92" d="M4.8 9l-1.1-2.5M7.8 7.5l-0.65-2.85M12 6.7V3.5M16.2 7.5l0.65-2.85M19.2 9l1.1-2.5" />
                        </svg>
                        <!-- عين مفتوحة واسعة + بؤبؤ ولمعة: تعبير النظر إلى النص الظاهر -->
                        <svg class="icon-password-hide" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" d="M1.5 12s4-7 10.5-7S22.5 12 22.5 12s-4 7-10.5 7S1.5 12 1.5 12z" />
                            <ellipse cx="12.5" cy="12" rx="3.4" ry="3.8" fill="currentColor" stroke="none" />
                            <circle cx="10.6" cy="9.9" r="1.15" fill="#ffffff" stroke="none" opacity="0.95" />
                        </svg>
                    </button>
                </div>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="remember-me">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">تذكرني</label>
                                                    </div>

            <button type="submit" class="btn-login">
                تسجيل الدخول
            </button>
                                            </form>

        @if (Route::has('password.request'))
            <div class="forgot-password">
                <a href="{{ route('password.request') }}">نسيت كلمة المرور؟</a>
                                            </div>
        @endif

        <div class="register-link">
            <p>ليس لديك حساب؟ <a href="{{ route('register') }}">إنشاء حساب جديد</a></p>
        </div>
    </div>
    <script>
        (function () {
            var input = document.getElementById('password');
            var btn = document.getElementById('password-toggle');
            if (!input || !btn) return;
            function applyState(revealed) {
                input.type = revealed ? 'text' : 'password';
                btn.classList.toggle('is-password-revealed', revealed);
                btn.setAttribute('aria-pressed', revealed ? 'true' : 'false');
                btn.setAttribute('aria-label', revealed ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور');
                btn.setAttribute('title', revealed ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور');
            }

            applyState(false);

            btn.addEventListener('click', function () {
                applyState(input.type === 'password');
            });
        })();
    </script>
</body>
</html>
