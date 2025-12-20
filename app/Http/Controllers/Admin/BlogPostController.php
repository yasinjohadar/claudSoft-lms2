<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BlogPostController extends Controller
{
    /**
     * Display a listing of blog posts.
     */
    public function index(Request $request)
    {
        $query = BlogPost::with(['author', 'category', 'tags']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('blog_category_id', $request->category);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by author
        if ($request->filled('author')) {
            $query->where('author_id', $request->author);
        }

        $posts = $query->latest('created_at')->paginate(15);
        $categories = BlogCategory::orderBy('name')->get();
        $authors = User::role(['admin', 'instructor'])->orderBy('name')->get();

        return view('admin.blog.posts.index', compact('posts', 'categories', 'authors'));
    }

    /**
     * Show the form for creating a new post.
     */
    public function create()
    {
        $categories = BlogCategory::orderBy('name')->get();
        $tags = BlogTag::orderBy('name')->get();

        return view('admin.blog.posts.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created post in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'category_id' => 'required|exists:blog_categories,id',
            'status' => 'required|in:draft,published,scheduled',
            'featured_image' => 'nullable|image|max:2048',
            'featured_image_alt' => 'nullable|string|max:255',
            'published_at' => 'nullable|date',

            // SEO Fields
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'focus_keyword' => 'nullable|string|max:255',

            // Schema.org
            'schema_type' => 'nullable|string|max:50',

            // Flags
            'is_featured' => 'boolean',
            'allow_comments' => 'boolean',
            'is_indexable' => 'boolean',
            'is_followable' => 'boolean',

            // Tags
            'tags' => 'nullable|array',
            'tags.*' => 'exists:blog_tags,id',
        ]);

        DB::beginTransaction();

        try {
            // Generate slug
            $validated['slug'] = Str::slug($validated['title']);

            // Check for unique slug
            $counter = 1;
            $originalSlug = $validated['slug'];
            while (BlogPost::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter++;
            }

            // Set author
            $validated['author_id'] = Auth::id();

            // Map category_id to blog_category_id
            if (isset($validated['category_id'])) {
                $validated['blog_category_id'] = $validated['category_id'];
                unset($validated['category_id']);
            }

            // Handle featured image upload
            if ($request->hasFile('featured_image')) {
                $validated['featured_image'] = $request->file('featured_image')->store('blog/images', 'public');
            }

            // Set published_at if status is published and not set
            if ($validated['status'] === 'published' && !isset($validated['published_at'])) {
                $validated['published_at'] = now();
            }

            // Set default schema type
            if (!isset($validated['schema_type'])) {
                $validated['schema_type'] = 'Article';
            }

            // Extract tags before creating post
            $tags = $validated['tags'] ?? [];
            unset($validated['tags']);

            // Create post
            $post = BlogPost::create($validated);

            // Attach tags
            if (!empty($tags)) {
                $post->tags()->attach($tags);

                // Update tags posts count
                foreach ($tags as $tagId) {
                    $tag = BlogTag::find($tagId);
                    if ($tag) {
                        $tag->posts_count = $tag->posts()->count();
                        $tag->save();
                    }
                }
            }

            // Calculate reading time
            $post->calculateReadingTime();

            DB::commit();

            return redirect()->route('admin.blog.posts.edit', $post->id)
                           ->with('success', 'تم إنشاء المقال بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء إنشاء المقال: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified post.
     */
    public function show(BlogPost $post)
    {
        $post->load(['author', 'category', 'tags']);
        return view('admin.blog.posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(BlogPost $post)
    {
        // Ensure post exists and is not deleted
        if (!$post->exists || $post->trashed()) {
            return redirect()->route('admin.blog.posts.index')
                           ->with('error', 'المقال غير موجود');
        }

        $categories = BlogCategory::orderBy('name')->get();
        $tags = BlogTag::orderBy('name')->get();
        $post->load('tags');

        return view('admin.blog.posts.edit', compact('post', 'categories', 'tags'));
    }

    /**
     * Update the specified post in storage.
     */
    public function update(Request $request, BlogPost $post)
    {
        // Double check - ensure this is actually an update request, not destroy
        if ($request->method() === 'DELETE') {
            \Log::error('UPDATE method received DELETE request!', [
                'post_id' => $post->id,
                'method' => $request->method(),
                '_method' => $request->input('_method'),
                'route' => $request->route()?->getName()
            ]);
            abort(405, 'Method not allowed. This is an update endpoint.');
        }

        // Log that update method was called (not destroy)
        \Log::info('UPDATE method called', [
            'method' => $request->method(),
            'post_id' => $post->id,
            '_method' => $request->input('_method'),
            'route' => $request->route()?->getName(),
            'url' => $request->fullUrl()
        ]);

        // Validate the request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'category_id' => 'required|exists:blog_categories,id',
            'status' => 'required|in:draft,published,scheduled',
            'featured_image' => 'nullable|image|max:2048',
            'featured_image_alt' => 'nullable|string|max:255',
            'published_at' => 'nullable|date',

            // SEO Fields
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'focus_keyword' => 'nullable|string|max:255',

            // Schema.org
            'schema_type' => 'nullable|string|max:50',

            // Flags
            'is_featured' => 'boolean',
            'allow_comments' => 'boolean',
            'is_indexable' => 'boolean',
            'is_followable' => 'boolean',

            // Tags
            'tags' => 'nullable|array',
            'tags.*' => 'exists:blog_tags,id',
        ]);

        DB::beginTransaction();

        try {
            // Ensure we're updating the correct post and it's not deleted
            if (!$post->exists || $post->trashed()) {
                DB::rollBack();
                return redirect()->route('admin.blog.posts.index')
                               ->with('error', 'المقال غير موجود أو تم حذفه');
            }

            // Log the update attempt for debugging
            \Log::info('Updating blog post', [
                'post_id' => $post->id,
                'title' => $validated['title'],
                'category_id' => $validated['category_id'] ?? 'not set'
            ]);

            // Update slug if title changed
            if ($validated['title'] !== $post->title) {
                $validated['slug'] = Str::slug($validated['title']);

                // Check for unique slug
                $counter = 1;
                $originalSlug = $validated['slug'];
                while (BlogPost::where('slug', $validated['slug'])->where('id', '!=', $post->id)->exists()) {
                    $validated['slug'] = $originalSlug . '-' . $counter++;
                }
            }

            // Handle featured image upload
            if ($request->hasFile('featured_image')) {
                // Delete old image
                if ($post->featured_image && Storage::disk('public')->exists($post->featured_image)) {
                    Storage::disk('public')->delete($post->featured_image);
                }
                $validated['featured_image'] = $request->file('featured_image')->store('blog/images', 'public');
            }

            // Set published_at if status changed to published
            if ($validated['status'] === 'published' && $post->status !== 'published' && !isset($validated['published_at'])) {
                $validated['published_at'] = now();
            }

            // Map category_id to blog_category_id
            if (isset($validated['category_id'])) {
                $validated['blog_category_id'] = $validated['category_id'];
                unset($validated['category_id']);
            }

            // Extract tags before updating post
            $tags = $validated['tags'] ?? [];
            unset($validated['tags']);

            // Get old tags for count update
            $oldTags = $post->tags->pluck('id')->toArray();

            // Update post
            $post->update($validated);

            // Sync tags
            $post->tags()->sync($tags);

            // Update tags posts count for old and new tags
            $allAffectedTags = array_unique(array_merge($oldTags, $tags));
            foreach ($allAffectedTags as $tagId) {
                $tag = BlogTag::find($tagId);
                if ($tag) {
                    $tag->posts_count = $tag->posts()->count();
                    $tag->save();
                }
            }

            // Recalculate reading time
            $post->calculateReadingTime();

            DB::commit();

            return redirect()->route('admin.blog.posts.index')
                           ->with('success', 'تم تحديث المقال بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء تحديث المقال: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified post from storage.
     */
    public function destroy(Request $request, BlogPost $post)
    {
        // Log that destroy method was called
        \Log::warning('DESTROY method called', [
            'method' => $request->method(),
            'post_id' => $post->id,
            '_method' => $request->input('_method'),
            'route' => $request->route()->getName(),
            'referer' => $request->header('referer')
        ]);

        try {
            // Get tags before deleting
            $tagIds = $post->tags->pluck('id')->toArray();

            // Delete featured image
            if ($post->featured_image && Storage::disk('public')->exists($post->featured_image)) {
                Storage::disk('public')->delete($post->featured_image);
            }

            // Delete post (will auto-detach tags due to cascade)
            $post->delete();

            // Update tags posts count
            foreach ($tagIds as $tagId) {
                $tag = BlogTag::find($tagId);
                if ($tag) {
                    $tag->posts_count = $tag->posts()->count();
                    $tag->save();
                }
            }

            // Check if request expects JSON (AJAX)
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => 'تم حذف المقال بنجاح'
                ]);
            }

            return redirect()->route('admin.blog.posts.index')
                           ->with('success', 'تم حذف المقال بنجاح');

        } catch (\Exception $e) {
            // Check if request expects JSON (AJAX)
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء حذف المقال: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'حدث خطأ أثناء حذف المقال: ' . $e->getMessage());
        }
    }

    /**
     * Toggle post featured status
     */
    public function toggleFeatured(BlogPost $post)
    {
        $post->is_featured = !$post->is_featured;
        $post->save();

        return back()->with('success', 'تم تحديث حالة المقال المميز');
    }

    /**
     * Toggle post publish status
     */
    public function togglePublish(BlogPost $post)
    {
        if ($post->status === 'published') {
            $post->status = 'draft';
        } else {
            $post->status = 'published';
            if (!$post->published_at) {
                $post->published_at = now();
            }
        }
        $post->save();

        return back()->with('success', 'تم تحديث حالة نشر المقال');
    }

    /**
     * Delete featured image
     */
    public function deleteFeaturedImage(Request $request, BlogPost $post)
    {
        if ($post->featured_image && Storage::disk('public')->exists($post->featured_image)) {
            Storage::disk('public')->delete($post->featured_image);
            $post->featured_image = null;
            $post->featured_image_alt = null;
            $post->save();

            // Check if request expects JSON (AJAX)
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => 'تم حذف الصورة البارزة'
                ]);
            }

            return back()->with('success', 'تم حذف الصورة البارزة');
        }

        // Check if request expects JSON (AJAX)
        if ($request->expectsJson() || $request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد صورة لحذفها'
            ], 404);
        }

        return back()->with('error', 'لا توجد صورة لحذفها');
    }
}
