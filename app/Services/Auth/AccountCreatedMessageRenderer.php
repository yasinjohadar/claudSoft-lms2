<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\WhatsAppMessageTemplate;
use App\Services\WhatsApp\WhatsAppSettingsService;

class AccountCreatedMessageRenderer
{
    public function __construct(
        private AccountCreatedMessageSettingsService $settingsService
    ) {}

    /**
     * @return array<string, string>
     */
    public function credentialVariables(User $user, #[\SensitiveParameter] string $plainPassword): array
    {
        $settings = $this->settingsService->getSettings();
        $nameAr = trim((string) ($user->name_ar ?? ''));
        if ($nameAr === '') {
            $nameAr = trim((string) ($user->name ?? '')) ?: 'عزيزي المستخدم';
        }
        $nameEn = trim((string) ($user->name ?? '')) ?: $nameAr;
        $appName = (string) (config('app.name') ?: 'أكاديمية كلاودسوفت');
        $loginUrl = $this->resolveLoginUrl();

        return [
            'student_name_ar' => $nameAr,
            'student_name_en' => $nameEn,
            'student_name' => $nameAr,
            'user_name' => $nameAr,
            'email' => (string) ($user->email ?? ''),
            'password' => $plainPassword,
            'new_password' => $plainPassword,
            'login_url' => $loginUrl,
            'admin_instructions' => trim((string) ($settings['admin_instructions'] ?? '')),
            'app_name' => $appName,
            'reset_url' => $loginUrl,
            'reset_link' => $loginUrl,
        ];
    }

    public function renderCredentialWhatsApp(User $user, #[\SensitiveParameter] string $plainPassword): string
    {
        $settings = $this->settingsService->getSettings();
        $variables = $this->credentialVariables($user, $plainPassword);

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

        // Strip editor HTML before injecting the password.
        $body = WhatsAppMessageTemplate::normalizeBodyForSending($body);

        return $this->renderTemplate($body, $variables, forWhatsApp: true);
    }

    public function renderEmailSubject(): string
    {
        $settings = $this->settingsService->getSettings();
        $subject = trim((string) ($settings['email_subject'] ?? ''));

        return $subject !== '' ? $subject : 'بيانات حسابك - أكاديمية كلاودسوفت';
    }

    public function renderCredentialEmailBodyHtml(User $user, #[\SensitiveParameter] string $plainPassword): string
    {
        $settings = $this->settingsService->getSettings();
        $variables = $this->credentialVariables($user, $plainPassword);
        $variables['password'] = e($variables['password']);
        $variables['new_password'] = e($variables['new_password']);

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
            $patterns = [
                '{{'.$key.'}}',
                '{'.$key.'}',
                '{{ '.$key.' }}',
                '{ '.$key.' }',
            ];
            $output = str_replace($patterns, $value, $output);
        }

        // Never run strip_tags after secrets are injected (forWhatsApp template is already plain).
        return $output;
    }

    private function resolveLoginUrl(): string
    {
        $appUrl = rtrim((string) config('app.url'), '/');

        if ($this->isLocalAppUrl($appUrl)) {
            $waSettings = app(WhatsAppSettingsService::class)->getSettings();
            $publicBase = rtrim((string) ($waSettings['evolution_webhook_base_url'] ?? ''), '/');

            if ($publicBase !== '' && ! $this->isLocalAppUrl($publicBase)) {
                return $publicBase.route('login', [], false);
            }
        }

        return url(route('login'));
    }

    private function isLocalAppUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }

    public static function defaultWhatsAppBody(): string
    {
        return <<<'TEXT'
مرحباً {student_name_ar} 👋

تم إنشاء حسابك في {app_name}. فيما يلي بيانات الدخول:

👤 الاسم (عربي): {student_name_ar}
👤 الاسم (إنجليزي): {student_name_en}
📧 البريد: {email}
🔑 كلمة المرور: {password}
🔗 رابط الدخول: {login_url}

{admin_instructions}
TEXT;
    }

    public static function defaultEmailBody(): string
    {
        return <<<'HTML'
<p class="greeting" style="font-size:20px;font-weight:700;color:#0555a2;margin-bottom:20px;">مرحباً {student_name_ar}! 👋</p>
<p style="margin-bottom:15px;font-size:16px;color:#555555;">تم إنشاء حسابك في {app_name}. فيما يلي بيانات الدخول الكاملة:</p>
<div style="background-color:#f8f9fa;border-right:4px solid #0555a2;padding:20px;margin:20px 0;border-radius:5px;">
    <p style="margin-bottom:10px;font-size:16px;color:#333333;"><strong>الاسم (عربي):</strong> {student_name_ar}</p>
    <p style="margin-bottom:10px;font-size:16px;color:#333333;"><strong>الاسم (إنجليزي):</strong> {student_name_en}</p>
    <p style="margin-bottom:10px;font-size:16px;color:#333333;"><strong>البريد الإلكتروني:</strong> {email}</p>
    <p style="margin-bottom:10px;font-size:16px;color:#333333;"><strong>كلمة المرور:</strong> {password}</p>
</div>
<p style="text-align:center;margin:30px 0;">
    <a href="{login_url}" style="display:inline-block;padding:15px 40px;background-color:#0555a2;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:600;font-size:16px;">تسجيل الدخول</a>
</p>
<div style="background-color:#e7f3ff;border-right:4px solid #0555a2;padding:15px;margin:20px 0;border-radius:5px;color:#004085;">
    <strong>📋 إرشادات:</strong>
    <p style="margin-top:10px;margin-bottom:0;">{admin_instructions}</p>
</div>
HTML;
    }
}
