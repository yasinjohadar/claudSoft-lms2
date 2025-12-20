<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\CourseModule;
use App\Models\CourseSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    /**
     * Display a listing of the videos.
     */
    public function index(Request $request)
    {
        try {
            $query = Video::with(['creator', 'updater', 'courseModules']);

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('video_url', 'like', "%{$search}%");
                });
            }

            // Filter by video type
            if ($request->filled('video_type')) {
                $query->where('video_type', $request->video_type);
            }

            // Filter by processing status
            if ($request->filled('processing_status')) {
                $query->where('processing_status', $request->processing_status);
            }

            // Filter by published status
            if ($request->filled('is_published')) {
                $query->where('is_published', $request->is_published);
            }

            // Filter by visibility
            if ($request->filled('is_visible')) {
                $query->where('is_visible', $request->is_visible);
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $videos = $query->paginate($request->get('per_page', 15));

            // Get filter options
            $videoTypes = ['upload', 'youtube', 'vimeo', 'external'];
            $processingStatuses = ['pending', 'processing', 'completed', 'failed'];

            // Get statistics
            $totalVideos = Video::count();
            $totalViews = 0; // Video::sum('views_count') ?? 0; // Column doesn't exist yet
            $totalDuration = Video::sum('duration') ?? 0;
            $publishedCount = Video::where('is_published', true)->count();

            // Get courses for filter
            $courses = \App\Models\Course::select('id', 'title')->get();

            return view('admin.pages.videos.index', compact(
                'videos',
                'videoTypes',
                'processingStatuses',
                'totalVideos',
                'totalViews',
                'totalDuration',
                'publishedCount',
                'courses'
            ));
        } catch (\Exception $e) {
            return redirect()->route('admin.dashboard')->with('error', 'حدث خطأ أثناء تحميل الفيديوهات: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new video.
     */
    public function create(Request $request)
    {
        try {
            $videoTypes = ['upload', 'youtube', 'vimeo', 'external'];
            $courses = \App\Models\Course::select('id', 'title')->get();
            
            // Get section_id and course_id from query parameters (if coming from course page)
            $sectionId = $request->query('section_id');
            $courseId = $request->query('course_id');
            $section = null;
            $course = null;
            
            if ($sectionId) {
                $section = \App\Models\CourseSection::with('course')->find($sectionId);
                if ($section && $section->course) {
                    $course = $section->course;
                    $courseId = $course->id;
                }
            } elseif ($courseId) {
                $course = \App\Models\Course::find($courseId);
            }

            return view('admin.pages.videos.create', compact('videoTypes', 'courses', 'section', 'course', 'sectionId', 'courseId'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحميل نموذج الإنشاء: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created video in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_type' => 'required|in:upload,youtube,vimeo,external',
            'video_url' => 'required_if:video_type,youtube,vimeo,external|nullable|url',
            'video_file' => 'required_if:video_type,upload|nullable|file|mimes:mp4,mov,avi,wmv|max:512000',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'duration' => 'nullable|integer|min:0',
            'quality' => 'nullable|json',
            'subtitles' => 'nullable|json',
            'is_published' => 'nullable|boolean',
            'is_visible' => 'nullable|boolean',
            'allow_download' => 'nullable|boolean',
            'allow_speed_control' => 'nullable|boolean',
            'require_watch_complete' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date|after:available_from',
            'course_id' => 'nullable|exists:courses,id',
            'section_id' => 'nullable|exists:course_sections,id',
        ]);

        DB::beginTransaction();
        try {
            // Handle video upload
            if ($request->video_type === 'upload' && $request->hasFile('video_file')) {
                $validated['video_path'] = $request->file('video_file')->store('videos', 'public');
                $validated['processing_status'] = 'pending';
            } else {
                $validated['processing_status'] = 'completed';
            }

            // Handle thumbnail upload
            if ($request->hasFile('thumbnail')) {
                $validated['thumbnail'] = $request->file('thumbnail')->store('videos/thumbnails', 'public');
            }

            // Convert boolean fields
            $validated['is_published'] = $request->has('is_published');
            $validated['is_visible'] = $request->has('is_visible');
            $validated['allow_download'] = $request->has('allow_download');
            $validated['allow_speed_control'] = $request->has('allow_speed_control');
            $validated['require_watch_complete'] = $request->has('require_watch_complete');

            // Decode JSON fields
            if ($request->filled('quality')) {
                $validated['quality'] = json_decode($validated['quality'], true);
            }

            if ($request->filled('subtitles')) {
                $validated['subtitles'] = json_decode($validated['subtitles'], true);
            }

            // Set creator
            $validated['created_by'] = auth()->id();

            $video = Video::create($validated);

            // If section_id is provided, create a module automatically
            if ($request->filled('section_id') && $video) {
                $section = CourseSection::findOrFail($request->section_id);
                
                // Get next sort_order
                $maxOrder = CourseModule::where('section_id', $section->id)->max('sort_order') ?? 0;
                
                // Create module
                CourseModule::create([
                    'course_id' => $section->course_id,
                    'section_id' => $section->id,
                    'module_type' => 'video',
                    'modulable_id' => $video->id,
                    'modulable_type' => Video::class,
                    'title' => $video->title,
                    'description' => $video->description,
                    'sort_order' => $maxOrder + 1,
                    'is_visible' => true,
                    'is_required' => false,
                    'is_graded' => false,
                    'completion_type' => 'auto',
                    'estimated_duration' => $video->duration ?? 0,
                ]);
            }

            DB::commit();

            if ($request->filled('section_id')) {
                return redirect()
                    ->route('courses.show', $request->course_id ?? $section->course_id)
                    ->with('success', 'تم إنشاء الفيديو وربطه بالقسم بنجاح');
            }

            return redirect()
                ->route('videos.show', $video->id)
                ->with('success', 'تم إنشاء الفيديو بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete uploaded files if exists
            if (isset($validated['video_path'])) {
                Storage::disk('public')->delete($validated['video_path']);
            }
            if (isset($validated['thumbnail'])) {
                Storage::disk('public')->delete($validated['thumbnail']);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء الفيديو: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified video.
     */
    public function show($id)
    {
        try {
            $video = Video::with(['creator', 'updater', 'courseModules.course'])->findOrFail($id);

            // Get statistics
            $stats = [
                'used_in_modules' => $video->courseModules()->count(),
                'duration_minutes' => $video->duration ?? 0,
                'formatted_duration' => $this->formatDuration($video->duration),
                'is_external' => in_array($video->video_type, ['youtube', 'vimeo', 'external']),
            ];

            return view('admin.pages.videos.show', compact('video', 'stats'));
        } catch (\Exception $e) {
            return redirect()
                ->route('videos.index')
                ->with('error', 'حدث خطأ أثناء تحميل الفيديو: ' . $e->getMessage());
        }
    }

    /**
     * Format duration in minutes to human readable format
     */
    private function formatDuration($minutes)
    {
        if (!$minutes) return '0 دقيقة';

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($hours > 0) {
            return $hours . ' ساعة' . ($mins > 0 ? ' و ' . $mins . ' دقيقة' : '');
        }

        return $mins . ' دقيقة';
    }

    /**
     * Show the form for editing the specified video.
     */
    public function edit($id)
    {
        try {
            $video = Video::findOrFail($id);
            $videoTypes = ['upload', 'youtube', 'vimeo', 'external'];

            return view('admin.pages.videos.edit', compact('video', 'videoTypes'));
        } catch (\Exception $e) {
            return redirect()
                ->route('videos.index')
                ->with('error', 'حدث خطأ أثناء تحميل نموذج التعديل: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified video in storage.
     */
    public function update(Request $request, $id)
    {
        $video = Video::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_type' => 'required|in:upload,youtube,vimeo,external',
            'video_url' => 'required_if:video_type,youtube,vimeo,external|nullable|url',
            'video_file' => 'nullable|file|mimes:mp4,mov,avi,wmv|max:512000',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'duration' => 'nullable|integer|min:0',
            'quality' => 'nullable|json',
            'subtitles' => 'nullable|json',
            'is_published' => 'nullable|boolean',
            'is_visible' => 'nullable|boolean',
            'allow_download' => 'nullable|boolean',
            'allow_speed_control' => 'nullable|boolean',
            'require_watch_complete' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date|after:available_from',
        ]);

        DB::beginTransaction();
        try {
            // Handle video upload
            if ($request->hasFile('video_file')) {
                // Delete old video
                if ($video->video_path) {
                    Storage::disk('public')->delete($video->video_path);
                }
                $validated['video_path'] = $request->file('video_file')->store('videos', 'public');
                $validated['processing_status'] = 'pending';
            }

            // Handle thumbnail upload
            if ($request->hasFile('thumbnail')) {
                // Delete old thumbnail
                if ($video->thumbnail) {
                    Storage::disk('public')->delete($video->thumbnail);
                }
                $validated['thumbnail'] = $request->file('thumbnail')->store('videos/thumbnails', 'public');
            }

            // Convert boolean fields
            $validated['is_published'] = $request->has('is_published');
            $validated['is_visible'] = $request->has('is_visible');
            $validated['allow_download'] = $request->has('allow_download');
            $validated['allow_speed_control'] = $request->has('allow_speed_control');
            $validated['require_watch_complete'] = $request->has('require_watch_complete');

            // Decode JSON fields
            if ($request->filled('quality')) {
                $validated['quality'] = json_decode($validated['quality'], true);
            }

            if ($request->filled('subtitles')) {
                $validated['subtitles'] = json_decode($validated['subtitles'], true);
            }

            // Set updater
            $validated['updated_by'] = auth()->id();

            $video->update($validated);

            DB::commit();

            return redirect()
                ->route('videos.show', $video->id)
                ->with('success', 'تم تحديث الفيديو بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث الفيديو: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified video from storage (soft delete).
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $video = Video::with('courseModules.section.course')->findOrFail($id);

            // Get modules that use this video
            $usedInModules = $video->courseModules()->with(['section.course'])->get();
            $usedInModulesCount = $usedInModules->count();
            
            $warningMessage = '';
            if ($usedInModulesCount > 0) {
                $moduleTitles = $usedInModules->map(function($module) {
                    return $module->section->course->title . ' - ' . $module->section->title . ' - ' . $module->title;
                })->implode(', ');
                
                $warningMessage = "تحذير: هذا الفيديو مرتبط بـ {$usedInModulesCount} وحدة دراسية. سيتم إزالة الفيديو من هذه الوحدات عند الحذف.";
                
                // Delete all course modules that use this video
                foreach ($usedInModules as $module) {
                    $module->delete();
                }
            }

            // Delete video file and thumbnail
            if ($video->video_path) {
                Storage::disk('public')->delete($video->video_path);
            }
            if ($video->thumbnail) {
                Storage::disk('public')->delete($video->thumbnail);
            }

            $video->delete();

            DB::commit();

            $successMessage = $usedInModulesCount > 0 
                ? "تم حذف الفيديو بنجاح. تم إزالة الفيديو من {$usedInModulesCount} وحدة دراسية."
                : 'تم حذف الفيديو بنجاح';

            // Always return JSON for AJAX requests
            if ($request->ajax() || $request->expectsJson() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'warning' => $warningMessage
                ]);
            }

            return redirect()
                ->route('videos.index')
                ->with('success', $successMessage)
                ->with('warning', $warningMessage);
        } catch (\Exception $e) {
            DB::rollBack();

            // Always return JSON for AJAX requests
            if ($request->ajax() || $request->expectsJson() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء حذف الفيديو: ' . $e->getMessage()
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء حذف الفيديو: ' . $e->getMessage());
        }
    }

    /**
     * Get video usage information before deletion.
     */
    public function getUsageInfo($id)
    {
        try {
            $video = Video::with(['courseModules.section.course'])->findOrFail($id);
            $usedInModules = $video->courseModules()->with(['section.course'])->get();
            
            return response()->json([
                'success' => true,
                'used_in_modules' => $usedInModules->count(),
                'modules' => $usedInModules->map(function($module) {
                    return [
                        'id' => $module->id,
                        'title' => $module->title,
                        'course' => $module->section->course->title ?? 'غير محدد',
                        'section' => $module->section->title ?? 'غير محدد',
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب المعلومات: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplicate a video.
     */
    public function duplicate($id)
    {
        DB::beginTransaction();
        try {
            $originalVideo = Video::findOrFail($id);

            // Create duplicate
            $newVideo = $originalVideo->replicate();
            $newVideo->title = $originalVideo->title . ' (نسخة)';
            $newVideo->is_published = false;
            $newVideo->created_by = auth()->id();
            $newVideo->updated_by = null;

            // Copy video file if it's uploaded
            if ($originalVideo->video_path && Storage::disk('public')->exists($originalVideo->video_path)) {
                $newPath = 'videos/' . uniqid() . '_' . basename($originalVideo->video_path);
                Storage::disk('public')->copy($originalVideo->video_path, $newPath);
                $newVideo->video_path = $newPath;
            }

            // Copy thumbnail
            if ($originalVideo->thumbnail && Storage::disk('public')->exists($originalVideo->thumbnail)) {
                $newPath = 'videos/thumbnails/' . uniqid() . '_' . basename($originalVideo->thumbnail);
                Storage::disk('public')->copy($originalVideo->thumbnail, $newPath);
                $newVideo->thumbnail = $newPath;
            }

            $newVideo->save();

            DB::commit();

            return redirect()
                ->route('videos.show', $newVideo->id)
                ->with('success', 'تم نسخ الفيديو بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء نسخ الفيديو: ' . $e->getMessage());
        }
    }

    /**
     * Toggle video publish status.
     */
    public function togglePublish(Request $request, $id)
    {
        try {
            $video = Video::findOrFail($id);
            $video->is_published = !$video->is_published;
            $video->updated_by = auth()->id();
            $video->save();

            $status = $video->is_published ? 'منشور' : 'مسودة';

            // Always return JSON for AJAX requests
            if ($request->ajax() || $request->expectsJson() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => "تم تحديث حالة النشر إلى: {$status}",
                    'is_published' => $video->is_published
                ]);
            }

            return redirect()
                ->back()
                ->with('success', "تم تحديث حالة النشر إلى: {$status}");
        } catch (\Exception $e) {
            // Always return JSON for AJAX requests
            if ($request->ajax() || $request->expectsJson() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء تحديث حالة النشر: ' . $e->getMessage()
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تحديث حالة النشر: ' . $e->getMessage());
        }
    }

    /**
     * Toggle video visibility.
     */
    public function toggleVisibility(Request $request, $id)
    {
        try {
            $video = Video::findOrFail($id);
            $video->is_visible = !$video->is_visible;
            $video->updated_by = auth()->id();
            $video->save();

            $status = $video->is_visible ? 'مرئي' : 'مخفي';

            // Always return JSON for AJAX requests
            if ($request->ajax() || $request->expectsJson() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => "تم تحديث الظهور إلى: {$status}",
                    'is_visible' => $video->is_visible
                ]);
            }

            return redirect()
                ->back()
                ->with('success', "تم تحديث الظهور إلى: {$status}");
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء تحديث الظهور: ' . $e->getMessage()
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تحديث الظهور: ' . $e->getMessage());
        }
    }

    /**
     * Update video processing status.
     */
    public function updateProcessingStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'processing_status' => 'required|in:pending,processing,completed,failed',
            'processing_error' => 'nullable|string',
        ]);

        try {
            $video = Video::findOrFail($id);
            $video->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث حالة المعالجة بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }
}
