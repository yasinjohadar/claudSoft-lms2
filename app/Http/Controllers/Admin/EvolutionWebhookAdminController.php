<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AIModel;
use App\Models\EvolutionInstance;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppWebhookEvent;
use App\Services\QueueWorkerService;
use App\Services\WhatsApp\Evolution\EvolutionService;
use App\Services\WhatsApp\WhatsAppSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class EvolutionWebhookAdminController extends Controller
{
    public function __construct(
        private EvolutionService $evolutionService,
        private WhatsAppSettingsService $settingsService
    ) {}

    public function index(): View
    {
        $instance = $this->evolutionService->activeInstanceName();
        $webhook = null;
        $error = null;

        try {
            if ($instance !== '') {
                // clientFor لا client: الـ instance قد يملك رابطاً ومفتاحاً خاصَّين به،
                // واستخدام المفتاح العام معه يرجع Unauthorized رغم أنه متصل ويعمل.
                $webhook = $this->evolutionService->clientFor(null, $instance)->getWebhook($instance);
            }
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        $settings = $this->evolutionService->getSettings();
        $webhookBaseUrl = $settings['evolution_webhook_base_url'] ?? '';
        $appUrl = rtrim((string) config('app.url'), '/');

        return view('admin.pages.evolution-api.webhook.index', [
            'instance' => $instance,
            'webhook' => $webhook,
            'webhookUrl' => $this->evolutionService->webhookUrl($instance),
            'webhookBaseUrl' => $webhookBaseUrl,
            'appUrl' => $appUrl,
            'events' => $this->evolutionService->defaultWebhookEvents(),
            'error' => $error,
            'webhookEventsCount' => \App\Models\WhatsAppWebhookEvent::count(),
            'isLocalWebhookUrl' => $this->evolutionService->isLocalWebhookBaseUrl(),
            'usesCustomWebhookBaseUrl' => $webhookBaseUrl !== '',
            // instance الرد التلقائي قد يختلف عن النشط — نعرضه لتحذير الأدمن
            'autoReplyInstance' => trim((string) ($this->settingsService->getSettings()['auto_reply_evolution_instance'] ?? '')),
            // رابط خادم Evolution — يُعرض للتفريق بينه وبين رابط المنصة (خلط شائع)
            'evolutionBaseUrl' => rtrim((string) ($settings['evolution_base_url'] ?? ''), '/'),
        ]);
    }

    public function saveUrl(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'evolution_webhook_base_url' => ['nullable', 'string', 'max:500'],
        ]);

        $url = rtrim(trim((string) ($validated['evolution_webhook_base_url'] ?? '')), '/');
        if ($url !== '' && ! filter_var($url, FILTER_VALIDATE_URL)) {
            return back()
                ->withInput()
                ->with('error', 'رابط المنصة غير صالح. استخدم صيغة مثل https://lms.example.com');
        }

        $this->settingsService->updateSettings([
            'evolution_webhook_base_url' => $url,
        ]);

        $message = $url === ''
            ? 'تمت إعادة استخدام APP_URL الافتراضي لرابط Webhook.'
            : 'تم حفظ رابط المنصة العام: '.$url;

        return back()->with('success', $message);
    }

    public function activate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'evolution_webhook_base_url' => ['nullable', 'string', 'max:500'],
            'instance' => ['nullable', 'string', 'max:150'],
        ]);

        // يقبل اسم instance صراحةً: التفعيل كان مقصوراً على الـ instance النشط،
        // فإن اختلف عنه instance الرد التلقائي بقي الأخير بلا webhook مسجَّل.
        $instance = trim((string) ($validated['instance'] ?? '')) ?: $this->evolutionService->activeInstanceName();
        abort_if($instance === '', 422, 'حدّد Instance في الإعدادات أولاً.');

        $url = rtrim(trim((string) ($validated['evolution_webhook_base_url'] ?? '')), '/');
        if ($url !== '' && ! filter_var($url, FILTER_VALIDATE_URL)) {
            return back()
                ->withInput()
                ->with('error', 'رابط المنصة غير صالح. استخدم صيغة مثل https://lms.example.com');
        }

        if ($request->has('evolution_webhook_base_url')) {
            $this->settingsService->updateSettings([
                'evolution_webhook_base_url' => $url,
            ]);
        }

        $webhookUrl = $this->evolutionService->webhookUrl($instance);
        $settings = $this->evolutionService->getSettings();

        try {
            // clientFor لا client: يحترم بيانات اعتماد الـ instance الخاصة إن وُجدت.
            // ملاحظة: الترويسة تبقى بالمفتاح العام لأن verifyRequest في نقطة الاستقبال
            // تقارن معه هو، لا مع مفتاح الـ instance.
            $this->evolutionService->clientFor(null, $instance)->setWebhook($instance, [
                'enabled' => true,
                'url' => $webhookUrl,
                'webhookByEvents' => false,
                'webhookBase64' => false,
                'events' => $this->evolutionService->defaultWebhookEvents(),
                'headers' => array_filter([
                    'apikey' => $settings['evolution_api_key'] ?? null,
                ]),
            ]);

            return back()->with('success', 'تم تفعيل Webhook بنجاح على Evolution لـ «'.$instance.'». الرابط: '.$webhookUrl);
        } catch (\Throwable $e) {
            return back()->with('error', 'فشل تفعيل Webhook: '.$e->getMessage());
        }
    }

    /**
     * تشخيص مسار الرد التلقائي خطوة بخطوة.
     *
     * الأعطال التي عطّلت الرد التلقائي (رابط يرجع 404، instance غير موجود،
     * webhook مسجَّل لـ instance آخر) كانت كلها صامتة تماماً. هذه النقطة تكشفها
     * في طلب واحد بدل التخمين.
     */
    public function diagnose(): JsonResponse
    {
        $settings = $this->settingsService->getSettings();
        $steps = [];

        $add = function (string $label, ?bool $ok, string $detail = '', ?string $hint = null) use (&$steps) {
            $steps[] = [
                'label' => $label,
                'status' => $ok === null ? 'warning' : ($ok ? 'ok' : 'fail'),
                'detail' => $detail,
                'hint' => $hint,
            ];
        };

        // 1) الـ instance المستخدم للرد التلقائي
        $autoReplyInstance = trim((string) ($settings['auto_reply_evolution_instance'] ?? ''));
        $activeInstance = $this->evolutionService->activeInstanceName();
        $instance = $autoReplyInstance ?: $activeInstance;

        if ($instance === '') {
            $add('تحديد Instance', false, 'لم يُحدَّد أي instance للرد التلقائي.', 'اختر Instance من إعدادات الرد التلقائي.');

            return response()->json(['success' => false, 'instance' => null, 'steps' => $steps]);
        }

        $record = EvolutionInstance::where('instance_name', $instance)->first();
        $add(
            'الـ Instance موجود',
            (bool) $record,
            'الاسم: '.$instance,
            $record ? null : 'هذا الاسم غير مسجَّل في قائمة الـ instances — اختر واحداً موجوداً.'
        );

        if ($record) {
            $connected = $record->connection_status === 'open';
            $add(
                'الـ Instance متصل',
                $connected,
                'الحالة: '.$record->connection_status,
                $connected ? null : 'أعد ربط الرقم من صفحة الـ Instances (مسح QR).'
            );
        }

        // 2) تطابق instance الرد مع الـ instance النشط
        if ($autoReplyInstance !== '' && $activeInstance !== '' && $autoReplyInstance !== $activeInstance) {
            $add(
                'تطابق Instance الرد مع النشط',
                null,
                'الرد التلقائي: «'.$autoReplyInstance.'» — النشط: «'.$activeInstance.'»',
                'مختلفان: تأكد أن الـ webhook مفعَّل لـ instance الرد التلقائي تحديداً.'
            );
        }

        // 3) رابط الـ webhook
        $expectedUrl = $this->evolutionService->webhookUrl($instance);
        $isLocal = $this->evolutionService->isLocalWebhookBaseUrl();
        $add(
            'رابط Webhook عام',
            ! $isLocal,
            $expectedUrl,
            $isLocal ? 'الرابط محلي ولا يمكن لـ Evolution الوصول إليه — اضبط رابط المنصة العام.' : null
        );

        // 4) التسجيل الفعلي على Evolution
        $registeredUrl = null;
        try {
            // clientFor: يستخدم مفتاح الـ instance الخاص إن وُجد بدل المفتاح العام
            $webhook = $this->evolutionService->clientFor(null, $instance)->getWebhook($instance);
            $registeredUrl = data_get($webhook, 'url') ?? data_get($webhook, 'webhook.url');
            $enabled = (bool) (data_get($webhook, 'enabled') ?? data_get($webhook, 'webhook.enabled'));
            $events = (array) (data_get($webhook, 'events') ?? data_get($webhook, 'webhook.events') ?? []);

            $add('Webhook مسجَّل ومفعَّل على Evolution', $enabled, (string) ($registeredUrl ?: '—'),
                $enabled ? null : 'اضغط «تفعيل Webhook» لهذا الـ instance.');

            $matches = is_string($registeredUrl) && rawurldecode($registeredUrl) === rawurldecode($expectedUrl);
            $add('الرابط المسجَّل يطابق المتوقَّع', $matches,
                $matches ? 'مطابق' : 'المسجَّل: '.($registeredUrl ?: '—'),
                $matches ? null : 'أعد التفعيل لتحديث الرابط على Evolution.');

            $hasUpsert = in_array('MESSAGES_UPSERT', array_map('strtoupper', array_map('strval', $events)), true);
            $add('حدث MESSAGES_UPSERT مفعَّل', $hasUpsert, implode(', ', array_slice($events, 0, 8)) ?: '—',
                $hasUpsert ? null : 'بدونه لا تصل الرسائل الواردة إطلاقاً — أعد التفعيل.');
        } catch (\Throwable $e) {
            $usesOwnCreds = $record && $record->hasCustomCredentials();
            $add('قراءة إعداد Webhook من Evolution', false, $e->getMessage(),
                str_contains(mb_strtolower($e->getMessage()), 'unauthor')
                    ? ($usesOwnCreds
                        ? 'مفتاح API الخاص بهذا الـ Instance غير صحيح — عدّله من صفحة الـ Instances.'
                        : 'مفتاح Evolution API العام غير صحيح — عدّله من إعدادات Evolution.')
                    : 'تحقق من رابط Evolution ومفتاح API.');
        }

        // 5) هل يستجيب الرابط فعلاً؟ (هذا ما يكشف عطل الـ 404)
        try {
            $probe = Http::withHeaders(array_filter(['apikey' => $settings['evolution_api_key'] ?? null]))
                ->timeout(15)
                ->acceptJson()
                ->post($expectedUrl, [
                    'event' => 'connection.update',
                    'instance' => $instance,
                    'data' => ['state' => 'open'],
                ]);

            $code = $probe->status();
            $add(
                'الرابط يستجيب',
                $code === 200,
                'HTTP '.$code,
                match (true) {
                    $code === 404 => 'خلل توجيه: الرابط لا يطابق أي راوت (غالباً بسبب رموز في اسم الـ instance).',
                    $code === 401 => 'خلل توثيق: مفتاح API لا يطابق المحفوظ في الإعدادات.',
                    $code === 200 => null,
                    default => 'استجابة غير متوقعة من الخادم.',
                }
            );
        } catch (\Throwable $e) {
            $add('الرابط يستجيب', false, $e->getMessage(), 'تعذّر الوصول للرابط من الخادم نفسه.');
        }

        // 6) الإعدادات العامة للرد التلقائي
        $add('WhatsApp مفعَّل', (bool) ($settings['whatsapp_enabled'] ?? false), '',
            ($settings['whatsapp_enabled'] ?? false) ? null : 'فعّل WhatsApp من الإعدادات العامة.');
        $add('الرد التلقائي مفعَّل', (bool) ($settings['auto_reply'] ?? false), '',
            ($settings['auto_reply'] ?? false) ? null : 'فعّل الرد التلقائي من تبويب الرد التلقائي.');
        $add('المزود = Evolution', ($settings['whatsapp_provider'] ?? '') === 'evolution',
            'المزود الحالي: '.($settings['whatsapp_provider'] ?? '—'),
            ($settings['whatsapp_provider'] ?? '') === 'evolution' ? null : 'الرد التلقائي يعمل مع Evolution فقط.');

        // 7) نموذج الذكاء الاصطناعي (عند تفعيله)
        if ($settings['auto_reply_use_ai'] ?? false) {
            $model = AIModel::find($settings['auto_reply_ai_model_id'] ?? null);
            $add('نموذج الذكاء الاصطناعي متاح', $model && $model->is_active,
                $model ? $model->name : 'غير محدد',
                ($model && $model->is_active) ? null : 'اختر نموذجاً مفعَّلاً من تبويب الرد التلقائي.');
        }

        // 8) عامل الطابور
        $worker = app(QueueWorkerService::class)->status();
        $running = (bool) ($worker['running'] ?? false);
        $add('عامل الطابور يعمل', $running, $running ? ('PID '.($worker['pid'] ?? '—')) : 'متوقف',
            $running ? null : 'شغّل العامل من تبويب عامل الطابور — بدونه لا يُرسل أي رد.');

        $failed = collect($steps)->where('status', 'fail')->count();

        return response()->json([
            'success' => $failed === 0,
            'instance' => $instance,
            'expected_url' => $expectedUrl,
            'failed_count' => $failed,
            'steps' => $steps,
            'activity' => $this->recentActivity(),
        ]);
    }

    /**
     * لقطة عن النشاط الحديث: تحدّد أين تتوقف السلسلة فعلياً حين تكون كل
     * الفحوص خضراء ومع ذلك لا يصل رد — وهو ما لا يظهر من الإعدادات وحدها.
     */
    protected function recentActivity(): array
    {
        $out = [
            'last_event_at' => null,
            'unprocessed_events' => 0,
            'last_inbound' => [],
            'pending_jobs' => null,
            'failed_jobs' => null,
            'worker_stale_hint' => null,
        ];

        try {
            $lastEvent = WhatsAppWebhookEvent::orderByDesc('id')->first();
            $out['last_event_at'] = $lastEvent?->created_at?->diffForHumans();
            $out['unprocessed_events'] = WhatsAppWebhookEvent::whereNull('processed_at')->count();

            // آخر الرسائل الواردة، ومعها هل تلاها رد صادر لنفس جهة الاتصال
            $inbound = WhatsAppMessage::with('contact')
                ->where('direction', WhatsAppMessage::DIRECTION_INBOUND)
                ->orderByDesc('id')
                ->limit(5)
                ->get();

            foreach ($inbound as $msg) {
                $reply = WhatsAppMessage::where('contact_id', $msg->contact_id)
                    ->where('direction', WhatsAppMessage::DIRECTION_OUTBOUND)
                    ->where('id', '>', $msg->id)
                    ->orderBy('id')
                    ->first();

                $out['last_inbound'][] = [
                    'at' => optional($msg->created_at)->format('Y-m-d H:i:s'),
                    'from' => $msg->contact?->wa_id ?? '—',
                    'body' => mb_substr((string) $msg->body, 0, 70),
                    'replied' => (bool) $reply,
                    'reply_status' => $reply?->status,
                ];
            }

            if (config('queue.default') === 'database') {
                $out['pending_jobs'] = (int) DB::table('jobs')->count();
                $out['failed_jobs'] = (int) DB::table('failed_jobs')
                    ->where('failed_at', '>=', now()->subDay())->count();
            }

            // أشيع سبب لتوقف الردود رغم أن كل شيء يبدو سليماً
            if (($out['pending_jobs'] ?? 0) > 0) {
                $out['worker_stale_hint'] = 'توجد وظائف منتظرة في الطابور ولم تُنفَّذ. '
                    .'إن كان العامل يعمل فهو غالباً يشغّل نسخة قديمة من الكود — '
                    .'نفّذ php artisan queue:restart بعد كل نشر.';
            }
        } catch (\Throwable $e) {
            $out['error'] = $e->getMessage();
        }

        return $out;
    }
}
