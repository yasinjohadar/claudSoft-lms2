<?php

namespace App\Services\BulkEmail;

use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\EmailTemplate;
use App\Models\GroupRegistrationSetting;
use App\Models\User;
use InvalidArgumentException;

class MembershipEmailInviteService
{
    public function __construct(
        private BulkEmailVariableBuilder $variableBuilder,
        private BulkEmailSender $emailSender
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
        $variables['group_link'] = $settings?->whatsapp_group_link ?? '';

        return $variables;
    }

    /**
     * @return array{subject: string, body: string}
     */
    public function renderTemplate(
        EmailTemplate $template,
        User $student,
        Course $course,
        CourseGroup $group,
        ?GroupRegistrationSetting $settings = null
    ): array {
        $variables = $this->variablesForInvite($student, $course, $group, $settings);

        return [
            'subject' => $template->renderSubject($variables),
            'body' => $template->render($variables),
        ];
    }

    public function sendTemplateInvite(
        User $student,
        Course $course,
        CourseGroup $group,
        EmailTemplate $template,
        ?int $emailSettingId = null
    ): string {
        $email = trim((string) ($student->email ?? ''));
        if ($email === '') {
            throw new InvalidArgumentException('لا يوجد بريد إلكتروني لهذا الطالب.');
        }

        $this->emailSender->sendTemplateToUserWithContext(
            $student,
            $template,
            $course,
            $group,
            $emailSettingId
        );

        return $email;
    }
}
