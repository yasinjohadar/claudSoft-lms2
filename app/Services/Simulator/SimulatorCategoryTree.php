<?php

namespace App\Services\Simulator;

use App\Models\SimulatorCategory;
use Illuminate\Support\Collection;

class SimulatorCategoryTree
{
    /**
     * @return array<int, string> id => indented label
     */
    public static function optionsForSelect(?int $excludeId = null, bool $activeOnly = false): array
    {
        $query = SimulatorCategory::query()->with('children')->roots()->ordered();
        if ($activeOnly) {
            $query->active();
        }

        $excludeIds = $excludeId ? static::excludeIds($excludeId) : [];
        $options = [];

        foreach ($query->get() as $root) {
            static::appendOptions($root, $options, 0, $excludeIds);
        }

        return $options;
    }

    /**
     * @return array<int, string>
     */
    public static function excludeIds(int $categoryId): array
    {
        $category = SimulatorCategory::query()->find($categoryId);
        if (! $category) {
            return [$categoryId];
        }

        return array_merge([$categoryId], $category->descendantIds());
    }

    /**
     * @return array<int>
     */
    public static function selfAndDescendantIds(int $categoryId): array
    {
        $category = SimulatorCategory::query()->find($categoryId);
        if (! $category) {
            return [$categoryId];
        }

        return array_merge([$categoryId], $category->descendantIds());
    }

    /**
     * @param  array<int, string>  $options
     * @param  array<int>  $excludeIds
     */
    protected static function appendOptions(
        SimulatorCategory $category,
        array &$options,
        int $depth,
        array $excludeIds,
    ): void {
        if (in_array($category->id, $excludeIds, true)) {
            return;
        }

        $prefix = $depth > 0 ? str_repeat('— ', $depth) : '';
        $options[$category->id] = $prefix.$category->name;

        $children = $category->relationLoaded('children')
            ? $category->children
            : $category->children()->ordered()->get();

        foreach ($children as $child) {
            static::appendOptions($child, $options, $depth + 1, $excludeIds);
        }
    }

    public static function flatList(): Collection
    {
        $all = SimulatorCategory::query()
            ->withCount(['children', 'simulators'])
            ->ordered()
            ->get();

        $flat = collect();

        foreach ($all->whereNull('parent_id') as $root) {
            static::flattenNode($root, $all, $flat, 0);
        }

        $included = $flat->pluck('id');
        foreach ($all->whereNotIn('id', $included) as $orphan) {
            $orphan->tree_depth = 0;
            $flat->push($orphan);
        }

        return $flat;
    }

    protected static function buildPath(SimulatorCategory $category, Collection $all): string
    {
        $parts = [$category->name];
        $parentId = $category->parent_id;

        while ($parentId) {
            $parent = $all->get($parentId);
            if (! $parent) {
                break;
            }
            array_unshift($parts, $parent->name);
            $parentId = $parent->parent_id;
        }

        return implode(' › ', $parts);
    }

    protected static function flattenNode(
        SimulatorCategory $category,
        Collection $all,
        Collection $flat,
        int $depth,
    ): void {
        $category->tree_depth = $depth;
        $category->display_path = static::buildPath($category, $all);
        $flat->push($category);

        foreach ($all->where('parent_id', $category->id)->sortBy('sort_order') as $child) {
            static::flattenNode($child, $all, $flat, $depth + 1);
        }
    }
}
