<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\TeacherAvailability;
use App\Models\User;
use App\Services\TimetableGeneratorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherAvailabilityController extends Controller
{
    public function __construct(
        protected TimetableGeneratorService $timetableGenerator
    ) {}

    public function index(): View
    {
        $instituteId = auth()->user()->institute_id;

        $teachers = User::where('institute_id', $instituteId)
            ->where('role', User::ROLE_TEACHER)
            ->with('availabilities')
            ->orderBy('name')
            ->get();

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        return view('principal.teachers.availability', compact('teachers', 'days'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'teacher_id'   => 'required|exists:users,id',
            'availabilities' => 'required|array',
            'availabilities.*.day_of_week'  => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'availabilities.*.start_time'   => 'nullable|string',
            'availabilities.*.end_time'     => 'nullable|string',
            'availabilities.*.is_available' => 'nullable',
        ]);

        $teacher = User::where('institute_id', auth()->user()->institute_id)
            ->findOrFail($validated['teacher_id']);

        foreach ($validated['availabilities'] as $dayData) {
            $isAvailable = !empty($dayData['is_available']) && $dayData['is_available'] !== '0' && $dayData['is_available'] !== 0 && $dayData['is_available'] !== false;

            $startTime = !empty($dayData['start_time']) ? date('H:i:s', strtotime($dayData['start_time'])) : '08:00:00';
            $endTime   = !empty($dayData['end_time'])   ? date('H:i:s', strtotime($dayData['end_time']))   : '14:30:00';

            TeacherAvailability::updateOrCreate(
                [
                    'teacher_id'  => $teacher->id,
                    'day_of_week' => strtolower($dayData['day_of_week']),
                ],
                [
                    'start_time'   => $startTime,
                    'end_time'     => $endTime,
                    'is_available' => $isAvailable,
                ]
            );
        }

        // The schedule updates automatically whenever an availability window changes
        $result = $this->timetableGenerator->regenerateForActiveTerm(auth()->user()->institute_id);

        $message = "Availability schedule for Teacher '{$teacher->name}' updated. The timetable was re-generated automatically.";

        if ($result && !empty($result['clashes'])) {
            return back()
                ->with('success', $message)
                ->with('warning', "Timetable re-generated with {$result['scheduled_slots']} slot(s). Some periods could not be scheduled:<br>" . implode('<br>', $result['clashes']));
        }

        return back()->with('success', $message);
    }
}
