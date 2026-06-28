<?php

return [
    'themes' => [
        'classic' => [
            'label' => 'كلاسيكي',
            'accent_default' => '#3b82f6',
        ],
        'gradient' => [
            'label' => 'متدرج',
            'accent_default' => '#8b5cf6',
        ],
        'minimal' => [
            'label' => 'بسيط',
            'accent_default' => '#0ea5e9',
        ],
        'dark' => [
            'label' => 'داكن',
            'accent_default' => '#6366f1',
        ],
    ],

    'social_platforms' => [
        'facebook' => [
            'default_icon' => 'fab fa-facebook-f',
            'default_label' => 'فيسبوك',
            'url_hint' => 'https://facebook.com/username',
            'brand_color' => '#1877F2',
        ],
        'instagram' => [
            'default_icon' => 'fab fa-instagram',
            'default_label' => 'إنستغرام',
            'url_hint' => 'https://instagram.com/username',
            'brand_gradient' => 'linear-gradient(45deg, #f58529, #dd2a7b, #8134af)',
        ],
        'linkedin' => [
            'default_icon' => 'fab fa-linkedin-in',
            'default_label' => 'لينكدإن',
            'url_hint' => 'https://linkedin.com/in/username',
            'brand_color' => '#0A66C2',
        ],
        'twitter' => [
            'default_icon' => 'fab fa-x-twitter',
            'default_label' => 'X (تويتر)',
            'url_hint' => 'https://x.com/username',
            'brand_color' => '#000000',
        ],
        'github' => [
            'default_icon' => 'fab fa-github',
            'default_label' => 'GitHub',
            'url_hint' => 'https://github.com/username',
            'brand_color' => '#24292f',
        ],
        'youtube' => [
            'default_icon' => 'fab fa-youtube',
            'default_label' => 'يوتيوب',
            'url_hint' => 'https://youtube.com/@channel',
            'brand_color' => '#FF0000',
        ],
        'tiktok' => [
            'default_icon' => 'fab fa-tiktok',
            'default_label' => 'تيك توك',
            'url_hint' => 'https://tiktok.com/@username',
            'brand_color' => '#010101',
        ],
        'whatsapp' => [
            'default_icon' => 'fab fa-whatsapp',
            'default_label' => 'واتساب',
            'url_hint' => 'https://wa.me/9665xxxxxxx',
            'brand_color' => '#25D366',
        ],
        'telegram' => [
            'default_icon' => 'fab fa-telegram',
            'default_label' => 'تلغرام',
            'url_hint' => 'https://t.me/username',
            'brand_color' => '#229ED9',
        ],
        'website' => [
            'default_icon' => 'fas fa-globe',
            'default_label' => 'الموقع',
            'url_hint' => 'https://example.com',
            'brand_color' => '#2563eb',
        ],
        'email' => [
            'default_icon' => 'fas fa-envelope',
            'default_label' => 'البريد',
            'url_hint' => 'mailto:email@example.com',
            'brand_color' => '#EA4335',
        ],
        'custom' => [
            'default_icon' => 'fas fa-link',
            'default_label' => 'رابط',
            'url_hint' => 'https://',
            'brand_color' => null,
        ],
    ],

    'defaults' => [
        'theme' => [
            'preset' => 'classic',
            'accent_color' => '#3b82f6',
            'card_style' => 'rounded',
        ],
    ],
];
