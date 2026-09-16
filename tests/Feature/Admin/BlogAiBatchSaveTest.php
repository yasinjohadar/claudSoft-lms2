<?php

namespace Tests\Feature\Admin;

use App\Models\BlogAiBatch;
use App\Models\BlogAiBatchItem;
use App\Models\BlogAiGeneration;
use App\Models\BlogAiSection;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\User;
use App\Services\AiNew\BlogAiBatchRunner;
use App\Services\AiNew\BlogAiPipelineService;
use App\Services\Blog\BlogTagResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The batch save path: a finished article is written straight to blog_posts as
 * a draft, authored by the batch owner (Auth::id() is null on a worker), with
 * the AI's suggested tags created on the fly.
 */
class BlogAiBatchSaveTest extends TestCase
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('Blog AI feature tests require MySQL (SQLite migrations incompatible).');
        }
    }

    private function adminUser(): User
    {
        $role = Role::findOrCreate('admin', 'web');
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function category(): BlogCategory
    {
        return BlogCategory::query()->create([
            'name' => 'تقنية',
            'slug' => 'tech',
            'is_active' => true,
        ]);
    }

    /**
     * A batch whose single item is about to be processed, with the pipeline
     * stubbed so no provider is touched: it just marks the generation complete
     * with the result the runner is expected to save.
     *
     * @param  array<string, mixed>  $result
     */
    private function runOneItem(User $user, array $result, bool $generateTags = true): BlogAiBatchItem
    {
        Queue::fake();

        $batch = BlogAiBatch::query()->create([
            'user_id' => $user->id,
            'status' => BlogAiBatch::STATUS_QUEUED,
            'total' => 1,
            'settings' => [
                'category_id' => $this->category()->id,
                'content_length' => 'very_long',
                'language' => 'ar',
                'blog_engine' => 'legacy',
                'generate_tags' => $generateTags,
            ],
        ]);

        $item = BlogAiBatchItem::query()->create([
            'batch_id' => $batch->id,
            'position' => 0,
            'topic' => 'أساسيات تحسين محركات البحث',
            'status' => BlogAiBatchItem::STATUS_PENDING,
        ]);

        $this->mock(BlogAiPipelineService::class, function ($mock) use ($result) {
            $mock->shouldReceive('run')->once()->andReturnUsing(
                function (BlogAiGeneration $generation) use ($result) {
                    $generation->markCompleted($result);
                }
            );
        });

        app(BlogAiBatchRunner::class)->processNext($batch->id);

        return $item->fresh();
    }

    public function test_a_completed_item_is_saved_as_a_draft_post(): void
    {
        $user = $this->adminUser();

        $item = $this->runOneItem($user, [
            'title' => 'دليل تحسين محركات البحث',
            'excerpt' => 'ملخص',
            'content' => '<h2>مقدمة</h2><p>نص المقال بالكامل هنا.</p>',
            'meta_title' => 'دليل SEO',
            'tags' => [],
        ]);

        $this->assertSame(BlogAiBatchItem::STATUS_COMPLETED, $item->status);
        $this->assertNotNull($item->blog_post_id);

        $post = BlogPost::find($item->blog_post_id);

        $this->assertSame('draft', $post->status);
        $this->assertNull($post->published_at);
        // Auth::id() is null inside a queue worker: the author must come from the batch.
        $this->assertSame($user->id, (int) $post->author_id);
        $this->assertNotNull($post->blog_category_id);
        $this->assertNotEmpty($post->slug);
        // Arabic content used to score zero words and land on a bogus reading time.
        $this->assertGreaterThanOrEqual(1, (int) $post->reading_time);
    }

    public function test_ai_suggested_tags_are_created_and_attached(): void
    {
        $user = $this->adminUser();

        $item = $this->runOneItem($user, [
            'title' => 'دليل تحسين محركات البحث',
            'content' => '<h2>مقدمة</h2><p>نص.</p>',
            'tags' => ['برمجة', 'Laravel', 'برمجة'],
        ]);

        $post = BlogPost::find($item->blog_post_id);

        $this->assertCount(2, $post->tags);
        $this->assertSame(2, BlogTag::query()->count());

        // Str::slug() empties Arabic, and blog_tags.slug is unique — two Arabic
        // tags colliding on '' used to take the whole article save down.
        BlogTag::query()->get()->each(function (BlogTag $tag) {
            $this->assertNotSame('', (string) $tag->slug);
        });
        $this->assertSame(2, BlogTag::query()->distinct()->count('slug'));
    }

    public function test_two_arabic_tags_do_not_collide_on_an_empty_slug(): void
    {
        $ids = app(BlogTagResolver::class)->resolveIds(['برمجة', 'تسويق', 'تصميم']);

        $this->assertCount(3, $ids);
        $this->assertSame(3, BlogTag::query()->distinct()->count('slug'));
        $this->assertSame(0, BlogTag::query()->where('slug', '')->count());
    }

    public function test_an_existing_tag_is_reused_case_insensitively(): void
    {
        BlogTag::query()->create(['name' => 'laravel', 'slug' => 'laravel', 'is_active' => true]);

        $ids = app(BlogTagResolver::class)->resolveIds(['Laravel', 'LARAVEL']);

        $this->assertCount(1, $ids);
        $this->assertSame(1, BlogTag::query()->count());
    }

    public function test_tags_fall_back_to_meta_keywords_when_the_model_suggests_none(): void
    {
        $user = $this->adminUser();

        $item = $this->runOneItem($user, [
            'title' => 'دليل تحسين محركات البحث',
            'content' => '<h2>مقدمة</h2><p>نص.</p>',
            'meta_keywords' => 'سيو، تسويق رقمي, محتوى',
        ]);

        $post = BlogPost::find($item->blog_post_id);

        $this->assertCount(3, $post->tags);
    }

    public function test_tags_are_skipped_when_the_batch_opted_out(): void
    {
        $user = $this->adminUser();

        $item = $this->runOneItem($user, [
            'title' => 'دليل تحسين محركات البحث',
            'content' => '<h2>مقدمة</h2><p>نص.</p>',
            'tags' => ['برمجة', 'تسويق'],
        ], generateTags: false);

        $post = BlogPost::find($item->blog_post_id);

        $this->assertCount(0, $post->tags);
        $this->assertSame(0, BlogTag::query()->count());
    }

    public function test_a_stray_ai_key_does_not_break_the_save(): void
    {
        $user = $this->adminUser();

        $item = $this->runOneItem($user, [
            'title' => 'دليل تحسين محركات البحث',
            'content' => '<h2>مقدمة</h2><p>نص.</p>',
            // Neither is a blog_posts column; the fillable filter must drop them.
            'reading_time' => 99,
            'some_future_field' => 'x',
        ]);

        $this->assertSame(BlogAiBatchItem::STATUS_COMPLETED, $item->status);
        $this->assertNotNull($item->blog_post_id);
    }

    public function test_a_failed_generation_closes_the_item_without_saving_a_post(): void
    {
        $user = $this->adminUser();

        Queue::fake();

        $batch = BlogAiBatch::query()->create([
            'user_id' => $user->id,
            'status' => BlogAiBatch::STATUS_QUEUED,
            'total' => 1,
            'settings' => ['category_id' => $this->category()->id, 'content_length' => 'long'],
        ]);

        BlogAiBatchItem::query()->create([
            'batch_id' => $batch->id,
            'position' => 0,
            'topic' => 'موضوع',
            'status' => BlogAiBatchItem::STATUS_PENDING,
        ]);

        $this->mock(BlogAiPipelineService::class, function ($mock) {
            $mock->shouldReceive('run')->once()->andReturnUsing(
                function (BlogAiGeneration $generation) {
                    BlogAiSection::query()->create([
                        'generation_id' => $generation->id,
                        'position' => 0,
                        'heading' => 'قسم',
                        'status' => BlogAiSection::STATUS_FAILED,
                    ]);
                    $generation->markPaused('توقّف التوليد.');
                }
            );
        });

        app(BlogAiBatchRunner::class)->processNext($batch->id);

        $item = $batch->items()->first();

        $this->assertSame(BlogAiBatchItem::STATUS_FAILED, $item->status);
        $this->assertNull($item->blog_post_id);
        $this->assertSame(0, BlogPost::query()->count());
        $this->assertNotEmpty($item->error_message);
    }
}
