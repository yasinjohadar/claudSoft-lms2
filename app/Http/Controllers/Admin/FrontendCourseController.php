<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FrontendCourse;
use App\Models\FrontendCourseCategory;
use App\Models\FrontendCourseSection;
use App\Models\FrontendCourseLesson;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FrontendCourseController extends Controller
{
    /**
     * Display a listing of the courses.
     */
    public function index(Request $request)
    {
        $query = FrontendCourse::with(['category', 'instructor']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by level
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        $courses = $query->latest()->paginate(15);
        $categories = FrontendCourseCategory::orderBy('name')->get();

        return view('admin.frontend-courses.index', compact('courses', 'categories'));
    }

    /**
     * Show the form for creating a new course.
     */
    public function create()
    {
        $categories = FrontendCourseCategory::where('is_active', true)->orderBy('name')->get();
        $instructors = User::role(['instructor', 'admin'])->orderBy('name')->get();

        return view('admin.frontend-courses.create', compact('categories', 'instructors'));
    }

    /**
     * Store a newly created course in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:frontend_course_categories,id',
            'instructor_id' => 'required|exists:users,id',
            'level' => 'required|in:beginner,intermediate,advanced',
            'language' => 'required|string|max:10',
            'price' => 'nullable|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'is_free' => 'boolean',
            'currency' => 'required|string|max:10',
            'status' => 'required|in:draft,published,archived',
            'requirements' => 'nullable|string',
            'thumbnail' => 'nullable|image|max:2048',
            'preview_video' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // Generate slug
            $validated['slug'] = Str::slug($validated['title']);

            // Check for unique slug
            $counter = 1;
            $originalSlug = $validated['slug'];
            while (FrontendCourse::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter++;
            }

            // Handle thumbnail upload
            if ($request->hasFile('thumbnail')) {
                $validated['thumbnail'] = $request->file('thumbnail')->store('courses/thumbnails', 'public');
            }

            // Set published_at if status is published
            if ($validated['status'] === 'published' && !isset($validated['published_at'])) {
                $validated['published_at'] = now();
            }

            // Create course
            $course = FrontendCourse::create($validated);

            // Handle sections and lessons
            if ($request->has('sections')) {
                $this->saveSectionsAndLessons($course, $request->sections);
            }

            DB::commit();

            return redirect()->route('admin.frontend-courses.edit', $course->id)
                           ->with('success', 'تم إنشاء الكورس بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء إنشاء الكورس: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified course.
     */
    public function show(FrontendCourse $frontendCourse)
    {
        $frontendCourse->load(['category', 'instructor', 'sections.lessons']);
        return view('admin.frontend-courses.show', compact('frontendCourse'));
    }

    /**
     * Show the form for editing the specified course.
     */
    public function edit(FrontendCourse $frontendCourse)
    {
        $categories = FrontendCourseCategory::where('is_active', true)->orderBy('name')->get();
        $instructors = User::role(['instructor', 'admin'])->orderBy('name')->get();
        $frontendCourse->load('sections.lessons');

        return view('admin.frontend-courses.edit', compact('frontendCourse', 'categories', 'instructors'));
    }

    /**
     * Update the specified course in storage.
     */
    public function update(Request $request, FrontendCourse $frontendCourse)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:frontend_course_categories,id',
            'instructor_id' => 'required|exists:users,id',
            'level' => 'required|in:beginner,intermediate,advanced',
            'language' => 'required|string|max:10',
            'price' => 'nullable|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'is_free' => 'boolean',
            'currency' => 'required|string|max:10',
            'status' => 'required|in:draft,published,archived',
            'requirements' => 'nullable|string',
            'thumbnail' => 'nullable|image|max:2048',
            'preview_video' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // Update slug if title changed
            if ($validated['title'] !== $frontendCourse->title) {
                $validated['slug'] = Str::slug($validated['title']);

                $counter = 1;
                $originalSlug = $validated['slug'];
                while (FrontendCourse::where('slug', $validated['slug'])->where('id', '!=', $frontendCourse->id)->exists()) {
                    $validated['slug'] = $originalSlug . '-' . $counter++;
                }
            }

            // Handle thumbnail upload
            if ($request->hasFile('thumbnail')) {
                // Delete old thumbnail if exists
                if ($frontendCourse->thumbnail) {
                    \Storage::disk('public')->delete($frontendCourse->thumbnail);
                }
                $validated['thumbnail'] = $request->file('thumbnail')->store('courses/thumbnails', 'public');
            }

            // Set published_at if status changed to published
            if ($validated['status'] === 'published' && $frontendCourse->status !== 'published') {
                $validated['published_at'] = now();
            }

            // Update course
            $frontendCourse->update($validated);

            // Handle sections and lessons
            if ($request->has('sections')) {
                $this->updateSectionsAndLessons($frontendCourse, $request->sections);
            }

            DB::commit();

            return redirect()->route('admin.frontend-courses.edit', $frontendCourse->id)
                           ->with('success', 'تم تحديث الكورس بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء تحديث الكورس: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified course from storage.
     */
    public function destroy(FrontendCourse $frontendCourse)
    {
        try {
            // Delete thumbnail if exists
            if ($frontendCourse->thumbnail) {
                \Storage::disk('public')->delete($frontendCourse->thumbnail);
            }

            $frontendCourse->delete();

            return redirect()->route('admin.frontend-courses.index')
                           ->with('success', 'تم حذف الكورس بنجاح');

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء حذف الكورس: ' . $e->getMessage());
        }
    }

    /**
     * Save sections and lessons for a new course
     */
    protected function saveSectionsAndLessons(FrontendCourse $course, array $sections)
    {
        foreach ($sections as $sectionIndex => $sectionData) {
            $section = $course->sections()->create([
                'title' => $sectionData['title'],
                'description' => $sectionData['description'] ?? null,
                'order' => $sectionIndex + 1,
                'is_active' => $sectionData['is_active'] ?? true,
            ]);

            if (isset($sectionData['lessons']) && is_array($sectionData['lessons'])) {
                foreach ($sectionData['lessons'] as $lessonIndex => $lessonData) {
                    $section->lessons()->create([
                        'title' => $lessonData['title'],
                        'description' => $lessonData['description'] ?? null,
                        'type' => $lessonData['type'] ?? 'video',
                        'video_url' => $lessonData['video_url'] ?? null,
                        'duration' => $lessonData['duration'] ?? 0,
                        'order' => $lessonIndex + 1,
                        'is_active' => $lessonData['is_active'] ?? true,
                        'is_free' => $lessonData['is_free'] ?? false,
                    ]);
                }
            }

            // Update section lessons count
            $section->updateLessonsCount();
        }

        // Update course total lessons count
        $this->updateCourseLessonsCount($course);
    }

    /**
     * Update sections and lessons for an existing course
     */
    protected function updateSectionsAndLessons(FrontendCourse $course, array $sections)
    {
        $existingSectionIds = [];

        foreach ($sections as $sectionIndex => $sectionData) {
            if (isset($sectionData['id']) && $sectionData['id']) {
                // Update existing section
                $section = FrontendCourseSection::find($sectionData['id']);
                if ($section && $section->course_id === $course->id) {
                    $section->update([
                        'title' => $sectionData['title'],
                        'description' => $sectionData['description'] ?? null,
                        'order' => $sectionIndex + 1,
                        'is_active' => $sectionData['is_active'] ?? true,
                    ]);
                    $existingSectionIds[] = $section->id;
                }
            } else {
                // Create new section
                $section = $course->sections()->create([
                    'title' => $sectionData['title'],
                    'description' => $sectionData['description'] ?? null,
                    'order' => $sectionIndex + 1,
                    'is_active' => $sectionData['is_active'] ?? true,
                ]);
                $existingSectionIds[] = $section->id;
            }

            // Handle lessons
            $existingLessonIds = [];
            if (isset($sectionData['lessons']) && is_array($sectionData['lessons'])) {
                foreach ($sectionData['lessons'] as $lessonIndex => $lessonData) {
                    if (isset($lessonData['id']) && $lessonData['id']) {
                        // Update existing lesson
                        $lesson = FrontendCourseLesson::find($lessonData['id']);
                        if ($lesson && $lesson->section_id === $section->id) {
                            $lesson->update([
                                'title' => $lessonData['title'],
                                'description' => $lessonData['description'] ?? null,
                                'type' => $lessonData['type'] ?? 'video',
                                'video_url' => $lessonData['video_url'] ?? null,
                                'duration' => $lessonData['duration'] ?? 0,
                                'order' => $lessonIndex + 1,
                                'is_active' => $lessonData['is_active'] ?? true,
                                'is_free' => $lessonData['is_free'] ?? false,
                            ]);
                            $existingLessonIds[] = $lesson->id;
                        }
                    } else {
                        // Create new lesson
                        $lesson = $section->lessons()->create([
                            'title' => $lessonData['title'],
                            'description' => $lessonData['description'] ?? null,
                            'type' => $lessonData['type'] ?? 'video',
                            'video_url' => $lessonData['video_url'] ?? null,
                            'duration' => $lessonData['duration'] ?? 0,
                            'order' => $lessonIndex + 1,
                            'is_active' => $lessonData['is_active'] ?? true,
                            'is_free' => $lessonData['is_free'] ?? false,
                        ]);
                        $existingLessonIds[] = $lesson->id;
                    }
                }
            }

            // Delete removed lessons
            $section->lessons()->whereNotIn('id', $existingLessonIds)->delete();

            // Update section lessons count
            $section->updateLessonsCount();
        }

        // Delete removed sections
        $course->sections()->whereNotIn('id', $existingSectionIds)->delete();

        // Update course total lessons count
        $this->updateCourseLessonsCount($course);
    }

    /**
     * Update course total lessons count
     */
    protected function updateCourseLessonsCount(FrontendCourse $course)
    {
        $totalLessons = FrontendCourseLesson::whereHas('section', function($q) use ($course) {
            $q->where('course_id', $course->id);
        })->count();

        $course->update(['lessons_count' => $totalLessons]);
    }
}
