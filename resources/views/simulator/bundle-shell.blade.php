<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'محاكاة')</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: #000;
        }
        .simulator-html-bundle-wrap {
            position: fixed;
            inset: 0;
            z-index: 1;
        }
        .simulator-bundle-iframe {
            width: 100%;
            height: 100%;
            border: 0;
            display: block;
            background: transparent;
        }
        .simulator-empty-state {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            font-family: 'Segoe UI', Tahoma, sans-serif;
        }
        .simulator-empty-box {
            text-align: center;
            max-width: 420px;
        }
        .simulator-empty-box h2 { margin: 0 0 0.5rem; }
        .simulator-empty-box .btn {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            background: #0066B3;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
        }
    </style>
    @stack('head')
</head>
<body>
    @yield('content')
    @stack('scripts')
</body>
</html>
