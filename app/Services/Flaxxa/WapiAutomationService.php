<?php

namespace App\Services\Flaxxa;

use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\GroupRegistration;
use App\Models\User;
use App\Models\WapiAutomationRule;
use App\Services\WapiOutboundDispatcher;
use App\Services\WhatsApp\BroadcastWhatsAppMessage;
use App\Services\WhatsAppService;
use App\Support\WapiTemplatePayloadBuilder;
use App\WapiAutomation\WapiAutomationDefaultVariables;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WapiAutomationService
{
    /**
     * يمنع إرسالاً مكرّراً في نفس الطلب عندما يستدعي كل من مركز الإشعارات والمستمعون `dispatchForUser` لنفس الحدث والسياق.
     *
     * @var array<string, true>
     */
    private static array $dispatchedSignaturesThisRequest = [];

    public function __construct(
        private FlaxxaTemplateVariableResolver $resolver,
        private WapiOutboundDispatcher $dispatcher,
        private BroadcastWhatsAppMessage $broadcastWhatsApp,
        private WhatsAppService $whatsAppService
    ) {}

    public function isTokenConfigured(): bool
    {
        try {
            $this->whatsAppService->assertConfigured();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * استدعاء من مركز الإشعارات عند تفعيل قناة whatsapp_wapi.
     *
     * @param  array<string, mixed>  $data
     */
    public function dispatchFromNotificationHub(string $eventKey, User $user, array $data = []): void
    {
        if (! $this->isTokenConfigured()) {
            return;
        }

        $context = $this->contextFromHubData($data);
        $this->dispatchForUser($eventKey, $user, $context);
    }

    /**
     * @param  array<string, mixed>  $context  course_id, lesson_id, module_id, group_id, quiz_id, score, ...
     */
    public function dispatchForUser(string $eventKey, User $user, array $context = []): void
    {
        if (! $this->isTokenConfigured()) {
            return;
        }

        $phone = $this->broadcastWhatsApp->normalizedPhoneDigitsForWapi($user);
        if ($phone === null) {
            return;
        }

        $rule = $this->resolveRule($eventKey, $context);
        if ($rule === null) {
            return;
        }

        if ($this->isCoolingDown($rule, $user, $context)) {
            return;
        }

        if ($this->shouldSkipDuplicateDispatchInRequest($eventKey, $user, $context)) {
            return;
        }

        $course = $this->resolveCourseFromContext($context);
        $group = $this->resolveGroupFromContext($context);
        $extra = $this->buildExtraPlaceholders($user, $context, $course, $group);

        $headerVars = is_array($rule->header_variables) ? array_values(array_map('strval', $rule->header_variables)) : [];
        $bodyVars = is_array($rule->body_variables) ? array_values(array_map('strval', $rule->body_variables)) : [];

        if ($bodyVars === []) {
            $fallback = WapiAutomationDefaultVariables::bodyLines($eventKey);
            if ($fallback !== []) {
                $bodyVars = $fallback;
                Log::channel('whatsapp')->info('[WAPI automation] Using default body variable lines (rule body empty)', [
                    'rule_id' => $rule->id,
                    'event_key' => $eventKey,
                ]);
            }
        }

        [$h, $b] = $this->resolver->resolveArrays($headerVars, $bodyVars, $user, $course, $group, $extra);

        $components = WapiTemplatePayloadBuilder::cloudApiComponentsFromVariables($h, $b);
        $tplName = $rule->effectiveTemplateName();
        $lang = $rule->effectiveLanguage();
        if ($tplName === null || $tplName === '' || $lang === null || $lang === '') {
            Log::channel('whatsapp')->warning('[WAPI automation] Rule missing template name/language', [
                'rule_id' => $rule->id,
                'event_key' => $eventKey,
            ]);

            return;
        }

        $this->dispatcher->queueTemplate(
            $phone,
            $tplName,
            $lang,
            $components,
            null,
            $rule->wapi_template_id,
            [
                'automation' => true,
                'event_key' => $eventKey,
                'user_id' => $user->id,
                'rule_id' => $rule->id,
            ],
            0
        );

        $this->touchCooldown($rule, $user, $context);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function shouldSkipDuplicateDispatchInRequest(string $eventKey, User $user, array $context): bool
    {
        $parts = [
            $eventKey,
            (string) $user->id,
            isset($context['course_id']) ? (string) $context['course_id'] : '',
            isset($context['lesson_id']) ? (string) $context['lesson_id'] : '',
            isset($context['quiz_id']) ? (string) $context['quiz_id'] : '',
            isset($context['module_id']) ? (string) $context['module_id'] : '',
            isset($context['group_id']) ? (string) $context['group_id'] : '',
        ];
        $sig = implode('|', $parts);
        if (isset(self::$dispatchedSignaturesThisRequest[$sig])) {
            return true;
        }
        self::$dispatchedSignaturesThisRequest[$sig] = true;

        return false;
    }

    /**
     * تسجيل مجموعة — بدون مستخدم Laravel في بعض الحالات.
     *
     * @param  array<string, string|int|float|null>  $placeholders
     */
    public function dispatchForGroupRegistration(
        GroupRegistration $registration,
        ?WapiAutomationRule $forcedRule = null
    ): bool {
        if (! $this->isTokenConfigured()) {
            return false;
        }

        $group = $registration->group;
        if (! $group) {
            return false;
        }

        $full = $registration->full_phone;
        if (! $full) {
            return false;
        }

        $phone = \App\Support\WapiPhoneNormalizer::normalize(preg_replace('/\s+/', '', (string) $full));
        if (! \App\Support\WapiPhoneNormalizer::isValidE164Digits($phone)) {
            return false;
        }

        $context = [
            'group_id' => $group->id,
        ];

        $rule = $forcedRule ?? $this->resolveRule(\App\WapiAutomation\WapiAutomationEventKey::GROUP_REGISTRATION_SUBMITTED, $context);
        if ($rule === null) {
            return false;
        }

        $placeholders = [
            'student_name' => $registration->name_ar ?? $registration->name,
            'group_name' => $group->name,
            'email' => $registration->email ?? '',
            'registration_id' => (string) $registration->id,
        ];

        $headerVars = is_array($rule->header_variables) ? array_values(array_map('strval', $rule->header_variables)) : [];
        $bodyVars = is_array($rule->body_variables) ? array_values(array_map('strval', $rule->body_variables)) : [];

        [$h, $b] = $this->resolver->resolveArraysWithoutUser($headerVars, $bodyVars, $placeholders);

        $components = WapiTemplatePayloadBuilder::cloudApiComponentsFromVariables($h, $b);
        $tplName = $rule->effectiveTemplateName();
        $lang = $rule->effectiveLanguage();
        if ($tplName === null || $tplName === '' || $lang === null || $lang === '') {
            return false;
        }

        $this->dispatcher->queueTemplate(
            $phone,
            $tplName,
            $lang,
            $components,
            null,
            $rule->wapi_template_id,
            [
                'automation' => true,
                'event_key' => \App\WapiAutomation\WapiAutomationEventKey::GROUP_REGISTRATION_SUBMITTED,
                'group_registration_id' => $registration->id,
                'rule_id' => $rule->id,
            ],
            0
        );

        return true;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function contextFromHubData(array $data): array
    {
        $ctx = [];
        foreach (['course_id', 'lesson_id', 'module_id', 'group_id', 'quiz_id', 'score', 'total_questions', 'attempt_id', 'time_taken', 'module_title'] as $k) {
            if (array_key_exists($k, $data) && $data[$k] !== null && $data[$k] !== '') {
                $ctx[$k] = $data[$k];
            }
        }
        if (isset($data['lesson_title'])) {
            $ctx['lesson_title'] = $data['lesson_title'];
        }
        if (isset($data['quiz_title'])) {
            $ctx['quiz_title'] = $data['quiz_title'];
        }
        if (isset($data['course_title'])) {
            $ctx['course_title'] = $data['course_title'];
        }
        if (isset($data['quiz_title'])) {
            $ctx['quiz_title'] = $data['quiz_title'];
        }

        return $ctx;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function resolveRule(string $eventKey, array $context): ?WapiAutomationRule
    {
        $cId = isset($context['course_id']) ? (int) $context['course_id'] : null;
        $gId = isset($context['group_id']) ? (int) $context['group_id'] : null;
        $mId = isset($context['module_id']) ? (int) $context['module_id'] : null;
        $lId = isset($context['lesson_id']) ? (int) $context['lesson_id'] : null;

        $rules = WapiAutomationRule::query()
            ->where('event_key', $eventKey)
            ->where('is_active', true)
            ->with('wapiTemplate')
            ->orderByDesc('priority')
            ->orderBy('sort_order')
            ->get();

        $matching = $rules->filter(function (WapiAutomationRule $rule) use ($cId, $gId, $mId, $lId) {
            if ($rule->course_id !== null && $cId !== (int) $rule->course_id) {
                return false;
            }
            if ($rule->group_id !== null && $gId !== (int) $rule->group_id) {
                return false;
            }
            if ($rule->module_id !== null && $mId !== (int) $rule->module_id) {
                return false;
            }
            if ($rule->lesson_id !== null && $lId !== (int) $rule->lesson_id) {
                return false;
            }

            return true;
        });

        if ($matching->isEmpty()) {
            return null;
        }

        return $matching->sortByDesc(fn (WapiAutomationRule $r) => $r->specificityScore())->first();
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function resolveCourseFromContext(array $context): ?Course
    {
        if (! empty($context['course_id'])) {
            return Course::query()->find((int) $context['course_id']);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function resolveGroupFromContext(array $context): ?CourseGroup
    {
        if (! empty($context['group_id'])) {
            return CourseGroup::query()->find((int) $context['group_id']);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, string|int|float|null>
     */
    private function buildExtraPlaceholders(User $user, array $context, ?Course $course, ?CourseGroup $group): array
    {
        $extra = [
            'student_name' => $user->name,
            'student_email' => $user->email ?? '',
        ];

        if ($course) {
            $extra['course_title'] = $course->title;
        } elseif (! empty($context['course_title'])) {
            $extra['course_title'] = (string) $context['course_title'];
        }

        if ($group) {
            $extra['group_name'] = $group->name;
        }

        foreach (['lesson_title', 'quiz_title', 'module_title'] as $k) {
            if (! empty($context[$k])) {
                $extra[$k] = (string) $context[$k];
            }
        }

        foreach (['score', 'total_questions', 'attempt_id', 'time_taken'] as $k) {
            if (isset($context[$k])) {
                $extra[$k] = $context[$k];
            }
        }

        if (! empty($context['enrollment_date'])) {
            $extra['enrollment_date'] = (string) $context['enrollment_date'];
        }

        if (! empty($context['module_id'])) {
            try {
                $extra['learn_url'] = url(route('student.learn.module', ['moduleId' => (int) $context['module_id']]));
            } catch (\Throwable) {
                $extra['learn_url'] = '';
            }
        } else {
            $extra['learn_url'] = $extra['learn_url'] ?? '';
        }

        return $extra;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function dedupeCacheKey(WapiAutomationRule $rule, User $user, array $context): string
    {
        $tpl = $rule->dedupe_template ?? '{user_id}:{event_key}:{rule_id}';
        $map = [
            '{user_id}' => (string) $user->id,
            '{rule_id}' => (string) $rule->id,
            '{event_key}' => (string) $rule->event_key,
            '{course_id}' => isset($context['course_id']) ? (string) $context['course_id'] : '',
            '{lesson_id}' => isset($context['lesson_id']) ? (string) $context['lesson_id'] : '',
            '{quiz_id}' => isset($context['quiz_id']) ? (string) $context['quiz_id'] : '',
        ];

        return 'wapi-auto:dedupe:'.md5(str_replace(array_keys($map), array_values($map), $tpl));
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function isCoolingDown(WapiAutomationRule $rule, User $user, array $context): bool
    {
        if ($rule->cooldown_seconds <= 0) {
            return false;
        }

        $key = $this->dedupeCacheKey($rule, $user, $context);
        if (Cache::has($key)) {
            return true;
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function touchCooldown(WapiAutomationRule $rule, User $user, array $context): void
    {
        if ($rule->cooldown_seconds <= 0) {
            return;
        }

        $key = $this->dedupeCacheKey($rule, $user, $context);
        Cache::put($key, true, now()->addSeconds($rule->cooldown_seconds));
    }
}
