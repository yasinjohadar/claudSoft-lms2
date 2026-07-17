@extends('frontend2.layouts.master')

@section('title', 'أكاديمية كلاودسوفت للخدمات والحلول البرمجية | Claud Soft Academy')
@section('meta_description', 'أكاديمية كلاودسوفت للخدمات والحلول البرمجية — تدريب تقني، تطوير ويب وموبايل، استشارات وحلول برمجية. دورات عملية واحترافية.')

@push('head')
    <link rel="canonical" href="{{ url('/') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="{{ config('app.name', 'أكاديمية كلاودسوفت') }} — للخدمات والحلول البرمجية">
    <meta property="og:description" content="أكاديمية كلاودسوفت للخدمات والحلول البرمجية — تدريب تقني، تطوير ويب وموبايل، استشارات وحلول برمجية. دورات عملية واحترافية.">
    <meta property="og:image" content="{{ asset('frontend2/assets/images/logo.png') }}">
    <meta property="og:locale" content="ar_SY">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="أكاديمية كلاودسوفت للخدمات والحلول البرمجية">
    <meta name="twitter:description" content="أكاديمية كلاودسوفت — تدريب تقني، تطوير ويب وموبايل، استشارات وحلول برمجية. دورات عملية واحترافية.">
    <meta name="twitter:image" content="{{ asset('frontend2/assets/images/logo.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/swiper/swiper-bundle.min.css') }}">
@endpush

