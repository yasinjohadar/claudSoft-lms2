@php
    /*
     * بطاقات الاختصارات بنمط Hr-System — نفس مكوّن لوحة الأدمن.
     * كل عنصر يحمل ثيمه وأيقونته من Remix مباشرة (لا خرائط تحويل).
     *
     * الثيمات المتاحة: blue green orange purple red teal cyan pink indigo gold yellow brown
     */
    $quickLinks = [
        ['route' => 'student.courses.my-courses', 'icon' => 'ri-book-open-line', 'theme' => 'blue', 'title' => 'كورساتي', 'subtitle' => 'الكورسات المسجّلة'],
        ['route' => 'student.assignments.index', 'icon' => 'ri-checkbox-line', 'theme' => 'gold', 'title' => 'واجباتي', 'subtitle' => 'التسليم والمتابعة'],
        ['route' => 'student.question-module.stats.index', 'icon' => 'ri-bar-chart-box-line', 'theme' => 'cyan', 'title' => 'إحصائيات الاختبارات', 'subtitle' => 'الأداء والمحاولات'],
        ['route' => 'student.training-camps.index', 'icon' => 'ri-flag-2-line', 'theme' => 'green', 'title' => 'المعسكرات التدريبية', 'subtitle' => 'التسجيل والمتابعة'],
        ['route' => 'student.groups.index', 'icon' => 'ri-team-line', 'theme' => 'teal', 'title' => 'المجموعات', 'subtitle' => 'مجموعاتي الدراسية'],
        ['route' => 'student.invoices.index', 'icon' => 'ri-file-list-3-line', 'theme' => 'red', 'title' => 'فواتيري', 'subtitle' => 'الفواتير والمدفوعات'],
        ['route' => 'gamification.badges.index', 'icon' => 'ri-award-line', 'theme' => 'yellow', 'title' => 'شاراتي', 'subtitle' => 'الإنجازات المكتسبة'],
        ['route' => 'gamification.leaderboards.index', 'icon' => 'ri-bar-chart-2-line', 'theme' => 'purple', 'title' => 'لوحة المتصدرين', 'subtitle' => 'ترتيب الطلاب'],
        ['route' => 'student.progress.overview', 'icon' => 'ri-line-chart-line', 'theme' => 'indigo', 'title' => 'تقدمي في الكورسات', 'subtitle' => 'نسب الإنجاز'],
        ['route' => 'gamification.dashboard', 'icon' => 'ri-flashlight-line', 'theme' => 'orange', 'title' => 'عالم الإنجاز', 'subtitle' => 'النقاط والتحديات'],
        ['route' => 'student.external-resources.index', 'icon' => 'ri-links-line', 'theme' => 'brown', 'title' => 'الموارد الخارجية', 'subtitle' => 'روابط ومراجع'],
        ['route' => 'student.gifts.index', 'icon' => 'ri-gift-line', 'theme' => 'pink', 'title' => 'هدايا الأكاديمية', 'subtitle' => 'هدايا وموارد من الأكاديمية'],
    ];

    if (! ($studentTelegram['linked'] ?? false)) {
        $quickLinks[] = [
            'route' => 'student.telegram.link',
            'icon' => 'ri-send-plane-line',
            'theme' => 'cyan',
            'title' => 'ربط Telegram',
            'subtitle' => 'استلم الإشعارات على تيليجرام',
        ];
    }
@endphp

<div class="shortcuts-section mb-4">
    <div class="shortcuts-section-header">
        <span class="shortcuts-section-icon"><i class="ri-flashlight-line"></i></span>
        <div>
            <h5 class="dashboard-section-title mb-0">روابط سريعة</h5>
            <p class="text-muted fs-12 mb-0">الوصول السريع لأهم صفحات التعلم والمتابعة</p>
        </div>
    </div>
    <div class="row g-3 shortcuts-grid">
        @foreach ($quickLinks as $index => $link)
            @php
                $href = (!empty($link['route']) && Route::has($link['route'])) ? route($link['route']) : null;
            @endphp
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                <a @if($href) href="{{ $href }}" @endif
                   class="shortcut-card shortcut-theme-{{ $link['theme'] }}"
                   style="--shortcut-delay: {{ $index * 0.05 }}s">
                    <span class="shortcut-shine"></span>
                    <span class="shortcut-accent"></span>
                    <span class="shortcut-icon-wrap">
                        <span class="shortcut-icon-ring"></span>
                        <span class="shortcut-icon">
                            <i class="{{ $link['icon'] }}"></i>
                        </span>
                    </span>
                    <span class="shortcut-title">{{ $link['title'] }}</span>
                    <span class="shortcut-desc">{{ $link['subtitle'] }}</span>
                    <span class="shortcut-arrow"><i class="ri-arrow-left-s-line"></i></span>
                </a>
            </div>
        @endforeach
    </div>
</div>
