<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\ClassSection;
use App\Models\InstituteClass;
use App\Models\Room;
use App\Models\Subject;
use App\Models\SystemClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassSubjectController extends Controller
{
    public function index(): View
    {
        $instituteId = auth()->user()->institute_id;

        $classes = InstituteClass::where('institute_id', $instituteId)
            ->with(['systemClass', 'subjects', 'sections.room'])
            ->get();

        $rooms = Room::where('institute_id', $instituteId)->orderBy('room_number')->get();
        $systemClasses = SystemClass::where('is_active', true)->get();

        return view('principal.classes-subjects.index', compact('classes', 'rooms', 'systemClasses'));
    }

    public function storeClass(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'custom_name'       => 'required|string|max:255',
            'section_name'      => 'required|string|max:100',
            'room_id'           => 'nullable|exists:rooms,id',
            'room_number'       => 'nullable|string|max:50',
            'capacity'          => 'required|integer|min:1|max:1000',
            'enrolled_students' => 'nullable|integer|min:0',
        ]);

        $instituteId = auth()->user()->institute_id;
        $roomNumber = $validated['room_number'] ?? null;
        $roomCapacity = $validated['capacity'];

        if (!empty($validated['room_id'])) {
            $selectedRoom = Room::where('institute_id', $instituteId)->find($validated['room_id']);
            if ($selectedRoom) {
                $roomNumber = $selectedRoom->room_number;
                // If capacity left as default or room capacity selected, use room capacity
                $roomCapacity = $selectedRoom->capacity;
            }
        }

        $enrolled = $validated['enrolled_students'] ?? 0;
        if ($enrolled > $roomCapacity) {
            return back()->withInput()->with('error', "Enrolled students count ({$enrolled}) cannot exceed Room Capacity ({$roomCapacity}).");
        }

        $class = InstituteClass::create([
            'institute_id' => $instituteId,
            'custom_name'  => $validated['custom_name'],
        ]);

        ClassSection::create([
            'institute_class_id' => $class->id,
            'section_name'       => $validated['section_name'],
            'room_id'            => $validated['room_id'] ?? null,
            'room_number'        => $roomNumber,
            'capacity'           => $roomCapacity,
            'enrolled_students'  => $enrolled,
        ]);

        return redirect()
            ->route('principal.classes-subjects.index')
            ->with('success', "Class '{$validated['custom_name']}' with Section '{$validated['section_name']}' created successfully.");
    }

    public function destroyClass(InstituteClass $class): RedirectResponse
    {
        if ($class->institute_id !== auth()->user()->institute_id) {
            abort(403);
        }

        $class->delete();

        return redirect()
            ->route('principal.classes-subjects.index')
            ->with('success', "Class '{$class->custom_name}' deleted successfully.");
    }

    public function storeSection(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'institute_class_id' => 'required|exists:institute_classes,id',
            'section_name'       => 'required|string|max:100',
            'room_id'           => 'nullable|exists:rooms,id',
            'room_number'       => 'nullable|string|max:50',
            'capacity'          => 'required|integer|min:1|max:1000',
            'enrolled_students'  => 'nullable|integer|min:0',
        ]);

        $class = InstituteClass::findOrFail($validated['institute_class_id']);
        if ($class->institute_id !== auth()->user()->institute_id) {
            abort(403);
        }

        $instituteId = auth()->user()->institute_id;
        $roomNumber = $validated['room_number'] ?? null;
        $roomCapacity = $validated['capacity'];

        if (!empty($validated['room_id'])) {
            $selectedRoom = Room::where('institute_id', $instituteId)->find($validated['room_id']);
            if ($selectedRoom) {
                $roomNumber = $selectedRoom->room_number;
                $roomCapacity = $selectedRoom->capacity;
            }
        }

        $enrolled = $validated['enrolled_students'] ?? 0;
        if ($enrolled > $roomCapacity) {
            return back()->withInput()->with('error', "Enrolled students count ({$enrolled}) cannot exceed Room Capacity ({$roomCapacity}).");
        }

        ClassSection::create([
            'institute_class_id' => $class->id,
            'section_name'       => $validated['section_name'],
            'room_id'            => $validated['room_id'] ?? null,
            'room_number'        => $roomNumber,
            'capacity'           => $roomCapacity,
            'enrolled_students'  => $enrolled,
        ]);

        return redirect()
            ->route('principal.classes-subjects.index')
            ->with('success', "Section '{$validated['section_name']}' added to {$class->custom_name}.");
    }

    public function destroySection(ClassSection $section): RedirectResponse
    {
        if ($section->instituteClass->institute_id !== auth()->user()->institute_id) {
            abort(403);
        }

        $sectionName = $section->section_name;
        $section->delete();

        return redirect()
            ->route('principal.classes-subjects.index')
            ->with('success', "Section '{$sectionName}' deleted successfully.");
    }

    public function storeSubject(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'institute_class_id' => 'required|exists:institute_classes,id',
            'subject_name'       => 'required|string|max:255',
            'subject_code'       => 'nullable|string|max:50',
            'credit_hours'       => 'required|integer|min:1|max:10',
        ]);

        $class = InstituteClass::findOrFail($validated['institute_class_id']);
        if ($class->institute_id !== auth()->user()->institute_id) {
            abort(403);
        }

        Subject::create([
            'institute_class_id' => $class->id,
            'subject_name'       => $validated['subject_name'],
            'subject_code'       => $validated['subject_code'] ?? null,
            'credit_hours'       => $validated['credit_hours'],
        ]);

        return redirect()
            ->route('principal.classes-subjects.index')
            ->with('success', "Subject '{$validated['subject_name']}' added to {$class->custom_name}.");
    }

    public function destroySubject(Subject $subject): RedirectResponse
    {
        if ($subject->instituteClass->institute_id !== auth()->user()->institute_id) {
            abort(403);
        }

        $subjectName = $subject->subject_name;
        $subject->delete();

        return redirect()
            ->route('principal.classes-subjects.index')
            ->with('success', "Subject '{$subjectName}' deleted successfully.");
    }
}
