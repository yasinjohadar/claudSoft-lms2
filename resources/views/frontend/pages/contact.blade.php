@extends('frontend.layouts.master')

@section('title', 'اتصل بنا')
@section('meta_description', 'تواصل معنا - نحن هنا للإجابة على استفساراتك ومساعدتك')

@section('content')

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="page-title">{{ $settings->page_title }}</h1>
                <p class="page-subtitle">{{ $settings->page_subtitle }}</p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active">اتصل بنا</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="contact-section py-5">
    <div class="container">
        <!-- Contact Info Cards -->
        <div class="row mb-5">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="contact-info-card">
                    <div class="icon-wrapper">
                        <i class="fa-solid {{ $settings->address_icon }}"></i>
                    </div>
                    <h4>{{ $settings->address_title }}</h4>
                    <p>{!! nl2br(e($settings->address_text)) !!}</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="contact-info-card">
                    <div class="icon-wrapper">
                        <i class="fa-solid {{ $settings->phone_icon }}"></i>
                    </div>
                    <h4>{{ $settings->phone_title }}</h4>
                    <p dir="ltr">
                        @foreach($settings->phone_numbers ?? [] as $phone)
                            @if(!empty($phone['number']))
                                <a href="tel:{{ $phone['number'] }}">{{ $phone['number'] }}</a>
                                @if(!empty($phone['label']))
                                    <small class="d-block text-muted">{{ $phone['label'] }}</small>
                                @endif
                                @if(!$loop->last)<br>@endif
                            @endif
                        @endforeach
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="contact-info-card">
                    <div class="icon-wrapper">
                        <i class="fa-solid {{ $settings->email_icon }}"></i>
                    </div>
                    <h4>{{ $settings->email_title }}</h4>
                    <p>
                        @foreach($settings->email_addresses ?? [] as $email)
                            @if(!empty($email['email']))
                                <a href="mailto:{{ $email['email'] }}">{{ $email['email'] }}</a>
                                @if(!empty($email['label']))
                                    <small class="d-block text-muted">{{ $email['label'] }}</small>
                                @endif
                                @if(!$loop->last)<br>@endif
                            @endif
                        @endforeach
                    </p>
                </div>
            </div>
        </div>

        <!-- Contact Form & Map -->
        <div class="row">
            <!-- Contact Form -->
            <div class="col-lg-6 mb-4">
                @if($settings->form_enabled)
                <div class="contact-form-wrapper">
                    <div class="form-header">
                        <h3>{{ $settings->form_title }}</h3>
                        <p>{{ $settings->form_subtitle }}</p>
                    </div>

                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-check-circle"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-exclamation-circle"></i>
                        <strong>حدث خطأ!</strong> يرجى التحقق من البيانات المدخلة.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    <form action="{{ route('frontend.contact.send') }}" method="POST" class="contact-form">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="name" class="form-label">
                                        <i class="fa-solid fa-user"></i>
                                        الاسم الكامل <span class="required">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror"
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name') }}"
                                           placeholder="أدخل اسمك الكامل" 
                                           required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        <i class="fa-solid fa-envelope"></i>
                                        البريد الإلكتروني <span class="required">*</span>
                                    </label>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror"
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email') }}"
                                           placeholder="example@email.com" 
                                           required>
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="phone" class="form-label">
                                        <i class="fa-solid fa-phone"></i>
                                        رقم الجوال
                                    </label>
                                    <input type="tel" 
                                           class="form-control @error('phone') is-invalid @enderror"
                                           id="phone" 
                                           name="phone" 
                                           value="{{ old('phone') }}"
                                           placeholder="05xxxxxxxx">
                                    @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="subject" class="form-label">
                                        <i class="fa-solid fa-tag"></i>
                                        الموضوع <span class="required">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('subject') is-invalid @enderror"
                                           id="subject" 
                                           name="subject" 
                                           value="{{ old('subject') }}"
                                           placeholder="موضوع الرسالة" 
                                           required>
                                    @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="message" class="form-label">
                                <i class="fa-solid fa-message"></i>
                                الرسالة <span class="required">*</span>
                            </label>
                            <textarea class="form-control @error('message') is-invalid @enderror"
                                      id="message" 
                                      name="message" 
                                      rows="6"
                                      placeholder="اكتب رسالتك هنا..." 
                                      required>{{ old('message') }}</textarea>
                            @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-submit w-100">
                            <i class="fa-solid fa-paper-plane"></i>
                            إرسال الرسالة
                        </button>
                    </form>
                </div>
                @endif
            </div>

            <!-- Map & Social -->
            <div class="col-lg-6 mb-4">
                <!-- Map -->
                @if($settings->show_map && $settings->map_embed_url)
                <div class="map-wrapper mb-4">
                    <iframe src="{{ $settings->map_embed_url }}"
                            width="100%" 
                            height="350" 
                            style="border:0; border-radius: 10px;" 
                            allowfullscreen="" 
                            loading="lazy"
                            title="موقعنا على الخريطة"></iframe>
                </div>
                @endif

                <!-- Social Media -->
                @if(!empty($settings->social_links))
                <div class="social-media-box">
                    <div class="social-header">
                        <h4>
                            <i class="fa-solid fa-share-nodes"></i>
                            {{ $settings->social_title }}
                        </h4>
                        @if($settings->social_subtitle)
                        <p>{{ $settings->social_subtitle }}</p>
                        @endif
                    </div>
                    <div class="social-links-grid">
                        @foreach($settings->social_links as $social)
                            @if(($social['enabled'] ?? true) && !empty($social['url']))
                            <a href="{{ $social['url'] }}" 
                               class="social-link-item {{ $social['platform'] ?? '' }}" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               title="{{ $social['label'] ?? '' }}">
                                <div class="social-icon-circle">
                                    <i class="fa-brands {{ $social['icon'] ?? 'fa-link' }}"></i>
                                </div>
                                <span class="social-label">{{ $social['label'] ?? '' }}</span>
                            </a>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Working Hours -->
                @if($settings->show_working_hours && !empty($settings->working_hours))
                <div class="working-hours-box mt-4">
                    <h4>
                        <i class="fa-solid fa-clock"></i>
                        {{ $settings->working_hours_title }}
                    </h4>
                    <ul class="hours-list">
                        @foreach($settings->working_hours as $hour)
                            @if(!empty($hour['day']) && !empty($hour['time']))
                            <li>
                                <span class="day">{{ $hour['day'] }}</span>
                                <span class="time">{{ $hour['time'] }}</span>
                            </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

