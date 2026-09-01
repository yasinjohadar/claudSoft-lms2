<?php

namespace App\Services\Simulator;

use App\Exceptions\Ai\AiProviderException;
use App\Exceptions\Ai\GenerationCancelledException;
use App\Exceptions\Ai\ResumableIncompleteException;
use App\Models\SimulatorAiGeneration;
use App\Models\SimulatorAiPhase;
use App\Services\Ai\AiErrorClassifier;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Engine-agnostic staged writer for lesson simulator bundles.
 *
 * Plans first, then writes html -> css -> js in order, persisting each finished
 * file to `simulator_ai_phases` before the next call starts, so a crashed or
 * rate-limited run never loses completed work and can be continued later.
 */
class SimulatorStagedGenerator
{
    /** Seconds to wait before each retry when the provider does not tell us. */
    private const BACKOFF_SECONDS = [2, 5, 12, 30];

    /** @var list<array{phase: string, label: string}> */
    private const PHASE_DEFS = [
        ['phase' => 'html', 'label' => 'HTML'],
        ['phase' => 'css', 'label' => 'CSS'],
        ['phase' => 'js', 'label' => 'JavaScript'],
    ];

    public function __construct(
        private AiErrorClassifier $classifier,
        private SimulatorBundleValidator $bundleValidator,
        private SimulatorBundleSanitizer $bundleSanitizer = new SimulatorBundleSanitizer,
    ) {}

    /**
     * @param  callable(int $maxTokens): array<string, mixed>  $planWriter
     * @param  callable(SimulatorPhaseAttempt): string  $phaseWriter
     * @return array<string, mixed>
     */
    public function generate(
        SimulatorAiGeneration $generation,
        int $planTokens,
        int $phaseTokens,
        callable $planWriter,
        callable $phaseWriter,
    ): array {
        $plan = $this->ensurePlan($generation, $planTokens, $planWriter);
        $phases = $this->ensurePhaseRows($generation);
        $this->writePhases($generation, $phases, $phaseTokens, $phaseWriter);

        return $this->assemble($generation, $plan, $phaseTokens, $phaseWriter);
    }

    /**
     * Reuse the plan stored on a previous attempt so a resumed run keeps the
     * same title/elements/interactions.
     *
     * @param  callable(int): array<string, mixed>  $planWriter
     * @return array<string, mixed>
     */
    private function ensurePlan(SimulatorAiGeneration $generation, int $planTokens, callable $planWriter): array
    {
        $stored = $generation->partial_result['plan'] ?? null;
        if (is_array($stored) && trim((string) ($stored['title'] ?? '')) !== '') {
            Log::info('simulator.plan', ['generation_id' => $generation->id, 'outcome' => 'reused']);

            return $stored;
        }

        $generation->markProgress('plan', 'بناء خطة المحاكاة…', 8);

        $maxAttempts = max(1, (int) config('ai.simulator.plan_attempts', 3));
        $tokens = max(512, $planTokens);
        $last = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $this->assertNotCancelled($generation);
            $started = hrtime(true);
            try {
                $plan = $planWriter($tokens);
                if (trim((string) ($plan['title'] ?? '')) === '' || empty($plan['key_elements'] ?? [])) {
                    throw new AiProviderException(
                        'خطة المحاكاة عادت ناقصة.',
                        AiProviderException::KIND_TOO_LARGE,
                    );
                }

                Log::info('simulator.plan', [
                    'generation_id' => $generation->id,
                    'outcome' => 'ok',
                    'attempt' => $attempt,
                    'max_tokens' => $tokens,
                    'duration_ms' => (int) ((hrtime(true) - $started) / 1_000_000),
                ]);

                $generation->markProgress('plan', 'تم بناء الخطة — بدء توليد الملفات', 15, ['plan' => $plan]);

                return $plan;
            } catch (Throwable $e) {
                $error = $this->classifier->fromThrowable($e);
                $last = $error;

                Log::warning('simulator.plan', [
                    'generation_id' => $generation->id,
                    'outcome' => 'error',
                    'attempt' => $attempt,
                    'max_tokens' => $tokens,
                    'error_kind' => $error->kind,
                    'error' => $error->getMessage(),
                    'duration_ms' => (int) ((hrtime(true) - $started) / 1_000_000),
                ]);

                if ($error->isFatal() || $attempt === $maxAttempts) {
                    break;
                }

                if ($error->needsSmallerRequest()) {
                    $tokens = max(768, (int) floor($tokens * 0.75));
                } else {
                    $this->pause($generation, $error->retryAfterSeconds ?? self::BACKOFF_SECONDS[$attempt - 1] ?? 20);
                }
            }
        }

