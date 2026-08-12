<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\Room;
use App\Models\Timetable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(Request $request): View
    {
        $instituteId = auth()->user()->institute_id;

        $rooms = Room::where('institute_id', $instituteId)
            ->withCount('classSections')
            ->orderBy('room_number')
            ->get();

        // Get active term for schedule queries
        $activeTerm = AcademicTerm::where('institute_id', $instituteId)
            ->where('is_active', true)
            ->first();

        // Determine which day to show schedule for (default = today)
        $dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $selectedDay = $request->get('day', strtolower(now()->format('l')));

        // Build schedule map: room_id => [timetable slots]
        $roomSchedules = collect();
        if ($activeTerm) {
            $roomIds = $rooms->pluck('id')->toArray();
            $slots = Timetable::where('academic_term_id', $activeTerm->id)
                ->whereIn('room_id', $roomIds)
                ->where('day_of_week', $selectedDay)
                ->with(['section.instituteClass', 'subject', 'teacher'])
                ->orderBy('start_time')
                ->get();

            $roomSchedules = $slots->groupBy('room_id');
        }

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        return view('principal.rooms.index', compact(
            'rooms',
            'activeTerm',
            'roomSchedules',
            'selectedDay',
            'days'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_number'    => 'required|string|max:100',
            'building_block' => 'nullable|string|max:100',
            'capacity'       => 'required|integer|min:1|max:1000',
        ]);

        $instituteId = auth()->user()->institute_id;

        Room::create([
            'institute_id'   => $instituteId,
            'room_number'    => $validated['room_number'],
            'building_block' => $validated['building_block'] ?? null,
            'capacity'       => $validated['capacity'],
        ]);

        return redirect()
            ->route('principal.rooms.index')
            ->with('success', "Room '{$validated['room_number']}' added successfully.");
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        if ($room->institute_id !== auth()->user()->institute_id) {
            abort(403);
        }

        $validated = $request->validate([
            'room_number'    => 'required|string|max:100',
            'building_block' => 'nullable|string|max:100',
            'capacity'       => 'required|integer|min:1|max:1000',
        ]);

        $room->update($validated);

        return redirect()
            ->route('principal.rooms.index')
            ->with('success', "Room '{$validated['room_number']}' updated successfully.");
    }

    public function destroy(Room $room): RedirectResponse
    {
        if ($room->institute_id !== auth()->user()->institute_id) {
            abort(403);
        }

        $roomName = $room->room_number;
        $room->delete();

        return redirect()
            ->route('principal.rooms.index')
            ->with('success', "Room '{$roomName}' deleted successfully.");
    }
}
