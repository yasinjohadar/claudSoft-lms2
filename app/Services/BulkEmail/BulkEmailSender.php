<?php

namespace App\Services\BulkEmail;

use App\Models\BulkEmailCampaign;
use App\Models\EmailSetting;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class BulkEmailSender
{
    public function __construct(
        private BulkEmailVariableBuilder $variableBuilder
    ) {}

    /**
     * @return array{subject: string, body: string}
     */
    public function renderForUser(BulkEmailCampaign $campaign, User $user): array
    {
        $variables = $this->variableBuilder->buildForCampaign($user, $campaign);

        if ($campaign->content_mode === BulkEmailCampaign::CONTENT_MODE_TEMPLATE && $campaign->email_template_id) {
            $template = $campaign->relationLoaded('emailTemplate')
                ? $campaign->emailTemplate
                : EmailTemplate::find($campaign->email_template_id);

            if ($template) {
                return [
                    'subject' => $template->renderSubject($variables),
                    'body' => $template->render($variables),
                ];
            }
        }

        return [
            'subject' => $this->variableBuilder->renderSubject($campaign->subject ?? '', $variables),
            'body' => $this->variableBuilder->renderBody($campaign->body ?? '', $variables),
        ];
    }

    /**
     * @return array{subject: string, body: string}
     */
    public function renderTemplateForUser(EmailTemplate $template, User $user): array
    {
        $variables = $this->variableBuilder->build($user);

        return [
            'subject' => $template->renderSubject($variables),
            'body' => $template->render($variables),
        ];
    }

    public function send(BulkEmailCampaign $campaign, User $user, string $subject, string $body): void
    {
        $this->applyEmailSetting($campaign->email_setting_id);
        $this->deliverMail($user, $subject, $body);
    }

    public function sendTemplateToUser(User $user, EmailTemplate $template, ?int $emailSettingId = null): void
    {
        $rendered = $this->renderTemplateForUser($template, $user);
        $this->applyEmailSetting($emailSettingId);
        $this->deliverMail($user, $rendered['subject'], $rendered['body']);
    }

    private function deliverMail(User $user, string $subject, string $body): void
    {
        $toEmail = trim((string) ($user->email ?? ''));
        if ($toEmail === '') {
            throw new \InvalidArgumentException('لا يوجد بريد إلكتروني للمستخدم.');
        }

        $fromAddress = config('mail.from.address', 'noreply@cloudsoft.edu');
        $fromName = config('mail.from.name', 'كلاودسوفت التعليمية');

        Mail::send([], [], function ($message) use ($user, $toEmail, $subject, $body, $fromAddress, $fromName) {
            $message->from($fromAddress, $fromName)
                ->to($toEmail, $user->name_ar ?? $user->name)
                ->subject($subject)
                ->html($body);
        });
    }

    private function applyEmailSetting(?int $emailSettingId = null): void
    {
        $setting = $emailSettingId ? EmailSetting::find($emailSettingId) : null;

        if (! $setting) {
            $setting = EmailSetting::getActive();
        }

        if ($setting) {
            $setting->applyToConfig();
        }
    }

    private function applyMailConfig(BulkEmailCampaign $campaign): void
    {
        $this->applyEmailSetting($campaign->email_setting_id);
    }
}
