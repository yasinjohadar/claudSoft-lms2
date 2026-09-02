<?php

namespace App\Services\AiNew;

use App\Exceptions\Ai\AiProviderException;
use App\Exceptions\Ai\ResumableIncompleteException;
use App\Models\BlogAiGeneration;
use App\Models\BlogAiSection;
use App\Services\Ai\AiErrorClassifier;
use App\Services\Ai\BlogAiResultNormalizer;
use App\Services\Ai\BlogHtmlRepairer;
use App\Services\Ai\BlogSectionValidator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Engine-agnostic staged writer for long blog articles.
 *
 * Forked from DocumentationStagedGenerator: same state machine (outline →
 * section rows → write → assemble) and the same per-section retry ladder, but
 * every finished section is written to `blog_ai_sections` instead, and the
 * repairer/validator used are the blog-specific ones (no forced
 * <section class="content-section"> wrapper — see BlogHtmlRepairer).
 */
class BlogStagedGenerator
{
    /** Seconds to wait before each retry when the provider does not tell us. */
    private const BACKOFF_SECONDS = [2, 5, 12, 30];

    public function __construct(
        private BlogAiResultNormalizer $resultNormalizer,
        private AiErrorClassifier $classifier,
        private BlogHtmlRepairer $repairer = new BlogHtmlRepairer,
        private BlogSectionValidator $validator = new BlogSectionValidator,
    ) {}

    /**
     * @param  array<string, mixed>  $options
     * @param  callable(int $sectionTarget, int $maxTokens): array<string, mixed>  $outlineWriter
     * @param  callable(BlogSectionAttempt): string  $sectionWriter
     * @return array<string, mixed>
     */
    public function generate(
        BlogAiGeneration $generation,
        string $topic,
        array $options,
        int $sectionTarget,
        int $outlineTokens,
        int $sectionTokens,
        callable $outlineWriter,
        callable $sectionWriter,
    ): array {
        $contentLength = (string) ($options['content_length'] ?? 'medium');

        $outline = $this->ensureOutline($generation, $topic, $sectionTarget, $outlineTokens, $outlineWriter);
        $sections = $this->ensureSectionRows($generation, $outline, $sectionTarget);

        $this->writeSections($generation, $sections, $sectionTokens, $sectionWriter, $contentLength);

        return $this->assemble($generation, $topic, $outline);
    }

