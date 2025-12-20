@extends('emails.layout')

@section('title', 'إنجاز جديد!')

@section('header-title', 'إنجاز مفتوح!')
@section('header-subtitle', 'أكملت إنجازاً جديداً')

@section('content')
    <div class="icon">{{ $achievementIcon ?? '🏆' }}</div>

    <h2 class="greeting">رائع {{ $userName }}!</h2>

    <div class="content">
        <p>أحسنت! لقد أكملت إنجازاً جديداً:</p>

        <div class="highlight-box" style="text-align: center;">
            <div style="font-size: 64px; margin-bottom: 10px;">{{ $achievementIcon ?? '🏆' }}</div>
            <h3 style="color: #667eea; font-size: 24px; margin-bottom: 10px;">{{ $achievementName }}</h3>
            <p style="color: #666; font-size: 14px;">{{ $achievementDescription }}</p>
        </div>

        <p style="margin-top: 15px;">
            كل إنجاز يقربك خطوة من أهدافك التعليمية. واصل التقدم!
        </p>

        <div style="text-align: center;">
            <a href="{{ url('/student/gamification/achievements') }}" class="btn">شاهد جميع إنجازاتك</a>
        </div>

        <div style="margin-top: 30px; background-color: #fff3cd; border-right: 4px solid #ffc107; padding: 15px; border-radius: 4px;">
            <p style="color: #856404; font-size: 14px; text-align: center;">
                ⭐ استمر في التفوق لتفتح إنجازات أعلى مستوى!
            </p>
        </div>
    </div>
@endsection
