<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailSetting;
use App\Services\SmtpConnectionTestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailSettingController extends Controller
{
    public function __construct(
        private SmtpConnectionTestService $connectionTester
    ) {}

    /**
     * Display email settings
     */
    public function index()
    {
        $settings = EmailSetting::orderBy('created_at', 'desc')->get();
        $activeSettings = EmailSetting::getActive();
        $providers = EmailSetting::getProviderPresets();

        return view('admin.pages.settings.email.index', compact('settings', 'activeSettings', 'providers'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $providers = EmailSetting::getProviderPresets();
        return view('admin.pages.settings.email.create', compact('providers'));
    }

    /**
     * Store new email configuration
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|string',
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|integer',
            'mail_username' => 'required|string|max:255',
            'mail_password' => 'required|string',
            'mail_encryption' => 'required|in:tls,ssl,none',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string|max:255',
        ]);

        $validated['mail_mailer'] = 'smtp';
        $validated['is_active'] = false;

        $setting = EmailSetting::create($validated);

        return redirect()
            ->route('admin.settings.email.index')
            ->with('success', 'تم إضافة إعدادات البريد الإلكتروني بنجاح');
    }

    /**
     * Show edit form
     */
    public function edit(EmailSetting $emailSetting)
    {
        $providers = EmailSetting::getProviderPresets();
        return view('admin.pages.settings.email.edit', compact('emailSetting', 'providers'));
    }

    /**
     * Update email configuration
     */
    public function update(Request $request, EmailSetting $emailSetting)
    {
        $validated = $request->validate([
            'provider' => 'required|string',
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|integer',
            'mail_username' => 'required|string|max:255',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'required|in:tls,ssl,none',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string|max:255',
        ]);

        // Only update password if provided
        if (empty($validated['mail_password'])) {
            unset($validated['mail_password']);
        }

        $emailSetting->update($validated);

        return redirect()
            ->route('admin.settings.email.index')
            ->with('success', 'تم تحديث إعدادات البريد الإلكتروني بنجاح');
    }

    /**
     * Delete email configuration
     */
    public function destroy(EmailSetting $emailSetting)
    {
        if ($emailSetting->is_active) {
            return back()->with('error', 'لا يمكن حذف الإعدادات النشطة');
        }

        $emailSetting->delete();

        return redirect()
            ->route('admin.settings.email.index')
            ->with('success', 'تم حذف إعدادات البريد الإلكتروني بنجاح');
    }

    /**
     * Activate email configuration
     */
    public function activate(EmailSetting $emailSetting)
    {
        $emailSetting->activate();

        return back()->with('success', 'تم تفعيل إعدادات البريد الإلكتروني بنجاح');
    }

    /**
     * Test email configuration
     */
    public function test(Request $request, EmailSetting $emailSetting)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            // Apply settings temporarily
            $emailSetting->applyToConfig();

            // Send test email
            Mail::raw('هذا بريد اختبار من نظام إدارة التعلم. إذا استلمت هذه الرسالة، فإن إعدادات SMTP تعمل بشكل صحيح.', function ($message) use ($request) {
                $message->to($request->test_email)
                    ->subject('اختبار إعدادات البريد الإلكتروني - LMS');
            });

            // Save test results
            $emailSetting->update([
                'test_results' => [
                    'status' => 'success',
                    'type' => 'send',
                    'message' => 'تم إرسال البريد الاختباري بنجاح',
                    'tested_email' => $request->test_email,
                    'tested_at' => now()->toDateTimeString(),
                ],
                'last_tested_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال البريد الاختباري بنجاح إلى ' . $request->test_email,
            ]);
        } catch (\Exception $e) {
            Log::error('Email test failed', [
                'error' => $e->getMessage(),
                'setting_id' => $emailSetting->id,
            ]);

            // Save test results
            $emailSetting->update([
                'test_results' => [
                    'status' => 'failed',
                    'type' => 'send',
                    'message' => $e->getMessage(),
                    'tested_email' => $request->test_email,
                    'tested_at' => now()->toDateTimeString(),
                ],
                'last_tested_at' => now(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'فشل إرسال البريد الاختباري: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test SMTP connection without sending email (saved setting).
     */
    public function testConnection(EmailSetting $emailSetting)
    {
        $result = $this->connectionTester->test($emailSetting->toConnectionConfig());

        $emailSetting->update([
            'test_results' => [
                'status' => $result['success'] ? 'success' : 'failed',
                'type' => 'connection',
                'message' => $result['message'],
                'tested_at' => now()->toDateTimeString(),
            ],
            'last_tested_at' => now(),
        ]);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Test SMTP connection without sending email (unsaved form data).
     */
    public function testConnectionTemp(Request $request)
    {
        $validated = $request->validate([
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|integer',
            'mail_username' => 'required|string|max:255',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'required|in:tls,ssl,none',
            'email_setting_id' => 'nullable|exists:email_settings,id',
        ]);

        $password = $validated['mail_password'] ?? null;
        if (empty($password) && ! empty($validated['email_setting_id'])) {
            $existing = EmailSetting::find($validated['email_setting_id']);
            $password = $existing?->mail_password;
        }

        if (empty($password)) {
            return response()->json([
                'success' => false,
                'message' => 'يرجى إدخال كلمة المرور لاختبار الاتصال.',
            ], 422);
        }

        $result = $this->connectionTester->test([
            'host' => $validated['mail_host'],
            'port' => (int) $validated['mail_port'],
            'encryption' => $validated['mail_encryption'],
            'username' => $validated['mail_username'],
            'password' => $password,
        ]);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Get provider preset via AJAX
     */
    public function getProviderPreset($provider)
    {
        $presets = EmailSetting::getProviderPresets();

        if (isset($presets[$provider])) {
            return response()->json($presets[$provider]);
        }

        return response()->json(['error' => 'Provider not found'], 404);
    }

    /**
     * Test email configuration before saving (temporary test)
     */
    public function testTemp(Request $request)
    {
        $validated = $request->validate([
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|integer',
            'mail_username' => 'required|string|max:255',
            'mail_password' => 'required|string',
            'mail_encryption' => 'required|in:tls,ssl,none',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string|max:255',
            'test_email' => 'required|email',
        ]);

        try {
            // Temporarily set mail configuration
            config([
                'mail.mailers.smtp.host' => $validated['mail_host'],
                'mail.mailers.smtp.port' => $validated['mail_port'],
                'mail.mailers.smtp.username' => $validated['mail_username'],
                'mail.mailers.smtp.password' => $validated['mail_password'],
                'mail.mailers.smtp.encryption' => $validated['mail_encryption'] !== 'none' ? $validated['mail_encryption'] : null,
                'mail.from.address' => $validated['mail_from_address'],
                'mail.from.name' => $validated['mail_from_name'],
            ]);

            // Send test email
            Mail::raw('هذا بريد اختبار من نظام إدارة التعلم. إذا استلمت هذه الرسالة، فإن إعدادات SMTP تعمل بشكل صحيح.', function ($message) use ($validated) {
                $message->to($validated['test_email'])
                    ->subject('اختبار إعدادات البريد الإلكتروني - LMS');
            });

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال البريد الاختباري بنجاح إلى ' . $validated['test_email'],
            ]);
        } catch (\Exception $e) {
            Log::error('Email test failed (temp)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'فشل إرسال البريد الاختباري: ' . $e->getMessage(),
            ], 500);
        }
    }
}
