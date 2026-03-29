<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="مشاريعي البرمجية — معرض مشاريع ياسين جوخدار في تطوير الويب، تطبيقات الموبايل والأنظمة البرمجية مع روابط مباشرة ومصدر الكود.">
    <title>المشاريع البرمجية | ياسين جوخدار</title>
    <link rel="canonical" href="https://yasinjokhadar.net/projects.html">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://yasinjokhadar.net/projects.html">
    <meta property="og:title" content="المشاريع البرمجية | ياسين جوخدار">
    <meta property="og:description" content="معرض مشاريعي البرمجية في تطوير الويب والموبايل مع روابط المشاريع ومصدر الكود.">
    <meta property="og:image" content="https://yasinjokhadar.net/assets/images/logo.svg">
    <meta property="og:locale" content="ar_SY">
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
                    <a href="projects.html" class="top-bar-item"><i class="fas fa-folder-open"></i><span class="top-bar-text">المشاريع</span></a>
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
    <section class="page-banner page-banner-projects">
        <div class="page-banner-overlay"></div>
        <div class="container position-relative">
            <div class="page-banner-content animate-on-scroll">
                <div class="page-banner-icon"><i class="fas fa-folder-open"></i></div>
                <h1 class="page-banner-title">مشاريعي <span>البرمجية</span></h1>
                <p class="page-banner-desc">معرض مشاريع تطوير الويب، تطبيقات الموبايل والأنظمة — مع روابط مباشرة ومصدر الكود</p>
                <nav class="page-banner-breadcrumb" aria-label="breadcrumb">
                    <a href="index.html">الرئيسية</a>
                    <span class="page-banner-sep">/</span>
                    <span>المشاريع</span>
                </nav>
            </div>
        </div>
        <div class="page-banner-shape"></div>
    </section>

    <!-- ============ PROJECTS LIST ============ -->
    <section class="section-padding">
        <div class="container">
            <!-- Filter -->
            <div class="projects-filter animate-on-scroll">
                <button class="filter-btn active" data-filter="all">الكل</button>
                <button class="filter-btn" data-filter="web">تطوير الويب</button>
                <button class="filter-btn" data-filter="mobile">الموبايل</button>
                <button class="filter-btn" data-filter="other">أخرى</button>
            </div>

            <div class="row g-4">
                <!-- Project 1 -->
                <div class="col-lg-4 col-md-6 project-filter-item" data-category="web">
                    <article class="glass-panel project-card animate-on-scroll">
                        <div class="project-card-thumb">
                            <div class="project-card-thumb-inner">
                                <i class="fas fa-globe"></i>
                            </div>
                            <span class="project-card-badge">ويب</span>
                        </div>
                        <div class="project-card-body">
                            <h3 class="project-card-title">اسم المشروع الأول</h3>
                            <p class="project-card-desc">وصف مختصر للمشروع وما يقدمه من ميزات أو حلول. يمكنك استبداله بمشروعك الفعلي وروابطه.</p>
                            <div class="project-card-tags">
                                <span>React</span>
                                <span>Node.js</span>
                                <span>MongoDB</span>
                            </div>
                        </div>
                        <div class="project-card-actions">
                            <a href="#" target="_blank" rel="noopener noreferrer" class="btn-project btn-project-primary">
                                <i class="fas fa-external-link-alt"></i> عرض المشروع
                            </a>
                            <a href="#" target="_blank" rel="noopener noreferrer" class="btn-project btn-project-outline">
                                <i class="fab fa-github"></i> الكود
                            </a>
                        </div>
                    </article>
                </div>

                <!-- Project 2 -->
                <div class="col-lg-4 col-md-6 project-filter-item" data-category="web">
                    <article class="glass-panel project-card animate-on-scroll">
                        <div class="project-card-thumb">
                            <div class="project-card-thumb-inner">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <span class="project-card-badge">ويب</span>
                        </div>
                        <div class="project-card-body">
                            <h3 class="project-card-title">متجر إلكتروني</h3>
                            <p class="project-card-desc">منصة بيع أونلاين مع سلة مشتريات، دفع وإدارة منتجات. ضع هنا رابط المشروع الحي ورابط المستودع.</p>
                            <div class="project-card-tags">
                                <span>Vue.js</span>
                                <span>Laravel</span>
                                <span>MySQL</span>
                            </div>
                        </div>
                        <div class="project-card-actions">
                            <a href="#" target="_blank" rel="noopener noreferrer" class="btn-project btn-project-primary">
                                <i class="fas fa-external-link-alt"></i> عرض المشروع
                            </a>
                            <a href="#" target="_blank" rel="noopener noreferrer" class="btn-project btn-project-outline">
                                <i class="fab fa-github"></i> الكود
                            </a>
                        </div>
                    </article>
                </div>

                <!-- Project 3 -->
                <div class="col-lg-4 col-md-6 project-filter-item" data-category="mobile">
                    <article class="glass-panel project-card animate-on-scroll">
                        <div class="project-card-thumb">
                            <div class="project-card-thumb-inner">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <span class="project-card-badge">موبايل</span>
                        </div>
                        <div class="project-card-body">
                            <h3 class="project-card-title">تطبيق جوال</h3>
                            <p class="project-card-desc">تطبيق أندرويد و iOS مبني بـ Flutter. أضف الرابط المباشر للتطبيق أو Demo ورابط GitHub.</p>
                            <div class="project-card-tags">
                                <span>Flutter</span>
                                <span>Dart</span>
                                <span>Firebase</span>
                            </div>
                        </div>
                        <div class="project-card-actions">
                            <a href="#" target="_blank" rel="noopener noreferrer" class="btn-project btn-project-primary">
                                <i class="fas fa-external-link-alt"></i> عرض المشروع
                            </a>
                            <a href="#" target="_blank" rel="noopener noreferrer" class="btn-project btn-project-outline">
                                <i class="fab fa-github"></i> الكود
                            </a>
                        </div>
                    </article>
                </div>

                <!-- Project 4 -->
                <div class="col-lg-4 col-md-6 project-filter-item" data-category="web">
                    <article class="glass-panel project-card animate-on-scroll">
                        <div class="project-card-thumb">
                            <div class="project-card-thumb-inner">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <span class="project-card-badge">ويب</span>
                        </div>
                        <div class="project-card-body">
                            <h3 class="project-card-title">لوحة إدارة (Dashboard)</h3>
                            <p class="project-card-desc">واجهة إدارة محتوى وإحصائيات مع جداول ورسوم بيانية. رابط المعاينة ورابط المستودع أدناه.</p>
                            <div class="project-card-tags">
                                <span>React</span>
                                <span>TypeScript</span>
                                <span>REST API</span>
                            </div>
                        </div>
                        <div class="project-card-actions">
                            <a href="#" target="_blank" rel="noopener noreferrer" class="btn-project btn-project-primary">
                                <i class="fas fa-external-link-alt"></i> عرض المشروع
                            </a>
                            <a href="#" target="_blank" rel="noopener noreferrer" class="btn-project btn-project-outline">
                                <i class="fab fa-github"></i> الكود
                            </a>
                        </div>
                    </article>
                </div>

                <!-- Project 5 -->
                <div class="col-lg-4 col-md-6 project-filter-item" data-category="other">
                    <article class="glass-panel project-card animate-on-scroll">
                        <div class="project-card-thumb">
                            <div class="project-card-thumb-inner">
                                <i class="fas fa-robot"></i>
                            </div>
                            <span class="project-card-badge">أخرى</span>
                        </div>
                        <div class="project-card-body">
                            <h3 class="project-card-title">أتمتة أو أداة مساعدة</h3>
                            <p class="project-card-desc">سكربت أو أداة سطر أوامر أو مشروع جانبي. ضع الوصف ورابط المشروع أو المستودع.</p>
                            <div class="project-card-tags">
                                <span>Python</span>
                                <span>CLI</span>
                            </div>
                        </div>
                        <div class="project-card-actions">
                            <a href="#" target="_blank" rel="noopener noreferrer" class="btn-project btn-project-primary">
                                <i class="fas fa-external-link-alt"></i> عرض المشروع
                            </a>
                            <a href="#" target="_blank" rel="noopener noreferrer" class="btn-project btn-project-outline">
                                <i class="fab fa-github"></i> الكود
                            </a>
                        </div>
                    </article>
                </div>

                <!-- Project 6 -->
                <div class="col-lg-4 col-md-6 project-filter-item" data-category="mobile">
                    <article class="glass-panel project-card animate-on-scroll">
                        <div class="project-card-thumb">
                            <div class="project-card-thumb-inner">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <span class="project-card-badge">موبايل</span>
                        </div>
                        <div class="project-card-body">
                            <h3 class="project-card-title">تطبيق مالية أو خدمة</h3>
                            <p class="project-card-desc">تطبيق لتتبع المصروفات أو حجز خدمات. أضف الرابط الحي ورابط الكود عند التحديث.</p>
                            <div class="project-card-tags">
                                <span>Flutter</span>
                                <span>API</span>
                            </div>
                        </div>
                        <div class="project-card-actions">
                            <a href="#" target="_blank" rel="noopener noreferrer" class="btn-project btn-project-primary">
                                <i class="fas fa-external-link-alt"></i> عرض المشروع
                            </a>
                            <a href="#" target="_blank" rel="noopener noreferrer" class="btn-project btn-project-outline">
                                <i class="fab fa-github"></i> الكود
                            </a>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ CTA ============ -->
    <section class="cta-section">
        <div class="container animate-on-scroll">
            <h2>هل تريد تنفيذ مشروع مماثل؟</h2>
            <p>تواصل معنا لمناقشة فكرتك والحصول على عرض tailored لاحتياجاتك</p>
            <a href="contact.html" class="btn-light-custom">
                <i class="fas fa-paper-plane"></i> تواصل معنا
            </a>
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
                        <li><a href="blog.html"><i class="fas fa-chevron-left"></i> المدونة</a></li>
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
    <script>
        // Filter projects (same pattern as courses)
        document.querySelectorAll('.projects-filter .filter-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var filter = this.getAttribute('data-filter');
                document.querySelectorAll('.projects-filter .filter-btn').forEach(function(b) { b.classList.remove('active'); });
                this.classList.add('active');
                document.querySelectorAll('.project-filter-item').forEach(function(item) {
                    var cat = item.getAttribute('data-category');
                    item.style.display = (filter === 'all' || cat === filter) ? '' : 'none';
                });
            });
        });
    </script>
</body>

</html>
