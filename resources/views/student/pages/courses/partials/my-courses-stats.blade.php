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

<x-stat-widgets :items="$statWidgets" />
