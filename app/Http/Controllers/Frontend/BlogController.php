<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    /**
     * Display a listing of all blog posts
     */
    public function index(Request $request): View
    {
        $query = BlogPost::with(['author', 'category', 'tags'])
                        ->published()
                        ->indexable()
                        ->orderBy('is_featured', 'desc')
                        ->orderBy('published_at', 'desc');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $category = BlogCategory::where('slug', $request->category)->first();
            if ($category) {
                $query->where('blog_category_id', $category->id);
            }
        }

        // Filter by tag
        if ($request->has('tag') && $request->tag) {
            $tag = BlogTag::where('slug', $request->tag)->first();
            if ($tag) {
                $query->whereHas('tags', function($q) use ($tag) {
                    $q->where('blog_tags.id', $tag->id);
                });
            }
        }

        $posts = $query->paginate(12);

        // Get categories and tags for sidebar
        $categories = BlogCategory::active()
                                  ->parents()
                                  ->orderBy('order')
                                  ->withCount('publishedPosts')
                                  ->get();

        $popularTags = BlogTag::active()
                             ->popular(15)
                             ->get();

        $featuredPosts = BlogPost::with(['author', 'category'])
                                ->published()
                                ->featured()
                                ->limit(3)
                                ->get();

        return view('frontend.pages.blog.index', compact(
            'posts',
            'categories',
            'popularTags',
            'featuredPosts'
        ));
    }

    /**
     * Display the specified blog post
     */
    public function show(string $slug): View
    {
        $post = BlogPost::with(['author', 'category', 'tags'])
                       ->where('slug', $slug)
                       ->published()
                       ->firstOrFail();

        // Increment views count
        $post->incrementViews();

        // Get related posts
        $relatedPosts = BlogPost::with(['author', 'category'])
                               ->published()
                               ->where('id', '!=', $post->id)
                               ->where(function($query) use ($post) {
                                   // Same category or same tags
                                   $query->where('blog_category_id', $post->blog_category_id)
                                         ->orWhereHas('tags', function($q) use ($post) {
                                             $q->whereIn('blog_tags.id', $post->tags->pluck('id'));
                                         });
                               })
                               ->limit(3)
                               ->get();

        // Get previous and next posts
        $previousPost = BlogPost::published()
                               ->where('published_at', '<', $post->published_at)
                               ->orderBy('published_at', 'desc')
                               ->first();

        $nextPost = BlogPost::published()
                           ->where('published_at', '>', $post->published_at)
                           ->orderBy('published_at', 'asc')
                           ->first();

        // Get popular posts from same category
        $popularPosts = BlogPost::with(['author', 'category'])
                               ->published()
                               ->where('blog_category_id', $post->blog_category_id)
                               ->where('id', '!=', $post->id)
                               ->orderBy('views_count', 'desc')
                               ->limit(5)
                               ->get();

        return view('frontend.pages.blog.show', compact(
            'post',
            'relatedPosts',
            'previousPost',
            'nextPost',
            'popularPosts'
        ));
    }

    /**
     * Display posts by category
     */
    public function category(string $slug): View
    {
        $category = BlogCategory::where('slug', $slug)
                                ->active()
                                ->firstOrFail();

        $posts = BlogPost::with(['author', 'category', 'tags'])
                        ->where('blog_category_id', $category->id)
                        ->published()
                        ->indexable()
                        ->orderBy('is_featured', 'desc')
                        ->orderBy('published_at', 'desc')
                        ->paginate(12);

        // Get subcategories
        $subcategories = $category->children()
                                  ->active()
                                  ->withCount('publishedPosts')
                                  ->get();

        // Get all categories for sidebar
        $categories = BlogCategory::active()
                                  ->parents()
                                  ->orderBy('order')
                                  ->withCount('publishedPosts')
                                  ->get();

        $popularTags = BlogTag::active()
                             ->popular(15)
                             ->get();

        return view('frontend.pages.blog.category', compact(
            'category',
            'posts',
            'subcategories',
            'categories',
            'popularTags'
        ));
    }

    /**
     * Display posts by tag
     */
    public function tag(string $slug): View
    {
        $tag = BlogTag::where('slug', $slug)
                     ->active()
                     ->firstOrFail();

        $posts = $tag->publishedPosts()
                    ->with(['author', 'category', 'tags'])
                    ->paginate(12);

        // Get categories for sidebar
        $categories = BlogCategory::active()
                                  ->parents()
                                  ->orderBy('order')
                                  ->withCount('publishedPosts')
                                  ->get();

        $popularTags = BlogTag::active()
                             ->popular(15)
                             ->get();

        return view('frontend.pages.blog.tag', compact(
            'tag',
            'posts',
            'categories',
            'popularTags'
        ));
    }

    /**
     * Display search results
     */
    public function search(Request $request): View
    {
        $keyword = $request->input('q', '');

        $posts = BlogPost::with(['author', 'category', 'tags'])
                        ->published()
                        ->indexable()
                        ->search($keyword)
                        ->orderBy('is_featured', 'desc')
                        ->orderBy('published_at', 'desc')
                        ->paginate(12);

        // Get categories for sidebar
        $categories = BlogCategory::active()
                                  ->parents()
                                  ->orderBy('order')
                                  ->withCount('publishedPosts')
                                  ->get();

        $popularTags = BlogTag::active()
                             ->popular(15)
                             ->get();

        return view('frontend.pages.blog.search', compact(
            'posts',
            'keyword',
            'categories',
            'popularTags'
        ));
    }
}