@section('content')

    <!-- ============ HERO SECTION ============ -->
    <section class="hero-section" id="hero">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-7 order-2 order-lg-1">
                    <div class="hero-content animate-on-scroll">
                        <h1>
                            <span id="typingText"
                                data-texts="أكاديمية كلاودسوفت|الخدمات البرمجية|الحلول التقنية|التدريب والتطوير">أكاديمية كلاودسوفت</span>
                            <span class="blinking-cursor"
                                style="animation: blink 0.8s infinite; color: var(--clr-primary);">|</span>
                            <br>
                            <span style="font-size: 0.65em; font-weight: 600; opacity: 0.95;">للخدمات والحلول البرمجية</span>
                        </h1>
                        <p class="subtitle">
                            نقدّم خدمات وحلولاً برمجية متكاملة: تدريب تقني عملي، تطوير تطبيقات الويب والموبايل، واستشارات تقنية. دورات واحترافية تناسب المبتدئين والمتخصصين.
                        </p>
                        <div class="hero-btns">
                            <a href="{{ route('frontend.courses.index') }}" class="btn-primary-custom">
                                <i class="fas fa-graduation-cap"></i> تصفّح الكورسات
                            </a>
                            <a href="{{ route('frontend.contact') }}" class="btn-outline-custom">
                                <i class="fas fa-paper-plane"></i> تواصل معي
                            </a>
                        </div>

                        <div class="hero-stats">
                            <div class="hero-stat-item">
                                <span class="stat-num counter-num" data-count="50">0+</span>
                                <span class="stat-label">دورة تدريبية</span>
                            </div>
                            <div class="hero-stat-item">
                                <span class="stat-num counter-num" data-count="5000">0+</span>
                                <span class="stat-label">طالب</span>
                            </div>
                            <div class="hero-stat-item">
                                <span class="stat-num counter-num" data-count="10">0+</span>
                                <span class="stat-label">سنوات خبرة</span>
                            </div>
                            <div class="hero-stat-item">
                                <span class="stat-num counter-num" data-count="200">0+</span>
                                <span class="stat-label">مشروع منجز</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 order-1 order-lg-2">
                    <div class="hero-image-wrapper animate-on-scroll">
                        <img src="{{ asset('frontend2/assets/images/hero-img.jpg') }}"
                            alt="تمثيل بصري لتقنية وبرمجة — أكاديمية كلاودسوفت"
                            class="hero-img"
                            width="900"
                            height="900"
                            loading="eager"
                            decoding="async">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ SKILLS SECTION ============ -->
    <section class="section-padding" id="services">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <span class="section-badge">مهاراتي</span>
                <h2>المهارات والاختصاصات</h2>
                <p>خبرة في مجالات تقنية متعددة من التطوير والإدارة إلى الأمن والاستشارات</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <a href="{{ route('frontend.services.web') }}" class="glass-panel skill-card animate-on-scroll animate-delay-1" style="text-decoration:none;color:inherit;display:block;height:100%;">
                        <div class="skill-icon"><i class="fas fa-globe"></i></div>
                        <h5>تطوير تطبيقات الويب</h5>
                        <p>تصميم وتطوير مواقع وتطبيقات ويب حديثة ومتجاوبة واحترافية</p>
                    </a>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <a href="{{ route('frontend.services.mobile') }}" class="glass-panel skill-card animate-on-scroll animate-delay-2" style="text-decoration:none;color:inherit;display:block;height:100%;">
                        <div class="skill-icon"><i class="fas fa-mobile-alt"></i></div>
                        <h5>تطبيقات الجوال</h5>
                        <p>تطوير تطبيقات الهواتف الذكية متعددة المنصات للأندرويد والـ iOS</p>
                    </a>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <a href="{{ route('frontend.services.security') }}" class="glass-panel skill-card animate-on-scroll animate-delay-3" style="text-decoration:none;color:inherit;display:block;height:100%;">
                        <div class="skill-icon"><i class="fas fa-shield-alt"></i></div>
                        <h5>أمن المعلومات</h5>
                        <p>حماية الأنظمة والبيانات وتقييم الثغرات وتطبيق أفضل الممارسات الأمنية</p>
                    </a>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <a href="{{ route('frontend.services.servers') }}" class="glass-panel skill-card animate-on-scroll animate-delay-4" style="text-decoration:none;color:inherit;display:block;height:100%;">
                        <div class="skill-icon"><i class="fas fa-server"></i></div>
                        <h5>إدارة السيرفرات</h5>
                        <p>إعداد وإدارة الخوادم، الاستضافة، والنشر مع Linux والخدمات السحابية</p>
                    </a>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <a href="{{ route('frontend.home') }}#services" class="glass-panel skill-card animate-on-scroll animate-delay-1" style="text-decoration:none;color:inherit;display:block;height:100%;">
                        <div class="skill-icon"><i class="fas fa-database"></i></div>
                        <h5>قواعد البيانات</h5>
                        <p>تصميم وإدارة قواعد البيانات SQL و NoSQL وتحسين الأداء</p>
                    </a>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <a href="{{ route('frontend.services.devops') }}" class="glass-panel skill-card animate-on-scroll animate-delay-2" style="text-decoration:none;color:inherit;display:block;height:100%;">
                        <div class="skill-icon"><i class="fas fa-cloud"></i></div>
                        <h5>DevOps والسحابة</h5>
                        <p>أتمتة النشر، الحاويات، CI/CD والعمل على منصات سحابية</p>
                    </a>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <a href="{{ route('frontend.home') }}#services" class="glass-panel skill-card animate-on-scroll animate-delay-3" style="text-decoration:none;color:inherit;display:block;height:100%;">
                        <div class="skill-icon"><i class="fas fa-project-diagram"></i></div>
                        <h5>إدارة المشاريع التقنية</h5>
                        <p>تخطيط ومتابعة المشاريع البرمجية وتنسيق الفرق التقنية</p>
                    </a>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <a href="{{ route('frontend.services.consultation') }}" class="glass-panel skill-card animate-on-scroll animate-delay-4" style="text-decoration:none;color:inherit;display:block;height:100%;">
                        <div class="skill-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                        <h5>استشارات وتدريب تقني</h5>
                        <p>تقديم الاستشارات التقنية ودورات تدريبية في البرمجة والتكنولوجيا</p>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ FEATURED COURSES ============ -->
    <section class="section-padding" id="courses" style="background: var(--clr-bg);">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <span class="section-badge">الكورسات</span>
                <h2>أحدث الدورات التدريبية</h2>
                <p>دورات عملية شاملة تأخذك من الصفر إلى الاحتراف</p>
            </div>
            <div class="home-courses-swiper-wrap position-relative">
                <div class="swiper home-courses-swiper">
                    <div class="swiper-wrapper">
                        @forelse($courses ?? [] as $course)
                        <div class="swiper-slide">
                            <a href="{{ route('frontend.courses.show', $course->slug) }}" class="glass-panel course-card animate-on-scroll animate-delay-1" style="text-decoration:none;color:inherit;cursor:pointer;">
                                <div class="course-img-wrapper">
                                    <img src="{{ $course->thumbnail ? course_image_url($course->thumbnail) : asset('frontend2/assets/images/course-webdev.svg') }}" alt="{{ $course->title }}" width="400" height="200" loading="lazy">
                                </div>
                                <div class="course-body">
                                    <h5>{{ $course->title }}</h5>
                                    <p>{{ Str::limit(strip_tags($course->description ?? ''), 100) }}</p>
                                </div>
                                <div class="course-footer">
                                    <span><i class="fas fa-folder"></i> {{ $course->category?->name ?? '—' }}</span>
                                    <span><i class="fas fa-clock"></i> {{ $course->duration ? number_format((float) $course->duration, 2) . ' ساعة' : '—' }}</span>
                                    <span class="price">{{ $course->is_free ? 'مجاني' : (isset($course->price) ? number_format((float) $course->price, 2) . ' ' . ($course->currency ?? 'ر.س') : '—') }}</span>
                                </div>
                            </a>
                        </div>
                        @empty
                        <div class="swiper-slide">
                            <a href="{{ route('frontend.courses.index') }}" class="glass-panel course-card animate-on-scroll animate-delay-1" style="text-decoration:none;color:inherit;cursor:pointer;">
                                <div class="course-img-wrapper">
                                    <img src="{{ asset('frontend2/assets/images/course-webdev.svg') }}" alt="تطوير الويب الشامل" width="400" height="200" loading="lazy">
                                    <span class="course-badge">الأكثر مبيعاً</span>
                                </div>
                                <div class="course-body">
                                    <h5>دورة تطوير الويب الشاملة</h5>
                                    <p>تعلم HTML, CSS, JavaScript, React وNode.js من الصفر حتى بناء مشاريع حقيقية كاملة</p>
                                </div>
                                <div class="course-footer">
                                    <span><i class="fas fa-folder"></i> تطوير الويب</span>
                                    <span><i class="fas fa-clock"></i> 45 ساعة</span>
                                    <span class="price">$49.99</span>
                                </div>
                            </a>
                        </div>
                        <div class="swiper-slide">
                            <a href="{{ route('frontend.courses.index') }}" class="glass-panel course-card animate-on-scroll animate-delay-2" style="text-decoration:none;color:inherit;cursor:pointer;">
                                <div class="course-img-wrapper">
                                    <img src="{{ asset('frontend2/assets/images/course-python.svg') }}" alt="بايثون للمبتدئين" width="400" height="200" loading="lazy">
                                    <span class="course-badge">جديد</span>
                                </div>
                                <div class="course-body">
                                    <h5>بايثون من الصفر إلى الاحتراف</h5>
                                    <p>تعلم لغة بايثون وعلوم البيانات والأتمتة مع تطبيقات عملية ومشاريع حقيقية</p>
                                </div>
                                <div class="course-footer">
                                    <span><i class="fas fa-folder"></i> البرمجة</span>
                                    <span><i class="fas fa-clock"></i> 35 ساعة</span>
                                    <span class="price">$39.99</span>
                                </div>
                            </a>
                        </div>
                        <div class="swiper-slide">
                            <a href="{{ route('frontend.courses.index') }}" class="glass-panel course-card animate-on-scroll animate-delay-3" style="text-decoration:none;color:inherit;cursor:pointer;">
                                <div class="course-img-wrapper">
                                    <img src="{{ asset('frontend2/assets/images/course-mobile.svg') }}" alt="تطوير تطبيقات الموبايل" width="400" height="200" loading="lazy">
                                    <span class="course-badge">متقدم</span>
                                </div>
                                <div class="course-body">
                                    <h5>تطوير تطبيقات الموبايل بـ Flutter</h5>
                                    <p>ابنِ تطبيقات موبايل احترافية لـ Android و iOS باستخدام Flutter و Dart</p>
                                </div>
                                <div class="course-footer">
                                    <span><i class="fas fa-folder"></i> تطبيقات الجوال</span>
                                    <span><i class="fas fa-clock"></i> 40 ساعة</span>
                                    <span class="price">$44.99</span>
                                </div>
                            </a>
                        </div>
                        @endforelse
                    </div>
                </div>
                <div class="swiper-button-prev home-courses-swiper-btn home-courses-swiper-btn-prev" role="button" aria-label="الشريحة السابقة"></div>
                <div class="swiper-button-next home-courses-swiper-btn home-courses-swiper-btn-next" role="button" aria-label="الشريحة التالية"></div>
            </div>
            <div class="text-center mt-5 animate-on-scroll">
                <a href="{{ route('frontend.courses.index') }}" class="btn-primary-custom">
                    <i class="fas fa-th-list"></i> عرض جميع الكورسات
                </a>
            </div>
        </div>
    </section>

    <!-- ============ TESTIMONIALS ============ -->
    <section class="section-padding" id="testimonials">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <span class="section-badge">آراء الطلاب</span>
                <h2>ماذا يقول طلابنا</h2>
                <p>آراء وتجارب بعض الطلاب الذين استفادوا من دوراتنا التدريبية</p>
            </div>
            <div class="row g-4">
                @forelse($reviews ?? [] as $review)
                <div class="col-lg-4 col-md-6">
                    <article class="tcard animate-on-scroll animate-delay-{{ min($loop->iteration, 3) }}">
                        <div class="tcard__top">
                            <div class="tcard__stars" aria-label="تقييم {{ (int)($review->rating ?? 5) }} من 5">
                                @php $rating = (int)($review->rating ?? 5); @endphp
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="{{ $i <= $rating ? 'fas' : 'far' }} fa-star"></i>
                                @endfor
                            </div>
                            <span class="tcard__quote" aria-hidden="true"><i class="fas fa-quote-left"></i></span>
                        </div>
                        <blockquote class="tcard__text">{{ Str::limit($review->review_text ?? '', 180) }}</blockquote>
                        <footer class="tcard__author">
                            <div class="tcard__avatar">
                                @if(!empty($review->student_image))
                                    <img src="{{ asset('storage/' . $review->student_image) }}" alt="" width="48" height="48" loading="lazy">
                                @elseif($review->relationLoaded('user') && $review->user && $review->user->avatar)
                                    <img src="{{ asset('storage/' . $review->user->avatar) }}" alt="" width="48" height="48" loading="lazy">
                                @else
                                    <span class="tcard__initial">{{ mb_substr($review->student_name ?? optional($review->user)->name ?? 'ط', 0, 1) }}</span>
                                @endif
                            </div>
                            <div class="tcard__meta">
                                <strong class="tcard__name">{{ $review->student_name ?? optional($review->user)->name ?? 'طالب' }}</strong>
                                <span class="tcard__role">{{ $review->student_position ?? '—' }}</span>
                            </div>
                        </footer>
                    </article>
                </div>
                @empty
                <div class="col-lg-4 col-md-6">
                    <article class="tcard animate-on-scroll animate-delay-1">
                        <div class="tcard__top">
                            <div class="tcard__stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                            <span class="tcard__quote" aria-hidden="true"><i class="fas fa-quote-left"></i></span>
                        </div>
                        <blockquote class="tcard__text">دورة تطوير الويب كانت نقطة تحول في مسيرتي المهنية. أسلوب الشرح ممتاز والتطبيقات العملية رائعة. أنصح الجميع بالتسجيل!</blockquote>
                        <footer class="tcard__author">
                            <div class="tcard__avatar"><span class="tcard__initial">أ</span></div>
                            <div class="tcard__meta">
                                <strong class="tcard__name">أحمد محمد</strong>
                                <span class="tcard__role">مطور ويب - سوريا</span>
                            </div>
                        </footer>
                    </article>
                </div>
                <div class="col-lg-4 col-md-6">
                    <article class="tcard animate-on-scroll animate-delay-2">
                        <div class="tcard__top">
                            <div class="tcard__stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                            <span class="tcard__quote" aria-hidden="true"><i class="fas fa-quote-left"></i></span>
                        </div>
                        <blockquote class="tcard__text">المدرب ياسين من أفضل المدربين العرب. شرحه واضح ومبسط، والمحتوى محدث دائماً بآخر التقنيات. استفدت كثيراً من دورة بايثون.</blockquote>
                        <footer class="tcard__author">
                            <div class="tcard__avatar"><span class="tcard__initial">س</span></div>
                            <div class="tcard__meta">
                                <strong class="tcard__name">سارة العلي</strong>
                                <span class="tcard__role">مهندسة برمجيات - الأردن</span>
                            </div>
                        </footer>
                    </article>
                </div>
                <div class="col-lg-4 col-md-6">
                    <article class="tcard animate-on-scroll animate-delay-3">
                        <div class="tcard__top">
                            <div class="tcard__stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div>
                            <span class="tcard__quote" aria-hidden="true"><i class="fas fa-quote-left"></i></span>
                        </div>
                        <blockquote class="tcard__text">تعلمت Flutter من دورة الموبايل وقمت ببناء أول تطبيق لي خلال شهرين فقط! الدعم الفني والمتابعة من المدرب كانت ممتازة.</blockquote>
                        <footer class="tcard__author">
                            <div class="tcard__avatar"><span class="tcard__initial">ع</span></div>
                            <div class="tcard__meta">
                                <strong class="tcard__name">عمر حسان</strong>
                                <span class="tcard__role">مطور تطبيقات - العراق</span>
                            </div>
                        </footer>
                    </article>
                </div>
                @endforelse
            </div>
            <div class="text-center mt-5 animate-on-scroll">
                <a href="{{ route('frontend.reviews.index') }}" class="btn-primary-custom">
                    <i class="fas fa-comments"></i> عرض كل آراء الطلاب
                </a>
            </div>
        </div>
    </section>

    <!-- ============ GALLERY SECTION ============ -->
    <section class="section-padding gallery-section" id="gallery">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <span class="section-badge">معرض الصور</span>
                <h2>صور من نشاطاتنا</h2>
                <p>لقطات من فعالياتنا وورشاتنا ودوراتنا التدريبية</p>
            </div>
            <div class="gallery-grid animate-on-scroll">
                <div class="gallery-item" role="button" tabindex="0">
                    <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800&h=560&fit=crop&q=80" alt="ورشة عمل تطوير الويب" width="400" height="280" loading="lazy">
                    <div class="gallery-overlay">
                        <span class="gallery-zoom" aria-hidden="true"><i class="fas fa-search-plus"></i></span>
                        <span class="gallery-caption">ورشة عمل تطوير الويب</span>
                    </div>
                </div>
                <div class="gallery-item" role="button" tabindex="0">
                    <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&h=560&fit=crop&q=80" alt="محاضرة تدريبية" width="400" height="280" loading="lazy">
                    <div class="gallery-overlay">
                        <span class="gallery-zoom" aria-hidden="true"><i class="fas fa-search-plus"></i></span>
                        <span class="gallery-caption">محاضرة في تطوير البرمجيات</span>
                    </div>
                </div>
                <div class="gallery-item" role="button" tabindex="0">
                    <img src="https://images.unsplash.com/photo-1531482615713-2afd69097998?w=800&h=560&fit=crop&q=80" alt="فعالية تقنية" width="400" height="280" loading="lazy">
                    <div class="gallery-overlay">
                        <span class="gallery-zoom" aria-hidden="true"><i class="fas fa-search-plus"></i></span>
                        <span class="gallery-caption">فعالية تقنية وورشات عمل</span>
                    </div>
                </div>
                <div class="gallery-item" role="button" tabindex="0">
                    <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=800&h=560&fit=crop&q=80" alt="دورة تدريبية" width="400" height="280" loading="lazy">
                    <div class="gallery-overlay">
                        <span class="gallery-zoom" aria-hidden="true"><i class="fas fa-search-plus"></i></span>
                        <span class="gallery-caption">دورة تحليل البيانات والتدريب</span>
                    </div>
                </div>
            </div>
            <div class="gallery-cta animate-on-scroll">
                <a href="#gallery" class="gallery-cta-btn">
                    <i class="fas fa-images"></i>
                    عرض المعرض
                </a>
            </div>
        </div>
    </section>

    <!-- ============ VIDEOS SECTION ============ -->
    <section class="section-padding videos-section" id="videos">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <span class="section-badge">الفيديوهات</span>
                <h2>فيديوهات من نشاطاتنا</h2>
                <p>مقاطع تعليمية وعملية من قناتنا وحصصنا التدريبية</p>
            </div>
            <div class="videos-grid animate-on-scroll">
                <article class="vcard">
                    <a class="vcard__media" href="https://www.youtube.com" target="_blank" rel="noopener noreferrer" aria-label="مشاهدة: أساسيات تطوير الويب">
                        <img src="https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=800&h=500&fit=crop&q=80" alt="أساسيات تطوير الويب" width="400" height="240" loading="lazy">
                        <span class="vcard__yt" aria-hidden="true"><i class="fab fa-youtube"></i></span>
                        <span class="vcard__watch"><i class="fas fa-play"></i> شاهد الآن</span>
                    </a>
                    <div class="vcard__body">
                        <h6 class="vcard__title">أساسيات تطوير الويب</h6>
                        <div class="vcard__meta">
                            <span class="vcard__views"><i class="fas fa-eye"></i> 15,000 مشاهدة</span>
                            <a class="vcard__ext" href="https://www.youtube.com" target="_blank" rel="noopener noreferrer">
                                يوتيوب <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>
                </article>

                <article class="vcard">
                    <a class="vcard__media" href="https://www.youtube.com" target="_blank" rel="noopener noreferrer" aria-label="مشاهدة: مقدمة في لغة بايثون">
                        <img src="https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?w=800&h=500&fit=crop&q=80" alt="مقدمة في لغة بايثون" width="400" height="240" loading="lazy">
                        <span class="vcard__yt" aria-hidden="true"><i class="fab fa-youtube"></i></span>
                        <span class="vcard__watch"><i class="fas fa-play"></i> شاهد الآن</span>
                    </a>
                    <div class="vcard__body">
                        <h6 class="vcard__title">مقدمة في لغة بايثون</h6>
                        <div class="vcard__meta">
                            <span class="vcard__views"><i class="fas fa-eye"></i> 12,000 مشاهدة</span>
                            <a class="vcard__ext" href="https://www.youtube.com" target="_blank" rel="noopener noreferrer">
                                يوتيوب <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>
                </article>

                <article class="vcard">
                    <a class="vcard__media" href="https://www.youtube.com" target="_blank" rel="noopener noreferrer" aria-label="مشاهدة: بناء تطبيق بـ Flutter">
                        <img src="https://images.unsplash.com/photo-1551650975-87deedd944c3?w=800&h=500&fit=crop&q=80" alt="بناء تطبيق متكامل بـ Flutter" width="400" height="240" loading="lazy">
                        <span class="vcard__yt" aria-hidden="true"><i class="fab fa-youtube"></i></span>
                        <span class="vcard__watch"><i class="fas fa-play"></i> شاهد الآن</span>
                    </a>
                    <div class="vcard__body">
                        <h6 class="vcard__title">بناء تطبيق متكامل بـ Flutter</h6>
                        <div class="vcard__meta">
                            <span class="vcard__views"><i class="fas fa-eye"></i> 8,500 مشاهدة</span>
                            <a class="vcard__ext" href="https://www.youtube.com" target="_blank" rel="noopener noreferrer">
                                يوتيوب <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>
                </article>
            </div>
            <div class="videos-cta animate-on-scroll">
                <a href="#videos" class="videos-cta-btn">
                    <i class="fas fa-play"></i>
                    عرض كل الفيديوهات
                </a>
            </div>
        </div>
    </section>

    <!-- ============ BLOG SECTION ============ -->
    <section class="section-padding blog-section" id="blog">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <span class="section-badge">المدونة</span>
                <h2>آخر التدوينات</h2>
                <p>مقالات تقنية وتعليمية في عالم البرمجة والتكنولوجيا</p>
            </div>
            <div class="blog-grid animate-on-scroll">
                @forelse($latestPosts ?? [] as $index => $post)
                <article class="bcard {{ $index === 0 ? 'bcard--featured' : '' }}">
                    <a class="bcard__media" href="{{ route('frontend.blog.show', $post->slug) }}" aria-label="{{ $post->title }}">
                        <img src="{{ $post->featured_image ? blog_image_url($post->featured_image) : 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&h=500&fit=crop&q=80' }}"
                             alt="{{ $post->title }}" width="400" height="220" loading="lazy">
                    </a>
                    <div class="bcard__body">
                        <div class="bcard__tags">
                            <span class="bcard__tag">
                                <i class="fas fa-calendar-alt"></i>
                                {{ $post->published_at?->translatedFormat('d F Y') ?? $post->published_at?->format('d M Y') }}
                            </span>
                            @if($post->category)
                                <span class="bcard__tag">
                                    <i class="fas fa-tag"></i>
                                    {{ $post->category->name }}
                                </span>
                            @endif
                        </div>
                        <h5 class="bcard__title">
                            <a href="{{ route('frontend.blog.show', $post->slug) }}">{{ Str::limit($post->title, 55) }}</a>
                        </h5>
                        <p class="bcard__excerpt">{{ Str::limit(strip_tags($post->excerpt ?? $post->content ?? ''), 90) }}</p>
                        <a href="{{ route('frontend.blog.show', $post->slug) }}" class="bcard__more">
                            اقرأ المزيد <i class="fas fa-arrow-left"></i>
                        </a>
                    </div>
                </article>
                @empty
                <article class="bcard bcard--featured">
                    <a class="bcard__media" href="{{ route('frontend.blog.index') }}">
                        <img src="https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=800&h=500&fit=crop&q=80" alt="تطوير الويب" width="400" height="220" loading="lazy">
                    </a>
                    <div class="bcard__body">
                        <div class="bcard__tags">
                            <span class="bcard__tag"><i class="fas fa-calendar-alt"></i> 20 فبراير 2026</span>
                            <span class="bcard__tag"><i class="fas fa-tag"></i> تطوير الويب</span>
                        </div>
                        <h5 class="bcard__title"><a href="{{ route('frontend.blog.index') }}">أفضل 10 أدوات لمطوري الويب في 2026</a></h5>
                        <p class="bcard__excerpt">تعرف على أحدث الأدوات والتقنيات التي يجب على كل مطور ويب معرفتها هذا العام.</p>
                        <a href="{{ route('frontend.blog.index') }}" class="bcard__more">اقرأ المزيد <i class="fas fa-arrow-left"></i></a>
                    </div>
                </article>
                <article class="bcard">
                    <a class="bcard__media" href="{{ route('frontend.blog.index') }}">
                        <img src="https://images.unsplash.com/photo-1677442136019-21780ecad995?w=800&h=500&fit=crop&q=80" alt="الذكاء الاصطناعي" width="400" height="220" loading="lazy">
                    </a>
                    <div class="bcard__body">
                        <div class="bcard__tags">
                            <span class="bcard__tag"><i class="fas fa-calendar-alt"></i> 15 فبراير 2026</span>
                            <span class="bcard__tag"><i class="fas fa-tag"></i> الذكاء الاصطناعي</span>
                        </div>
                        <h5 class="bcard__title"><a href="{{ route('frontend.blog.index') }}">كيف تبدأ في تعلم الذكاء الاصطناعي</a></h5>
                        <p class="bcard__excerpt">دليل شامل للمبتدئين في عالم الذكاء الاصطناعي وتعلم الآلة مع بايثون.</p>
                        <a href="{{ route('frontend.blog.index') }}" class="bcard__more">اقرأ المزيد <i class="fas fa-arrow-left"></i></a>
                    </div>
                </article>
                <article class="bcard">
                    <a class="bcard__media" href="{{ route('frontend.blog.index') }}">
                        <img src="https://images.unsplash.com/photo-1551650975-87deedd944c3?w=800&h=500&fit=crop&q=80" alt="موبايل" width="400" height="220" loading="lazy">
                    </a>
                    <div class="bcard__body">
                        <div class="bcard__tags">
                            <span class="bcard__tag"><i class="fas fa-calendar-alt"></i> 10 فبراير 2026</span>
                            <span class="bcard__tag"><i class="fas fa-tag"></i> موبايل</span>
                        </div>
                        <h5 class="bcard__title"><a href="{{ route('frontend.blog.index') }}">Flutter vs React Native: مقارنة شاملة</a></h5>
                        <p class="bcard__excerpt">مقارنة تفصيلية بين أشهر إطارين لتطوير تطبيقات الموبايل في 2026.</p>
                        <a href="{{ route('frontend.blog.index') }}" class="bcard__more">اقرأ المزيد <i class="fas fa-arrow-left"></i></a>
                    </div>
                </article>
                @endforelse
            </div>
            <div class="blog-cta animate-on-scroll">
                <a href="{{ route('frontend.blog.index') }}" class="blog-cta-btn">
                    عرض كل التدوينات
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- ============ CTA SECTION ============ -->
    <section class="cta-section">
        <div class="container animate-on-scroll">
            <h2>هل أنت مستعد لبدء رحلتك البرمجية؟</h2>
            <p>انضم لأكثر من 5000 طالب وابدأ رحلتك في عالم البرمجة اليوم</p>
            <a href="{{ route('frontend.courses.index') }}" class="btn-light-custom">
                <i class="fas fa-rocket"></i> ابدأ الآن
            </a>
        </div>
    </section>

    <!-- Lightbox (للمعرض) -->
    <div class="lightbox-overlay" id="lightbox">
        <button class="lightbox-close" id="lightboxClose"><i class="fas fa-times"></i></button>
        <img src="" alt="" id="lightboxImg">
    </div>

    <style>
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0; }
        }
    </style>
@endsection

@push('scripts')
    <script src="{{ asset('assets/libs/swiper/swiper-bundle.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.querySelector('.home-courses-swiper');
            if (!el || typeof Swiper === 'undefined') return;
            new Swiper('.home-courses-swiper', {
                rtl: true,
                slidesPerView: 1,
                spaceBetween: 24,
                watchOverflow: true,
                navigation: {
                    nextEl: '.home-courses-swiper-wrap .swiper-button-next',
                    prevEl: '.home-courses-swiper-wrap .swiper-button-prev',
                },
                breakpoints: {
                    576: { slidesPerView: 2, spaceBetween: 24 },
                    992: { slidesPerView: 3, spaceBetween: 24 },
                    1200: { slidesPerView: 4, spaceBetween: 24 },
                },
            });
        });
    </script>
@endpush
