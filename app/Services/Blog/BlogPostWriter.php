<?php

namespace App\Services\Blog;

use App\Models\BlogPost;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The one place a blog post is written from a payload.
 *
 * The same ~90 lines (slug cleaning, author, category remap, SEO defaults,
 * reading time, tag sync, sitemap busting) were copy-pasted across the post
 * controllers; batch generation needs to run them from a queue worker, where
 * Auth::id() is null, so they live here instead.
 */
class BlogPostWriter
{
    public function __construct(private BlogTagResolver $tags) {}

    /**
     * @param  array<string, mixed>  $data  post fields, plus optional
     *                                      `tag_ids` (list<int>) and `tag_names` (list<string>)
     * @param  int|null  $authorId  explicit author — required on a queue worker
     */
    public function create(array $data, ?int $authorId = null): BlogPost
    {
        return DB::transaction(function () use ($data, $authorId) {
            $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, (string) ($data['title'] ?? ''));
            $data['author_id'] = $authorId ?? Auth::id();

            if (isset($data['category_id']) && ! isset($data['blog_category_id'])) {
                $data['blog_category_id'] = $data['category_id'];
            }
            unset($data['category_id']);

            $data = $this->applyDefaults($data);

            $tagIds = $this->collectTagIds($data);
            unset($data['tag_ids'], $data['tag_names']);

            // An unknown key from an AI payload must never be a fatal mass-assignment.
            $post = BlogPost::create(Arr::only($data, (new BlogPost)->getFillable()));

            if ($tagIds !== []) {
                $post->tags()->sync($tagIds);
                $this->tags->refreshCounts($tagIds);
            }

            $post->calculateReadingTime();
            $post->category?->updatePostsCount();

            if ($post->status === 'published') {
                Cache::forget('sitemap.xml');
            }

            return $post;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyDefaults(array $data): array
    {
        $status = $data['status'] ?? 'draft';
        $data['status'] = $status;

        if ($status === 'published') {
            $publishedAt = $data['published_at'] ?? null;
            if (empty($publishedAt) || strtotime((string) $publishedAt) > time()) {
                $data['published_at'] = now();
            }
        }

        $isEnglish = ($data['language'] ?? 'ar') === 'en';

        $data['schema_type'] ??= 'Article';
        $data['og_type'] ??= 'article';
        $data['og_locale'] ??= $isEnglish ? 'en_US' : 'ar_SA';
        $data['twitter_card'] ??= 'summary_large_image';
        $data['is_indexable'] ??= true;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<int>
     */
    private function collectTagIds(array $data): array
    {
        $ids = array_map('intval', array_filter((array) ($data['tag_ids'] ?? []), 'is_numeric'));
        $names = (array) ($data['tag_names'] ?? []);

        if ($names !== []) {
            $ids = array_merge($ids, $this->tags->resolveIds(array_values($names)));
        }

        return array_values(array_unique($ids));
    }

    private function uniqueSlug(?string $requested, string $title): string
    {
        $slug = trim((string) ($requested ?: Str::slug($title, '-', 'ar')));
        $slug = (string) preg_replace('/\s+/u', '-', $slug);
        $slug = (string) preg_replace('/[^\p{Arabic}a-zA-Z0-9-]/u', '', $slug);
        $slug = trim((string) preg_replace('/-+/', '-', $slug), '-');

        if ($slug === '') {
            $slug = 'post-'.time();
        }

        $base = $slug;
        $counter = 1;
        while (BlogPost::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
