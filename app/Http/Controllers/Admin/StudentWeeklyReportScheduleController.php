<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\StudentWeeklyReportSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentWeeklyReportScheduleController extends Controller
{
    public function index()
    {
        $schedules = StudentWeeklyReportSchedule::query()
            ->with(['targetCourse:id,title', 'targetGroup:id,name'])
            ->latest('id')
            ->paginate(20);

        $courses = Course::query()
            ->whereHas('groups')
            ->orderBy('title')
            ->get(['id', 'title']);

        $groups = CourseGroup::query()
            ->with(['courses:id,title'])
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.weekly-reports.schedules', compact('schedules', 'courses', 'groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'weekday' => ['required', 'integer', 'between:0,6'],
            'due_time' => ['required', 'date_format:H:i'],
            'target_course_id' => ['required', 'integer', 'exists:courses,id'],
            'target_group_id' => ['required', 'integer', 'exists:course_groups,id'],
        ]);

        $isLinked = DB::table('course_group_courses')
            ->where('course_id', (int) $validated['target_course_id'])
            ->where('group_id', (int) $validated['target_group_id'])
            ->exists();

        if (!$isLinked) {
            return back()->withErrors([
                'target_group_id' => 'المجموعة المحددة غير مرتبطة بالكورس المحدد.',
            ])->withInput();
        }

        $validated['target_scope'] = 'specific_students';
        $validated['target_student_ids'] = null;

        $schedule = new StudentWeeklyReportSchedule($validated);
        $schedule->created_by_admin_id = auth()->id();
        $schedule->next_run_at = $schedule->calculateNextRun();
        $schedule->save();

        return back()->with('success', 'تم إنشاء الجدولة بنجاح.');
    }

    public function toggle(StudentWeeklyReportSchedule $schedule)
    {
        $schedule->update(['is_active' => !$schedule->is_active]);
        return back()->with('success', 'تم تحديث حالة الجدولة.');
    }
}

