@extends('emails.layout')

@section('title', 'مرحباً بك في نظام إدارة التعلم')

@section('header-title', 'مرحباً بك!')
@section('header-subtitle', 'نحن سعداء بانضمامك إلينا')

@section('content')
    <div class="icon">👋</div>

    <h2 class="greeting">مرحباً {{ $userName }}،</h2>

    <div class="content">
        <p>نرحب بك في <strong>نظام إدارة التعلم</strong> - منصتك التعليمية المتكاملة!</p>

        <p style="margin-top: 15px;">
            نحن متحمسون لانضمامك إلى مجتمعنا التعليمي. يمكنك الآن الوصول إلى:
        </p>

        <div class="highlight-box">
            <ul style="list-style: none; padding: 0;">
                <li style="margin-bottom: 10px;">✅ مكتبة واسعة من الدورات التعليمية</li>
                <li style="margin-bottom: 10px;">✅ تتبع تقدمك وإنجازاتك</li>
                <li style="margin-bottom: 10px;">✅ نظام تحفيزي متقدم مع الشارات والنقاط</li>
                <li style="margin-bottom: 10px;">✅ تفاعل مع المعلمين والطلاب الآخرين</li>
            </ul>
        </div>

        <p style="margin-top: 15px;">
            ابدأ رحلتك التعليمية الآن واستكشف كل ما نقدمه!
        </p>

        <div style="text-align: center;">
            <a href="{{ url('/student/dashboard') }}" class="btn">ابدأ التعلم الآن</a>
        </div>

        <p style="margin-top: 20px; color: #888; font-size: 14px;">
            إذا كان لديك أي أسئلة، لا تتردد في التواصل معنا.
        </p>
    </div>
@endsection
