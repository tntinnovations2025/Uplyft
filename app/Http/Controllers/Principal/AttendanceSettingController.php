<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AttendanceSettingController extends Controller
{
    /**
     * Display Attendance Controls & Lock Settings
     */
    public function index(): View
    {
        $user = auth()->user();
        $setting = AttendanceSetting::getForInstitute($user->institute_id);

        return view('principal.attendance.settings', compact('setting'));
    }

    /**
     * Update Attendance Controls & Lock Settings
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'attendance_mode' => ['required', 'string', 'in:daily,subject'],
            'start_time' => ['required', 'string'],
            'end_time' => ['required', 'string'],
            'allow_past_edits' => ['nullable', 'boolean'],
            'is_locked_override' => ['nullable', 'boolean'],
        ]);

        $user = auth()->user();
        $setting = AttendanceSetting::getForInstitute($user->institute_id);

        $setting->update([
            'attendance_mode' => $validated['attendance_mode'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'allow_past_edits' => $request->has('allow_past_edits'),
            'is_locked_override' => $request->has('is_locked_override'),
        ]);

        // Keep InstituteSetting model in sync for Student & Teacher portal consistency
        try {
            $instSetting = \App\Models\InstituteSetting::getForInstitute($user->institute_id);
            $instSetting->update([
                'attendance_mode' => $validated['attendance_mode'],
                'attendance_start_time' => $validated['start_time'],
                'attendance_end_time' => $validated['end_time'],
                'allow_past_attendance_edits' => $request->has('allow_past_edits'),
                'is_attendance_locked_override' => $request->has('is_locked_override'),
            ]);
        } catch (\Throwable $e) {
            // Non-blocking sync
        }

        return redirect()->back()->with('success', 'Attendance Controls & Mode updated successfully for your campus!');
    }
}
