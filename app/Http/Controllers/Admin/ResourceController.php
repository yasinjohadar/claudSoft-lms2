<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use App\Models\CourseModule;
use App\Models\CourseSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ResourceController extends Controller
{
    /**
     * Display a listing of the resources.
     */
    public function index(Request $request)
    {
        try {
            $query = Resource::with(['creator', 'updater', 'courseModules.course']);

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('file_name', 'like', "%{$search}%");
                });
            }

            // Filter by resource type (support both 'type' and 'resource_type' for compatibility)
            $typeFilter = $request->input('type') ?? $request->input('resource_type');
            if (!empty($typeFilter)) {
                // Map common file type names to resource_type values
                $typeMapping = [
                    'zip' => 'archive',
                    'rar' => 'archive',
                    '7z' => 'archive',
                    'xls' => 'excel',
                    'xlsx' => 'excel',
                ];
                
                $resourceType = $typeMapping[$typeFilter] ?? $typeFilter;
                $query->where('resource_type', $resourceType);
            }
            
            // Filter by course
            if ($request->filled('course_id')) {
                $courseId = $request->course_id;
                // Resources are linked to courses through course_modules
                $query->whereHas('courseModules', function($q) use ($courseId) {
                    $q->where('course_id', $courseId);
                });
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

            $resources = $query->paginate($request->get('per_page', 15));

            // Get filter options
            $resourceTypes = ['pdf', 'doc', 'ppt', 'excel', 'image', 'audio', 'archive', 'other'];

            // Get statistics
            $totalResources = Resource::count();
            $totalDownloads = Resource::sum('download_count') ?? 0;
            $pdfCount = Resource::where('resource_type', 'pdf')->count();
            $totalSize = Resource::sum('file_size') ?? 0;

            // Get courses for filter
            $courses = \App\Models\Course::select('id', 'title')->get();

            return view('admin.pages.resources.index', compact(
                'resources',
                'resourceTypes',
                'totalResources',
                'totalDownloads',
                'pdfCount',
                'totalSize',
                'courses'
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحميل الملفات: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        try {
            $resourceTypes = ['pdf', 'doc', 'ppt', 'excel', 'image', 'audio', 'archive', 'other'];
            $courses = \App\Models\Course::select('id', 'title')->get();
            
            // Get all existing resources for selection
            $existingResources = Resource::select('id', 'title', 'resource_type', 'resource_source')
                ->where('is_published', true)
                ->orderBy('title')
                ->get();
            
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

            return view('admin.pages.resources.create', compact('resourceTypes', 'courses', 'section', 'course', 'sectionId', 'courseId', 'existingResources'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحميل نموذج الإنشاء: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Convert checkbox values to boolean before validation
        $request->merge([
            'is_published' => $request->has('is_published'),
            'is_visible' => $request->has('is_visible'),
            'allow_download' => $request->has('allow_download'),
            'preview_available' => $request->has('preview_available'),
        ]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'resource_type' => 'required|in:pdf,doc,ppt,excel,image,audio,archive,other',
            'resource_source' => 'required|in:file,url,existing',
            'file' => 'required_if:resource_source,file|nullable|file|max:51200',
            'resource_url' => 'required_if:resource_source,url|nullable|url|max:500',
            'course_id' => 'nullable|exists:courses,id',
            'section_id' => 'nullable|exists:course_sections,id',
            'is_published' => 'sometimes|boolean',
            'is_visible' => 'sometimes|boolean',
            'allow_download' => 'sometimes|boolean',
            'preview_available' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date|after:available_from',
        ], [
            'resource_type.required' => 'يرجى اختيار نوع المورد',
            'resource_type.in' => 'نوع المورد غير صحيح',
            'resource_source.required' => 'يرجى اختيار مصدر المورد',
            'resource_source.in' => 'مصدر المورد غير صحيح',
            'file.required_if' => 'يرجى اختيار ملف للرفع',
            'resource_url.required_if' => 'يرجى إدخال رابط المورد',
            'resource_url.url' => 'الرابط المدخل غير صحيح',
            'existing_resource_id.required_if' => 'يرجى اختيار مورد موجود',
            'existing_resource_id.exists' => 'المورد المحدد غير موجود',
        ]);

        DB::beginTransaction();
        try {
            $validated['resource_source'] = $request->resource_source ?? 'file';

            // Handle file upload, URL, or existing resource
            if ($request->resource_source === 'file') {
                if (!$request->hasFile('file')) {
                    throw new \Exception('لم يتم اختيار ملف للرفع');
                }
                $file = $request->file('file');
                $validated['file_path'] = $file->store('resources', 'public');
                $validated['file_name'] = $file->getClientOriginalName();
                $validated['file_size'] = $file->getSize();
                $validated['mime_type'] = $file->getMimeType();
                $validated['resource_url'] = null;
            } elseif ($request->resource_source === 'url') {
                if (empty($request->resource_url)) {
                    throw new \Exception('لم يتم إدخال رابط المورد');
                }
                $validated['resource_url'] = $request->resource_url;
                $validated['file_path'] = null;
                $validated['file_name'] = null;
                $validated['file_size'] = null;
                $validated['mime_type'] = null;
            } elseif ($request->resource_source === 'existing') {
                // Use existing resource - get the resource and use its data
                $existingResource = Resource::findOrFail($request->existing_resource_id);
                $validated['file_path'] = $existingResource->file_path;
                $validated['file_name'] = $existingResource->file_name;
                $validated['file_size'] = $existingResource->file_size;
                $validated['mime_type'] = $existingResource->mime_type;
                $validated['resource_url'] = $existingResource->resource_url;
                $validated['resource_type'] = $existingResource->resource_type;
                // Don't create a new resource, use the existing one
                $resource = $existingResource;
            } else {
                throw new \Exception('مصدر المورد غير صحيح');
            }

            // Boolean fields are already converted in validation step above

            // If using existing resource, don't create a new one
            if ($request->resource_source === 'existing') {
                // Resource is already set in the if-else block above
                // Just update title and description if provided
                if ($request->filled('title')) {
                    $resource->title = $request->title;
                }
                if ($request->filled('description')) {
                    $resource->description = $request->description;
                }
                $resource->save();
            } else {
                // Set creator for new resource
                $validated['created_by'] = auth()->id();
                $validated['download_count'] = 0;
                $resource = Resource::create($validated);
            }

            // If section_id is provided, create a module automatically
            if ($request->filled('section_id')) {
                $section = CourseSection::findOrFail($request->section_id);
                
                // Get next sort_order
                $maxOrder = CourseModule::where('section_id', $section->id)->max('sort_order') ?? 0;
                
                // Create module
                $module = CourseModule::create([
                    'course_id' => $section->course_id,
                    'section_id' => $section->id,
                    'module_type' => 'resource',
                    'modulable_id' => $resource->id,
                    'modulable_type' => Resource::class,
                    'title' => $resource->title,
                    'description' => $resource->description,
                    'sort_order' => $maxOrder + 1,
                    'is_visible' => true,
                    'is_required' => false,
                    'is_graded' => false,
                    'completion_type' => 'auto',
                ]);
                
                DB::commit();
                
                return redirect()
                    ->route('courses.show', $section->course_id)
                    ->with('success', 'تم إنشاء المورد وربطه بالقسم بنجاح');
            }

            DB::commit();

            return redirect()
                ->route('resources.show', $resource->id)
                ->with('success', 'تم إنشاء المورد بنجاح');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            
            // Delete uploaded file if exists
            if (isset($validated['file_path'])) {
                Storage::disk('public')->delete($validated['file_path']);
            }
            
            throw $e; // Re-throw validation exception to show validation errors
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete uploaded file if exists
            if (isset($validated['file_path'])) {
                Storage::disk('public')->delete($validated['file_path']);
            }

            \Log::error('Resource creation error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء المورد: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $resource = Resource::with(['creator', 'updater', 'courseModules.course'])->findOrFail($id);

            // Get statistics
            $stats = [
                'used_in_modules' => $resource->courseModules()->count(),
                'download_count' => $resource->download_count,
                'formatted_size' => $resource->getFormattedFileSize(),
                'extension' => $resource->getFileExtension(),
                'can_preview' => $resource->canPreview(),
            ];

            return view('admin.pages.resources.show', compact('resource', 'stats'));
        } catch (\Exception $e) {
            return redirect()
                ->route('resources.index')
                ->with('error', 'حدث خطأ أثناء تحميل الملف: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $resource = Resource::findOrFail($id);
            $resourceTypes = ['pdf', 'doc', 'ppt', 'excel', 'image', 'audio', 'archive', 'other'];
            $courses = \App\Models\Course::select('id', 'title')->get();

            return view('admin.pages.resources.edit', compact('resource', 'resourceTypes', 'courses'));
        } catch (\Exception $e) {
            return redirect()
                ->route('resources.index')
                ->with('error', 'حدث خطأ أثناء تحميل نموذج التعديل: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $resource = Resource::findOrFail($id);

        // Convert checkbox values to boolean before validation
        $request->merge([
            'is_published' => $request->has('is_published'),
            'is_visible' => $request->has('is_visible'),
            'allow_download' => $request->has('allow_download'),
            'preview_available' => $request->has('preview_available'),
        ]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'resource_type' => 'required|in:pdf,doc,ppt,excel,image,audio,archive,other',
            'resource_source' => 'required|in:file,url,existing',
            'file' => 'required_if:resource_source,file|nullable|file|max:51200',
            'resource_url' => 'required_if:resource_source,url|nullable|url|max:500',
            'course_id' => 'nullable|exists:courses,id',
            'section_id' => 'nullable|exists:course_sections,id',
            'is_published' => 'sometimes|boolean',
            'is_visible' => 'sometimes|boolean',
            'allow_download' => 'sometimes|boolean',
            'preview_available' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date|after:available_from',
        ], [
            'resource_type.required' => 'يرجى اختيار نوع المورد',
            'resource_type.in' => 'نوع المورد غير صحيح',
            'resource_source.required' => 'يرجى اختيار مصدر المورد',
            'resource_source.in' => 'مصدر المورد غير صحيح',
            'file.required_if' => 'يرجى اختيار ملف للرفع',
            'resource_url.required_if' => 'يرجى إدخال رابط المورد',
            'resource_url.url' => 'الرابط المدخل غير صحيح',
            'existing_resource_id.required_if' => 'يرجى اختيار مورد موجود',
            'existing_resource_id.exists' => 'المورد المحدد غير موجود',
        ]);

        DB::beginTransaction();
        try {
            $validated['resource_source'] = $request->resource_source ?? $resource->resource_source ?? 'file';

            // Handle file upload or URL
            if ($request->resource_source === 'file' && $request->hasFile('file')) {
                // Delete old file if exists
                if ($resource->file_path) {
                    Storage::disk('public')->delete($resource->file_path);
                }

                $file = $request->file('file');
                $validated['file_path'] = $file->store('resources', 'public');
                $validated['file_name'] = $file->getClientOriginalName();
                $validated['file_size'] = $file->getSize();
                $validated['mime_type'] = $file->getMimeType();
                $validated['resource_url'] = null;
            } elseif ($request->resource_source === 'url') {
                $validated['resource_url'] = $request->resource_url;
                
                // Delete old file if switching from file to URL
                if ($resource->file_path) {
                    Storage::disk('public')->delete($resource->file_path);
                }
                
                $validated['file_path'] = null;
                $validated['file_name'] = null;
                $validated['file_size'] = null;
                $validated['mime_type'] = null;
            } else {
                // Keep existing file if not changing source type
                if ($resource->resource_source === 'file') {
                    unset($validated['file_path'], $validated['file_name'], $validated['file_size'], $validated['mime_type']);
                } else {
                    $validated['resource_url'] = $request->resource_url ?? $resource->resource_url;
                }
            }

            // Boolean fields are already converted in validation step above
            $validated['is_published'] = $validated['is_published'] ?? false;
            $validated['is_visible'] = $validated['is_visible'] ?? false;
            $validated['allow_download'] = $validated['allow_download'] ?? false;
            $validated['preview_available'] = $validated['preview_available'] ?? false;

            // Set updater
            $validated['updated_by'] = auth()->id();

            $resource->update($validated);

            DB::commit();

            return redirect()
                ->route('resources.show', $resource->id)
                ->with('success', 'تم تحديث المورد بنجاح');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            
            throw $e; // Re-throw validation exception to show validation errors
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Resource update error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'resource_id' => $id
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث المورد: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $resource = Resource::findOrFail($id);

            // Check if resource is used in any course modules
            $usedInModules = $resource->courseModules()->count();
            if ($usedInModules > 0) {
                $message = "لا يمكن حذف الملف لأنه مستخدم في {$usedInModules} وحدة دراسية";
                
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ]);
                }
                
                return redirect()
                    ->back()
                    ->with('error', $message);
            }

            // Delete file
            if ($resource->file_path) {
                Storage::disk('public')->delete($resource->file_path);
            }

            $resource->delete();

            DB::commit();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم حذف المورد بنجاح'
                ]);
            }

            return redirect()
                ->route('resources.index')
                ->with('success', 'تم حذف المورد بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Resource deletion error: ' . $e->getMessage(), [
                'exception' => $e,
                'resource_id' => $id
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء حذف المورد: ' . $e->getMessage()
                ]);
            }

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء حذف المورد: ' . $e->getMessage());
        }
    }

    /**
     * Duplicate a resource.
     */
    public function duplicate($id)
    {
        DB::beginTransaction();
        try {
            $originalResource = Resource::findOrFail($id);

            // Create duplicate
            $newResource = $originalResource->replicate();
            $newResource->title = $originalResource->title . ' (نسخة)';
            $newResource->is_published = false;
            $newResource->created_by = auth()->id();
            $newResource->updated_by = null;
            $newResource->download_count = 0;

            // Copy file
            if ($originalResource->file_path && Storage::disk('public')->exists($originalResource->file_path)) {
                $newPath = 'resources/' . uniqid() . '_' . basename($originalResource->file_path);
                Storage::disk('public')->copy($originalResource->file_path, $newPath);
                $newResource->file_path = $newPath;
            }

            $newResource->save();

            DB::commit();

            return redirect()
                ->route('admin.resources.show', $newResource->id)
                ->with('success', 'تم نسخ الملف بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء نسخ الملف: ' . $e->getMessage());
        }
    }

    /**
     * Toggle resource publish status.
     */
    public function togglePublish($id)
    {
        try {
            $resource = Resource::findOrFail($id);
            $resource->is_published = !$resource->is_published;
            $resource->updated_by = auth()->id();
            $resource->save();

            $status = $resource->is_published ? 'منشور' : 'مسودة';

            return redirect()
                ->back()
                ->with('success', "تم تحديث حالة النشر إلى: {$status}");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تحديث حالة النشر: ' . $e->getMessage());
        }
    }

    /**
     * Toggle resource visibility.
     */
    public function toggleVisibility($id)
    {
        try {
            $resource = Resource::findOrFail($id);
            $resource->is_visible = !$resource->is_visible;
            $resource->updated_by = auth()->id();
            $resource->save();

            $status = $resource->is_visible ? 'مرئي' : 'مخفي';

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
     * Download resource file.
     */
    public function download($id)
    {
        try {
            $resource = Resource::findOrFail($id);

            if (!$resource->allow_download) {
                return redirect()
                    ->back()
                    ->with('error', 'التحميل غير مسموح لهذا الملف');
            }

            if (!Storage::disk('public')->exists($resource->file_path)) {
                return redirect()
                    ->back()
                    ->with('error', 'الملف غير موجود');
            }

            // Increment download count
            $resource->incrementDownloadCount();

            return Storage::disk('public')->download($resource->file_path, $resource->file_name);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تحميل الملف: ' . $e->getMessage());
        }
    }

    /**
     * Preview resource file.
     */
    public function preview($id)
    {
        try {
            $resource = Resource::findOrFail($id);

            if (!$resource->canPreview()) {
                return redirect()
                    ->back()
                    ->with('error', 'المعاينة غير متاحة لهذا الملف');
            }

            if (!Storage::disk('public')->exists($resource->file_path)) {
                return redirect()
                    ->back()
                    ->with('error', 'الملف غير موجود');
            }

            return response()->file(Storage::disk('public')->path($resource->file_path));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء معاينة الملف: ' . $e->getMessage());
        }
    }
}
