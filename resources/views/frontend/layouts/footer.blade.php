<script src="{{ asset('frontend/assets/bootstrap.js') }}"></script>

<!-- Modern Professional Footer -->
<footer class="modern-footer">
    <div class="footer-top">
        <div class="container">
            <div class="row g-4">
                <!-- About Section -->
                <div class="col-lg-4 col-md-6">
                    <div class="footer-widget">
                        <div class="footer-logo mb-3">
                            <img src="{{ asset('frontend/assets/images/logo.png') }}" alt="كلاودسوفت" width="180">
                        </div>
                        <p class="footer-description">
                            منصة تعليمية رائدة تقدم أفضل الدورات والكورسات في البرمجة والتصميم والتسويق الرقمي لتأهيل المتدربين لسوق العمل باحترافية عالية.
                        </p>
                        <div class="footer-social-links mt-4">
                            @if(isset($contactSettings) && $contactSettings->social_links)
                                @foreach($contactSettings->social_links as $social)
                                    @if(!empty($social['enabled']) && !empty($social['url']))
                                    <a href="{{ $social['url'] }}" 
                                       class="social-link" 
                                       title="{{ $social['label'] ?? $social['platform'] }}"
                                       target="_blank"
                                       rel="noopener noreferrer">
                                        <i class="fa-brands {{ $social['icon'] ?? 'fa-link' }}"></i>
                                    </a>
                                    @endif
                                @endforeach
                            @else
                                {{-- Fallback إذا لم تكن هناك إعدادات --}}
                                <a href="#" class="social-link" title="فيسبوك">
                                    <i class="fa-brands fa-facebook-f"></i>
                                </a>
                                <a href="#" class="social-link" title="تويتر">
                                    <i class="fa-brands fa-twitter"></i>
                                </a>
                                <a href="#" class="social-link" title="إنستجرام">
                                    <i class="fa-brands fa-instagram"></i>
                                </a>
                                <a href="#" class="social-link" title="لينكد إن">
                                    <i class="fa-brands fa-linkedin-in"></i>
                                </a>
                                <a href="#" class="social-link" title="يوتيوب">
                                    <i class="fa-brands fa-youtube"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6">
                    <div class="footer-widget">
                        <h5 class="footer-widget-title">روابط سريعة</h5>
                        <ul class="footer-links">
                            <li><a href="{{ route('frontend.home') }}"><i class="fa-solid fa-angle-left"></i> الرئيسية</a></li>
                            <li><a href="{{ route('frontend.courses.index') }}"><i class="fa-solid fa-angle-left"></i> الكورسات</a></li>
                            <li><a href="{{ route('frontend.blog.index') }}"><i class="fa-solid fa-angle-left"></i> المدونة</a></li>
                            <li><a href="{{ route('frontend.reviews.index') }}"><i class="fa-solid fa-angle-left"></i> آراء الطلاب</a></li>
                            <li><a href="{{ route('frontend.contact') }}"><i class="fa-solid fa-angle-left"></i> اتصل بنا</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Services -->
                <div class="col-lg-2 col-md-6">
                    <div class="footer-widget">
                        <h5 class="footer-widget-title">خدماتنا</h5>
                        <ul class="footer-links">
                            <li><a href="#"><i class="fa-solid fa-angle-left"></i> دورات البرمجة</a></li>
                            <li><a href="#"><i class="fa-solid fa-angle-left"></i> التصميم الجرافيكي</a></li>
                            <li><a href="#"><i class="fa-solid fa-angle-left"></i> التسويق الرقمي</a></li>
                            <li><a href="#"><i class="fa-solid fa-angle-left"></i> استضافة المواقع</a></li>
                            <li><a href="#"><i class="fa-solid fa-angle-left"></i> الاستشارات</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Contact & Newsletter -->
                <div class="col-lg-4 col-md-6">
                    <div class="footer-widget">
                        <h5 class="footer-widget-title">تواصل معنا</h5>
                        <ul class="footer-contact">
                            @if(isset($contactSettings))
                                @if($contactSettings->address_text)
                                <li>
                                    <i class="fa-solid {{ $contactSettings->address_icon ?? 'fa-location-dot' }}"></i>
                                    <span>{!! nl2br(e($contactSettings->address_text)) !!}</span>
                                </li>
                                @endif

                                @if($contactSettings->phone_numbers && count($contactSettings->phone_numbers) > 0)
                                    @foreach($contactSettings->phone_numbers as $phone)
                                        @if(!empty($phone['number']))
                                        <li>
                                            <i class="fa-solid {{ $contactSettings->phone_icon ?? 'fa-phone' }}"></i>
                                            <a href="tel:{{ $phone['number'] }}">{{ $phone['number'] }}</a>
                                            @if(!empty($phone['label']))
                                                <small class="d-block text-muted" style="color: rgba(255,255,255,0.6);">{{ $phone['label'] }}</small>
                                            @endif
                                        </li>
                                        @endif
                                    @endforeach
                                @endif

                                @if($contactSettings->email_addresses && count($contactSettings->email_addresses) > 0)
                                    @foreach($contactSettings->email_addresses as $email)
                                        @if(!empty($email['email']))
                                        <li>
                                            <i class="fa-solid {{ $contactSettings->email_icon ?? 'fa-envelope' }}"></i>
                                            <a href="mailto:{{ $email['email'] }}">{{ $email['email'] }}</a>
                                            @if(!empty($email['label']))
                                                <small class="d-block text-muted" style="color: rgba(255,255,255,0.6);">{{ $email['label'] }}</small>
                                            @endif
                                        </li>
                                        @endif
                                    @endforeach
                                @endif
                            @else
                                {{-- Fallback إذا لم تكن هناك إعدادات --}}
                                <li>
                                    <i class="fa-solid fa-location-dot"></i>
                                    <span>الرياض، المملكة العربية السعودية</span>
                                </li>
                                <li>
                                    <i class="fa-solid fa-phone"></i>
                                    <a href="tel:+966551234567">+966 55 123 4567</a>
                                </li>
                                <li>
                                    <i class="fa-solid fa-envelope"></i>
                                    <a href="mailto:info@claudsoft.com">info@claudsoft.com</a>
                                </li>
                            @endif
                        </ul>

                        <div class="footer-newsletter mt-4">
                            <h6 class="newsletter-title">اشترك في النشرة البريدية</h6>
                            <form action="#" method="POST" class="newsletter-form">
                                <div class="input-group">
                                    <input type="email" class="form-control" placeholder="بريدك الإلكتروني" required>
                                    <button type="submit" class="btn btn-subscribe">
                                        <i class="fa-solid fa-paper-plane"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-end">
                    <p class="footer-copyright mb-0">
                        &copy; {{ date('Y') }} <strong>أكاديمية كلاودسوفت</strong>. جميع الحقوق محفوظة
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-start">
                        <ul class="footer-bottom-links">
                        <li><a href="{{ route('frontend.contact') }}">اتصل بنا</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button class="back-to-top" id="backToTop" title="العودة للأعلى">
    <i class="fa-solid fa-arrow-up"></i>
