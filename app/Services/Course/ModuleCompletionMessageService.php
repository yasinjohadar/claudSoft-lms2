<?php

namespace App\Services\Course;

use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\CourseModule;
use App\Models\EmailTemplate;
use App\Models\GroupRegistrationSetting;
use App\Models\ModuleCompletion;
use App\Models\User;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppMessageTemplate;
use App\Services\BulkEmail\BulkEmailSender;
use App\Services\BulkEmail\BulkEmailVariableBuilder;
use App\Services\WhatsApp\BroadcastWhatsAppMessage;
use App\Services\WhatsApp\SendWhatsAppMessage;
use InvalidArgumentException;

class ModuleCompletionMessageService
{
    public function __construct(
        private BulkEmailVariableBuilder $variableBuilder,
        private BulkEmailSender $emailSender,
        private SendWhatsAppMessage $sendWhatsAppMessage,
        private BroadcastWhatsAppMessage $broadcastService,
    ) {}

    /**
     * @return array<string, string>
     */
    public function buildVariables(
        User $student,
        Course $course,
        CourseModule $module,
        ?CourseGroup $group = null,
        ?ModuleCompletion $completion = null
    ): array {
        $variables = $this->variableBuilder->build($student, $course, $group);
        $variables['student_email'] = $variables['email'] ?? '';
        $variables['module_name'] = $module->title ?? '';
        $variables['module_type'] = $this->moduleTypeLabel($module);
        $variables['completion_status'] = $this->completionStatusLabel($completion);
        $variables['completed_at'] = $completion?->completed_at?->format('Y-m-d H:i') ?? '';

        if ($group) {
            $settings = GroupRegistrationSetting::where('group_id', $group->id)->first();
            $variables['group_link'] = $settings?->whatsapp_group_link ?? '';
        }

        return $variables;
    }

    public function renderWhatsAppTemplate(
        WhatsAppMessageTemplate $template,
        User $student,
        Course $course,
        CourseModule $module,
        ?CourseGroup $group = null,
        ?ModuleCompletion $completion = null
    ): string {
        return $template->render($this->buildVariables($student, $course, $module, $group, $completion));
    }

    /**
     * @return array{subject: string, body: string}
     */
    public function renderEmailTemplate(
        EmailTemplate $template,
        User $student,
        Course $course,
        CourseModule $module,
        ?CourseGroup $group = null,
        ?ModuleCompletion $completion = null
    ): array {
        $variables = $this->buildVariables($student, $course, $module, $group, $completion);

        return [
            'subject' => $template->renderSubject($variables),
            'body' => $template->render($variables),
        ];
    }

    /**
     * @return array{phone: string, instance_name: string|null}
     */
    public function sendWhatsAppTemplate(
        User $student,
        Course $course,
        CourseModule $module,
        WhatsAppMessageTemplate $template,
        ?CourseGroup $group = null,
        ?ModuleCompletion $completion = null
    ): array {
        $body = $this->renderWhatsAppTemplate($template, $student, $course, $module, $group, $completion);
        $digits = $this->broadcastService->normalizedPhoneDigitsForWapi($student);

        if ($digits === null) {
            throw new InvalidArgumentException('رقم واتساب الطالب غير صالح أو غير متوفر.');
        }

        $phone = '+'.$digits;
        $message = $this->sendWhatsAppMessage->sendTextSync($phone, $body);
        $instanceName = $message->payload['evolution_instance_name'] ?? null;

        return [
            'phone' => $phone,
            'instance_name' => is_string($instanceName) && $instanceName !== '' ? $instanceName : null,
        ];
    }

    public function sendEmailTemplate(
        User $student,
        Course $course,
        CourseModule $module,
        EmailTemplate $template,
        ?CourseGroup $group = null,
        ?ModuleCompletion $completion = null,
        ?int $emailSettingId = null
    ): string {
        $email = trim((string) ($student->email ?? ''));
        if ($email === '') {
            throw new InvalidArgumentException('لا يوجد بريد إلكتروني لهذا الطالب.');
        }

        $rendered = $this->renderEmailTemplate($template, $student, $course, $module, $group, $completion);
        $this->emailSender->sendRenderedToUser($student, $rendered['subject'], $rendered['body'], $emailSettingId);

        return $email;
    }

    private function moduleTypeLabel(CourseModule $module): string
    {
        return match ($module->module_type) {
            'lesson' => 'درس',
            'video' => 'فيديو',
            'quiz' => 'اختبار',
            'assignment' => 'واجب',
            'question_module' => 'وحدة أسئلة',
            'resource' => 'مورد',
            default => (string) ($module->module_type ?? ''),
        };
    }

    private function completionStatusLabel(?ModuleCompletion $completion): string
    {
        return match ($completion?->completion_status) {
            'completed' => 'مكتمل',
            'in_progress' => 'قيد التقدم',
            default => $completion?->completion_status ?? '',
        };
    }
}