    /**
     * Reuse the outline stored on a previous attempt so resumed runs keep the same
     * headings, ordering and section identities.
     *
     * @param  callable(int, int): array<string, mixed>  $outlineWriter
     * @return array<string, mixed>
     */
    private function ensureOutline(
        BlogAiGeneration $generation,
        string $topic,
        int $sectionTarget,
        int $outlineTokens,
        callable $outlineWriter,
    ): array {
        $stored = $generation->partial_result['outline'] ?? null;
        if (is_array($stored) && is_array($stored['sections'] ?? null) && count($stored['sections']) >= 2) {
            Log::info('blog.outline', [
                'generation_id' => $generation->id,
                'outcome' => 'reused',
                'sections' => count($stored['sections']),
            ]);

            return $stored;
        }

        $generation->markProgress('outline', 'بناء مخطط الأقسام…', 8);

        $maxAttempts = max(1, (int) config('ai.blog.outline_attempts', 3));
        $tokens = max(512, $outlineTokens);
        $target = $sectionTarget;
        $last = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $started = hrtime(true);
            try {
                $outline = $outlineWriter($target, $tokens);
                $sections = $outline['sections'] ?? null;
                if (! is_array($sections) || count($sections) < 2) {
                    throw new AiProviderException(
                        'مخطط الأقسام عاد ناقصاً.',
                        AiProviderException::KIND_TOO_LARGE,
                    );
                }

                Log::info('blog.outline', [
                    'generation_id' => $generation->id,
                    'outcome' => 'ok',
                    'attempt' => $attempt,
                    'max_tokens' => $tokens,
                    'sections' => count($sections),
                    'duration_ms' => (int) ((hrtime(true) - $started) / 1_000_000),
                ]);

                $generation->markProgress('outline', 'تم المخطط — بدء كتابة الأقسام', 12, ['outline' => $outline]);

                return $outline;
            } catch (Throwable $e) {
                $error = $this->classifier->fromThrowable($e);
                $last = $error;

                Log::warning('blog.outline', [
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

                // A truncated outline means fewer sections, not a longer wait.
                if ($error->needsSmallerRequest()) {
                    $target = max(3, (int) floor($target * 0.75));
                } else {
                    $this->pause($generation, $error->retryAfterSeconds ?? self::BACKOFF_SECONDS[$attempt - 1] ?? 20);
                }
            }
        }

        throw $last ?? new AiProviderException('فشل بناء مخطط الأقسام.');
    }

    /**
     * @param  array<string, mixed>  $outline
     * @return Collection<int, BlogAiSection>
     */
    private function ensureSectionRows(
        BlogAiGeneration $generation,
        array $outline,
        int $sectionTarget,
    ) {
        $existing = $generation->sections()->get();
        if ($existing->isNotEmpty()) {
            return $existing;
        }

        $planned = array_values(array_slice($outline['sections'] ?? [], 0, $sectionTarget + 2));
        $position = 0;
        foreach ($planned as $section) {
            $heading = trim((string) ($section['heading'] ?? ''));
            if ($heading === '') {
                continue;
            }

            BlogAiSection::query()->create([
                'generation_id' => $generation->id,
                'position' => $position++,
                'heading' => mb_substr($heading, 0, 250),
                'brief' => trim((string) ($section['brief'] ?? '')) ?: null,
                'status' => BlogAiSection::STATUS_PENDING,
            ]);
        }

        if ($position < 2) {
            throw new AiProviderException('فشل بناء مخطط الأقسام: لم تُستخرج عناوين صالحة.');
        }

        return $generation->sections()->get();
    }

    /**
     * @param  Collection<int, BlogAiSection>  $sections
     * @param  callable(BlogSectionAttempt): string  $sectionWriter
     */
    private function writeSections(
        BlogAiGeneration $generation,
        $sections,
        int $sectionTokens,
        callable $sectionWriter,
        string $contentLength = 'medium',
    ): void {
        $total = $sections->count();
        $delayMs = max(0, (int) config('ai.blog.section_delay_ms', 300));
        $allHeadings = $sections->pluck('heading')->filter()->values()->all();
        $priorHeadings = [];
        $index = 0;

        foreach ($sections as $section) {
            $index++;
            if ($section->isDone()) {
                $priorHeadings[] = $section->heading;

                continue;
            }

            $doneBefore = $sections->where('status', BlogAiSection::STATUS_DONE)->count();
            $generation->markProgress(
                'section_'.$index,
                'كتابة القسم '.$index.' من '.$total.'…',
                15 + (int) floor(($index / max(1, $total)) * 70),
                [
                    'sections_done' => $doneBefore,
                    'sections_planned' => $total,
                    'current_heading' => $section->heading,
                ]
            );

            // Spacing between calls keeps long runs under provider per-minute token budgets.
            if ($delayMs > 0 && $index > 1) {
                usleep($delayMs * 1000);
            }

            // Everything after this heading, so the section does not wander into
            // material a later section is meant to cover.
            $laterHeadings = array_values(array_slice($allHeadings, $index));

            $this->writeOneSection(
                $generation,
                $section,
                $priorHeadings,
                $sectionTokens,
                $sectionWriter,
                $contentLength,
                $laterHeadings,
            );

            if ($section->isDone()) {
                $priorHeadings[] = $section->heading;
            }
        }
    }

    /**
     * Write one section, retrying it as many times as the failure warrants.
     *
     * @param  list<string>  $priorHeadings
     * @param  list<string>  $laterHeadings
     * @param  callable(BlogSectionAttempt): string  $sectionWriter
     */
    private function writeOneSection(
        BlogAiGeneration $generation,
        BlogAiSection $section,
        array $priorHeadings,
        int $sectionTokens,
        callable $sectionWriter,
        string $contentLength = 'medium',
        array $laterHeadings = [],
    ): void {
        $ladder = $this->attemptLadder($sectionTokens);
        $attemptsUsed = (int) $section->attempts;
        $waitBudget = max(0, (int) config('ai.blog.rate_limit_retries', 2));

        $rung = 0;
        $waitsUsed = 0;
        $callNumber = 0;
        $qualityRetried = false;
        $last = null;

        while ($rung < count($ladder)) {
            $step = $ladder[$rung];
            $attemptsUsed++;
            $callNumber++;
            $started = hrtime(true);

            try {
                $html = $sectionWriter(new BlogSectionAttempt(
                    heading: $section->heading,
                    brief: (string) $section->brief,
                    priorHeadings: $priorHeadings,
                    attempt: $callNumber,
                    maxTokens: $step['tokens'],
                    compact: $step['compact'],
                    laterHeadings: $laterHeadings,
                ));

                $html = $this->repairer->repairSection($html, $section->heading);
                if ($html === '') {
                    throw new AiProviderException(
                        'استجابة القسم فارغة بعد التنظيف.',
                        AiProviderException::KIND_TOO_LARGE,
                    );
                }

                $reject = $this->validator->rejectionReason($html, $contentLength, $step['compact']);
                if ($reject !== null && ! $qualityRetried) {
                    // Ask again at the SAME size. Stepping down here would be
                    // backwards: the next rung asks for a shorter section, which is
                    // the opposite of what a thin answer needs.
                    $qualityRetried = true;

                    Log::warning('blog.section', [
                        'generation_id' => $generation->id,
                        'position' => $section->position,
                        'heading' => $section->heading,
                        'attempt' => $callNumber,
                        'rung' => $rung + 1,
                        'max_tokens' => $step['tokens'],
                        'outcome' => 'rejected',
                        'reject_reason' => $reject,
                        'retry_action' => 'retry_same_rung',
                        'duration_ms' => (int) ((hrtime(true) - $started) / 1_000_000),
                    ]);

                    $section->update(['attempts' => $attemptsUsed]);

                    continue;
                }

                // Second look was no better: a mediocre section still beats a
                // missing one, so keep it and record why it was let through.
                $section->markDone($html, $attemptsUsed);

                Log::info('blog.section', [
                    'generation_id' => $generation->id,
                    'position' => $section->position,
                    'heading' => $section->heading,
                    'attempt' => $callNumber,
                    'rung' => $rung + 1,
                    'max_tokens' => $step['tokens'],
                    'html_len' => mb_strlen($html),
                    'outcome' => 'ok',
                    'accepted_with' => $reject,
                    'duration_ms' => (int) ((hrtime(true) - $started) / 1_000_000),
                ]);

                return;
            } catch (Throwable $e) {
                $error = $this->classifier->fromThrowable($e);
                $last = $error;

                // Waiting fixes a rate limit or a blip; a smaller request does not.
                $waitInstead = ! $error->needsSmallerRequest() && ! $error->isFatal();
                $canWait = $waitInstead && $waitsUsed < $waitBudget;

                Log::warning('blog.section', [
                    'generation_id' => $generation->id,
                    'position' => $section->position,
                    'heading' => $section->heading,
                    'attempt' => $callNumber,
                    'rung' => $rung + 1,
                    'max_tokens' => $step['tokens'],
                    'outcome' => 'error',
                    'error_kind' => $error->kind,
                    'error' => $error->getMessage(),
                    'retry_action' => $error->isFatal() ? 'abort' : ($canWait ? 'wait_same_rung' : 'step_down'),
                    'duration_ms' => (int) ((hrtime(true) - $started) / 1_000_000),
                ]);

                $section->update([
                    'attempts' => $attemptsUsed,
                    'last_error' => mb_substr($error->getMessage(), 0, 2000),
                ]);

                // Bad key or exhausted credit: stop before burning the remaining sections.
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

                // New rung, new wait budget: the next size may be rate limited too.
                $rung++;
                $waitsUsed = 0;
            }
        }

        $section->markFailed(
            $last?->getMessage() ?? 'فشل توليد القسم بعد كل المحاولات.',
            $attemptsUsed,
        );
    }

    /**
     * Escalating fallbacks: the full section, then progressively smaller requests.
     *
     * @return list<array{tokens: int, compact: bool}>
     */
    public function attemptLadder(int $baseTokens): array
    {
        $base = max(512, $baseTokens);
        $shape = [
            ['factor' => 1.0, 'compact' => false],
            ['factor' => 0.75, 'compact' => false],
            ['factor' => 0.6, 'compact' => true],
            ['factor' => 0.45, 'compact' => true],
        ];

        $wanted = max(1, min(count($shape), (int) config('ai.blog.section_attempts', 4)));
        $ladder = [];
        foreach (array_slice($shape, 0, $wanted) as $step) {
            $ladder[] = [
                'tokens' => max(512, (int) floor($base * $step['factor'])),
                'compact' => $step['compact'],
            ];
        }

        return $ladder;
    }

    /**
     * @param  array<string, mixed>  $outline
     * @return array<string, mixed>
     */
    private function assemble(BlogAiGeneration $generation, string $topic, array $outline): array
    {
        $sections = $generation->sections()->get();
        $done = $sections->filter(fn (BlogAiSection $s) => $s->isDone());
        $failed = $sections->reject(fn (BlogAiSection $s) => $s->isDone());

        Log::info('blog.assemble', [
            'generation_id' => $generation->id,
            'planned' => $sections->count(),
            'done' => $done->count(),
            'failed' => $failed->count(),
        ]);

        if ($failed->isNotEmpty()) {
            $headings = $failed->pluck('heading')->filter()->values()->all();

            throw new ResumableIncompleteException(
                'تم توليد '.$done->count().' من '.$sections->count().' قسماً وحُفظت. '
                .'تعذّر إكمال: '.implode('، ', array_slice($headings, 0, 4))
                .(count($headings) > 4 ? ' وغيرها' : '')
                .'. اضغط «متابعة التوليد» لإكمال الأقسام الناقصة فقط.',
                done: $done->count(),
                planned: $sections->count(),
                failedHeadings: $headings,
            );
        }

        $generation->markProgress('assemble', 'دمج الأقسام…', 92);

        $content = $this->repairer->repairDocument(
            $this->resultNormalizer->normalizeHtmlString(
                $done->pluck('html')->filter()->implode("\n")
            )
        );
        if ($content === '') {
            throw new \RuntimeException('لم يُنتج محتوى HTML صالحاً بعد دمج الأقسام.');
        }

        $title = $this->resolveTitle($outline, $content, $topic);
        $excerpt = trim((string) ($outline['excerpt'] ?? ''));
        if ($excerpt === '' || $this->resultNormalizer->looksLikeJsonBlob($excerpt)) {
            $excerpt = $this->resultNormalizer->excerptFromHtml($content);
        }

        $shaped = $this->resultNormalizer->assertWizardShape([
            'title' => $title,
            'slug' => $outline['slug'] ?? null,
            'excerpt' => $excerpt,
            'content' => $content,
        ], $topic);

        return [
            'title' => $shaped['title'],
            'slug' => $this->normalizeSlug($shaped['slug'] ?? ($outline['slug'] ?? null), $shaped['title']),
            'excerpt' => $shaped['excerpt'],
            'content' => $shaped['content'],
            'meta_title' => $shaped['meta_title'] ?? null,
            'meta_description' => $shaped['meta_description'] ?? null,
        ];
    }

    /**
     * Never discard a finished article over a bad title — derive one instead.
     *
     * @param  array<string, mixed>  $outline
     */
    public function resolveTitle(array $outline, string $content, string $topic): string
    {
        $title = trim((string) ($outline['title'] ?? ''));
        if ($title !== ''
            && ! $this->resultNormalizer->looksLikeInstructionPrompt($title, $topic)
            && ! $this->resultNormalizer->looksLikeJsonBlob($title)) {
            return $title;
        }

        if (preg_match('/<h[12][^>]*>(.*?)<\/h[12]>/is', $content, $m)) {
            $fromHeading = trim(strip_tags($m[1]));
            if ($fromHeading !== '' && ! $this->resultNormalizer->looksLikeInstructionPrompt($fromHeading, $topic)) {
                return mb_substr($fromHeading, 0, 180);
            }
        }

        $fromTopic = trim(preg_replace('/\s+/', ' ', strip_tags($topic)) ?: '');
        $fromTopic = preg_replace('/^(قم\s+ب|أنشئ|اكتب|إنشاء|Create\s+|Write\s+|Generate\s+|Please\s+)\S*\s*/ui', '', $fromTopic) ?: $fromTopic;

        return mb_substr(trim($fromTopic) !== '' ? trim($fromTopic) : 'مقال مدونة', 0, 70);
    }

    private function normalizeSlug(?string $slug, string $title): string
    {
        if ($slug !== null && trim($slug) !== '') {
            $s = preg_replace('/\s+/', '-', trim($slug));
            $s = preg_replace('/[^\p{Arabic}a-zA-Z0-9-]/u', '', (string) $s);
            $s = preg_replace('/-+/', '-', (string) $s);
            $s = trim((string) $s, '-');
            if ($s !== '') {
                return $s;
            }
        }

        $s = Str::slug($title, '-', 'ar');

        return $s !== '' ? $s : 'post-'.time();
    }

    /** Sleep while keeping the job visibly alive for the polling UI. */
    private function pause(BlogAiGeneration $generation, int $seconds): void
    {
        if (! config('ai.blog.retry_backoff', true)) {
            return;
        }

        $seconds = max(0, min(120, $seconds));
        for ($i = 0; $i < $seconds; $i++) {
            sleep(1);
            $generation->touchHeartbeat();
        }
    }
}
