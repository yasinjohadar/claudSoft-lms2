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
            ->active()
            ->ordered()
            ->withCount(['pages as published_pages_count' => fn ($query) => $query->published()])
            ->get();

        $technologies = $categories->where('kind', 'technology')->values();
        $sections = $categories->where('kind', '!=', 'technology')->values();

        return view('frontend.docs.index', compact('technologies', 'sections', 'categories'));
    }

    public function category(string $categorySlug): View
    {
        $category = DocumentationCategory::query()
            ->where('slug', $categorySlug)
            ->active()
            ->firstOrFail();

        $pageTree = $category->publishedPageTree();
        $pagesCount = $category->publishedPagesCount();

        return view('frontend.docs.category', compact('category', 'pageTree', 'pagesCount'));
    }

    public function show(string $categorySlug, string $pagePath): View
    {
        $category = DocumentationCategory::query()
            ->where('slug', $categorySlug)
            ->active()
            ->firstOrFail();

        $pagePath = trim($pagePath, '/');

        if ($pagePath === '') {
            abort(404);
        }

        $page = DocumentationPage::resolvePublishedFromCategoryPath($category, $pagePath);

        if (! $page) {
            abort(404);
        }

        return view('frontend.docs.show', [
            'category' => $category,
            'page' => $page,
        ]);
    }
}
