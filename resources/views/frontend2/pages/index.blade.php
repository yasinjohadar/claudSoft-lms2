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
                        <div class="hero-ring"></div>
                        <img src="{{ asset('frontend2/assets/images/trainer.svg') }}" alt="أكاديمية كلاودسوفت" class="hero-img" width="350" height="350" loading="eager">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ SKILLS SECTION ============ -->
    <section class="section-padding" id="skills">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <span class="section-badge">مهاراتي</span>
                <h2>المهارات والاختصاصات</h2>
                <p>خبرة في مجالات تقنية متعددة من التطوير والإدارة إلى الأمن والاستشارات</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <a href="{{ route('frontend.skills.web') }}" class="glass-panel skill-card animate-on-scroll animate-delay-1" style="text-decoration:none;color:inherit;display:block;height:100%;">
                        <div class="skill-icon"><i class="fas fa-globe"></i></div>
                        <h5>تطوير تطبيقات الويب</h5>
                        <p>تصميم وتطوير مواقع وتطبيقات ويب حديثة ومتجاوبة واحترافية</p>
                    </a>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <a href="{{ route('frontend.skills.mobile') }}" class="glass-panel skill-card animate-on-scroll animate-delay-2" style="text-decoration:none;color:inherit;display:block;height:100%;">
                        <div class="skill-icon"><i class="fas fa-mobile-alt"></i></div>
                        <h5>تطبيقات الجوال</h5>
                        <p>تطوير تطبيقات الهواتف الذكية متعددة المنصات للأندرويد والـ iOS</p>
                    </a>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <a href="{{ route('frontend.skills.security') }}" class="glass-panel skill-card animate-on-scroll animate-delay-3" style="text-decoration:none;color:inherit;display:block;height:100%;">
                        <div class="skill-icon"><i class="fas fa-shield-alt"></i></div>
                        <h5>أمن المعلومات</h5>
                        <p>حماية الأنظمة والبيانات وتقييم الثغرات وتطبيق أفضل الممارسات الأمنية</p>
                    </a>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <a href="{{ route('frontend.skills.servers') }}" class="glass-panel skill-card animate-on-scroll animate-delay-4" style="text-decoration:none;color:inherit;display:block;height:100%;">
                        <div class="skill-icon"><i class="fas fa-server"></i></div>
                        <h5>إدارة السيرفرات</h5>
                        <p>إعداد وإدارة الخوادم، الاستضافة، والنشر مع Linux والخدمات السحابية</p>
                    </a>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <a href="{{ route('frontend.home') }}#skills" class="glass-panel skill-card animate-on-scroll animate-delay-1" style="text-decoration:none;color:inherit;display:block;height:100%;">
                        <div class="skill-icon"><i class="fas fa-database"></i></div>
                        <h5>قواعد البيانات</h5>
                        <p>تصميم وإدارة قواعد البيانات SQL و NoSQL وتحسين الأداء</p>
                    </a>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <a href="{{ route('frontend.skills.devops') }}" class="glass-panel skill-card animate-on-scroll animate-delay-2" style="text-decoration:none;color:inherit;display:block;height:100%;">
                        <div class="skill-icon"><i class="fas fa-cloud"></i></div>
                        <h5>DevOps والسحابة</h5>
                        <p>أتمتة النشر، الحاويات، CI/CD والعمل على منصات سحابية</p>
                    </a>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <a href="{{ route('frontend.home') }}#skills" class="glass-panel skill-card animate-on-scroll animate-delay-3" style="text-decoration:none;color:inherit;display:block;height:100%;">
                        <div class="skill-icon"><i class="fas fa-project-diagram"></i></div>
                        <h5>إدارة المشاريع التقنية</h5>
                        <p>تخطيط ومتابعة المشاريع البرمجية وتنسيق الفرق التقنية</p>
                    </a>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <a href="{{ route('frontend.skills.consultation') }}" class="glass-panel skill-card animate-on-scroll animate-delay-4" style="text-decoration:none;color:inherit;display:block;height:100%;">
                        <div class="skill-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                        <h5>استشارات وتدريب تقني</h5>
                        <p>تقديم الاستشارات التقنية ودورات تدريبية في البرمجة والتكنولوجيا</p>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ FEATURED COURSES ============ -->
    <section class="section-padding" id="courses" style="background: var(--clr-bg-secondary);">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <span class="section-badge">الكورسات</span>
                <h2>أحدث الدورات التدريبية</h2>
                <p>دورات عملية شاملة تأخذك من الصفر إلى الاحتراف</p>
            </div>
            <div class="row g-4">
                @forelse($courses ?? [] as $course)
                <div class="col-lg-4 col-md-6">
                    <a href="{{ route('frontend.courses.show', $course->slug) }}" class="glass-panel course-card animate-on-scroll animate-delay-1" style="text-decoration:none;color:inherit;cursor:pointer;">
                        <div class="course-img-wrapper">
                            <img src="{{ $course->thumbnail ? course_image_url($course->thumbnail) : asset('frontend2/assets/images/course-webdev.svg') }}" alt="{{ $course->title }}" width="400" height="200" loading="lazy">
                            @if($course->is_featured)<span class="course-badge">مميز</span>@endif
                        </div>
                        <div class="course-body">
                            <h5>{{ $course->title }}</h5>
                            <p>{{ Str::limit(strip_tags($course->description ?? ''), 100) }}</p>
                        </div>
                        <div class="course-footer">
                            <span><i class="fas fa-users"></i> {{ number_format($course->students_count ?? 0) }} طالب</span>
                            <span><i class="fas fa-clock"></i> {{ $course->duration ? number_format((float) $course->duration, 2) . ' ساعة' : '—' }}</span>
                            <span class="price">{{ $course->is_free ? 'مجاني' : (isset($course->price) ? number_format((float) $course->price, 2) . ' ' . ($course->currency ?? 'ر.س') : '—') }}</span>
                        </div>
                    </a>
                </div>
                @empty
                <div class="col-lg-4 col-md-6">
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
                            <span><i class="fas fa-users"></i> 1,200 طالب</span>
                            <span><i class="fas fa-clock"></i> 45 ساعة</span>
                            <span class="price">$49.99</span>
                        </div>
                    </a>
                </div>
                <div class="col-lg-4 col-md-6">
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
                            <span><i class="fas fa-users"></i> 800 طالب</span>
                            <span><i class="fas fa-clock"></i> 35 ساعة</span>
                            <span class="price">$39.99</span>
                        </div>
                    </a>
                </div>
                <div class="col-lg-4 col-md-6">
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
                            <span><i class="fas fa-users"></i> 650 طالب</span>
                            <span><i class="fas fa-clock"></i> 40 ساعة</span>
                            <span class="price">$44.99</span>
                        </div>
                    </a>
                </div>
                @endforelse
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
                    <div class="glass-panel testimonial-card animate-on-scroll animate-delay-{{ min($loop->iteration, 3) }}">
                        <div class="stars">
                            @php $rating = (int)($review->rating ?? 5); @endphp
                            @for($i = 1; $i <= 5; $i++)
                                <i class="{{ $i <= $rating ? 'fas' : 'far' }} fa-star"></i>
                            @endfor
                        </div>
                        <p class="quote-text">"{{ Str::limit($review->review_text ?? '', 180) }}"</p>
                        <div class="student-info">
                            <div>
                                <div class="student-name">{{ $review->student_name ?? optional($review->user)->name ?? 'طالب' }}</div>
                                <div class="student-role">{{ $review->student_position ?? '—' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel testimonial-card animate-on-scroll animate-delay-1">
                        <div class="stars">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <p class="quote-text">"دورة تطوير الويب كانت نقطة تحول في مسيرتي المهنية. أسلوب الشرح ممتاز والتطبيقات العملية رائعة. أنصح الجميع بالتسجيل!"</p>
                        <div class="student-info">
                            <div>
                                <div class="student-name">أحمد محمد</div>
                                <div class="student-role">مطور ويب - سوريا</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel testimonial-card animate-on-scroll animate-delay-2">
                        <div class="stars">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <p class="quote-text">"المدرب ياسين من أفضل المدربين العرب. شرحه واضح ومبسط، والمحتوى محدث دائماً بآخر التقنيات. استفدت كثيراً من دورة بايثون."</p>
                        <div class="student-info">
                            <div>
                                <div class="student-name">سارة العلي</div>
                                <div class="student-role">مهندسة برمجيات - الأردن</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel testimonial-card animate-on-scroll animate-delay-3">
                        <div class="stars">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                        </div>
                        <p class="quote-text">"تعلمت Flutter من دورة الموبايل وقمت ببناء أول تطبيق لي خلال شهرين فقط! الدعم الفني والمتابعة من المدرب كانت ممتازة."</p>
                        <div class="student-info">
                            <div>
                                <div class="student-name">عمر حسان</div>
                                <div class="student-role">مطور تطبيقات - العراق</div>
                            </div>
                        </div>
                    </div>
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
    <section class="section-padding" id="gallery" style="background: var(--clr-bg-secondary);">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <span class="section-badge">معرض الصور</span>
                <h2>صور من نشاطاتي</h2>
                <p>لقطات من الفعاليات والورشات والدورات التدريبية</p>
            </div>
            <div class="gallery-grid animate-on-scroll">
                <div class="gallery-item">
                    <img src="{{ asset('frontend2/assets/images/workshop.svg') }}" alt="ورشة عمل تقنية" width="400" height="250" loading="lazy">
                    <div class="gallery-overlay">
                        <span class="gallery-caption">ورشة عمل تطوير الويب</span>
                    </div>
                </div>
                <div class="gallery-item">
                    <img src="{{ asset('frontend2/assets/images/trainer.svg') }}" alt="محاضرة تدريبية" width="400" height="250" loading="lazy">
                    <div class="gallery-overlay">
                        <span class="gallery-caption">محاضرة في تطوير البرمجيات</span>
                    </div>
                </div>
                <div class="gallery-item">
                    <img src="{{ asset('frontend2/assets/images/course-webdev.svg') }}" alt="فعالية تقنية" width="400" height="250" loading="lazy">
                    <div class="gallery-overlay">
                        <span class="gallery-caption">فعالية تقنية سنوية</span>
                    </div>
                </div>
                <div class="gallery-item">
                    <img src="{{ asset('frontend2/assets/images/course-python.svg') }}" alt="مؤتمر تكنولوجيا" width="400" height="250" loading="lazy">
                    <div class="gallery-overlay">
                        <span class="gallery-caption">مؤتمر التكنولوجيا والابتكار</span>
                    </div>
                </div>
                <div class="gallery-item">
                    <img src="{{ asset('frontend2/assets/images/course-mobile.svg') }}" alt="تخريج دورة" width="400" height="250" loading="lazy">
                    <div class="gallery-overlay">
                        <span class="gallery-caption">حفل تخريج دورة Flutter</span>
                    </div>
                </div>
                <div class="gallery-item">
                    <img src="{{ asset('frontend2/assets/images/workshop.svg') }}" alt="ورشة برمجة" width="400" height="250" loading="lazy">
                    <div class="gallery-overlay">
                        <span class="gallery-caption">ورشة البرمجة للمبتدئين</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ VIDEOS SECTION ============ -->
    <section class="section-padding" id="videos">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <span class="section-badge">الفيديوهات</span>
                <h2>فيديوهات من أعمالي</h2>
                <p>مقاطع فيديو تعليمية وعملية من القناة</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel video-card animate-on-scroll animate-delay-1">
                        <div class="video-wrapper" onclick="window.open('https://youtube.com', '_blank')">
                            <img src="{{ asset('frontend2/assets/images/course-webdev.svg') }}" alt="فيديو تعليمي" width="400" height="200" loading="lazy">
                            <div class="play-btn"><i class="fas fa-play-circle"></i></div>
                        </div>
                        <div class="video-body">
                            <h6>أساسيات تطوير الويب</h6>
                            <span><i class="fas fa-eye"></i> 15,000 مشاهدة</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel video-card animate-on-scroll animate-delay-2">
                        <div class="video-wrapper" onclick="window.open('https://youtube.com', '_blank')">
                            <img src="{{ asset('frontend2/assets/images/course-python.svg') }}" alt="فيديو بايثون" width="400" height="200" loading="lazy">
                            <div class="play-btn"><i class="fas fa-play-circle"></i></div>
                        </div>
                        <div class="video-body">
                            <h6>مقدمة في لغة بايثون</h6>
                            <span><i class="fas fa-eye"></i> 12,000 مشاهدة</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel video-card animate-on-scroll animate-delay-3">
                        <div class="video-wrapper" onclick="window.open('https://youtube.com', '_blank')">
                            <img src="{{ asset('frontend2/assets/images/course-mobile.svg') }}" alt="فيديو Flutter" width="400" height="200" loading="lazy">
                            <div class="play-btn"><i class="fas fa-play-circle"></i></div>
                        </div>
                        <div class="video-body">
                            <h6>بناء تطبيق متكامل بـ Flutter</h6>
                            <span><i class="fas fa-eye"></i> 8,500 مشاهدة</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5 animate-on-scroll">
                <a href="{{ route('frontend.home') }}#videos" class="btn-primary-custom">
                    <i class="fas fa-play-circle"></i> عرض كل الفيديوهات
                </a>
            </div>
        </div>
    </section>

    <!-- ============ BLOG SECTION ============ -->
    <section class="section-padding" id="blog" style="background: var(--clr-bg-secondary);">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <span class="section-badge">المدونة</span>
                <h2>آخر التدوينات</h2>
                <p>مقالات تقنية وتعليمية في عالم البرمجة والتكنولوجيا</p>
            </div>
            <div class="row g-4">
                @forelse($latestPosts ?? [] as $post)
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel blog-card animate-on-scroll animate-delay-1">
                        <div class="blog-img-wrapper">
                            <img src="{{ $post->featured_image ? asset('storage/' . $post->featured_image) : asset('frontend2/assets/images/course-webdev.svg') }}" alt="{{ $post->title }}" width="400" height="180" loading="lazy">
                        </div>
                        <div class="blog-body">
                            <div class="blog-meta">
                                <span><i class="fas fa-calendar-alt"></i> {{ $post->published_at?->format('d F Y') }}</span>
                                @if($post->category)<span><i class="fas fa-tag"></i> {{ $post->category->name }}</span>@endif
                            </div>
                            <h5>{{ Str::limit($post->title, 50) }}</h5>
                            <p>{{ Str::limit(strip_tags($post->excerpt ?? $post->content ?? ''), 80) }}</p>
                            <a href="{{ route('frontend.blog.show', $post->slug) }}" class="read-more">اقرأ المزيد <i class="fas fa-arrow-left"></i></a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel blog-card animate-on-scroll animate-delay-1">
                        <div class="blog-img-wrapper">
                            <img src="{{ asset('frontend2/assets/images/course-webdev.svg') }}" alt="مقال تقني" width="400" height="180" loading="lazy">
                        </div>
                        <div class="blog-body">
                            <div class="blog-meta">
                                <span><i class="fas fa-calendar-alt"></i> 20 فبراير 2026</span>
                                <span><i class="fas fa-tag"></i> تطوير الويب</span>
                            </div>
                            <h5>أفضل 10 أدوات لمطوري الويب في 2026</h5>
                            <p>تعرف على أحدث الأدوات والتقنيات التي يجب على كل مطور ويب معرفتها...</p>
                            <a href="{{ route('frontend.blog.index') }}" class="read-more">اقرأ المزيد <i class="fas fa-arrow-left"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel blog-card animate-on-scroll animate-delay-2">
                        <div class="blog-img-wrapper">
                            <img src="{{ asset('frontend2/assets/images/course-python.svg') }}" alt="مقال بايثون" width="400" height="180" loading="lazy">
                        </div>
                        <div class="blog-body">
                            <div class="blog-meta">
                                <span><i class="fas fa-calendar-alt"></i> 15 فبراير 2026</span>
                                <span><i class="fas fa-tag"></i> بايثون</span>
                            </div>
                            <h5>كيف تبدأ في تعلم الذكاء الاصطناعي</h5>
                            <p>دليل شامل للمبتدئين في عالم الذكاء الاصطناعي وتعلم الآلة مع بايثون...</p>
                            <a href="{{ route('frontend.blog.index') }}" class="read-more">اقرأ المزيد <i class="fas fa-arrow-left"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel blog-card animate-on-scroll animate-delay-3">
                        <div class="blog-img-wrapper">
                            <img src="{{ asset('frontend2/assets/images/course-mobile.svg') }}" alt="مقال موبايل" width="400" height="180" loading="lazy">
                        </div>
                        <div class="blog-body">
                            <div class="blog-meta">
                                <span><i class="fas fa-calendar-alt"></i> 10 فبراير 2026</span>
                                <span><i class="fas fa-tag"></i> موبايل</span>
                            </div>
                            <h5>Flutter vs React Native: مقارنة شاملة</h5>
                            <p>مقارنة تفصيلية بين أشهر إطارين لتطوير تطبيقات الموبايل في 2026...</p>
                            <a href="{{ route('frontend.blog.index') }}" class="read-more">اقرأ المزيد <i class="fas fa-arrow-left"></i></a>
                        </div>
                    </div>
                </div>
                @endforelse
            </div>
            <div class="text-center mt-5 animate-on-scroll">
                <a href="{{ route('frontend.blog.index') }}" class="btn-primary-custom">
                    <i class="fas fa-newspaper"></i> عرض كل التدوينات
                </a>
            </div>
        </div>
    </section>

    <!-- ============ CLIENTS PREVIEW ============ -->
    <section class="section-padding" id="clients-preview" style="background: var(--clr-bg-secondary);">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <span class="section-badge">ثقة غالية</span>
                <h2>شركاؤنا والعملاء</h2>
                <p>شكراً لكل من وثق بي — تعرف على بعض الشركات والعملاء الذين تعاملت معهم</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel client-card animate-on-scroll">
                        <div class="client-card-logo">
                            <img src="{{ asset('frontend2/assets/images/logo.svg') }}" alt="اسم الشركة الأولى" width="80" height="80" loading="lazy">
                        </div>
                        <span class="client-card-type">شركة</span>
                        <h3 class="client-card-name">اسم الشركة الأولى</h3>
                        <p class="client-card-desc">شركة رائدة في مجالها، تعاملت معها بكل احترافية وشفافية. أشكرهم على الثقة والتعاون المثمر.</p>
                        <blockquote class="client-card-quote">"شريك موثوق يلتزم بالمواعيد والجودة."</blockquote>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel client-card animate-on-scroll">
                        <div class="client-card-logo">
                            <img src="{{ asset('frontend2/assets/images/logo.svg') }}" alt="عميل / مشروع ثانٍ" width="80" height="80" loading="lazy">
                        </div>
                        <span class="client-card-type">عميل</span>
                        <h3 class="client-card-name">عميل / مشروع ثانٍ</h3>
                        <p class="client-card-desc">عميل كريم كان واضحاً في المتطلبات ومتعاوناً طوال التنفيذ. أقدّر صبره وثقته.</p>
                        <blockquote class="client-card-quote">"تجربة سلسة ونتيجة تفوق التوقعات."</blockquote>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel client-card animate-on-scroll">
                        <div class="client-card-logo">
                            <img src="{{ asset('frontend2/assets/images/logo.svg') }}" alt="شركة تقنية" width="80" height="80" loading="lazy">
                        </div>
                        <span class="client-card-type">شركة</span>
                        <h3 class="client-card-name">شركة تقنية</h3>
                        <p class="client-card-desc">تعاون مميز في مشروع تطوير ويب وتدريب الفريق. فريقهم المحترم جعل العمل متعة.</p>
                        <blockquote class="client-card-quote">"احترافية عالية وتواصل ممتاز."</blockquote>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5 animate-on-scroll">
                <a href="{{ route('frontend.students.index') }}" class="btn-primary-custom">
                    <i class="fas fa-handshake"></i> تعرف على كل الشركات والعملاء
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
