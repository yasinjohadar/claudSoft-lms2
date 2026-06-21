<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\WhatsApp\Evolution\EvolutionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EvolutionWebhookAdminController extends Controller
{
    public function __construct(
        private EvolutionService $evolutionService
    ) {}

    public function index(): View
    {
        $instance = $this->evolutionService->activeInstanceName();
        $webhook = null;
        $error = null;

        try {
            if ($instance !== '') {
                $webhook = $this->evolutionService->client()->getWebhook($instance);
            }
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('admin.pages.evolution-api.webhook.index', [
            'instance' => $instance,
            'webhook' => $webhook,
            'webhookUrl' => $this->evolutionService->webhookUrl($instance),
            'events' => $this->evolutionService->defaultWebhookEvents(),
            'error' => $error,
            'webhookEventsCount' => \App\Models\WhatsAppWebhookEvent::count(),
            'isLocalWebhookUrl' => str_contains($this->evolutionService->webhookUrl($instance), '127.0.0.1')
                || str_contains($this->evolutionService->webhookUrl($instance), 'localhost'),
        ]);
    }

    public function activate(Request $request): RedirectResponse
    {
        $instance = $this->evolutionService->activeInstanceName();
        abort_if($instance === '', 422, 'حدّد Instance في الإعدادات أولاً.');

        $url = $this->evolutionService->webhookUrl($instance);
        $settings = $this->evolutionService->getSettings();

        try {
            $this->evolutionService->client()->setWebhook($instance, [
                'enabled' => true,
                'url' => $url,
                'webhookByEvents' => false,
                'webhookBase64' => false,
                'events' => $this->evolutionService->defaultWebhookEvents(),
                'headers' => array_filter([
                    'apikey' => $settings['evolution_api_key'] ?? null,
                ]),
            ]);

            return back()->with('success', 'تم تفعيل Webhook بنجاح على Evolution. الرابط: ' . $url);
        } catch (\Throwable $e) {
            return back()->with('error', 'فشل تفعيل Webhook: ' . $e->getMessage());
        }
    }
}
