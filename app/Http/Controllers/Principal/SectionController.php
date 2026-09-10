<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\ClassSection;
use App\Models\InstituteClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SectionController extends Controller
{
    public function index(): View
    {
        $instituteId = auth()->user()->institute_id;

        $classes = InstituteClass::where('institute_id', $instituteId)
            ->with(['sections'])
            ->get();

        return view('principal.sections.index', compact('classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'institute_class_id' => 'required|exists:institute_classes,id',
            'section_name'       => 'required|string|max:100',
            'capacity'           => 'required|integer|min:1|max:200',
        ]);

        $class = InstituteClass::findOrFail($validated['institute_class_id']);
        if ($class->institute_id !== auth()->user()->institute_id) {
            abort(403);
        }

        ClassSection::create([
            'institute_class_id' => $class->id,
            'section_name'       => $validated['section_name'],
            'capacity'           => $validated['capacity'],
        ]);

        return redirect()
            ->route('principal.sections.index')
            ->with('success', "Section '{$validated['section_name']}' created for {$class->custom_name}.");
    }

    public function destroy(ClassSection $section): RedirectResponse
    {
        if ($section->instituteClass->institute_id !== auth()->user()->institute_id) {
            abort(403);
        }

        $section->delete();

        return redirect()
            ->route('principal.sections.index')
            ->with('success', "Section '{$section->section_name}' removed.");
    }
}
