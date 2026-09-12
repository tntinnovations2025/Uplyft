<?php

namespace App\Http\Middleware;

use App\Models\Invoice;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gatekeeper Middleware: Restrict access to LMS materials, tests, and courses
 * until the student's admission fee is settled and enrollment is confirmed (BUG-ENROLL-001).
 */
class EnsureStudentFeePaid
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isStudent()) {
            $student = $user->studentProfile()->first() ?? $user->studentProfile;

            // If student has no profile or is not enrolled in a section
            if (! $student || empty($student->class_section_id)) {
                return $this->restrictAccess($request);
            }

            // Check for any unpaid admission fee invoices
            $hasUnpaidAdmission = Invoice::withoutGlobalScopes()
                ->where('student_id', $student->id)
                ->where(function ($q) {
                    $q->where('title', 'like', '%Admission%')
                      ->orWhere('title', 'like', '%Initial Tuition%');
                })
                ->where('status', '!=', 'paid')
                ->where('status', '!=', 'Paid')
                ->exists();

            if ($hasUnpaidAdmission) {
                return $this->restrictAccess($request);
            }
        }

        return $next($request);
    }

    protected function restrictAccess(Request $request): Response
    {
        $message = '🔒 Enrollment Pending: Please settle your admission fee voucher to unlock course materials, syllabus documents, and AI features.';

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'redirect_url' => route('student.fees'),
            ], 403);
        }

        return redirect()->route('student.fees')->with('error', $message);
    }
}
