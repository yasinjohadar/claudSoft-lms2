<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\Video;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CourseModuleController extends Controller
{
    /**
     * Display a listing of the modules.
     */
    public function index(Request $request)
    {
        try {
            $query = CourseModule::with(['course', 'section', 'modulable']);

            // Filter by course
            if ($request->filled('course_id')) {
                $query->where('course_id', $request->course_id);
            }

            // Filter by section
            if ($request->filled('section_id')) {
                $query->where('section_id', $request->section_id);
            }

            // Filter by module type
            if ($request->filled('module_type')) {
                $query->where('module_type', $request->module_type);
            }

            // Filter by visibility
            if ($request->filled('is_visible')) {
                $query->where('is_visible', $request->is_visible);
            }

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Sort
            $sortBy = $request->get('sort_by', 'sort_order');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            $modules = $query->paginate($request->get('per_page', 15));

            // Get filter options
            $courses = Course::all();
            $sections = CourseSection::all();
            $moduleTypes = ['lesson', 'video', 'resource', 'quiz', 'assignment'];

            return view('admin.course-modules.index', compact('modules', 'courses', 'sections', 'moduleTypes'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحميل الوحدات: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new module.
     */
    public function create($sectionId)
    {
        try {
            $section = CourseSection::with('course')->findOrFail($sectionId);

            $moduleTypes = ['lesson', 'video', 'resource', 'quiz', 'assignment'];
            $completionTypes = ['auto', 'manual', 'score_based'];

            // Get available content based on type
            $lessons = Lesson::orderBy('created_at', 'desc')->get();
            $videos = Video::orderBy('created_at', 'desc')->get();
            $resources = Resource::orderBy('created_at', 'desc')->get();

            return view('admin.modules.create', compact(
                'section',
                'moduleTypes',
                'completionTypes',
                'lessons',
                'videos',
                'resources'
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحميل نموذج الإنشاء: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created module in storage.
     */
    public function store(Request $request, $sectionId)
    {
        $section = CourseSection::findOrFail($sectionId);

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'section_id' => 'required|exists:course_sections,id',
            'module_type' => 'required|in:lesson,video,resource,quiz,assignment,programming_challenge,forum,live_session',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_visible' => 'nullable|boolean',
            'is_required' => 'nullable|boolean',
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date|after:available_from',
            'is_graded' => 'nullable|boolean',
            'max_score' => 'nullable|numeric|min:0',
            'completion_type' => 'required|in:auto,manual,score_based',
            'estimated_duration' => 'nullable|integer|min:0',
            'attempts_allowed' => 'nullable|integer|min:1',
            'time_limit' => 'nullable|integer|min:0',
            // New video creation fields
            'video_source_type' => 'nullable|in:existing,new',
            'new_video_url' => 'required_if:video_source_type,new|nullable|url',
            'new_video_title' => 'required_if:video_source_type,new|nullable|string|max:255',
            'new_video_description' => 'nullable|string',
            'new_video_type' => 'nullable|in:youtube,vimeo,external',
            // Resource creation fields
            'resource_source_type' => 'required_without:modulable_id_resource|nullable|in:file,url',
            'resource_file' => 'required_if:resource_source_type,file|nullable|file|max:51200',
            'resource_url' => 'required_if:resource_source_type,url|nullable|url|max:500',
            'resource_type' => 'required_without:modulable_id_resource|nullable|in:pdf,doc,ppt,excel,image,audio,archive,other',
            'modulable_id_resource' => 'nullable|exists:resources,id',
        ]);

        DB::beginTransaction();
        try {
            // Determine modulable_id and modulable_type based on module_type
            $modulableTypes = [
                'lesson' => Lesson::class,
                'video' => Video::class,
                'resource' => Resource::class,
            ];

            $modulableId = null;
            $modulableType = null;

            if (isset($modulableTypes[$validated['module_type']])) {
                $modulableType = $modulableTypes[$validated['module_type']];

                // Handle video creation from URL
                if ($validated['module_type'] == 'video' && $request->filled('new_video_url')) {
                    // Create new video from URL
                    $videoData = [
                        'title' => $request->input('new_video_title'),
                        'description' => $request->input('new_video_description'),
                        'video_type' => $request->input('new_video_type', 'youtube'),
                        'video_url' => $request->input('new_video_url'),
                        'is_published' => true,
                        'is_visible' => true,
                        'processing_status' => 'completed',
                        'created_by' => auth()->id(),
                    ];

                    $video = Video::create($videoData);
                    $modulableId = $video->id;
                } elseif ($validated['module_type'] == 'resource') {
                    // Check if using existing resource
                    if ($request->filled('modulable_id_resource')) {
                        // Use existing resource
                        $modulableId = $request->modulable_id_resource;
                    } elseif ($request->filled('resource_source_type')) {
                        // Create new resource from file or URL
                        $resourceData = [
                            'title' => $validated['title'],
                            'description' => $validated['description'] ?? null,
                            'resource_type' => $request->input('resource_type'),
                            'course_id' => $request->input('course_id'),
                            'is_published' => true,
                            'is_visible' => true,
                            'allow_download' => true,
                            'created_by' => auth()->id(),
                            'download_count' => 0,
                        ];

                        if ($request->resource_source_type === 'file' && $request->hasFile('resource_file')) {
                            $file = $request->file('resource_file');
                            $resourceData['file_path'] = $file->store('resources', 'public');
                            $resourceData['file_name'] = $file->getClientOriginalName();
                            $resourceData['file_size'] = $file->getSize();
                            $resourceData['mime_type'] = $file->getMimeType();
                            $resourceData['resource_source'] = 'file';
                            $resourceData['resource_url'] = null;
                        } elseif ($request->resource_source_type === 'url' && $request->filled('resource_url')) {
                            $resourceData['resource_url'] = $request->input('resource_url');
                            $resourceData['resource_source'] = 'url';
                            $resourceData['file_name'] = basename(parse_url($request->input('resource_url'), PHP_URL_PATH));
                            $resourceData['file_path'] = null;
                            $resourceData['file_size'] = null;
                            $resourceData['mime_type'] = null;
                        } else {
                            throw new \Exception('يجب اختيار ملف للرفع أو إدخال رابط خارجي');
                        }

                        $resource = Resource::create($resourceData);
                        $modulableId = $resource->id;
                    }
                } elseif ($validated['module_type'] == 'lesson' && $request->filled('modulable_id_lesson')) {
                    $modulableId = $request->modulable_id_lesson;
                } elseif ($validated['module_type'] == 'video' && $request->filled('modulable_id_video')) {
                    $modulableId = $request->modulable_id_video;
                }

                // IMPORTANT: Must have modulable_id for these types
                if (!$modulableId) {
                    $errorMessage = 'يجب اختيار محتوى للدرس';
                    if ($validated['module_type'] == 'resource') {
                        $errorMessage .= ' (اختر مورد موجود، أو ارفع ملف، أو أدخل رابط خارجي)';
                    } elseif ($validated['module_type'] == 'video') {
                        $errorMessage .= ' (اختر فيديو موجود أو أنشئ فيديو جديد من رابط)';
                    } else {
                        $errorMessage .= ' (درس نصي، فيديو، أو مورد)';
                    }
                    throw new \Exception($errorMessage);
                }

                // Verify that modulable exists
                $modulable = $modulableType::find($modulableId);
                if (!$modulable) {
                    throw new \Exception('المحتوى المحدد غير موجود');
                }
            }

            // Prepare data for creation
            $moduleData = [
                'course_id' => $validated['course_id'],
                'section_id' => $validated['section_id'],
                'module_type' => $validated['module_type'],
                'modulable_id' => $modulableId,
                'modulable_type' => $modulableType,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'is_visible' => $request->has('is_visible'),
                'is_required' => $request->has('is_required'),
                'is_graded' => $request->has('is_graded'),
                'available_from' => $validated['available_from'] ?? null,
                'available_until' => $validated['available_until'] ?? null,
                'max_score' => $validated['max_score'] ?? null,
                'completion_type' => $validated['completion_type'],
                'estimated_duration' => $validated['estimated_duration'] ?? null,
                'attempts_allowed' => $validated['attempts_allowed'] ?? null,
                'time_limit' => $validated['time_limit'] ?? null,
            ];

            // Set sort_order
            $maxOrder = CourseModule::where('section_id', $validated['section_id'])->max('sort_order') ?? 0;
            $moduleData['sort_order'] = $maxOrder + 1;

            $module = CourseModule::create($moduleData);

            DB::commit();

            return redirect()
                ->route('courses.show', $section->course_id)
                ->with('success', 'تم إنشاء الدرس بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete uploaded file if exists and resource creation failed
            if ($request->hasFile('resource_file')) {
                try {
                    $file = $request->file('resource_file');
                    $filePath = 'resources/' . $file->hashName();
                    if (Storage::disk('public')->exists($filePath)) {
                        Storage::disk('public')->delete($filePath);
                    }
                } catch (\Exception $fileException) {
                    // Ignore file deletion errors
                }
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء الدرس: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified module.
     */
    public function show($sectionId, $id)
    {
        try {
            $module = CourseModule::with([
                'course',
                'section',
                'modulable',
                'completions.student',
                'accessRestrictions'
            ])->where('section_id', $sectionId)->findOrFail($id);

            // Get statistics
            $stats = [
                'total_completions' => $module->completions()->count(),
                'completed_count' => $module->completions()->where('completion_status', 'completed')->count(),
                'in_progress_count' => $module->completions()->where('completion_status', 'in_progress')->count(),
                'average_score' => $module->completions()->avg('score') ?? 0,
                'completion_rate' => $module->completions()->count() > 0
                    ? ($module->completions()->where('completion_status', 'completed')->count() / $module->completions()->count() * 100)
                    : 0,
            ];

            return view('admin.modules.show', compact('module', 'stats'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تحميل الوحدة: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified module.
     */
    public function edit($id)
    {
        try {
            $module = CourseModule::with(['course', 'section', 'modulable'])->findOrFail($id);

            $courses = Course::all();
            $sections = CourseSection::where('course_id', $module->course_id)->get();
            $moduleTypes = ['lesson', 'video', 'resource', 'quiz', 'assignment'];
            $completionTypes = ['manual', 'automatic', 'grade'];

            // Get available content based on type
            $lessons = Lesson::all();
            $videos = Video::all();
            $resources = Resource::all();

            return view('admin.course-modules.edit', compact(
                'module',
                'courses',
                'sections',
                'moduleTypes',
                'completionTypes',
                'lessons',
                'videos',
                'resources'
            ));
        } catch (\Exception $e) {
            if ($module && $module->section_id) {
                return redirect()
                    ->route('courses.show', $module->course_id)
                    ->with('error', 'حدث خطأ أثناء تحميل نموذج التعديل: ' . $e->getMessage());
            }
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تحميل نموذج التعديل: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified module in storage.
     */
    public function update(Request $request, $id)
    {
        $module = CourseModule::findOrFail($id);

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'section_id' => 'required|exists:course_sections,id',
            'module_type' => 'required|in:lesson,video,resource,quiz,assignment,question_module',
            'modulable_id' => 'nullable|integer', // Make nullable for quiz, assignment, question_module
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_visible' => 'nullable|boolean',
            'is_required' => 'nullable|boolean',
            'unlock_conditions' => 'nullable|json',
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date|after:available_from',
            'is_graded' => 'nullable|boolean',
            'max_score' => 'nullable|numeric|min:0',
            'completion_type' => 'required|in:manual,automatic,grade',
            'estimated_duration' => 'nullable|integer|min:0',
            'attempts_allowed' => 'nullable|integer|min:1',
            'time_limit' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Determine modulable_type based on module_type
            $modulableTypes = [
                'lesson' => Lesson::class,
                'video' => Video::class,
                'resource' => Resource::class,
            ];

            $validated['modulable_type'] = $modulableTypes[$validated['module_type']] ?? $module->modulable_type;

            // Only require modulable_id for types that need it
            if ($validated['modulable_type']) {
                // Use existing modulable_id if not provided
                if (!isset($validated['modulable_id']) || !$validated['modulable_id']) {
                    $validated['modulable_id'] = $module->modulable_id;
                }
                
                // Verify that modulable exists if changed
                if ($validated['modulable_id'] != $module->modulable_id || $validated['modulable_type'] != $module->modulable_type) {
                    $modulable = $validated['modulable_type']::find($validated['modulable_id']);
                    if (!$modulable) {
                        throw new \Exception('المحتوى المحدد غير موجود');
                    }
                }
            } else {
                // For types that don't need modulable (quiz, assignment, question_module)
                $validated['modulable_id'] = $module->modulable_id;
                $validated['modulable_type'] = $module->modulable_type;
            }

            // Convert boolean fields
            $validated['is_visible'] = $request->has('is_visible');
            $validated['is_required'] = $request->has('is_required');
            $validated['is_graded'] = $request->has('is_graded');

            // Decode unlock_conditions if present
            if ($request->filled('unlock_conditions')) {
                $validated['unlock_conditions'] = json_decode($validated['unlock_conditions'], true);
            }

            $module->update($validated);

            DB::commit();

            return redirect()
                ->route('courses.show', $module->course_id)
                ->with('success', 'تم تحديث الوحدة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث الوحدة: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified module from storage (soft delete).
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $module = CourseModule::findOrFail($id);

            // Check if module has completions
            $completionsCount = $module->completions()->count();
            if ($completionsCount > 0) {
                return redirect()
                    ->back()
                    ->with('warning', "تحذير: هذه الوحدة لديها {$completionsCount} سجل إتمام. سيتم حذف الوحدة ولكن سيتم الاحتفاظ بسجلات الإتمام.");
            }

            $module->delete();

            DB::commit();

            $courseId = $module->course_id;
            return redirect()
                ->route('courses.show', $courseId)
                ->with('success', 'تم حذف الوحدة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء حذف الوحدة: ' . $e->getMessage());
        }
    }

    /**
     * Duplicate a module.
     */
    public function duplicate($id)
    {
        DB::beginTransaction();
        try {
            $originalModule = CourseModule::findOrFail($id);

            // Create duplicate
            $newModule = $originalModule->replicate();
            $newModule->title = $originalModule->title . ' (نسخة)';
            $newModule->is_visible = false;
            $newModule->sort_order = CourseModule::where('section_id', $originalModule->section_id)->max('sort_order') + 1;
            $newModule->save();

            DB::commit();

            return redirect()
                ->route('courses.show', $newModule->course_id)
                ->with('success', 'تم نسخ الوحدة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء نسخ الوحدة: ' . $e->getMessage());
        }
    }

    /**
     * Toggle module visibility.
     */
    public function toggleVisibility($id)
    {
        try {
            $module = CourseModule::findOrFail($id);
            $module->is_visible = !$module->is_visible;
            $module->save();

            $status = $module->is_visible ? 'مرئية' : 'مخفية';

            return redirect()
                ->back()
                ->with('success', "تم تحديث حالة الظهور إلى: {$status}");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تحديث حالة الظهور: ' . $e->getMessage());
        }
    }

    /**
     * Update module order.
     */
    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'modules' => 'required|array',
            'modules.*.id' => 'required|exists:course_modules,id',
            'modules.*.sort_order' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['modules'] as $moduleData) {
                CourseModule::where('id', $moduleData['id'])->update([
                    'sort_order' => $moduleData['sort_order']
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث ترتيب الوحدات بنجاح'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الترتيب: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sections by course (AJAX).
     */
    public function getSectionsByCourse(Request $request)
    {
        try {
            $courseId = $request->input('course_id');
            $sections = CourseSection::where('course_id', $courseId)
                ->orderBy('sort_order')
                ->get(['id', 'title']);

            return response()->json([
                'success' => true,
                'sections' => $sections
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }
}
