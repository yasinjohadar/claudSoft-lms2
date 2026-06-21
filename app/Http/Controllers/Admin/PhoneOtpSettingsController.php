<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WapiTemplate;
use App\Services\Auth\PhoneOtpService;
use App\Services\Auth\PhoneOtpSettingsService;
use App\Services\Auth\PhoneOtpWhatsAppSender;
use App\Support\WapiPhoneNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhoneOtpSettingsController extends Controller
{
    public function __construct(
        private PhoneOtpSettingsService $settingsService,
        private PhoneOtpWhatsAppSender $sender
    ) {
        $this->middleware(['auth', 'role:admin']);
    }

    public function edit(): View
    {
        $settings = $this->settingsService->getSettings();
        $wapiTemplates = WapiTemplate::query()->orderBy('name')->get(['id', 'name', 'language']);

        return view('admin.pages.settings.phone-otp.edit', compact('settings', 'wapiTemplates'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => 'nullable|boolean',
            'wapi_template_id' => 'nullable|exists:wapi_templates,id',
            'template_language' => 'nullable|string|max:16',
            'ttl_seconds' => 'nullable|integer|min:60|max:3600',
            'max_attempts' => 'nullable|integer|min:1|max:20',
            'resend_cooldown_seconds' => 'nullable|integer|min:30|max:600',
            'code_length' => 'nullable|integer|min:4|max:8',
            'register_enabled' => 'nullable|boolean',
            'login_enabled' => 'nullable|boolean',
            'reset_password_enabled' => 'nullable|boolean',
            'change_phone_enabled' => 'nullable|boolean',
        ]);

        $this->settingsService->updateSettings([
            'enabled' => $request->boolean('enabled'),
            'wapi_template_id' => $validated['wapi_template_id'] ?? '',
            'template_language' => $validated['template_language'] ?? 'ar',
            'ttl_seconds' => $validated['ttl_seconds'] ?? 300,
            'max_attempts' => $validated['max_attempts'] ?? 5,
            'resend_cooldown_seconds' => $validated['resend_cooldown_seconds'] ?? 60,
            'code_length' => $validated['code_length'] ?? 6,
            'register_enabled' => $request->boolean('register_enabled'),
            'login_enabled' => $request->boolean('login_enabled'),
            'reset_password_enabled' => $request->boolean('reset_password_enabled'),
            'change_phone_enabled' => $request->boolean('change_phone_enabled'),
        ]);

        return redirect()->route('admin.settings.phone-otp.edit')->with('success', 'تم حفظ إعدادات OTP.');
    }

    public function testSend(Request $request, PhoneOtpService $otpService): RedirectResponse
    {
        $validated = $request->validate([
            'test_phone' => 'required|string',
            'test_country_code' => 'nullable|string',
        ]);

        $phone = $validated['test_phone'];
        if (! empty($validated['test_country_code'])) {
            $phone = $otpService->formatPhoneDisplay(
                (string) $validated['test_country_code'],
                $phone
            );
        }

        if (! $this->sender->isAvailable()) {
            return back()->withErrors(['test_phone' => 'Flaxxa OTP غير مُعدّ (توكن + قالب).']);
        }

        try {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $this->sender->send(WapiPhoneNormalizer::normalize($phone) ?: $phone, $code);
        } catch (\Throwable $e) {
            return back()->withErrors(['test_phone' => $e->getMessage()]);
        }

        return back()->with('success', 'تمت جدولة رسالة اختبار OTP (تحقق من سجل Flaxxa).');
    }

    public function restoreDefaults(): RedirectResponse
    {
        $this->settingsService->restoreDefaults();

        return redirect()->route('admin.settings.phone-otp.edit')->with('success', 'تمت استعادة الإعدادات الافتراضية.');
    }
}
