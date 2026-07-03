<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseGroup;
use App\Models\TelegramIncomingMessage;
use App\Services\Telegram\SendTelegramMessage;
use App\Services\Telegram\TelegramGroupLinkService;
use App\Services\Telegram\TelegramLinkService;
use App\Services\Telegram\TelegramSettingsService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function __construct(
        private TelegramSettingsService $settingsService,
        private TelegramLinkService $linkService,
        private SendTelegramMessage $sendService,
        private TelegramGroupLinkService $groupLinkService,
    ) {}

    public function handle(Request $request): Response
    {
        $settings = $this->settingsService->getSettings();
        $secret = $settings['webhook_secret'] ?? '';
        if ($secret !== '' && $request->header('X-Telegram-Bot-Api-Secret-Token') !== $secret) {
            return response('Unauthorized', 401);
        }

        $update = $request->all();
        Log::channel('single')->debug('Telegram webhook', ['update_id' => $update['update_id'] ?? null]);

        $message = $update['message'] ?? null;
        if (! is_array($message)) {
            return response('OK', 200);
        }

        $chatId = (string) ($message['chat']['id'] ?? '');
        $text = trim((string) ($message['text'] ?? ''));
        $from = $message['from'] ?? [];
        $username = $from['username'] ?? null;
        $updateId = $update['update_id'] ?? null;
        $messageId = $message['message_id'] ?? null;

        TelegramIncomingMessage::create([
            'chat_id' => $chatId,
            'telegram_username' => $username,
            'update_id' => $updateId,
            'message_id' => $messageId,
            'text' => $text,
            'payload' => $update,
            'received_at' => now(),
        ]);

        if (str_starts_with($text, '/start')) {
            $this->handleStart($text, $chatId, $username);

            return response('OK', 200);
        }

        if ($text === '/link_group' || str_starts_with($text, '/link_group')) {
            $this->handleLinkGroup($message, $chatId);

            return response('OK', 200);
        }

        if ($settings['auto_reply'] && $text !== '' && ! str_starts_with($text, '/')) {
            try {
                $this->sendService->sendText($chatId, $settings['auto_reply_message'], applyDelay: false);
            } catch (\Throwable $e) {
                Log::warning('Telegram auto-reply failed', ['error' => $e->getMessage()]);
            }
        }

        return response('OK', 200);
    }

    private function handleStart(string $text, string $chatId, ?string $username): void
    {
        if (preg_match('/\/start\s+link_([a-zA-Z0-9]+)/', $text, $matches)) {
            $user = $this->linkService->linkUserByToken($matches[1], $chatId, $username);
            $reply = $user
                ? '✅ تم ربط حسابك بنجاح في المنصة. ستصلك الإشعارات والدعوات هنا.'
                : '❌ رابط الربط غير صالح أو منتهٍ. أنشئ رابطاً جديداً من ملفك في المنصة.';

            try {
                $this->sendService->sendText($chatId, $reply, applyDelay: false);
            } catch (\Throwable) {
                // ignore
            }

            return;
        }

        try {
            $this->sendService->sendText(
                $chatId,
                "مرحباً! لربط حسابك في المنصة، افتح رابط الربط من صفحة ملفك الشخصي.\n/start link_...",
                applyDelay: false
            );
        } catch (\Throwable) {
            // ignore
        }
    }

    private function handleLinkGroup(array $message, string $chatId): void
    {
        $chatType = $message['chat']['type'] ?? '';
        if (! in_array($chatType, ['group', 'supergroup', 'channel'], true)) {
            return;
        }

        $title = $message['chat']['title'] ?? 'Telegram Group';
        $pendingGroupId = cache()->get('telegram_pending_link_group:'.$chatId);

        if (! $pendingGroupId) {
            try {
                $this->sendService->sendText(
                    $chatId,
                    "ℹ️ لربط هذه المجموعة بمجموعة تسجيل:\n1) افتح لوحة Telegram → ربط مجموعة\n2) اختر مجموعة التسجيل واضغط «انتظر /link_group»\n3) أرسل /link_group هنا",
                    applyDelay: false
                );
            } catch (\Throwable) {
                // ignore
            }

            return;
        }

        $group = CourseGroup::find($pendingGroupId);
        if (! $group) {
            return;
        }

        $inviteLink = $this->groupLinkService->tryCreateInviteLink($chatId);
        $this->groupLinkService->linkToCourseGroup($group, $chatId, 'group', $title, $inviteLink);
        cache()->forget('telegram_pending_link_group:'.$chatId);

        try {
            $this->sendService->sendText($chatId, '✅ تم ربط هذه المجموعة بمجموعة التسجيل: '.$group->name, applyDelay: false);
        } catch (\Throwable) {
            // ignore
        }
    }
}
