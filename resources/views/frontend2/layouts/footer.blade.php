<footer class="main-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <h5><img src="{{ asset('frontend2/assets/images/logo.png') }}" alt="Claud Soft" style="height:35px; width:auto; margin-left:8px; vertical-align:middle;" width="auto" height="35">أكاديمية كلاودسوفت</h5>
                <p>أكاديمية كلاودسوفت للخدمات والحلول البرمجية — تدريب تقني، تطوير ويب وموبايل، واستشارات. دورات عملية واحترافية.</p>
                <div class="footer-social">
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
                    @endif
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <h5>روابط سريعة</h5>
                <ul class="footer-links">
                    <li><a href="{{ route('frontend.home') }}"><i class="fas fa-chevron-left"></i> الرئيسية</a></li>
                    <li><a href="{{ route('frontend.about') }}"><i class="fas fa-chevron-left"></i> حول الأكاديمية</a></li>
                    <li><a href="{{ route('frontend.courses.index') }}"><i class="fas fa-chevron-left"></i> الكورسات</a></li>
                    <li><a href="{{ route('frontend.home') }}#videos"><i class="fas fa-chevron-left"></i> الفيديوهات</a></li>
                    <li><a href="{{ route('frontend.reviews.index') }}"><i class="fas fa-chevron-left"></i> آراء الطلاب</a></li>
                    <li><a href="{{ route('frontend.contact') }}"><i class="fas fa-chevron-left"></i> تواصل معنا</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h5>أحدث الكورسات</h5>
                <ul class="footer-links">
                    <li><a href="{{ route('frontend.courses.index') }}"><i class="fas fa-chevron-left"></i> تصفّح الكورسات</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h5>تواصل معنا</h5>
                <ul class="footer-links">
                    @if(isset($contactSettings) && !empty($contactSettings->email_addresses))
                        @foreach($contactSettings->email_addresses as $item)
                            @if(!empty($item['email']))
                                <li><i class="fas fa-envelope" style="color: var(--clr-primary); margin-left:8px;"></i> <a href="mailto:{{ $item['email'] }}">{{ $item['email'] }}</a></li>
                            @endif
                        @endforeach
                    @else
                        <li><i class="fas fa-envelope" style="color: var(--clr-primary); margin-left:8px;"></i> info@claudsoft.com</li>
                    @endif
                    @if(isset($contactSettings) && !empty($contactSettings->phone_numbers))
                        @foreach($contactSettings->phone_numbers as $item)
                            @if(!empty($item['number']))
                                <li><i class="fas fa-phone" style="color: var(--clr-primary); margin-left:8px;"></i> <a href="tel:{{ preg_replace('/\s+/', '', $item['number']) }}">{{ $item['number'] }}</a></li>
                            @endif
                        @endforeach
                    @else
                        <li><i class="fas fa-phone" style="color: var(--clr-primary); margin-left:8px;"></i> +966 55 196 6588</li>
                    @endif
                    @if(isset($contactSettings) && !empty($contactSettings->address_text))
                        <li><i class="fas fa-map-marker-alt" style="color: var(--clr-primary); margin-left:8px;"></i> {!! $contactSettings->address_text !!}</li>
                    @else
                        <li><i class="fas fa-map-marker-alt" style="color: var(--clr-primary); margin-left:8px;"></i> المملكة العربية السعودية - الرياض</li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            جميع الحقوق محفوظة &copy; {{ date('Y') }} <span>أكاديمية كلاودسوفت</span> | صُنع بـ ❤️
        </div>
    </div>
</footer>

<button class="back-to-top" id="backToTop" aria-label="العودة للأعلى"><i class="fas fa-chevron-up"></i></button>
