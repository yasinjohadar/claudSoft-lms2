<?php

namespace Tests\Feature\Api\Mobile\V1;

/**
 * المسارات تعيد استخدام منطق API الطالب؛ هنا نتحقق من الربط والتحقق من المدخلات
 * دون إرسال رسائل حقيقية (بريد/واتساب).
 */
class MobileForgotPasswordApiTest extends MobileApiMysqlTestCase
{
    public function test_options_list_channels_and_country_codes(): void
    {
        $this->getJson('/api/mobile/v1/auth/forgot-password/options')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => [
                'whatsapp_available', 'whatsapp_otp_available', 'default_channel',
                'default_country_code', 'country_codes' => [['code', 'label', 'iso']],
            ]]);
    }

    public function test_email_channel_validates_and_rejects_unknown_email(): void
    {
        $this->postJson('/api/mobile/v1/auth/forgot-password', ['channel' => 'email'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        $this->postJson('/api/mobile/v1/auth/forgot-password', [
            'channel' => 'email',
            'email' => 'nobody-'.uniqid().'@example.com',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }

    public function test_phone_channels_require_valid_phone(): void
    {
        $this->postJson('/api/mobile/v1/auth/forgot-password', [
            'channel' => 'whatsapp',
            'country_code' => '+963',
            'phone' => '09',
        ])->assertUnprocessable()->assertJsonValidationErrors(['phone']);
    }

    public function test_verify_otp_and_reset_reject_invalid_input(): void
    {
        $this->postJson('/api/mobile/v1/auth/forgot-password/verify-otp', ['phone' => '963999', 'code' => '000000'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);

        $this->postJson('/api/mobile/v1/auth/reset-password', [
            'token' => 'invalid',
            'email' => 'nobody-'.uniqid().'@example.com',
            'password' => 'NewPass#2026',
            'password_confirmation' => 'NewPass#2026',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }
}
