<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\CourseModule;
use App\Models\EmailSetting;
use App\Models\EmailTemplate;
use App\Models\ModuleCompletion;
use App\Models\User;
use App\Models\WhatsAppMessageTemplate;
use App\Services\Course\ModuleCompletionMessageService;
use App\Services\WhatsApp\Evolution\EvolutionInstanceRotator;
use App\Services\WhatsApp\Evolution\EvolutionRotatingSendService;
use App\Support\WhatsAppSendErrorMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ModuleCompletionReportController extends Controller
{
    public function __construct(
        private ModuleCompletionMessageService $messageService,
    ) {}

    /**
     * List students with progress/completion records for a course module.
     */
    public function index(Request $request, Course $course, CourseModule $module)
    {
        if ((int) $module->course_id !== (int) $course->id) {
            abort(404);
        }

        $context = $this->buildListContext($request, $course, $module);
        $viewData = array_merge($context, $this->messagingViewData());

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.pages.courses.partials.module-completions-results', $viewData)->render(),
            ]);
        }

        return view('admin.pages.courses.module-completions', $viewData);
    }

    public function previewWhatsApp(Request $request, Course $course, CourseModule $module): JsonResponse
    {
        [$student, $completion, $group] = $this->resolveMessagingContext($request, $course, $module);

        $validated = $request->validate([
            'whatsapp_template_id' => 'required|exists:whatsapp_message_templates,id',
        ]);

        $template = WhatsAppMessageTemplate::active()
            ->byType(WhatsAppMessageTemplate::TYPE_TEXT)
            ->where('id', $validated['whatsapp_template_id'])
            ->firstOrFail();

        try {
            $body = $this->messageService->renderWhatsAppTemplate(
                $template,
                $student,
                $course,
                $module,
                $group,
                $completion
            );

            return response()->json(['success' => true, 'body' => $body]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'تعذر تحميل المعاينة: '.WhatsAppSendErrorMessage::fromThrowable($e),
            ], 500);
        }
    }

    public function sendWhatsApp(Request $request, Course $course, CourseModule $module): JsonResponse
    {
        [$student, $completion, $group] = $this->resolveMessagingContext($request, $course, $module);

        $validated = $request->validate([
            'whatsapp_template_id' => 'required|exists:whatsapp_message_templates,id',
        ]);

        $template = WhatsAppMessageTemplate::active()
            ->byType(WhatsAppMessageTemplate::TYPE_TEXT)
            ->where('id', $validated['whatsapp_template_id'])
            ->firstOrFail();

        try {
            $result = $this->messageService->sendWhatsAppTemplate(
                $student,
                $course,
                $module,
                $template,
                $group,
                $completion
            );

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال رسالة الواتساب إلى '.$result['phone'],
                'instance_name' => $result['instance_name'],
                'rotation_pool_count' => app(EvolutionInstanceRotator::class)->poolCount(),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'تعذر إرسال الرسالة: '.WhatsAppSendErrorMessage::fromThrowable($e),
            ], 500);
        }
    }

    public function previewEmail(Request $request, Course $course, CourseModule $module): JsonResponse
    {
        [$student, $completion, $group] = $this->resolveMessagingContext($request, $course, $module);

        $validated = $request->validate([
            'email_template_id' => 'required|exists:email_templates,id',
        ]);

        $template = EmailTemplate::where('id', $validated['email_template_id'])
            ->where('is_active', true)
            ->firstOrFail();

        try {
            $rendered = $this->messageService->renderEmailTemplate(
                $template,
                $student,
                $course,
                $module,
                $group,
                $completion
            );

            return response()->json([
                'success' => true,
                'subject' => $rendered['subject'],
                'body' => $rendered['body'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'تعذر تحميل المعاينة: '.$e->getMessage(),
            ], 500);
        }
    }

    public function sendEmail(Request $request, Course $course, CourseModule $module): JsonResponse
    {
        [$student, $completion, $group] = $this->resolveMessagingContext($request, $course, $module);

        $validated = $request->validate([
            'email_template_id' => 'required|exists:email_templates,id',
            'email_setting_id' => 'nullable|exists:email_settings,id',
        ]);

        $template = EmailTemplate::where('id', $validated['email_template_id'])
            ->where('is_active', true)
            ->firstOrFail();

        try {
            $email = $this->messageService->sendEmailTemplate(
                $student,
                $course,
                $module,
                $template,
                $group,
                $completion,
                $validated['email_setting_id'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال البريد إلى '.$email,
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'تعذر إرسال البريد: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildListContext(Request $request, Course $course, CourseModule $module): array
    {
        $statusFilter = $request->get('status', 'completed');
        if (! in_array($statusFilter, ['completed', 'in_progress', 'any'], true)) {
            $statusFilter = 'completed';
        }

        $applyStatus = function ($query) use ($statusFilter) {
            if ($statusFilter === 'completed') {
                $query->where('completion_status', 'completed');
            } elseif ($statusFilter === 'in_progress') {
                $query->where('completion_status', 'in_progress');
            } else {
                $query->whereIn('completion_status', ['in_progress', 'completed']);
            }
        };

        $applySearch = function ($query) use ($request) {
            if (! $request->filled('search')) {
                return;
            }
            $search = $request->input('search');
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        };

        $queryWithoutGroup = ModuleCompletion::query()
            ->where('module_id', $module->id)
            ->with(['student']);
        $applyStatus($queryWithoutGroup);
        $applySearch($queryWithoutGroup);
        $totalWithoutGroupFilter = (clone $queryWithoutGroup)->count();

        $queryStatusOnly = ModuleCompletion::query()->where('module_id', $module->id);
        $applyStatus($queryStatusOnly);
        $totalStatusOnly = (clone $queryStatusOnly)->count();

        $listQuery = ModuleCompletion::query()
            ->where('module_id', $module->id)
            ->with(['student']);
        $applyStatus($listQuery);
        $applySearch($listQuery);

        $allowedGroupIds = $course->groups()->pluck('course_groups.id')->all();
        $groupFilterActive = false;
        $selectedGroup = null;

        if ($request->filled('group_id')) {
            $groupId = (int) $request->input('group_id');
            if ($groupId > 0 && in_array($groupId, $allowedGroupIds, true)) {
                $groupFilterActive = true;
                $selectedGroup = CourseGroup::find($groupId);
                $listQuery->whereHas('student.courseGroupMemberships', function ($q) use ($groupId) {
                    $q->where('group_id', $groupId);
                });
            }
        }

        $completions = $listQuery
            ->orderByRaw('CASE WHEN completed_at IS NULL THEN 1 ELSE 0 END')
            ->orderByDesc('completed_at')
            ->orderByDesc('last_accessed_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $course->load(['groups' => fn ($q) => $q->orderBy('name')]);

        $studentIds = (clone $listQuery)->pluck('student_id')->unique()->filter();
        $studentsForStats = User::query()->whereIn('id', $studentIds)->get(['id', 'email', 'phone', 'country_code', 'full_phone']);

        $stats = [
            'total' => $completions->total(),
            'completed' => (clone $listQuery)->where('completion_status', 'completed')->count(),
            'in_progress' => (clone $listQuery)->where('completion_status', 'in_progress')->count(),
            'with_phone' => $studentsForStats->filter(fn ($u) => $this->studentHasPhone($u))->count(),
            'with_email' => $studentsForStats->filter(fn ($u) => trim((string) ($u->email ?? '')) !== '')->count(),
        ];

        return compact(
            'course',
            'module',
            'completions',
            'statusFilter',
            'totalWithoutGroupFilter',
            'totalStatusOnly',
            'groupFilterActive',
            'stats',
            'selectedGroup',
        ) + ['searchActive' => $request->filled('search')];
    }

    /**
     * @return array<string, mixed>
     */
    private function messagingViewData(): array
    {
        return [
            'whatsappTemplates' => WhatsAppMessageTemplate::active()
                ->byType(WhatsAppMessageTemplate::TYPE_TEXT)
                ->orderBy('name')
                ->get(['id', 'name']),
            'emailTemplates' => EmailTemplate::where('is_active', true)
                ->orderBy('name_ar')
                ->get(['id', 'name', 'name_ar', 'subject']),
            'emailSettings' => EmailSetting::orderByDesc('is_active')->get(),
            'defaultEmailSetting' => EmailSetting::getActive(),
            'evolutionRotationEnabled' => app(EvolutionRotatingSendService::class)->isRotationActive(),
            'rotationPoolCount' => app(EvolutionInstanceRotator::class)->poolCount(),
        ];
    }

    /**
     * @return array{0: User, 1: ModuleCompletion, 2: ?CourseGroup}
     */
    private function resolveMessagingContext(Request $request, Course $course, CourseModule $module): array
    {
        if ((int) $module->course_id !== (int) $course->id) {
            abort(404);
        }

        $validated = $request->validate([
            'student_id' => 'required|integer|exists:users,id',
            'completion_id' => 'nullable|integer|exists:module_completions,id',
            'group_id' => 'nullable|integer',
        ]);

        $student = User::findOrFail((int) $validated['student_id']);

        $completion = null;
        if (! empty($validated['completion_id'])) {
            $completion = ModuleCompletion::where('id', $validated['completion_id'])
                ->where('module_id', $module->id)
                ->where('student_id', $student->id)
                ->firstOrFail();
        } else {
            $completion = ModuleCompletion::where('module_id', $module->id)
                ->where('student_id', $student->id)
                ->latest('id')
                ->first();
        }

        $group = null;
        if (! empty($validated['group_id'])) {
            $groupId = (int) $validated['group_id'];
            $group = $course->groups()->where('course_groups.id', $groupId)->first();
        }

        return [$student, $completion, $group];
    }

    private function studentHasPhone(?User $student): bool
    {
        if (! $student) {
            return false;
        }

        $phone = trim((string) ($student->full_phone ?? ''));
        if ($phone === '') {
            $phone = trim(($student->country_code ?? '').($student->phone ?? ''));
        }

        return $phone !== '';
    }
}
