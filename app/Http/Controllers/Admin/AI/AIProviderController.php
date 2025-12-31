<?php

namespace App\Http\Controllers\Admin\AI;

use App\Http\Controllers\Controller;
use App\Models\AIProvider;
use App\Services\AI\AIManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AIProviderController extends Controller
{
    protected AIManager $aiManager;

    public function __construct(AIManager $aiManager)
    {
        $this->aiManager = $aiManager;
    }

    /**
     * Display a listing of AI providers.
     */
    public function index(Request $request)
    {
        $query = AIProvider::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $providers = $query->orderBy('priority', 'desc')->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.ai.providers.index', compact('providers'));
    }

    /**
     * Show the form for creating a new provider.
     */
    public function create()
    {
        return view('admin.ai.providers.create');
    }

    /**
     * Store a newly created provider.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ai_providers,name',
            'type' => 'required|in:openai,gemini,glm,openrouter,custom',
            'api_key' => 'required|string',
            'api_url' => 'nullable|url',
            'model_name' => 'required|string|max:255',
            'config' => 'nullable|array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'priority' => 'integer|min:0|max:100',
        ]);

        DB::transaction(function () use ($validated) {
            // If setting as default, unset other defaults
            if ($validated['is_default'] ?? false) {
                AIProvider::where('id', '!=', 0)->update(['is_default' => false]);
            }

            AIProvider::create($validated);
        });

        return redirect()->route('admin.ai.providers.index')
            ->with('success', 'تم إنشاء مقدم الخدمة بنجاح');
    }

    /**
     * Display the specified provider.
     */
    public function show(AIProvider $provider)
    {
        $provider->load('requests');
        $stats = $this->getProviderStats($provider);

        return view('admin.ai.providers.show', compact('provider', 'stats'));
    }

    /**
     * Show the form for editing the provider.
     */
    public function edit(AIProvider $provider)
    {
        return view('admin.ai.providers.edit', compact('provider'));
    }

    /**
     * Update the provider.
     */
    public function update(Request $request, AIProvider $provider)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ai_providers,name,' . $provider->id,
            'type' => 'required|in:openai,gemini,glm,openrouter,custom',
            'api_key' => 'nullable|string',
            'api_url' => 'nullable|url',
            'model_name' => 'required|string|max:255',
            'config' => 'nullable|array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'priority' => 'integer|min:0|max:100',
        ]);

        DB::transaction(function () use ($validated, $provider) {
            // If setting as default, unset other defaults
            if ($validated['is_default'] ?? false) {
                AIProvider::where('id', '!=', $provider->id)->update(['is_default' => false]);
            }

            // Don't update API key if not provided
            if (empty($validated['api_key'])) {
                unset($validated['api_key']);
            }

            $provider->update($validated);
        });

        return redirect()->route('admin.ai.providers.index')
            ->with('success', 'تم تحديث مقدم الخدمة بنجاح');
    }

    /**
     * Remove the provider.
     */
    public function destroy(AIProvider $provider)
    {
        if ($provider->is_default) {
            return redirect()->back()
                ->with('error', 'لا يمكن حذف مقدم الخدمة الافتراضي');
        }

        $provider->delete();

        return redirect()->route('admin.ai.providers.index')
            ->with('success', 'تم حذف مقدم الخدمة بنجاح');
    }

    /**
     * Test provider connection
     */
    public function testConnection(AIProvider $provider)
    {
        try {
            $result = $provider->testConnection();

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Set as default provider
     */
    public function setDefault(AIProvider $provider)
    {
        $provider->setAsDefault();

        return redirect()->back()
            ->with('success', 'تم تعيين مقدم الخدمة كافتراضي');
    }

    /**
     * Get provider statistics
     */
    protected function getProviderStats(AIProvider $provider): array
    {
        $requests = $provider->requests();

        return [
            'total_requests' => $requests->count(),
            'completed_requests' => $requests->where('status', 'completed')->count(),
            'failed_requests' => $requests->where('status', 'failed')->count(),
            'total_tokens' => $requests->sum('tokens_used'),
            'total_cost' => $requests->sum('cost'),
            'average_response_time' => $requests->whereNotNull('response_time_ms')->avg('response_time_ms'),
        ];
    }
}
