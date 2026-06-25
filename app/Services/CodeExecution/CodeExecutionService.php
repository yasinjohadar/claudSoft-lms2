<?php

namespace App\Services\CodeExecution;

use App\Models\ProgrammingChallenge;
use App\Models\ProgrammingChallengeAttempt;
use App\Models\ProgrammingChallengeRun;
use App\Models\ProgrammingChallengeSubmission;
use App\Models\ProgrammingLanguage;

class CodeExecutionService
{
    public function __construct(
        protected PistonClient $piston
    ) {}

    /**
     * Execute code for a challenge (manual run from IDE).
     */
    public function run(
        ProgrammingChallenge $challenge,
        array $files,
        string $stdin = '',
        ?ProgrammingChallengeAttempt $attempt = null,
        ?ProgrammingChallengeSubmission $submission = null,
        string $trigger = 'run',
        bool $checkRateLimit = true,
        bool $shouldLog = true
    ): array {
        if ($challenge->isWebSandbox()) {
            return [
                'success' => true,
                'message' => 'تنفيذ Sandbox يتم في المتصفح',
                'stdout' => '',
                'stderr' => '',
                'exit_code' => 0,
                'duration_ms' => 0,
            ];
        }

        if (! $this->isAvailable()) {
            return $this->unavailableResult();
        }

        if ($checkRateLimit && $attempt) {
            $this->assertRateLimit($attempt->user_id);
        }

        $result = $this->execute($challenge, $files, $stdin);

        if ($shouldLog && $attempt) {
            $this->logRun($attempt, $submission, $trigger, $result);
        }

        return $result;
    }

    /**
     * Run all test cases for a submission (auto-grading).
     */
    public function runTestSuite(
        ProgrammingChallenge $challenge,
        array $files,
        ?ProgrammingChallengeAttempt $attempt = null,
        ?ProgrammingChallengeSubmission $submission = null
    ): array {
        $testCases = $challenge->testCases()->get();
        $total = $testCases->count();

        if ($total === 0) {
            return [
                'success' => false,
                'message' => 'لا توجد حالات اختبار لهذا التحدي',
                'passed' => 0,
                'total' => 0,
                'score' => 0,
                'max_score' => (float) ($attempt?->max_score ?? $challenge->max_score),
                'results' => [],
            ];
        }

        if (! $this->isAvailable()) {
            return array_merge($this->unavailableResult(), [
                'passed' => 0,
                'total' => $total,
                'score' => 0,
                'max_score' => (float) ($attempt?->max_score ?? $challenge->max_score),
                'results' => [],
            ]);
        }

        $results = [];
        $passed = 0;
        $earnedPoints = 0.0;
        $totalPoints = (float) $testCases->sum('points');
        $lastResult = null;

        foreach ($testCases as $testCase) {
            $runResult = $this->run(
                $challenge,
                $files,
                (string) ($testCase->input ?? ''),
                $attempt,
                $submission,
                'test',
                checkRateLimit: false,
                shouldLog: false
            );

            $lastResult = $runResult;
            $actualOutput = $this->normalizeOutput($runResult['stdout'] ?? '');
            $expectedOutput = $this->normalizeOutput($testCase->expected_output ?? '');
            $testPassed = ($runResult['exit_code'] ?? -1) === 0 && $actualOutput === $expectedOutput;

            if ($testPassed) {
                $passed++;
                $earnedPoints += (float) $testCase->points;
            }

            $results[] = [
                'test_case_id' => $testCase->id,
                'passed' => $testPassed,
                'is_hidden' => (bool) $testCase->is_hidden,
                'expected_output' => $testCase->is_hidden ? null : $expectedOutput,
                'actual_output' => $testCase->is_hidden ? null : $actualOutput,
                'exit_code' => $runResult['exit_code'] ?? -1,
                'duration_ms' => $runResult['duration_ms'] ?? null,
                'stderr' => $testCase->is_hidden ? null : ($runResult['stderr'] ?? ''),
                'message' => $testPassed ? 'نجح' : 'فشل',
                'points' => (float) $testCase->points,
            ];
        }

        $maxScore = (float) ($attempt?->max_score ?? $challenge->max_score);
        $score = $totalPoints > 0
            ? round(($earnedPoints / $totalPoints) * $maxScore, 2)
            : round(($passed / $total) * $maxScore, 2);

        $suiteResult = [
            'success' => $passed === $total,
            'message' => $passed === $total
                ? "نجحت جميع الاختبارات ({$passed}/{$total})"
                : "نجح {$passed} من {$total} اختبار",
            'passed' => $passed,
            'total' => $total,
            'score' => $score,
            'max_score' => $maxScore,
            'results' => $results,
        ];

        if ($attempt) {
            $this->logRun($attempt, $submission, 'test_suite', $lastResult ?? [], $results);
        }

        return $suiteResult;
    }

