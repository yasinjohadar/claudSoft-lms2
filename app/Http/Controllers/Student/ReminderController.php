<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\GroupReminder;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    public function index()
    {
        $reminders = GroupReminder::whereHas('target', function($query) {
            // للكورسات
            if (method_exists($query->getModel(), 'enrollments')) {
                $query->whereHas('enrollments', function($q) {
                    $q->where('student_id', auth()->id());
                });
            }
        })
        ->where('is_sent', true)
        ->latest('sent_at')
        ->paginate(20);

        return view('student.reminders.index', compact('reminders'));
    }

    public function show(GroupReminder $reminder)
    {
        return view('student.reminders.show', compact('reminder'));
    }
}
