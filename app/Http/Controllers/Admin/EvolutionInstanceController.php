<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EvolutionInstance;
use App\Services\WhatsApp\Evolution\EvolutionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EvolutionInstanceController extends Controller
{
    public function __construct(
        private EvolutionService $evolutionService
    ) {}

    public function index(): View
    {
        $instances = [];
        $error = null;

        try {
            $this->evolutionService->syncInstances(true);
            $instances = EvolutionInstance::orderByDesc('is_default')->orderBy('instance_name')->get();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            $instances = EvolutionInstance::orderByDesc('is_default')->get();
        }

        return view('admin.pages.evolution-api.instances.index', compact('instances', 'error'));
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
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
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
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'instanceName' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9_-]+$/'],
        ]);

        $this->evolutionService->client()->createInstance([
            'instanceName' => $validated['instanceName'],
            'integration' => 'WHATSAPP-BAILEYS',
            'qrcode' => true,
        ]);

        $this->evolutionService->syncInstances();

        return redirect()
            ->route('admin.evolution-api.instances.connect', $validated['instanceName'])
            ->with('success', 'تم إنشاء Instance «' . $validated['instanceName'] . '». امسح QR للربط.');
    }

    public function restart(string $instanceName): RedirectResponse
    {
        $this->evolutionService->client()->restartInstance($instanceName);

        return back()->with('success', 'تم إعادة تشغيل Instance.');
    }

    public function logout(string $instanceName): RedirectResponse
    {
        $this->evolutionService->client()->logoutInstance($instanceName);
        $this->evolutionService->syncInstances();

        return back()->with('success', 'تم تسجيل الخروج من Instance.');
    }

    public function destroy(string $instanceName): RedirectResponse
    {
        $this->evolutionService->client()->deleteInstance($instanceName);
        EvolutionInstance::where('instance_name', $instanceName)->delete();

        return redirect()->route('admin.evolution-api.instances.index')->with('success', 'تم حذف Instance.');
    }

    public function sync(): RedirectResponse
    {
        $this->evolutionService->syncInstances(true);

        return back()->with('success', 'تمت مزامنة ' . count($this->evolutionService->syncInstances(true)) . ' Instance من Evolution API.');
    }
}
