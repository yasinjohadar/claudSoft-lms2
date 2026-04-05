<?php

namespace App\Services\AiNew;

use App\Ai\Agents\BlogDraftAgent;
use App\Models\BlogCategory;
use App\Models\LaravelAiModel;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Ai\Responses\StructuredAgentResponse;
use Throwable;

class LaravelAiBlogService
{
    public function __construct(
        private LaravelAiProviderManager $providerManager,
        private LaravelAiRequestLogger $logger,
        private LaravelAiPromptRunner $promptRunner,
    ) {}

    /**
     * @return array{title: string, content: string, excerpt?: string|null}
     */
    public function generateDraft(
        string $topic,
        ?Authenticatable $user = null,
        ?LaravelAiModel $model = null,
        int $timeout = 120,
    ): array {
        $topic = Str::limit(trim($topic), 4000);
        $prompt = "Write a blog post draft about:\n\n{$topic}";

        return $this->executeBlogDraft($prompt, $user, $model, $timeout, ['topic' => $topic]);
    }

    /**
     * Same shape as legacy AIBlogPostService::generateBlogPost for the admin wizard JSON.
     *
     * @param  array{
     *     content_length?: string,
     *     tone?: string,
     *     language?: string,
     *     category?: BlogCategory|null,
     *     generate_seo?: bool,
     *     generate_og?: bool,
     *     generate_twitter?: bool,
     *     generate_schema?: bool,
     *     generate_keyword_synonyms?: bool
     * }  $options
     */
    public function generateForLegacyWizard(
        string $topic,
        array $options,
        ?Authenticatable $user = null,
        ?LaravelAiModel $explicitModel = null,
        int $timeout = 300,
    ): array {
        set_time_limit(500);

        $prompt = $this->buildWizardPrompt($topic, $options);
        $draft = $this->executeBlogDraft($prompt, $user, $explicitModel, $timeout, [
            'topic' => $topic,
            'wizard' => true,
        ]);

        return $this->expandDraftToWizardPayload($draft, $options);
    }

    /**
     * @return array{title: string, content: string, excerpt?: string|null}
     */
    private function executeBlogDraft(
        string $prompt,
        ?Authenticatable $user,
        ?LaravelAiModel $model,
        int $timeout,
        array $logContext,
    ): array {
        $model ??= LaravelAiModel::query()->activeOrdered()->forCapability('blog.generate')->first()
            ?? LaravelAiModel::query()->activeOrdered()->first();

        if (! $model) {
            throw new \RuntimeException('لا يوجد موديل Laravel AI نشط. أضف موديلاً من لوحة «Laravel AI SDK» مع القدرة blog.generate إن رغبت.');
        }

        $started = hrtime(true);
        $operation = 'blog.generate';

        try {
            /** @var StructuredAgentResponse $response */
            $response = $this->providerManager->runWithModel($model, function () use ($model, $prompt, $timeout) {
                $agent = new BlogDraftAgent;

                return $this->promptRunner->runStructured($model, $agent, $prompt, $timeout);
            });

            $latency = (int) ((hrtime(true) - $started) / 1_000_000);
            $structured = $response->toArray();

            $this->logger->logSuccess(
                $model,
                $user,
                $operation,
                $logContext,
                $structured,
                $latency,
            );

            return $structured;
        } catch (Throwable $e) {
            $latency = (int) ((hrtime(true) - $started) / 1_000_000);
            Log::error('LaravelAiBlogService failed', ['exception' => $e->getMessage()]);

            $this->logger->logFailure(
                $model,
                $user,
                $operation,
                $logContext,
                $e->getMessage(),
                $latency,
            );

            if (str_contains(strtolower($e->getMessage()), 'too large')) {
                throw new \RuntimeException(
                    'طلب التوليد تجاوز حد الحجم أو الرموز عند المزود (مثلاً Groq). جرّب اختيار «طول محتوى» أقصر، أو موديلاً آخر، أو راجع حد max_tokens في لوحة الموديل.',
                    0,
                    $e
                );
            }

            throw $e;
        }
    }

