<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\DocumentationCategory;
use App\Models\DocumentationPage;
use Illuminate\View\View;

class DocumentationController extends Controller
{
    public function index(): View
    {
        $categories = DocumentationCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('frontend.docs.index', compact('categories'));
    }

    public function show(string $categorySlug, ?string $pagePath = null): View
    {
        $category = DocumentationCategory::query()
            ->where('slug', $categorySlug)
            ->where('is_active', true)
            ->firstOrFail();

        $pagePath = $pagePath !== null ? trim($pagePath, '/') : '';

        $page = $pagePath !== ''
            ? DocumentationPage::resolvePublishedFromCategoryPath($category, $pagePath)
            : null;

        if ($pagePath !== '' && ! $page) {
            abort(404);
        }

        if ($page === null && $pagePath === '') {
            $page = DocumentationPage::query()
                ->where('documentation_category_id', $category->id)
                ->whereNull('parent_id')
                ->published()
                ->orderBy('sort_order')
                ->orderBy('title')
                ->first();
        }

        if (! $page) {
            abort(404);
        }

        return view('frontend.docs.show', [
            'category' => $category,
            'page' => $page,
        ]);
    }
}