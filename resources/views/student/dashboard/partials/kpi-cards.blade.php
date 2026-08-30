@php
    /*
     * نفس ودجات لوحة الأدمن بالضبط (نمط Hr-System).
     * الغلاف .hr-stat-widgets إلزامي — انظر التعليق في portal-kpi.css
     *
     * الثيمات المتاحة: blue · green · orange · purple
     * نوع الحساب يستخدم purple للذهبي و blue للفضي لعدم وجود ثيم ذهبي.
     */
    $tier = $accountTier ?? 'silver';
    $isGoldAccount = $tier === 'gold';

    $statWidgets = [
        [
            'theme' => $isGoldAccount ? 'orange' : 'blue',
            'icon' => $isGoldAccount ? 'ri-vip-crown-fill' : 'ri-medal-fill',
            'title' => 'نوع الحساب',
            'value' => $isGoldAccount ? 'ذهبي' : 'فضي',
            'value_text' => true,
            'subtext' => $isGoldAccount ? 'منضم لمجموعة معسكر مدفوعة' : 'مجموعة عادية أو غير منضم',
            'route' => 'student.groups.index',
        ],
        [
            'theme' => 'blue',
            'icon' => 'ri-book-open-line',
            'title' => 'إجمالي الكورسات المسجلة',
            'value' => $courseStats['total_courses'] ?? 0,
            'subtext' => ($courseStats['completed'] ?? 0) . ' مكتملة',
            'route' => 'student.courses.my-courses',
        ],
        [
            'theme' => 'purple',
            'icon' => 'ri-award-line',
            'title' => 'متوسط درجات الاختبارات',
            'value' => $questionModuleStats['average_score'] ?? 0,
            'subtext' => 'من المحاولات المكتملة',
            'route' => 'student.question-module.stats.index',
            'suffix' => '%',
        ],
        [
            'theme' => 'green',
            'icon' => 'ri-checkbox-circle-line',
            'title' => 'اختبارات ناجحة',
            'value' => $questionModuleStats['passed_attempts'] ?? 0,
            'subtext' => 'محاولات اجتازت النجاح',
            'route' => 'student.question-module.stats.index',
        ],
        [
            'theme' => 'orange',
            'icon' => 'ri-file-list-3-line',
            'title' => 'إجمالي المحاولات',
            'value' => $questionModuleStats['total_attempts'] ?? 0,
            'subtext' => 'محاولات اختبار مكتملة',
            'route' => 'student.question-module.stats.index',
        ],
    ];
@endphp

<div class="row row-cols-xl-5 row-cols-lg-2 row-cols-md-2 row-cols-1 g-3 mb-4 hr-stat-widgets">
    @foreach ($statWidgets as $index => $widget)
        <div class="col">
            @php
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
                            @if (!empty($widget['value_text']))
                                <span class="stat-value">{{ $widget['value'] }}</span>
                            @else
                                <span class="stat-value"
                                      data-countup="{{ $widget['value'] }}"
                                      @if(!empty($widget['suffix'])) data-countup-suffix="{{ $widget['suffix'] }}" @endif>0</span>
                            @endif
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
