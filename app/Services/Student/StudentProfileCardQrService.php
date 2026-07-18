<?php

namespace App\Services\Student;

use App\Models\StudentProfileCard;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class StudentProfileCardQrService
{
    public function publicUrl(StudentProfileCard $card): string
    {
        return route('frontend.profile-card.show', $card->slug);
    }

    public function generate(StudentProfileCard $card): string
    {
        $this->deleteStoredQr($card->qr_code_path);

        // SVG does not require the imagick extension (PNG does).
        $qrCodePath = 'profile-cards/qr/'.$card->slug.'.svg';
        $qrCodeFullPath = storage_path('app/public/'.$qrCodePath);

        if (! file_exists(dirname($qrCodeFullPath))) {
            mkdir(dirname($qrCodeFullPath), 0755, true);
        }

        QrCode::format('svg')
            ->size(300)
            ->margin(2)
            ->generate($this->publicUrl($card), $qrCodeFullPath);

        $card->update(['qr_code_path' => $qrCodePath]);

        return $qrCodePath;
    }

    public function deleteStoredQr(?string $path): void
    {
        if (! $path) {
            return;
        }

        $fullPath = storage_path('app/public/'.$path);
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }
    }

    public function deleteStoredQrForSlug(string $slug): void
    {
        $this->deleteStoredQr('profile-cards/qr/'.$slug.'.svg');
        $this->deleteStoredQr('profile-cards/qr/'.$slug.'.png');
    }

    public function qrUrl(StudentProfileCard $card): ?string
    {
        if (! $card->qr_code_path) {
            return null;
        }

        return storage_url($card->qr_code_path);
    }
}