<style>
/* ============================================
   Page Header - Unified with other pages
   ============================================ */
.page-header {
    background: var(--secondary-Color) !important;
    color: #ffffff !important;
    padding: 80px 0 40px !important;
    margin-bottom: 40px !important;
}

.page-title {
    font-size: 2.5rem !important;
    font-weight: 700 !important;
    margin-bottom: 15px !important;
    color: #ffffff !important;
}

.page-subtitle {
    font-size: 1.2rem;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 25px;
}

.page-header .breadcrumb {
    background: transparent;
}

.page-header .breadcrumb-item a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: color 0.3s ease;
}

.page-header .breadcrumb-item a:hover {
    color: #ffffff;
}

.page-header .breadcrumb-item.active {
    color: #ffffff;
}

.page-header .breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: rgba(255, 255, 255, 0.6);
    margin: 0 10px;
}

/* ============================================
   Contact Section
   ============================================ */
.contact-section {
    background: #f8f9fa;
    min-height: 60vh;
}

/* ============================================
   Contact Info Cards
   ============================================ */
.contact-info-card {
    background: #ffffff;
    padding: 40px 30px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    height: 100%;
    border: 1px solid #f0f0f0;
}

.contact-info-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    border-color: var(--main-Color);
}

.icon-wrapper {
    width: 80px;
    height: 80px;
    background: var(--main-Color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    transition: all 0.3s ease;
}

.contact-info-card:hover .icon-wrapper {
    background: var(--secondary-Color);
    transform: scale(1.1) rotate(5deg);
}

.icon-wrapper i {
    font-size: 35px;
    color: #ffffff;
}

.contact-info-card h4 {
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--secondary-Color);
    margin-bottom: 15px;
}

.contact-info-card p {
    color: #666;
    line-height: 1.8;
    margin: 0;
}

.contact-info-card a {
    color: var(--main-Color);
    text-decoration: none;
    transition: color 0.3s ease;
    display: inline-block;
}

.contact-info-card a:hover {
    color: var(--secondary-Color);
    text-decoration: underline;
}

/* ============================================
   Contact Form
   ============================================ */
.contact-form-wrapper {
    background: #ffffff;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #f0f0f0;
}

.form-header {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid var(--main-Color);
}

.form-header h3 {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--secondary-Color);
    margin-bottom: 10px;
}

.form-header p {
    color: #666;
    margin: 0;
    font-size: 0.95rem;
}

.form-group {
    margin-bottom: 0;
}

.form-label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.95rem;
}

.form-label i {
    color: var(--main-Color);
    font-size: 14px;
}

.required {
    color: #e74c3c;
    margin-right: 3px;
}

