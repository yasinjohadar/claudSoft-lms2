<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Certificate Prefix
    |--------------------------------------------------------------------------
    |
    | البادئة المستخدمة لرقم الشهادة (مثال: CERT-2025-00001)
    |
    */
    'certificate_prefix' => env('CERTIFICATE_PREFIX', 'CERT'),

    /*
    |--------------------------------------------------------------------------
    | Auto Issue Certificates
    |--------------------------------------------------------------------------
    |
    | إصدار الشهادات تلقائياً عند إكمال الكورس
    |
    */
    'auto_issue' => env('CERTIFICATE_AUTO_ISSUE', true),

    /*
    |--------------------------------------------------------------------------
    | QR Code Settings
    |--------------------------------------------------------------------------
    |
    | إعدادات QR Code
    |
    */
    'qr_code' => [
        'enabled' => env('CERTIFICATE_QR_ENABLED', true),
        'size' => env('CERTIFICATE_QR_SIZE', 300),
        'margin' => env('CERTIFICATE_QR_MARGIN', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | PDF Settings
    |--------------------------------------------------------------------------
    |
    | إعدادات PDF
    |
    */
    'pdf' => [
        'default_orientation' => 'landscape', // landscape or portrait
        'default_page_size' => 'A4',
        'dpi' => 96,
    ],

    /*
    |--------------------------------------------------------------------------
    | Certificate Language
    |--------------------------------------------------------------------------
    |
    | اللغة الافتراضية للشهادات
    |
    */
    'default_language' => 'ar',

    /*
    |--------------------------------------------------------------------------
    | Verification URL
    |--------------------------------------------------------------------------
    |
    | رابط التحقق من الشهادة
    |
    */
    'verification_url' => env('APP_URL') . '/verify-certificate',

    /*
    |--------------------------------------------------------------------------
    | Storage Paths
    |--------------------------------------------------------------------------
    |
    | مسارات التخزين
    |
    */
    'storage' => [
        'certificates' => 'certificates/pdf',
        'templates' => 'certificates/templates',
        'qr_codes' => 'certificates/qr-codes',
        'images' => 'certificates/images',
    ],

    /*
    |--------------------------------------------------------------------------
    | Fonts
    |--------------------------------------------------------------------------
    |
    | الخطوط المستخدمة
    |
    */
    'fonts' => [
        'arabic' => public_path('fonts/NotoKufiArabic-Regular.ttf'),
        'arabic_bold' => public_path('fonts/NotoKufiArabic-Bold.ttf'),
        'english' => public_path('fonts/Roboto-Regular.ttf'),
        'english_bold' => public_path('fonts/Roboto-Bold.ttf'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Expiry Settings
    |--------------------------------------------------------------------------
    |
    | إعدادات انتهاء الصلاحية
    |
    */
    'expiry' => [
        'enabled' => env('CERTIFICATE_EXPIRY_ENABLED', false),
        'default_months' => env('CERTIFICATE_EXPIRY_MONTHS', 12),
    ],

    /*
    |--------------------------------------------------------------------------
    | Watermark Settings
    |--------------------------------------------------------------------------
    |
    | إعدادات العلامة المائية
    |
    */
    'watermark' => [
        'enabled' => env('CERTIFICATE_WATERMARK_ENABLED', false),
        'text' => env('CERTIFICATE_WATERMARK_TEXT', 'ORIGINAL'),
        'opacity' => 0.1,
    ],
];
