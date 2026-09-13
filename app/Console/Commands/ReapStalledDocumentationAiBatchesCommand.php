<?php

namespace App\Console\Commands;

use App\Models\DocumentationAiBatch;
use App\Services\AiNew\DocumentationAiBatchRunner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Closes out documentation AI batches whose queue worker died mid-topic.
 *
 * The admin pages reap on every poll, but only while somebody is watching.
 * A batch abandoned overnight would otherwise sit at "running" until its page
 * is opened again, which is exactly the case that leaves topics unreachable.
 */
class ReapStalledDocumentationAiBatchesCommand extends Command
{
    protected $signature = 'docs:reap-stalled-ai-batches';

    protected $description = 'تعليم مواضيع دفعات التوثيق العالقة كغير مكتملة بعد توقّف معالج الطابور';

    public function handle(DocumentationAiBatchRunner $runner): int
    {
        $batches = DocumentationAiBatch::query()
            ->whereNotIn('status', [
                DocumentationAiBatch::STATUS_COMPLETED,
                DocumentationAiBatch::STATUS_COMPLETED_WITH_ERRORS,
                DocumentationAiBatch::STATUS_CANCELLED,
            ])
            ->get();

        $healed = 0;

        foreach ($batches as $batch) {
            if ($runner->reapStalled($batch)) {
                $healed++;
                Log::warning('Reaped stalled documentation AI batch', ['uuid' => $batch->uuid]);
            }
        }

        $this->info("فُحصت {$batches->count()} دفعة، وحُرِّرت {$healed} دفعة عالقة.");

        return self::SUCCESS;
    }
}
