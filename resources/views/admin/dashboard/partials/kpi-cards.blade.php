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

<x-stat-widgets :items="$statWidgets" />
