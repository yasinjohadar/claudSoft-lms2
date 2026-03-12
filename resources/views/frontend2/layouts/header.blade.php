<!-- ============ TOP BAR ============ -->
<div class="top-bar" id="topBar">
    <div class="container">
        <div class="top-bar-inner">
            <div class="top-bar-contact">
                <a href="mailto:info@yasinjokhadar.net" class="top-bar-item"><i class="fas fa-envelope"></i><span class="top-bar-text">info@yasinjokhadar.net</span></a>
                <a href="tel:+963XXXXXXXXX" class="top-bar-item"><i class="fas fa-phone-alt"></i><span class="top-bar-text">+963 XXX XXX XXX</span></a>
            </div>
            <div class="top-bar-links">
                <a href="{{ route('frontend.courses.index') }}" class="top-bar-item"><i class="fas fa-graduation-cap"></i><span class="top-bar-text">الكورسات</span></a>
                <a href="{{ route('frontend.home') }}#videos" class="top-bar-item"><i class="fas fa-play-circle"></i><span class="top-bar-text">الفيديوهات</span></a>
                <a href="{{ route('frontend.home') }}#consultation" class="top-bar-item"><i class="fas fa-calendar-check"></i><span class="top-bar-text">حجز موعد</span></a>
                <a href="{{ route('frontend.contact') }}" class="top-bar-item"><i class="fas fa-paper-plane"></i><span class="top-bar-text">تواصل معنا</span></a>
            </div>
        </div>
    </div>
</div>

<!-- ============ NAVBAR ============ -->
<nav class="navbar navbar-expand-lg main-navbar" id="mainNavbar">
    <div class="container">
        <a class="navbar-brand" href="{{ route('frontend.home') }}">
            <img src="{{ asset('frontend2/assets/images/logo.png') }}" alt="Claud Soft - Technical Services" class="navbar-brand-img" style="height: 40px; width: auto;">
            <span>أكاديمية كلاودسوفت</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link" href="{{ route('frontend.home') }}">الرئيسية</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('frontend.about') }}">حول الأكاديمية</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('frontend.courses.index') }}">الكورسات</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('frontend.home') }}#skills">المهارات</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('frontend.home') }}#gallery">معرض الصور</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('frontend.blog.index') }}">المدونة</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('frontend.contact') }}">تواصل معنا</a></li>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <div class="nav-social">
                    <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" title="فيسبوك" aria-label="فيسبوك"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" title="يوتيوب" aria-label="يوتيوب"><i class="fab fa-youtube"></i></a>
                    <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" title="انستغرام" aria-label="انستغرام"><i class="fab fa-instagram"></i></a>
                    <a href="https://linkedin.com" target="_blank" rel="noopener noreferrer" title="لينكد إن" aria-label="لينكد إن"><i class="fab fa-linkedin-in"></i></a>
                    <a href="https://github.com" target="_blank" rel="noopener noreferrer" title="جيت هاب" aria-label="جيت هاب"><i class="fab fa-github"></i></a>
                    <a href="https://t.me" target="_blank" rel="noopener noreferrer" title="تليجرام" aria-label="تليجرام"><i class="fab fa-telegram-plane"></i></a>
                </div>
                <button class="theme-toggle" id="themeToggle" title="تبديل الوضع" aria-label="تبديل الوضع الليلي/النهاري"><i class="fas fa-moon"></i></button>
            </div>
        </div>
    </div>
</nav>
