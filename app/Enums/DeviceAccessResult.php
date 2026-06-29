<?php

namespace App\Enums;

enum DeviceAccessResult: string
{
    case Allowed = 'allowed';
    case AllowedFirstDevice = 'allowed_first_device';
    case Blocked = 'blocked';
    case UntrustedNew = 'untrusted_new';
    case UntrustedExisting = 'untrusted_existing';

    public function isAllowed(): bool
    {
        return in_array($this, [self::Allowed, self::AllowedFirstDevice], true);
    }

    public function userMessage(): string
    {
        return match ($this) {
            self::Blocked => 'تم حظر هذا الجهاز. يرجى التواصل مع الإدارة.',
            self::UntrustedNew, self::UntrustedExisting => 'هذا الجهاز غير موثوق. يرجى التواصل مع الإدارة لاعتماد الجهاز قبل تسجيل الدخول.',
            default => '',
        };
    }
}
