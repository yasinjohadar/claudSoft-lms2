<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\N8nWebhookEndpoint;
use App\Models\OutgoingWebhookLog;
use App\Models\N8nIncomingWebhookHandler;
use App\Http\Requests\N8nWebhookEndpointRequest;
use App\Http\Requests\N8nIncomingHandlerRequest;
use App\Services\N8nWebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class N8nWebhookController extends Controller
{
    public function __construct(
        public N8nWebhookService $webhookService
    ) {
        $this->middleware(['auth', 'role:admin|super-admin']);
    }

    /**
     * Dashboard - Main overview
     */
    public function index()
    {
        $stats = $this->webhookService->getOverallStats();

        $recentLogs = OutgoingWebhookLog::with('endpoint')
            ->latest()
            ->limit(10)
            ->get();

        $activeEndpoints = N8nWebhookEndpoint::active()
            ->withCount('logs')
            ->get();

        return view('admin.n8n.index', compact('stats', 'recentLogs', 'activeEndpoints'));
    }

    /**
     * Endpoints Management - List all endpoints
     */
    public function endpoints()
    {
        $endpoints = N8nWebhookEndpoint::withCount('logs')
            ->latest()
            ->paginate(15);

        $availableEvents = config('webhooks.n8n.available_events', []);

        return view('admin.n8n.endpoints.index', compact('endpoints', 'availableEvents'));
    }

    /**
     * Show form to create new endpoint
     */
    public function createEndpoint()
    {
        $availableEvents = config('webhooks.n8n.available_events', []);

        return view('admin.n8n.endpoints.create', compact('availableEvents'));
    }

    /**
     * Store new endpoint
     */
    public function storeEndpoint(N8nWebhookEndpointRequest $request)
    {
        $data = $request->validated();

        // Generate secret key if not provided
        if (empty($data['secret_key'])) {
            $data['secret_key'] = Str::random(32);
        }

        // Parse headers if provided as JSON string
        if (isset($data['headers']) && is_string($data['headers'])) {
            $data['headers'] = json_decode($data['headers'], true) ?? [];
        }

        // Parse metadata if provided as JSON string
        if (isset($data['metadata']) && is_string($data['metadata'])) {
            $data['metadata'] = json_decode($data['metadata'], true) ?? [];
        }

        $endpoint = N8nWebhookEndpoint::create($data);

        return redirect()
            ->route('admin.n8n.endpoints.show', $endpoint)
            ->with('success', 'تم إنشاء نقطة النهاية بنجاح');
    }

    /**
     * Show endpoint details
     */
    public function showEndpoint(N8nWebhookEndpoint $endpoint)
    {
        $endpoint->load(['logs' => function ($query) {
            $query->latest()->limit(50);
        }]);

        $stats = $this->webhookService->getEndpointStats($endpoint);

        return view('admin.n8n.endpoints.show', compact('endpoint', 'stats'));
    }

    /**
     * Show form to edit endpoint
     */
    public function editEndpoint(N8nWebhookEndpoint $endpoint)
    {
        $availableEvents = config('webhooks.n8n.available_events', []);

        return view('admin.n8n.endpoints.edit', compact('endpoint', 'availableEvents'));
    }

    /**
     * Update endpoint
     */
    public function updateEndpoint(N8nWebhookEndpointRequest $request, N8nWebhookEndpoint $endpoint)
    {
        $data = $request->validated();

        // Parse headers if provided as JSON string
        if (isset($data['headers']) && is_string($data['headers'])) {
            $data['headers'] = json_decode($data['headers'], true) ?? [];
        }

        // Parse metadata if provided as JSON string
        if (isset($data['metadata']) && is_string($data['metadata'])) {
            $data['metadata'] = json_decode($data['metadata'], true) ?? [];
        }

        $endpoint->update($data);

        return redirect()
            ->route('admin.n8n.endpoints.show', $endpoint)
            ->with('success', 'تم تحديث نقطة النهاية بنجاح');
    }

    /**
     * Delete endpoint
     */
    public function destroyEndpoint(N8nWebhookEndpoint $endpoint)
    {
        $endpoint->delete();

        return redirect()
            ->route('admin.n8n.endpoints.index')
            ->with('success', 'تم حذف نقطة النهاية بنجاح');
    }

    /**
     * Toggle endpoint status
     */
    public function toggleEndpoint(N8nWebhookEndpoint $endpoint)
    {
        $endpoint->is_active ? $endpoint->deactivate() : $endpoint->activate();

        return back()->with('success', 'تم تغيير حالة نقطة النهاية بنجاح');
    }

    /**
     * Test endpoint
     */
    public function testEndpoint(N8nWebhookEndpoint $endpoint)
    {
        $log = $this->webhookService->testEndpoint($endpoint);

        return back()->with('success', 'تم إرسال طلب اختبار. تحقق من السجلات للنتيجة');
    }

    /**
     * Logs - View all logs
     */
    public function logs(Request $request)
    {
        $query = OutgoingWebhookLog::with('endpoint');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by event type
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        // Filter by endpoint
        if ($request->filled('endpoint_id')) {
            $query->where('endpoint_id', $request->endpoint_id);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs = $query->latest()->paginate(20);
        $endpoints = N8nWebhookEndpoint::all();

        return view('admin.n8n.logs.index', compact('logs', 'endpoints'));
    }

    /**
     * Show single log details
     */
    public function showLog(OutgoingWebhookLog $log)
    {
        $log->load('endpoint');

        return view('admin.n8n.logs.show', compact('log'));
    }

    /**
     * Retry failed log
     */
    public function retryLog(OutgoingWebhookLog $log)
    {
        if ($this->webhookService->retry($log)) {
            return back()->with('success', 'تم إعادة المحاولة بنجاح');
        }

        return back()->with('error', 'لا يمكن إعادة المحاولة. تحقق من الحد الأقصى للمحاولات');
    }

    /**
     * Incoming Handlers Management
     */
    public function handlers()
    {
        $handlers = N8nIncomingWebhookHandler::latest()->paginate(15);

        return view('admin.n8n.handlers.index', compact('handlers'));
    }

    /**
     * Show handler details
     */
    public function showHandler(N8nIncomingWebhookHandler $handler)
    {
        return view('admin.n8n.handlers.show', compact('handler'));
    }

    /**
     * Show form to create handler
     */
    public function createHandler()
    {
        return view('admin.n8n.handlers.create');
    }

    /**
     * Store new handler
     */
    public function storeHandler(N8nIncomingHandlerRequest $request)
    {
        $data = $request->validated();

        $handler = N8nIncomingWebhookHandler::create($data);

        return redirect()
            ->route('admin.n8n.handlers.show', $handler)
            ->with('success', 'تم إنشاء المعالج بنجاح');
    }

    /**
     * Show form to edit handler
     */
    public function editHandler(N8nIncomingWebhookHandler $handler)
    {
        return view('admin.n8n.handlers.edit', compact('handler'));
    }

    /**
     * Update handler
     */
    public function updateHandler(N8nIncomingHandlerRequest $request, N8nIncomingWebhookHandler $handler)
    {
        $handler->update($request->validated());

        return redirect()
            ->route('admin.n8n.handlers.show', $handler)
            ->with('success', 'تم تحديث المعالج بنجاح');
    }

    /**
     * Delete handler
     */
    public function destroyHandler(N8nIncomingWebhookHandler $handler)
    {
        $handler->delete();

        return redirect()
            ->route('admin.n8n.handlers.index')
            ->with('success', 'تم حذف المعالج بنجاح');
    }

    /**
     * Toggle handler status
     */
    public function toggleHandler(N8nIncomingWebhookHandler $handler)
    {
        $handler->is_active ? $handler->deactivate() : $handler->activate();

        return back()->with('success', 'تم تغيير حالة المعالج بنجاح');
    }

    /**
     * Documentation page
     */
    public function documentation()
    {
        $handlers = N8nIncomingWebhookHandler::active()->get();
        $endpoints = N8nWebhookEndpoint::active()->get();

        return view('admin.n8n.documentation', compact('handlers', 'endpoints'));
    }

    /**
     * Statistics page
     */
    public function statistics()
    {
        $stats = $this->webhookService->getOverallStats();

        // Get daily stats for the last 30 days
        $dailyStats = OutgoingWebhookLog::selectRaw('DATE(created_at) as date, status, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date', 'status')
            ->orderBy('date')
            ->get();

        // Get top performing endpoints
        $topEndpoints = N8nWebhookEndpoint::withCount([
            'logs',
            'logs as successful_logs' => function ($query) {
                $query->where('status', 'sent');
            },
            'logs as failed_logs' => function ($query) {
                $query->where('status', 'failed');
            }
        ])
        ->having('logs_count', '>', 0)
        ->orderByDesc('logs_count')
        ->limit(10)
        ->get();

        return view('admin.n8n.statistics', compact('stats', 'dailyStats', 'topEndpoints'));
    }
}
