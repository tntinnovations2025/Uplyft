<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherPortalController extends Controller
{
    /**
     * Dashboard Home
     * Route: /teacher/dashboard
     */
    public function dashboard()
    {
        return view('teacher.dashboard');
    }

    /**
     * My Schedule (Placeholder)
     * Route: /teacher/schedule
     */
    public function schedule()
    {
        return view('teacher.schedule');
    }

    /**
     * Mark Attendance (Connect to Module 5)
     * Route: /teacher/attendance
     */
    public function attendance(Request $request)
    {
        $classes = Student::select('enrolled_class')->distinct()->whereNotNull('enrolled_class')->pluck('enrolled_class');
        
        $selectedClass = $request->input('class');
        
        $query = Student::orderBy('first_name');
        if ($selectedClass) {
            $query->where('enrolled_class', $selectedClass);
        }
        $students = $query->get();

        return view('teacher.attendance', compact('students', 'classes', 'selectedClass'));
    }

    /**
     * LMS & Exams (Placeholder for Module 6)
     * Route: /teacher/lms
     */
    public function lms()
    {
        return view('teacher.lms');
    }
}
