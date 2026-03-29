<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="دليلك الشامل لتعلم تطوير الويب في 2026 - خارطة طريق من البداية للاحتراف مع أفضل الموارد والتقنيات.">
    <title>دليلك الشامل لتعلم تطوير الويب في 2026 | ياسين جوخدار</title>
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

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg main-navbar" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand" href="index.html"><img src="assets/images/logo.svg" alt="ياسين جوخدار" width="45" height="45"><span>ياسين
                    جوخدار</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span
                    class="bar"></span><span class="bar"></span><span class="bar"></span></button>
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
                        <a href="#" target="_blank" rel="noopener noreferrer" title="فيسبوك" aria-label="فيسبوك"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" target="_blank" rel="noopener noreferrer" title="يوتيوب" aria-label="يوتيوب"><i class="fab fa-youtube"></i></a>
                        <a href="#" target="_blank" rel="noopener noreferrer" title="انستغرام" aria-label="انستغرام"><i class="fab fa-instagram"></i></a>
                        <a href="#" target="_blank" rel="noopener noreferrer" title="لينكد إن" aria-label="لينكد إن"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" target="_blank" rel="noopener noreferrer" title="جيت هاب" aria-label="جيت هاب"><i class="fab fa-github"></i></a>
                        <a href="#" target="_blank" rel="noopener noreferrer" title="تليجرام" aria-label="تليجرام"><i class="fab fa-telegram-plane"></i></a>
                    </div>
                    <button class="theme-toggle" id="themeToggle" title="تبديل الوضع" aria-label="تبديل الوضع الليلي/النهاري"><i class="fas fa-sun"></i></button>
                </div>
            </div>
        </div>
    </nav>

    <!-- BLOG POST HERO IMAGE -->
    <section class="blog-detail-hero">
        <div class="blog-detail-hero-img">
            <img src="assets/images/course-webdev.svg" alt="دليلك الشامل لتعلم تطوير الويب" width="1200" height="400" loading="eager">
            <div class="blog-detail-hero-overlay"></div>
        </div>
    </section>

    <!-- BLOG POST CONTENT -->
    <section class="section-padding" style="padding-top: 0; margin-top: -80px; position: relative; z-index: 10;">
        <div class="container">
            <div class="row g-4">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <div class="glass-panel blog-detail-content animate-on-scroll">
                        <!-- Breadcrumb -->
                        <div class="breadcrumb-custom" style="justify-content: flex-start; margin-bottom: 20px;">
                            <a href="index.html">الرئيسية</a><span>/</span><a
                                href="blog.html">المدونة</a><span>/</span><span>تطوير الويب</span>
                        </div>

                        <!-- Category & Date -->
                        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:15px;">
                            <span class="bd-category"><i class="fas fa-folder"></i> تطوير الويب</span>
                            <span class="bd-date"><i class="fas fa-calendar-alt"></i> 25 فبراير 2026</span>
                            <span class="bd-date"><i class="fas fa-clock"></i> 12 دقيقة قراءة</span>
                        </div>

                        <!-- Title -->
                        <h1 class="bd-title">دليلك الشامل لتعلم تطوير الويب في 2026: خارطة طريق من البداية للاحتراف</h1>

                        <!-- Author & Stats -->
                        <div class="bd-author-bar">
                            <div class="bd-author-info">
                                <img src="assets/images/logo.svg" alt="ياسين جوخدار" width="45" height="45" loading="lazy">
                                <div>
                                    <strong>ياسين جوخدار</strong>
                                    <span>مطور ويب ومدرب تقني</span>
                                </div>
                            </div>
                            <div class="bd-post-stats">
                                <span><i class="fas fa-eye"></i> 3,240</span>
                                <span><i class="fas fa-comments"></i> 28</span>
                                <span><i class="fas fa-heart"></i> 156</span>
                            </div>
                        </div>

                        <!-- Article Body -->
                        <div class="bd-article">
                            <p class="bd-intro">
                                عالم تطوير الويب يتطور بسرعة هائلة، ومع كثرة التقنيات والأدوات المتاحة، قد يشعر المبتدئ
                                بالحيرة من أين يبدأ. في هذا المقال الشامل، سأشاركك خارطة طريق واضحة ومحدثة لعام 2026
                                تأخذك من الصفر إلى مستوى احترافي في تطوير الويب.
                            </p>

                            <h2><i class="fas fa-flag" style="color:var(--clr-primary); margin-left:8px;"></i> المرحلة
                                الأولى: الأساسيات (1-2 شهور)</h2>
                            <p>
                                كل رحلة تبدأ بالأساسيات. في هذه المرحلة ستتعلم اللبنات الأساسية لأي موقع ويب:
                                <strong>HTML</strong> لبناء الهيكل، <strong>CSS</strong> للتنسيق والتصميم، و
                                <strong>JavaScript</strong> لإضافة التفاعلية. لا تستعجل هذه المرحلة فهي الأساس الذي
                                سيُبنى عليه كل شيء لاحقاً.
                            </p>

                            <div class="bd-highlight-box">
                                <h5><i class="fas fa-lightbulb"></i> نصيحة مهمة</h5>
                                <p>لا تنتقل من مرحلة لأخرى حتى تتأكد من إتقان المرحلة الحالية. التعلم التراكمي هو مفتاح
                                    النجاح في البرمجة. قم ببناء مشاريع صغيرة بعد كل موضوع تتعلمه.</p>
                            </div>

                            <h2><i class="fas fa-code" style="color:var(--clr-primary); margin-left:8px;"></i> المرحلة
                                الثانية: JavaScript المتقدم (2-3 شهور)</h2>
                            <p>
                                بعد إتقان الأساسيات، حان وقت التعمق في JavaScript. تعلّم المفاهيم المتقدمة مثل:
                            </p>
                            <ul class="bd-list">
                                <li><i class="fas fa-check-circle"></i> ES6+ Features (Arrow Functions, Destructuring,
                                    Modules)</li>
                                <li><i class="fas fa-check-circle"></i> Async/Await والتعامل مع APIs</li>
                                <li><i class="fas fa-check-circle"></i> DOM Manipulation بشكل متقدم</li>
                                <li><i class="fas fa-check-circle"></i> Error Handling والتعامل مع الأخطاء</li>
                                <li><i class="fas fa-check-circle"></i> OOP في JavaScript</li>
                            </ul>

                            <h2><i class="fas fa-laptop-code" style="color:var(--clr-primary); margin-left:8px;"></i>
                                المرحلة الثالثة: إطار عمل أمامي (2-3 شهور)</h2>
                            <p>
                                في هذه المرحلة، ستختار إطار عمل Front-end واحد وتتعمق فيه. الخيارات الأكثر طلباً في سوق
                                العمل لعام 2026 هي:
                            </p>

                            <div class="bd-comparison-table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>الإطار</th>
                                            <th>سهولة التعلم</th>
                                            <th>الطلب في السوق</th>
                                            <th>الأداء</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>React.js</strong></td>
                                            <td>⭐⭐⭐</td>
                                            <td>⭐⭐⭐⭐⭐</td>
                                            <td>⭐⭐⭐⭐</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Vue.js</strong></td>
                                            <td>⭐⭐⭐⭐⭐</td>
                                            <td>⭐⭐⭐</td>
                                            <td>⭐⭐⭐⭐</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Angular</strong></td>
                                            <td>⭐⭐</td>
                                            <td>⭐⭐⭐⭐</td>
                                            <td>⭐⭐⭐⭐⭐</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h2><i class="fas fa-server" style="color:var(--clr-primary); margin-left:8px;"></i> المرحلة
                                الرابعة: تطوير الخادم Backend (3-4 شهور)</h2>
                            <p>
                                حان الوقت لتعلم الجانب الآخر من المعادلة. تطوير الخادم (Backend) يشمل بناء APIs، التعامل
                                مع قواعد البيانات، المصادقة والتفويض، وغيرها. الخيارات الشائعة تشمل <strong>Node.js مع
                                    Express</strong> أو <strong>Python مع Django/FastAPI</strong>.
                            </p>

                            <div class="bd-code-block">
                                <div class="bd-code-header">
                                    <span>مثال: إنشاء خادم Express.js بسيط</span>
                                    <button
                                        onclick="navigator.clipboard.writeText(this.parentElement.nextElementSibling.textContent)"
                                        style="background:var(--clr-primary);color:#fff;border:none;padding:4px 12px;border-radius:5px;font-size:0.75rem;cursor:pointer;">
                                        <i class="fas fa-copy"></i> نسخ
                                    </button>
                                </div>
                                <pre><code>const express = require('express');
