<?php

namespace Tests\Feature\Admin;

use App\Jobs\ProcessDocumentationAiBatchJob;
use App\Models\DocumentationAiBatch;
use App\Models\DocumentationAiBatchItem;
use App\Models\DocumentationAiGeneration;
use App\Models\DocumentationAiSection;
use App\Models\DocumentationCategory;
use App\Models\User;
use App\Services\AiNew\DocumentationAiBatchRunner;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Covers the four things that used to go wrong on the batch page: topics
 * vanishing on refresh, topics stuck "running" after a worker died, a resume
 * that reported nothing and killed the polling loop, and section progress
 * being invisible on a stopped topic.
 */
class DocumentationAiBatchResumeTest extends TestCase
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('Documentation AI feature tests require MySQL (SQLite migrations incompatible).');
        }
    }

    private function adminUser(): User
    {
        $role = Role::findOrCreate('admin', 'web');
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function category(): DocumentationCategory
    {
        return DocumentationCategory::query()->create([
            'name' => 'PHP',
            'slug' => 'php',
            'is_active' => true,
        ]);
    }

    /**
     * A batch with one topic that stopped after $done of $planned sections.
     *
     * @return array{0: DocumentationAiBatch, 1: DocumentationAiBatchItem, 2: DocumentationAiGeneration}
     */
    private function stoppedBatch(
        User $user,
        string $generationStatus = DocumentationAiGeneration::STATUS_PAUSED,
        string $itemStatus = DocumentationAiBatchItem::STATUS_FAILED,
        int $done = 12,
        int $planned = 13,
        ?CarbonInterface $heartbeat = null,
    ): array {
        $batch = DocumentationAiBatch::query()->create([
            'user_id' => $user->id,
            'status' => $itemStatus === DocumentationAiBatchItem::STATUS_RUNNING
                ? DocumentationAiBatch::STATUS_RUNNING
                : DocumentationAiBatch::STATUS_COMPLETED_WITH_ERRORS,
            'total' => 1,
            'failed' => $itemStatus === DocumentationAiBatchItem::STATUS_FAILED ? 1 : 0,
            'settings' => [
                'documentation_category_id' => $this->category()->id,
                'content_length' => 'long',
                'status' => 'draft',
            ],
        ]);

        $generation = DocumentationAiGeneration::query()->create([
            'user_id' => $user->id,
            'operation' => DocumentationAiGeneration::OPERATION_GENERATE,
            'status' => $generationStatus,
            'progress' => 80,
            'stage' => 'paused',
            'stage_label' => 'متوقف',
            'payload' => ['topic' => 'الدوال في PHP'],
            'started_at' => now()->subHour(),
            'heartbeat_at' => $heartbeat,
        ]);

        for ($i = 0; $i < $planned; $i++) {
            DocumentationAiSection::query()->create([
                'generation_id' => $generation->id,
                'position' => $i,
                'heading' => 'القسم '.($i + 1),
                'status' => $i < $done
                    ? DocumentationAiSection::STATUS_DONE
                    : DocumentationAiSection::STATUS_FAILED,
                'html' => $i < $done ? '<h2>القسم '.($i + 1).'</h2><p>محتوى</p>' : null,
            ]);
        }

        $item = DocumentationAiBatchItem::query()->create([
            'batch_id' => $batch->id,
            'position' => 0,
            'topic' => 'الدوال في PHP',
            'status' => $itemStatus,
            'documentation_ai_generation_id' => $generation->id,
            'error_message' => 'تم توليد '.$done.' من '.$planned.' قسماً وحُفظت.',
        ]);

        return [$batch, $item, $generation];
    }

    public function test_status_payload_exposes_section_progress_for_a_stopped_topic(): void
    {
        $user = $this->adminUser();
        [$batch] = $this->stoppedBatch($user);

        $response = $this->actingAs($user)
            ->getJson(route('admin.docs.ai-pages.batch.status', $batch->uuid))
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
            generationStatus: DocumentationAiGeneration::STATUS_RUNNING,
            itemStatus: DocumentationAiBatchItem::STATUS_RUNNING,
            heartbeat: now()->subMinutes(30),
        );

        $this->actingAs($user)
            ->getJson(route('admin.docs.ai-pages.batch.status', $batch->uuid))
            ->assertOk()
            ->assertJsonPath('batch.items.0.status', DocumentationAiBatchItem::STATUS_FAILED)
            ->assertJsonPath('batch.items.0.resumable', true)
            ->assertJsonPath('batch.finished', true);

        // Finished sections survive, so continuing does not start over.
        $this->assertSame(DocumentationAiGeneration::STATUS_PAUSED, $generation->fresh()->status);
        $this->assertSame(DocumentationAiBatchItem::STATUS_FAILED, $item->fresh()->status);
    }

    public function test_reaping_twice_does_not_double_count_the_failure(): void
    {
        $user = $this->adminUser();
        [$batch] = $this->stoppedBatch(
            $user,
            generationStatus: DocumentationAiGeneration::STATUS_RUNNING,
            itemStatus: DocumentationAiBatchItem::STATUS_RUNNING,
            heartbeat: now()->subMinutes(30),
        );

        $runner = app(DocumentationAiBatchRunner::class);

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
            ->postJson(route('admin.docs.ai-pages.batch.items.resume', [
                'uuid' => $batch->uuid,
                'item' => $item->id,
            ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            // These three are what keep the page's polling loop alive.
            ->assertJsonPath('batch.finished', false)
            ->assertJsonPath('batch.any_running', true)
            ->assertJsonPath('batch.items.0.status', DocumentationAiBatchItem::STATUS_RESUME_QUEUED);
    }

    public function test_resuming_twice_is_rejected_and_queues_only_one_job(): void
    {
        Queue::fake();

        $user = $this->adminUser();
        [$batch, $item] = $this->stoppedBatch($user);

        $url = route('admin.docs.ai-pages.batch.items.resume', [
            'uuid' => $batch->uuid,
            'item' => $item->id,
        ]);

        $this->actingAs($user)->postJson($url)->assertOk();
        $this->actingAs($user)->postJson($url)->assertStatus(422);

        Queue::assertPushed(ProcessDocumentationAiBatchJob::class, 1);
    }

    public function test_a_queued_resume_still_counts_as_resumable_so_the_driver_reuses_its_sections(): void
    {
        Queue::fake();

        $user = $this->adminUser();
        [, $item, $generation] = $this->stoppedBatch($user);

        app(DocumentationAiBatchRunner::class)->queueResume($item->fresh());

        // The regression this guards: flipping the generation to "queued" made
        // isResumable() false, so processNext() built a brand new generation and
        // silently threw away the 12 sections that were already written.
        $this->assertTrue($generation->fresh()->isResumable());
        $this->assertSame('resume_queued', $generation->fresh()->stage);
    }

    public function test_resume_all_queues_every_incomplete_topic(): void
    {
        Queue::fake();

        $user = $this->adminUser();
        [$batch] = $this->stoppedBatch($user);

        $this->actingAs($user)
            ->postJson(route('admin.docs.ai-pages.batch.resume-all', $batch->uuid))
            ->assertOk()
            ->assertJsonPath('batch.resume_queued', 1);
    }

    public function test_the_create_page_restores_the_last_batch_after_a_refresh(): void
    {
        $user = $this->adminUser();
        [$batch] = $this->stoppedBatch($user);

        $this->actingAs($user)
            ->get(route('admin.docs.ai-pages.batch.create'))
            ->assertOk()
            ->assertViewHas('initialBatch', fn ($initial) => $initial['uuid'] === $batch->uuid)
            ->assertViewHas('initialSettings', fn ($s) => $s['content_length'] === 'long');
    }

    public function test_another_users_batch_is_never_restored_or_readable(): void
    {
        $user = $this->adminUser();
        $other = $this->adminUser();

        [$mine] = $this->stoppedBatch($user);
        [$theirs] = $this->stoppedBatch($other);

        $this->actingAs($user)
            ->get(route('admin.docs.ai-pages.batch.create', ['batch' => $theirs->uuid]))
            ->assertOk()
            ->assertViewHas('initialBatch', fn ($initial) => $initial['uuid'] === $mine->uuid);

        $this->actingAs($user)
            ->getJson(route('admin.docs.ai-pages.batch.status', $theirs->uuid))
            ->assertNotFound();
    }

    public function test_history_page_lists_only_the_current_users_batches(): void
    {
        $user = $this->adminUser();
        $other = $this->adminUser();

        [$mine] = $this->stoppedBatch($user);
        [$theirs] = $this->stoppedBatch($other);

        $this->actingAs($user)
            ->get(route('admin.docs.ai-pages.batch.index'))
            ->assertOk()
            ->assertSee($mine->uuid)
            ->assertDontSee($theirs->uuid);
    }

    public function test_a_poll_costs_a_constant_number_of_queries_regardless_of_topic_count(): void
    {
        $user = $this->adminUser();
        [$batch] = $this->stoppedBatch($user);

        // Add more stopped topics; the payload must not start querying per row.
        for ($n = 1; $n <= 5; $n++) {
            $generation = DocumentationAiGeneration::query()->create([
                'user_id' => $user->id,
                'operation' => DocumentationAiGeneration::OPERATION_GENERATE,
                'status' => DocumentationAiGeneration::STATUS_PAUSED,
                'progress' => 50,
                'stage' => 'paused',
                'payload' => ['topic' => 'موضوع '.$n],
            ]);
            DocumentationAiSection::query()->create([
                'generation_id' => $generation->id,
                'position' => 0,
                'heading' => 'ق',
                'status' => DocumentationAiSection::STATUS_FAILED,
            ]);
            DocumentationAiBatchItem::query()->create([
                'batch_id' => $batch->id,
                'position' => $n,
                'topic' => 'موضوع '.$n,
                'status' => DocumentationAiBatchItem::STATUS_FAILED,
                'documentation_ai_generation_id' => $generation->id,
            ]);
        }

        $this->actingAs($user);
        DB::enableQueryLog();
        $this->getJson(route('admin.docs.ai-pages.batch.status', $batch->uuid))->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(
            12,
            $count,
            "A poll of 6 stopped topics took {$count} queries — the per-row N+1 is back."
        );
    }
}
