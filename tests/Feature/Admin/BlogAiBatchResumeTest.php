<?php

namespace Tests\Feature\Admin;

use App\Jobs\ProcessBlogAiBatchJob;
use App\Models\BlogAiBatch;
use App\Models\BlogAiBatchItem;
use App\Models\BlogAiGeneration;
use App\Models\BlogAiSection;
use App\Models\BlogCategory;
use App\Models\User;
use App\Services\AiNew\BlogAiBatchRunner;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Covers the four things that used to go wrong on the documentation batch page,
 * now guarded for the blog twin: topics vanishing on refresh, topics stuck
 * "running" after a worker died, a resume that reported nothing and killed the
 * polling loop, and section progress being invisible on a stopped article.
 */
class BlogAiBatchResumeTest extends TestCase
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
     * A batch with one topic that stopped after $done of $planned sections.
     *
     * @return array{0: BlogAiBatch, 1: BlogAiBatchItem, 2: BlogAiGeneration}
     */
    private function stoppedBatch(
        User $user,
        string $generationStatus = BlogAiGeneration::STATUS_PAUSED,
        string $itemStatus = BlogAiBatchItem::STATUS_FAILED,
        int $done = 12,
        int $planned = 13,
        ?CarbonInterface $heartbeat = null,
    ): array {
        $batch = BlogAiBatch::query()->create([
            'user_id' => $user->id,
            'status' => $itemStatus === BlogAiBatchItem::STATUS_RUNNING
                ? BlogAiBatch::STATUS_RUNNING
                : BlogAiBatch::STATUS_COMPLETED_WITH_ERRORS,
            'total' => 1,
            'failed' => $itemStatus === BlogAiBatchItem::STATUS_FAILED ? 1 : 0,
            'settings' => [
                'category_id' => $this->category()->id,
                'content_length' => 'very_long',
            ],
        ]);

        $generation = BlogAiGeneration::query()->create([
            'user_id' => $user->id,
            'operation' => BlogAiGeneration::OPERATION_GENERATE,
            'status' => $generationStatus,
            'progress' => 80,
            'stage' => 'paused',
            'stage_label' => 'متوقف',
            'payload' => ['topic' => 'أساسيات تحسين محركات البحث'],
            'started_at' => now()->subHour(),
            'heartbeat_at' => $heartbeat,
        ]);

        for ($i = 0; $i < $planned; $i++) {
            BlogAiSection::query()->create([
                'generation_id' => $generation->id,
                'position' => $i,
                'heading' => 'القسم '.($i + 1),
                'status' => $i < $done
                    ? BlogAiSection::STATUS_DONE
                    : BlogAiSection::STATUS_FAILED,
                'html' => $i < $done ? '<h2>القسم '.($i + 1).'</h2><p>محتوى</p>' : null,
            ]);
        }

        $item = BlogAiBatchItem::query()->create([
            'batch_id' => $batch->id,
            'position' => 0,
            'topic' => 'أساسيات تحسين محركات البحث',
            'status' => $itemStatus,
            'blog_ai_generation_id' => $generation->id,
            'error_message' => 'تم توليد '.$done.' من '.$planned.' قسماً وحُفظت.',
        ]);

        return [$batch, $item, $generation];
    }

    public function test_status_payload_exposes_section_progress_for_a_stopped_article(): void
    {
        $user = $this->adminUser();
        [$batch] = $this->stoppedBatch($user);

        $response = $this->actingAs($user)
            ->getJson(route('admin.blog.ai-posts.batch.status', $batch->uuid))
            ->assertOk();

        $item = $response->json('batch.items.0');

        $this->assertSame(12, $item['sections']['done']);
        $this->assertSame(13, $item['sections']['planned']);
        $this->assertSame(92, $item['sections_progress']);
        $this->assertTrue($item['resumable']);
        $this->assertTrue($item['is_incomplete']);
        $this->assertStringContainsString('12 من 13', $item['progress_hint']);
        $this->assertSame(1, $response->json('batch.incomplete'));
    }

    public function test_a_dead_worker_leaves_the_topic_resumable_instead_of_stuck_running(): void
    {
        $user = $this->adminUser();
        [$batch, $item, $generation] = $this->stoppedBatch(
            $user,
            generationStatus: BlogAiGeneration::STATUS_RUNNING,
            itemStatus: BlogAiBatchItem::STATUS_RUNNING,
            heartbeat: now()->subMinutes(30),
        );

        $this->actingAs($user)
            ->getJson(route('admin.blog.ai-posts.batch.status', $batch->uuid))
            ->assertOk()
            ->assertJsonPath('batch.items.0.status', BlogAiBatchItem::STATUS_FAILED)
            ->assertJsonPath('batch.items.0.resumable', true)
            ->assertJsonPath('batch.finished', true);

        // Finished sections survive, so continuing does not start over.
        $this->assertSame(BlogAiGeneration::STATUS_PAUSED, $generation->fresh()->status);
        $this->assertSame(BlogAiBatchItem::STATUS_FAILED, $item->fresh()->status);
    }

    public function test_reaping_twice_does_not_double_count_the_failure(): void
    {
        $user = $this->adminUser();
        [$batch] = $this->stoppedBatch(
            $user,
            generationStatus: BlogAiGeneration::STATUS_RUNNING,
            itemStatus: BlogAiBatchItem::STATUS_RUNNING,
            heartbeat: now()->subMinutes(30),
        );

        $runner = app(BlogAiBatchRunner::class);

        $this->assertTrue($runner->reapStalled($batch->fresh()));
        $this->assertSame(1, (int) $batch->fresh()->failed);

        $this->assertFalse($runner->reapStalled($batch->fresh()));
        $this->assertSame(1, (int) $batch->fresh()->failed);
    }

    public function test_resume_reopens_the_batch_in_the_same_response_so_polling_continues(): void
    {
        Queue::fake();

        $user = $this->adminUser();
        [$batch, $item] = $this->stoppedBatch($user);

        $this->actingAs($user)
            ->postJson(route('admin.blog.ai-posts.batch.items.resume', [
                'uuid' => $batch->uuid,
                'item' => $item->id,
            ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            // These three are what keep the page's polling loop alive.
            ->assertJsonPath('batch.finished', false)
            ->assertJsonPath('batch.any_running', true)
            ->assertJsonPath('batch.items.0.status', BlogAiBatchItem::STATUS_RESUME_QUEUED);
    }

    public function test_resuming_twice_is_rejected_and_queues_only_one_job(): void
    {
        Queue::fake();

        $user = $this->adminUser();
        [$batch, $item] = $this->stoppedBatch($user);

        $url = route('admin.blog.ai-posts.batch.items.resume', [
            'uuid' => $batch->uuid,
            'item' => $item->id,
        ]);

        $this->actingAs($user)->postJson($url)->assertOk();
        $this->actingAs($user)->postJson($url)->assertStatus(422);

        Queue::assertPushed(ProcessBlogAiBatchJob::class, 1);
    }

    public function test_a_queued_resume_still_counts_as_resumable_so_the_driver_reuses_its_sections(): void
    {
        Queue::fake();

        $user = $this->adminUser();
        [, $item, $generation] = $this->stoppedBatch($user);

        app(BlogAiBatchRunner::class)->queueResume($item->fresh());

        // The regression this guards: flipping the generation to "queued" made
        // isResumable() false, so processNext() built a brand new generation and
        // silently threw away the 12 sections that were already written.
        $this->assertTrue($generation->fresh()->isResumable());
        $this->assertSame('resume_queued', $generation->fresh()->stage);
    }

    public function test_resume_all_queues_every_incomplete_article(): void
    {
        Queue::fake();

        $user = $this->adminUser();
        [$batch] = $this->stoppedBatch($user);

        $this->actingAs($user)
            ->postJson(route('admin.blog.ai-posts.batch.resume-all', $batch->uuid))
            ->assertOk()
            ->assertJsonPath('batch.resume_queued', 1);
    }

    public function test_the_create_page_restores_the_last_batch_after_a_refresh(): void
    {
        $user = $this->adminUser();
        [$batch] = $this->stoppedBatch($user);

        $this->actingAs($user)
            ->get(route('admin.blog.ai-posts.batch.create'))
            ->assertOk()
            ->assertViewHas('initialBatch', fn ($initial) => $initial['uuid'] === $batch->uuid)
            ->assertViewHas('initialSettings', fn ($s) => $s['content_length'] === 'very_long');
    }

    public function test_another_users_batch_is_never_restored_or_readable(): void
    {
        $user = $this->adminUser();
        $other = $this->adminUser();

        [$mine] = $this->stoppedBatch($user);
        [$theirs] = $this->stoppedBatch($other);

        $this->actingAs($user)
            ->get(route('admin.blog.ai-posts.batch.create', ['batch' => $theirs->uuid]))
            ->assertOk()
            ->assertViewHas('initialBatch', fn ($initial) => $initial['uuid'] === $mine->uuid);

        $this->actingAs($user)
            ->getJson(route('admin.blog.ai-posts.batch.status', $theirs->uuid))
            ->assertNotFound();
    }

    public function test_history_page_lists_only_the_current_users_batches(): void
    {
        $user = $this->adminUser();
        $other = $this->adminUser();

        [$mine] = $this->stoppedBatch($user);
        [$theirs] = $this->stoppedBatch($other);

        $this->actingAs($user)
            ->get(route('admin.blog.ai-posts.batch.index'))
            ->assertOk()
            ->assertSee($mine->uuid)
            ->assertDontSee($theirs->uuid);
    }

    public function test_a_poll_costs_a_constant_number_of_queries_regardless_of_article_count(): void
    {
        $user = $this->adminUser();
        [$batch] = $this->stoppedBatch($user);

        // Add more stopped articles; the payload must not start querying per row.
        for ($n = 1; $n <= 5; $n++) {
            $generation = BlogAiGeneration::query()->create([
                'user_id' => $user->id,
                'operation' => BlogAiGeneration::OPERATION_GENERATE,
                'status' => BlogAiGeneration::STATUS_PAUSED,
                'progress' => 50,
                'stage' => 'paused',
                'payload' => ['topic' => 'موضوع '.$n],
            ]);
            BlogAiSection::query()->create([
                'generation_id' => $generation->id,
                'position' => 0,
                'heading' => 'ق',
                'status' => BlogAiSection::STATUS_FAILED,
            ]);
            BlogAiBatchItem::query()->create([
                'batch_id' => $batch->id,
                'position' => $n,
                'topic' => 'موضوع '.$n,
                'status' => BlogAiBatchItem::STATUS_FAILED,
                'blog_ai_generation_id' => $generation->id,
            ]);
        }

        $this->actingAs($user);
        DB::enableQueryLog();
        $this->getJson(route('admin.blog.ai-posts.batch.status', $batch->uuid))->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(
            12,
            $count,
            "A poll of 6 stopped articles took {$count} queries — the per-row N+1 is back."
        );
    }
}
