<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentationPage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'documentation_category_id',
        'parent_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'sort_order',
        'status',
        'published_at',
        'meta_title',
        'meta_description',
        'is_indexable',
        'updated_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_indexable' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentationCategory::class, 'documentation_category_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('title');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published'
            && $this->published_at
            && $this->published_at->lte(now());
    }

    /**
     * مسار slug من الجذر إلى هذه الصفحة داخل القسم (مثل routing/route-parameters).
     */
    public function slugPathUnderCategory(): string
    {
        $slugs = [];
        $idsVisited = [];
        $page = $this;

        while ($page) {
            if (isset($idsVisited[$page->id])) {
                break;
            }
            $idsVisited[$page->id] = true;
            array_unshift($slugs, $page->slug);
            $page = $page->parent_id
                ? static::query()->find($page->parent_id)
                : null;
        }

        return implode('/', $slugs);
    }

    /**
     * حل مسار نشر مثل introduction أو routing/child من القسم.
     */
    public static function resolvePublishedFromCategoryPath(DocumentationCategory $category, string $path): ?self
    {
        $path = trim($path, '/');
        if ($path === '') {
            return null;
        }

        $segments = explode('/', $path);
        $parentId = null;
        $page = null;

        foreach ($segments as $segment) {
            $page = static::query()
                ->where('documentation_category_id', $category->id)
                ->where('parent_id', $parentId)
                ->where('slug', $segment)
                ->published()
                ->first();

            if (! $page) {
                return null;
            }

            $parentId = $page->id;
        }

        return $page;
    }
}
