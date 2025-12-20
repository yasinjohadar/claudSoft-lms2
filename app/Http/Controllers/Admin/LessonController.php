<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LessonController extends Controller
{
    /**
     * Display a listing of the lessons.
     */
    public function index(Request $request)
    {
        try {
            // Check if module_id is provided
            if (!$request->filled('module')) {
                return redirect()->route('lessons.all');
            }

            $module = \App\Models\CourseModule::with(['section.course'])->findOrFail($request->module);

            $query = Lesson::with(['module', 'creator', 'updater'])
                ->whereHas('module', function($q) use ($module) {
                    $q->where('id', $module->id);
                });

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%");
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
            $sortBy = $request->get('sort_by', 'sort_order');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            $lessons = $query->get();
            $totalDuration = $lessons->sum('reading_time');

            return view('admin.pages.lessons.index', compact('lessons', 'module', 'totalDuration'));
        } catch (\Exception $e) {
            return redirect()->route('admin.dashboard')->with('error', 'حدث خطأ أثناء تحميل الدروس: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new lesson.
     */
    public function create(Request $request)
    {
        try {
            $module = null;
            
            // Get module if provided
            if ($request->filled('module')) {
                $module = \App\Models\CourseModule::with(['section.course'])->find($request->module);
            }

            return view('admin.pages.lessons.create', compact('module'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحميل نموذج الإنشاء: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created lesson in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'required|string',
            'objectives' => 'nullable|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
            'is_published' => 'nullable|boolean',
            'is_visible' => 'nullable|boolean',
            'allow_comments' => 'nullable|boolean',
            'reading_time' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date|after:available_from',
        ]);

        DB::beginTransaction();
        try {
            // Handle attachments upload
            $attachmentsPaths = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('lessons/attachments', 'public');
                    $attachmentsPaths[] = [
                        'path' => $path,
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'type' => $file->getMimeType(),
                    ];
                }
            }

            $validated['attachments'] = $attachmentsPaths;

            // Convert boolean fields
            $validated['is_published'] = $request->has('is_published');
            $validated['is_visible'] = $request->has('is_visible');
            $validated['allow_comments'] = $request->has('allow_comments');

            // Set creator
            $validated['created_by'] = auth()->id();

            $lesson = Lesson::create($validated);

            DB::commit();

            return redirect()
                ->route('admin.lessons.show', $lesson->id)
                ->with('success', 'تم إنشاء الدرس بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete uploaded files if exists
            if (!empty($attachmentsPaths)) {
                foreach ($attachmentsPaths as $attachment) {
                    Storage::disk('public')->delete($attachment['path']);
                }
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء الدرس: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified lesson.
     */
    public function show($id)
    {
        try {
            $lesson = Lesson::with(['creator', 'updater', 'courseModules.course'])->findOrFail($id);

            // Get statistics
            $stats = [
                'used_in_modules' => $lesson->courseModules()->count(),
                'word_count' => str_word_count(strip_tags($lesson->content)),
                'estimated_reading_time' => $lesson->getEstimatedReadingTime(),
                'attachments_count' => $lesson->getAttachmentsCount(),
            ];

            return view('admin.pages.lessons.show', compact('lesson', 'stats'));
        } catch (\Exception $e) {
            return redirect()
                ->route('lessons.all')
                ->with('error', 'حدث خطأ أثناء تحميل الدرس: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified lesson.
     */
    public function edit($id)
    {
        try {
            $lesson = Lesson::findOrFail($id);

            return view('admin.pages.lessons.edit', compact('lesson'));
        } catch (\Exception $e) {
            return redirect()
                ->route('lessons.all')
                ->with('error', 'حدث خطأ أثناء تحميل نموذج التعديل: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified lesson in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $lesson = Lesson::findOrFail($id);

            // Handle boolean fields before validation
            $request->merge([
                'is_published' => $request->has('is_published'),
                'is_visible' => $request->has('is_visible'),
                'allow_comments' => $request->has('allow_comments'),
            ]);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'content' => 'nullable|string',
                'objectives' => 'nullable|string',
                'new_attachments' => 'nullable|array',
                'new_attachments.*' => 'file|max:10240',
                'remove_attachments' => 'nullable|array',
                'is_published' => 'sometimes|boolean',
                'is_visible' => 'sometimes|boolean',
                'allow_comments' => 'sometimes|boolean',
                'reading_time' => 'nullable|integer|min:0',
                'sort_order' => 'nullable|integer|min:0',
                'available_from' => 'nullable|date',
                'available_until' => 'nullable|date|after:available_from',
            ], [
                'title.required' => 'عنوان الدرس مطلوب',
                'title.max' => 'عنوان الدرس يجب ألا يتجاوز 255 حرف',
                'available_until.after' => 'تاريخ الانتهاء يجب أن يكون بعد تاريخ البدء',
            ]);

            DB::beginTransaction();
            $existingAttachments = $lesson->attachments ?? [];

            // Remove selected attachments
            if ($request->filled('remove_attachments')) {
                foreach ($request->remove_attachments as $index) {
                    if (isset($existingAttachments[$index])) {
                        Storage::disk('public')->delete($existingAttachments[$index]['path']);
                        unset($existingAttachments[$index]);
                    }
                }
                $existingAttachments = array_values($existingAttachments);
            }

            // Add new attachments
            if ($request->hasFile('new_attachments')) {
                foreach ($request->file('new_attachments') as $file) {
                    $path = $file->store('lessons/attachments', 'public');
                    $existingAttachments[] = [
                        'path' => $path,
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'type' => $file->getMimeType(),
                    ];
                }
            }

            $validated['attachments'] = $existingAttachments;

            // Set updater
            $validated['updated_by'] = auth()->id();

            $lesson->update($validated);

            DB::commit();

            // Redirect back to edit page or to lessons list
            $module = $lesson->courseModules()->first();
            if ($module) {
                return redirect()
                    ->route('lessons.index', ['module' => $module->id])
                    ->with('success', 'تم تحديث الدرس بنجاح');
            }

            return redirect()
                ->route('lessons.all')
                ->with('success', 'تم تحديث الدرس بنجاح');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'يرجى التحقق من البيانات المدخلة');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating lesson: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث الدرس: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified lesson from storage (soft delete).
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $lesson = Lesson::findOrFail($id);

            // Check if lesson is used in any course modules
            $usedInModules = $lesson->courseModules()->count();
            if ($usedInModules > 0) {
                return redirect()
                    ->back()
                    ->with('error', "لا يمكن حذف الدرس لأنه مستخدم في {$usedInModules} وحدة دراسية");
            }

            // Delete attachments
            if ($lesson->attachments) {
                foreach ($lesson->attachments as $attachment) {
                    Storage::disk('public')->delete($attachment['path']);
                }
            }

            $lesson->delete();

            DB::commit();

            $module = $lesson->courseModules()->first();
            if ($module) {
                return redirect()
                    ->route('lessons.index', ['module' => $module->id])
                    ->with('success', 'تم حذف الدرس بنجاح');
            }

            return redirect()
                ->route('lessons.all')
                ->with('success', 'تم حذف الدرس بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء حذف الدرس: ' . $e->getMessage());
        }
    }

    /**
     * Reorder lessons (drag & drop).
     */
    public function reorder(Request $request, $moduleId)
    {
        try {
            $validated = $request->validate([
                'order' => 'required|array',
                'order.*.id' => 'required|exists:lessons,id',
                'order.*.order' => 'required|integer|min:1',
            ]);

            DB::beginTransaction();

            foreach ($validated['order'] as $item) {
                Lesson::where('id', $item['id'])
                    ->whereHas('module', function($q) use ($moduleId) {
                        $q->where('id', $moduleId);
                    })
                    ->update(['sort_order' => $item['order']]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إعادة ترتيب الدروس بنجاح'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error reordering lessons: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إعادة الترتيب: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplicate a lesson.
     */
    public function duplicate($id)
    {
        DB::beginTransaction();
        try {
            $originalLesson = Lesson::findOrFail($id);

            // Create duplicate
            $newLesson = $originalLesson->replicate();
            $newLesson->title = $originalLesson->title . ' (نسخة)';
            $newLesson->is_published = false;
            $newLesson->created_by = auth()->id();
            $newLesson->updated_by = null;

            // Copy attachments
            if ($originalLesson->attachments) {
                $newAttachments = [];
                foreach ($originalLesson->attachments as $attachment) {
                    if (Storage::disk('public')->exists($attachment['path'])) {
                        $newPath = 'lessons/attachments/' . uniqid() . '_' . basename($attachment['path']);
                        Storage::disk('public')->copy($attachment['path'], $newPath);
                        $newAttachments[] = [
                            'path' => $newPath,
                            'name' => $attachment['name'],
                            'size' => $attachment['size'],
                            'type' => $attachment['type'],
                        ];
                    }
                }
                $newLesson->attachments = $newAttachments;
            }

            $newLesson->save();

            DB::commit();

            return redirect()
                ->route('admin.lessons.show', $newLesson->id)
                ->with('success', 'تم نسخ الدرس بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء نسخ الدرس: ' . $e->getMessage());
        }
    }

    /**
     * Toggle lesson publish status.
     */
    public function togglePublish($id)
    {
        try {
            $lesson = Lesson::findOrFail($id);
            $lesson->is_published = !$lesson->is_published;
            $lesson->updated_by = auth()->id();
            $lesson->save();

            $status = $lesson->is_published ? 'منشور' : 'مسودة';

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
     * Toggle lesson visibility.
     */
    public function toggleVisibility($id)
    {
        try {
            $lesson = Lesson::findOrFail($id);
            $lesson->is_visible = !$lesson->is_visible;
            $lesson->updated_by = auth()->id();
            $lesson->save();

            $status = $lesson->is_visible ? 'مرئي' : 'مخفي';

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
     * Download lesson attachment.
     */
    public function downloadAttachment($id, $index)
    {
        try {
            $lesson = Lesson::findOrFail($id);

            if (!isset($lesson->attachments[$index])) {
                return redirect()
                    ->back()
                    ->with('error', 'المرفق غير موجود');
            }

            $attachment = $lesson->attachments[$index];

            if (!Storage::disk('public')->exists($attachment['path'])) {
                return redirect()
                    ->back()
                    ->with('error', 'الملف غير موجود');
            }

            return Storage::disk('public')->download($attachment['path'], $attachment['name']);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تحميل المرفق: ' . $e->getMessage());
        }
    }

    /**
     * Display all lessons from all courses.
     */
    public function allLessons(Request $request)
    {
        try {
            $query = Lesson::with(['module', 'module.section', 'module.section.course', 'creator']);

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhereHas('module.section.course', function($cq) use ($search) {
                          $cq->where('title', 'like', "%{$search}%");
                      });
                });
            }

            // Filter by course
            if ($request->filled('course_id')) {
                $query->whereHas('module.section', function($q) use ($request) {
                    $q->where('course_id', $request->course_id);
                });
            }

            // Filter by published status
            if ($request->filled('is_published')) {
                $query->where('is_published', $request->is_published);
            }

            // Sort
            $sortBy = $request->get('sort', 'created_at');
            $sortOrder = $request->get('order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $lessons = $query->paginate(20);

            // Get courses for filter
            $courses = \App\Models\Course::select('id', 'title')->get();

            // Get statistics
            $totalLessons = Lesson::count();
            $publishedLessons = Lesson::where('is_published', true)->count();
            $totalReadingTime = Lesson::sum('reading_time') ?? 0;

            return view('admin.pages.lessons.all', compact(
                'lessons',
                'courses',
                'totalLessons',
                'publishedLessons',
                'totalReadingTime'
            ));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'حدث خطأ أثناء تحميل الدروس: ' . $e->getMessage());
        }
    }
}
