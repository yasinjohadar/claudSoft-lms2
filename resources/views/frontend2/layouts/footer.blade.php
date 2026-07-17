<footer class="main-footer ftr">
    <div class="container">
        <div class="ftr__grid">
            {{-- Brand --}}
            <div class="ftr__brand">
                <a href="{{ route('frontend.home') }}" class="ftr__logo">
                    <img src="{{ asset('frontend2/assets/images/logo.png') }}" alt="أكاديمية كلاودسوفت" width="42" height="42">
                    <span>أكاديمية كلاودسوفت</span>
                </a>
                <p class="ftr__about">أكاديمية كلاودسوفت للخدمات والحلول البرمجية — تدريب تقني، تطوير ويب وموبايل، واستشارات. دورات عملية واحترافية.</p>
                <div class="ftr__social">
                    @php $socialLinks = $contactSettings->social_links ?? []; @endphp
                    @foreach($socialLinks as $link)
                        @if(!empty($link['enabled']) && !empty($link['url']))
                            <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" title="{{ $link['label'] ?? $link['platform'] }}" aria-label="{{ $link['label'] ?? $link['platform'] }}"><i class="fab {{ $link['icon'] ?? 'fa-link' }}"></i></a>
                        @endif
                    @endforeach
                    @if(empty($socialLinks) || collect($socialLinks)->where('enabled', true)->isEmpty())
                        <a href="#" target="_blank" rel="noopener noreferrer" title="فيسبوك" aria-label="فيسبوك"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" target="_blank" rel="noopener noreferrer" title="يوتيوب" aria-label="يوتيوب"><i class="fab fa-youtube"></i></a>
                        <a href="#" target="_blank" rel="noopener noreferrer" title="انستغرام" aria-label="انستغرام"><i class="fab fa-instagram"></i></a>
                        <a href="#" target="_blank" rel="noopener noreferrer" title="لينكد إن" aria-label="لينكد إن"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" target="_blank" rel="noopener noreferrer" title="جيت هاب" aria-label="جيت هاب"><i class="fab fa-github"></i></a>
                    @endif
                </div>
            </div>

            {{-- Quick links --}}
            <div class="ftr__col">
                <h5 class="ftr__title">روابط سريعة</h5>
                <ul class="ftr__links">
                    <li><a href="{{ route('frontend.home') }}"><i class="fas fa-chevron-left"></i> الرئيسية</a></li>
                    <li><a href="{{ route('frontend.about') }}"><i class="fas fa-chevron-left"></i> حول الأكاديمية</a></li>
                    <li><a href="{{ route('frontend.courses.index') }}"><i class="fas fa-chevron-left"></i> الكورسات</a></li>
                    <li><a href="{{ route('frontend.home') }}#videos"><i class="fas fa-chevron-left"></i> الفيديوهات</a></li>
                    <li><a href="{{ route('frontend.reviews.index') }}"><i class="fas fa-chevron-left"></i> آراء الطلاب</a></li>
                    <li><a href="{{ route('frontend.contact') }}"><i class="fas fa-chevron-left"></i> تواصل معنا</a></li>
                </ul>
            </div>

            {{-- Services --}}
            <div class="ftr__col">
                <h5 class="ftr__title">خدماتنا</h5>
                <ul class="ftr__links">
                    <li><a href="{{ route('frontend.services.web') }}"><i class="fas fa-chevron-left"></i> تطوير الويب</a></li>
                    <li><a href="{{ route('frontend.services.mobile') }}"><i class="fas fa-chevron-left"></i> تطبيقات الموبايل</a></li>
                    <li><a href="{{ route('frontend.services.security') }}"><i class="fas fa-chevron-left"></i> الأمن السيبراني</a></li>
                    <li><a href="{{ route('frontend.services.servers') }}"><i class="fas fa-chevron-left"></i> الخوادم والاستضافة</a></li>
                    <li><a href="{{ route('frontend.services.devops') }}"><i class="fas fa-chevron-left"></i> DevOps</a></li>
                    <li><a href="{{ route('frontend.services.consultation') }}"><i class="fas fa-chevron-left"></i> الاستشارات التقنية</a></li>
                </ul>
            </div>

            {{-- Contact --}}
            <div class="ftr__col">
                <h5 class="ftr__title">تواصل معنا</h5>
                <ul class="ftr__contact">
                    @if(isset($contactSettings) && !empty($contactSettings->email_addresses))
                        @foreach($contactSettings->email_addresses as $item)
                            @if(!empty($item['email']))
                                <li>
                                    <span class="ftr__cicon"><i class="fas fa-envelope"></i></span>
                                    <a href="mailto:{{ $item['email'] }}">{{ $item['email'] }}</a>
                                </li>
                            @endif
                        @endforeach
                    @else
                        <li>
                            <span class="ftr__cicon"><i class="fas fa-envelope"></i></span>
                            <a href="mailto:info@claudsoft.com">info@claudsoft.com</a>
                        </li>
                    @endif
                    @if(isset($contactSettings) && !empty($contactSettings->phone_numbers))
                        @foreach($contactSettings->phone_numbers as $item)
                            @if(!empty($item['number']))
                                <li>
                                    <span class="ftr__cicon"><i class="fas fa-phone"></i></span>
                                    <a href="tel:{{ preg_replace('/\s+/', '', $item['number']) }}" dir="ltr">{{ $item['number'] }}</a>
                                </li>
                            @endif
                        @endforeach
                    @else
                        <li>
                            <span class="ftr__cicon"><i class="fas fa-phone"></i></span>
                            <a href="tel:+966551966588" dir="ltr">+966 55 196 6588</a>
                        </li>
                    @endif
                    @if(isset($contactSettings) && !empty($contactSettings->address_text))
                        <li>
                            <span class="ftr__cicon"><i class="fas fa-map-marker-alt"></i></span>
                            <span>{!! $contactSettings->address_text !!}</span>
                        </li>
                    @else
                        <li>
                            <span class="ftr__cicon"><i class="fas fa-map-marker-alt"></i></span>
                            <span>المملكة العربية السعودية - الرياض</span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        {{-- Newsletter --}}
        <div class="ftr__news">
            <div class="ftr__news-copy">
                <span class="ftr__news-badge">النشرة البريدية</span>
                <h3 class="ftr__news-title">اشترك ليصلك كل جديد <i class="fas fa-paper-plane"></i></h3>
                <p class="ftr__news-desc">كن أول من يعرف عن الدورات والمقالات والعروض الجديدة مباشرة إلى بريدك.</p>
            </div>
            <form class="ftr__news-form" action="{{ route('frontend.contact') }}" method="get" onsubmit="return false;">
                <div class="ftr__news-field">
                    <span class="ftr__news-field-icon" aria-hidden="true"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" placeholder="بريدك الإلكتروني..." required aria-label="البريد الإلكتروني" autocomplete="email">
                    <button type="button" class="ftr__news-btn">
                        اشترك الآن <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
                <p class="ftr__news-note"><i class="fas fa-lock"></i> لن نشارك بريدك مع أي طرف ثالث</p>
            </form>
        </div>

        {{-- Bottom bar --}}
        <div class="ftr__bottom">
            <p class="ftr__copy">جميع الحقوق محفوظة &copy; {{ date('Y') }} <span>أكاديمية كلاودسوفت</span> | صُنع بـ ❤️</p>
            <nav class="ftr__bottom-links" aria-label="روابط الفوتر">
                <a href="{{ route('frontend.home') }}">الرئيسية</a>
                <a href="{{ route('frontend.contact') }}">تواصل معنا</a>
            </nav>
        </div>
    </div>
</footer>

<button class="back-to-top" id="backToTop" aria-label="العودة للأعلى"><i class="fas fa-chevron-up"></i></button>
