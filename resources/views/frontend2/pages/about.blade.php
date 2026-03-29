@extends('frontend2.layouts.master')

@section('title', 'حول الأكاديمية | أكاديمية كلاودسوفت للخدمات والحلول البرمجية')
@section('meta_description', 'تعرف على أكاديمية كلاودسوفت — خدمات وحلول برمجية، تدريب تقني، تطوير ويب وموبايل، واستشارات. خبرة واسعة في التدريب والتطوير.')

@section('content')

    <!-- ============ PAGE BANNER (حول الأكاديمية) ============ -->
    <section class="page-banner page-banner-about">
        <div class="page-banner-overlay"></div>
        <div class="container position-relative">
            <div class="page-banner-content animate-on-scroll">
                <div class="page-banner-icon"><i class="fas fa-building"></i></div>
                <h1 class="page-banner-title">حول <span>الأكاديمية</span></h1>
                <p class="page-banner-desc">أكاديمية كلاودسوفت للخدمات والحلول البرمجية — تدريب تقني عملي، تطوير ويب وموبايل، واستشارات احترافية</p>
                <nav class="page-banner-breadcrumb" aria-label="breadcrumb">
                    <a href="{{ route('frontend.home') }}">الرئيسية</a>
                    <span class="page-banner-sep">/</span>
                    <span>حول الأكاديمية</span>
                </nav>
            </div>
        </div>
        <div class="page-banner-shape"></div>
    </section>

    <!-- ============ ABOUT INTRO ============ -->
    <section class="section-padding">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-5">
                    <div class="about-img-wrapper animate-on-scroll">
                        <img src="{{ asset('frontend2/assets/images/trainer.svg') }}" alt="أكاديمية كلاودسوفت" class="w-100" width="400" height="400" loading="lazy">
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="animate-on-scroll">
                        <span class="section-badge" style="display:inline-block; margin-bottom:15px;">من نحن؟</span>
                        <h2 style="font-weight:800; font-size:2rem; margin-bottom:20px;">أكاديمية كلاودسوفت</h2>
                        <p style="font-size:1.05rem; line-height:2; color:var(--clr-text-secondary);">
                            أكاديمية متخصصة في الخدمات والحلول البرمجية، نقدّم تدريباً تقنياً عملياً في تطوير الويب وتطبيقات الموبايل وقواعد البيانات وأنظمة إدارة المحتوى.
                        </p>
                        <p style="font-size:1.05rem; line-height:2; color:var(--clr-text-secondary);">
                            نؤمن بأن التعليم العملي هو أفضل طريقة لاكتساب المهارات البرمجية، لذلك نركز في دوراتنا على المشاريع الحقيقية والتطبيق العملي. ندرّب آلاف المتدربين في مختلف مجالات البرمجة والتقنية.
                        </p>

                        <!-- Quick Facts -->
                        <div class="row g-3 mt-3">
                            <div class="col-sm-6">
                                <div class="glass-panel" style="padding:18px; text-align:center;">
                                    <i class="fas fa-graduation-cap" style="font-size:1.5rem; color:var(--clr-primary); margin-bottom:8px; display:block;"></i>
                                    <strong>+50 دورة تدريبية</strong>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="glass-panel" style="padding:18px; text-align:center;">
                                    <i class="fas fa-users" style="font-size:1.5rem; color:var(--clr-primary); margin-bottom:8px; display:block;"></i>
                                    <strong>+5000 متدرب</strong>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="glass-panel" style="padding:18px; text-align:center;">
                                    <i class="fas fa-laptop-code" style="font-size:1.5rem; color:var(--clr-primary); margin-bottom:8px; display:block;"></i>
                                    <strong>+200 مشروع</strong>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="glass-panel" style="padding:18px; text-align:center;">
                                    <i class="fas fa-certificate" style="font-size:1.5rem; color:var(--clr-primary); margin-bottom:8px; display:block;"></i>
                                    <strong>شهادات معتمدة</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ TIMELINE ============ -->
    <section class="section-padding" style="background: var(--clr-bg-secondary);">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <span class="section-badge">المسيرة</span>
                <h2>رحلة الأكاديمية</h2>
                <p>محطات بارزة في تقديم الخدمات والحلول البرمجية والتدريب التقني</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="timeline animate-on-scroll">
                        <div class="timeline-item">
                            <span class="year">2016</span>
                            <h5>بداية المشوار</h5>
                            <p>انطلاق العمل في مجال البرمجة وتطوير الويب من خلال الموارد العملية وتعلم HTML, CSS و JavaScript.</p>
                        </div>
                        <div class="timeline-item">
                            <span class="year">2018</span>
                            <h5>تطوير مشاريع ويب</h5>
                            <p>تنفيذ مشاريع متعددة في شركات تقنية باستخدام React و Node.js وتقديم حلول برمجية احترافية.</p>
                        </div>
                        <div class="timeline-item">
                            <span class="year">2019</span>
                            <h5>انطلاق التدريب</h5>
                            <p>بدء تقديم دورات تدريبية محلية ثم التوسع إلى التدريب أونلاين لمختلف المجالات البرمجية.</p>
                        </div>
                        <div class="timeline-item">
                            <span class="year">2021</span>
                            <h5>إطلاق منصة الكورسات</h5>
                            <p>إطلاق المنصة التعليمية وتقديم أكثر من 30 دورة تدريبية في تطوير الويب والموبايل والبرمجة.</p>
                        </div>
                        <div class="timeline-item">
                            <span class="year">2023</span>
                            <h5>التوسع</h5>
                            <p>التوسع في تقديم الدورات ليشمل عدة دول عربية والمشاركة في فعاليات تقنية إقليمية.</p>
                        </div>
                        <div class="timeline-item">
                            <span class="year">2026</span>
                            <h5>المرحلة الحالية</h5>
                            <p>التركيز على محتوى تعليمي متقدم في الذكاء الاصطناعي و DevOps والحوسبة السحابية والخدمات البرمجية.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ مهاراتنا التفصيلية ============ -->
    <section class="section-padding" style="background: var(--clr-bg-secondary);">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <span class="section-badge">المهارات</span>
                <h2>مهاراتنا التقنية</h2>
                <p>لغات برمجة، أطر عمل، قواعد بيانات وأدوات نستخدمها في مشاريعنا ودوراتنا</p>
            </div>
            <div class="skills-detailed animate-on-scroll">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="glass-panel skills-category">
                            <h4 class="skills-category-title"><i class="fas fa-code"></i> المهارات البرمجية — اللغات</h4>
                            <div class="skill-progress-item">
                                <div class="skill-progress-head"><span>JavaScript / TypeScript</span><span class="skill-progress-pct">92%</span></div>
                                <div class="skill-progress-bar"><div class="skill-progress-fill" style="width:92%"></div></div>
                            </div>
                            <div class="skill-progress-item">
                                <div class="skill-progress-head"><span>Python</span><span class="skill-progress-pct">88%</span></div>
                                <div class="skill-progress-bar"><div class="skill-progress-fill" style="width:88%"></div></div>
                            </div>
                            <div class="skill-progress-item">
                                <div class="skill-progress-head"><span>HTML5 / CSS3 / SASS</span><span class="skill-progress-pct">95%</span></div>
                                <div class="skill-progress-bar"><div class="skill-progress-fill" style="width:95%"></div></div>
                            </div>
                            <div class="skill-progress-item">
                                <div class="skill-progress-head"><span>PHP</span><span class="skill-progress-pct">80%</span></div>
                                <div class="skill-progress-bar"><div class="skill-progress-fill" style="width:80%"></div></div>
                            </div>
                            <div class="skill-progress-item">
                                <div class="skill-progress-head"><span>Dart</span><span class="skill-progress-pct">82%</span></div>
                                <div class="skill-progress-bar"><div class="skill-progress-fill" style="width:82%"></div></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="glass-panel skills-category">
                            <h4 class="skills-category-title"><i class="fas fa-puzzle-piece"></i> أطر العمل والمكتبات</h4>
                            <div class="skill-progress-item">
                                <div class="skill-progress-head"><span>React.js</span><span class="skill-progress-pct">90%</span></div>
                                <div class="skill-progress-bar"><div class="skill-progress-fill" style="width:90%"></div></div>
                            </div>
                            <div class="skill-progress-item">
                                <div class="skill-progress-head"><span>Node.js / Express</span><span class="skill-progress-pct">88%</span></div>
                                <div class="skill-progress-bar"><div class="skill-progress-fill" style="width:88%"></div></div>
                            </div>
                            <div class="skill-progress-item">
                                <div class="skill-progress-head"><span>Flutter</span><span class="skill-progress-pct">85%</span></div>
                                <div class="skill-progress-bar"><div class="skill-progress-fill" style="width:85%"></div></div>
                            </div>
                            <div class="skill-progress-item">
                                <div class="skill-progress-head"><span>Bootstrap / Tailwind</span><span class="skill-progress-pct">92%</span></div>
                                <div class="skill-progress-bar"><div class="skill-progress-fill" style="width:92%"></div></div>
                            </div>
                            <div class="skill-progress-item">
                                <div class="skill-progress-head"><span>Next.js / Nuxt</span><span class="skill-progress-pct">82%</span></div>
                                <div class="skill-progress-bar"><div class="skill-progress-fill" style="width:82%"></div></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="glass-panel skills-category">
                            <h4 class="skills-category-title"><i class="fas fa-database"></i> قواعد البيانات</h4>
                            <div class="skill-progress-item">
                                <div class="skill-progress-head"><span>MongoDB</span><span class="skill-progress-pct">87%</span></div>
                                <div class="skill-progress-bar"><div class="skill-progress-fill" style="width:87%"></div></div>
                            </div>
                            <div class="skill-progress-item">
                                <div class="skill-progress-head"><span>MySQL / PostgreSQL</span><span class="skill-progress-pct">85%</span></div>
                                <div class="skill-progress-bar"><div class="skill-progress-fill" style="width:85%"></div></div>
                            </div>
                            <div class="skill-progress-item">
                                <div class="skill-progress-head"><span>Firebase</span><span class="skill-progress-pct">83%</span></div>
                                <div class="skill-progress-bar"><div class="skill-progress-fill" style="width:83%"></div></div>
                            </div>
                            <div class="skill-progress-item">
                                <div class="skill-progress-head"><span>Redis</span><span class="skill-progress-pct">75%</span></div>
                                <div class="skill-progress-bar"><div class="skill-progress-fill" style="width:75%"></div></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="glass-panel skills-category">
                            <h4 class="skills-category-title"><i class="fas fa-tools"></i> أدوات ومنصات</h4>
                            <div class="skill-progress-item">
                                <div class="skill-progress-head"><span>Git / GitHub</span><span class="skill-progress-pct">92%</span></div>
                                <div class="skill-progress-bar"><div class="skill-progress-fill" style="width:92%"></div></div>
                            </div>
                            <div class="skill-progress-item">
                                <div class="skill-progress-head"><span>Docker</span><span class="skill-progress-pct">78%</span></div>
                                <div class="skill-progress-bar"><div class="skill-progress-fill" style="width:78%"></div></div>
                            </div>
                            <div class="skill-progress-item">
                                <div class="skill-progress-head"><span>Linux / Shell</span><span class="skill-progress-pct">85%</span></div>
                                <div class="skill-progress-bar"><div class="skill-progress-fill" style="width:85%"></div></div>
                            </div>
                            <div class="skill-progress-item">
                                <div class="skill-progress-head"><span>Figma / UI Design</span><span class="skill-progress-pct">80%</span></div>
                                <div class="skill-progress-bar"><div class="skill-progress-fill" style="width:80%"></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ مجالات التخصص ============ -->
    <section class="section-padding" id="specialties">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <span class="section-badge">التخصصات</span>
                <h2>مجالات نقدمها</h2>
                <p>المجالات التقنية التي نقدم فيها خدمات وحلولاً ودورات تدريبية</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel animate-on-scroll animate-delay-1" style="padding:30px; height:100%;">
                        <div style="display:flex; align-items:center; gap:15px; margin-bottom:20px;">
                            <div style="width:55px; height:55px; border-radius:var(--radius-md); background:linear-gradient(135deg, var(--clr-primary), var(--clr-primary-dark)); display:flex; align-items:center; justify-content:center; color:#fff; font-size:1.4rem; flex-shrink:0;">
                                <i class="fas fa-code"></i></div>
                            <h5 style="font-weight:700; margin:0;">تطوير الواجهات الأمامية</h5>
                        </div>
                        <p style="color:var(--clr-text-secondary); font-size:0.95rem;">
                            بناء واجهات مستخدم تفاعلية وجذابة باستخدام HTML5, CSS3, JavaScript, React.js, Vue.js و Next.js مع التركيز على التجاوب وتجربة المستخدم.
                        </p>
                        <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:15px;">
                            <span style="background:var(--clr-surface); padding:4px 12px; border-radius:50px; font-size:0.78rem; color:var(--clr-text-secondary);">React</span>
                            <span style="background:var(--clr-surface); padding:4px 12px; border-radius:50px; font-size:0.78rem; color:var(--clr-text-secondary);">Vue.js</span>
                            <span style="background:var(--clr-surface); padding:4px 12px; border-radius:50px; font-size:0.78rem; color:var(--clr-text-secondary);">Next.js</span>
                            <span style="background:var(--clr-surface); padding:4px 12px; border-radius:50px; font-size:0.78rem; color:var(--clr-text-secondary);">Bootstrap</span>
                        </div>
                        <a href="{{ route('frontend.home') }}#services" class="btn-outline-custom mt-3" style="display:inline-flex; padding:8px 18px; font-size:0.88rem;"><i class="fas fa-arrow-left"></i> اعرف المزيد</a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel animate-on-scroll animate-delay-2" style="padding:30px; height:100%;">
                        <div style="display:flex; align-items:center; gap:15px; margin-bottom:20px;">
                            <div style="width:55px; height:55px; border-radius:var(--radius-md); background:linear-gradient(135deg, var(--clr-primary), var(--clr-primary-dark)); display:flex; align-items:center; justify-content:center; color:#fff; font-size:1.4rem; flex-shrink:0;">
                                <i class="fas fa-server"></i></div>
                            <h5 style="font-weight:700; margin:0;">تطوير الخوادم و API</h5>
                        </div>
                        <p style="color:var(--clr-text-secondary); font-size:0.95rem;">
                            تصميم وبناء خوادم وواجهات برمجة التطبيقات RESTful APIs باستخدام Node.js, Express, NestJS, Python Django وقواعد بيانات متنوعة.
                        </p>
                        <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:15px;">
                            <span style="background:var(--clr-surface); padding:4px 12px; border-radius:50px; font-size:0.78rem; color:var(--clr-text-secondary);">Node.js</span>
                            <span style="background:var(--clr-surface); padding:4px 12px; border-radius:50px; font-size:0.78rem; color:var(--clr-text-secondary);">NestJS</span>
                            <span style="background:var(--clr-surface); padding:4px 12px; border-radius:50px; font-size:0.78rem; color:var(--clr-text-secondary);">Django</span>
                            <span style="background:var(--clr-surface); padding:4px 12px; border-radius:50px; font-size:0.78rem; color:var(--clr-text-secondary);">MySQL</span>
                        </div>
                        <a href="{{ route('frontend.home') }}#services" class="btn-outline-custom mt-3" style="display:inline-flex; padding:8px 18px; font-size:0.88rem;"><i class="fas fa-arrow-left"></i> اعرف المزيد</a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="glass-panel animate-on-scroll animate-delay-3" style="padding:30px; height:100%;">
                        <div style="display:flex; align-items:center; gap:15px; margin-bottom:20px;">
                            <div style="width:55px; height:55px; border-radius:var(--radius-md); background:linear-gradient(135deg, var(--clr-primary), var(--clr-primary-dark)); display:flex; align-items:center; justify-content:center; color:#fff; font-size:1.4rem; flex-shrink:0;">
                                <i class="fas fa-mobile-alt"></i></div>
                            <h5 style="font-weight:700; margin:0;">تطوير تطبيقات الموبايل</h5>
                        </div>
                        <p style="color:var(--clr-text-secondary); font-size:0.95rem;">
                            تطوير تطبيقات موبايل متعددة المنصات لنظامي Android و iOS باستخدام Flutter و React Native مع واجهات مستخدم احترافية.
                        </p>
                        <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:15px;">
                            <span style="background:var(--clr-surface); padding:4px 12px; border-radius:50px; font-size:0.78rem; color:var(--clr-text-secondary);">Flutter</span>
                            <span style="background:var(--clr-surface); padding:4px 12px; border-radius:50px; font-size:0.78rem; color:var(--clr-text-secondary);">React Native</span>
                            <span style="background:var(--clr-surface); padding:4px 12px; border-radius:50px; font-size:0.78rem; color:var(--clr-text-secondary);">Dart</span>
                            <span style="background:var(--clr-surface); padding:4px 12px; border-radius:50px; font-size:0.78rem; color:var(--clr-text-secondary);">Firebase</span>
                        </div>
                        <a href="{{ route('frontend.home') }}#services" class="btn-outline-custom mt-3" style="display:inline-flex; padding:8px 18px; font-size:0.88rem;"><i class="fas fa-arrow-left"></i> اعرف المزيد</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ CTA ============ -->
    <section class="cta-section">
        <div class="container animate-on-scroll">
            <h2>هل لديك مشروع أو فكرة تحتاج تنفيذها؟</h2>
            <p>تواصل معنا ونحول فكرتك إلى واقع ملموس</p>
            <a href="{{ route('frontend.contact') }}" class="btn-light-custom">
                <i class="fas fa-envelope"></i> تواصل معنا
            </a>
        </div>
    </section>
@endsection
