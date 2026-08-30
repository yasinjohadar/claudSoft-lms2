@php
    /*
     * نفس ودجات لوحة الأدمن بالضبط (نمط Hr-System).
     * الغلاف .hr-stat-widgets إلزامي — انظر التعليق في portal-kpi.css
     *
     * الثيمات المتاحة: blue · green · orange · purple · gold · silver
     * الذهبي/الفضي مخصّصان لبطاقة «نوع الحساب» — والذهبي يحمل لمعة متحركة.
     */
    $tier = $accountTier ?? 'silver';
    $isGoldAccount = $tier === 'gold';

    $statWidgets = [
        [
            'theme' => $isGoldAccount ? 'gold' : 'silver',
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

<x-stat-widgets :items="$statWidgets" cols="col" rowClass="row row-cols-xl-5 row-cols-lg-2 row-cols-md-2 row-cols-1 g-3 mb-4" />
