<?php

namespace App\Services\StudentGifts;

use App\Models\StudentGift;
use App\Models\StudentGiftRecipient;
use App\Models\User;
use App\Services\Storage\StorageHelperService;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentGiftDeliveryService
{
    public function __construct(
        protected StorageHelperService $storageHelper
    ) {}

    public function assertRecipientAccess(User $student, StudentGiftRecipient $recipient): StudentGift
    {
        if ((int) $recipient->student_id !== (int) $student->id) {
            abort(403);
        }

        $gift = $recipient->gift;

        if (! $gift || ! $gift->isGranted()) {
            abort(403);
        }

        return $gift;
    }

    public function preview(User $student, StudentGiftRecipient $recipient): RedirectResponse|StreamedResponse|BinaryFileResponse
    {
        $gift = $this->assertRecipientAccess($student, $recipient);

        if ($gift->isExternalMode()) {
            if (! $gift->preview_url) {
                abort(404);
            }

            $recipient->update(['previewed_at' => $recipient->previewed_at ?? now()]);

            return redirect()->away($gift->preview_url);
        }

        if (! $gift->preview_file_path) {
            abort(404);
        }

        $recipient->update(['previewed_at' => $recipient->previewed_at ?? now()]);

        return serve_storage_file_response(
            ['public'],
            $gift->preview_file_path,
            $gift->preview_file_name
        );
    }

    public function download(User $student, StudentGiftRecipient $recipient): RedirectResponse|BinaryFileResponse
    {
        $gift = $this->assertRecipientAccess($student, $recipient);

        if ($gift->isExternalMode()) {
            if (! $gift->download_url) {
                abort(404);
            }

            $recipient->update(['downloaded_at' => $recipient->downloaded_at ?? now()]);

            return redirect()->away($gift->download_url);
        }

        if (! $gift->download_file_path) {
            abort(404);
        }

        $recipient->update(['downloaded_at' => $recipient->downloaded_at ?? now()]);

        $disk = $this->storageHelper->getDisk('public');
        $downloadName = $gift->download_file_name ?: basename($gift->download_file_path);

        $failover = $this->storageHelper->retrieveFileWithFailover('public', $gift->download_file_path);
        if ($failover && isset($failover['local_path'])) {
            return response()->download($failover['local_path'], $downloadName);
        }

        if ($disk->exists($gift->download_file_path)) {
            return $disk->download($gift->download_file_path, $downloadName);
        }

        abort(404);
    }
}
