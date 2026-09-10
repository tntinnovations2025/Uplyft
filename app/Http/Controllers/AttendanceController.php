<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceRequest;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Fetch class roster for a given academic term and date.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'academic_term_id' => ['required', 'integer'],
            'date'             => ['nullable', 'date'],
        ]);

        $academicTermId = (int) $request->query('academic_term_id');
        $date           = $request->query('date', now()->format('Y-m-d'));

        $roster = $this->attendanceService->getRosterForTerm($academicTermId, $date);

        return response()->json([
            'academic_term_id' => $academicTermId,
            'date'             => $date,
            'total_students'   => $roster->count(),
            'roster'           => $roster,
        ]);
    }

    /**
     * Bulk store or update attendance entries.
     *
     * @param StoreAttendanceRequest $request
     * @return JsonResponse
     */
    public function store(StoreAttendanceRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $processed = $this->attendanceService->markBulkAttendance($validated);

        return response()->json([
            'message'   => "Successfully updated attendance for {$processed} student(s).",
            'date'      => $validated['date'],
            'processed' => $processed,
        ], 200);
    }
}
