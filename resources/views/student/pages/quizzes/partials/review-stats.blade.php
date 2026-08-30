@php
    $statWidgets = [
        [
            'theme' => 'blue',
            'icon' => 'ri-list-check-2',
            'title' => 'إجمالي المحاولات',
            'value' => $stats['total_attempts'] ?? 0,
            'subtext' => 'كل محاولاتك',
        ],
        [
            'theme' => 'green',
            'icon' => 'ri-checkbox-circle-line',
            'title' => 'محاولات ناجحة',
            'value' => $stats['passed_attempts'] ?? 0,
            'subtext' => 'اجتازت حد النجاح',
        ],
        [
            'theme' => 'purple',
            'icon' => 'ri-percent-line',
            'title' => 'متوسط النتيجة',
            'value' => round($stats['average_score'] ?? 0, 1),
            'suffix' => '%',
            'decimals' => true,
            'subtext' => 'عبر المحاولات المكتملة',
        ],
        [
            'theme' => 'orange',
            'icon' => 'ri-time-line',
            'title' => 'محاولات مكتملة',
            'value' => $stats['completed_attempts'] ?? 0,
            'subtext' => 'أنهيتها بالكامل',
        ],
    ];
@endphp

<x-stat-widgets :items="$statWidgets" />
