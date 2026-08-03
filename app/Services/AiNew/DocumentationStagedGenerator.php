<?php

namespace App\Services\AiNew;

use App\Exceptions\Ai\AiProviderException;
use App\Exceptions\Ai\ResumableIncompleteException;
use App\Models\DocumentationAiGeneration;
use App\Models\DocumentationAiSection;
use App\Services\Ai\AiErrorClassifier;
use App\Services\Ai\DocumentationAiResultNormalizer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Engine-agnostic staged writer for long documentation pages.
 *
 * Every finished section is written to `documentation_ai_sections` before the next
 * call starts, so a crashed or rate-limited run never loses completed work and can
 * be continued later from exactly where it stopped.
 */
class DocumentationStagedGenerator
{
    /** Seconds to wait before each retry when the provider does not tell us. */
    private const BACKOFF_SECONDS = [3, 8, 20, 45];

    public function __construct(
        private DocumentationAiResultNormalizer $resultNormalizer,
        private AiErrorClassifier $classifier,
    ) {}

    /**
     * @param  array<string, mixed>  $options
     * @param  callable(int $sectionTarget, int $maxTokens): array<string, mixed>  $outlineWriter
     * @param  callable(DocumentationSectionAttempt): string  $sectionWriter
     * @return array<string, mixed>
     */
    public function generate(
        DocumentationAiGeneration $generation,
        string $topic,
        array $options,
        int $sectionTarget,
        int $outlineTokens,
        int $sectionTokens,
        callable $outlineWriter,
        callable $sectionWriter,
    ): array {
        $outline = $this->ensureOutline($generation, $topic, $sectionTarget, $outlineTokens, $outlineWriter);
        $sections = $this->ensureSectionRows($generation, $outline, $sectionTarget);

        $this->writeSections($generation, $sections, $sectionTokens, $sectionWriter);

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
        DocumentationAiGeneration $generation,
        string $topic,
        int $sectionTarget,
        int $outlineTokens,
        callable $outlineWriter,
    ): array {
        $stored = $generation->partial_result['outline'] ?? null;
        if (is_array($stored) && is_array($stored['sections'] ?? null) && count($stored['sections']) >= 2) {
            Log::info('docs.outline', [
                'generation_id' => $generation->id,
                'outcome' => 'reused',
                'sections' => count($stored['sections']),
            ]);

            return $stored;
        }

        $generation->markProgress('outline', 'بناء مخطط الأقسام…', 8);

        $maxAttempts = max(1, (int) config('ai.docs.outline_attempts', 3));
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

                Log::info('docs.outline', [
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

                Log::warning('docs.outline', [
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
                    $target = max(4, (int) floor($target * 0.75));
                } else {
                    $this->pause($generation, $error->retryAfterSeconds ?? self::BACKOFF_SECONDS[$attempt - 1] ?? 20);
                }
            }
        }

        throw $last ?? new AiProviderException('فشل بناء مخطط الأقسام.');
    }

    /**
     * @param  array<string, mixed>  $outline
     * @return \Illuminate\Support\Collection<int, DocumentationAiSection>
     */
    private function ensureSectionRows(
        DocumentationAiGeneration $generation,
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

            DocumentationAiSection::query()->create([
                'generation_id' => $generation->id,
                'position' => $position++,
                'heading' => mb_substr($heading, 0, 250),
                'brief' => trim((string) ($section['brief'] ?? '')) ?: null,
                'status' => DocumentationAiSection::STATUS_PENDING,
            ]);
        }

        if ($position < 2) {
            throw new AiProviderException('فشل بناء مخطط الأقسام: لم تُستخرج عناوين صالحة.');
        }

        return $generation->sections()->get();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, DocumentationAiSection>  $sections
     * @param  callable(DocumentationSectionAttempt): string  $sectionWriter
     */
    private function writeSections(
        DocumentationAiGeneration $generation,
        $sections,
        int $sectionTokens,
        callable $sectionWriter,
    ): void {
        $total = $sections->count();
        $delayMs = max(0, (int) config('ai.docs.section_delay_ms', 1200));
        $priorHeadings = [];
        $index = 0;

        foreach ($sections as $section) {
            $index++;
            if ($section->isDone()) {
                $priorHeadings[] = $section->heading;

                continue;
            }

            $doneBefore = $sections->where('status', DocumentationAiSection::STATUS_DONE)->count();
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

            $this->writeOneSection($generation, $section, $priorHeadings, $sectionTokens, $sectionWriter);

            if ($section->isDone()) {
                $priorHeadings[] = $section->heading;
            }
        }
    }

    /**
     * @param  list<string>  $priorHeadings
     * @param  callable(DocumentationSectionAttempt): string  $sectionWriter
     */
    private function writeOneSection(
        DocumentationAiGeneration $generation,
        DocumentationAiSection $section,
        array $priorHeadings,
        int $sectionTokens,
        callable $sectionWriter,
    ): void {
        $ladder = $this->attemptLadder($sectionTokens);
        $attemptsUsed = (int) $section->attempts;
        $last = null;

        foreach ($ladder as $rung => $step) {
            $attemptsUsed++;
            $started = hrtime(true);

            try {
                $html = $sectionWriter(new DocumentationSectionAttempt(
                    heading: $section->heading,
                    brief: (string) $section->brief,
                    priorHeadings: $priorHeadings,
                    attempt: $rung + 1,
                    maxTokens: $step['tokens'],
                    compact: $step['compact'],
                    plain: $step['plain'],
                ));

                $html = $this->wrapSection($html, $section->heading);
                if ($html === '') {
                    throw new AiProviderException(
                        'استجابة القسم فارغة بعد التنظيف.',
                        AiProviderException::KIND_TOO_LARGE,
                    );
                }

                $section->markDone($html, $attemptsUsed);

                Log::info('docs.section', [
                    'generation_id' => $generation->id,
                    'position' => $section->position,
                    'heading' => $section->heading,
                    'attempt' => $rung + 1,
                    'max_tokens' => $step['tokens'],
                    'html_len' => mb_strlen($html),
                    'outcome' => 'ok',
                    'duration_ms' => (int) ((hrtime(true) - $started) / 1_000_000),
                ]);

                return;
            } catch (Throwable $e) {
                $error = $this->classifier->fromThrowable($e);
                $last = $error;

                Log::warning('docs.section', [
                    'generation_id' => $generation->id,
                    'position' => $section->position,
                    'heading' => $section->heading,
                    'attempt' => $rung + 1,
                    'max_tokens' => $step['tokens'],
                    'outcome' => 'error',
                    'error_kind' => $error->kind,
                    'error' => $error->getMessage(),
                    'duration_ms' => (int) ((hrtime(true) - $started) / 1_000_000),
                ]);

                $section->update(['attempts' => $attemptsUsed, 'last_error' => mb_substr($error->getMessage(), 0, 2000)]);

                // Bad key or exhausted credit: stop before burning the remaining sections.
                if ($error->isFatal()) {
                    throw $error;
                }

                if ($rung === count($ladder) - 1) {
                    break;
                }

                // Oversized requests are fixed by the next (smaller) rung, not by waiting.
                if (! $error->needsSmallerRequest()) {
                    $this->pause($generation, $error->retryAfterSeconds ?? self::BACKOFF_SECONDS[$rung] ?? 20);
                }
            }
        }

        $section->markFailed(
            $last?->getMessage() ?? 'فشل توليد القسم بعد كل المحاولات.',
            $attemptsUsed,
        );
    }

    /**
     * Escalating fallbacks: full section, then shorter, then bare HTML without the
     * JSON envelope that truncation tends to break.
     *
     * @return list<array{tokens: int, compact: bool, plain: bool}>
     */
    public function attemptLadder(int $baseTokens): array
    {
        $base = max(768, $baseTokens);
        $shape = [
            ['factor' => 1.0, 'compact' => false, 'plain' => false],
            ['factor' => 0.75, 'compact' => false, 'plain' => false],
            ['factor' => 0.6, 'compact' => true, 'plain' => false],
            ['factor' => 0.45, 'compact' => true, 'plain' => true],
        ];

        $wanted = max(1, min(count($shape), (int) config('ai.docs.section_attempts', 4)));
        $ladder = [];
        foreach (array_slice($shape, 0, $wanted) as $step) {
            $ladder[] = [
                'tokens' => max(768, (int) floor($base * $step['factor'])),
                'compact' => $step['compact'],
                'plain' => $step['plain'],
            ];
        }

        return $ladder;
    }

    /**
     * @param  array<string, mixed>  $outline
     * @return array<string, mixed>
     */
    private function assemble(DocumentationAiGeneration $generation, string $topic, array $outline): array
    {
        $sections = $generation->sections()->get();
        $done = $sections->filter(fn (DocumentationAiSection $s) => $s->isDone());
        $failed = $sections->reject(fn (DocumentationAiSection $s) => $s->isDone());

        Log::info('docs.assemble', [
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

        $content = $this->resultNormalizer->normalizeHtmlString(
            $done->pluck('html')->filter()->implode("\n")
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
     * Never discard a finished page over a bad title — derive one instead.
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

        return mb_substr(trim($fromTopic) !== '' ? trim($fromTopic) : 'صفحة توثيق', 0, 70);
    }

    private function wrapSection(string $html, string $heading): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        if (! str_contains($html, 'content-section')) {
            $html = '<section class="content-section"><h2 class="section-title">'.e($heading).'</h2>'.$html.'</section>';
        }

        return $html;
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

        return $s !== '' ? $s : 'doc-'.time();
    }

    /** Sleep while keeping the job visibly alive for the polling UI. */
    private function pause(DocumentationAiGeneration $generation, int $seconds): void
    {
        if (! config('ai.docs.retry_backoff', true)) {
            return;
        }

        $seconds = max(0, min(120, $seconds));
        for ($i = 0; $i < $seconds; $i++) {
            sleep(1);
            $generation->touchHeartbeat();
        }
    }
}
