@php
    /*
     * نفس ودجات لوحة الأدمن بالضبط (نمط Hr-System).
     * الغلاف .hr-stat-widgets إلزامي — انظر التعليق في portal-kpi.css
     *
     * الثيمات المتاحة: blue · green · orange · purple · gold · silver
     * الذهبي/الفضي مخصّصان لبطاقة «نوع الحساب» — والذهبي يحمل لمعة متحركة.
     *
     * أربع بطاقات لا خمس: الخمس كانت تضيق على الشاشات المتوسطة فتُبتر
     * العناوين. «متوسط درجات الاختبارات» متاح ضمن صفحة إحصائيات الاختبارات.
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
            // نصوص قصيرة: السطر الوصفي مقصور على سطر واحد داخل البطاقة
            'subtext' => $isGoldAccount ? 'معسكر مدفوع' : 'مجموعة عادية',
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

<x-stat-widgets :items="$statWidgets" />
