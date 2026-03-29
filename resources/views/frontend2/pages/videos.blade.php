<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="فيديوهات ياسين جوخدار التعليمية - مقاطع من القناة في تطوير الويب، بايثون، Flutter والمزيد.">
    <title>فيديوهاتي | ياسين جوخدار</title>
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

    <!-- ============ PAGE BANNER (نفس المدونة) ============ -->
    <section class="page-banner page-banner-blog">
        <div class="page-banner-overlay"></div>
        <div class="container position-relative">
            <div class="page-banner-content animate-on-scroll">
                <div class="page-banner-icon"><i class="fas fa-play-circle"></i></div>
                <h1 class="page-banner-title">فيديوهاتي <span>التعليمية</span></h1>
                <p class="page-banner-desc">مقاطع فيديو تعليمية وعملية من قناتي على يوتيوب في تطوير الويب، البرمجة، وتطبيقات الموبايل</p>
                <nav class="page-banner-breadcrumb" aria-label="breadcrumb">
                    <a href="index.html">الرئيسية</a>
                    <span class="page-banner-sep">/</span>
                    <span>الفيديوهات</span>
                </nav>
            </div>
        </div>
        <div class="page-banner-shape"></div>
    </section>

    <!-- ============ VIDEOS SECTION ============ -->
    <section class="section-padding">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <span class="section-badge">القناة</span>
                <h2>مقاطع فيديو تعليمية وعملية</h2>
                <p>فيديوهات من قناتنا على يوتيوب في تطوير الويب، البرمجة، وتطبيقات الموبايل</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel video-card animate-on-scroll animate-delay-1">
                        <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" class="video-wrapper d-block text-decoration-none">
                            <img src="assets/images/course-webdev.svg" alt="أساسيات تطوير الويب" width="400" height="200" loading="lazy">
                            <div class="play-btn"><i class="fas fa-play-circle"></i></div>
                        </a>
                        <div class="video-body">
                            <h6>أساسيات تطوير الويب</h6>
                            <span><i class="fas fa-eye"></i> 15,000 مشاهدة</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel video-card animate-on-scroll animate-delay-2">
                        <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" class="video-wrapper d-block text-decoration-none">
                            <img src="assets/images/course-python.svg" alt="مقدمة في لغة بايثون" width="400" height="200" loading="lazy">
                            <div class="play-btn"><i class="fas fa-play-circle"></i></div>
                        </a>
                        <div class="video-body">
                            <h6>مقدمة في لغة بايثون</h6>
                            <span><i class="fas fa-eye"></i> 12,000 مشاهدة</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel video-card animate-on-scroll animate-delay-3">
                        <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" class="video-wrapper d-block text-decoration-none">
                            <img src="assets/images/course-mobile.svg" alt="بناء تطبيق Flutter" width="400" height="200" loading="lazy">
                            <div class="play-btn"><i class="fas fa-play-circle"></i></div>
                        </a>
                        <div class="video-body">
                            <h6>بناء تطبيق متكامل بـ Flutter</h6>
                            <span><i class="fas fa-eye"></i> 8,500 مشاهدة</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel video-card animate-on-scroll animate-delay-1">
                        <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" class="video-wrapper d-block text-decoration-none">
                            <img src="assets/images/course-webdev.svg" alt="React للمبتدئين" width="400" height="200" loading="lazy">
                            <div class="play-btn"><i class="fas fa-play-circle"></i></div>
                        </a>
                        <div class="video-body">
                            <h6>React.js للمبتدئين</h6>
                            <span><i class="fas fa-eye"></i> 9,200 مشاهدة</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel video-card animate-on-scroll animate-delay-2">
                        <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" class="video-wrapper d-block text-decoration-none">
                            <img src="assets/images/course-python.svg" alt="الذكاء الاصطناعي" width="400" height="200" loading="lazy">
                            <div class="play-btn"><i class="fas fa-play-circle"></i></div>
                        </a>
                        <div class="video-body">
                            <h6>مقدمة في الذكاء الاصطناعي</h6>
                            <span><i class="fas fa-eye"></i> 11,000 مشاهدة</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel video-card animate-on-scroll animate-delay-3">
                        <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" class="video-wrapper d-block text-decoration-none">
                            <img src="assets/images/course-mobile.svg" alt="Node.js و Express" width="400" height="200" loading="lazy">
                            <div class="play-btn"><i class="fas fa-play-circle"></i></div>
                        </a>
                        <div class="video-body">
                            <h6>Node.js و Express من الصفر</h6>
                            <span><i class="fas fa-eye"></i> 7,800 مشاهدة</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5 animate-on-scroll">
                <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" class="btn-primary-custom">
                    <i class="fab fa-youtube"></i> اشترك في القناة
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
