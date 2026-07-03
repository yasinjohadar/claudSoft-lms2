<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EvolutionInstance;
use App\Services\WhatsApp\Evolution\EvolutionApiException;
use App\Services\WhatsApp\Evolution\EvolutionService;
use App\Services\WhatsApp\WhatsAppSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class EvolutionInstanceController extends Controller
{
    public function __construct(
        private EvolutionService $evolutionService,
        private WhatsAppSettingsService $settingsService,
    ) {}

    public function index(): View
    {
        $instances = [];
        $error = null;

        try {
            $this->evolutionService->syncInstances(true);
            $instances = EvolutionInstance::orderByDesc('is_default')->orderBy('instance_name')->get();
        } catch (Throwable $e) {
            $error = EvolutionApiException::resolveUserMessage($e);
            $instances = EvolutionInstance::orderByDesc('is_default')->get();
        }

        return view('admin.pages.evolution-api.instances.index', [
            'instances' => $instances,
            'error' => $error,
            'rotationPoolCount' => EvolutionInstance::rotationPoolCount(),
            'defaultInstanceName' => $this->settingsService->getSettings()['evolution_instance_name'] ?? '',
        ]);
    }

    public function connect(string $instanceName): View
    {
        $instance = EvolutionInstance::where('instance_name', $instanceName)->firstOrFail();
        $settings = $this->evolutionService->getSettings();

        return view('admin.pages.evolution-api.instances.connect', compact('instance', 'settings'));
    }

    public function fetchQr(string $instanceName): JsonResponse
    {
        try {
            $response = $this->evolutionService->client()->connectInstance($instanceName);
            $qr = $response['base64'] ?? $response['qrcode']['base64'] ?? $response['code'] ?? null;

            if ($qr) {
                EvolutionInstance::where('instance_name', $instanceName)->update([
                    'qr_code' => $qr,
                    'connection_status' => 'connecting',
                ]);
            }

            return response()->json([
                'success' => true,
                'qr' => $qr,
                'raw' => $response,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => EvolutionApiException::resolveUserMessage($e),
            ], 422);
        }
    }

    public function status(string $instanceName): JsonResponse
    {
        try {
            $state = $this->evolutionService->client()->getConnectionState($instanceName);
            $connection = strtolower((string) ($state['instance']['state'] ?? $state['state'] ?? 'close'));

            EvolutionInstance::where('instance_name', $instanceName)->update([
                'connection_status' => $connection,
                'connected_at' => $connection === 'open' ? now() : null,
            ]);

            $this->evolutionService->syncInstances();

            return response()->json(['success' => true, 'state' => $connection, 'raw' => $state]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => EvolutionApiException::resolveUserMessage($e),
            ], 422);
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'instanceName' => ['required', 'string', 'max:150'],
            'set_as_default' => ['nullable', 'boolean'],
        ]);

        $instanceName = trim($validated['instanceName']);

        try {
            $this->evolutionService->client()->createInstance([
                'instanceName' => $instanceName,
                'integration' => 'WHATSAPP-BAILEYS',
                'qrcode' => true,
            ]);

            if ($request->boolean('set_as_default')) {
                $this->assignAsDefaultInstance($instanceName);
            }

            $this->evolutionService->syncInstances($request->boolean('set_as_default'));

            $message = 'تم إنشاء Instance «'.$instanceName.'». امسح QR للربط.';
            if ($request->boolean('set_as_default')) {
                $message .= ' وتم تعيينه كافتراضي.';
            }

            return redirect()
                ->route('admin.evolution-api.instances.connect', ['instanceName' => $instanceName])
                ->with('success', $message);
        } catch (Throwable $e) {
            return $this->evolutionErrorRedirect($e);
        }
    }

    public function link(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'instanceName' => ['required', 'string', 'max:150'],
            'set_as_default' => ['nullable', 'boolean'],
        ]);

        $instanceName = trim($validated['instanceName']);

        try {
            $this->evolutionService->client()->getConnectionState($instanceName);

            if ($request->boolean('set_as_default')) {
                $this->assignAsDefaultInstance($instanceName);
            }

            $this->evolutionService->syncInstances($request->boolean('set_as_default'));

            if (! EvolutionInstance::where('instance_name', $instanceName)->exists()) {
                return back()->with('error', 'لم يُعثر على Instance «'.$instanceName.'» بعد المزامنة. تأكد من الاسم كما في Evolution Manager.');
            }

            $message = 'تم ربط Instance «'.$instanceName.'» بنجاح.';
            if ($request->boolean('set_as_default')) {
                $message .= ' وتم تعيينه كافتراضي — يمكنك حفظ الإعدادات من صفحة الإعدادات.';
            }

            return back()->with('success', $message);
        } catch (Throwable $e) {
            return $this->evolutionErrorRedirect($e);
        }
    }

    public function setDefault(string $instanceName): RedirectResponse
    {
        $instance = EvolutionInstance::where('instance_name', $instanceName)->firstOrFail();

        try {
            $this->assignAsDefaultInstance($instance->instance_name);
            $this->evolutionService->syncInstances(true);

            return back()->with('success', 'تم تعيين «'.$instance->instance_name.'» كـ Instance افتراضي.');
        } catch (Throwable $e) {
            return $this->evolutionErrorRedirect($e);
        }
    }

    public function restart(string $instanceName): RedirectResponse
    {
        try {
            $this->evolutionService->client()->restartInstance($instanceName);

            return back()->with('success', 'تم إعادة تشغيل Instance.');
        } catch (Throwable $e) {
            return $this->evolutionErrorRedirect($e);
        }
    }

    public function logout(string $instanceName): RedirectResponse
    {
        try {
            $this->evolutionService->client()->logoutInstance($instanceName);
            $this->evolutionService->syncInstances();

            return back()->with('success', 'تم تسجيل الخروج من Instance.');
        } catch (Throwable $e) {
            return $this->evolutionErrorRedirect($e);
        }
    }

    public function destroy(string $instanceName): RedirectResponse
    {
        $instance = EvolutionInstance::where('instance_name', $instanceName)->first();
        $remoteDeleted = false;
        $remoteAlreadyGone = false;

        try {
            $this->evolutionService->client()->deleteInstance($instanceName);
            $remoteDeleted = true;
        } catch (Throwable $e) {
            if (EvolutionApiException::isNotFound($e)) {
                $remoteAlreadyGone = true;
            } else {
                return $this->evolutionErrorRedirect($e);
            }
        }

        $wasDefault = (bool) ($instance?->is_default);
        $settingsName = $this->settingsService->getSettings()['evolution_instance_name'] ?? '';

        EvolutionInstance::where('instance_name', $instanceName)->delete();

        if ($wasDefault || $settingsName === $instanceName) {
            $replacement = EvolutionInstance::orderByDesc('connection_status')
                ->orderBy('instance_name')
                ->first();

            if ($replacement) {
                $this->assignAsDefaultInstance($replacement->instance_name);
            } elseif ($settingsName === $instanceName) {
                $this->settingsService->updateSettings(['evolution_instance_name' => '']);
            }
        }

        $message = match (true) {
            $remoteDeleted => 'تم حذف Instance من Evolution والمنصة.',
            $remoteAlreadyGone => 'تم إزالة Instance من المنصة (لم يعد موجوداً على Evolution API).',
            default => 'تم حذف Instance من المنصة.',
        };

        return redirect()->route('admin.evolution-api.instances.index')->with('success', $message);
    }

    public function sync(): RedirectResponse
    {
        try {
            $synced = $this->evolutionService->syncInstances(true);

            return back()->with('success', 'تمت مزامنة ' . count($synced) . ' Instance من Evolution API.');
        } catch (Throwable $e) {
            return $this->evolutionErrorRedirect($e);
        }
    }

    public function toggleRotation(string $instanceName): RedirectResponse
    {
        $instance = EvolutionInstance::where('instance_name', $instanceName)->firstOrFail();
        $instance->update(['rotation_enabled' => ! $instance->rotation_enabled]);

        $status = $instance->rotation_enabled ? 'مفعّل' : 'معطّل';

        return back()->with('success', 'تم '.$status.' مشاركة «'.$instance->instance_name.'» في التبديل التلقائي.');
    }

    private function assignAsDefaultInstance(string $instanceName): void
    {
        $this->settingsService->updateSettings([
            'evolution_instance_name' => $instanceName,
        ]);

        EvolutionInstance::query()->update(['is_default' => false]);
        EvolutionInstance::where('instance_name', $instanceName)->update(['is_default' => true]);
    }

    private function evolutionErrorRedirect(Throwable $e): RedirectResponse
    {
        return back()->with('error', EvolutionApiException::resolveUserMessage($e));
    }
}
