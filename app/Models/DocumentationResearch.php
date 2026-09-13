<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A "بحث" — a named group of documentation pages inside one category, e.g.
 * "الدوال" under "php". Purely an admin-side organisation layer: it is not part
 * of the public URL, which is still built from the page parent_id chain.
 */
class DocumentationResearch extends Model
{
    use SoftDeletes;

    /**
     * Eloquent would guess "documentation_research" here — Laravel's pluraliser
     * treats "Research" as uncountable — so the table name has to be explicit.
     */
    protected $table = 'documentation_researches';

    protected $fillable = [
        'documentation_category_id',
        'name',
        'slug',
        'description',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentationCategory::class, 'documentation_category_id');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(DocumentationPage::class, 'documentation_research_id')
            ->orderBy('research_sort_order')
            ->orderBy('title');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function publishedPagesCount(): int
    {
        return $this->pages()->published()->count();
    }

    public function draftPagesCount(): int
    {
        return $this->pages()->where('status', 'draft')->count();
    }
}