.form-control {
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 12px 15px;
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.form-control:focus {
    border-color: var(--main-Color);
    box-shadow: 0 0 0 0.2rem rgba(242, 145, 37, 0.15);
    outline: none;
}

.form-control.is-invalid {
    border-color: #e74c3c;
}

.invalid-feedback {
    font-size: 0.875rem;
    display: block;
    margin-top: 5px;
}

.btn-submit {
    background: var(--main-Color);
    color: #ffffff;
    border: none;
    padding: 15px 40px;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
}

.btn-submit:hover {
    background: var(--secondary-Color);
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
    color: #ffffff;
}

.btn-submit:active {
    transform: translateY(0);
}

/* ============================================
   Alerts
   ============================================ */
.alert {
    border-radius: 8px;
    padding: 15px 20px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-success {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
}

.alert-danger {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}

.alert i {
    font-size: 1.2rem;
}

/* ============================================
   Map
   ============================================ */
.map-wrapper {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #f0f0f0;
}

.map-wrapper iframe {
    display: block;
    width: 100%;
}

/* ============================================
   Social Media Box
   ============================================ */
.social-media-box,
.working-hours-box {
    background: #ffffff;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #f0f0f0;
}

.social-header {
    margin-bottom: 25px;
    text-align: center;
}

.social-media-box h4,
.working-hours-box h4 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--secondary-Color);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.social-media-box h4 i,
.working-hours-box h4 i {
    color: var(--main-Color);
    font-size: 1.3rem;
}

.social-media-box p {
    color: #666;
    margin: 0;
    font-size: 0.95rem;
    line-height: 1.6;
}

.social-links-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
}

.social-link-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px 15px;
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    min-height: 100px;
}

.social-link-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.1);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.social-link-item:hover::before {
    opacity: 1;
}

.social-link-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.social-link-item.facebook { 
    background: var(--secondary-Color); 
}

.social-link-item.twitter { 
    background: var(--secondary-Color); 
}

.social-link-item.instagram { 
    background: var(--secondary-Color); 
}

.social-link-item.youtube { 
    background: var(--secondary-Color); 
}

.social-link-item.whatsapp { 
    background: var(--secondary-Color); 
}

.social-link-item.telegram { 
    background: var(--secondary-Color); 
}

.social-icon-circle {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.25);
    border-radius: 50%;
    transition: all 0.3s ease;
    margin-bottom: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
}

.social-link-item:hover .social-icon-circle {
    background: rgba(255, 255, 255, 0.35);
    transform: scale(1.15) rotate(5deg);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.25);
}

.social-icon-circle i {
    font-size: 24px;
    color: #ffffff;
    display: block;
}

.social-label {
    color: #ffffff;
    font-size: 0.9rem;
    font-weight: 600;
    text-align: center;
    margin-top: 5px;
    transition: all 0.3s ease;
}

.social-link-item:hover .social-label {
    transform: scale(1.05);
}

/* ============================================
   Working Hours
   ============================================ */
.hours-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.hours-list li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #f0f0f0;
}

.hours-list li:last-child {
    border-bottom: none;
}

.day {
    font-weight: 600;
    color: #2c3e50;
}

.time {
    color: var(--main-Color);
    font-weight: 600;
}

/* ============================================
   Responsive Design
   ============================================ */
@media (max-width: 992px) {
    .contact-section {
        padding: 40px 0;
    }
}

@media (max-width: 768px) {
    .page-header {
        padding: 60px 0 30px;
    }

    .page-title {
        font-size: 2rem;
    }

    .page-subtitle {
        font-size: 1rem;
    }

    .contact-form-wrapper {
        padding: 25px 20px;
    }

    .form-header h3 {
        font-size: 1.5rem;
    }

    .contact-info-card {
        margin-bottom: 20px;
        padding: 30px 20px;
    }

    .icon-wrapper {
        width: 70px;
        height: 70px;
    }

    .icon-wrapper i {
        font-size: 28px;
    }

    .section-header h2 {
        font-size: 2rem;
    }

    .social-media-box,
    .working-hours-box {
        padding: 20px;
    }

    .social-links-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    .social-link-item {
        padding: 15px 10px;
        min-height: 90px;
    }

    .social-icon-circle {
        width: 45px;
        height: 45px;
    }

    .social-icon-circle i {
        font-size: 20px;
    }

    .social-label {
        font-size: 0.85rem;
    }

    .map-wrapper iframe {
        height: 300px;
    }
}

@media (max-width: 576px) {
    .page-title {
        font-size: 1.75rem;
    }

    .contact-info-card h4 {
        font-size: 1.2rem;
    }

    .form-header h3 {
        font-size: 1.3rem;
    }

    .section-header h2 {
        font-size: 1.75rem;
    }

    .accordion-button {
        padding: 15px 20px;
        font-size: 1rem;
    }

    .accordion-body {
        padding: 15px 20px;
    }
}
</style>

@endsection
