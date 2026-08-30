@php
    /*
     * بطاقات الاختصارات بنمط Hr-System: لمعة + شريط لوني + حلقة حول الأيقونة
     * + سهم ينزلق عند التمرير + موجة (ripple) عند النقر.
     *
     * $quickLinks يأتي من DashboardController وفيه 'color' من مسمّيات القالب
     * (primary/danger/teal/orange...) بينما ثيمات هذا المكوّن مسمّيات ألوان
     * صريحة — لذا الخريطة أدناه. أي لون غير معروف يسقط إلى blue.
     *
     * الثيمات المتاحة: blue green orange purple red teal cyan pink indigo gold yellow brown
     */
    $themeMap = [
        'primary' => 'blue',
        'secondary' => 'indigo',
        'success' => 'green',
        'danger' => 'red',
        'warning' => 'gold',
        'info' => 'cyan',
        'teal' => 'teal',
        'orange' => 'orange',
        'pink' => 'pink',
        'indigo' => 'indigo',
        'purple' => 'purple',
        'yellow' => 'yellow',
    ];

    // أيقونات Feather القديمة (fe-*) مقابل Remix المستخدمة في هذا المكوّن
    $iconMap = [
        'fe-users' => 'ri-team-line',
        'fe-book-open' => 'ri-book-open-line',
        'fe-user-check' => 'ri-user-follow-line',
        'fe-award' => 'ri-award-line',
        'fe-file-text' => 'ri-file-list-3-line',
        'fe-edit' => 'ri-edit-box-line',
        'fe-help-circle' => 'ri-question-line',
        'fe-file' => 'ri-file-text-line',
        'fe-credit-card' => 'ri-bank-card-line',
        'fe-globe' => 'ri-global-line',
        'fe-mail' => 'ri-mail-line',
        'fe-zap' => 'ri-flashlight-line',
        'fe-git-commit' => 'ri-git-commit-line',
    ];
@endphp

<div class="shortcuts-section mb-4">
    <div class="shortcuts-section-header">
        <span class="shortcuts-section-icon"><i class="ri-flashlight-line"></i></span>
        <div>
            <h5 class="dashboard-section-title mb-0">اختصارات سريعة</h5>
            <p class="text-muted fs-12 mb-0">الوصول السريع لأهم صفحات إدارة المنصة</p>
        </div>
    </div>
    <div class="row g-3 shortcuts-grid">
        @foreach ($quickLinks as $index => $link)
            @php
                // راوت غير موجود يُعرض بلا رابط بدل أن يُسقط الصفحة باستثناء
                $href = (!empty($link['route']) && Route::has($link['route'])) ? route($link['route']) : null;
                $theme = $themeMap[$link['color'] ?? ''] ?? 'blue';
                $icon = $iconMap[$link['icon'] ?? ''] ?? 'ri-apps-2-line';
            @endphp
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                <a @if($href) href="{{ $href }}" @endif
                   class="shortcut-card shortcut-theme-{{ $theme }}"
                   style="--shortcut-delay: {{ $index * 0.05 }}s">
                    <span class="shortcut-shine"></span>
                    <span class="shortcut-accent"></span>
                    <span class="shortcut-icon-wrap">
                        <span class="shortcut-icon-ring"></span>
                        <span class="shortcut-icon">
                            <i class="{{ $icon }}"></i>
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
