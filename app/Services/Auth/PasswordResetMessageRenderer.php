<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\WhatsAppMessageTemplate;
use Carbon\Carbon;

class PasswordResetMessageRenderer
{
    public function __construct(
        private PasswordResetMessageSettingsService $settingsService
    ) {}

    /**
     * @return array<string, string>
     */
    public function variables(User $user, string $resetUrl, int $expireMinutes): array
    {
        $expiresAt = Carbon::now()->addMinutes($expireMinutes);
        $userName = $user->name_ar ?? $user->name ?? 'عزيزي المستخدم';
        $appName = (string) (config('app.name') ?: 'أكاديمية كلاودسوفت');

        return [
            'user_name' => $userName,
            'student_name' => $userName,
            'reset_url' => $resetUrl,
            'reset_link' => $resetUrl,
            'expire_minutes' => (string) $expireMinutes,
            'expire_at' => $expiresAt->format('Y-m-d H:i'),
            'expire_at_date' => $expiresAt->format('Y-m-d'),
            'expire_at_time' => $expiresAt->format('H:i'),
            'app_name' => $appName,
            'email' => (string) ($user->email ?? ''),
        ];
    }

    public function renderWhatsApp(User $user, string $resetUrl, int $expireMinutes): string
    {
        $settings = $this->settingsService->getSettings();
        $variables = $this->variables($user, $resetUrl, $expireMinutes);

        if (! empty($settings['whatsapp_template_id'])) {
            $template = WhatsAppMessageTemplate::active()
                ->byType(WhatsAppMessageTemplate::TYPE_TEXT)
                ->find($settings['whatsapp_template_id']);

            if ($template) {
                return $template->render($variables);
            }
        }

        $body = trim((string) ($settings['whatsapp_body'] ?? ''));
        if ($body === '') {
            $body = self::defaultWhatsAppBody();
        }

        return $this->renderTemplate($body, $variables, forWhatsApp: true);
    }

    public function renderEmailSubject(): string
    {
        $settings = $this->settingsService->getSettings();
        $subject = trim((string) ($settings['email_subject'] ?? ''));

        return $subject !== '' ? $subject : 'إعادة تعيين كلمة المرور - أكاديمية كلاودسوفت';
    }

    public function renderEmailBodyHtml(User $user, string $resetUrl, int $expireMinutes): string
    {
        $settings = $this->settingsService->getSettings();
        $variables = $this->variables($user, $resetUrl, $expireMinutes);

        $body = trim((string) ($settings['email_body'] ?? ''));
        if ($body === '') {
            $body = self::defaultEmailBody();
        }

        return $this->renderTemplate($body, $variables, forWhatsApp: false);
    }

    /**
     * @param  array<string, string>  $variables
     */
    private function renderTemplate(string $template, array $variables, bool $forWhatsApp): string
    {
        $output = $template;
        foreach ($variables as $key => $value) {
            $output = str_replace(
                ['{{'.$key.'}}', '{'.$key.'}'],
                $value,
                $output
            );
        }

        if ($forWhatsApp) {
            return WhatsAppMessageTemplate::normalizeBodyForSending($output);
        }

        return $output;
    }

    public static function defaultWhatsAppBody(): string
    {
        return <<<'TEXT'
مرحباً {user_name} 👋

طلبت إعادة تعيين كلمة المرور في {app_name}.
اضغط الرابط التالي:
{reset_url}

⏱ ينتهي صلاحية الرابط في: {expire_at} (بعد {expire_minutes} دقيقة)

إذا لم تطلب ذلك تجاهل هذه الرسالة 🙏
TEXT;
    }

    public static function defaultEmailBody(): string
    {
        return <<<'HTML'
<p class="greeting" style="font-size:20px;font-weight:700;color:#0555a2;margin-bottom:20px;">مرحباً {user_name}! 👋</p>
<p style="margin-bottom:15px;font-size:16px;color:#555555;">لقد تلقينا طلباً لإعادة تعيين كلمة المرور لحسابك في {app_name}.</p>
<p style="text-align:center;margin:30px 0;">
    <a href="{reset_url}" style="display:inline-block;padding:15px 40px;background-color:#0555a2;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:600;font-size:16px;">إعادة تعيين كلمة المرور</a>
</p>
<p style="margin-bottom:15px;font-size:16px;color:#555555;">يرجى الضغط على الزر أعلاه لإعادة تعيين كلمة المرور الخاصة بك.</p>
<div style="background-color:#fff3cd;border-right:4px solid #ffc107;padding:15px;margin:20px 0;border-radius:5px;color:#856404;">
    <strong>⚠️ مهم:</strong>
    <span>ينتهي صلاحية هذا الرابط في <strong>{expire_at}</strong> (بعد {expire_minutes} دقيقة).</span>
</div>
<div style="background-color:#e7f3ff;border-right:4px solid #0555a2;padding:15px;margin:20px 0;border-radius:5px;color:#004085;">
    <strong>🔒 ملاحظة أمان:</strong>
    <ul style="margin-right:20px;margin-top:10px;margin-bottom:0;">
        <li>إذا لم تطلب إعادة تعيين كلمة المرور، يمكنك تجاهل هذه الرسالة بأمان.</li>
        <li>لا تشارك هذا الرابط مع أي شخص آخر.</li>
    </ul>
</div>
<p style="font-size:12px;color:#666666;direction:ltr;text-align:left;word-break:break-all;background:#f8f9fa;padding:15px;border-radius:5px;">
    <strong style="color:#333;">إذا لم يعمل الزر، انسخ الرابط:</strong><br>
    <a href="{reset_url}" style="color:#0555a2;">{reset_url}</a>
</p>
HTML;
    }
}