</button>

<style>
/* Modern Professional Footer Styles */
.modern-footer {
    background: #2c3e50;
    color: #ffffff;
    position: relative;
}

.modern-footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: #3498db;
}

.footer-top {
    padding: 60px 0 40px;
}

.footer-widget {
    margin-bottom: 20px;
}

.footer-logo img {
    max-width: 180px;
    height: auto;
    filter: brightness(0) invert(1);
}

.footer-description {
    color: rgba(255, 255, 255, 0.8);
    line-height: 1.8;
    font-size: 14px;
}

.footer-widget-title {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 25px;
}

/* Social Links */
.footer-social-links {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.social-link {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    color: #ffffff;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.social-link:hover {
    background: #ffffff;
    color: #1e3c72;
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(255, 255, 255, 0.3);
}

/* Footer Links */
.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 12px;
}

.footer-links a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.footer-links a:hover {
    color: #ffffff;
    transform: translateX(-5px);
}

.footer-links a i {
    font-size: 12px;
    transition: transform 0.3s ease;
    color: rgba(255, 255, 255, 0.8) !important;
}

.footer-links a:hover i {
    transform: translateX(-3px);
    color: #ffffff !important;
}

/* Contact Info */
.footer-contact {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-contact li {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 15px;
    color: rgba(255, 255, 255, 0.8);
    font-size: 14px;
}

.footer-contact li i {
    color: rgba(255, 255, 255, 0.9) !important;
    font-size: 16px;
    margin-top: 2px;
}

.footer-contact a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-contact a:hover {
    color: #ffffff;
}

/* Newsletter */
.footer-newsletter {
    background: rgba(255, 255, 255, 0.05);
    padding: 20px;
    border-radius: 10px;
    backdrop-filter: blur(10px);
}

.newsletter-title {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 15px;
    color: #ffffff;
}

.newsletter-form .input-group {
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
    border-radius: 8px;
    overflow: hidden;
}

.newsletter-form .form-control {
    border: none;
    padding: 12px 15px;
    background: rgba(255, 255, 255, 0.95);
    color: #333;
    font-size: 14px;
}

.newsletter-form .form-control:focus {
    box-shadow: none;
    background: #ffffff;
}

.newsletter-form .btn-subscribe {
    background: #3498db;
    border: none;
    padding: 12px 20px;
    color: #ffffff;
    transition: all 0.3s ease;
}

.newsletter-form .btn-subscribe:hover {
    background: #2980b9;
    transform: scale(1.05);
}

/* Footer Bottom */
.footer-bottom {
    background: rgba(0, 0, 0, 0.2);
    padding: 25px 0;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.footer-copyright {
    color: rgba(255, 255, 255, 0.8);
    font-size: 14px;
}

.footer-bottom-links {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
}

.footer-bottom-links li {
    position: relative;
}

.footer-bottom-links li:not(:last-child)::after {
    content: '|';
    position: absolute;
    left: -12px;
    color: rgba(255, 255, 255, 0.3);
}

.footer-bottom-links a {
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    font-size: 13px;
    transition: color 0.3s ease;
}

.footer-bottom-links a:hover {
    color: #ffffff;
}

/* Back to Top Button */
.back-to-top {
    position: fixed;
    bottom: 30px;
    left: 30px;
    width: 45px;
    height: 45px;
    background: #3498db;
    color: #ffffff;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    z-index: 999;
    box-shadow: 0 5px 20px rgba(52, 152, 219, 0.4);
}

.back-to-top.show {
    opacity: 1;
    visibility: visible;
}

.back-to-top:hover {
    background: #2980b9;
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(52, 152, 219, 0.6);
}

/* Responsive */
@media (max-width: 768px) {
    .footer-top {
        padding: 40px 0 30px;
    }

    .footer-widget {
        text-align: center;
    }

    .footer-logo,
    .footer-social-links {
        justify-content: center;
    }


    .footer-links a,
    .footer-contact li {
        justify-content: center;
    }

    .footer-bottom-links {
        margin-top: 15px;
    }

    .back-to-top {
        bottom: 20px;
        left: 20px;
        width: 40px;
        height: 40px;
    }
}
</style>

<script>
// Back to Top Button Functionality
window.addEventListener('scroll', function() {
    const backToTop = document.getElementById('backToTop');
    if (window.pageYOffset > 300) {
        backToTop.classList.add('show');
    } else {
        backToTop.classList.remove('show');
    }
});

document.getElementById('backToTop').addEventListener('click', function() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});
</script>
