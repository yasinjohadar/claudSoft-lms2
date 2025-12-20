<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\User;
use App\Events\N8nWebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    /**
     * Display a listing of the courses.
     */
    public function index(Request $request)
    {
        try {
            $query = Course::with(['category', 'creator', 'enrollments']);

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Filter by category
            if ($request->filled('category_id')) {
                $query->where('course_category_id', $request->category_id);
            }

            // Filter by level
            if ($request->filled('level')) {
                $query->where('level', $request->level);
            }

            // Filter by status
            if ($request->filled('status')) {
                if ($request->status === 'published') {
                    $query->where('is_published', true);
                } elseif ($request->status === 'draft') {
                    $query->where('is_published', false);
                }
            }

            // Filter by visibility
            if ($request->filled('visibility')) {
                if ($request->visibility === 'visible') {
                    $query->where('is_visible', true);
                } elseif ($request->visibility === 'hidden') {
                    $query->where('is_visible', false);
                }
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $courses = $query->paginate($request->get('per_page', 15));

            // Get filter options
            $categories = CourseCategory::all();
            $levels = ['beginner', 'intermediate', 'advanced', 'expert'];

            // Get statistics
            $totalCourses = Course::count();
            $publishedCourses = Course::where('is_published', true)->count();
            $totalEnrollments = DB::table('course_enrollments')->count();
            $activeCourses = Course::where('is_published', true)
                ->where('is_visible', true)
                ->count();

            return view('admin.pages.courses.index', compact(
                'courses',
                'categories',
                'levels',
                'totalCourses',
                'publishedCourses',
                'totalEnrollments',
                'activeCourses'
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحميل الكورسات: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new course.
     */
    public function create()
    {
        try {
            $categories = CourseCategory::all();
            $instructors = User::role('instructor')->get();
            $levels = ['beginner', 'intermediate', 'advanced', 'expert'];
            $languages = ['ar', 'en', 'fr'];
            $enrollmentTypes = ['open', 'invitation', 'approval'];

            return view('admin.pages.courses.create', compact(
                'categories',
                'instructors',
                'levels',
                'languages',
                'enrollmentTypes'
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحميل نموذج الإنشاء: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created course in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_category_id' => 'required|exists:course_categories,id',
            'title' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:courses,code',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'level' => 'nullable|in:beginner,intermediate,advanced,expert',
            'duration_in_hours' => 'nullable|numeric|min:0',
            // checkboxes are handled manually via $request->has(...)
            'is_published' => 'nullable',
            'is_visible' => 'nullable',
            'is_featured' => 'nullable',
            'max_students' => 'nullable|integer|min:1',
            'enrollment_type' => 'required|in:open,by_approval,invite_only',
            'instructor_id' => 'nullable|exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date|after:available_from',
        ]);

        DB::beginTransaction();
        try {
            // Generate slug
            $validated['slug'] = Str::slug($validated['title']);

            // Handle duplicate slugs
            $originalSlug = $validated['slug'];
            $count = 1;
            while (Course::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $count;
                $count++;
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('courses/images', 'public');
            }

            // Set created_by
            $validated['created_by'] = auth()->id();

            // Convert boolean fields
            $validated['is_published'] = $request->has('is_published');
            $validated['is_featured'] = $request->has('is_featured');

            $course = Course::create($validated);

            DB::commit();

            return redirect()
                ->route('courses.show', $course->id)
                ->with('success', 'تم إنشاء الكورس بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete uploaded image if exists
            if (isset($validated['image'])) {
                Storage::disk('public')->delete($validated['image']);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء الكورس: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified course.
     */
    public function show($id)
    {
        try {
            $course = Course::with([
                'category',
                'creator',
                'enrollments.student',
                'instructors',
                'sections.modules.modulable',
                'sections.questions.questionType' // Load questions directly linked to sections
            ])->withCount('enrollments')->findOrFail($id);

            // Load questions only for question modules
            $course->sections->each(function ($section) {
                $section->modules->each(function ($module) {
                    if ($module->module_type === 'question_module' && $module->modulable) {
                        $module->modulable->load(['questions.questionType']);
                    }
                });
            });

            // Get statistics
            $stats = [
                'total_enrollments' => $course->enrollments_count ?? $course->enrollments()->count(),
                'active_enrollments' => $course->enrollments()->where('enrollment_status', 'active')->count(),
                'completed_enrollments' => $course->enrollments()->where('enrollment_status', 'completed')->count(),
                'total_sections' => DB::table('course_sections')->where('course_id', $id)->whereNull('deleted_at')->count(),
                'total_modules' => DB::table('course_modules')->where('course_id', $id)->whereNull('deleted_at')->count(),
                'average_completion' => $course->enrollments()->avg('completion_percentage') ?? 0,
            ];

            return view('admin.pages.courses.show', compact('course', 'stats'));
        } catch (\Exception $e) {
            return redirect()
                ->route('courses.index')
                ->with('error', 'حدث خطأ أثناء تحميل الكورس: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified course.
     */
    public function edit($id)
    {
        try {
            $course = Course::with(['category', 'instructors'])->findOrFail($id);
            $categories = CourseCategory::all();
            $instructors = User::role('instructor')->get();
            $levels = ['beginner', 'intermediate', 'advanced', 'expert'];
            $languages = ['ar', 'en', 'fr'];
            $enrollmentTypes = ['open', 'invitation', 'approval'];

            return view('admin.pages.courses.edit', compact(
                'course',
                'categories',
                'instructors',
                'levels',
                'languages',
                'enrollmentTypes'
            ));
        } catch (\Exception $e) {
            return redirect()
                ->route('courses.index')
                ->with('error', 'حدث خطأ أثناء تحميل نموذج التعديل: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified course in storage.
     */
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'required|exists:course_categories,id',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:courses,slug,' . $id,
            // use same image field as in store()
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'level' => 'nullable|in:beginner,intermediate,advanced,expert',
            'duration_in_hours' => 'nullable|numeric|min:0',
            'max_students' => 'nullable|integer|min:1',
            'instructor_id' => 'nullable|exists:users,id',
            'enrollment_type' => 'required|in:open,by_approval,invite_only',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date|after:available_from',
        ]);

        DB::beginTransaction();
        try {
            // Map category_id to course_category_id
            if (isset($validated['category_id'])) {
                $validated['course_category_id'] = $validated['category_id'];
                unset($validated['category_id']);
            }

            // Update slug if title changed
            if ($validated['title'] !== $course->title) {
                $validated['slug'] = Str::slug($validated['title']);

                // Handle duplicate slugs
                $originalSlug = $validated['slug'];
                $count = 1;
                while (Course::where('slug', $validated['slug'])->where('id', '!=', $id)->exists()) {
                    $validated['slug'] = $originalSlug . '-' . $count;
                    $count++;
                }
            }

            // Handle image upload (same logic as store)
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($course->image) {
                    Storage::disk('public')->delete($course->image);
                }
                $validated['image'] = $request->file('image')->store('courses/images', 'public');
            }

            // Set updated_by
            $validated['updated_by'] = auth()->id();

            // Convert boolean fields
            $wasPublished = $course->is_published;
            $validated['is_published'] = $request->has('is_published');
            $validated['is_featured'] = $request->has('is_featured');

            $course->update($validated);

            // Dispatch n8n webhook event when course is published
            if ($course->is_published && !$wasPublished) {
                event(new N8nWebhookEvent('course.published', [
                    'course_id' => $course->id,
                    'course_title' => $course->title,
                    'course_slug' => $course->slug,
                    'category_id' => $course->course_category_id,
                    'instructor_id' => $course->created_by,
                    'published_at' => now()->toIso8601String(),
                    'published_by' => auth()->id(),
                ]));
            }

            DB::commit();

            return redirect()
                ->route('courses.show', $course->id)
                ->with('success', 'تم تحديث الكورس بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث الكورس: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified course from storage (soft delete).
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $course = Course::findOrFail($id);

            // Check if course has active enrollments
            $activeEnrollments = $course->enrollments()->where('enrollment_status', 'active')->count();
            if ($activeEnrollments > 0) {
                return redirect()
                    ->back()
                    ->with('error', 'لا يمكن حذف الكورس لوجود طلاب مسجلين فيه');
            }

            $course->delete();

            DB::commit();

            return redirect()
                ->route('courses.index')
                ->with('success', 'تم حذف الكورس بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء حذف الكورس: ' . $e->getMessage());
        }
    }

    /**
     * Duplicate a course.
     */
    public function duplicate($id)
    {
        DB::beginTransaction();
        try {
            $originalCourse = Course::with(['sections.modules'])->findOrFail($id);

            // Create duplicate
            $newCourse = $originalCourse->replicate();
            $newCourse->title = $originalCourse->title . ' (نسخة)';
            $newCourse->slug = Str::slug($newCourse->title);
            $newCourse->code = null;
            $newCourse->is_published = false;
            $newCourse->created_by = auth()->id();
            $newCourse->updated_by = null;

            // Handle duplicate slugs
            $originalSlug = $newCourse->slug;
            $count = 1;
            while (Course::where('slug', $newCourse->slug)->exists()) {
                $newCourse->slug = $originalSlug . '-' . $count;
                $count++;
            }

            $newCourse->save();

            // Duplicate sections and modules
            foreach ($originalCourse->sections as $section) {
                $newSection = $section->replicate();
                $newSection->course_id = $newCourse->id;
                $newSection->save();

                foreach ($section->modules as $module) {
                    $newModule = $module->replicate();
                    $newModule->course_id = $newCourse->id;
                    $newModule->section_id = $newSection->id;
                    $newModule->save();
                }
            }

            DB::commit();

            return redirect()
                ->route('courses.show', $newCourse->id)
                ->with('success', 'تم نسخ الكورس بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء نسخ الكورس: ' . $e->getMessage());
        }
    }

    /**
     * Toggle course publish status.
     */
    public function togglePublish(Request $request, $id)
    {
        try {
            $course = Course::findOrFail($id);
            $wasPublished = $course->is_published;
            $course->is_published = !$course->is_published;
            $course->updated_by = auth()->id();
            $course->save();

            $status = $course->is_published ? 'منشور' : 'مسودة';

            // Dispatch n8n webhook event when course is published
            if ($course->is_published && !$wasPublished) {
                event(new N8nWebhookEvent('course.published', [
                    'course_id' => $course->id,
                    'course_title' => $course->title,
                    'course_slug' => $course->slug,
                    'category_id' => $course->course_category_id,
                    'instructor_id' => $course->created_by,
                    'published_at' => now()->toIso8601String(),
                    'published_by' => auth()->id(),
                ]));
            }

            // Always return JSON for this endpoint (it's called via AJAX)
            return response()->json([
                'success' => true,
                'status' => $status,
                'is_published' => (bool) $course->is_published,
                'message' => "تم تحديث حالة النشر إلى: {$status}",
            ]);
        } catch (\Exception $e) {
            \Log::error('Course toggle publish error: ' . $e->getMessage(), [
                'course_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث حالة النشر: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle course visibility.
     */
    public function toggleVisibility($id)
    {
        try {
            $course = Course::findOrFail($id);
            $course->is_visible = !$course->is_visible;
            $course->updated_by = auth()->id();
            $course->save();

            $status = $course->is_visible ? 'مرئي' : 'مخفي';

            return redirect()
                ->back()
                ->with('success', "تم تحديث الظهور إلى: {$status}");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تحديث الظهور: ' . $e->getMessage());
        }
    }

    /**
     * Get modules for a specific course (API endpoint)
     */
    public function getModules($id)
    {
        try {
            $course = Course::findOrFail($id);

            // Get all modules for this course through sections
            $modules = \App\Models\CourseModule::whereHas('section', function($query) use ($id) {
                $query->where('course_id', $id);
            })
            ->with('section')
            ->orderBy('sort_order')
            ->get()
            ->map(function($module) {
                return [
                    'id' => $module->id,
                    'title' => $module->title,
                    'section_title' => $module->section ? $module->section->title : '',
                ];
            });

            return response()->json([
                'success' => true,
                'modules' => $modules
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الموديولات: ' . $e->getMessage()
            ], 500);
        }
    }
}
