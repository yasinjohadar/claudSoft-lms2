@extends('emails.layout')

@section('title', 'مستوى جديد!')

@section('header-title', 'ترقية!')
@section('header-subtitle', 'وصلت إلى مستوى جديد')

@section('content')
    <div class="icon">⬆️</div>

    <h2 class="greeting">تهانينا {{ $userName }}!</h2>

    <div class="content">
        <p>أنت الآن أقوى وأفضل! لقد ترقيت إلى مستوى جديد:</p>

        <div class="highlight-box" style="text-align: center; background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);">
            <div style="font-size: 64px; margin-bottom: 10px;">⭐</div>
            <h3 style="color: #667eea; font-size: 36px; margin-bottom: 10px;">المستوى {{ $newLevel }}</h3>
            <p style="color: #666; font-size: 14px;">مستوى جديد من التميز!</p>
        </div>

        <div style="margin-top: 25px; background-color: #f8f9fa; padding: 20px; border-radius: 8px;">
            <h4 style="color: #333; margin-bottom: 15px; text-align: center;">مزايا المستوى {{ $newLevel }}</h4>
            <ul style="list-style: none; padding: 0;">
                <li style="margin-bottom: 10px; color: #666;">
                    <span style="color: #28a745; font-weight: bold;">✓</span> نقاط إضافية لكل نشاط
                </li>
                <li style="margin-bottom: 10px; color: #666;">
                    <span style="color: #28a745; font-weight: bold;">✓</span> الوصول إلى شارات خاصة
                </li>
                <li style="margin-bottom: 10px; color: #666;">
                    <span style="color: #28a745; font-weight: bold;">✓</span> ترتيب أعلى في لوحة المتصدرين
                </li>
                <li style="margin-bottom: 10px; color: #666;">
                    <span style="color: #28a745; font-weight: bold;">✓</span> محتوى حصري للمستويات العالية
                </li>
            </ul>
        </div>

        <p style="margin-top: 20px; text-align: center;">
            استمر في التعلم للوصول إلى المستوى {{ $newLevel + 1 }}!
        </p>

        <div style="text-align: center;">
            <a href="{{ url('/student/gamification/levels') }}" class="btn">شاهد تقدمك</a>
        </div>

        <div style="margin-top: 30px; text-align: center; color: #888; font-size: 13px;">
            <p>💪 أنت في الطريق الصحيح نحو التميز!</p>
        </div>
    </div>
@endsection
