<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BulkEmailCampaign;
use App\Models\BulkEmailRecipient;
use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\EmailSetting;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\BulkEmail\BulkEmailAudienceResolver;
use App\Services\BulkEmail\BulkEmailCampaignService;
use App\Services\BulkEmail\BulkEmailSettingsService;
use App\Services\BulkEmail\BulkEmailVariableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BulkEmailController extends Controller
{
    public function __construct(
        private BulkEmailAudienceResolver $audienceResolver,
        private BulkEmailCampaignService $campaignService,
        private BulkEmailVariableBuilder $variableBuilder,
        private BulkEmailSettingsService $settingsService
    ) {}

    public function index(): View
    {
        $campaigns = BulkEmailCampaign::with(['course', 'group', 'creator', 'emailTemplate'])
            ->orderByDesc('created_at')
            ->paginate(20);

        $stats = [
            'total' => BulkEmailCampaign::count(),
            'completed' => BulkEmailCampaign::where('status', BulkEmailCampaign::STATUS_COMPLETED)->count(),
            'processing' => BulkEmailCampaign::where('status', BulkEmailCampaign::STATUS_PROCESSING)->count(),
            'failed' => BulkEmailCampaign::where('status', BulkEmailCampaign::STATUS_FAILED)->count(),
        ];

        return view('admin.pages.bulk-emails.index', compact('campaigns', 'stats'));
    }

    public function create(): View
    {
        $emailSettings = EmailSetting::orderByDesc('is_active')->orderBy('id')->get();
        $templates = EmailTemplate::active()->orderBy('name_ar')->orderBy('name')->get();
        $courses = Course::orderBy('title')->get(['id', 'title']);
        $groups = CourseGroup::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $defaultSetting = EmailSetting::getActive();

        return view('admin.pages.bulk-emails.create', compact(
            'emailSettings',
            'templates',
            'courses',
            'groups',
            'defaultSetting'
        ));
    }

    public function previewCount(Request $request): JsonResponse
    {
        $validated = $this->validateAudienceRequest($request);

        $count = $this->audienceResolver->countFromParams(
            $validated['audience_type'],
            $validated['student_ids'] ?? [],
            $validated['course_id'] ?? null,
            $validated['group_id'] ?? null
        );

        $estimatedSeconds = $this->settingsService->estimateDurationSeconds($count);

        return response()->json([
            'success' => true,
            'count' => $count,
            'estimated_duration_seconds' => $estimatedSeconds,
            'estimated_duration_label' => $this->settingsService->formatDuration($estimatedSeconds),
        ]);
    }

    public function previewRecipients(Request $request): JsonResponse
    {
        $validated = $this->validateAudienceRequest($request);

        $users = $this->audienceResolver->resolveFromParams(
            $validated['audience_type'],
            $validated['student_ids'] ?? [],
            $validated['course_id'] ?? null,
            $validated['group_id'] ?? null
        );

        $totalCount = $users->count();
        $limit = 200;
        $truncated = $totalCount > $limit;
        $previewUsers = $users->take($limit);

        return response()->json([
            'success' => true,
            'total_count' => $totalCount,
            'truncated' => $truncated,
            'recipients' => $previewUsers->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'name_ar' => $user->name_ar,
                'email' => $user->email,
            ])->values()->all(),
        ]);
    }

    public function previewContent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content_mode' => ['required', Rule::in([
                BulkEmailCampaign::CONTENT_MODE_TEMPLATE,
                BulkEmailCampaign::CONTENT_MODE_CUSTOM,
            ])],
            'email_template_id' => 'nullable|exists:email_templates,id',
            'subject' => 'nullable|string|max:500',
            'body' => 'nullable|string',
            'student_id' => 'nullable|exists:users,id',
        ]);

        $sampleUser = null;
        if (! empty($validated['student_id'])) {
            $sampleUser = User::role('student')->find($validated['student_id']);
        }
        if (! $sampleUser) {
            $sampleUser = User::role('student')->whereNotNull('email')->first();
        }

        if (! $sampleUser) {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد طالب نموذجي لمعاينة المحتوى.',
            ], 422);
        }

        $variables = $this->variableBuilder->build($sampleUser);

        if ($validated['content_mode'] === BulkEmailCampaign::CONTENT_MODE_TEMPLATE) {
            $template = EmailTemplate::find($validated['email_template_id'] ?? 0);
            if (! $template) {
                return response()->json([
                    'success' => false,
                    'message' => 'يرجى اختيار قالب بريد صالح.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'subject' => $template->renderSubject($variables),
                'body' => $template->render($variables),
                'sample_user' => $sampleUser->name_ar ?? $sampleUser->name,
            ]);
        }

        return response()->json([
            'success' => true,
            'subject' => $this->variableBuilder->renderSubject($validated['subject'] ?? '', $variables),
            'body' => $this->variableBuilder->renderBody($validated['body'] ?? '', $variables),
            'sample_user' => $sampleUser->name_ar ?? $sampleUser->name,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateStoreRequest($request);

        try {
            $campaign = $this->campaignService->createAndDispatch($validated);

            return redirect()
                ->route('admin.bulk-emails.show', $campaign)
                ->with('success', 'تم بدء إرسال '.$campaign->total_recipients.' بريد إلكتروني. يمكنك متابعة التقرير في هذه الصفحة.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'فشل إنشاء حملة البريد: '.$e->getMessage());
        }
    }

    public function show(Request $request, BulkEmailCampaign $campaign): View
    {
        $campaign->load(['course', 'group', 'creator', 'emailTemplate', 'emailSetting']);

        $statusFilter = $request->get('status');

        $recipientsQuery = $campaign->recipients()->with('user')->orderBy('id');

        if ($statusFilter && in_array($statusFilter, [
            BulkEmailRecipient::STATUS_PENDING,
            BulkEmailRecipient::STATUS_SENT,
            BulkEmailRecipient::STATUS_FAILED,
            BulkEmailRecipient::STATUS_SKIPPED,
        ], true)) {
            $recipientsQuery->where('status', $statusFilter);
        }

        $recipients = $recipientsQuery->paginate(50)->withQueryString();

        return view('admin.pages.bulk-emails.show', [
            'campaign' => $campaign,
            'recipients' => $recipients,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function retryFailed(BulkEmailCampaign $campaign): RedirectResponse
    {
        $retried = $this->campaignService->retryFailed($campaign);

        if ($retried === 0) {
            return redirect()
                ->route('admin.bulk-emails.show', $campaign)
                ->with('error', 'لا توجد رسائل فاشلة لإعادة الإرسال.');
        }

        return redirect()
            ->route('admin.bulk-emails.show', $campaign)
            ->with('success', 'تمت جدولة إعادة إرسال '.$retried.' رسالة فاشلة.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateAudienceRequest(Request $request): array
    {
        return $request->validate([
            'audience_type' => ['required', Rule::in([
                BulkEmailCampaign::AUDIENCE_INDIVIDUAL,
                BulkEmailCampaign::AUDIENCE_SELECTED,
                BulkEmailCampaign::AUDIENCE_GROUP,
                BulkEmailCampaign::AUDIENCE_COURSE,
                BulkEmailCampaign::AUDIENCE_COURSE_GROUP,
            ])],
            'student_ids' => [
                Rule::requiredIf(fn () => in_array($request->input('audience_type'), [
                    BulkEmailCampaign::AUDIENCE_INDIVIDUAL,
                    BulkEmailCampaign::AUDIENCE_SELECTED,
                ], true)),
                'nullable',
                'array',
                'min:1',
            ],
            'student_ids.*' => 'integer|exists:users,id',
            'course_id' => [
                Rule::requiredIf(fn () => in_array($request->input('audience_type'), [
                    BulkEmailCampaign::AUDIENCE_COURSE,
                    BulkEmailCampaign::AUDIENCE_COURSE_GROUP,
                ], true)),
                'nullable',
                'exists:courses,id',
            ],
            'group_id' => [
                Rule::requiredIf(fn () => in_array($request->input('audience_type'), [
                    BulkEmailCampaign::AUDIENCE_GROUP,
                    BulkEmailCampaign::AUDIENCE_COURSE_GROUP,
                ], true)),
                'nullable',
                'exists:course_groups,id',
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateStoreRequest(Request $request): array
    {
        $validated = $request->validate([
            'email_setting_id' => 'nullable|exists:email_settings,id',
            'content_mode' => ['required', Rule::in([
                BulkEmailCampaign::CONTENT_MODE_TEMPLATE,
                BulkEmailCampaign::CONTENT_MODE_CUSTOM,
            ])],
            'email_template_id' => 'required_if:content_mode,template|nullable|exists:email_templates,id',
            'subject' => 'required_if:content_mode,custom|nullable|string|max:500',
            'body' => 'required_if:content_mode,custom|nullable|string',
            'audience_type' => ['required', Rule::in([
                BulkEmailCampaign::AUDIENCE_INDIVIDUAL,
                BulkEmailCampaign::AUDIENCE_SELECTED,
                BulkEmailCampaign::AUDIENCE_GROUP,
                BulkEmailCampaign::AUDIENCE_COURSE,
                BulkEmailCampaign::AUDIENCE_COURSE_GROUP,
            ])],
            'student_ids' => [
                Rule::requiredIf(fn () => in_array($request->input('audience_type'), [
                    BulkEmailCampaign::AUDIENCE_INDIVIDUAL,
                    BulkEmailCampaign::AUDIENCE_SELECTED,
                ], true)),
                'nullable',
                'array',
            ],
            'student_ids.*' => 'integer|exists:users,id',
            'course_id' => [
                Rule::requiredIf(fn () => in_array($request->input('audience_type'), [
                    BulkEmailCampaign::AUDIENCE_COURSE,
                    BulkEmailCampaign::AUDIENCE_COURSE_GROUP,
                ], true)),
                'nullable',
                'exists:courses,id',
            ],
            'group_id' => [
                Rule::requiredIf(fn () => in_array($request->input('audience_type'), [
                    BulkEmailCampaign::AUDIENCE_GROUP,
                    BulkEmailCampaign::AUDIENCE_COURSE_GROUP,
                ], true)),
                'nullable',
                'exists:course_groups,id',
            ],
        ]);

        if ($validated['audience_type'] === BulkEmailCampaign::AUDIENCE_INDIVIDUAL) {
            $ids = $validated['student_ids'] ?? [];
            if (count($ids) !== 1) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'student_ids' => ['يجب اختيار طالب واحد فقط للإرسال الفردي.'],
                ]);
            }
        }

        return $validated;
    }
}