        throw $last ?? new AiProviderException('فشل بناء خطة المحاكاة.');
    }

    /**
     * @return Collection<int, SimulatorAiPhase>
     */
    private function ensurePhaseRows(SimulatorAiGeneration $generation): Collection
    {
        $existing = $generation->phases()->get();
        if ($existing->isNotEmpty()) {
            return $existing;
        }

        foreach (self::PHASE_DEFS as $position => $def) {
            SimulatorAiPhase::query()->create([
                'generation_id' => $generation->id,
                'position' => $position,
                'phase' => $def['phase'],
                'label' => $def['label'],
                'status' => SimulatorAiPhase::STATUS_PENDING,
            ]);
        }

        return $generation->phases()->get();
    }

    /**
     * @param  Collection<int, SimulatorAiPhase>  $phases
     * @param  callable(SimulatorPhaseAttempt): string  $phaseWriter
     */
    private function writePhases(
        SimulatorAiGeneration $generation,
        Collection $phases,
        int $phaseTokens,
        callable $phaseWriter,
    ): void {
        $total = $phases->count();
        $delayMs = max(0, (int) config('ai.simulator.phase_delay_ms', 300));
        $index = 0;

        foreach ($phases as $phase) {
            $index++;
            if ($phase->isDone()) {
                continue;
            }

            $doneBefore = $phases->where('status', SimulatorAiPhase::STATUS_DONE)->count();
            $generation->markProgress(
                'phase_'.$phase->phase,
                'توليد ملف '.$phase->label.'…',
                15 + (int) floor(($index / max(1, $total)) * 70),
                [
                    'phases_done' => $doneBefore,
                    'phases_planned' => $total,
                    'current_phase' => $phase->phase,
                ]
            );

            if ($delayMs > 0 && $index > 1) {
                usleep($delayMs * 1000);
            }

            $this->writeOnePhase($generation, $phase, $phaseTokens, $phaseWriter);
        }
    }

    /**
     * Write one phase file, retrying it as many times as the failure warrants.
     *
     * @param  callable(SimulatorPhaseAttempt): string  $phaseWriter
     */
    private function writeOnePhase(
        SimulatorAiGeneration $generation,
        SimulatorAiPhase $phase,
        int $phaseTokens,
        callable $phaseWriter,
    ): void {
        $ladder = $this->attemptLadder($phaseTokens);
        $attemptsUsed = (int) $phase->attempts;
        $waitBudget = max(0, (int) config('ai.simulator.rate_limit_retries', 2));
        $feedback = $phase->last_error;

        $rung = 0;
        $waitsUsed = 0;
        $callNumber = 0;
        $last = null;

        while ($rung < count($ladder)) {
            $this->assertNotCancelled($generation);
            $step = $ladder[$rung];
            $attemptsUsed++;
            $callNumber++;
            $started = hrtime(true);

            try {
                $content = $phaseWriter(new SimulatorPhaseAttempt(
                    phase: $phase->phase,
                    label: $phase->label,
                    attempt: $callNumber,
                    maxTokens: $step['tokens'],
                    compact: $step['compact'],
                    validationFeedback: $feedback,
                ));

                $content = $this->stripCodeFence(trim((string) $content));
                if ($content === '') {
                    throw new AiProviderException(
                        'استجابة الملف فارغة.',
                        AiProviderException::KIND_TOO_LARGE,
                    );
                }

                $phase->markDone($content, $attemptsUsed);

                Log::info('simulator.phase', [
                    'generation_id' => $generation->id,
                    'phase' => $phase->phase,
                    'attempt' => $callNumber,
                    'rung' => $rung + 1,
                    'max_tokens' => $step['tokens'],
                    'content_len' => mb_strlen($content),
                    'outcome' => 'ok',
                    'duration_ms' => (int) ((hrtime(true) - $started) / 1_000_000),
                ]);

                return;
            } catch (Throwable $e) {
                $error = $this->classifier->fromThrowable($e);
                $last = $error;

                $waitInstead = ! $error->needsSmallerRequest() && ! $error->isFatal();
                $canWait = $waitInstead && $waitsUsed < $waitBudget;

                Log::warning('simulator.phase', [
                    'generation_id' => $generation->id,
                    'phase' => $phase->phase,
                    'attempt' => $callNumber,
                    'rung' => $rung + 1,
                    'max_tokens' => $step['tokens'],
                    'outcome' => 'error',
                    'error_kind' => $error->kind,
                    'error' => $error->getMessage(),
                    'retry_action' => $error->isFatal() ? 'abort' : ($canWait ? 'wait_same_rung' : 'step_down'),
                    'duration_ms' => (int) ((hrtime(true) - $started) / 1_000_000),
                ]);

                $phase->update([
                    'attempts' => $attemptsUsed,
                    'last_error' => mb_substr($error->getMessage(), 0, 2000),
                ]);

                if ($error->isFatal()) {
                    throw $error;
                }

                if ($canWait) {
                    $this->pause($generation, $error->retryAfterSeconds ?? self::BACKOFF_SECONDS[$waitsUsed] ?? 20);
                    $waitsUsed++;

                    continue;
                }

                if ($rung === count($ladder) - 1) {
                    break;
                }

                $rung++;
                $waitsUsed = 0;
            }
        }

        $phase->markFailed(
            $last?->getMessage() ?? 'فشل توليد الملف بعد كل المحاولات.',
            $attemptsUsed,
        );
    }

    /**
     * @return list<array{tokens: int, compact: bool}>
     */
    public function attemptLadder(int $baseTokens): array
    {
        $base = max(768, $baseTokens);
        $shape = [
            ['factor' => 1.0, 'compact' => false],
            ['factor' => 0.75, 'compact' => false],
            ['factor' => 0.6, 'compact' => true],
            ['factor' => 0.45, 'compact' => true],
        ];

        $wanted = max(1, min(count($shape), (int) config('ai.simulator.phase_attempts', 4)));
        $ladder = [];
        foreach (array_slice($shape, 0, $wanted) as $step) {
            $ladder[] = [
                'tokens' => max(768, (int) floor($base * $step['factor'])),
                'compact' => $step['compact'],
            ];
        }

        return $ladder;
    }

    /**
     * @param  array<string, mixed>  $plan
     * @param  callable(SimulatorPhaseAttempt): string  $phaseWriter
     * @return array<string, mixed>
     */
    private function assemble(
        SimulatorAiGeneration $generation,
        array $plan,
        int $phaseTokens,
        callable $phaseWriter,
    ): array {
        $phases = $generation->phases()->get();
        $bundle = $this->bundleFromPhases($phases);

        if ($bundle === null) {
            throw $this->incompleteException($phases);
        }

        $bundle = $this->bundleSanitizer->sanitize($bundle);
        $langCode = (string) ($plan['lang_code'] ?? 'ar');
        $textDirection = (string) ($plan['text_direction'] ?? 'rtl');

        $generation->markProgress('validate', 'التحقق من الملفات…', 90);
        $validation = $this->bundleValidator->validate($bundle, $langCode, $textDirection);

        if (! $validation['valid']) {
            Log::info('simulator.assemble', [
                'generation_id' => $generation->id,
                'outcome' => 'validation_failed',
                'errors' => $validation['errors'],
            ]);

            $this->resetImplicatedPhases($generation, $validation['errors']);
            $this->writePhases($generation, $generation->phases()->get(), $phaseTokens, $phaseWriter);

            $phases = $generation->phases()->get();
            $bundle = $this->bundleFromPhases($phases);
            if ($bundle === null) {
                throw $this->incompleteException($phases);
            }

            $bundle = $this->bundleSanitizer->sanitize($bundle);
            $validation = $this->bundleValidator->validate($bundle, $langCode, $textDirection);
            if (! $validation['valid']) {
                throw new ResumableIncompleteException(
                    'الملفات لا تزال غير صالحة بعد محاولة الإصلاح: '.implode(' ', $validation['errors'])
                    .' — راجعها يدوياً من نموذج التحرير ثم احفظ.',
                    done: $phases->count(),
                    planned: $phases->count(),
                    failedHeadings: [],
                );
            }
        }

        $generation->markProgress('assemble', 'التجميع النهائي…', 95);

        $title = trim((string) ($plan['title'] ?? ''));
        if ($title === '') {
            $title = $this->extractTitleFromHtml($bundle['html']) ?? 'محاكاة';
        }
        $description = trim((string) ($plan['description'] ?? ''));

        return [
            'bundle' => $bundle,
            'title' => mb_substr($title, 0, 255),
            'description' => $description !== '' ? $description : null,
            'archetype' => $plan['archetype'] ?? 'playground',
            'lang_code' => $langCode,
            'text_direction' => $textDirection,
        ];
    }

    /**
     * @param  Collection<int, SimulatorAiPhase>  $phases
     * @return array{html: string, css: string, js: string}|null
     */
    private function bundleFromPhases(Collection $phases): ?array
    {
        $byPhase = $phases->keyBy('phase');
        /** @var SimulatorAiPhase|null $html */
        $html = $byPhase->get('html');
        /** @var SimulatorAiPhase|null $css */
        $css = $byPhase->get('css');
        /** @var SimulatorAiPhase|null $js */
        $js = $byPhase->get('js');

        if (! $html?->isDone() || ! $css?->isDone() || ! $js?->isDone()) {
            return null;
        }

        return [
            'html' => (string) $html->content,
            'css' => (string) $css->content,
            'js' => (string) $js->content,
        ];
    }

    /**
     * @param  Collection<int, SimulatorAiPhase>  $phases
     */
    private function incompleteException(Collection $phases): ResumableIncompleteException
    {
        $done = $phases->where('status', SimulatorAiPhase::STATUS_DONE)->count();
        $failed = $phases->reject(fn (SimulatorAiPhase $p) => $p->isDone());
        $labels = $failed->pluck('label')->filter()->values()->all();

        return new ResumableIncompleteException(
            'تم توليد '.$done.' من '.$phases->count().' ملفات وحُفظت. '
            .'تعذّر إكمال: '.implode('، ', $labels)
            .'. اضغط «متابعة التوليد» لإكمال الملفات الناقصة فقط.',
            done: $done,
            planned: $phases->count(),
            failedHeadings: $labels,
        );
    }

    /**
     * Bucket validation errors by which file they mention and reset that phase
     * to pending with the errors attached, so the next writer attempt sees them
     * as targeted feedback instead of redoing every file blind.
     *
     * @param  list<string>  $errors
     */
    private function resetImplicatedPhases(SimulatorAiGeneration $generation, array $errors): void
    {
        $byFile = ['html' => [], 'css' => [], 'js' => []];
        foreach ($errors as $error) {
            if (stripos($error, 'javascript') !== false) {
                $byFile['js'][] = $error;
            } elseif (stripos($error, 'css') !== false) {
                $byFile['css'][] = $error;
            } elseif (stripos($error, 'html') !== false) {
                $byFile['html'][] = $error;
            } else {
                // Ambiguous (e.g. the whole response looked like JSON) — safest is to redo all three.
                $byFile['html'][] = $error;
                $byFile['css'][] = $error;
                $byFile['js'][] = $error;
            }
        }

        foreach ($byFile as $phaseKey => $phaseErrors) {
            if (empty($phaseErrors)) {
                continue;
            }

            $generation->phases()->where('phase', $phaseKey)->update([
                'status' => SimulatorAiPhase::STATUS_PENDING,
                'last_error' => mb_substr(implode(' ', array_unique($phaseErrors)), 0, 2000),
            ]);
        }
    }

    private function stripCodeFence(string $content): string
    {
        if (preg_match('/^```[a-zA-Z]*\s*\n(.*?)\n?```$/s', $content, $m)) {
            return trim($m[1]);
        }

        return $content;
    }

    private function extractTitleFromHtml(string $html): ?string
    {
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m)) {
            return trim(strip_tags($m[1])) ?: null;
        }
        if (preg_match('/<h1[^>]*>(.*?)<\/h1>/is', $html, $m)) {
            return trim(strip_tags($m[1])) ?: null;
        }

        return null;
    }

    /** Sleep while keeping the job visibly alive for the polling UI. */
    private function pause(SimulatorAiGeneration $generation, int $seconds): void
    {
        if (! config('ai.simulator.retry_backoff', true)) {
            return;
        }

        $seconds = max(0, min(120, $seconds));
        for ($i = 0; $i < $seconds; $i++) {
            sleep(1);
            $this->assertNotCancelled($generation);
            $generation->touchHeartbeat();
        }
    }

    /** Checked before every attempt/wait so a cancel request is honored at the next safe point. */
    private function assertNotCancelled(SimulatorAiGeneration $generation): void
    {
        if ($generation->isCancelled()) {
            throw new GenerationCancelledException;
        }
    }
}
