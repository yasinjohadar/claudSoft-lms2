<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\UsesLaravelAiSdkForWizards;
use App\Http\Controllers\Controller;
use App\Models\AIModel;
use App\Models\DocumentationCategory;
use App\Models\LaravelAiModel;
use App\Services\Ai\AIModelService;
use App\Services\Ai\AIProviderFactory;
use App\Services\AiNew\LaravelAiContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Suggests documentation page titles for a main subject. Nothing is stored:
 * the chosen titles are handed to the batch page, which generates them.
 */
class AIDocumentationTitleSuggestionController extends Controller
{
    use UsesLaravelAiSdkForWizards;

    /** Same ceiling as one batch, so every suggestion list fits in a single run. */
    private const MAX_TITLES = 100;

    public function __construct(
        private AIModelService $modelService,
        private LaravelAiContentService $laravelAi,
    ) {}

    public function create(Request $request)
    {
        $categories = DocumentationCategory::active()->ordered()->get();
        $models = $this->modelService->getAvailableModels('all');
        $categoryId = $request->get('documentation_category_id');

        $useLaravelAiEngine = $this->wizardUsesLaravelAiSdk('docs_engine');
        $laravelAiModels = LaravelAiModel::query()->activeOrdered()->get();
        $docsEngineChoiceAvailable = $models->isNotEmpty() && $laravelAiModels->isNotEmpty();

        return view('admin.docs.pages.ai-title-suggest', compact(
            'categories',
            'models',
            'categoryId',
            'useLaravelAiEngine',
            'laravelAiModels',
            'docsEngineChoiceAvailable',
        ));
    }

