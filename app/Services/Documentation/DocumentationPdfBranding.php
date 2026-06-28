<?php

namespace App\Services\Documentation;

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
}
