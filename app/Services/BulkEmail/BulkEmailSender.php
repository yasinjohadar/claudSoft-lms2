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

    public function send(BulkEmailCampaign $campaign, User $user, string $subject, string $body): void
    {
        $this->applyMailConfig($campaign);

        $toEmail = trim((string) ($user->email ?? ''));
        if ($toEmail === '') {
            throw new \InvalidArgumentException('لا يوجد بريد إلكتروني للطالب.');
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

    private function applyMailConfig(BulkEmailCampaign $campaign): void
    {
        $setting = null;

        if ($campaign->email_setting_id) {
            $setting = EmailSetting::find($campaign->email_setting_id);
        }

        if (! $setting) {
            $setting = EmailSetting::getActive();
        }

        if ($setting) {
            $setting->applyToConfig();
        }
    }
}
