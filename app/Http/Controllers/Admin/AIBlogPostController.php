<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\CleansUtf8AiResponse;
use App\Http\Controllers\Admin\Concerns\UsesLaravelAiSdkForWizards;
use App\Http\Controllers\Controller;
use App\Models\AIModel;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\LaravelAiModel;
use App\Services\Ai\AIBlogPostService;
use App\Services\Ai\AIModelService;
use App\Services\AiNew\LaravelAiBlogService;
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
        private AIModelService $modelService
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
        $laravelAiModels = $useLaravelAiEngine
            ? LaravelAiModel::query()->activeOrdered()->get()
            : collect();

        return view('admin.blog.ai-posts.create', compact(
            'categories',
            'tags',
            'models',
            'useLaravelAiEngine',
            'laravelAiModels',
        ));
    }

    /**
     * AJAX endpoint لتوليد المحتوى
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'topic' => 'required|string|max:500',
            'ai_model_id' => 'nullable|exists:ai_models,id',
            'laravel_ai_model_id' => 'nullable|exists:laravel_ai_models,id',
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
            $category = $validated['category_id']
                ? BlogCategory::find($validated['category_id'])
                : null;

            $wizardOptions = [
                'content_length' => $validated['content_length'],
                'tone' => $validated['tone'] ?? 'professional',
                'language' => $validated['language'] ?? 'ar',
                'category' => $category,
                'generate_seo' => $validated['generate_seo'] ?? true,
                'generate_og' => $validated['generate_og'] ?? true,
                'generate_twitter' => $validated['generate_twitter'] ?? true,
                'generate_schema' => $validated['generate_schema'] ?? true,
                'generate_keyword_synonyms' => $validated['generate_keyword_synonyms'] ?? true,
            ];

            if ($this->wizardUsesLaravelAiSdk('blog_engine')) {
                $laraModel = null;
                if (! empty($validated['laravel_ai_model_id'])) {
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

                $blogPostData = app(LaravelAiBlogService::class)->generateForLegacyWizard(
                    $validated['topic'],
                    $wizardOptions,
                    Auth::user(),
                    $laraModel,
                );
            } else {
                $model = $validated['ai_model_id']
                    ? AIModel::find($validated['ai_model_id'])
                    : $this->modelService->getDefaultModel();

                if (! $model) {
                    return response()->json([
                        'success' => false,
                        'message' => 'لا يوجد موديل AI متاح',
                    ], 400);
                }

                $blogPostData = $this->blogPostService->generateBlogPost(
                    $validated['topic'],
                    $model,
                    $wizardOptions
                );
            }

            // تنظيف جميع البيانات من الأحرف غير الصالحة في UTF-8
            $blogPostData = $this->cleanUtf8Data($blogPostData);

            return response()->json([
                'success' => true,
                'data' => $blogPostData,
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);

        } catch (\Exception $e) {
            Log::error('Error generating blog post with AI: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'validated_data' => $validated,
                'model_id' => $validated['ai_model_id'] ?? null,
                'topic' => $validated['topic'] ?? null,
            ]);

            // تحسين رسالة الخطأ لتكون أكثر وضوحاً
            $errorMessage = $e->getMessage();

            // تحديد نوع الخطأ وإعطاء رسالة مناسبة
            if (strpos($errorMessage, 'timeout') !== false || strpos($errorMessage, 'Timeout') !== false) {
                $userMessage = 'انتهت مهلة الاتصال. يرجى المحاولة مرة أخرى أو تقليل طول المحتوى المطلوب.';
            } elseif (strpos($errorMessage, 'API Key') !== false || strpos($errorMessage, 'api key') !== false) {
                $userMessage = 'مشكلة في API Key. يرجى التحقق من إعدادات الموديل.';
            } elseif (strpos($errorMessage, 'quota') !== false || strpos($errorMessage, 'رصيد') !== false) {
                $userMessage = 'رصيد الموديل غير كافٍ. يرجى التحقق من رصيدك.';
            } elseif (strpos($errorMessage, 'connection') !== false || strpos($errorMessage, 'اتصال') !== false) {
                $userMessage = 'مشكلة في الاتصال بالخادم. يرجى التحقق من اتصالك بالإنترنت والمحاولة مرة أخرى.';
            } else {
                $userMessage = 'حدث خطأ أثناء توليد المقال: '.$errorMessage;
            }

            return response()->json([
                'success' => false,
                'message' => $userMessage,
                'error_details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
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
                $validated['featured_image'] = $request->file('featured_image')->store('blog/images', 'public');
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
