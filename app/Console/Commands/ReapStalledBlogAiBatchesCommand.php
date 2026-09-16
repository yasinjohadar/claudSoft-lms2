<?php

namespace App\Console\Commands;

use App\Models\BlogAiBatch;
use App\Services\AiNew\BlogAiBatchRunner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Closes out blog AI batches whose queue worker died mid-topic.
 *
 * The admin pages reap on every poll, but only while somebody is watching.
 * A batch abandoned overnight would otherwise sit at "running" until its page
 * is opened again, which is exactly the case that leaves topics unreachable.
 */
class ReapStalledBlogAiBatchesCommand extends Command
{
    protected $signature = 'blog:reap-stalled-ai-batches';

    protected $description = 'تعليم مقالات دفعات المدونة العالقة كغير مكتملة بعد توقّف معالج الطابور';

    public function handle(BlogAiBatchRunner $runner): int
    {
        $batches = BlogAiBatch::query()
            ->whereNotIn('status', [
                BlogAiBatch::STATUS_COMPLETED,
                BlogAiBatch::STATUS_COMPLETED_WITH_ERRORS,
                BlogAiBatch::STATUS_CANCELLED,
            ])
            ->get();

        $healed = 0;

        foreach ($batches as $batch) {
            if ($runner->reapStalled($batch)) {
                $healed++;
                Log::warning('Reaped stalled blog AI batch', ['uuid' => $batch->uuid]);
            }
        }

        $this->info("فُحصت {$batches->count()} دفعة، وحُرِّرت {$healed} دفعة عالقة.");

        return self::SUCCESS;
    }
}
