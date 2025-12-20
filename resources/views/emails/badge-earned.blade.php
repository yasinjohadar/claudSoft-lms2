@extends('emails.layout')

@section('title', 'حصلت على شارة جديدة!')

@section('header-title', 'إنجاز جديد!')
@section('header-subtitle', 'لقد حصلت على شارة جديدة')

@section('content')
    <div class="icon">{{ $badgeIcon ?? '🏅' }}</div>

    <h2 class="greeting">مبروك {{ $userName }}!</h2>

    <div class="content">
        <p>تهانينا! لقد حصلت على شارة جديدة:</p>

        <div class="highlight-box" style="text-align: center;">
            <div style="font-size: 64px; margin-bottom: 10px;">{{ $badgeIcon ?? '🏅' }}</div>
            <h3 style="color: #667eea; font-size: 24px; margin-bottom: 10px;">{{ $badgeName }}</h3>
            <p style="color: #666; font-size: 14px;">{{ $badgeDescription }}</p>
        </div>

        <p style="margin-top: 15px;">
            استمر في التقدم واجمع المزيد من الشارات لفتح المزايا الخاصة!
        </p>

        <div style="text-align: center;">
            <a href="{{ url('/student/gamification/badges') }}" class="btn">شاهد جميع شاراتك</a>
        </div>

        <div class="stats" style="margin-top: 30px; background-color: #f8f9fa; padding: 15px; border-radius: 5px;">
            <p style="text-align: center; color: #666; font-size: 14px;">
                💡 نصيحة: استمر في التعلم بانتظام لفتح المزيد من الشارات النادرة!
            </p>
        </div>
    </div>
@endsection
