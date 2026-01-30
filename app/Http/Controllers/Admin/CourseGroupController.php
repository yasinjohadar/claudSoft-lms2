<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\GroupMembershipRequest;
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

            $query = CourseGroup::with(['courses', 'creator'])
                                ->withCount('members')
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
            $validated['is_visible_for_students'] = $request->has('is_visible_for_students');

            // Set creator
            $validated['created_by'] = auth()->id();

            // Remove course_ids from validated data (will be attached separately)
            $courseIds = $validated['course_ids'];
            unset($validated['course_ids']);

            // Create group
            $group = CourseGroup::create($validated);

            // Attach courses to group
            $group->courses()->attach($courseIds);

            // Enroll all current members in the attached courses (if any members exist)
            // Note: At creation time, there are usually no members yet, but we handle it for consistency
            $group->handleCourseAttached($courseIds);

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
            $group = CourseGroup::with([
                'courses',
                'creator',
                'leaders',
                'groupEnrollments'
            ])
            ->withCount('members')
            ->findOrFail($id);

            // Get the course - use provided courseId if valid, otherwise use first course from group
            $course = null;
            if ($courseId) {
                try {
                    $course = Course::findOrFail($courseId);
                    // Verify that this course is actually associated with the group
                    if (!$group->courses->contains('id', $courseId)) {
                        $course = null;
                    }
                } catch (\Exception $e) {
                    $course = null;
                }
            }

            // If no valid course found, try to get first course from group
            if (!$course && $group->courses->count() > 0) {
                $course = $group->courses->first();
            }

            // If still no course, we'll proceed without it (course will be null)
            // The view should handle this case gracefully

            // Get statistics
            $stats = [
                'total_members' => $group->members_count ?? $group->getMembersCount(),
                'available_slots' => $group->getAvailableSlots(),
                'is_full' => $group->isFull(),
                'leaders_count' => $group->leaders()->count(),
                'regular_members_count' => $group->members()->where('role', 'member')->count(),
            ];

            // Get all member student IDs first (for sessions query)
            $memberStudentIds = $group->members()->pluck('student_id')->toArray();

            // Get last activity from sessions table for group members only
            $sessions = DB::table('sessions')
                ->whereNotNull('user_id')
                ->whereIn('user_id', $memberStudentIds)
                ->orderByDesc('last_activity')
                ->get()
                ->groupBy('user_id');

            // Build arrays for last activity and online status
            $lastActivityByUserId = [];
            $onlineUserIds = [];
            $fiveMinutesAgo = now()->subMinutes(5)->timestamp;

            foreach ($sessions as $userId => $userSessions) {
                // Get the most recent session
                $latestSession = $userSessions->first();
                if ($latestSession && $latestSession->last_activity) {
                    $lastActivityTimestamp = $latestSession->last_activity;
                    $lastActivityByUserId[$userId] = \Carbon\Carbon::createFromTimestamp($lastActivityTimestamp);
                    
                    // Check if user is online (last activity within 5 minutes)
                    if ($lastActivityTimestamp >= $fiveMinutesAgo) {
                        $onlineUserIds[] = $userId;
                    }
                }
            }

            // Get paginated members with search and filters
            $membersQuery = $group->members()->with('student');

            // Search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $membersQuery->whereHas('student', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // Role filter
            if ($request->filled('role')) {
                $membersQuery->where('role', $request->role);
            }

            // Filter by other group membership
            if ($request->filled('other_group_id')) {
                $otherGroupId = $request->other_group_id;
                $membersQuery->whereHas('student.courseGroupMemberships', function($q) use ($otherGroupId) {
                    $q->where('group_id', $otherGroupId);
                });
            }

            // Filter by number of other groups
            if ($request->filled('groups_count')) {
                $groupsCount = $request->groups_count;
                if ($groupsCount === '0') {
                    // Students with no other groups
                    $membersQuery->whereDoesntHave('student.courseGroupMemberships', function($q) use ($group) {
                        $q->where('group_id', '!=', $group->id);
                    });
                } else {
                    // Students with specific number of other groups
                    $operator = '>=';
                    $count = (int)$groupsCount;
                    $membersQuery->whereHas('student.courseGroupMemberships', function($q) use ($group) {
                        $q->where('group_id', '!=', $group->id);
                    }, $operator, $count);
                }
            }

            // Filter by online status
            if ($request->filled('online_status')) {
                if ($request->online_status === 'online') {
                    // Only show online members
                    if (!empty($onlineUserIds)) {
                        $membersQuery->whereIn('student_id', $onlineUserIds);
                    } else {
                        // No online users, return empty result
                        $membersQuery->whereRaw('1 = 0');
                    }
                } elseif ($request->online_status === 'offline') {
                    // Only show offline members
                    if (!empty($onlineUserIds)) {
                        $membersQuery->whereNotIn('student_id', $onlineUserIds);
                    }
                    // If no online users, all members are offline, so no filter needed
                }
            }

            // Sort
            $sortBy = $request->get('sort', 'joined_at');
            $sortOrder = $request->get('order', 'desc');
            $membersQuery->orderBy($sortBy, $sortOrder);

            $members = $membersQuery->paginate($request->get('per_page', 15));

            // Load other groups for each student member
            $members->each(function($member) use ($group) {
                if ($member->student) {
                    $member->student->load([
                        'courseGroupMemberships' => function($query) use ($group) {
                            $query->where('group_id', '!=', $group->id);
                        },
                        'courseGroupMemberships.group'
                    ]);
                }
            });

            // Get all groups for filter dropdown (excluding current group)
            $allGroups = CourseGroup::where('id', '!=', $group->id)->orderBy('name')->get();

            // Get available students (not in this group)
            $groupStudentIds = $group->students->pluck('id')->toArray();
            $availableStudents = User::role('student')
                ->whereNotIn('id', $groupStudentIds)
                ->get();

            return view('admin.pages.groups.show', compact('course', 'group', 'stats', 'availableStudents', 'members', 'allGroups', 'lastActivityByUserId', 'onlineUserIds'));
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
            $group = CourseGroup::with('courses')
                                ->withCount('members')
                                ->findOrFail($id);
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
            $validated['allow_membership_requests'] = $request->has('allow_membership_requests');
            $validated['is_visible_for_students'] = $request->has('is_visible_for_students');

            // Remove course_ids from validated data
            $courseIds = $validated['course_ids'];
            unset($validated['course_ids']);

            // Update group
            $group->update($validated);

            // Get old course IDs before sync
            $oldCourseIds = $group->courses->pluck('id')->toArray();

            // Prepare sync data with visibility settings
            $syncData = [];
            $courseVisibility = $request->input('course_visibility', []);
            
            foreach ($courseIds as $courseId) {
                $syncData[$courseId] = [
                    'is_visible' => isset($courseVisibility[$courseId]) && $courseVisibility[$courseId] == '1'
                ];
            }

            // Sync courses with visibility settings
            $group->courses()->sync($syncData);

            // Handle enrollment changes for added/removed courses
            $group->handleCoursesSynced($oldCourseIds, $courseIds);

            // Sync visibility requirements
            // Always delete existing requirements first
            $group->visibilityRequirements()->delete();
            
            // If visibility_required_groups is provided and not empty, create new requirements
            if ($request->has('visibility_required_groups')) {
                $requiredGroupIds = $request->input('visibility_required_groups', []);
                
                // Filter out empty values and self-reference
                $requiredGroupIds = array_filter($requiredGroupIds, function($id) use ($group) {
                    return !empty($id) && $id != $group->id;
                });
                
                // Create new requirements
                foreach ($requiredGroupIds as $requiredGroupId) {
                    \App\Models\CourseGroupVisibilityRequirement::create([
                        'group_id' => $group->id,
                        'required_group_id' => $requiredGroupId,
                    ]);
                }
            }
            // If not provided or empty array, requirements remain deleted (group hidden)

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
     * Bulk remove members from group.
     */
    public function bulkRemoveMembers(Request $request, $groupId)
    {
        $validated = $request->validate([
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $group = CourseGroup::findOrFail($groupId);
            $removedCount = 0;
            $skippedCount = 0;
            $errors = [];

            foreach ($validated['member_ids'] as $memberId) {
                try {
                    $student = User::findOrFail($memberId);

                    if (!$group->hasMember($student)) {
                        $skippedCount++;
                        continue;
                    }

                    $group->removeMember($student);
                    $removedCount++;
                } catch (\Exception $e) {
                    $errors[] = "خطأ في إزالة العضو ID: {$memberId} - " . $e->getMessage();
                }
            }

            DB::commit();

            if ($removedCount === 0) {
                $message = "لم يتم إزالة أي عضو";
                if ($skippedCount > 0) {
                    $message .= " (تم تخطي {$skippedCount} عضو غير موجود في المجموعة)";
                }
                return redirect()
                    ->back()
                    ->with('warning', $message);
            }

            $message = "✅ تم فك الارتباط بنجاح! تم إزالة {$removedCount} عضو من المجموعة";
            if ($removedCount > 1) {
                $message .= " وتم إلغاء تسجيلهم من الكورسات المرتبطة بهذه المجموعة";
            } else {
                $message .= " وتم إلغاء تسجيله من الكورسات المرتبطة بهذه المجموعة";
            }
            
            if ($skippedCount > 0) {
                $message .= " (تم تخطي {$skippedCount} عضو غير موجود في المجموعة)";
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
                ->with('error', 'حدث خطأ أثناء إزالة الأعضاء: ' . $e->getMessage());
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
            $query = CourseGroup::with(['courses', 'creator'])
                                ->withCount('members');

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

    /**
     * Delete a group from all-groups page (without course context).
     */
    public function deleteGroup($id)
    {
        DB::beginTransaction();
        try {
            $group = CourseGroup::findOrFail($id);

            // Check if group has members
            $membersCount = $group->getMembersCount();
            if ($membersCount > 0) {
                DB::rollBack();
                if (request()->expectsJson() || request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => "لا يمكن حذف المجموعة لوجود {$membersCount} عضو فيها"
                    ], 400);
                }
                return redirect()
                    ->route('groups.all')
                    ->with('error', "لا يمكن حذف المجموعة لوجود {$membersCount} عضو فيها");
            }

            // Delete image
            if ($group->image) {
                Storage::disk('public')->delete($group->image);
            }

            $group->delete();

            DB::commit();

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم حذف المجموعة بنجاح'
                ]);
            }

            return redirect()
                ->route('groups.all')
                ->with('success', 'تم حذف المجموعة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting group: ' . $e->getMessage(), [
                'group_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء حذف المجموعة: ' . $e->getMessage()
                ], 500);
            }

            return redirect()
                ->route('groups.all')
                ->with('error', 'حدث خطأ أثناء حذف المجموعة: ' . $e->getMessage());
        }
    }

    /**
     * Show group details without requiring courseId.
     */
    public function showGroup(Request $request, $id)
    {
        try {
            $group = CourseGroup::with([
                'courses',
                'creator',
                'leaders',
                'groupEnrollments'
            ])
            ->withCount('members')
            ->findOrFail($id);

            // Get first course if available, otherwise null
            $course = $group->courses->first();

            // Get statistics
            $stats = [
                'total_members' => $group->members_count ?? $group->getMembersCount(),
                'available_slots' => $group->getAvailableSlots(),
                'is_full' => $group->isFull(),
                'leaders_count' => $group->leaders()->count(),
                'regular_members_count' => $group->members()->where('role', 'member')->count(),
            ];

            // Get paginated members with search and filters
            $membersQuery = $group->members()->with('student');

            // Search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $membersQuery->whereHas('student', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // Role filter
            if ($request->filled('role')) {
                $membersQuery->where('role', $request->role);
            }

            // Filter by other group membership
            if ($request->filled('other_group_id')) {
                $otherGroupId = $request->other_group_id;
                $membersQuery->whereHas('student.courseGroupMemberships', function($q) use ($otherGroupId) {
                    $q->where('group_id', $otherGroupId);
                });
            }

            // Filter by number of other groups
            if ($request->filled('groups_count')) {
                $groupsCount = $request->groups_count;
                if ($groupsCount === '0') {
                    // Students with no other groups
                    $membersQuery->whereDoesntHave('student.courseGroupMemberships', function($q) use ($group) {
                        $q->where('group_id', '!=', $group->id);
                    });
                } else {
                    // Students with specific number of other groups
                    $operator = '>=';
                    $count = (int)$groupsCount;
                    $membersQuery->whereHas('student.courseGroupMemberships', function($q) use ($group) {
                        $q->where('group_id', '!=', $group->id);
                    }, $operator, $count);
                }
            }

            // Sort
            $sortBy = $request->get('sort', 'joined_at');
            $sortOrder = $request->get('order', 'desc');
            $membersQuery->orderBy($sortBy, $sortOrder);

            $members = $membersQuery->paginate($request->get('per_page', 15));

            // Load other groups for each student member
            $members->each(function($member) use ($group) {
                if ($member->student) {
                    $member->student->load([
                        'courseGroupMemberships' => function($query) use ($group) {
                            $query->where('group_id', '!=', $group->id);
                        },
                        'courseGroupMemberships.group'
                    ]);
                }
            });

            // Get all groups for filter dropdown (excluding current group)
            $allGroups = CourseGroup::where('id', '!=', $group->id)->orderBy('name')->get();

            // Get available students (not in this group)
            $groupStudentIds = $group->students->pluck('id')->toArray();
            $availableStudents = User::role('student')
                ->whereNotIn('id', $groupStudentIds)
                ->get();

            return view('admin.pages.groups.show', compact('course', 'group', 'stats', 'availableStudents', 'members', 'allGroups'));
        } catch (\Exception $e) {
            return redirect()
                ->route('groups.all')
                ->with('error', 'حدث خطأ أثناء تحميل المجموعة: ' . $e->getMessage());
        }
    }

    /**
     * Edit group without requiring courseId.
     */
    public function editGroup($id)
    {
        try {
            $group = CourseGroup::with('courses')
                                ->withCount('members')
                                ->findOrFail($id);
            
            // Get first course if available
            $course = $group->courses->first();
            $courses = Course::all();

            return view('admin.pages.groups.edit', compact('course', 'group', 'courses'));
        } catch (\Exception $e) {
            return redirect()
                ->route('groups.all')
                ->with('error', 'حدث خطأ أثناء تحميل نموذج التعديل: ' . $e->getMessage());
        }
    }

    /**
     * Display membership requests for a group.
     */
    public function membershipRequests($courseId, $groupId, Request $request)
    {
        try {
            $course = Course::findOrFail($courseId);
            $group = CourseGroup::with(['courses', 'creator'])
                ->whereHas('courses', function($q) use ($courseId) {
                    $q->where('courses.id', $courseId);
                })
                ->findOrFail($groupId);

            $query = GroupMembershipRequest::with(['student', 'approver', 'rejecter'])
                ->where('group_id', $groupId);

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('student', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $requests = $query->paginate($request->get('per_page', 15));
            
            // Get count of pending requests for "Approve All" button
            $pendingCount = GroupMembershipRequest::where('group_id', $groupId)
                ->where('status', 'pending')
                ->count();

            return view('admin.course-groups.membership-requests', compact('course', 'group', 'requests', 'pendingCount'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء تحميل طلبات الانضمام: ' . $e->getMessage());
        }
    }

    /**
     * Approve a membership request.
     */
    public function approveRequest($courseId, $groupId, $requestId, Request $request)
    {
        DB::beginTransaction();
        try {
            $course = Course::findOrFail($courseId);
            $group = CourseGroup::findOrFail($groupId);
            $membershipRequest = GroupMembershipRequest::findOrFail($requestId);

            // Verify request belongs to group
            if ($membershipRequest->group_id != $groupId) {
                return redirect()->back()
                    ->with('error', 'طلب الانضمام غير مرتبط بهذه المجموعة');
            }

            // السماح بقبول الطلبات المرفوضة مرة أخرى
            // فقط منع قبول الطلبات المقبولة مسبقاً
            if ($membershipRequest->isApproved()) {
                return redirect()->back()
                    ->with('error', 'تم قبول هذا الطلب مسبقاً');
            }

            // Check if group is full
            if ($group->isFull()) {
                return redirect()->back()
                    ->with('error', 'المجموعة ممتلئة ولا يمكن إضافة المزيد من الأعضاء');
            }

            // Approve request (this will automatically add student to group)
            $membershipRequest->approve(auth()->id());

            DB::commit();

            return redirect()->back()
                ->with('success', 'تم قبول طلب الانضمام وإضافة الطالب للمجموعة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء قبول طلب الانضمام: ' . $e->getMessage());
        }
    }

    /**
     * Reject a membership request.
     */
    public function rejectRequest($courseId, $groupId, $requestId, Request $request)
    {
        DB::beginTransaction();
        try {
            $course = Course::findOrFail($courseId);
            $group = CourseGroup::findOrFail($groupId);
            $membershipRequest = GroupMembershipRequest::findOrFail($requestId);

            // Verify request belongs to group
            if ($membershipRequest->group_id != $groupId) {
                return redirect()->back()
                    ->with('error', 'طلب الانضمام غير مرتبط بهذه المجموعة');
            }

            // Check if request is already processed
            if (!$membershipRequest->isPending()) {
                return redirect()->back()
                    ->with('error', 'تم معالجة هذا الطلب مسبقاً');
            }

            // Validate admin notes
            $validated = $request->validate([
                'admin_notes' => 'nullable|string|max:1000',
            ]);

            // Reject request
            $membershipRequest->reject(auth()->id(), $validated['admin_notes'] ?? null);

            DB::commit();

            return redirect()->back()
                ->with('success', 'تم رفض طلب الانضمام بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء رفض طلب الانضمام: ' . $e->getMessage());
        }
    }

    /**
     * Delete a membership request permanently.
     */
    public function deleteRequest($courseId, $groupId, $requestId, Request $request)
    {
        DB::beginTransaction();
        try {
            $course = Course::findOrFail($courseId);
            $group = CourseGroup::findOrFail($groupId);
            $membershipRequest = GroupMembershipRequest::findOrFail($requestId);

            // Verify request belongs to group
            if ($membershipRequest->group_id != $groupId) {
                return redirect()->back()
                    ->with('error', 'طلب الانضمام غير مرتبط بهذه المجموعة');
            }

            // حذف طلب الانضمام نهائياً فقط (بدون حذف التسجيل)
            $membershipRequest->forceDelete();

            DB::commit();

            return redirect()->back()
                ->with('success', 'تم حذف طلب الانضمام بنجاح. تم الاحتفاظ بالتسجيل المرتبط.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete membership request', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حذف طلب الانضمام: ' . $e->getMessage());
        }
    }

    /**
     * Delete multiple membership requests permanently (without affecting group membership).
     */
    public function deleteMultipleRequests($courseId, $groupId, Request $request)
    {
        $request->validate([
            'request_ids' => 'required|array|min:1',
            'request_ids.*' => 'required|integer|exists:group_membership_requests,id',
        ], [], [
            'request_ids' => 'معرفات الطلبات',
        ]);

        DB::beginTransaction();
        try {
            $course = Course::findOrFail($courseId);
            $group = CourseGroup::findOrFail($groupId);
            $requestIds = $request->input('request_ids');
            $deletedCount = 0;

            foreach ($requestIds as $requestId) {
                $membershipRequest = GroupMembershipRequest::find($requestId);
                if (!$membershipRequest || $membershipRequest->group_id != $groupId) {
                    continue;
                }
                $membershipRequest->forceDelete();
                $deletedCount++;
            }

            DB::commit();

            return redirect()->back()
                ->with('success', "تم حذف {$deletedCount} طلب انضمام نهائياً. تم الاحتفاظ بالتسجيل المرتبط بمن تم قبولهم.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete multiple membership requests', [
                'group_id' => $groupId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حذف الطلبات: ' . $e->getMessage());
        }
    }

    /**
     * Approve multiple membership requests.
     */
    public function approveMultipleRequests($courseId, $groupId, Request $request)
    {
        DB::beginTransaction();
        try {
            $course = Course::findOrFail($courseId);
            $group = CourseGroup::findOrFail($groupId);

            // Validate request IDs
            $validated = $request->validate([
                'request_ids' => 'required|array|min:1',
                'request_ids.*' => 'required|integer|exists:group_membership_requests,id',
            ], [], [
                'request_ids' => 'معرفات الطلبات',
            ]);

            $requestIds = $validated['request_ids'];
            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($requestIds as $requestId) {
                try {
                    $membershipRequest = GroupMembershipRequest::findOrFail($requestId);

                    // Verify request belongs to group
                    if ($membershipRequest->group_id != $groupId) {
                        $failedCount++;
                        $errors[] = "طلب #{$requestId} غير مرتبط بهذه المجموعة";
                        continue;
                    }

                    // Check if request is already processed
                    if (!$membershipRequest->isPending()) {
                        $failedCount++;
                        $errors[] = "طلب #{$requestId} تم معالجته مسبقاً";
                        continue;
                    }

                    // Check if group is full
                    if ($group->isFull()) {
                        $failedCount++;
                        $errors[] = "المجموعة ممتلئة - لا يمكن قبول المزيد من الطلبات";
                        break; // Stop processing if group is full
                    }

                    // Approve request
                    $membershipRequest->approve(auth()->id());
                    $successCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = "خطأ في طلب #{$requestId}: " . $e->getMessage();
                    Log::error('Failed to approve membership request', [
                        'request_id' => $requestId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();

            $message = "تم قبول {$successCount} طلب بنجاح";
            if ($failedCount > 0) {
                $message .= "، فشل {$failedCount} طلب";
            }

            return redirect()->back()
                ->with('success', $message)
                ->with('errors', $errors);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء قبول الطلبات: ' . $e->getMessage());
        }
    }

    /**
     * Approve all pending membership requests for the group.
     */
    public function approveAllPendingRequests($courseId, $groupId, Request $request)
    {
        DB::beginTransaction();
        try {
            $course = Course::findOrFail($courseId);
            $group = CourseGroup::findOrFail($groupId);

            // Get all pending requests for this group
            $pendingRequests = GroupMembershipRequest::where('group_id', $groupId)
                ->where('status', 'pending')
                ->get();

            if ($pendingRequests->isEmpty()) {
                return redirect()->back()
                    ->with('warning', 'لا توجد طلبات معلقة للموافقة عليها');
            }

            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($pendingRequests as $membershipRequest) {
                try {
                    // Check if group is full
                    if ($group->isFull()) {
                        $failedCount = $pendingRequests->count() - $successCount;
                        $errors[] = "المجموعة ممتلئة - تم قبول {$successCount} طلب فقط";
                        break; // Stop processing if group is full
                    }

                    // Approve request
                    $membershipRequest->approve(auth()->id());
                    $successCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = "خطأ في طلب #{$membershipRequest->id}: " . $e->getMessage();
                    Log::error('Failed to approve membership request', [
                        'request_id' => $membershipRequest->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();

            $message = "تم قبول {$successCount} طلب بنجاح";
            if ($failedCount > 0) {
                $message .= "، فشل {$failedCount} طلب";
            }

            return redirect()->back()
                ->with('success', $message)
                ->with('errors', $errors);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء قبول جميع الطلبات: ' . $e->getMessage());
        }
    }
}
