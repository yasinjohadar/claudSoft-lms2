<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppMessageTemplate;
use App\Services\Auth\PasswordResetMessageRenderer;
use App\Services\Auth\PasswordResetMessageSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordResetMessageSettingsController extends Controller
{
    public function __construct(
        private PasswordResetMessageSettingsService $settingsService,
        private PasswordResetMessageRenderer $renderer
    ) {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function edit(): View
    {
        $settings = $this->settingsService->getSettings();
        $whatsappTemplates = WhatsAppMessageTemplate::active()
            ->byType(WhatsAppMessageTemplate::TYPE_TEXT)
            ->orderBy('name')
            ->get(['id', 'name']);
        $expireMinutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);
        $placeholders = array_keys($this->renderer->variables(
            new \App\Models\User(['name' => 'أحمد', 'email' => 'user@example.com']),
            'https://example.com/reset',
            $expireMinutes
        ));

        return view('admin.pages.settings.password-reset-message.edit', compact(
            'settings',
            'whatsappTemplates',
            'expireMinutes',
            'placeholders'
        ));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'whatsapp_template_id' => 'nullable|exists:whatsapp_message_templates,id',
            'whatsapp_body' => 'nullable|string|max:10000',
            'email_subject' => 'required|string|max:255',
            'email_body' => 'nullable|string|max:50000',
        ]);

        $this->settingsService->updateSettings([
            'whatsapp_template_id' => $validated['whatsapp_template_id'] ?? '',
            'whatsapp_body' => $validated['whatsapp_body'] ?? '',
            'email_subject' => $validated['email_subject'],
            'email_body' => $validated['email_body'] ?? '',
        ]);

        return redirect()
            ->route('admin.settings.password-reset-message.edit')
            ->with('success', 'تم حفظ رسالة إعادة تعيين كلمة المرور بنجاح.');
    }

    public function restoreDefaults(): RedirectResponse
    {
        $this->settingsService->restoreDefaults();

        return redirect()
            ->route('admin.settings.password-reset-message.edit')
            ->with('success', 'تمت استعادة الرسائل الافتراضية.');
    }
}