    public function suggest(Request $request)
    {
        $validated = $request->validate([
            'main_topic' => 'required|string|max:500',
            'auto_count' => 'boolean',
            'count' => 'nullable|required_unless:auto_count,true|integer|min:5|max:'.self::MAX_TITLES,
            'notes' => 'nullable|string|max:2000',
            'language' => 'nullable|in:ar,en',
            'level' => 'nullable|in:beginner,intermediate,advanced,all',
            'documentation_category_id' => 'nullable|exists:documentation_categories,id',
            'exclude' => 'nullable|array|max:200',
            'exclude.*' => 'nullable|string|max:3000',
            'ai_model_id' => 'nullable|exists:ai_models,id',
            'laravel_ai_model_id' => 'nullable|exists:laravel_ai_models,id',
            'docs_engine' => 'nullable|in:laravel_ai,legacy',
        ]);

        $exclude = collect($validated['exclude'] ?? [])
            ->map(fn ($t) => trim((string) $t))
            ->filter()
            ->values()
            ->all();

        $category = ! empty($validated['documentation_category_id'])
            ? DocumentationCategory::find($validated['documentation_category_id'])
            : null;

        // Auto: the model decides how many titles the subject really needs.
        $count = $request->boolean('auto_count') ? null : (int) $validated['count'];

        $prompt = $this->buildPrompt(
            trim($validated['main_topic']),
            $count,
            $validated['language'] ?? 'ar',
            $validated['level'] ?? 'all',
            trim((string) ($validated['notes'] ?? '')),
            $category?->name,
            $exclude,
        );

        try {
            $useLaravelAi = $this->resolveDocumentationAiEngine(
                $validated['docs_engine'] ?? null,
                ! empty($validated['laravel_ai_model_id']) ? (int) $validated['laravel_ai_model_id'] : null,
                ! empty($validated['ai_model_id']) ? (int) $validated['ai_model_id'] : null,
            );

            $raw = $useLaravelAi
                ? $this->askLaravelAi($prompt, $validated['laravel_ai_model_id'] ?? null)
                : $this->askLegacy($prompt, $validated['ai_model_id'] ?? null);
        } catch (\Throwable $e) {
            Log::error('AI documentation title suggestion: '.$e->getMessage());

            return $this->fail('تعذر اقتراح العناوين: '.$e->getMessage(), 500);
        }

        $titles = $this->parseTitles($raw, $exclude, $count ?? self::MAX_TITLES);

        if ($titles === []) {
            return $this->fail('لم يُستخرج أي عنوان من رد الموديل. حاول مجدداً أو غيّر الموديل.', 422);
        }

        return response()->json([
            'success' => true,
            'titles' => $titles,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    private function askLaravelAi(string $prompt, int|string|null $modelId): string
    {
        $model = null;
        if (! empty($modelId)) {
            $model = LaravelAiModel::query()->whereKey($modelId)->where('is_active', true)->first();
            if (! $model) {
                throw new \RuntimeException('موديل Laravel AI المحدد غير متاح أو غير نشط.');
            }
        }

        return $this->laravelAi->generateText($prompt, 'content.general', Auth::user(), $model, 120)['text'];
    }

    private function askLegacy(string $prompt, int|string|null $modelId): string
    {
        $model = ! empty($modelId)
            ? AIModel::query()->whereKey($modelId)->first()
            : $this->modelService->getDefaultModel();

        if (! $model) {
            throw new \RuntimeException('لا يوجد موديل AI نشط.');
        }

        return (string) AIProviderFactory::create($model)->generateText($prompt, [
            'max_tokens' => $model->max_tokens ?? 4000,
            'temperature' => 0.7,
        ]);
    }

    /**
     * @param  list<string>  $exclude
     */
    private function buildPrompt(
        string $mainTopic,
        ?int $count,
        string $language,
        string $level,
        string $notes,
        ?string $categoryName,
        array $exclude,
    ): string {
        $langLine = $language === 'en'
            ? 'اكتب العناوين باللغة الإنجليزية.'
            : 'اكتب العناوين باللغة العربية، مع إبقاء أسماء التقنيات والدوال والمصطلحات البرمجية بالإنجليزية.';

        $levelLine = match ($level) {
            'beginner' => 'المستوى المستهدف: مبتدئ — ركّز على الأساسيات والمفاهيم الأولى.',
            'intermediate' => 'المستوى المستهدف: متوسط — افترض معرفة الأساسيات وركّز على الاستخدام العملي.',
            'advanced' => 'المستوى المستهدف: متقدم — ركّز على المواضيع العميقة والأداء والأنماط المتقدمة.',
            default => 'غطِّ كل المستويات: من الأساسيات إلى المواضيع المتقدمة.',
        };

        $max = self::MAX_TITLES;
        $countLine = $count !== null
            ? "اقترح {$count} عنواناً"
            : "حدّد أنت عدد العناوين المناسب الذي يغطي الموضوع تغطية كاملة دون حشو أو تكرار — موضوع صغير يحتاج عناوين قليلة وموضوع واسع يحتاج عناوين أكثر (بحد أقصى {$max})، ثم اقترحها";

        $categoryLine = $categoryName ? "قسم التوثيق: {$categoryName}" : '';
        $notesLine = $notes !== '' ? "ملاحظات إضافية من المستخدم: {$notes}" : '';
        $excludeBlock = $exclude !== []
            ? "لا تكرر أياً من العناوين التالية ولا ما يطابقها في المعنى:\n- ".implode("\n- ", $exclude)
            : '';

        return trim(<<<PROMPT
أنت خبير في كتابة التوثيق التقني وتصميم المناهج.
المطلوب: {$countLine} لصفحات توثيق تشرح الموضوع الرئيسي التالي: «{$mainTopic}».
{$categoryLine}

القواعد:
1. كل عنوان يصلح أن يكون صفحة توثيق مستقلة ومحددة (ليس عاماً جداً ولا ضيقاً جداً).
2. رتّب العناوين ترتيباً تعليمياً منطقياً من الأساسيات إلى المتقدم.
3. لا تكرر العناوين ولا تجعل عنوانين يغطيان نفس المحتوى.
4. {$levelLine}
5. {$langLine}
6. العنوان قصير وواضح (عادة أقل من 12 كلمة) بدون ترقيم.
{$notesLine}
{$excludeBlock}

أعد مصفوفة JSON فقط من النصوص، بدون markdown أو أي شرح، بالشكل:
["العنوان الأول", "العنوان الثاني"]
PROMPT);
    }

    /**
     * Accepts a JSON array (optionally fenced or wrapped in an object) and falls
     * back to one title per line, since not every model honours "JSON only".
     *
     * @param  list<string>  $exclude
     * @return list<string>
     */
    private function parseTitles(string $raw, array $exclude, int $limit): array
    {
        $text = trim(preg_replace('/^```[a-zA-Z]*\s*|\s*```$/m', '', trim($raw)) ?? '');

        $items = null;
        if (preg_match('/\[[\s\S]*\]/', $text, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) {
                $items = $decoded;
            }
        }
        if ($items === null) {
            $decoded = json_decode($text, true);
            if (is_array($decoded)) {
                $items = $decoded['titles'] ?? $decoded;
            }
        }
        if ($items === null) {
            $items = preg_split('/\R/u', $text) ?: [];
        }

        $seen = collect($exclude)->mapWithKeys(fn ($t) => [mb_strtolower($t) => true])->all();
        $titles = [];

        foreach ($items as $item) {
            if (is_array($item)) {
                $item = $item['title'] ?? reset($item);
            }
            if (! is_string($item)) {
                continue;
            }

            $title = preg_replace('/^\s*(?:[-*•]|\d+[.)\-]|[٠-٩]+[.)\-])\s*/u', '', $item);
            $title = trim(preg_replace('/\s+/u', ' ', (string) $title), " \t\"'«»,");
            if ($title === '' || in_array($title, ['[', ']'], true)) {
                continue;
            }

            $key = mb_strtolower($title);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $titles[] = Str::limit($title, 3000, '');

            if (count($titles) >= $limit) {
                break;
            }
        }

        return $titles;
    }

    private function fail(string $message, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status, [], JSON_UNESCAPED_UNICODE);
    }
}
