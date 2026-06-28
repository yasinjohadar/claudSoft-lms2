<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class DocumentationCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'description', 'icon', 'kind', 'parent_id', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(DocumentationPage::class, 'documentation_category_id');
    }

    public function rootPages(): HasMany
    {
        return $this->pages()->whereNull('parent_id')->orderBy('sort_order')->orderBy('title');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeTechnology($query)
    {
        return $query->where('kind', 'technology');
    }

    /**
     * @param  iterable<int, DocumentationPage>  $pages
     * @return list<array{page: DocumentationPage, children: list<mixed>}>
     */
    public function buildPageTree(iterable $pages): array
    {
        $collection = $pages instanceof Collection
            ? $pages
            : collect($pages);

        $byParent = $collection->groupBy(fn (DocumentationPage $page) => $page->parent_id ?? 0);

        $build = function (int $parentId) use (&$build, $byParent): array {
            return $byParent->get($parentId, collect())
                ->sortBy(fn (DocumentationPage $page) => sprintf('%05d-%s', $page->sort_order, $page->title))
                ->map(fn (DocumentationPage $page) => [
                    'page' => $page,
                    'children' => $build($page->id),
                ])
                ->values()
                ->all();
        };

        return $build(0);
    }

    public function publicUrl(): string
    {
        return route('frontend.docs.category', ['categorySlug' => $this->slug]);
    }

    public function isTechnology(): bool
    {
        return $this->kind === 'technology';
    }

    /**
     * @return list<array{page: DocumentationPage, children: list<mixed>}>
     */
    public function publishedPageTree(): array
    {
        $pages = $this->pages()->published()->ordered()->get();
        $byParent = $pages->groupBy(fn (DocumentationPage $page) => $page->parent_id ?? 0);

        $build = function (int $parentId) use (&$build, $byParent): array {
            return $byParent->get($parentId, collect())
                ->map(fn (DocumentationPage $page) => [
                    'page' => $page,
                    'children' => $build($page->id),
                ])
                ->values()
                ->all();
        };

        return $build(0);
    }

    public function publishedPagesCount(): int
    {
        return $this->pages()->published()->count();
    }
}