const app = express();

app.get('/api/courses', (req, res) => {
  res.json({
    success: true,
    data: [
      { id: 1, title: 'دورة تطوير الويب' },
      { id: 2, title: 'دورة بايثون' }
    ]
  });
});

app.listen(3000, () => {
  console.log('Server running on port 3000');
});</code></pre>
                            </div>

                            <h2><i class="fas fa-rocket" style="color:var(--clr-primary); margin-left:8px;"></i> المرحلة
                                الخامسة: المشاريع والنشر (مستمر)</h2>
                            <p>
                                أهم مرحلة هي بناء مشاريع حقيقية ونشرها للعالم. ابنِ معرض أعمال (Portfolio) يعرض مشاريعك،
                                وتعلم كيفية نشر تطبيقاتك على منصات مثل <strong>Vercel</strong>،
                                <strong>Netlify</strong>، أو <strong>AWS</strong>. هذه المشاريع ستكون تذكرتك لسوق العمل.
                            </p>

                            <div class="bd-highlight-box" style="border-right-color: #28a745;">
                                <h5 style="color: #28a745;"><i class="fas fa-graduation-cap"></i> خلاصة</h5>
                                <p>تعلم تطوير الويب رحلة ممتعة تحتاج صبراً ومثابرة. اتبع الخطة خطوة بخطوة، طبّق ما
                                    تتعلمه في مشاريع حقيقية، ولا تتوقف عن التعلم. سوق العمل بحاجة لمطورين أكفاء، وأنت
                                    قادر على أن تكون واحداً منهم!</p>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div class="bd-tags">
                            <span class="bd-tag-label"><i class="fas fa-tags"></i> الوسوم:</span>
                            <a href="#" class="bd-tag">تطوير الويب</a>
                            <a href="#" class="bd-tag">HTML</a>
                            <a href="#" class="bd-tag">CSS</a>
                            <a href="#" class="bd-tag">JavaScript</a>
                            <a href="#" class="bd-tag">React</a>
                            <a href="#" class="bd-tag">Node.js</a>
                            <a href="#" class="bd-tag">برمجة</a>
                        </div>

                        <!-- Share -->
                        <div class="bd-share">
                            <span><i class="fas fa-share-alt"></i> شارك المقال:</span>
                            <div class="bd-share-icons">
                                <a href="#" class="bd-share-btn" style="background:#1877F2;"><i
                                        class="fab fa-facebook-f"></i></a>
                                <a href="#" class="bd-share-btn" style="background:#1DA1F2;"><i
                                        class="fab fa-twitter"></i></a>
                                <a href="#" class="bd-share-btn" style="background:#25D366;"><i
                                        class="fab fa-whatsapp"></i></a>
                                <a href="#" class="bd-share-btn" style="background:#0077B5;"><i
                                        class="fab fa-linkedin-in"></i></a>
                                <a href="#" class="bd-share-btn" style="background:#E4405F;"><i
                                        class="fab fa-instagram"></i></a>
                                <a href="#" class="bd-share-btn" style="background:#0088cc;"><i
                                        class="fab fa-telegram-plane"></i></a>
                            </div>
                        </div>

                        <!-- Prev / Next -->
                        <div class="bd-nav-posts">
                            <a href="#" class="bd-nav-post bd-nav-prev">
                                <span class="bd-nav-label"><i class="fas fa-arrow-right"></i> المقال السابق</span>
                                <span class="bd-nav-title">فهم React Hooks بشكل عميق</span>
                            </a>
                            <a href="#" class="bd-nav-post bd-nav-next">
                                <span class="bd-nav-label">المقال التالي <i class="fas fa-arrow-left"></i></span>
                                <span class="bd-nav-title">كيف تبدأ في تعلم الذكاء الاصطناعي</span>
                            </a>
                        </div>

                        <!-- Comments Section -->
                        <div class="bd-comments">
                            <h4 class="bd-comments-title"><i class="fas fa-comments"
                                    style="color:var(--clr-primary);"></i> التعليقات (28)</h4>

                            <!-- Comment 1 -->
                            <div class="bd-comment">
                                <div class="bd-comment-avatar"
                                    style="background:linear-gradient(135deg,var(--clr-primary),var(--clr-primary-dark));">
                                    أ</div>
                                <div class="bd-comment-body">
                                    <div class="bd-comment-head">
                                        <strong>أحمد سليمان</strong>
                                        <span>منذ 3 أيام</span>
                                    </div>
                                    <p>مقال رائع ومفيد جداً! بدأت فعلاً بتطبيق الخطة وأنا الآن في مرحلة تعلم JavaScript
                                        المتقدم. شكراً للمدرب ياسين على هذا المحتوى القيّم 🙏</p>
                                    <button class="bd-reply-btn"><i class="fas fa-reply"></i> رد</button>
                                </div>
                            </div>

                            <!-- Comment 2 with reply -->
                            <div class="bd-comment">
                                <div class="bd-comment-avatar"
                                    style="background:linear-gradient(135deg,#667eea,#764ba2);">س</div>
                                <div class="bd-comment-body">
                                    <div class="bd-comment-head">
                                        <strong>سناء محمود</strong>
                                        <span>منذ 5 أيام</span>
                                    </div>
                                    <p>هل تنصح بالبدء بـ React أم Vue للمبتدئين؟ أنا مترددة بينهما. ومتى ستطلق دورة
                                        جديدة؟</p>
                                    <button class="bd-reply-btn"><i class="fas fa-reply"></i> رد</button>

                                    <!-- Reply -->
                                    <div class="bd-comment bd-comment-reply">
                                        <div class="bd-comment-avatar"
                                            style="background:linear-gradient(135deg,var(--clr-primary),var(--clr-primary-dark));">
                                            <img src="assets/images/logo.svg" alt="ياسين"
                                                style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
                                        </div>
                                        <div class="bd-comment-body">
                                            <div class="bd-comment-head">
                                                <strong>ياسين جوخدار <span
                                                        class="bd-author-badge">الكاتب</span></strong>
                                                <span>منذ 4 أيام</span>
                                            </div>
                                            <p>مرحباً سناء! أنصحك بالبدء بـ React لأنها الأكثر طلباً في سوق العمل.
                                                بالنسبة للدورة الجديدة، سيتم الإعلان عنها قريباً على قناة التليجرام 😊
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Comment 3 -->
                            <div class="bd-comment">
                                <div class="bd-comment-avatar"
                                    style="background:linear-gradient(135deg,#f093fb,#f5576c);">م</div>
                                <div class="bd-comment-body">
                                    <div class="bd-comment-head">
                                        <strong>محمد عبدالله</strong>
                                        <span>منذ أسبوع</span>
                                    </div>
                                    <p>جدول المقارنة بين الإطارات مفيد جداً! كنت محتاج هذا النوع من المقارنات لاتخاذ
                                        القرار. شكراً جزيلاً 👏</p>
                                    <button class="bd-reply-btn"><i class="fas fa-reply"></i> رد</button>
                                </div>
                            </div>

                            <!-- Add Comment Form -->
                            <div class="bd-add-comment">
                                <h5><i class="fas fa-pen" style="color:var(--clr-primary);"></i> أضف تعليقك</h5>
                                <form>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <input type="text" class="form-control" placeholder="الاسم الكامل"
                                                style="background:var(--clr-surface);border:1px solid var(--clr-border);color:var(--clr-text);padding:12px 16px;border-radius:var(--radius-md);font-family:var(--font-family);">
                                        </div>
                                        <div class="col-md-6">
                                            <input type="email" class="form-control" placeholder="البريد الإلكتروني"
                                                style="background:var(--clr-surface);border:1px solid var(--clr-border);color:var(--clr-text);padding:12px 16px;border-radius:var(--radius-md);font-family:var(--font-family);">
                                        </div>
                                        <div class="col-12">
                                            <textarea class="form-control" rows="4" placeholder="اكتب تعليقك هنا..."
                                                style="background:var(--clr-surface);border:1px solid var(--clr-border);color:var(--clr-text);padding:12px 16px;border-radius:var(--radius-md);font-family:var(--font-family);"></textarea>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn-primary-custom"><i
                                                    class="fas fa-paper-plane"></i> إرسال التعليق</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SIDEBAR -->
                <div class="col-lg-4">
                    <!-- Author Card -->
                    <div class="glass-panel animate-on-scroll"
                        style="padding:25px; text-align:center; margin-bottom:20px;">
                        <img src="assets/images/trainer.svg" alt="ياسين جوخدار"
                            style="width:90px;height:90px;border-radius:50%;border:3px solid var(--clr-primary);object-fit:cover;margin-bottom:12px;">
                        <h5 style="font-weight:700;margin-bottom:3px;">ياسين جوخدار</h5>
                        <p style="font-size:0.82rem;color:var(--clr-primary);font-weight:600;margin-bottom:10px;">مطور
                            ويب ومدرب تقني</p>
                        <p style="font-size:0.88rem;color:var(--clr-text-secondary);margin-bottom:15px;">مدرب ومطور
                            برمجيات بخبرة +10 سنوات. شغوف بنقل المعرفة وتبسيط المفاهيم البرمجية.</p>
                        <a href="about.html" class="btn-outline-custom"
                            style="width:100%;justify-content:center;padding:8px;font-size:0.88rem;">
                            <i class="fas fa-user"></i> عرض الملف الشخصي
                        </a>
                    </div>

                    <!-- Search -->
                    <div class="glass-panel animate-on-scroll" style="padding:20px; margin-bottom:20px;">
                        <h6 style="font-weight:700;margin-bottom:12px;"><i class="fas fa-search"
                                style="color:var(--clr-primary);"></i> بحث في المدونة</h6>
                        <div style="position:relative;">
                            <input type="text" class="form-control" placeholder="ابحث عن مقال..."
                                style="background:var(--clr-surface);border:1px solid var(--clr-border);color:var(--clr-text);padding:10px 16px 10px 40px;border-radius:var(--radius-md);font-family:var(--font-family);font-size:0.9rem;">
                            <i class="fas fa-search"
                                style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--clr-text-muted);font-size:0.85rem;"></i>
                        </div>
                    </div>

                    <!-- Categories -->
                    <div class="glass-panel animate-on-scroll" style="padding:20px; margin-bottom:20px;">
                        <h6 style="font-weight:700;margin-bottom:15px;"><i class="fas fa-th-list"
                                style="color:var(--clr-primary);"></i> التصنيفات</h6>
                        <div class="bd-sidebar-cats">
                            <a href="#" class="bd-cat-item"><span>تطوير الويب</span><span
                                    class="bd-cat-count">12</span></a>
                            <a href="#" class="bd-cat-item"><span>بايثون و AI</span><span
                                    class="bd-cat-count">8</span></a>
                            <a href="#" class="bd-cat-item"><span>الموبايل</span><span class="bd-cat-count">6</span></a>
                            <a href="#" class="bd-cat-item"><span>DevOps</span><span class="bd-cat-count">4</span></a>
                            <a href="#" class="bd-cat-item"><span>نصائح تقنية</span><span
                                    class="bd-cat-count">10</span></a>
                        </div>
                    </div>

                    <!-- Recent Posts -->
                    <div class="glass-panel animate-on-scroll" style="padding:20px; margin-bottom:20px;">
                        <h6 style="font-weight:700;margin-bottom:15px;"><i class="fas fa-fire"
                                style="color:var(--clr-primary);"></i> مقالات حديثة</h6>
                        <a href="#" class="bd-recent-post">
                            <img src="assets/images/course-python.svg" alt="مقال">
                            <div>
                                <h6 style="font-weight:700;font-size:0.85rem;margin-bottom:3px;">مدخل إلى الذكاء
                                    الاصطناعي التوليدي</h6>
                                <span style="font-size:0.75rem;color:var(--clr-text-muted);"><i
                                        class="fas fa-calendar-alt"></i> 20 فبراير 2026</span>
                            </div>
                        </a>
                        <a href="#" class="bd-recent-post">
                            <img src="assets/images/course-mobile.svg" alt="مقال">
                            <div>
                                <h6 style="font-weight:700;font-size:0.85rem;margin-bottom:3px;">Flutter vs React Native
                                    في 2026</h6>
                                <span style="font-size:0.75rem;color:var(--clr-text-muted);"><i
                                        class="fas fa-calendar-alt"></i> 18 فبراير 2026</span>
                            </div>
                        </a>
                        <a href="#" class="bd-recent-post">
                            <img src="assets/images/workshop.svg" alt="مقال">
                            <div>
                                <h6 style="font-weight:700;font-size:0.85rem;margin-bottom:3px;">Docker للمبتدئين: كل ما
                                    تحتاج معرفته</h6>
                                <span style="font-size:0.75rem;color:var(--clr-text-muted);"><i
                                        class="fas fa-calendar-alt"></i> 15 فبراير 2026</span>
                            </div>
                        </a>
                        <a href="#" class="bd-recent-post"
                            style="margin-bottom:0; padding-bottom:0; border-bottom:none;">
                            <img src="assets/images/trainer.svg" alt="مقال">
                            <div>
                                <h6 style="font-weight:700;font-size:0.85rem;margin-bottom:3px;">10 نصائح ذهبية لكل
                                    مبرمج مبتدئ</h6>
                                <span style="font-size:0.75rem;color:var(--clr-text-muted);"><i
                                        class="fas fa-calendar-alt"></i> 12 فبراير 2026</span>
                            </div>
                        </a>
                    </div>

                    <!-- Tags Cloud -->
                    <div class="glass-panel animate-on-scroll" style="padding:20px; margin-bottom:20px;">
                        <h6 style="font-weight:700;margin-bottom:15px;"><i class="fas fa-tags"
                                style="color:var(--clr-primary);"></i> الوسوم</h6>
                        <div style="display:flex;flex-wrap:wrap;gap:8px;">
                            <a href="#" class="bd-tag">HTML</a>
                            <a href="#" class="bd-tag">CSS</a>
                            <a href="#" class="bd-tag">JavaScript</a>
                            <a href="#" class="bd-tag">React</a>
                            <a href="#" class="bd-tag">Node.js</a>
                            <a href="#" class="bd-tag">بايثون</a>
                            <a href="#" class="bd-tag">Flutter</a>
                            <a href="#" class="bd-tag">Docker</a>
                            <a href="#" class="bd-tag">Git</a>
                            <a href="#" class="bd-tag">AI</a>
                            <a href="#" class="bd-tag">WordPress</a>
                        </div>
                    </div>

                    <!-- Newsletter -->
                    <div class="glass-panel animate-on-scroll"
                        style="padding:25px; text-align:center; background:linear-gradient(135deg, rgba(230,57,70,0.1), rgba(230,57,70,0.05));">
                        <i class="fas fa-envelope-open-text"
                            style="font-size:2rem;color:var(--clr-primary);margin-bottom:12px;display:block;"></i>
                        <h6 style="font-weight:700;margin-bottom:8px;">اشترك في النشرة البريدية</h6>
                        <p style="font-size:0.82rem;color:var(--clr-text-secondary);margin-bottom:15px;">احصل على أحدث
                            المقالات في بريدك</p>
                        <input type="email" placeholder="بريدك الإلكتروني"
                            style="width:100%;padding:10px 14px;border:1px solid var(--clr-border);border-radius:var(--radius-md);background:var(--clr-surface);color:var(--clr-text);font-family:var(--font-family);font-size:0.9rem;margin-bottom:10px;">
                        <button class="btn-primary-custom"
                            style="width:100%;justify-content:center;padding:10px;font-size:0.9rem;">
                            <i class="fas fa-paper-plane"></i> اشتراك
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="main-footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <h5><img src="assets/images/logo.svg" alt="لوغو"
                            style="width:35px;height:35px;border-radius:50%;margin-left:8px;border:2px solid var(--clr-primary);">ياسين
                        جوخدار</h5>
                    <p>مدرب ومطور برمجيات شغوف بالتعليم ونقل المعرفة.</p>
                    <div class="footer-social">
                        <a href="#"><i class="fab fa-facebook-f"></i></a><a href="#"><i
                                class="fab fa-youtube"></i></a><a href="#"><i class="fab fa-instagram"></i></a><a
                            href="#"><i class="fab fa-linkedin-in"></i></a><a href="#"><i
                                class="fab fa-github"></i></a><a href="#"><i class="fab fa-telegram-plane"></i></a>
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
                        <li><a href="course-detail.html"><i class="fas fa-chevron-left"></i> تطوير الويب الشامل</a></li>
                        <li><a href="#"><i class="fas fa-chevron-left"></i> بايثون للمبتدئين</a></li>
                        <li><a href="#"><i class="fas fa-chevron-left"></i> Flutter للموبايل</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5>تواصل معنا</h5>
                    <ul class="footer-links">
                        <li><i class="fas fa-envelope" style="color:var(--clr-primary);margin-left:8px;"></i>
                            info@yasinjokhadar.net</li>
                        <li><i class="fas fa-phone" style="color:var(--clr-primary);margin-left:8px;"></i> +963 XXX XXX
                            XXX</li>
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