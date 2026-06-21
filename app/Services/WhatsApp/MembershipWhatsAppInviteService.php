<?php

namespace App\Services\WhatsApp;

use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\GroupRegistrationSetting;
use App\Models\User;
use App\Models\WhatsAppMessageTemplate;
use App\Services\BulkEmail\BulkEmailVariableBuilder;
use InvalidArgumentException;

class MembershipWhatsAppInviteService
{
    public function __construct(
        private BulkEmailVariableBuilder $variableBuilder,
        private BroadcastWhatsAppMessage $broadcastService,
        private SendWhatsAppMessage $sendWhatsAppMessage
    ) {}

    /**
     * @return array<string, string>
     */
    public function variablesForInvite(
        User $student,
        Course $course,
        CourseGroup $group,
        ?GroupRegistrationSetting $settings = null
    ): array {
        $settings ??= GroupRegistrationSetting::where('group_id', $group->id)->first();

        $variables = $this->variableBuilder->build($student, $course, $group);
        $variables['student_email'] = $variables['email'] ?? '';
        $variables['group_link'] = $settings?->whatsapp_group_link ?? '';

        return $variables;
    }

    public function renderTemplate(
        WhatsAppMessageTemplate $template,
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
        WhatsAppMessageTemplate $template
    ): string {
        $settings = GroupRegistrationSetting::where('group_id', $group->id)->first();
        $body = $this->renderTemplate($template, $student, $course, $group, $settings);
        $this->assertGroupLinkResolved($body, $settings);

        $digits = $this->broadcastService->normalizedPhoneDigitsForWapi($student);
        if ($digits === null) {
            throw new InvalidArgumentException('رقم واتساب الطالب غير صالح أو غير متوفر.');
        }

        $phone = '+'.$digits;
        $this->sendWhatsAppMessage->sendTextSync($phone, $body);

        return $phone;
    }

    private function assertGroupLinkResolved(string $body, ?GroupRegistrationSetting $settings): void
    {
        $link = trim((string) ($settings?->whatsapp_group_link ?? ''));
        if ($link !== '') {
            return;
        }

        if (str_contains($body, '{group_link}') || str_contains($body, '{{group_link}}')) {
            throw new InvalidArgumentException(
                'رابط مجموعة الواتساب غير مُعرّف. أضفه من إعدادات التسجيل للمجموعة.'
            );
        }
    }
}
