<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicTermController extends Controller
{
    public function index(): View
    {
        $instituteId = auth()->user()->institute_id;

        $terms = AcademicTerm::where('institute_id', $instituteId)
            ->orderByDesc('is_active')
            ->orderByDesc('start_date')
            ->get();

        $activeTerm = $terms->firstWhere('is_active', true);

        return view('principal.academic-terms.index', compact('terms', 'activeTerm'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
            'is_active'  => 'nullable|boolean',
        ]);

        $instituteId = auth()->user()->institute_id;

        $term = AcademicTerm::create([
            'institute_id' => $instituteId,
            'name'         => $validated['name'],
            'start_date'   => $validated['start_date'],
            'end_date'     => $validated['end_date'],
            'is_active'    => false,
        ]);

        // If marked active or first term for institute, activate it
        if (!empty($validated['is_active']) || AcademicTerm::where('institute_id', $instituteId)->count() === 1) {
            $term->markAsActive();
        }

        return redirect()
            ->route('principal.academic-terms.index')
            ->with('success', "Academic Term '{$term->name}' created successfully.");
    }

    public function setActive(AcademicTerm $term): RedirectResponse
    {
        // Security check
        if ($term->institute_id !== auth()->user()->institute_id) {
            abort(403, 'Unauthorized action.');
        }

        $term->markAsActive();

        return redirect()
            ->route('principal.academic-terms.index')
            ->with('success', "Academic Term '{$term->name}' is now the Active session for your institute.");
    }

    public function destroy(AcademicTerm $term): RedirectResponse
    {
        if ($term->institute_id !== auth()->user()->institute_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($term->is_active) {
            return back()->with('error', 'Cannot delete an active academic term. Please set another term as active first.');
        }

        $term->delete();

        return redirect()
            ->route('principal.academic-terms.index')
            ->with('success', "Academic Term '{$term->name}' removed.");
    }
}
