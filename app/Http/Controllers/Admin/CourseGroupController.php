<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CourseGroupController extends Controller
{
    /**
     * Display a listing of the course groups.
     */
    public function index(Request $request, $courseId)
    {
        try {
            $course = Course::findOrFail($courseId);

            $query = CourseGroup::with(['courses', 'creator', 'members'])
                ->whereHas('courses', function($q) use ($courseId) {
                    $q->where('courses.id', $courseId);
                });

            // Filter by visibility
            if ($request->filled('is_visible')) {
                $query->where('is_visible', $request->is_visible);
            }

            // Filter by active status
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $groups = $query->paginate($request->get('per_page', 15));

            return view('admin.course-groups.index', compact('groups', 'course'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحميل المجموعات: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new course group.
     */
    public function create($courseId)
    {
        try {
            $course = Course::findOrFail($courseId);
            $courses = Course::all();

            return view('admin.pages.groups.create', compact('course', 'courses'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحميل نموذج الإنشاء: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created course group in storage.
     */
    public function store(Request $request, $courseId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'max_members' => 'nullable|integer|min:1',
            'course_ids' => 'required|array|min:1',
            'course_ids.*' => 'exists:courses,id',
        ]);

        DB::beginTransaction();
        try {
            $course = Course::findOrFail($courseId);

            // Convert boolean fields (checkboxes send "on" when checked, nothing when unchecked)
            $validated['is_visible'] = $request->has('is_visible');
            $validated['is_active'] = $request->has('is_active');

            // Set creator
            $validated['created_by'] = auth()->id();

            // Remove course_ids from validated data (will be attached separately)
            $courseIds = $validated['course_ids'];
            unset($validated['course_ids']);

            // Create group
            $group = CourseGroup::create($validated);

            // Attach courses to group
            $group->courses()->attach($courseIds);

            DB::commit();

            return redirect()
                ->route('courses.enrollments.group', $courseId)
                ->with('success', 'تم إنشاء المجموعة بنجاح وربطها بـ ' . count($courseIds) . ' كورس');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء المجموعة: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified course group.
     */
    public function show(Request $request, $courseId, $id)
    {
        try {
            $course = Course::findOrFail($courseId);
            $group = CourseGroup::with([
                'courses',
                'creator',
                'leaders',
                'groupEnrollments'
            ])->findOrFail($id);

            // Get statistics
            $stats = [
                'total_members' => $group->getMembersCount(),
                'available_slots' => $group->getAvailableSlots(),
                'is_full' => $group->isFull(),
                'leaders_count' => $group->leaders()->count(),
                'regular_members_count' => $group->members()->where('role', 'member')->count(),
            ];

            // Get paginated members
            $members = $group->members()
                ->with('student')
                ->orderBy('joined_at', 'desc')
                ->paginate($request->get('per_page', 15));

            // Get available students (not in this group)
            $groupStudentIds = $group->students->pluck('id')->toArray();
            $availableStudents = User::role('student')
                ->whereNotIn('id', $groupStudentIds)
                ->get();

            return view('admin.pages.groups.show', compact('course', 'group', 'stats', 'availableStudents', 'members'));
        } catch (\Exception $e) {
            return redirect()
                ->route('courses.groups.index', $courseId)
                ->with('error', 'حدث خطأ أثناء تحميل المجموعة: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified course group.
     */
    public function edit($courseId, $id)
    {
        try {
            $course = Course::findOrFail($courseId);
            $group = CourseGroup::with('courses')->findOrFail($id);
            $courses = Course::all();

            return view('admin.pages.groups.edit', compact('course', 'group', 'courses'));
        } catch (\Exception $e) {
            return redirect()
                ->route('courses.groups.index', $courseId)
                ->with('error', 'حدث خطأ أثناء تحميل نموذج التعديل: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified course group in storage.
     */
    public function update(Request $request, $courseId, $id)
    {
        $course = Course::findOrFail($courseId);
        $group = CourseGroup::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'max_members' => 'nullable|integer|min:1',
            'course_ids' => 'required|array|min:1',
            'course_ids.*' => 'exists:courses,id',
        ]);

        DB::beginTransaction();
        try {
            // Convert boolean fields
            $validated['is_visible'] = $request->has('is_visible');
            $validated['is_active'] = $request->has('is_active');

            // Remove course_ids from validated data
            $courseIds = $validated['course_ids'];
            unset($validated['course_ids']);

            // Update group
            $group->update($validated);

            // Sync courses (this will add new and remove old)
            $group->courses()->sync($courseIds);

            DB::commit();

            return redirect()
                ->route('courses.groups.show', [$courseId, $group->id])
                ->with('success', 'تم تحديث المجموعة بنجاح وربطها بـ ' . count($courseIds) . ' كورس');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث المجموعة: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified course group from storage (soft delete).
     */
    public function destroy($courseId, $id)
    {
        DB::beginTransaction();
        try {
            $course = Course::findOrFail($courseId);
            $group = CourseGroup::findOrFail($id);

            // Check if group has members
            $membersCount = $group->getMembersCount();
            if ($membersCount > 0) {
                return redirect()
                    ->back()
                    ->with('error', "لا يمكن حذف المجموعة لوجود {$membersCount} عضو فيها");
            }

            // Delete image
            if ($group->image) {
                Storage::disk('public')->delete($group->image);
            }

            $group->delete();

            DB::commit();

            return redirect()
                ->route('courses.groups.index', $courseId)
                ->with('success', 'تم حذف المجموعة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء حذف المجموعة: ' . $e->getMessage());
        }
    }

    /**
     * Add member to group.
     */
    public function addMember(Request $request, $id)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'role' => 'required|in:member,leader',
        ]);

        DB::beginTransaction();
        try {
            $group = CourseGroup::findOrFail($id);
            $student = User::findOrFail($validated['student_id']);

            // Check if group is full
            if ($group->isFull()) {
                return redirect()
                    ->back()
                    ->with('error', 'المجموعة ممتلئة');
            }

            // Check if student is already a member
            if ($group->hasMember($student)) {
                return redirect()
                    ->back()
                    ->with('error', 'الطالب عضو بالفعل في هذه المجموعة');
            }

            $group->addMember($student, $validated['role']);

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'تم إضافة العضو بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء إضافة العضو: ' . $e->getMessage());
        }
    }

    /**
     * Show bulk enrollment page with filters.
     */
    public function showBulkEnrollPage(Request $request, $id)
    {
        try {
            $group = CourseGroup::with('courses')->findOrFail($id);

            // Build query for students
            $query = User::role('student');

            // Get current group members to exclude them
            $groupMemberIds = $group->students->pluck('id')->toArray();
            $query->whereNotIn('id', $groupMemberIds);

            // Filter by name or email
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Filter by enrollment date range
            if ($request->filled('enrolled_from')) {
                $query->where('created_at', '>=', $request->enrolled_from);
            }
            if ($request->filled('enrolled_to')) {
                $query->where('created_at', '<=', $request->enrolled_to . ' 23:59:59');
            }

            // Filter by status
            if ($request->filled('status')) {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                }
            }

            // Filter by course enrollment (students enrolled in specific courses)
            if ($request->filled('enrolled_in_course')) {
                $query->whereHas('enrollments', function($q) use ($request) {
                    $q->where('course_id', $request->enrolled_in_course);
                });
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $students = $query->paginate($request->get('per_page', 20));

            // Get all courses for filter
            $courses = \App\Models\Course::select('id', 'title')->orderBy('title')->get();

            // Get statistics
            $stats = [
                'total_available' => User::role('student')->whereNotIn('id', $groupMemberIds)->count(),
                'current_members' => $group->getMembersCount(),
                'available_slots' => $group->getAvailableSlots(),
            ];

            return view('admin.pages.groups.bulk-enroll', compact('group', 'students', 'courses', 'stats'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تحميل الصفحة: ' . $e->getMessage());
        }
    }

    /**
     * Add multiple members to group at once.
     */
    public function addBulkMembers(Request $request, $id)
    {
        $validated = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:users,id',
            'default_role' => 'required|in:member,leader',
        ]);

        DB::beginTransaction();
        try {
            $group = CourseGroup::findOrFail($id);

            $addedCount = 0;
            $skippedCount = 0;
            $errors = [];

            foreach ($validated['student_ids'] as $studentId) {
                $student = User::findOrFail($studentId);

                // Check if group is full
                if ($group->isFull()) {
                    $errors[] = "المجموعة ممتلئة. تمت إضافة {$addedCount} عضو فقط";
                    break;
                }

                // Check if student is already a member
                if ($group->hasMember($student)) {
                    $skippedCount++;
                    continue;
                }

                $group->addMember($student, $validated['default_role']);
                $addedCount++;
            }

            DB::commit();

            $message = "تم إضافة {$addedCount} عضو بنجاح";
            if ($skippedCount > 0) {
                $message .= " (تم تخطي {$skippedCount} عضو موجود بالفعل)";
            }
            if (!empty($errors)) {
                $message .= ". " . implode('. ', $errors);
            }

            return redirect()
                ->back()
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء إضافة الأعضاء: ' . $e->getMessage());
        }
    }

    /**
     * Remove member from group.
     */
    public function removeMember($groupId, $memberId)
    {
        DB::beginTransaction();
        try {
            $group = CourseGroup::findOrFail($groupId);
            $student = User::findOrFail($memberId);

            if (!$group->hasMember($student)) {
                return redirect()
                    ->back()
                    ->with('error', 'الطالب ليس عضواً في هذه المجموعة');
            }

            $group->removeMember($student);

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'تم إزالة العضو بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء إزالة العضو: ' . $e->getMessage());
        }
    }

    /**
     * Update member role.
     */
    public function updateMemberRole(Request $request, $id)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'role' => 'required|in:member,leader',
        ]);

        DB::beginTransaction();
        try {
            $group = CourseGroup::findOrFail($id);

            $member = $group->members()
                ->where('student_id', $validated['student_id'])
                ->first();

            if (!$member) {
                return redirect()
                    ->back()
                    ->with('error', 'العضو غير موجود في المجموعة');
            }

            $member->update(['role' => $validated['role']]);

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'تم تحديث دور العضو بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تحديث دور العضو: ' . $e->getMessage());
        }
    }

    /**
     * Toggle group visibility.
     */
    public function toggleVisibility($id)
    {
        try {
            $group = CourseGroup::findOrFail($id);
            $group->is_visible = !$group->is_visible;
            $group->save();

            $status = $group->is_visible ? 'مرئية' : 'مخفية';

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
     * Toggle group active status.
     */
    public function toggleActive($id)
    {
        try {
            $group = CourseGroup::findOrFail($id);
            $group->is_active = !$group->is_active;
            $group->save();

            $status = $group->is_active ? 'نشطة' : 'غير نشطة';

            return redirect()
                ->back()
                ->with('success', "تم تحديث الحالة إلى: {$status}");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تحديث الحالة: ' . $e->getMessage());
        }
    }

    /**
     * Display all groups from all courses.
     */
    public function allGroups(Request $request)
    {
        try {
            $query = CourseGroup::with(['courses', 'creator', 'members']);

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhereHas('courses', function($cq) use ($search) {
                          $cq->where('title', 'like', "%{$search}%");
                      });
                });
            }

            // Filter by course
            if ($request->filled('course_id')) {
                $query->whereHas('courses', function($q) use ($request) {
                    $q->where('courses.id', $request->course_id);
                });
            }

            // Filter by status
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            // Sort
            $sortBy = $request->get('sort', 'created_at');
            $sortOrder = $request->get('order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $groups = $query->paginate(20);

            // Get courses for filter
            $courses = \App\Models\Course::select('id', 'title')->get();

            // Get statistics
            $totalGroups = CourseGroup::count();
            $activeGroups = CourseGroup::where('is_active', true)->count();
            $totalMembers = DB::table('course_group_members')->count();

            return view('admin.pages.groups.all', compact(
                'groups',
                'courses',
                'totalGroups',
                'activeGroups',
                'totalMembers'
            ));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'حدث خطأ أثناء تحميل المجموعات: ' . $e->getMessage());
        }
    }

    /**
     * Show page to select course for creating a new group.
     */
    public function selectCourse()
    {
        try {
            $courses = Course::select('id', 'title', 'code')->orderBy('title')->get();
            return view('admin.pages.groups.select-course', compact('courses'));
        } catch (\Exception $e) {
            return redirect()
                ->route('groups.all')
                ->with('error', 'حدث خطأ أثناء تحميل الكورسات: ' . $e->getMessage());
        }
    }

    /**
     * Redirect to create page with selected course.
     */
    public function createWithCourse(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id'
        ]);

        $courseId = $request->input('course_id');

        return redirect()->route('courses.groups.create', ['course' => $courseId]);
    }
}
