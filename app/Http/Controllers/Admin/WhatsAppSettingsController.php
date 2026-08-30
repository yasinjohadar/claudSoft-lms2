<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EvolutionInstance;
use App\Services\Ai\AIModelService;
use App\Services\QueueWorkerService;
use App\Services\WhatsApp\AutoReply\WhatsAppAutoReplyAiGenerator;
use App\Services\WhatsApp\AutoReply\WhatsAppAutoReplyHumanizer;
use App\Services\WhatsApp\AutoReply\WhatsAppAutoReplyService;
use App\Services\WhatsApp\WhatsAppProviderFactory;
use App\Services\WhatsApp\WhatsAppSettingsService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class WhatsAppSettingsController extends Controller
{
    public function __construct(
        private WhatsAppSettingsService $settingsService,
        private AIModelService $aiModelService,
        private QueueWorkerService $queueWorkerService,
        private WhatsAppAutoReplyAiGenerator $autoReplyAiGenerator,
        private WhatsAppAutoReplyHumanizer $autoReplyHumanizer,
        private WhatsAppAutoReplyService $autoReplyService,
    ) {}

    /**
     * Display settings page
     */
    public function index()
    {
        $this->settingsService->initializeDefaults();
        $settings = $this->settingsService->getSettings();
        $aiModels = $this->aiModelService->getAvailableModels('chat');
        $queueWorkerStatus = $this->queueWorkerService->status();
        $evolutionInstances = EvolutionInstance::connected()->orderBy('instance_name')->get(['instance_name', 'phone_number', 'profile_name']);

        return view('admin.pages.whatsapp-settings.index', compact('settings', 'aiModels', 'queueWorkerStatus', 'evolutionInstances'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        // When WhatsApp Web is selected: use saved URL from settings if not in request
        if ($request->input('whatsapp_provider') === 'whatsapp_web') {
            $existing = $this->settingsService->getSettings();
            if (empty($request->input('whatsapp_web_service_url'))) {
                $request->merge(['whatsapp_web_service_url' => $existing['whatsapp_web_service_url'] ?? 'http://localhost:3000']);
            }
        }

        $request->merge([
            'auto_reply_ai_model_id' => $request->input('auto_reply_ai_model_id') ?: null,
        ]);

        // التبويب النشط يُقرأ من الطلب ويُعاد في الجلسة ليبقى مفتوحاً بعد الحفظ.
        // لا يُضاف إلى قواعد validate: updateSettings() يكتب كل مفتاح يصله،
        // فتسجيله هناك يُنشئ صفاً وهمياً في system_settings.
        $activeTab = preg_replace('/[^a-z0-9_\-]/', '', (string) $request->input('active_tab', 'general')) ?: 'general';

        $validated = $request->validate([
            'whatsapp_enabled' => 'nullable',
            'whatsapp_provider' => 'required|string|in:custom_api,whatsapp_web,evolution',
            'api_version' => 'required_if:whatsapp_provider,meta|nullable|string|max:10',
            'phone_number_id' => 'required_if:whatsapp_provider,meta|nullable|string|max:255',
            'waba_id' => 'nullable|string|max:255',
            'access_token' => 'nullable|string|max:500',
            'verify_token' => 'required_if:whatsapp_provider,meta|nullable|string|max:255',
            'app_secret' => 'nullable|string|max:255',
            'webhook_path' => 'nullable|string|max:255',
            'default_from' => 'nullable|string|max:50',
            'strict_signature' => 'nullable',
            'auto_reply' => 'nullable',
            'auto_reply_message' => 'nullable|string|max:500',
            'auto_reply_use_ai' => 'nullable',
            'auto_reply_ai_model_id' => 'nullable|integer|exists:ai_models,id',
            'auto_reply_ai_system_prompt' => 'nullable|string|max:4000',
            // exists: يمنع حفظ اسم instance غير موجود — وهو ما جعل الرد التلقائي
            // يستهدف "ClaudSoft New" غير الموجود فتُرفض كل الرسائل بـ instance mismatch
            'auto_reply_evolution_instance' => [
                'nullable',
                'string',
                'max:150',
                Rule::exists('evolution_instances', 'instance_name'),
            ],
            'auto_reply_faq_context' => 'nullable|string|max:8000',
            'auto_reply_initial_delay_min' => 'nullable|integer|min:0|max:30',
            'auto_reply_initial_delay_max' => 'nullable|integer|min:0|max:60',
            'auto_reply_typing_duration' => 'nullable|integer|min:1|max:15',
            'auto_reply_max_chunks' => 'nullable|integer|min:1|max:5',
            'auto_reply_chunk_max_chars' => 'nullable|integer|min:100|max:1000',
            'auto_reply_contact_cooldown' => 'nullable|integer|min:10|max:600',
            'auto_reply_debounce_seconds' => 'nullable|integer|min:1|max:60',
            'auto_reply_test_phone' => 'nullable|string|max:30',
            'timeout' => 'nullable|integer|min:1|max:300',
            'custom_api_url' => 'required_if:whatsapp_provider,custom_api|nullable|string|url|max:500',
            'custom_api_key' => 'nullable|string|max:500',
            'whatsapp_web_service_url' => 'required_if:whatsapp_provider,whatsapp_web|nullable|string|url|max:500',
            'whatsapp_web_api_token' => 'nullable|string|max:500',
            'delay_between_messages' => 'nullable|integer|min:1|max:60',
            'delay_between_broadcasts' => 'nullable|integer|min:1|max:60',
            'max_messages_per_minute' => 'nullable|integer|min:1|max:100',
            'random_delay_enabled' => 'nullable',
            'min_delay' => 'nullable|integer|min:1|max:10',
            'max_delay' => 'nullable|integer|min:1|max:10',
            'custom_api_method' => 'nullable|string|in:GET,POST',
            'custom_api_headers' => 'nullable|string|max:1000',
            'custom_api_preflight_enabled' => 'nullable',
            'custom_api_preflight_url' => 'nullable|string|url|max:500',
            'study_report_delivery' => 'nullable|string|in:email,whatsapp,both',
        ], [
            'whatsapp_provider.required' => 'نوع المزود مطلوب',
            'whatsapp_provider.in' => 'نوع المزود غير صالح',
            'api_version.required_if' => 'إصدار API مطلوب للمزود Meta',
            'phone_number_id.required_if' => 'معرف رقم الهاتف مطلوب للمزود Meta',
            'verify_token.required_if' => 'رمز التحقق مطلوب للمزود Meta',
            'custom_api_url.required_if' => 'رابط API مطلوب للمزود المخصص',
            'custom_api_url.url' => 'رابط API غير صالح',
            'whatsapp_web_service_url.required_if' => 'رابط خدمة WhatsApp Web مطلوب. يمكن تعبئته من صفحة إعدادات WhatsApp Web.',
            'whatsapp_web_service_url.url' => 'رابط خدمة WhatsApp Web غير صالح',
            'timeout.integer' => 'المهلة الزمنية يجب أن تكون رقماً',
            'timeout.min' => 'المهلة الزمنية يجب أن تكون على الأقل ثانية واحدة',
            'timeout.max' => 'المهلة الزمنية يجب أن تكون أقل من 300 ثانية',
            'auto_reply_evolution_instance.exists' => 'الـ Instance المختار للرد التلقائي غير موجود. اختر واحداً من القائمة.',
        ]);

        try {
            // Handle checkboxes
            $validated['whatsapp_enabled'] = $request->has('whatsapp_enabled') ? '1' : '0';
            $validated['strict_signature'] = $request->has('strict_signature') ? '1' : '0';
            $validated['auto_reply'] = $request->has('auto_reply') ? '1' : '0';
            $validated['auto_reply_use_ai'] = $request->has('auto_reply_use_ai') ? '1' : '0';
            $validated['random_delay_enabled'] = $request->has('random_delay_enabled') ? '1' : '0';
            $validated['custom_api_preflight_enabled'] = $request->has('custom_api_preflight_enabled') ? '1' : '0';

            if (empty($validated['auto_reply_ai_model_id'])) {
                $validated['auto_reply_ai_model_id'] = '';
            }
            $validated['auto_reply_ai_system_prompt'] = $validated['auto_reply_ai_system_prompt'] ?? '';
            $validated['auto_reply_faq_context'] = $validated['auto_reply_faq_context'] ?? '';
            $validated['auto_reply_evolution_instance'] = $validated['auto_reply_evolution_instance'] ?? '';
            $validated['auto_reply_test_phone'] = $validated['auto_reply_test_phone'] ?? '';

            if (isset($validated['auto_reply_initial_delay_max'], $validated['auto_reply_initial_delay_min'])
                && (int) $validated['auto_reply_initial_delay_max'] < (int) $validated['auto_reply_initial_delay_min']) {
                $validated['auto_reply_initial_delay_max'] = $validated['auto_reply_initial_delay_min'];
            }

            // If access_token, app_secret, custom_api_key, or whatsapp_web_api_token is empty, keep existing values
            if (empty($validated['access_token'])) {
                $existingSettings = $this->settingsService->getSettings();
                $validated['access_token'] = $existingSettings['access_token'] ?? '';
            }

            if (empty($validated['app_secret'])) {
                $existingSettings = $this->settingsService->getSettings();
                $validated['app_secret'] = $existingSettings['app_secret'] ?? '';
            }

            if (empty($validated['custom_api_key'])) {
                $existingSettings = $this->settingsService->getSettings();
                $validated['custom_api_key'] = $existingSettings['custom_api_key'] ?? '';
            }

            if (empty($validated['whatsapp_web_api_token'])) {
                $existingSettings = $this->settingsService->getSettings();
                $validated['whatsapp_web_api_token'] = $existingSettings['whatsapp_web_api_token'] ?? '';
            }

            if (empty($validated['study_report_delivery'] ?? null)) {
                $validated['study_report_delivery'] = $this->settingsService->getSettings()['study_report_delivery'] ?? 'both';
            }

            $this->settingsService->updateSettings($validated);

            return redirect()->route('admin.whatsapp-settings.index')
                ->with('success', 'تم حفظ الإعدادات بنجاح.')
                ->with('active_tab', $activeTab);
        } catch (\Exception $e) {
            Log::error('Error updating WhatsApp settings: '.$e->getMessage());

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حفظ الإعدادات: '.$e->getMessage())
                ->with('active_tab', $activeTab)
                ->withInput();
        }
    }

    /**
     * Preview AI auto-reply without sending WhatsApp message.
     */
    public function autoReplyPreview(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:2000',
        ]);

        $settings = $this->settingsService->getAutoReplySettings();
        $result = $this->autoReplyAiGenerator->preview(
            $settings,
            $validated['question'],
            $this->autoReplyHumanizer,
        );

        return response()->json([
            'success' => true,
            'reply' => $result['reply'],
            'chunks' => $result['chunks'],
        ]);
    }

    /**
     * Send full auto-reply pipeline to a test phone number.
     */
    public function autoReplyTestSend(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:2000',
            'test_phone' => 'nullable|string|max:30',
        ]);

        $settings = $this->settingsService->getAutoReplySettings();
        $phone = trim($validated['test_phone'] ?? $settings['auto_reply_test_phone'] ?? '');

        if ($phone === '') {
            return response()->json([
                'success' => false,
                'message' => 'أدخل رقم اختبار في الحقل أو احفظه في الإعدادات.',
            ], 422);
        }

        try {
            $this->autoReplyService->testSend($settings, $phone, $validated['question']);

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال الرد التجريبي بنجاح.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Auto-reply test send failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test connection to WhatsApp API
     */
    public function testConnection(Request $request)
    {
        try {
            $settings = $this->settingsService->getSettings();
            $provider = $request->input('whatsapp_provider', $settings['whatsapp_provider'] ?? 'evolution');

            // Get provider config
            if ($provider === 'custom_api') {
                $customApiUrl = $request->input('custom_api_url', $settings['custom_api_url'] ?? '');
                $customApiKey = $request->input('custom_api_key', $settings['custom_api_key'] ?? '');

                $config = [
                    'api_url' => $customApiUrl,
                    'api_key' => $customApiKey,
                    'api_method' => $request->input('custom_api_method', $settings['custom_api_method'] ?? 'POST'),
                    'headers' => $this->parseHeaders($request->input('custom_api_headers', $settings['custom_api_headers'] ?? [])),
                    'preflight_enabled' => filter_var($request->input('custom_api_preflight_enabled', $settings['custom_api_preflight_enabled'] ?? false), FILTER_VALIDATE_BOOLEAN),
                    'preflight_url' => $request->input('custom_api_preflight_url', $settings['custom_api_preflight_url'] ?? ''),
                ];
            } elseif ($provider === 'whatsapp_web') {
                $nodejsUrl = $request->input('whatsapp_web_service_url', $settings['whatsapp_web_service_url'] ?? 'http://localhost:3000');
                $apiToken = $request->input('whatsapp_web_api_token', $settings['whatsapp_web_api_token'] ?? '');

                $config = [
                    'nodejs_service_url' => $nodejsUrl,
                    'api_token' => $apiToken,
                ];
            } elseif ($provider === 'evolution') {
                $config = [
                    'base_url' => $request->input('evolution_base_url', $settings['evolution_base_url'] ?? ''),
                    'api_key' => $request->input('evolution_api_key') ?: ($settings['evolution_api_key'] ?? ''),
                    'instance_name' => $request->input('evolution_instance_name', $settings['evolution_instance_name'] ?? ''),
                ];
            } else {
                $apiVersion = $request->input('api_version', $settings['api_version'] ?? 'v20.0');
                $phoneNumberId = $request->input('phone_number_id', $settings['phone_number_id'] ?? '');
                // If access_token is empty in request, use from settings (for password fields that remain empty)
                $accessToken = $request->input('access_token');
                if (empty($accessToken)) {
                    $accessToken = $settings['access_token'] ?? '';
                }

                $config = [
                    'api_version' => $apiVersion,
                    'phone_number_id' => $phoneNumberId,
                    'access_token' => $accessToken,
                ];
            }

            // Create provider and test connection
            $providerInstance = WhatsAppProviderFactory::create($provider, $config);
            $result = $providerInstance->testConnection();

            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            Log::error('Error testing WhatsApp connection: '.$e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Queue worker: get status (for AJAX or initial page load).
     */
    public function queueWorkerStatus()
    {
        $status = $this->queueWorkerService->status();

        return response()->json($status);
    }

    /**
     * Queue worker: start.
     */
    public function queueWorkerStart()
    {
        $result = $this->queueWorkerService->start();

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Queue worker: stop.
     */
    public function queueWorkerStop()
    {
        $result = $this->queueWorkerService->stop();

        return response()->json($result);
    }

    /**
     * Parse headers from JSON string or array
     */
    protected function parseHeaders($headers): array
    {
        if (is_array($headers)) {
            return $headers;
        }

        if (is_string($headers)) {
            try {
                $decoded = json_decode($headers, true);

                return is_array($decoded) ? $decoded : [];
            } catch (\Exception $e) {
                return [];
            }
        }

        return [];
    }
}
