<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WapiTemplate;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WapiTemplateController extends Controller
{
    public function index(Request $request): View
    {
        $q = WapiTemplate::query()->orderByDesc('id');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $q->where(function ($sub) use ($search) {
                $sub->where('name', 'like', '%'.$search.'%')
                    ->orWhere('language', 'like', '%'.$search.'%')
                    ->orWhere('provider_template_id', 'like', '%'.$search.'%');
            });
        }

        $templates = $q->paginate(20)->withQueryString();

        return view('admin.pages.flaxxa-wapi.templates.index', compact('templates'));
    }

    public function create(): View
    {
        return view('admin.pages.flaxxa-wapi.templates.create', [
            'template' => new WapiTemplate,
            'headerPlaceholders' => 0,
            'bodyPlaceholders' => 0,
            'previewTemplate' => '',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTemplate($request, null);
        $structure = $this->buildStructureFromValidated($validated);

        WapiTemplate::query()->create([
            'name' => $validated['name'],
            'language' => $validated['language'],
            'provider_template_id' => $validated['provider_template_id'] ?: null,
            'structure' => $structure,
        ]);

        return redirect()
            ->route('admin.flaxxa-wapi.templates.index')
            ->with('success', 'تم حفظ القالب.');
    }

    public function edit(WapiTemplate $wapiTemplate): View
    {
        $structure = $wapiTemplate->structure ?? [];

        return view('admin.pages.flaxxa-wapi.templates.edit', [
            'template' => $wapiTemplate,
            'headerPlaceholders' => (int) ($structure['header_placeholders'] ?? 0),
            'bodyPlaceholders' => (int) ($structure['body_placeholders'] ?? 0),
            'previewTemplate' => (string) ($structure['preview_template'] ?? ''),
        ]);
    }

    public function update(Request $request, WapiTemplate $wapiTemplate): RedirectResponse
    {
        $validated = $this->validateTemplate($request, $wapiTemplate);
        $structure = $this->buildStructureFromValidated($validated);

        $wapiTemplate->update([
            'name' => $validated['name'],
            'language' => $validated['language'],
            'provider_template_id' => $validated['provider_template_id'] ?: null,
            'structure' => $structure,
        ]);

        return redirect()
            ->route('admin.flaxxa-wapi.templates.index')
            ->with('success', 'تم تحديث القالب.');
    }

    public function destroy(WapiTemplate $wapiTemplate): RedirectResponse
    {
        $wapiTemplate->delete();

        return redirect()
            ->route('admin.flaxxa-wapi.templates.index')
            ->with('success', 'تم حذف القالب.');
    }

    /**
     * مزامنة القوالب من Meta عبر Flaxxa (GET getTemplates) ثم upsert محلياً.
     */
    public function syncFromProvider(WhatsAppService $service): RedirectResponse
    {
        $result = $service->fetchTemplates();

        if (! ($result['success'] ?? false)) {
            return back()->withErrors([
                'sync' => $result['message'] ?? 'تعذّرت مزامنة القوالب.',
            ]);
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($result['templates'] as $raw) {
            $name = (string) ($raw['name'] ?? '');
            $language = (string) ($raw['language'] ?? '');
            if ($name === '' || $language === '') {
                $skipped++;

                continue;
            }

            $structure = $this->buildStructureFromProvider($raw);

            $existing = WapiTemplate::query()
                ->where('name', $name)
                ->where('language', $language)
                ->first();

            if ($existing) {
                $existing->update([
                    'provider_template_id' => isset($raw['id']) ? (string) $raw['id'] : $existing->provider_template_id,
                    'structure' => $structure,
                ]);
                $updated++;
            } else {
                WapiTemplate::query()->create([
                    'name' => $name,
                    'language' => $language,
                    'provider_template_id' => isset($raw['id']) ? (string) $raw['id'] : null,
                    'structure' => $structure,
                ]);
                $created++;
            }
        }

        return redirect()
            ->route('admin.flaxxa-wapi.templates.index')
            ->with('success', sprintf(
                'تمت المزامنة: %d جديدة، %d محدَّثة%s.',
                $created,
                $updated,
                $skipped > 0 ? '، '.$skipped.' تم تجاوزها' : ''
            ));
    }

    /**
     * @return array<string, mixed>
     */
    private function validateTemplate(Request $request, ?WapiTemplate $existing): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('wapi_templates', 'name')
                    ->where('language', $request->input('language'))
                    ->ignore($existing?->id),
            ],
            'language' => ['required', 'string', 'max:24'],
            'provider_template_id' => ['nullable', 'string', 'max:128'],
            'header_placeholders' => ['nullable', 'integer', 'min:0', 'max:50'],
            'body_placeholders' => ['nullable', 'integer', 'min:0', 'max:50'],
            'preview_template' => ['nullable', 'string', 'max:10000'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function buildStructureFromValidated(array $validated): array
    {
        return [
            'header_placeholders' => (int) ($validated['header_placeholders'] ?? 0),
            'body_placeholders' => (int) ($validated['body_placeholders'] ?? 0),
            'preview_template' => ! empty($validated['preview_template']) ? (string) $validated['preview_template'] : null,
            'source' => 'manual',
        ];
    }

    /**
     * يبني structure من بيانات قالب قادم من Flaxxa (Meta).
     *
     * Flaxxa يعيد components قد تكون: نصاً JSON، أو مصفوفة جاهزة.
     *
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    private function buildStructureFromProvider(array $raw): array
    {
        $components = $this->normalizeProviderComponents($raw['components'] ?? null);

        $header = $this->findComponent($components, 'HEADER');
        $body = $this->findComponent($components, 'BODY');
        $footer = $this->findComponent($components, 'FOOTER');

        $headerFormat = is_array($header) ? strtoupper((string) ($header['format'] ?? 'TEXT')) : null;
        $hasMediaHeader = in_array($headerFormat, ['IMAGE', 'VIDEO', 'DOCUMENT'], true);

        $headerText = is_array($header) ? (string) ($header['text'] ?? '') : '';
        $bodyText = is_array($body) ? (string) ($body['text'] ?? '') : '';
        $footerText = is_array($footer) ? (string) ($footer['text'] ?? '') : '';

        return [
            'source' => 'provider',
            'status' => isset($raw['status']) ? strtoupper((string) $raw['status']) : null,
            'category' => isset($raw['category']) ? (string) $raw['category'] : null,
            'description' => isset($raw['description']) ? (string) $raw['description'] : null,
            'components_raw' => $components,
            'header_format' => $headerFormat,
            'has_media_header' => $hasMediaHeader,
            'header_text' => $headerText,
            'header_placeholders' => $this->countPlaceholders($headerText),
            'body_placeholders' => $this->countPlaceholders($bodyText),
            'preview_template' => $bodyText !== '' ? $bodyText : null,
            'footer_text' => $footerText,
            'synced_at' => now()->toIso8601String(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeProviderComponents(mixed $components): array
    {
        if (is_string($components)) {
            $decoded = json_decode($components, true);
            $components = is_array($decoded) ? $decoded : [];
        }
        if (! is_array($components)) {
            return [];
        }

        return array_values(array_filter($components, 'is_array'));
    }

    /**
     * @param  array<int, array<string, mixed>>  $components
     * @return array<string, mixed>|null
     */
    private function findComponent(array $components, string $type): ?array
    {
        foreach ($components as $c) {
            if (strtoupper((string) ($c['type'] ?? '')) === strtoupper($type)) {
                return $c;
            }
        }

        return null;
    }

    private function countPlaceholders(string $text): int
    {
        if ($text === '') {
            return 0;
        }
        preg_match_all('/\{\{\s*(\d+)\s*\}\}/', $text, $m);
        if (empty($m[1])) {
            return 0;
        }

        return (int) max(array_map('intval', $m[1]));
    }
}
