<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\Attendance;
use App\Models\Invoice;
use Illuminate\Http\Request;

class AdminPortalController extends Controller
{
    /**
     * Dashboard Home
     * Route: /admin/dashboard
     */
    public function dashboard()
    {
        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $totalAttendance = Attendance::count();
        
        // Active institute check is handled automatically via global scopes,
        // but if we want to show tenant name, we can fetch the first institute (since they are isolated).
        $activeInstitute = \App\Models\Institute::first();

        return view('dashboard', compact('totalStudents', 'totalTeachers', 'totalAttendance', 'activeInstitute'));
    }

    /**
     * Student Admissions Form
     * Route: /admin/admissions
     */
    public function admissions()
    {
        return view('admissions');
    }

    /**
     * Teacher Onboarding Form
     * Route: /admin/teachers/onboarding
     */
    public function onboarding()
    {
        return view('teachers.onboarding');
    }

    public function studentsDirectory()
    {
        $students = Student::orderBy('first_name')->get();
        return view('admin.students_directory', compact('students'));
    }

    public function teachersDirectory()
    {
        $teachers = Teacher::orderBy('first_name')->get();
        return view('admin.teachers_directory', compact('teachers'));
    }

    public function feeManagement()
    {
        $invoices = Invoice::with('student')->orderBy('created_at', 'desc')->get();
        return view('admin.fee_management', compact('invoices'));
    }

    public function markInvoicePaid(Invoice $invoice)
    {
        $invoice->update(['status' => 'paid']);
        return redirect()->back()->with('success', 'Invoice marked as paid.');
    }
}