    private function buildWizardPrompt(string $topic, array $options): string
    {
        $topic = Str::limit(trim($topic), 4000);

        $contentLength = $options['content_length'] ?? 'medium';
        $tone = $options['tone'] ?? 'professional';
        $language = $options['language'] ?? 'ar';
        /** @var BlogCategory|null $category */
        $category = $options['category'] ?? null;

        $lengthMap = [
            'short' => 'roughly 500–800 words',
            'medium' => 'roughly 1000–1500 words',
            'long' => 'roughly 2000–3000 words',
        ];
        $lengthText = $lengthMap[$contentLength] ?? $lengthMap['medium'];

        $toneMap = [
            'professional' => 'professional and clear',
            'friendly' => 'friendly and approachable',
            'technical' => 'technical and detailed',
            'casual' => 'casual and conversational',
            'formal' => 'formal and polished',
        ];
        $toneText = $toneMap[$tone] ?? $toneMap['professional'];

        $langLine = $language === 'en'
            ? 'Write the title, excerpt, and full article body in English.'
            : 'Write the title, excerpt, and full article body in Arabic (Modern Standard Arabic).';

        $categoryLine = $category
            ? "The post fits the blog category: {$category->name}.\n"
            : '';

        return <<<PROMPT
Write a complete blog post draft as structured fields (title, content HTML, excerpt).

Topic: {$topic}

{$categoryLine}{$langLine}
Tone: {$toneText}.
Target length: {$lengthText}.

Content requirements:
- Use semantic HTML in "content": h2, h3, p, ul, ol, strong, em where appropriate.
- Include an engaging introduction, clear sections with subheadings, and a short conclusion.
- "excerpt" should be a concise plain-text summary (about 100–150 words or characters appropriate to the language).

PROMPT;
    }

    /**
     * @param  array{title?: string, content?: string, excerpt?: string|null}  $draft
     */
    private function expandDraftToWizardPayload(array $draft, array $options): array
    {
        $title = trim((string) ($draft['title'] ?? ''));
        $content = (string) ($draft['content'] ?? '');
        $excerpt = isset($draft['excerpt']) ? trim((string) $draft['excerpt']) : '';
        if ($excerpt === '') {
            $excerpt = Str::limit(preg_replace('/\s+/', ' ', strip_tags($content)), 150);
        }

        $slug = $this->slugFromTitle($title !== '' ? $title : 'post');
        $lang = $options['language'] ?? 'ar';
        $locale = $lang === 'en' ? 'en_US' : 'ar_SA';

        $plainExcerpt = strip_tags($excerpt);

        $result = [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content' => $content,
            'reading_time' => max(1, (int) ceil(str_word_count(strip_tags($content)) / 200)),
            'canonical_url' => url('/blog/'.$slug),
        ];

        if ($options['generate_seo'] ?? true) {
            $result['meta_title'] = Str::limit($title, 60);
            $result['meta_description'] = Str::limit($plainExcerpt, 160);
            $result['meta_keywords'] = Str::limit($title, 100);
            $result['focus_keyword'] = Str::limit($title, 50);
        }

        if ($options['generate_og'] ?? true) {
            $result['og_title'] = $title;
            $result['og_description'] = Str::limit($plainExcerpt, 200);
            $result['og_type'] = 'article';
            $result['og_locale'] = $locale;
        }

        if ($options['generate_twitter'] ?? true) {
            $result['twitter_card'] = 'summary_large_image';
            $result['twitter_title'] = Str::limit($title, 70);
            $result['twitter_description'] = Str::limit($plainExcerpt, 200);
        }

        if ($options['generate_schema'] ?? true) {
            $result['schema_type'] = 'Article';
            $result['schema_headline'] = $title;
            $result['schema_description'] = Str::limit($plainExcerpt, 300);
        }

        if ($options['generate_keyword_synonyms'] ?? true) {
            $result['focus_keyword_synonyms'] = '';
        }

        return $result;
    }

    private function slugFromTitle(string $title): string
    {
        $slug = preg_replace('/\s+/', '-', trim($title));
        $slug = preg_replace('/[^\p{Arabic}a-zA-Z0-9-]/u', '', (string) $slug);
        $slug = preg_replace('/-+/', '-', (string) $slug);
        $slug = trim((string) $slug, '-');

        return $slug !== '' ? $slug : 'post-'.time();
    }
}
