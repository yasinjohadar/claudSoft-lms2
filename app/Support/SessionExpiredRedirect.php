<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class SessionExpiredRedirect
{
    /**
     * @return array{url: string, label: string, secondary_url: string|null, secondary_label: string|null}
     */
    public static function resolve(Request $request): array
    {
        $referer = (string) $request->headers->get('referer', '');
        $path = strtolower((string) parse_url($referer, PHP_URL_PATH));

        $candidates = [
            'register' => [
                'match' => ['register', 'phone-otp'],
                'url' => self::registrationUrl(),
                'label' => self::registrationEnabled() ? 'العودة لصفحة التسجيل' : 'تحديث الصفحة وتسجيل الدخول',
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
                'secondary_url' => self::registrationEnabled() ? self::routeUrl('register') : self::routeUrl('phone-login'),
                'secondary_label' => self::registrationEnabled() ? 'إنشاء حساب جديد' : 'تسجيل الدخول بالهاتف',
            ],
        ];

        foreach ($candidates as $candidate) {
            foreach ($candidate['match'] as $segment) {
                if ($segment !== '' && str_contains($path, $segment)) {
                    return self::formatCandidate($candidate);
                }
            }
        }

        return self::formatCandidate([
            'url' => self::routeUrl('login'),
            'label' => 'تحديث الصفحة وتسجيل الدخول',
            'secondary_url' => self::registrationEnabled() ? self::routeUrl('register') : self::routeUrl('phone-login'),
            'secondary_label' => self::registrationEnabled() ? 'إنشاء حساب جديد' : 'تسجيل الدخول بالهاتف',
        ]);
    }

    /**
     * @param  array{url: string, label: string, secondary_url: string, secondary_label: string}  $candidate
     * @return array{url: string, label: string, secondary_url: string|null, secondary_label: string|null}
     */
    protected static function formatCandidate(array $candidate): array
    {
        $primaryUrl = self::freshUrl($candidate['url']);
        $secondaryUrl = self::freshUrl($candidate['secondary_url']);

        if ($secondaryUrl === $primaryUrl) {
            return [
                'url' => $primaryUrl,
                'label' => $candidate['label'],
                'secondary_url' => null,
                'secondary_label' => null,
            ];
        }

        return [
            'url' => $primaryUrl,
            'label' => $candidate['label'],
            'secondary_url' => $secondaryUrl,
            'secondary_label' => $candidate['secondary_label'],
        ];
    }

    protected static function registrationEnabled(): bool
    {
        return SiteSetting::isPublicRegistrationEnabled();
    }

    protected static function registrationUrl(): string
    {
        return self::registrationEnabled()
            ? self::routeUrl('register')
            : self::routeUrl('login');
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
