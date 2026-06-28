<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 0; background: #f3f4f6; color: #111827; }
        .guest-wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem; }
        .guest-card { width: 100%; max-width: 28rem; background: #fff; padding: 1.5rem; border-radius: .5rem; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
    </style>
</head>
<body>
    <div class="guest-wrap">
        <div class="guest-card">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
