<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'بطاقة تعريفية')</title>
    <link rel="icon" type="image/png" href="{{ asset('frontend2/assets/images/logo.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@500;600;700;800&family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    @stack('head')
</head>
<body class="profile-card-page-body">
    @yield('content')
    @stack('scripts')
</body>
</html>
