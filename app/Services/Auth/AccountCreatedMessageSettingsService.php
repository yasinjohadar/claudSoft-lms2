<?php

namespace App\Services\Auth;

use App\Models\SystemSetting;

class AccountCreatedMessageSettingsService
{
    public const GROUP = 'account_created';

    public function initializeDefaults(): void
    {
        foreach ($this->defaultSettings() as $key => $meta) {
            if (! SystemSetting::byKey($key)->ofGroup(self::GROUP)->exists()) {
                SystemSetting::set($key, $meta['value'], $meta['type'], self::GROUP, $meta['description'] ?? null);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        $this->initializeDefaults();

        $stored = SystemSetting::where('group', self::GROUP)
            ->get()
            ->keyBy('key')
            ->map(fn ($row) => $row->value)
            ->toArray();

        $defaults = collect($this->defaultSettings())->mapWithKeys(
            fn ($meta, $key) => [$key => $meta['value']]
        )->all();

        return array_merge($defaults, $stored);
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    public function updateSettings(array $settings): void
    {
        $allowed = array_keys($this->defaultSettings());

        foreach ($settings as $key => $value) {
            if (! in_array($key, $allowed, true)) {
                continue;
            }

            if ($key === 'whatsapp_template_id' && ($value === '' || $value === null)) {
                $value = '';
            }

            SystemSetting::set(
                $key,
                $value,
                $this->defaultSettings()[$key]['type'],
                self::GROUP,
                $this->defaultSettings()[$key]['description'] ?? null
            );
        }
    }

    /**
     * @return array<string, array{value: string, type: string, description?: string}>
     */
    public function defaultSettings(): array
    {
        return [
            'whatsapp_template_id' => [
                'value' => '',
                'type' => 'string',
                'description' => 'قالب واتساب لرسالة بيانات الحساب عند الإنشاء',
            ],
            'whatsapp_body' => [
                'value' => AccountCreatedMessageRenderer::defaultWhatsAppBody(),
                'type' => 'string',
                'description' => 'نص واتساب مخصص عند عدم اختيار قالب',
            ],
            'email_subject' => [
                'value' => 'بيانات حسابك - أكاديمية كلاودسوفت',
                'type' => 'string',
                'description' => 'موضوع بريد بيانات الحساب عند الإنشاء',
            ],
            'email_body' => [
                'value' => AccountCreatedMessageRenderer::defaultEmailBody(),
                'type' => 'string',
                'description' => 'محتوى HTML لبريد بيانات الحساب عند الإنشاء',
            ],
            'admin_instructions' => [
                'value' => 'يُنصح بتغيير كلمة المرور بعد أول تسجيل دخول. لا تشارك بيانات الدخول مع أي شخص آخر. إذا واجهت مشكلة في الدخول تواصل مع الدعم.',
                'type' => 'string',
                'description' => 'إرشادات الإدارة المضمّنة في رسالة بيانات الحساب',
            ],
        ];
    }

    public function restoreDefaults(): void
    {
        $defaults = $this->defaultSettings();
        $payload = [];
        foreach ($defaults as $key => $meta) {
            $payload[$key] = $meta['value'];
        }
        $payload['whatsapp_template_id'] = '';
        $this->updateSettings($payload);
    }
}
