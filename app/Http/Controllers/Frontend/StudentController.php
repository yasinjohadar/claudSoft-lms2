<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of all students
     */
    public function index()
    {
        $students = User::role('student')
                       ->where('is_active', true)
                       ->orderBy('created_at', 'desc')
                       ->paginate(12);

        return view('frontend.pages.students', compact('students'));
    }

    /**
     * Display the specified student details
     */
    public function show($id)
    {
        $student = User::role('student')
                      ->where('is_active', true)
                      ->findOrFail($id);

        return view('frontend.pages.student-details', compact('student'));
    }
}
