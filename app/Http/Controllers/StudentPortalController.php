<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentPortalController extends Controller
{
    /**
     * My Fee Ledger (Connect to Module 4)
     * Route: /student/fees
     */
    public function fees()
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            abort(404, 'Student record not found.');
        }

        // Logic similar to StudentAdmissionController
        $baseFee = 50000;
        $isFiler = $student->guardian_tax_status === 'Filer';
        $taxPercentage = $isFiler ? 0 : 5; // 0% for Filers, 5% for Non-Filers
        $taxAmount = ($baseFee * $taxPercentage) / 100;
        $totalFee = $baseFee + $taxAmount;

        return view('student.fees', compact('student', 'baseFee', 'isFiler', 'taxPercentage', 'taxAmount', 'totalFee'));
    }

    /**
     * Attendance Record (Connect to Module 5)
     * Route: /student/attendance
     */
    public function attendance()
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            abort(404, 'Student record not found.');
        }

        // Scope to a generic term if not provided
        $academicTermId = request('term_id', 1);

        $attendances = Attendance::where('student_id', $student->id)
            ->where('academic_term_id', $academicTermId)
            ->orderBy('date', 'desc')
            ->get();

        $totalDays = $attendances->count();
        $presentDays = $attendances->where('status', 'Present')->count();
        
        $percentage = $totalDays > 0 ? round(($presentDays / $totalDays) * 100) : 0;

        return view('student.attendance', compact('student', 'attendances', 'totalDays', 'presentDays', 'percentage'));
    }

    public function invoices()
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            abort(404, 'Student record not found.');
        }

        $invoices = $student->invoices()->orderBy('created_at', 'desc')->get();
        return view('student.invoices', compact('student', 'invoices'));
    }

    /**
     * Timetable (Placeholder)
     * Route: /student/timetable
     */
    public function timetable()
    {
        return view('student.timetable');
    }

    /**
     * My Courses (Placeholder)
     * Route: /student/courses
     */
    public function courses()
    {
        return view('student.courses');
    }

    /**
     * Assignments & Exams (Placeholder for Module 6)
     * Route: /student/lms
     */
    public function lms()
    {
        return view('student.lms');
    }
}
