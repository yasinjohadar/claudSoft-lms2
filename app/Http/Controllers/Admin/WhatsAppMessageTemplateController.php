<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EvolutionInstance;
use App\Models\WhatsAppMessageTemplate;
use App\Services\WhatsApp\WhatsAppTemplateTestSendService;
use App\Support\WhatsAppSendErrorMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class WhatsAppMessageTemplateController extends Controller
{
    public function __construct(
        private WhatsAppTemplateTestSendService $testSendService
    ) {}

    public function index(Request $request)
    {
        $query = WhatsAppMessageTemplate::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active == '1');
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        $templates = $query->orderBy('name')->paginate(15);

        $evolutionInstances = EvolutionInstance::orderByDesc('is_default')->orderBy('instance_name')->get();
        $defaultEvolutionInstance = $evolutionInstances->firstWhere('is_default', true)
            ?? $evolutionInstances->firstWhere('connection_status', 'open');

        return view('admin.pages.whatsapp-templates.index', compact(
            'templates',
            'evolutionInstances',
            'defaultEvolutionInstance'
        ));
    }

    public function create()
    {
        return view('admin.pages.whatsapp-templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:100|unique:whatsapp_message_templates,slug',
            'body' => 'required|string',
            'type' => 'required|in:text,template',
            'language' => 'required|string|max:10',
            'meta_template_name' => 'nullable|string|max:255',
            'variables' => 'nullable|array',
            'variables.*' => 'string|max:50',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        if (!empty($validated['variables'])) {
            $validated['variables'] = array_values(array_filter($validated['variables']));
        } else {
            $validated['variables'] = null;
        }
        if ($validated['type'] === 'template' && empty($validated['meta_template_name'])) {
            $validated['meta_template_name'] = null;
        }

        WhatsAppMessageTemplate::create($validated);

        return redirect()->route('admin.whatsapp-templates.index')
            ->with('success', 'تم إنشاء قالب الرسالة بنجاح.');
    }

    public function edit(WhatsAppMessageTemplate $whatsapp_template)
    {
        return view('admin.pages.whatsapp-templates.edit', compact('whatsapp_template'));
    }

    public function update(Request $request, WhatsAppMessageTemplate $whatsapp_template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:100|unique:whatsapp_message_templates,slug,' . $whatsapp_template->id,
            'body' => 'required|string',
            'type' => 'required|in:text,template',
            'language' => 'required|string|max:10',
            'meta_template_name' => 'nullable|string|max:255',
            'variables' => 'nullable|array',
            'variables.*' => 'string|max:50',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        if (!empty($validated['variables'])) {
            $validated['variables'] = array_values(array_filter($validated['variables']));
        } else {
            $validated['variables'] = null;
        }
        if ($validated['type'] === 'template' && empty($validated['meta_template_name'])) {
            $validated['meta_template_name'] = null;
        }

        $whatsapp_template->update($validated);

        return redirect()->route('admin.whatsapp-templates.index')
            ->with('success', 'تم تحديث قالب الرسالة بنجاح.');
    }

    public function destroy(WhatsAppMessageTemplate $whatsapp_template)
    {
        $whatsapp_template->delete();
        return redirect()->route('admin.whatsapp-templates.index')
            ->with('success', 'تم حذف قالب الرسالة بنجاح.');
    }

    /**
     * API: get template by id (for use in send form / other places).
     */
    public function getTemplate(WhatsAppMessageTemplate $whatsapp_template)
    {
        return response()->json([
            'id' => $whatsapp_template->id,
            'name' => $whatsapp_template->name,
            'body' => $whatsapp_template->body,
            'type' => $whatsapp_template->type,
            'language' => $whatsapp_template->language,
            'meta_template_name' => $whatsapp_template->meta_template_name,
            'variables' => $whatsapp_template->variables ?? [],
        ]);
    }

    public function previewTest(Request $request, WhatsAppMessageTemplate $whatsapp_template): JsonResponse
    {
        return response()->json([
            'success' => true,
            'body' => $this->testSendService->renderForTest($whatsapp_template),
            'template_name' => $whatsapp_template->name,
            'template_type' => $whatsapp_template->type,
        ]);
    }

    public function sendTest(Request $request, WhatsAppMessageTemplate $whatsapp_template): JsonResponse
    {
        $validated = $this->validateTestRequest($request);

        try {
            $this->testSendService->sendTest(
                $whatsapp_template,
                $validated['phone'],
                $validated['evolution_instance_name'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال رسالة الاختبار بنجاح إلى '.$validated['phone'],
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الإرسال: '.WhatsAppSendErrorMessage::fromThrowable($e),
            ], 500);
        }
    }

    /**
     * @return array{phone: string, evolution_instance_name?: string|null}
     */
    private function validateTestRequest(Request $request): array
    {
        $validated = $request->validate([
            'phone' => 'required|string|max:30',
            'evolution_instance_name' => 'nullable|string|max:255',
        ]);

        $validated['phone'] = trim($validated['phone']);

        if (! empty($validated['evolution_instance_name'])) {
            $exists = EvolutionInstance::where('instance_name', $validated['evolution_instance_name'])->exists();
            if (! $exists) {
                abort(response()->json([
                    'success' => false,
                    'message' => 'Instance Evolution المحدد غير موجود.',
                ], 422));
            }
        }

        return $validated;
    }
}
