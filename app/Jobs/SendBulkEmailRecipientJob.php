<?php

namespace App\Jobs;

use App\Models\BulkEmailCampaign;
use App\Models\BulkEmailRecipient;
use App\Services\BulkEmail\BulkEmailSender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendBulkEmailRecipientJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** One attempt: failures are recorded per-recipient; retries would duplicate sends. */
    public $tries = 1;

    public function __construct(
        public BulkEmailCampaign $campaign,
        public BulkEmailRecipient $recipient
    ) {}

    public function handle(BulkEmailSender $sender): void
    {
        $this->campaign->refresh();
        $this->recipient->refresh();

        if ($this->recipient->status !== BulkEmailRecipient::STATUS_PENDING) {
            return;
        }

        if ($this->campaign->status === BulkEmailCampaign::STATUS_PENDING) {
            $this->campaign->update(['status' => BulkEmailCampaign::STATUS_PROCESSING]);
        }

        $user = $this->recipient->user ?? $this->recipient->user()->first();

        if (! $user) {
            $this->markFailed('المستخدم غير موجود.');

            return;
        }

        $email = trim((string) ($user->email ?? $this->recipient->email ?? ''));
        if ($email === '') {
            $this->markSkipped('لا يوجد بريد إلكتروني للطالب.');

            return;
        }

        try {
            $this->campaign->loadMissing(['course', 'group', 'emailTemplate']);

            $rendered = $sender->renderForUser($this->campaign, $user);
            $sender->send($this->campaign, $user, $rendered['subject'], $rendered['body']);

            $this->recipient->update([
                'status' => BulkEmailRecipient::STATUS_SENT,
                'rendered_subject' => $rendered['subject'],
                'sent_at' => now(),
                'error_message' => null,
            ]);

            $this->campaign->increment('sent_count');

            Log::info('Bulk email sent successfully', [
                'campaign_id' => $this->campaign->id,
                'recipient_id' => $this->recipient->id,
                'user_id' => $user->id,
            ]);
        } catch (Throwable $e) {
            $this->markFailed($e->getMessage());

            Log::error('Failed to send bulk email', [
                'campaign_id' => $this->campaign->id,
                'recipient_id' => $this->recipient->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        } finally {
            $this->finalizeCampaignIfDone();
        }
    }

    private function markFailed(string $message): void
    {
        $this->recipient->update([
            'status' => BulkEmailRecipient::STATUS_FAILED,
            'error_message' => $message,
        ]);

        $this->campaign->increment('failed_count');
    }

    private function markSkipped(string $message): void
    {
        if ($this->recipient->status === BulkEmailRecipient::STATUS_SKIPPED) {
            return;
        }

        $this->recipient->update([
            'status' => BulkEmailRecipient::STATUS_SKIPPED,
            'error_message' => $message,
        ]);

        $this->campaign->increment('skipped_count');
    }

    private function finalizeCampaignIfDone(): void
    {
        $this->campaign->refresh();

        if ($this->campaign->processed_count >= $this->campaign->total_recipients) {
            $status = ($this->campaign->sent_count === 0 && $this->campaign->failed_count > 0)
                ? BulkEmailCampaign::STATUS_FAILED
                : BulkEmailCampaign::STATUS_COMPLETED;

            $this->campaign->update([
                'status' => $status,
                'completed_at' => now(),
            ]);
        }
    }
}
