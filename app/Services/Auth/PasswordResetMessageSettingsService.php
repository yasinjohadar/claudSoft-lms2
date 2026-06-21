<?php

namespace App\Services\Auth;

use App\Models\SystemSetting;

class PasswordResetMessageSettingsService
{
    public const GROUP = 'password_reset';

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

    public function hasCustomEmailBody(): bool
    {
        $body = trim((string) ($this->getSettings()['email_body'] ?? ''));

        return $body !== '';
    }

    public function hasCustomWhatsAppBody(): bool
    {
        $body = trim((string) ($this->getSettings()['whatsapp_body'] ?? ''));

        return $body !== '';
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
                'description' => 'قالب واتساب لرسالة استعادة كلمة المرور',
            ],
            'whatsapp_body' => [
                'value' => PasswordResetMessageRenderer::defaultWhatsAppBody(),
                'type' => 'string',
                'description' => 'نص واتساب مخصص عند عدم اختيار قالب',
            ],
            'email_subject' => [
                'value' => 'إعادة تعيين كلمة المرور - أكاديمية كلاودسوفت',
                'type' => 'string',
                'description' => 'موضوع بريد استعادة كلمة المرور',
            ],
            'email_body' => [
                'value' => PasswordResetMessageRenderer::defaultEmailBody(),
                'type' => 'string',
                'description' => 'محتوى HTML لبريد استعادة كلمة المرور',
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
