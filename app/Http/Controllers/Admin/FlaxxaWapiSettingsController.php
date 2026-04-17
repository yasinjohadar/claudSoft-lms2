<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\WhatsApp\WhatsAppSettingsService;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FlaxxaWapiSettingsController extends Controller
{
    public function __construct(
        private WhatsAppSettingsService $settingsService
    ) {}

    public function index(): View
    {
        $this->settingsService->initializeDefaults();
        $settings = $this->settingsService->getSettings();

        $hasToken = ($settings['wapi_token'] ?? '') !== '';

        return view('admin.pages.flaxxa-wapi.settings.index', [
            'hasToken' => $hasToken,
            'wapi_base_url' => $settings['wapi_base_url'] ?? '',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'wapi_token' => ['nullable', 'string', 'max:4000'],
            'wapi_base_url' => ['nullable', 'string', 'max:500'],
        ], [], [
            'wapi_token' => 'التوكن',
            'wapi_base_url' => 'عنوان واجهة الـ API',
        ]);

        $baseTrimmed = trim((string) ($validated['wapi_base_url'] ?? ''));
        if ($baseTrimmed !== '' && $this->wapiBaseUrlHostMatchesThisApp($baseTrimmed)) {
            return back()->withInput()->withErrors([
                'wapi_base_url' => 'عنوان واجهة Flaxxa يجب أن يشير إلى خادم wapi (مثل wapi.flaxxa.com حسب ما يزودكم به المزود)، وليس إلى عنوان موقعك أو لوحة التحكم هنا.',
            ]);
        }

        $payload = [
            'wapi_base_url' => $baseTrimmed,
        ];

        $newToken = trim((string) ($request->input('wapi_token') ?? ''));
        if ($newToken !== '') {
            $payload['wapi_token'] = $newToken;
        }

        $this->settingsService->updateSettings($payload);

        return redirect()
            ->route('admin.flaxxa-wapi.settings.index')
            ->with('success', 'تم حفظ إعدادات Flaxxa.');
    }

    /**
     * اختبار الاتصال (يستخدم القيم في النموذج؛ إن كان حقل التوكن فارغاً يُستخدم التوكن المحفوظ أو .env).
     */
    public function testConnection(Request $request, WhatsAppService $whatsAppService): JsonResponse
    {
        $request->validate([
            'wapi_token' => ['nullable', 'string', 'max:4000'],
            'wapi_base_url' => ['nullable', 'string', 'max:500'],
        ]);

        $base = trim((string) $request->input('wapi_base_url', ''));
        if ($base !== '' && $this->wapiBaseUrlHostMatchesThisApp($base)) {
            return response()->json([
                'success' => false,
                'message' => 'عنوان الأساس يشير إلى نفس نطاق هذا التطبيق. يجب استخدام عنوان خادم Flaxxa wapi (يُزوَّد من المزود)، وليس رابط موقعك.',
            ]);
        }

        $result = $whatsAppService->testConnection(
            $request->input('wapi_token'),
            $request->input('wapi_base_url'),
        );

        return response()->json($result);
    }

    /**
     * إذا وُضِع عنوان LMS كعنوان واجهة wapi، يصل الطلب إلى Laravel هنا فيُعاد 404 بنمط "The route ... could not be found".
     */
    private function wapiBaseUrlHostMatchesThisApp(string $rawBaseUrl): bool
    {
        $normalized = app(WhatsAppService::class)->normalizeWapiBaseUrl($rawBaseUrl);
        $wapiHost = parse_url($normalized, PHP_URL_HOST);
        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);

        if (! is_string($wapiHost) || $wapiHost === '' || ! is_string($appHost) || $appHost === '') {
            return false;
        }

        return strcasecmp($wapiHost, $appHost) === 0;
    }
}
