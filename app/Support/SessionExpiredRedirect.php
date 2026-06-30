<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class SessionExpiredRedirect
{
    /**
     * @return array{url: string, label: string, secondary_url: string, secondary_label: string}
     */
    public static function resolve(Request $request): array
    {
        $referer = (string) $request->headers->get('referer', '');
        $path = strtolower((string) parse_url($referer, PHP_URL_PATH));

        $candidates = [
            'register' => [
                'match' => ['register', 'phone-otp'],
                'url' => self::routeUrl('register'),
                'label' => 'العودة لصفحة التسجيل',
                'secondary_url' => self::routeUrl('login'),
                'secondary_label' => 'تسجيل الدخول',
            ],
            'phone-login' => [
                'match' => ['phone-login'],
                'url' => self::routeUrl('phone-login'),
                'label' => 'إعادة تسجيل الدخول بالهاتف',
                'secondary_url' => self::routeUrl('login'),
                'secondary_label' => 'تسجيل الدخول بالبريد',
            ],
            'forgot-password' => [
                'match' => ['forgot-password', 'reset-password'],
                'url' => self::routeUrl('password.request'),
                'label' => 'العودة لاستعادة كلمة المرور',
                'secondary_url' => self::routeUrl('login'),
                'secondary_label' => 'تسجيل الدخول',
            ],
            'login' => [
                'match' => ['login', 'simple-login', 'local-dev-login'],
                'url' => self::routeUrl('login'),
                'label' => 'تحديث الصفحة وتسجيل الدخول',
                'secondary_url' => self::routeUrl('register'),
                'secondary_label' => 'إنشاء حساب جديد',
            ],
        ];

        foreach ($candidates as $candidate) {
            foreach ($candidate['match'] as $segment) {
                if ($segment !== '' && str_contains($path, $segment)) {
                    return [
                        'url' => self::freshUrl($candidate['url']),
                        'label' => $candidate['label'],
                        'secondary_url' => self::freshUrl($candidate['secondary_url']),
                        'secondary_label' => $candidate['secondary_label'],
                    ];
                }
            }
        }

        return [
            'url' => self::freshUrl(self::routeUrl('login')),
            'label' => 'تحديث الصفحة وتسجيل الدخول',
            'secondary_url' => self::freshUrl(self::routeUrl('register')),
            'secondary_label' => 'إنشاء حساب جديد',
        ];
    }

    public static function freshUrl(string $url): string
    {
        $separator = str_contains($url, '?') ? '&' : '?';

        return $url.$separator.'session_expired=1&_='.time();
    }

    protected static function routeUrl(string $name): string
    {
        if (! Route::has($name)) {
            return url('/');
        }

        return route($name, absolute: false);
    }
}
