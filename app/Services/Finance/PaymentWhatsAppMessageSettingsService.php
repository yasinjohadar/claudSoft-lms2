<?php

namespace App\Services\Finance;

use App\Models\SystemSetting;

class PaymentWhatsAppMessageSettingsService
{
    public const GROUP = 'payment_whatsapp';

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

        $merged = array_merge($defaults, $stored);

        $merged['enabled'] = filter_var($merged['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);

        return $merged;
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

            if ($key === 'enabled') {
                $value = $value ? '1' : '0';
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
            'enabled' => [
                'value' => '1',
                'type' => 'boolean',
                'description' => 'تفعيل إرسال واتساب عند تسجيل دفعة',
            ],
            'whatsapp_template_id' => [
                'value' => '',
                'type' => 'string',
                'description' => 'قالب واتساب لإشعار الدفع',
            ],
            'whatsapp_body' => [
                'value' => PaymentWhatsAppMessageRenderer::defaultBody(),
                'type' => 'string',
                'description' => 'نص واتساب مخصص عند تسجيل دفعة',
            ],
        ];
    }

    public function restoreDefaults(): void
    {
        $this->updateSettings([
            'enabled' => true,
            'whatsapp_template_id' => '',
            'whatsapp_body' => PaymentWhatsAppMessageRenderer::defaultBody(),
        ]);
    }
}
