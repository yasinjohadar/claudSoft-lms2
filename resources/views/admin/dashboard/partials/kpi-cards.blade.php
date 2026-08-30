@php
    /*
     * ودجات الإحصاء بنمط Hr-System بالضبط: بطاقة بطبقات زخرفية
     * (لمعة + شبكة + فقاعات + توهّج) وأيقونة داخل حلقة نابضة.
     *
     * الغلاف .hr-stat-widgets إلزامي — كل تنسيقات portal-kpi.css معزولة
     * تحته لأن stat-value / stat-label مستخدمان بمعانٍ أخرى في المشروع.
     *
     * الثيمات المتاحة: blue · green · orange · purple
     */
    $statWidgets = [
        [
            'theme' => 'blue',
            'icon' => 'ri-team-line',
            'title' => 'إجمالي الطلاب',
            'value' => $userStats['students'] ?? 0,
            'subtext' => number_format($userStats['active_today'] ?? 0) . ' نشط اليوم',
            'route' => 'users.index',
        ],
        [
            'theme' => 'green',
            'icon' => 'ri-user-follow-line',
            'title' => 'الالتحاقات النشطة',
            'value' => $courseStats['active_enrollments'] ?? 0,
            'subtext' => 'إجمالي: ' . number_format($courseStats['total_enrollments'] ?? 0) . ' التحاق',
            'route' => 'enrollments.all',
        ],
        [
            'theme' => 'purple',
            'icon' => 'ri-book-open-line',
            'title' => 'الكورسات النشطة',
            'value' => $courseStats['published_courses'] ?? 0,
            'subtext' => 'من أصل ' . number_format($courseStats['total_courses'] ?? 0) . ' كورس',
            'route' => 'courses.index',
        ],
        [
            'theme' => 'orange',
            'icon' => 'ri-award-line',
            'title' => 'الشهادات الصادرة',
            'value' => $learningStats['certificates_issued'] ?? 0,
            'subtext' => 'صادرة اليوم: ' . number_format($todayStats['certificates_today'] ?? 0),
            'route' => 'admin.certificates.index',
        ],
    ];
@endphp

<div class="row g-3 mb-4 hr-stat-widgets">
    @foreach ($statWidgets as $index => $widget)
        <div class="col-xl-3 col-lg-6 col-md-6">
            @php
                // راوت غير موجود يُعرض بلا رابط بدل أن يُسقط الصفحة باستثناء
                $href = (!empty($widget['route']) && Route::has($widget['route'])) ? route($widget['route']) : null;
            @endphp
            <a @if($href) href="{{ $href }}" @endif
               class="dashboard-stat-link"
               style="--card-delay: {{ $index * 0.1 }}s">
                <div class="dashboard-stat-card dashboard-stat-{{ $widget['theme'] }}">
                    <div class="stat-card-shine"></div>
                    <div class="stat-card-mesh"></div>
                    <div class="stat-card-bubble stat-card-bubble-1"></div>
                    <div class="stat-card-bubble stat-card-bubble-2"></div>
                    <div class="stat-card-bubble stat-card-bubble-3"></div>
                    <div class="stat-card-glow"></div>
                    <div class="stat-card-body">
                        <div class="stat-card-content">
                            <span class="stat-label">{{ $widget['title'] }}</span>
                            <span class="stat-value" data-countup="{{ $widget['value'] }}">0</span>
                            <span class="stat-subtext">{{ $widget['subtext'] }}</span>
                        </div>
                        <div class="stat-icon-wrap">
                            <span class="stat-icon-ring"></span>
                            <span class="stat-icon-circle">
                                <i class="{{ $widget['icon'] }}"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>
