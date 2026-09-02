<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\CleansUtf8AiResponse;
use App\Http\Controllers\Admin\Concerns\UsesLaravelAiSdkForWizards;
use App\Http\Controllers\Controller;
use App\Models\BlogAiGeneration;
use App\Models\BlogAiSection;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\LaravelAiModel;
use App\Services\Ai\AIBlogPostService;
use App\Services\Ai\AIModelService;
use App\Services\Ai\BlogHtmlRepairer;
use App\Services\AiNew\BlogAiJobStarter;
use App\Services\Storage\StorageHelperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AIBlogPostController extends Controller
{
    use CleansUtf8AiResponse;
    use UsesLaravelAiSdkForWizards;

    public function __construct(
        private AIBlogPostService $blogPostService,
        private AIModelService $modelService,
        private StorageHelperService $storageHelper,
        private BlogAiJobStarter $jobStarter,
        private BlogHtmlRepairer $repairer = new BlogHtmlRepairer,
    ) {}

    /**
     * عرض نموذج إنشاء مقال بالذكاء الاصطناعي
     */
    public function create(Request $request)
    {
        $categories = BlogCategory::orderBy('name')->get();
        $tags = BlogTag::orderBy('name')->get();
        // جلب جميع الموديلات المتاحة
        $models = $this->modelService->getAvailableModels('all');

        $useLaravelAiEngine = $this->wizardUsesLaravelAiSdk('blog_engine');
        $laravelAiModels = LaravelAiModel::query()->activeOrdered()->get();
        $blogEngineChoiceAvailable = $models->isNotEmpty() && $laravelAiModels->isNotEmpty();

        return view('admin.blog.ai-posts.create', compact(
            'categories',
            'tags',
            'models',
            'useLaravelAiEngine',
            'laravelAiModels',
            'blogEngineChoiceAvailable',
        ));
    }

    /**
     * AJAX endpoint لبدء توليد المحتوى — يبدأ مهمة في الطابور ويرجع فوراً برقم
     * المهمة (uuid)؛ التوليد الفعلي (مخطط ثم أقسام لو متوسط/طويل) يتم في الخلفية
     * عبر BlogAiPipelineService، بنفس آلية توليد صفحات التوثيق.
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'topic' => 'required|string|max:500',
            'ai_model_id' => 'nullable|exists:ai_models,id',
            'laravel_ai_model_id' => 'nullable|exists:laravel_ai_models,id',
            'blog_engine' => 'nullable|in:laravel_ai,legacy',
            'content_length' => 'required|in:short,medium,long',
            'tone' => 'nullable|in:professional,friendly,technical,casual,formal',
            'language' => 'nullable|in:ar,en',
            'category_id' => 'nullable|exists:blog_categories,id',
            'generate_seo' => 'boolean',
            'generate_og' => 'boolean',
            'generate_twitter' => 'boolean',
            'generate_schema' => 'boolean',
            'generate_keyword_synonyms' => 'boolean',
        ]);

        try {
            $requestedEngine = $validated['blog_engine'] ?? null;
            if ($requestedEngine === 'laravel_ai' && ! LaravelAiModel::query()->where('is_active', true)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يوجد موديل Laravel AI نشط. أضف موديلاً من لوحة «موديلات Laravel AI SDK» أو اختر المحرك القديم.',
                ], 400);
            }

            $engine = $this->resolveWizardAiEngine($requestedEngine, 'blog_engine') ? 'laravel_ai' : 'legacy';

            if ($engine === 'laravel_ai' && ! empty($validated['laravel_ai_model_id'])) {
                $laraModel = LaravelAiModel::query()
                    ->where('id', $validated['laravel_ai_model_id'])
                    ->where('is_active', true)
                    ->first();
                if (! $laraModel) {
                    return response()->json([
                        'success' => false,
                        'message' => 'موديل Laravel AI المحدد غير متاح أو غير نشط.',
                    ], 400);
                }
            }

            Log::info('AI blog generate engine resolved', [
                'engine' => $engine,
                'requested_engine' => $requestedEngine,
                'content_length' => $validated['content_length'],
                'laravel_ai_model_id' => $validated['laravel_ai_model_id'] ?? null,
                'ai_model_id' => $validated['ai_model_id'] ?? null,
            ]);

            $generation = $this->jobStarter->start(
                Auth::user(),
                BlogAiGeneration::OPERATION_GENERATE,
                [
                    'topic' => $validated['topic'],
                    'blog_engine' => $engine,
                    'ai_model_id' => $validated['ai_model_id'] ?? null,
                    'laravel_ai_model_id' => $validated['laravel_ai_model_id'] ?? null,
                    'content_length' => $validated['content_length'],
                    'tone' => $validated['tone'] ?? 'professional',
                    'language' => $validated['language'] ?? 'ar',
                    'category_id' => $validated['category_id'] ?? null,
                    'generate_seo' => $validated['generate_seo'] ?? true,
                    'generate_og' => $validated['generate_og'] ?? true,
                    'generate_twitter' => $validated['generate_twitter'] ?? true,
                    'generate_schema' => $validated['generate_schema'] ?? true,
                    'generate_keyword_synonyms' => $validated['generate_keyword_synonyms'] ?? true,
                ]
            );

            return response()->json([
                'success' => true,
                'async' => true,
                'job' => $generation->toStatusPayload(),
            ]);
        } catch (\Exception $e) {
            Log::error('AI blog generate start: '.$e->getMessage(), [
                'validated' => $validated,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'تعذر بدء التوليد: '.$e->getMessage(),
            ], 500);
        }
    }

    public function jobStatus(string $uuid)
    {
        $generation = BlogAiGeneration::query()
            ->where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // The worker died without reporting: stop the UI from polling forever.
        if ($generation->isStale()) {
            $generation->markPaused(
                'توقّفت عملية التوليد دون استجابة من الخادم. الأقسام المكتملة محفوظة — اضغط «متابعة التوليد».'
            );
        }

        return response()->json([
            'success' => true,
            'job' => $generation->toStatusPayload(),
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
    }

    /**
     * Continue a paused/failed staged generation: only the sections that are not
     * done yet are regenerated, the outline and finished sections are reused.
     */
    public function jobResume(string $uuid)
    {
        $generation = BlogAiGeneration::query()
            ->where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (! $generation->isResumable()) {
            return response()->json([
                'success' => false,
                'message' => $generation->status === BlogAiGeneration::STATUS_COMPLETED
                    ? 'اكتمل التوليد بالفعل.'
                    : 'لا يوجد تقدم محفوظ لمتابعته. ابدأ توليداً جديداً.',
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }

        $generation->update([
            'status' => BlogAiGeneration::STATUS_QUEUED,
            'stage' => 'resuming',
            'stage_label' => 'استئناف التوليد…',
            'error_message' => null,
            'finished_at' => null,
            'heartbeat_at' => now(),
        ]);

        $this->jobStarter->dispatch($generation);

        return response()->json([
            'success' => true,
            'job' => $generation->fresh()->toStatusPayload(),
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Assemble whatever sections finished so the admin can keep partial work
     * without waiting for the missing sections.
     */
    public function jobPartial(string $uuid)
    {
        $generation = BlogAiGeneration::query()
            ->where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $done = $generation->sections()
            ->where('status', BlogAiSection::STATUS_DONE)
            ->orderBy('position')
            ->pluck('html')
            ->filter()
            ->values();

        if ($done->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد أقسام مكتملة بعد.',
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }

        $outline = $generation->partial_result['outline'] ?? [];
        // Partial assemblies get the same balancing as a finished run, so an
        // article saved from here cannot carry an unclosed tag into the editor.
        $content = $this->repairer->repairDocument($done->implode("\n"));

        return response()->json([
            'success' => true,
            'result' => [
                'title' => is_array($outline) ? trim((string) ($outline['title'] ?? '')) : '',
                'slug' => is_array($outline) ? trim((string) ($outline['slug'] ?? '')) : '',
                'excerpt' => is_array($outline) ? trim((string) ($outline['excerpt'] ?? '')) : '',
                'content' => $content,
            ],
            'sections' => $generation->sectionSummary(),
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
    }

    /**
     * حفظ المقال المنشأ بالذكاء الاصطناعي
     */
    public function store(Request $request)
    {
        // تجاهل الحقول الخاصة بالتوليد (إذا كانت موجودة)
        $request->merge(array_diff_key($request->all(), array_flip([
            'topic', 'ai_model_id', 'content_length', 'tone', 'language',
            'generate_seo', 'generate_og', 'generate_twitter', 'generate_schema', 'generate_keyword_synonyms',
        ])));

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', 'regex:/^[\p{Arabic}a-zA-Z0-9\s-]+$/u', 'unique:blog_posts,slug'],
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'category_id' => 'required|exists:blog_categories,id',
            'status' => 'required|in:draft,published,scheduled',
            'featured_image' => 'nullable|image|max:2048',
            'featured_image_alt' => 'nullable|string|max:255',
            'published_at' => 'nullable|date',

            // SEO Fields
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'focus_keyword' => 'nullable|string|max:255',
            'focus_keyword_synonyms' => 'nullable|string',
            'canonical_url' => 'nullable|url|max:500',

            // Open Graph
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string',
            'og_type' => 'nullable|in:article,website,blog',
            'og_locale' => 'nullable|string|max:10',

            // Twitter Card
            'twitter_card' => 'nullable|in:summary,summary_large_image',
            'twitter_title' => 'nullable|string|max:255',
            'twitter_description' => 'nullable|string',
            'twitter_creator' => 'nullable|string|max:255',

            // Schema.org
            'schema_type' => 'nullable|string|max:50',
            'schema_headline' => 'nullable|string|max:255',
            'schema_description' => 'nullable|string',
            'schema_image' => 'nullable|string|max:500',
            'schema_author_name' => 'nullable|string|max:255',
            'schema_author_url' => 'nullable|url|max:500',

            // Flags
            'is_featured' => 'boolean',
            'allow_comments' => 'boolean',
            'is_indexable' => 'boolean',
            'is_followable' => 'boolean',

            // Tags
            'tags' => 'nullable|array',
            'tags.*' => 'exists:blog_tags,id',
        ]);

        DB::beginTransaction();

        try {
            // Use provided slug or generate from title
            $slug = $validated['slug'] ?? Str::slug($validated['title'], '-', 'ar');

            // Clean slug
            $slug = preg_replace('/\s+/', '-', trim($slug));
            $slug = preg_replace('/[^\p{Arabic}a-zA-Z0-9-]/u', '', $slug);
            $slug = preg_replace('/-+/', '-', $slug);
            $slug = trim($slug, '-');

            if (empty($slug)) {
                $slug = 'post-'.time();
            }

            // Check for unique slug
            $counter = 1;
            $originalSlug = $slug;
            while (BlogPost::where('slug', $slug)->exists()) {
                $slug = $originalSlug.'-'.$counter++;
            }

            $validated['slug'] = $slug;

            // Set author
            $validated['author_id'] = Auth::id();

            // Map category_id to blog_category_id
            if (isset($validated['category_id'])) {
                $validated['blog_category_id'] = $validated['category_id'];
                unset($validated['category_id']);
            }

            // Handle featured image upload
            if ($request->hasFile('featured_image')) {
                $featuredImagePath = $this->storageHelper->storeUploadedFileWithFailover('blog_images', 'blog/images', $request->file('featured_image'), 'image');
                if ($featuredImagePath) {
                    $validated['featured_image'] = $featuredImagePath;
                }
            }

            // Set published_at if status is published and not set
            if ($validated['status'] === 'published') {
                if (! isset($validated['published_at']) ||
                    (isset($validated['published_at']) && strtotime($validated['published_at']) > time())) {
                    $validated['published_at'] = now();
                }
            }

            // Set defaults
            if (! isset($validated['schema_type'])) {
                $validated['schema_type'] = 'Article';
            }

            if (! isset($validated['is_indexable'])) {
                $validated['is_indexable'] = true;
            }

            if (! isset($validated['og_type'])) {
                $validated['og_type'] = 'article';
            }

            if (! isset($validated['og_locale'])) {
                $validated['og_locale'] = 'ar_SA';
            }

            if (! isset($validated['twitter_card'])) {
                $validated['twitter_card'] = 'summary_large_image';
            }

            // Calculate reading time
            $wordCount = str_word_count(strip_tags($validated['content']));
            $validated['reading_time'] = max(1, ceil($wordCount / 200)); // Assuming 200 words per minute

            // Create post
            $post = BlogPost::create($validated);

            // Attach tags
            if (isset($validated['tags']) && is_array($validated['tags'])) {
                $post->tags()->sync($validated['tags']);
            }

            DB::commit();

            return redirect()->route('admin.blog.posts.edit', $post->id)
                ->with('success', 'تم إنشاء المقال بنجاح باستخدام الذكاء الاصطناعي!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing AI-generated blog post: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'validated_data' => $validated,
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حفظ المقال: '.$e->getMessage())
                ->withInput();
        }
    }
}
