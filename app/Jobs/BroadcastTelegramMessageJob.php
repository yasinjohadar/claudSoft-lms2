<?php

namespace App\Jobs;

use App\Models\TelegramBroadcast;
use App\Models\TelegramBroadcastRecipient;
use App\Models\User;
use App\Services\Telegram\SendTelegramMessage;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BroadcastTelegramMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public function __construct(
        public TelegramBroadcast $broadcast,
        public User $student,
        public string $message,
        public ?int $delaySeconds = null,
    ) {
        if ($this->delaySeconds === null) {
            $this->delaySeconds = app(\App\Services\Telegram\TelegramSettingsService::class)->calculateDelay();
        }

        if ($this->delaySeconds !== null && $this->delaySeconds > 0) {
            $this->delay($this->delaySeconds);
        }
    }

    public function handle(SendTelegramMessage $sendService): void
    {
        try {
            if ($this->broadcast->status === TelegramBroadcast::STATUS_PENDING) {
                $this->broadcast->update(['status' => TelegramBroadcast::STATUS_PROCESSING]);
            }

            $sendService->sendToUser($this->student, $this->message);

            TelegramBroadcastRecipient::where('broadcast_id', $this->broadcast->id)
                ->where('user_id', $this->student->id)
                ->update([
                    'status' => TelegramBroadcastRecipient::STATUS_SENT,
                    'sent_at' => now(),
                ]);

            $this->broadcast->increment('sent_count');
        } catch (Exception $e) {
            TelegramBroadcastRecipient::where('broadcast_id', $this->broadcast->id)
                ->where('user_id', $this->student->id)
                ->update([
                    'status' => TelegramBroadcastRecipient::STATUS_FAILED,
                    'error_message' => $e->getMessage(),
                ]);

            $this->broadcast->increment('failed_count');

            Log::error('Telegram broadcast failed', [
                'broadcast_id' => $this->broadcast->id,
                'user_id' => $this->student->id,
                'error' => $e->getMessage(),
            ]);
        } finally {
            $this->broadcast->refresh();
            $total = $this->broadcast->sent_count + $this->broadcast->failed_count;
            if ($total >= $this->broadcast->total_recipients) {
                $status = $this->broadcast->failed_count === $this->broadcast->total_recipients
                    ? TelegramBroadcast::STATUS_FAILED
                    : TelegramBroadcast::STATUS_COMPLETED;
                $this->broadcast->update(['status' => $status]);
            }
        }
    }
}
