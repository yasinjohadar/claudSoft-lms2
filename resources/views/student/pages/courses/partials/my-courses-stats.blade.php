@php
    /*
     * نفس ودجات لوحة الطالب الرئيسية (نمط Hr-System).
     * الغلاف .hr-stat-widgets إلزامي — انظر التعليق في portal-kpi.css
     *
     * الثيمات المتاحة: blue · green · orange · purple
     */
    $statWidgets = [
        [
            'theme' => 'blue',
            'icon' => 'ri-book-open-line',
            'title' => 'إجمالي الكورسات',
            'value' => $stats['total_courses'],
            'subtext' => 'الكورسات المسجّلة',
        ],
        [
            'theme' => 'green',
            'icon' => 'ri-play-circle-line',
            'title' => 'كورسات نشطة',
            'value' => $stats['active_courses'],
            'subtext' => 'قيد الدراسة حالياً',
        ],
        [
            'theme' => 'purple',
            'icon' => 'ri-checkbox-circle-line',
            'title' => 'كورسات مكتملة',
            'value' => $stats['completed_courses'],
            'subtext' => 'أنهيتها بالكامل',
        ],
        [
            'theme' => 'orange',
            'icon' => 'ri-line-chart-line',
            'title' => 'متوسط التقدم',
            'value' => round($stats['average_progress']),
            'suffix' => '%',
            'subtext' => 'عبر كل كورساتك',
        ],
    ];
@endphp

<div class="row g-3 mb-4 hr-stat-widgets">
    @foreach ($statWidgets as $index => $widget)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="dashboard-stat-link" style="--card-delay: {{ $index * 0.1 }}s">
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
                            <span class="stat-value"
                                  data-countup="{{ $widget['value'] }}"
                                  @if(!empty($widget['suffix'])) data-countup-suffix="{{ $widget['suffix'] }}" @endif>0</span>
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
            </div>
        </div>
    @endforeach
</div>
