<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\User;
use App\Models\WapiAutomationRule;
use App\Models\WapiTemplate;
use App\Services\Flaxxa\FlaxxaTemplateVariableResolver;
use App\Services\Flaxxa\WapiAutomationService;
use App\Services\WhatsApp\BroadcastWhatsAppMessage;
use App\Support\WapiPhoneNormalizer;
use App\Support\WapiTemplatePayloadBuilder;
use App\WapiAutomation\WapiAutomationEventKey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WapiAutomationRuleController extends Controller
{
    public function __construct(
        private FlaxxaTemplateVariableResolver $variableResolver,
        private BroadcastWhatsAppMessage $broadcastWhatsApp
    ) {}

    public function index(): View
    {
        $rules = WapiAutomationRule::query()
            ->with(['wapiTemplate', 'course', 'group'])
            ->orderBy('event_key')
            ->orderByDesc('priority')
            ->orderBy('sort_order')
            ->paginate(25);

        return view('admin.pages.flaxxa-wapi.automation.index', [
            'rules' => $rules,
            'eventLabels' => WapiAutomationEventKey::labels(),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.flaxxa-wapi.automation.create', $this->formOptions());
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'templates' => WapiTemplate::query()->orderBy('name')->get(),
            'courses' => Course::query()->where('is_published', true)->orderBy('title')->get(),
            'groups' => CourseGroup::query()->where('is_active', true)->orderBy('name')->get(),
            'eventKeys' => WapiAutomationEventKey::labels(),
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $rule = $this->validateAndFill(new WapiAutomationRule, $request);
        $rule->save();

        return redirect()
            ->route('admin.flaxxa-wapi.automation.index')
            ->with('success', 'تم حفظ قاعدة الأتمتة.');
    }

    public function edit(WapiAutomationRule $wapiAutomationRule): View
    {
        return view('admin.pages.flaxxa-wapi.automation.edit', array_merge(
            $this->formOptions(),
            ['rule' => $wapiAutomationRule]
        ));
    }

    public function update(Request $request, WapiAutomationRule $wapiAutomationRule): RedirectResponse
    {
        $this->validateAndFill($wapiAutomationRule, $request);
        $wapiAutomationRule->save();

        return redirect()
            ->route('admin.flaxxa-wapi.automation.index')
            ->with('success', 'تم تحديث القاعدة.');
    }

    public function destroy(WapiAutomationRule $wapiAutomationRule): RedirectResponse
    {
        $wapiAutomationRule->delete();

        return redirect()
            ->route('admin.flaxxa-wapi.automation.index')
            ->with('success', 'تم حذف القاعدة.');
    }

    public function testSend(Request $request, WapiAutomationRule $wapiAutomationRule, WapiAutomationService $automationService): RedirectResponse
    {
        $validated = $request->validate([
            'test_phone' => ['required', 'string', 'max:40'],
            'test_student_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        if (! $automationService->isTokenConfigured()) {
            return back()->withErrors(['test_phone' => 'عيّن توكن Flaxxa في الإعدادات أولاً.']);
        }

        $phone = WapiPhoneNormalizer::normalize($validated['test_phone']);
        if (! WapiPhoneNormalizer::isValidE164Digits($phone)) {
            return back()->withErrors(['test_phone' => 'رقم غير صالح (تنسيق دولي).']);
        }

        $student = isset($validated['test_student_id'])
            ? User::query()->findOrFail((int) $validated['test_student_id'])
            : auth()->user();

        $course = $wapiAutomationRule->course_id ? Course::query()->find($wapiAutomationRule->course_id) : null;
        $group = $wapiAutomationRule->group_id ? CourseGroup::query()->find($wapiAutomationRule->group_id) : null;

        $headerVars = is_array($wapiAutomationRule->header_variables) ? array_values(array_map('strval', $wapiAutomationRule->header_variables)) : [];
        $bodyVars = is_array($wapiAutomationRule->body_variables) ? array_values(array_map('strval', $wapiAutomationRule->body_variables)) : [];

        $extra = [
            'lesson_title' => 'درس تجريبي',
            'quiz_title' => 'اختبار تجريبي',
            'module_title' => 'وحدة تجريبية',
            'course_title' => $course?->title ?? 'كورس تجريبي',
            'score' => 10,
            'total_questions' => 20,
            'student_name' => $student->name,
            'student_email' => $student->email ?? '',
        ];

        [$h, $b] = $this->variableResolver->resolveArrays($headerVars, $bodyVars, $student, $course, $group, $extra);

        $components = WapiTemplatePayloadBuilder::cloudApiComponentsFromVariables($h, $b);
        $tplName = $wapiAutomationRule->effectiveTemplateName();
        $lang = $wapiAutomationRule->effectiveLanguage();

        if ($tplName === null || $tplName === '' || $lang === null || $lang === '') {
            return back()->withErrors(['test_phone' => 'القاعدة لا تملك اسم قالب أو لغة صالحة.']);
        }

        app(\App\Services\WapiOutboundDispatcher::class)->queueTemplate(
            $phone,
            $tplName,
            $lang,
            $components,
            null,
            $wapiAutomationRule->wapi_template_id,
            [
                'automation_test' => true,
                'rule_id' => $wapiAutomationRule->id,
                'student_id' => $student->id,
            ],
            0
        );

        return redirect()
            ->route('admin.flaxxa-wapi.messages.index')
            ->with('success', 'تم جدولة رسالة اختبار لهذه القاعدة.');
    }

    private function validateAndFill(WapiAutomationRule $rule, Request $request): WapiAutomationRule
    {
        $validated = $request->validate([
            'event_key' => ['required', 'string', 'max:190'],
            'is_active' => ['nullable', 'boolean'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'wapi_template_id' => ['nullable', 'exists:wapi_templates,id'],
            'template_name' => ['nullable', 'string', 'max:255'],
            'language' => ['nullable', 'string', 'max:32'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'group_id' => ['nullable', 'exists:course_groups,id'],
            'module_id' => ['nullable', 'exists:course_modules,id'],
            'lesson_id' => ['nullable', 'exists:lessons,id'],
            'header_variables_text' => ['nullable', 'string', 'max:65500'],
            'body_variables_text' => ['nullable', 'string', 'max:65500'],
            'cooldown_seconds' => ['nullable', 'integer', 'min:0', 'max:864000'],
            'dedupe_template' => ['nullable', 'string', 'max:512'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $rule->fill([
            'event_key' => $validated['event_key'],
            'is_active' => $request->boolean('is_active', true),
            'priority' => (int) ($validated['priority'] ?? 0),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'wapi_template_id' => $validated['wapi_template_id'] ?? null,
            'template_name' => $validated['template_name'] ?? null,
            'language' => $validated['language'] ?? null,
            'course_id' => $validated['course_id'] ?? null,
            'group_id' => $validated['group_id'] ?? null,
            'module_id' => $validated['module_id'] ?? null,
            'lesson_id' => $validated['lesson_id'] ?? null,
            'header_variables' => $this->linesToArray($validated['header_variables_text'] ?? ''),
            'body_variables' => $this->linesToArray($validated['body_variables_text'] ?? ''),
            'cooldown_seconds' => (int) ($validated['cooldown_seconds'] ?? 0),
            'dedupe_template' => $validated['dedupe_template'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return $rule;
    }

    /**
     * @return array<int, string>
     */
    private function linesToArray(string $text): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $out = [];
        foreach ($lines as $line) {
            $trim = trim((string) $line);
            if ($trim !== '') {
                $out[] = $trim;
            }
        }

        return array_values($out);
    }
}