    public function assertRateLimit(int $userId): void
    {
        $max = (int) config('challenges.rate_limit.runs_per_hour', 30);

        $count = ProgrammingChallengeRun::query()
            ->whereHas('attempt', fn ($q) => $q->where('user_id', $userId))
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($count >= $max) {
            throw new \RuntimeException("تجاوزت الحد المسموح لتشغيل الكود ({$max} تشغيل/ساعة). حاول لاحقاً.");
        }
    }

    public function isAvailable(): bool
    {
        return $this->piston->isConfigured();
    }

    public function isReachable(): bool
    {
        return $this->piston->ping();
    }

    protected function execute(ProgrammingChallenge $challenge, array $files, string $stdin): array
    {
        [$runtimeSlug, $language] = $this->resolveRuntime($files, $challenge);

        if (! $runtimeSlug) {
            return [
                'success' => false,
                'message' => 'لم يتم تحديد لغة تنفيذ صالحة لهذا التحدي',
                'stdout' => '',
                'stderr' => 'لم يتم العثور على runtime_slug للغة المحددة',
                'exit_code' => -1,
                'duration_ms' => 0,
                'runtime_slug' => null,
                'runtime_version' => null,
            ];
        }

        $pistonFiles = $this->buildPistonFiles($files, $language);
        $timeoutMs = ($challenge->time_limit_seconds ?? (int) config('challenges.piston.timeout', 10)) * 1000;

        return $this->piston->execute($runtimeSlug, $pistonFiles, $stdin, null, $timeoutMs);
    }

    /**
     * @return array{0: ?string, 1: ?ProgrammingLanguage}
     */
    protected function resolveRuntime(array $files, ProgrammingChallenge $challenge): array
    {
        $languageIds = collect($files)->pluck('programming_language_id')->filter()->unique()->values();

        if ($languageIds->isNotEmpty()) {
            $language = ProgrammingLanguage::query()
                ->whereIn('id', $languageIds)
                ->where('execution_mode', 'server')
                ->whereNotNull('runtime_slug')
                ->first();

            if ($language) {
                return [$language->runtime_slug, $language];
            }
        }

        $defaultLang = $challenge->languages()
            ->where('execution_mode', 'server')
            ->whereNotNull('runtime_slug')
            ->orderByDesc('programming_challenge_language.is_default')
            ->first();

        if ($defaultLang) {
            return [$defaultLang->runtime_slug, $defaultLang];
        }

        return [null, null];
    }

    protected function buildPistonFiles(array $files, ?ProgrammingLanguage $language): array
    {
        return collect($files)
            ->filter(fn ($file) => ($file['content'] ?? '') !== '' || ($file['filename'] ?? '') !== '')
            ->map(function ($file) use ($language) {
                return [
                    'name' => $file['filename'] ?? ($language?->default_filename ?? 'main.txt'),
                    'content' => (string) ($file['content'] ?? ''),
                ];
            })
            ->values()
            ->all();
    }

    protected function logRun(
        ProgrammingChallengeAttempt $attempt,
        ?ProgrammingChallengeSubmission $submission,
        string $trigger,
        array $result,
        ?array $testResults = null
    ): ProgrammingChallengeRun {
        return ProgrammingChallengeRun::create([
            'programming_challenge_attempt_id' => $attempt->id,
            'programming_challenge_submission_id' => $submission?->id,
            'trigger' => $trigger,
            'runtime_slug' => $result['runtime_slug'] ?? null,
            'stdout' => $result['stdout'] ?? null,
            'stderr' => $result['stderr'] ?? null,
            'exit_code' => $result['exit_code'] ?? null,
            'duration_ms' => $result['duration_ms'] ?? null,
            'test_results' => $testResults,
        ]);
    }

    protected function normalizeOutput(string $output): string
    {
        return rtrim(str_replace("\r\n", "\n", $output), "\n\r");
    }

    protected function unavailableResult(): array
    {
        return [
            'success' => false,
            'message' => 'خدمة تنفيذ الكود غير متاحة. تأكد من إعداد PISTON_URL وتشغيل المحرك.',
            'stdout' => '',
            'stderr' => 'Piston غير متاح',
            'exit_code' => -1,
            'duration_ms' => 0,
            'runtime_slug' => null,
            'runtime_version' => null,
        ];
    }
}
