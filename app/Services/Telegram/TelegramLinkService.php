<?php

namespace App\Services\Telegram;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TelegramLinkService
{
    private const CACHE_PREFIX = 'telegram_link_token:';

    public function __construct(
        private TelegramSettingsService $settingsService,
        private TelegramBotClient $client,
    ) {}

    public function createLinkToken(User $user): string
    {
        $token = Str::random(32);
        $ttl = config('telegram.link_token_ttl_minutes', 30);

        Cache::put(self::CACHE_PREFIX.$token, $user->id, now()->addMinutes($ttl));

        return $token;
    }

    public function botStartLink(User $user): string
    {
        $token = $this->createLinkToken($user);
        $username = $this->settingsService->getSettings()['bot_username'] ?? '';

        if ($username === '') {
            return '';
        }

        return 'https://t.me/'.ltrim($username, '@').'?start=link_'.$token;
    }

    public function linkUserByToken(string $token, string $chatId, ?string $username = null): ?User
    {
        $userId = Cache::pull(self::CACHE_PREFIX.$token);
        if (! $userId) {
            return null;
        }

        $user = User::find($userId);
        if (! $user) {
            return null;
        }

        User::where('telegram_chat_id', $chatId)->where('id', '!=', $user->id)->update([
            'telegram_chat_id' => null,
            'telegram_username' => null,
            'telegram_linked_at' => null,
        ]);

        $user->update([
            'telegram_chat_id' => $chatId,
            'telegram_username' => $username,
            'telegram_linked_at' => now(),
        ]);

        return $user->fresh();
    }

    public function unlink(User $user): void
    {
        $user->update([
            'telegram_chat_id' => null,
            'telegram_username' => null,
            'telegram_linked_at' => null,
        ]);
    }
}
