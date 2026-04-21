<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\User;
use App\Models\WapiMessage;
use App\Models\WapiTemplate;
use App\Services\Flaxxa\FlaxxaTemplateVariableResolver;
use App\Services\WapiOutboundDispatcher;
use App\Services\WhatsApp\BroadcastWhatsAppMessage;
use App\Services\WhatsApp\WhatsAppSettingsService;
use App\Services\WhatsAppService;
use App\Support\WapiPhoneNormalizer;
use App\Support\WapiTemplatePayloadBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FlaxxaWapiController extends Controller
{
    public function __construct(
        private WapiOutboundDispatcher $dispatcher,
        private BroadcastWhatsAppMessage $broadcastWhatsApp,
        private WhatsAppSettingsService $whatsappSettings,
        private FlaxxaTemplateVariableResolver $flaxxaVariables
    ) {}

    public function messagesIndex(Request $request): View
    {
        $query = WapiMessage::query()->orderByDesc('id');

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('phone')) {
            $q = $request->string('phone');
            $query->where('phone', 'like', '%'.$q.'%');
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }

        $messages = $query->paginate(25)->withQueryString();

        return view('admin.pages.flaxxa-wapi.messages.index', compact('messages'));
    }

    public function messageShow(WapiMessage $wapiMessage): View
    {
        return view('admin.pages.flaxxa-wapi.messages.show', ['message' => $wapiMessage]);
    }

    /**
     * استعلام حالة التوصيل من Flaxxa لرسالة مُسجَّلة باستخدام message_id المُخزَّن.
     */
    public function messageCheckStatus(WapiMessage $wapiMessage, WhatsAppService $service): JsonResponse
    {
        $response = $wapiMessage->response ?? [];
        $json = is_array($response['json'] ?? null) ? $response['json'] : [];
        $messageId = $json['message_id'] ?? null;

        if ($messageId === null) {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد message_id محفوظ لهذه الرسالة (قد تكون لم تُرسل بعد أو فشلت).',
            ]);
        }

        $result = $service->getMessageResponse($messageId);

        return response()->json($result);
    }

    public function sendMessageForm(): View
    {
        return view('admin.pages.flaxxa-wapi.send.message');
    }

    public function sendMessage(Request $request): RedirectResponse
    {
        $maxKb = (int) config('services.whatsapp.max_attachment_kb', 5120);

        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:40'],
            'message' => ['nullable', 'string', 'max:4096'],
            'header' => ['nullable', 'string', 'max:1024'],
            'footer' => ['nullable', 'string', 'max:1024'],
            'buttons' => ['nullable', 'string', 'max:2048'],
            'attachment' => ['nullable', 'file', 'max:'.$maxKb, 'mimes:jpeg,jpg,png,gif,webp,pdf,doc,docx,xls,xlsx'],
        ], [], [
            'phone' => 'رقم الهاتف',
        ]);

        if (! $request->hasFile('attachment') && (! isset($validated['message']) || trim((string) $validated['message']) === '')) {
            return back()->withInput()->withErrors(['message' => 'أدخل نص الرسالة أو أرفق ملفاً.']);
        }

        $normalized = WapiPhoneNormalizer::normalize($validated['phone']);
        if (! WapiPhoneNormalizer::isValidE164Digits($normalized)) {
            return back()->withInput()->withErrors(['phone' => 'رقم الهاتف غير صالح (تنسيق دولي).']);
        }

        $path = $request->file('attachment')?->store('wapi-temp', 'local');

        $this->dispatcher->queueMessage(
            $validated['phone'],
            (string) ($validated['message'] ?? ''),
            $path,
            (string) ($validated['header'] ?? ''),
            (string) ($validated['footer'] ?? ''),
            (string) ($validated['buttons'] ?? ''),
        );

        return redirect()
            ->route('admin.flaxxa-wapi.messages.index')
            ->with('success', 'تمت جدولة إرسال الرسالة عبر Flaxxa. تأكد من تشغيل الطابور (queue worker).');
    }

    public function sendTemplateForm(): View
    {
        $templates = WapiTemplate::query()->orderBy('name')->get();
        $courses = Course::query()->where('is_published', true)->orderBy('title')->get();
        $groups = CourseGroup::query()->where('is_active', true)->orderBy('name')->get();

        return view('admin.pages.flaxxa-wapi.send.template', compact('templates', 'courses', 'groups'));
    }

    public function sendTemplate(Request $request): RedirectResponse
    {
        $maxKb = (int) config('services.whatsapp.max_attachment_kb', 5120);

        $validated = $request->validate([
            'send_type' => ['required', Rule::in(['individual', 'broadcast'])],
            'phone' => [
                'nullable',
                'string',
                'max:40',
                Rule::requiredIf(fn () => $request->input('send_type') === 'individual' && ! $request->filled('student_id')),
            ],
            'student_id' => ['nullable', 'integer', 'exists:users,id'],
            'course_id' => [
                Rule::requiredIf(fn () => $request->input('send_type') === 'broadcast' && ! $request->filled('group_id')),
                'nullable',
                'exists:courses,id',
            ],
            'group_id' => ['nullable', 'exists:course_groups,id'],
            'wapi_template_id' => ['nullable', 'integer', 'exists:wapi_templates,id'],
            'template_name' => ['required_without:wapi_template_id', 'string', 'max:255'],
            'language' => ['required_without:wapi_template_id', 'string', 'max:24'],
            'header_variables_text' => ['nullable', 'string', 'max:65500'],
            'body_variables_text' => ['nullable', 'string', 'max:65500'],
            'header_vars' => ['nullable', 'array'],
            'header_vars.*' => ['nullable', 'string', 'max:8192'],
            'body_vars' => ['nullable', 'array'],
            'body_vars.*' => ['nullable', 'string', 'max:8192'],
            'attachment' => ['nullable', 'file', 'max:'.$maxKb, 'mimes:jpeg,jpg,png,gif,webp,pdf,doc,docx,mp4'],
        ], [
            'send_type.required' => 'نوع الإرسال مطلوب',
            'course_id.required' => 'اختر الكورس أو المجموعة للإرسال الجماعي',
        ]);

        $template = null;
        if (! empty($validated['wapi_template_id'])) {
            $template = WapiTemplate::query()->findOrFail($validated['wapi_template_id']);
            if (($validated['template_name'] ?? '') === '') {
                $validated['template_name'] = $template->name;
            }
            if (($validated['language'] ?? '') === '') {
                $validated['language'] = $template->language;
            }
        }

        $headerVars = $this->collectVars(
            $request->input('header_vars'),
            $validated['header_variables_text'] ?? ''
        );
        $bodyVars = $this->collectVars(
            $request->input('body_vars'),
            $validated['body_variables_text'] ?? ''
        );

        if ($template !== null && is_array($template->structure ?? null)) {
            $structure = $template->structure;
            $headerNeed = (int) ($structure['header_placeholders'] ?? 0);
            $bodyNeed = (int) ($structure['body_placeholders'] ?? 0);
            if ($headerNeed > 0 && count($headerVars) < $headerNeed) {
                return back()->withInput()->withErrors(['body_variables_text' => 'عدد متغيرات الرأس أقل من المطلوب للقالب ('.$headerNeed.').']);
            }
            if ($bodyNeed > 0 && count($bodyVars) < $bodyNeed) {
                return back()->withInput()->withErrors(['body_variables_text' => 'عدد متغيرات النص أقل من المطلوب للقالب ('.$bodyNeed.').']);
            }

            if (! empty($structure['has_media_header']) && ! $request->hasFile('attachment')) {
                return back()->withInput()->withErrors(['attachment' => 'هذا القالب يتطلب مرفقاً في الرأس (Media Header).']);
            }
        }

        $storedAttachment = $request->file('attachment')?->store('wapi-temp', 'local');

        if ($validated['send_type'] === 'broadcast') {
            $courseId = isset($validated['course_id']) ? (int) $validated['course_id'] : null;
            $groupId = isset($validated['group_id']) ? (int) $validated['group_id'] : null;

            $students = $this->broadcastWhatsApp->getStudentsByCriteria($courseId, $groupId);

            if ($students->isEmpty()) {
                $this->deleteStoredAttachmentIfAny($storedAttachment);

                return back()
                    ->withInput()
                    ->withErrors(['course_id' => 'لا يوجد طلاب مطابقون للمعايير المحددة برقم هاتف صالح.']);
            }

            $course = $courseId ? Course::query()->find($courseId) : null;
            $group = $groupId ? CourseGroup::query()->find($groupId) : null;

            $cumulativeDelay = 0;
            $queued = 0;

            foreach ($students as $student) {
                $phoneDigits = $this->broadcastWhatsApp->normalizedPhoneDigitsForWapi($student);
                if ($phoneDigits === null) {
                    continue;
                }

                [$resHeader, $resBody] = $this->replaceVarsWithLmsPlaceholders(
                    $headerVars,
                    $bodyVars,
                    $student,
                    $course,
                    $group
                );

                $components = WapiTemplatePayloadBuilder::cloudApiComponentsFromVariables($resHeader, $resBody);

                $perAttachment = $storedAttachment !== null
                    ? $this->duplicateWapiTempFile($storedAttachment)
                    : null;

                if ($queued > 0) {
                    $cumulativeDelay += $this->whatsappSettings->calculateDelay();
                }

                $this->dispatcher->queueTemplate(
                    $phoneDigits,
                    (string) $validated['template_name'],
                    (string) $validated['language'],
                    $components,
                    $perAttachment,
                    isset($validated['wapi_template_id']) ? (int) $validated['wapi_template_id'] : null,
                    [
                        'send_type' => 'broadcast',
                        'header_variables' => $resHeader,
                        'variables' => $resBody,
                        'student_id' => $student->id,
                    ],
                    $cumulativeDelay
                );
                $queued++;
            }

            $this->deleteStoredAttachmentIfAny($storedAttachment);

            if ($queued === 0) {
                return back()
                    ->withInput()
                    ->withErrors(['course_id' => 'لم يُجدَّل أي إرسال — تحقق من أرقام الهواتف للطلاب المختارين.']);
            }

            return redirect()
                ->route('admin.flaxxa-wapi.messages.index')
                ->with('success', 'تمت جدولة إرسال القالب لـ '.$queued.' مستلم عبر Flaxxa.');
        }

        // individual
        $course = null;
        $group = null;
        $phoneDigits = null;

        if (! empty($validated['student_id'])) {
            $student = User::query()->findOrFail((int) $validated['student_id']);
            $phoneDigits = $this->broadcastWhatsApp->normalizedPhoneDigitsForWapi($student);
            if ($phoneDigits === null) {
                $this->deleteStoredAttachmentIfAny($storedAttachment);

                return back()->withInput()->withErrors(['student_id' => 'الطالب المحدد لا يملك رقم هاتف صالحاً لـ Flaxxa.']);
            }

            [$resHeader, $resBody] = $this->replaceVarsWithLmsPlaceholders(
                $headerVars,
                $bodyVars,
                $student,
                $course,
                $group
            );
        } else {
            $phoneDigits = WapiPhoneNormalizer::normalize((string) ($validated['phone'] ?? ''));
            if (! WapiPhoneNormalizer::isValidE164Digits($phoneDigits)) {
                $this->deleteStoredAttachmentIfAny($storedAttachment);

                return back()->withInput()->withErrors(['phone' => 'رقم الهاتف غير صالح (تنسيق دولي).']);
            }
            $resHeader = $headerVars;
            $resBody = $bodyVars;
        }

        $components = WapiTemplatePayloadBuilder::cloudApiComponentsFromVariables($resHeader, $resBody);

        $this->dispatcher->queueTemplate(
            $phoneDigits,
            (string) $validated['template_name'],
            (string) $validated['language'],
            $components,
            $storedAttachment,
            isset($validated['wapi_template_id']) ? (int) $validated['wapi_template_id'] : null,
            [
                'send_type' => 'individual',
                'header_variables' => $resHeader,
                'variables' => $resBody,
                'student_id' => $validated['student_id'] ?? null,
            ],
            0
        );

        return redirect()
            ->route('admin.flaxxa-wapi.messages.index')
            ->with('success', 'تمت جدولة إرسال القالب عبر Flaxxa.');
    }

    /**
     * @param  array<int, string>  $headerVars
     * @param  array<int, string>  $bodyVars
     * @return array{0: array<int, string>, 1: array<int, string>}
     */
    private function replaceVarsWithLmsPlaceholders(
        array $headerVars,
        array $bodyVars,
        User $student,
        ?Course $course,
        ?CourseGroup $group
    ): array {
        return $this->flaxxaVariables->resolveArrays($headerVars, $bodyVars, $student, $course, $group, []);
    }

    private function duplicateWapiTempFile(string $relativePath): string
    {
        $disk = Storage::disk('local');
        $ext = pathinfo($relativePath, PATHINFO_EXTENSION);
        $dest = 'wapi-temp/broadcast-'.uniqid('', true).($ext !== '' ? '.'.$ext : '');
        $disk->copy($relativePath, $dest);

        return $dest;
    }

    private function deleteStoredAttachmentIfAny(?string $relativePath): void
    {
        if ($relativePath === null || $relativePath === '') {
            return;
        }
        Storage::disk('local')->delete($relativePath);
    }

    /**
     * جمع قيم المتغيرات من مصفوفة ديناميكية (الأسبقية) أو نص أسطر (احتياطي).
     *
     * @return array<int, string>
     */
    private function collectVars(mixed $arrayInput, string $textFallback): array
    {
        if (is_array($arrayInput)) {
            $values = array_values(array_map('strval', $arrayInput));
            $values = array_values(array_filter($values, fn ($v) => trim($v) !== ''));
            if ($values !== []) {
                return $values;
            }
        }

        return $this->linesToArray($textFallback);
    }

    public function sendCampaignForm(): View
    {
        $templates = WapiTemplate::query()->orderBy('name')->get();

        return view('admin.pages.flaxxa-wapi.send.campaign', compact('templates'));
    }

    public function sendCampaign(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'template_id' => ['required', 'string', 'max:128'],
            'group_id' => ['required', 'string', 'max:128'],
            'wapi_template_id' => ['nullable', 'integer', 'exists:wapi_templates,id'],
            'header_variables_text' => ['nullable', 'string', 'max:65500'],
            'body_variables_text' => ['nullable', 'string', 'max:65500'],
        ]);

        $headerVars = $this->linesToArray($validated['header_variables_text'] ?? '');
        $bodyVars = $this->linesToArray($validated['body_variables_text'] ?? '');

        if (! empty($validated['wapi_template_id'])) {
            $tpl = WapiTemplate::query()->findOrFail($validated['wapi_template_id']);
            if (is_array($tpl->structure ?? null)) {
                $structure = $tpl->structure;
                $headerNeed = (int) ($structure['header_placeholders'] ?? 0);
                $bodyNeed = (int) ($structure['body_placeholders'] ?? 0);
                if ($headerNeed > 0 && count($headerVars) < $headerNeed) {
                    return back()->withInput()->withErrors(['header_variables_text' => 'عدد متغيرات الرأس غير كافٍ.']);
                }
                if ($bodyNeed > 0 && count($bodyVars) < $bodyNeed) {
                    return back()->withInput()->withErrors(['body_variables_text' => 'عدد متغيرات النص غير كافٍ.']);
                }
            }
        }

        $this->dispatcher->queueCampaign(
            $validated['name'],
            $validated['template_id'],
            $validated['group_id'],
            $headerVars,
            $bodyVars,
            isset($validated['wapi_template_id']) ? (int) $validated['wapi_template_id'] : null,
            [
                'header_variables' => $headerVars,
                'variables' => $bodyVars,
            ],
        );

        return redirect()
            ->route('admin.flaxxa-wapi.messages.index')
            ->with('success', 'تمت جدولة الحملة عبر Flaxxa.');
    }

    private function linesToArray(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $text);

        return array_values(array_filter(array_map('trim', $lines), fn ($l) => $l !== ''));
    }
}
