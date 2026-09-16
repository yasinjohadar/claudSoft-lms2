<?php

namespace App\Services\Blog;

use App\Models\BlogTag;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Log;

/**
 * Turn AI-suggested tag names into blog_tags ids, creating what is missing.
 *
 * The blog module has no "find or create a tag by name" path: every existing
 * caller validates `exists:blog_tags,id`. Batch generation needs one, and it
 * has to work for Arabic names — see uniqueSlug() for why that is not trivial.
 */
class BlogTagResolver
{
    /** Longest tag name we keep; anything past this is a sentence, not a tag. */
    private const MAX_NAME_LENGTH = 60;

    /**
     * @param  list<string>  $names
     * @return list<int>
     */
    public function resolveIds(array $names, int $limit = 6): array
    {
        $ids = [];
        $seen = [];

        foreach ($names as $raw) {
            if (count($ids) >= $limit) {
                break;
            }

            $name = $this->normalizeName($raw);
            if ($name === null) {
                continue;
            }

            $key = mb_strtolower($name);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $tag = $this->findOrCreate($name);
            if ($tag !== null) {
                $ids[] = $tag->id;
            }
        }

        return $ids;
    }

    /**
     * Keep blog_tags.posts_count honest after a sync.
     *
     * @param  list<int>  $tagIds
     */
    public function refreshCounts(array $tagIds): void
    {
        if ($tagIds === []) {
            return;
        }

        BlogTag::query()->whereIn('id', $tagIds)->get()
            ->each(fn (BlogTag $tag) => $tag->updatePostsCount());
    }

    private function normalizeName(mixed $raw): ?string
    {
        if (! is_scalar($raw)) {
            return null;
        }

        $name = trim((string) $raw);
        $name = ltrim($name, "#\u{FF03}");
        $name = trim((string) preg_replace('/\s+/u', ' ', $name));
        $name = mb_substr($name, 0, self::MAX_NAME_LENGTH);

        return mb_strlen($name) >= 2 ? $name : null;
    }

    private function findOrCreate(string $name): ?BlogTag
    {
        $existing = $this->findByName($name);
        if ($existing !== null) {
            return $existing;
        }

        try {
            // The slug is passed explicitly and is never empty, so BlogTag's own
            // Str::slug() fallbacks stay dormant.
            return BlogTag::query()->create([
                'slug' => $this->uniqueSlug($name),
                'name' => $name,
                'is_active' => true,
                'is_indexable' => true,
                'posts_count' => 0,
            ]);
        } catch (UniqueConstraintViolationException $e) {
            // Two batches racing on the same tag name must not fail an article save.
            $raced = $this->findByName($name);
            if ($raced !== null) {
                return $raced;
            }

            Log::warning('BlogTagResolver could not create tag', [
                'name' => $name,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function findByName(string $name): ?BlogTag
    {
        return BlogTag::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();
    }

    /**
     * Arabic-safe slug.
     *
     * Str::slug() strips Arabic entirely and returns '', and blog_tags.slug is
     * unique — so two auto-created Arabic tags would collide on '' and take the
     * whole article save down with them. This mirrors BlogPost::boot()'s routine.
     */
    private function uniqueSlug(string $name): string
    {
        $slug = (string) preg_replace('/\s+/u', '-', trim($name));
        $slug = (string) preg_replace('/[^\p{Arabic}a-zA-Z0-9-]/u', '', $slug);
        $slug = trim((string) preg_replace('/-+/', '-', $slug), '-');

        if ($slug === '') {
            $slug = 'tag-'.time();
        }

        $base = $slug;
        $counter = 1;
        while (BlogTag::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
