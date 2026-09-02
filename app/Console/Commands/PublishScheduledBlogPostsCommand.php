<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class PublishScheduledBlogPostsCommand extends Command
{
    protected $signature = 'blog:publish-scheduled';

    protected $description = 'Publish blog posts whose scheduled_at time has passed';

    public function handle(): int
    {
        $due = BlogPost::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->get();

        foreach ($due as $post) {
            $post->status = 'published';
            if (! $post->published_at) {
                $post->published_at = $post->scheduled_at ?? now();
            }
            // save(), not a mass update — the model's `updating` boot hook
            // sets schema_modified_time, which a query-builder update() bypasses.
            $post->save();
        }

        if ($due->isNotEmpty()) {
            Cache::forget('sitemap.xml');
        }

        $this->info("Published {$due->count()} scheduled post(s).");

        return self::SUCCESS;
    }
}
