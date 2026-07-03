<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('auth-title', 'أكاديمية كلاودسوفت')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/auth-login.css') }}?v={{ filemtime(public_path('assets/css/auth-login.css')) }}">
    @stack('auth-head')
</head>
<body>
    <div class="auth-page">
        @include('auth.partials.auth-brand')

        <main class="auth-main">
            <div class="auth-card">
                <div class="auth-card__header">
                    <div class="auth-card__logo-wrap">
                        <img src="{{ asset('assets/logo/logo.png') }}" alt="كلاودسوفت" class="auth-card__logo">
                    </div>
                    <h1>@yield('auth-heading')</h1>
                    <p>@yield('auth-subheading')</p>
                </div>

                @if ($errors->any())
                    <div class="auth-alert auth-alert--danger" role="alert">
                        <strong>يرجى مراجعة البيانات</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('status'))
                    <div class="auth-alert auth-alert--success" role="status">{{ session('status') }}</div>
                @endif

                @if (session('error'))
                    <div class="auth-alert auth-alert--danger" role="alert">{{ session('error') }}</div>
                @endif

                @yield('auth-content')

                @hasSection('auth-footer')
                    <div class="auth-footer">
                        @yield('auth-footer')
                    </div>
                @endif
            </div>
        </main>
    </div>

    @stack('auth-scripts')
</body>
</html>
