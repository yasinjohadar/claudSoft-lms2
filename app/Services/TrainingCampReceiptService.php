<?php

namespace App\Services;

use App\Services\Storage\StorageHelperService;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class TrainingCampReceiptService
{
    public const DISK = 'payment_receipts';

    public function __construct(
        private StorageHelperService $storageHelper
    ) {}

    public function store(UploadedFile $receipt, int $campId, int $studentId): string
    {
        $directory = 'training-camps/payment-receipts/'.date('Y').'/'.$campId.'/'.$studentId;

        $storedPath = $this->storageHelper->storeUploadedFileWithFailover(
            self::DISK,
            $directory,
            $receipt,
            'document'
        );

        if (! $storedPath) {
            throw new RuntimeException('تعذر رفع إيصال الدفع. يرجى المحاولة مرة أخرى.');
        }

        return $storedPath;
    }

    /**
     * @return array{content: string, mime_type: string}|null
     */
    public function retrieve(?string $path, ?string $disk = null): ?array
    {
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        return $this->storageHelper->retrieveFileWithFailover(
            $disk ?: self::DISK,
            $path
        );
    }
}
