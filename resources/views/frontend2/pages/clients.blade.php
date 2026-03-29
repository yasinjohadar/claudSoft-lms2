<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="الشركات والعملاء — تعرف على من تعامل معهم ياسين جوخدار من شركات وعملاء مع عبارات شكر وتقدير.">
    <title>الشركات والعملاء | ياسين جوخدار</title>
    <link rel="icon" type="image/png" href="{{ asset('frontend2/assets/images/logo.png') }}">
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

    <!-- ============ NAVBAR (بدون رابط للصفحة الحالية) ============ -->
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
                    <li class="nav-item"><a class="nav-link" href="projects.html">المشاريع</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('frontend.home') }}#services">الخدمات</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.html#gallery">معرض الصور</a></li>
                    <li class="nav-item"><a class="nav-link" href="blog.html">المدونة</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.html">تواصل معنا</a></li>
                </ul>
                <div class="d-flex align-items-center gap-3">
                    <div class="nav-social">
                        <a href="#" target="_blank" rel="noopener noreferrer" title="فيسبوك" aria-label="فيسبوك"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" target="_blank" rel="noopener noreferrer" title="يوتيوب" aria-label="يوتيوب"><i class="fab fa-youtube"></i></a>
                        <a href="#" target="_blank" rel="noopener noreferrer" title="انستغرام" aria-label="انستغرام"><i class="fab fa-instagram"></i></a>
                        <a href="#" target="_blank" rel="noopener noreferrer" title="لينكد إن" aria-label="لينكد إن"><i class="fab fa-linkedin-in"></i></a>
                        <a href="https://github.com" target="_blank" rel="noopener noreferrer" title="جيت هاب" aria-label="جيت هاب"><i class="fab fa-github"></i></a>
                        <a href="#" target="_blank" rel="noopener noreferrer" title="تليجرام" aria-label="تليجرام"><i class="fab fa-telegram-plane"></i></a>
                    </div>
                    <button class="theme-toggle" id="themeToggle" title="تبديل الوضع" aria-label="تبديل الوضع الليلي/النهاري"><i class="fas fa-sun"></i></button>
                </div>
            </div>
        </div>
    </nav>

    <!-- ============ PAGE BANNER ============ -->
    <section class="page-banner page-banner-clients">
        <div class="page-banner-overlay"></div>
        <div class="container position-relative">
            <div class="page-banner-content animate-on-scroll">
                <div class="page-banner-icon"><i class="fas fa-handshake"></i></div>
                <h1 class="page-banner-title">الشركات <span>والعملاء</span></h1>
                <p class="page-banner-desc">شكراً لكل من وثق بي — شركات وعملاء كرام تعاملت معهم بامتنان واحترام</p>
                <nav class="page-banner-breadcrumb" aria-label="breadcrumb">
                    <a href="index.html">الرئيسية</a>
                    <span class="page-banner-sep">/</span>
                    <span>الشركات والعملاء</span>
                </nav>
            </div>
        </div>
        <div class="page-banner-shape"></div>
    </section>

    <!-- ============ INTRO ============ -->
    <section class="section-padding">
        <div class="container">
            <div class="clients-intro text-center mx-auto animate-on-scroll" style="max-width: 720px;">
                <span class="section-badge">امتنان</span>
                <h2 class="mb-3">ثقة غالية نقدّرها</h2>
                <p class="text-secondary mb-0">كل شركة وكل عميل تعاملت معه كان جزءاً من رحلتي — أقدّر الثقة والتعاون المثمر، وأضع هنا كلمة شكر وعرفان لهم.</p>
            </div>
        </div>
    </section>

    <!-- ============ CLIENTS GRID ============ -->
    <section class="section-padding" style="background: var(--clr-bg-secondary);">
        <div class="container">
            <div class="row g-4">
                <!-- Client 1 -->
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel client-card animate-on-scroll">
                        <div class="client-card-logo">
                            <img src="assets/images/logo.svg" alt="اسم الشركة الأولى" width="80" height="80" loading="lazy">
                        </div>
                        <span class="client-card-type">شركة</span>
                        <h3 class="client-card-name">اسم الشركة الأولى</h3>
                        <p class="client-card-desc">شركة رائدة في مجالها، تعاملت معها بكل احترافية وشفافية. أشكرهم على الثقة والتعاون المثمر في تنفيذ المشروع.</p>
                        <blockquote class="client-card-quote">"شريك موثوق يلتزم بالمواعيد والجودة."</blockquote>
                    </div>
                </div>
                <!-- Client 2 -->
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel client-card animate-on-scroll">
                        <div class="client-card-logo">
                            <img src="assets/images/logo.svg" alt="عميل / مشروع ثانٍ" width="80" height="80" loading="lazy">
                        </div>
                        <span class="client-card-type">عميل</span>
                        <h3 class="client-card-name">عميل / مشروع ثانٍ</h3>
                        <p class="client-card-desc">عميل كريم كان واضحاً في المتطلبات ومتعاوناً طوال التنفيذ. أقدّر صبره وثقته وأتمنى له التوفيق دوماً.</p>
                        <blockquote class="client-card-quote">"تجربة سلسة ونتيجة تفوق التوقعات."</blockquote>
                    </div>
                </div>
                <!-- Client 3 -->
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel client-card animate-on-scroll">
                        <div class="client-card-logo">
                            <img src="assets/images/logo.svg" alt="شركة تقنية" width="80" height="80" loading="lazy">
                        </div>
                        <span class="client-card-type">شركة</span>
                        <h3 class="client-card-name">شركة تقنية</h3>
                        <p class="client-card-desc">تعاون مميز في مشروع تطوير ويب وتدريب الفريق. فريقهم المحترم جعل العمل متعة وحققنا أهدافاً مشتركة.</p>
                        <blockquote class="client-card-quote">"احترافية عالية وتواصل ممتاز."</blockquote>
                    </div>
                </div>
                <!-- Client 4 -->
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel client-card animate-on-scroll">
                        <div class="client-card-logo">
                            <img src="assets/images/logo.svg" alt="متجر / مشروع تجاري" width="80" height="80" loading="lazy">
                        </div>
                        <span class="client-card-type">عميل</span>
                        <h3 class="client-card-name">متجر / مشروع تجاري</h3>
                        <p class="client-card-desc">مشروع متجر إلكتروني نُفّذ بالكامل مع الدعم والتدريب. أشكر صاحب المشروع على حسن الاستقبال والتقييم الإيجابي.</p>
                        <blockquote class="client-card-quote">"التزام بالوقت وجودة في التنفيذ."</blockquote>
                    </div>
                </div>
                <!-- Client 5 -->
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel client-card animate-on-scroll">
                        <div class="client-card-logo">
                            <img src="assets/images/logo.svg" alt="مركز أو أكاديمية" width="80" height="80" loading="lazy">
                        </div>
                        <span class="client-card-type">جهة تدريب</span>
                        <h3 class="client-card-name">مركز أو أكاديمية</h3>
                        <p class="client-card-desc">شراكة تدريبية مع جهة تعليمية. تقديري الكبير لإدارة المركز وطلابهم على الجدية والتفاعل خلال الدورات.</p>
                        <blockquote class="client-card-quote">"مدرب متميز ومحتوى عملي قيّم."</blockquote>
                    </div>
                </div>
                <!-- Client 6 -->
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel client-card animate-on-scroll">
                        <div class="client-card-logo">
                            <img src="assets/images/logo.svg" alt="عميل / مشروع تطبيق" width="80" height="80" loading="lazy">
                        </div>
                        <span class="client-card-type">عميل</span>
                        <h3 class="client-card-name">عميل / مشروع تطبيق</h3>
                        <p class="client-card-desc">مشروع تطبيق جوال من الفكرة حتى النشر. أشكر العميل على الثقة والمرونة في اتخاذ القرارات المشتركة.</p>
                        <blockquote class="client-card-quote">"تعاون رائع ونتيجة نفتخر بها."</blockquote>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ CLOSING MESSAGE ============ -->
    <section class="section-padding">
        <div class="container">
            <div class="glass-panel clients-closing text-center py-5 px-4 animate-on-scroll">
                <i class="fas fa-heart mb-3" style="font-size: 2.5rem; color: var(--clr-primary);"></i>
                <h3 class="mb-2">شكراً لكم</h3>
                <p class="text-secondary mb-0 mx-auto" style="max-width: 560px;">كل اسم في هذه الصفحة يمثّل ثقة غالية وذكرى تعاون نقدّرها. نتمنى لكم التوفيق ونبقى في خدمتكم.</p>
            </div>
        </div>
    </section>

    <!-- ============ FOOTER ============ -->
    <footer class="main-footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <h5><img src="assets/images/logo.svg" alt="لوغو" style="width:35px;height:35px;border-radius:50%;margin-left:8px;border:2px solid var(--clr-primary);" width="35" height="35"> ياسين جوخدار</h5>
                    <p>مدرب ومطور برمجيات شغوف بالتعليم ونقل المعرفة.</p>
                    <div class="footer-social">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="https://github.com" target="_blank" rel="noopener noreferrer"><i class="fab fa-github"></i></a>
                        <a href="#"><i class="fab fa-telegram-plane"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h5>روابط سريعة</h5>
                    <ul class="footer-links">
                        <li><a href="index.html"><i class="fas fa-chevron-left"></i> الرئيسية</a></li>
                        <li><a href="about.html"><i class="fas fa-chevron-left"></i> حول المدرب</a></li>
                        <li><a href="courses.html"><i class="fas fa-chevron-left"></i> الكورسات</a></li>
                        <li><a href="projects.html"><i class="fas fa-chevron-left"></i> المشاريع</a></li>
                        <li><a href="videos.html"><i class="fas fa-chevron-left"></i> الفيديوهات</a></li>
                        <li><a href="contact.html"><i class="fas fa-chevron-left"></i> تواصل معنا</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5>أحدث الكورسات</h5>
                    <ul class="footer-links">
                        <li><a href="course-detail.html"><i class="fas fa-chevron-left"></i> تطوير الويب الشامل</a></li>
                        <li><a href="#"><i class="fas fa-chevron-left"></i> بايثون للمبتدئين</a></li>
                        <li><a href="#"><i class="fas fa-chevron-left"></i> Flutter للموبايل</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5>تواصل معنا</h5>
                    <ul class="footer-links">
                        <li><i class="fas fa-envelope" style="color:var(--clr-primary);margin-left:8px;"></i> info@yasinjokhadar.net</li>
                        <li><i class="fas fa-phone" style="color:var(--clr-primary);margin-left:8px;"></i> +963 XXX XXX XXX</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">جميع الحقوق محفوظة &copy; 2026 <span>ياسين جوخدار</span> | صُنع بـ ❤️</div>
        </div>
    </footer>

    <button class="back-to-top" id="backToTop"><i class="fas fa-chevron-up"></i></button>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>
