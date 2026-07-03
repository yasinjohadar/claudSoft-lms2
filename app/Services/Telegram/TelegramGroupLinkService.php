<?php

namespace App\Services\Telegram;

use App\Models\CourseGroup;
use App\Models\GroupRegistrationSetting;
use App\Models\TelegramChannelLink;
use Illuminate\Support\Str;

class TelegramGroupLinkService
{
    public function __construct(
        private TelegramBotClient $client,
    ) {}

    /**
     * Link a Telegram group/channel to a course group via chat_id from admin or /link_group command.
     */
    public function linkToCourseGroup(
        CourseGroup $group,
        string $chatId,
        string $linkType = TelegramChannelLink::TYPE_GROUP,
        ?string $title = null,
        ?string $inviteLink = null
    ): TelegramChannelLink {
        GroupRegistrationSetting::updateOrCreate(
            ['group_id' => $group->id],
            [
                'telegram_chat_id' => $chatId,
                'telegram_group_link' => $inviteLink,
            ]
        );

        return TelegramChannelLink::updateOrCreate(
            [
                'entity_type' => TelegramChannelLink::ENTITY_GROUP,
                'entity_id' => $group->id,
                'link_type' => $linkType,
            ],
            [
                'telegram_chat_id' => $chatId,
                'title' => $title ?? $group->name,
                'invite_link' => $inviteLink,
                'is_active' => true,
            ]
        );
    }

    public function tryCreateInviteLink(string $chatId): ?string
    {
        try {
            $result = $this->client->createChatInviteLink($chatId, [
                'name' => 'LMS-'.Str::random(6),
            ]);

            return $result['invite_link'] ?? null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function linkInstructions(?string $botUsername = null): array
    {
        $bot = ltrim($botUsername ?? '', '@');

        return [
            'step1' => 'أنشئ مجموعة Telegram جديدة أو افتح المجموعة الموجودة.',
            'step2' => 'أضف البوت @'.$bot.' كـ **مسؤول (Admin)** في المجموعة.',
            'step3' => 'أرسل الأمر `/link_group` داخل المجموعة، أو الصق chat_id يدوياً في النموذج.',
            'step4' => 'احفظ — ستتمكن من النشر والمزامنة من لوحة Telegram.',
        ];
    }
}
