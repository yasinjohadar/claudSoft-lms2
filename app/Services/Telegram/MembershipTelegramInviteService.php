<?php

namespace App\Services\Telegram;

use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\GroupRegistrationSetting;
use App\Models\TelegramMessageTemplate;
use App\Models\User;
use App\Services\BulkEmail\BulkEmailVariableBuilder;
use InvalidArgumentException;

class MembershipTelegramInviteService
{
    public function __construct(
        private BulkEmailVariableBuilder $variableBuilder,
        private BroadcastTelegramMessage $broadcastService,
        private SendTelegramMessage $sendTelegramMessage,
    ) {}

    public function variablesForInvite(
        User $student,
        Course $course,
        CourseGroup $group,
        ?GroupRegistrationSetting $settings = null
    ): array {
        $settings ??= GroupRegistrationSetting::where('group_id', $group->id)->first();
        $variables = $this->variableBuilder->build($student, $course, $group);
        $variables['student_email'] = $variables['email'] ?? '';
        $variables['group_link'] = $settings?->telegram_group_link
            ?? $settings?->whatsapp_group_link
            ?? '';

        return $variables;
    }

    public function renderTemplate(
        TelegramMessageTemplate $template,
        User $student,
        Course $course,
        CourseGroup $group,
        ?GroupRegistrationSetting $settings = null
    ): string {
        return $template->render($this->variablesForInvite($student, $course, $group, $settings));
    }

    public function sendTemplateInvite(
        User $student,
        Course $course,
        CourseGroup $group,
        TelegramMessageTemplate $template
    ): string {
        if (empty($student->telegram_chat_id)) {
            throw new InvalidArgumentException('الطالب لم يربط حساب Telegram. اطلب منه الضغط على رابط الربط أولاً.');
        }

        $settings = GroupRegistrationSetting::where('group_id', $group->id)->first();
        $body = $this->renderTemplate($template, $student, $course, $group, $settings);
        $this->assertGroupLinkResolved($body, $settings);

        $this->sendTelegramMessage->sendToUser($student, $body);

        return (string) $student->telegram_chat_id;
    }

    private function assertGroupLinkResolved(string $body, ?GroupRegistrationSetting $settings): void
    {
        $link = trim((string) ($settings?->telegram_group_link ?? $settings?->whatsapp_group_link ?? ''));
        if ($link !== '') {
            return;
        }

        if (str_contains($body, '{group_link}') || str_contains($body, '{{group_link}}')) {
            throw new InvalidArgumentException(
                'رابط مجموعة Telegram غير مُعرّف. أضفه من إعدادات التسجيل للمجموعة.'
            );
        }
    }
}
