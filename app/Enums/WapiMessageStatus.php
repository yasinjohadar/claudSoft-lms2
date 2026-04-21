<?php

namespace App\Enums;

enum WapiMessageStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';
    case SentPendingConfirmation = 'sent_pending_confirmation';

    /** وصف للواجهات — لا يعني تسليماً للجهاز عند Sent. */
    public function labelAr(): string
    {
        return match ($this) {
            self::Pending => 'في انتظار الإرسال (الطابور)',
            self::Sent => 'مقبول من Flaxxa — التسليم للهاتف غير مضمون من السجل',
            self::Failed => 'فشل (مرفوض من API أو خطأ)',
            self::SentPendingConfirmation => 'رد غير واضح — راجع JSON أو استعلم من المزود',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-secondary',
            self::Sent => 'bg-primary',
            self::Failed => 'bg-danger',
            self::SentPendingConfirmation => 'bg-warning text-dark',
        };
    }

    public function badgeTextAr(): string
    {
        return match ($this) {
            self::Pending => 'قيد الإرسال',
            self::Sent => 'مقبول API',
            self::Failed => 'فشل',
            self::SentPendingConfirmation => 'غير مؤكد',
        };
    }
}
