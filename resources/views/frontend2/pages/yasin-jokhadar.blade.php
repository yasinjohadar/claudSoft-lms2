@extends('frontend2.layouts.master')

@php
    $pageTitle = 'ياسين جوخدار | مؤسس أكاديمية كلاودسوفت — خبرة، مشاريع، وتقنية';
    $pageDescription = 'تعرّف على ياسين جوخدار — مؤسس أكاديمية كلاودسوفت: المسيرة المهنية، الدراسة، الأدوات التقنية، أعمال مختارة ومعرض صور. نصوص وصور مؤقتة قابلة للاستبدال.';
    $canonicalUrl = route('frontend.yasin-jokhadar');
    $ogImage = asset('frontend2/assets/images/founder/portrait.svg');
    $keywords = 'ياسين جوخدار, Claud Soft, أكاديمية كلاودسوفت, مطور ويب, Laravel, PHP, تدريب برمجة, حلول برمجية';
@endphp

@section('title', $pageTitle)
@section('meta_description', $pageDescription)

@push('head')
    <link rel="stylesheet" href="{{ asset('frontend2/assets/css/founder.css') }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta name="robots" content="index, follow, max-image-preview:large">
    <meta name="keywords" content="{{ $keywords }}">
    <meta name="author" content="ياسين جوخدار">

    <meta property="og:type" content="profile">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:locale" content="ar_SY">
    <meta property="og:site_name" content="{{ config('app.name', 'أكاديمية كلاودسوفت') }}">
    <meta property="profile:first_name" content="ياسين">
    <meta property="profile:last_name" content="جوخدار">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'الرئيسية',
            'item' => route('frontend.home'),
        ],
        [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => 'ياسين جوخدار',
            'item' => $canonicalUrl,
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
    </script>
    <script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Person',
    'name' => 'ياسين جوخدار',
    'alternateName' => 'Yasin Jokhadar',
    'url' => $canonicalUrl,
    'image' => $ogImage,
    'jobTitle' => 'مؤسس ومدير تقني',
    'worksFor' => [
        '@type' => 'Organization',
        'name' => 'أكاديمية كلاودسوفت',
        'url' => route('frontend.home'),
    ],
    'description' => $pageDescription,
    'knowsAbout' => [
        'Frontend',
        'Backend',
        'Laravel',
        'PHP',
        'Vue.js',
        'React',
        'DevOps',
        'Docker',
        'Cybersecurity',
        'OWASP',
        'أنظمة إدارة التعلم',
        'التدريب التقني',
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
    </script>
@endpush

@section('content')
<div class="founder-page">

    {{-- Hero: brand name first, one supporting line, one CTA, full-bleed portrait --}}
    <section class="founder-hero" aria-label="مقدمة ياسين جوخدار">
        <div class="container founder-hero-inner">
            <h1 class="founder-brand">ياسين جوخدار</h1>
            <p class="founder-tagline">
                مؤسس أكاديمية كلاودسوفت — بناء منصات تعليمية وحلول برمجية عملية تربط التقنية بالتدريب.
            </p>
            <a href="{{ route('frontend.contact') }}" class="founder-cta">
                تواصل معي
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
            </a>
        </div>
    </section>

    {{-- Experience --}}
    <section class="founder-section" id="experience" aria-labelledby="founder-exp-title">
        <div class="container">
            <div class="founder-section-head animate-on-scroll">
                <span class="section-badge">المسيرة</span>
                <h2 id="founder-exp-title">الخبرات المهنية</h2>
                <p>نص افتراضي مؤقت — يُستبدل بالمسيرة الفعلية بعد اعتماد التصميم.</p>
            </div>
            <ul class="founder-exp-list animate-on-scroll">
                <li>
                    <div class="founder-exp-role">مؤسس ومدير تقني — أكاديمية كلاودسوفت</div>
                    <div class="founder-exp-meta">٢٠٢٠ — حتى الآن · نص وهمي</div>
                    <p class="founder-exp-desc">
                        قيادة تطوير منصة تعليمية متكاملة، تصميم دورات عملية، وبناء حلول برمجية للعملاء والمتدربين.
                        هذا الوصف مؤقت ويمكن استبداله بتفاصيل أدق لاحقاً.
                    </p>
                </li>
                <li>
                    <div class="founder-exp-role">مطور ويب وحلول خلفية</div>
                    <div class="founder-exp-meta">٢٠١٧ — ٢٠٢٠ · نص وهمي</div>
                    <p class="founder-exp-desc">
                        تطوير تطبيقات ويب وأنظمة إدارة محتوى وواجهات برمجة باستخدام PHP وأطر عمل حديثة.
                        مشروع نموذجي وهمي لأغراض العرض فقط.
                    </p>
                </li>
                <li>
                    <div class="founder-exp-role">مدرب تقني مستقل</div>
                    <div class="founder-exp-meta">٢٠١٦ — حتى الآن · نص وهمي</div>
                    <p class="founder-exp-desc">
                        تقديم ورش ودورات في تطوير الويب وقواعد البيانات وأساسيات هندسة البرمجيات لمجموعات محلية وأونلاين.
                    </p>
                </li>
            </ul>
        </div>
    </section>

    {{-- Education --}}
    <section class="founder-section founder-section--alt" id="education" aria-labelledby="founder-edu-title">
        <div class="container">
            <div class="founder-section-head animate-on-scroll">
                <span class="section-badge">التعليم</span>
                <h2 id="founder-edu-title">الدراسة والمؤهلات</h2>
                <p>مؤهلات افتراضية للعرض — تُحدَّث بعد اعتماد المحتوى النهائي.</p>
            </div>
            <div class="founder-edu-grid animate-on-scroll">
                <div class="founder-edu-item">
                    <h3>بكالوريوس في علوم الحاسوب</h3>
                    <div class="founder-exp-meta">جامعة افتراضية · نص وهمي</div>
                    <p class="founder-exp-desc">تخصص في هندسة البرمجيات وقواعد البيانات. تفاصيل الدراسة هنا مؤقتة وقابلة للاستبدال.</p>
                </div>
                <div class="founder-edu-item">
                    <h3>شهادات ودورات متخصصة</h3>
                    <div class="founder-exp-meta">مسارات تقنية · نص وهمي</div>
                    <p class="founder-exp-desc">دورات في Laravel، الحوسبة السحابية، وأمن التطبيقات. قائمة الشهادات الفعلية تُضاف لاحقاً.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Tools by domain --}}
    @php
        $techStacks = [
            [
                'id' => 'frontend',
                'title' => 'الواجهة الأمامية — Frontend',
                'desc' => 'بناء واجهات عربية متجاوبة وتجارب مستخدم سريعة.',
                'icon' => 'fas fa-desktop',
                'items' => [
                    ['name' => 'HTML5', 'icon' => 'fab fa-html5'],
                    ['name' => 'CSS3', 'icon' => 'fab fa-css3-alt'],
                    ['name' => 'JavaScript', 'icon' => 'fab fa-js'],
                    ['name' => 'TypeScript', 'icon' => 'fas fa-code'],
                    ['name' => 'Vue.js', 'icon' => 'fab fa-vuejs'],
                    ['name' => 'React', 'icon' => 'fab fa-react'],
                    ['name' => 'Bootstrap', 'icon' => 'fab fa-bootstrap'],
                    ['name' => 'Tailwind CSS', 'icon' => 'fas fa-wind'],
                    ['name' => 'Sass', 'icon' => 'fab fa-sass'],
                    ['name' => 'Blade / Alpine', 'icon' => 'fas fa-layer-group'],
                    ['name' => 'Responsive UI', 'icon' => 'fas fa-mobile-screen'],
                    ['name' => 'RTL / UX', 'icon' => 'fas fa-language'],
                ],
            ],
            [
                'id' => 'backend',
                'title' => 'الخلفية — Backend',
                'desc' => 'واجهات برمجة، قواعد بيانات، ومنطق أعمال للمنصات التعليمية.',
                'icon' => 'fas fa-server',
                'items' => [
                    ['name' => 'PHP', 'icon' => 'fab fa-php'],
                    ['name' => 'Laravel', 'icon' => 'fab fa-laravel'],
                    ['name' => 'MySQL', 'icon' => 'fas fa-database'],
                    ['name' => 'PostgreSQL', 'icon' => 'fas fa-database'],
                    ['name' => 'Redis', 'icon' => 'fas fa-bolt'],
                    ['name' => 'REST APIs', 'icon' => 'fas fa-plug'],
                    ['name' => 'Eloquent ORM', 'icon' => 'fas fa-table'],
                    ['name' => 'Queues / Jobs', 'icon' => 'fas fa-list-check'],
                    ['name' => 'Sanctum / Auth', 'icon' => 'fas fa-key'],
                    ['name' => 'Node.js', 'icon' => 'fab fa-node-js'],
                    ['name' => 'WhatsApp API', 'icon' => 'fab fa-whatsapp'],
                    ['name' => 'LMS Architecture', 'icon' => 'fas fa-graduation-cap'],
                ],
            ],
            [
                'id' => 'devops',
                'title' => 'التشغيل والنشر — DevOps',
                'desc' => 'نشر، مراقبة، وأتمتة بيئات التطوير والإنتاج.',
                'icon' => 'fas fa-gears',
                'items' => [
                    ['name' => 'Linux', 'icon' => 'fab fa-linux'],
                    ['name' => 'Docker', 'icon' => 'fab fa-docker'],
                    ['name' => 'Nginx', 'icon' => 'fas fa-globe'],
                    ['name' => 'Git', 'icon' => 'fab fa-git-alt'],
                    ['name' => 'GitHub Actions', 'icon' => 'fab fa-github'],
                    ['name' => 'CI / CD', 'icon' => 'fas fa-infinity'],
                    ['name' => 'Coolify', 'icon' => 'fas fa-cloud'],
                    ['name' => 'Cloudflare', 'icon' => 'fab fa-cloudflare'],
                    ['name' => 'SSL / TLS', 'icon' => 'fas fa-lock'],
                    ['name' => 'Monitoring', 'icon' => 'fas fa-chart-line'],
                    ['name' => 'Backups', 'icon' => 'fas fa-hard-drive'],
                    ['name' => 'DNS / Domains', 'icon' => 'fas fa-network-wired'],
                ],
            ],
            [
                'id' => 'security',
                'title' => 'الأمن السيبراني — Cybersecurity',
                'desc' => 'حماية التطبيقات، الهوية، والبيانات وفق ممارسات أمنية عملية.',
                'icon' => 'fas fa-shield-halved',
                'items' => [
                    ['name' => 'OWASP Top 10', 'icon' => 'fas fa-shield'],
                    ['name' => 'XSS / CSRF', 'icon' => 'fas fa-bug'],
                    ['name' => 'SQL Injection', 'icon' => 'fas fa-syringe'],
                    ['name' => 'Auth & Sessions', 'icon' => 'fas fa-user-shield'],
                    ['name' => 'Hashing / Encryption', 'icon' => 'fas fa-fingerprint'],
                    ['name' => 'HTTPS / HSTS', 'icon' => 'fas fa-lock'],
                    ['name' => 'WAF / Firewall', 'icon' => 'fas fa-fire'],
                    ['name' => 'Rate Limiting', 'icon' => 'fas fa-gauge-high'],
                    ['name' => '2FA / MFA', 'icon' => 'fas fa-mobile-screen-button'],
                    ['name' => 'Secure Headers', 'icon' => 'fas fa-file-shield'],
                    ['name' => 'Access Control', 'icon' => 'fas fa-users-gear'],
                    ['name' => 'Audit Logging', 'icon' => 'fas fa-clipboard-list'],
                ],
            ],
        ];
    @endphp

    <section class="founder-section" id="tools" aria-labelledby="founder-tools-title">
        <div class="container">
            <div class="founder-section-head animate-on-scroll">
                <span class="section-badge">الأدوات</span>
                <h2 id="founder-tools-title">التقنيات والأدوات</h2>
                <p>تفصيل حسب المجال: واجهة، خلفية، DevOps، وأمن سيبراني — مع أيقونة لكل تقنية (محتوى قابل للاستبدال).</p>
            </div>

            <div class="founder-stacks animate-on-scroll">
                @foreach ($techStacks as $stack)
                    <div class="founder-stack" id="tech-{{ $stack['id'] }}">
                        <div class="founder-stack-head">
                            <span class="founder-stack-icon" aria-hidden="true"><i class="{{ $stack['icon'] }}"></i></span>
                            <div>
                                <h3>{{ $stack['title'] }}</h3>
                                <p>{{ $stack['desc'] }}</p>
                            </div>
                        </div>
                        <ul class="founder-tools" role="list">
                            @foreach ($stack['items'] as $tool)
                                <li class="founder-tool" role="listitem">
                                    <i class="{{ $tool['icon'] }}" aria-hidden="true"></i>
                                    <span>{{ $tool['name'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Work cards --}}
    <section class="founder-section founder-section--alt" id="works" aria-labelledby="founder-works-title">
        <div class="container">
            <div class="founder-section-head animate-on-scroll">
                <span class="section-badge">الأعمال</span>
                <h2 id="founder-works-title">أعمال مختارة</h2>
                <p>بطاقات مشاريع وهمية للتصميم — استبدل العناوين والصور والروابط عند الجاهزية.</p>
            </div>
            <div class="founder-works animate-on-scroll">
                <article class="founder-work">
                    <div class="founder-work-media">
                        <img src="{{ asset('frontend2/assets/images/founder/work-1.svg') }}" alt="منصة تعليمية — صورة وهمية" width="800" height="500" loading="lazy">
                    </div>
                    <div class="founder-work-body">
                        <h3>منصة أكاديمية كلاودسوفت</h3>
                        <p>نظام إدارة تعلم ودورات ومجموعات — وصف مشروع وهمي للعرض فقط.</p>
                    </div>
                </article>
                <article class="founder-work">
                    <div class="founder-work-media">
                        <img src="{{ asset('frontend2/assets/images/founder/work-2.svg') }}" alt="واجهات برمجة — صورة وهمية" width="800" height="500" loading="lazy">
                    </div>
                    <div class="founder-work-body">
                        <h3>تكامل واتساب وإشعارات</h3>
                        <p>ربط أنظمة التسجيل بإشعارات فورية — محتوى مؤقت قابل للاستبدال.</p>
                    </div>
                </article>
                <article class="founder-work">
                    <div class="founder-work-media">
                        <img src="{{ asset('frontend2/assets/images/founder/work-3.svg') }}" alt="تطبيق موبايل — صورة وهمية" width="800" height="500" loading="lazy">
                    </div>
                    <div class="founder-work-body">
                        <h3>واجهات ويب متجاوبة</h3>
                        <p>تصميم وتنفيذ واجهات عربية RTL لتجارب تعليمية — نص وهمي.</p>
                    </div>
                </article>
                <article class="founder-work">
                    <div class="founder-work-media">
                        <img src="{{ asset('frontend2/assets/images/founder/work-4.svg') }}" alt="DevOps — صورة وهمية" width="800" height="500" loading="lazy">
                    </div>
                    <div class="founder-work-body">
                        <h3>نشر وتشغيل المنصات</h3>
                        <p>إعداد بيئات الإنتاج والمراقبة — تفاصيل المشروع تُحدَّث لاحقاً.</p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    {{-- Gallery --}}
    <section class="founder-section" id="gallery" aria-labelledby="founder-gallery-title">
        <div class="container">
            <div class="founder-section-head animate-on-scroll">
                <span class="section-badge">المعرض</span>
                <h2 id="founder-gallery-title">معرض الصور</h2>
                <p>صور وهمية — اضغط للتكبير. استبدل الملفات في مجلد الصور عند الاعتماد.</p>
            </div>
            <div class="founder-gallery animate-on-scroll">
                @for ($i = 1; $i <= 6; $i++)
                    <a href="{{ asset('frontend2/assets/images/founder/gallery-'.$i.'.svg') }}"
                       data-founder-lightbox
                       aria-label="فتح صورة المعرض {{ $i }}">
                        <img src="{{ asset('frontend2/assets/images/founder/gallery-'.$i.'.svg') }}"
                             alt="صورة وهمية من المعرض رقم {{ $i }}"
                             width="600" height="450" loading="lazy">
                    </a>
                @endfor
            </div>
        </div>
    </section>

    <div class="founder-lightbox" id="founderLightbox" role="dialog" aria-modal="true" aria-label="عرض الصورة" hidden>
        <button type="button" class="founder-lightbox-close" id="founderLightboxClose" aria-label="إغلاق">&times;</button>
        <img src="" alt="">
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var box = document.getElementById('founderLightbox');
    var img = box && box.querySelector('img');
    var closeBtn = document.getElementById('founderLightboxClose');
    if (!box || !img) return;

    function openLb(src, alt) {
        img.src = src;
        img.alt = alt || '';
        box.hidden = false;
        box.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeLb() {
        box.classList.remove('is-open');
        box.hidden = true;
        img.src = '';
        document.body.style.overflow = '';
    }

    document.querySelectorAll('[data-founder-lightbox]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            var thumb = a.querySelector('img');
            openLb(a.getAttribute('href'), thumb ? thumb.alt : '');
        });
    });

    if (closeBtn) closeBtn.addEventListener('click', closeLb);
    box.addEventListener('click', function (e) {
        if (e.target === box) closeLb();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && box.classList.contains('is-open')) closeLb();
    });
})();
</script>
@endpush
