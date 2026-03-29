<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="آراء وتجارب طلاب ياسين جوخدار - ماذا يقول طلاب الدورات التدريبية عن التجربة والنتائج.">
    <title>آراء الطلاب | ياسين جوخدار</title>
    <link rel="icon" type="image/svg+xml" href="assets/images/favicon.svg">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <div id="pageLoader" aria-hidden="true">
        <div class="pageLoader-inner">
            <div class="pageLoader-logo"><img src="assets/images/logo.svg" alt="" width="72" height="72"></div>
            <div class="pageLoader-spinner"></div>
            <p class="pageLoader-text">جاري التحميل...</p>
        </div>
    </div>

    <div class="bg-orbs">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <!-- ============ TOP BAR ============ -->
    <div class="top-bar" id="topBar">
        <div class="container">
            <div class="top-bar-inner">
                <div class="top-bar-contact">
                    <a href="mailto:info@yasinjokhadar.net" class="top-bar-item"><i class="fas fa-envelope"></i><span class="top-bar-text">info@yasinjokhadar.net</span></a>
                    <a href="tel:+963XXXXXXXXX" class="top-bar-item"><i class="fas fa-phone-alt"></i><span class="top-bar-text">+963 XXX XXX XXX</span></a>
                </div>
                <div class="top-bar-links">
                    <a href="courses.html" class="top-bar-item"><i class="fas fa-graduation-cap"></i><span class="top-bar-text">الكورسات</span></a>
                    <a href="videos.html" class="top-bar-item"><i class="fas fa-play-circle"></i><span class="top-bar-text">الفيديوهات</span></a>
                    <a href="contact.html" class="top-bar-item"><i class="fas fa-paper-plane"></i><span class="top-bar-text">تواصل معنا</span></a>
                </div>
            </div>
        </div>
    </div>

    <!-- ============ NAVBAR ============ -->
    <nav class="navbar navbar-expand-lg main-navbar" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <img src="assets/images/logo.svg" alt="ياسين جوخدار" width="45" height="45">
                <span>ياسين جوخدار</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="bar"></span><span class="bar"></span><span class="bar"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="index.html">الرئيسية</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.html">حول المدرب</a></li>
                    <li class="nav-item"><a class="nav-link" href="courses.html">الكورسات</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('frontend.home') }}#services">الخدمات</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.html#gallery">معرض الصور</a></li>
                    <li class="nav-item"><a class="nav-link" href="blog.html">المدونة</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.html">تواصل معنا</a></li>
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
                    <button class="theme-toggle" id="themeToggle" title="تبديل الوضع" aria-label="تبديل الوضع الليلي/النهاري"><i class="fas fa-sun"></i></button>
                </div>
            </div>
        </div>
    </nav>

    <!-- ============ PAGE BANNER (آراء الطلاب) ============ -->
    <section class="page-banner page-banner-testimonials">
        <div class="page-banner-overlay"></div>
        <div class="container position-relative">
            <div class="page-banner-content animate-on-scroll">
                <div class="page-banner-icon"><i class="fas fa-quote-right"></i></div>
                <h1 class="page-banner-title">آراء <span>الطلاب</span></h1>
                <p class="page-banner-desc">تجارب حقيقية وتقييمات من طلاب استفادوا من دوراتنا التدريبية</p>
                <nav class="page-banner-breadcrumb" aria-label="breadcrumb">
                    <a href="index.html">الرئيسية</a>
                    <span class="page-banner-sep">/</span>
                    <span>آراء الطلاب</span>
                </nav>
            </div>
        </div>
        <div class="page-banner-shape"></div>
    </section>

    <!-- ============ TESTIMONIALS SECTION ============ -->
    <section class="section-padding">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <span class="section-badge">تجارب حقيقية</span>
                <h2>ماذا يقول طلابنا</h2>
                <p>آراء وتجارب بعض الطلاب الذين استفادوا من دوراتنا التدريبية</p>
            </div>
            <div class="row g-4">
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
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel testimonial-card animate-on-scroll animate-delay-1">
                        <div class="stars">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <p class="quote-text">"دورة Node.js فتحت لي آفاقاً جديدة. المشاريع العملية ساعدتني في الحصول على أول وظيفة كمطور باكند. شكراً ياسين!"</p>
                        <div class="student-info">
                            <div>
                                <div class="student-name">محمد خالد</div>
                                <div class="student-role">مطور Backend - مصر</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel testimonial-card animate-on-scroll animate-delay-2">
                        <div class="stars">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <p class="quote-text">"المحتوى منظم جداً والتمارين متنوعة. تحولت من مبتدئ إلى قادر على بناء مواقع كاملة بفضل دورة تطوير الويب الشاملة."</p>
                        <div class="student-info">
                            <div>
                                <div class="student-name">نور الدين</div>
                                <div class="student-role">مطور ويب - تونس</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel testimonial-card animate-on-scroll animate-delay-3">
                        <div class="stars">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                        </div>
                        <p class="quote-text">"أفضل استثمار قمت به في تعلم البرمجة. المدرب يرد على الاستفسارات بسرعة ويشرح بأمثلة من الواقع. أنصح بشدة."</p>
                        <div class="student-info">
                            <div>
                                <div class="student-name">لينا أحمد</div>
                                <div class="student-role">مطورة - لبنان</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5 animate-on-scroll">
                <a href="courses.html" class="btn-primary-custom">
                    <i class="fas fa-graduation-cap"></i> تصفّح الكورسات
                </a>
            </div>
        </div>
    </section>

    <!-- ============ FOOTER ============ -->
    <footer class="main-footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <h5><img src="assets/images/logo.svg" alt="لوغو" style="width:35px; height:35px; border-radius:50%; margin-left:8px; border:2px solid var(--clr-primary);" width="35" height="35">ياسين جوخدار</h5>
                    <p>مدرب ومطور برمجيات شغوف بالتعليم ونقل المعرفة. أقدم دورات تدريبية عملية في مختلف مجالات البرمجة وتطوير الويب والموبايل.</p>
                    <div class="footer-social">
                        <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" title="فيسبوك" aria-label="فيسبوك"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" title="يوتيوب" aria-label="يوتيوب"><i class="fab fa-youtube"></i></a>
                        <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" title="انستغرام" aria-label="انستغرام"><i class="fab fa-instagram"></i></a>
                        <a href="https://linkedin.com" target="_blank" rel="noopener noreferrer" title="لينكد إن" aria-label="لينكد إن"><i class="fab fa-linkedin-in"></i></a>
                        <a href="https://github.com" target="_blank" rel="noopener noreferrer" title="جيت هاب" aria-label="جيت هاب"><i class="fab fa-github"></i></a>
                        <a href="https://t.me" target="_blank" rel="noopener noreferrer" title="تليجرام" aria-label="تليجرام"><i class="fab fa-telegram-plane"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h5>روابط سريعة</h5>
                    <ul class="footer-links">
                        <li><a href="index.html"><i class="fas fa-chevron-left"></i> الرئيسية</a></li>
                        <li><a href="about.html"><i class="fas fa-chevron-left"></i> حول المدرب</a></li>
                        <li><a href="courses.html"><i class="fas fa-chevron-left"></i> الكورسات</a></li>
                        <li><a href="videos.html"><i class="fas fa-chevron-left"></i> الفيديوهات</a></li>
                        <li><a href="testimonials.html"><i class="fas fa-chevron-left"></i> آراء الطلاب</a></li>
                        <li><a href="contact.html"><i class="fas fa-chevron-left"></i> تواصل معنا</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5>أحدث الكورسات</h5>
                    <ul class="footer-links">
                        <li><a href="courses.html"><i class="fas fa-chevron-left"></i> تطوير الويب الشامل</a></li>
                        <li><a href="courses.html"><i class="fas fa-chevron-left"></i> بايثون للمبتدئين</a></li>
                        <li><a href="courses.html"><i class="fas fa-chevron-left"></i> Flutter للموبايل</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5>تواصل معنا</h5>
                    <ul class="footer-links">
                        <li><i class="fas fa-envelope" style="color: var(--clr-primary); margin-left:8px;"></i> info@yasinjokhadar.net</li>
                        <li><i class="fas fa-map-marker-alt" style="color: var(--clr-primary); margin-left:8px;"></i> سوريا</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                جميع الحقوق محفوظة &copy; 2026 <span>ياسين جوخدار</span> | صُنع بـ ❤️
            </div>
        </div>
    </footer>

    <button class="back-to-top" id="backToTop" aria-label="العودة للأعلى"><i class="fas fa-chevron-up"></i></button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>
