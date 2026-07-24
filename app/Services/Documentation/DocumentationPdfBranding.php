<?php

namespace App\Services\Documentation;

use App\Models\ContactSetting;
use Throwable;

class DocumentationPdfBranding
{
    public static function organizationName(): string
    {
        return (string) config('browsershot.pdf_branding.organization_name', 'أكاديمية كلاودسوفت');
    }

    public static function tagline(): string
    {
        return (string) config('browsershot.pdf_branding.tagline', 'تعليم البرمجة وتطوير البرمجيات');
    }

    public static function website(): string
    {
        $configured = trim((string) config('browsershot.pdf_branding.website', ''));

        if ($configured !== '') {
            return $configured;
        }

        return 'https://claudsoft.com';
    }

    /**
     * Public web path to the academy logo (admin sidebar / frontend navbar).
     */
    public static function logoPublicPath(): string
    {
        $configured = trim((string) config('browsershot.pdf_branding.logo_path', ''));

        if ($configured !== '') {
            return $configured;
        }

        return 'frontend2/assets/images/logo.png';
    }

    public static function logoUrl(): string
    {
        return asset(self::logoPublicPath());
    }

    /**
     * Absolute filesystem path when the logo file exists locally.
     */
    public static function logoAbsolutePath(): ?string
    {
        $path = public_path(self::logoPublicPath());

        return is_readable($path) ? $path : null;
    }

    /**
     * Data-URI for reliable PDF embedding without remote fetch.
     */
    public static function logoDataUri(): ?string
    {
        $path = self::logoAbsolutePath();

        if ($path === null) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($path));
    }

    /**
     * @return list<string>
     */
    public static function phoneNumbers(): array
    {
        $configured = config('browsershot.pdf_branding.phones');

        if (is_array($configured) && $configured !== []) {
            return array_values(array_filter(array_map('strval', $configured)));
        }

        try {
            $settings = ContactSetting::getSettings();
            $phones = collect($settings->phone_numbers ?? [])
                ->pluck('number')
                ->filter()
                ->map(fn ($n) => trim((string) $n))
                ->unique()
                ->values()
                ->all();

            if ($phones !== []) {
                return $phones;
            }
        } catch (Throwable) {
            // fall through to defaults
        }

        return ['905050580036'];
    }

    /**
     * @return list<string>
     */
    public static function emails(): array
    {
        $configured = config('browsershot.pdf_branding.emails');

        if (is_array($configured) && $configured !== []) {
            return array_values(array_filter(array_map('strval', $configured)));
        }

        try {
            $settings = ContactSetting::getSettings();
            $emails = collect($settings->email_addresses ?? [])
                ->pluck('email')
                ->filter()
                ->map(fn ($e) => trim((string) $e))
                ->unique()
                ->values()
                ->all();

            if ($emails !== []) {
                return $emails;
            }
        } catch (Throwable) {
            // fall through to defaults
        }

        return ['info@claudsoft.com', 'support@claudsoft.com'];
    }

    public static function address(): string
    {
        $configured = trim((string) config('browsershot.pdf_branding.address', ''));

        if ($configured !== '') {
            return $configured;
        }

        try {
            $settings = ContactSetting::getSettings();
            $address = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], ' — ', (string) ($settings->address_text ?? ''))));

            if ($address !== '') {
                return $address;
            }
        } catch (Throwable) {
            // fall through
        }

        return 'المملكة العربية السعودية — الرياض';
    }
}
