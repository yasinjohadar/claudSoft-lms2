<?php

return [
    'graph_api_version' => env('META_GRAPH_API_VERSION', 'v21.0'),

    'default_currency' => env('META_PIXEL_DEFAULT_CURRENCY', 'SAR'),

    'session_flash_key' => 'meta_pixel_flash_events',

    'events' => [
        'PageView' => [
            'label' => 'PageView',
            'label_ar' => 'زيارة صفحة',
            'description' => 'كل تحميل لصفحة عامة',
            'setting_key' => 'track_page_view',
            'capi' => false,
        ],
        'ViewContent' => [
            'label' => 'ViewContent',
            'label_ar' => 'عرض محتوى',
            'description' => 'كورس، مقال، خدمة، عنّا',
            'setting_key' => 'track_view_content',
            'capi' => false,
        ],
        'Search' => [
            'label' => 'Search',
            'label_ar' => 'بحث',
            'description' => 'بحث المدونة',
            'setting_key' => 'track_search',
            'capi' => false,
        ],
        'Lead' => [
            'label' => 'Lead',
            'label_ar' => 'عميل محتمل',
            'description' => 'إتمام تسجيل الدبلوم',
            'setting_key' => 'track_lead',
            'capi' => true,
        ],
        'Contact' => [
            'label' => 'Contact',
            'label_ar' => 'تواصل',
            'description' => 'إرسال نموذج اتصل بنا',
            'setting_key' => 'track_contact',
            'capi' => true,
        ],
        'LeadStarted' => [
            'label' => 'LeadStarted',
            'label_ar' => 'بدء التسجيل',
            'description' => 'فتح فورم تسجيل الدبلوم',
            'setting_key' => 'track_lead_started',
            'capi' => false,
            'custom' => true,
        ],
    ],
];
