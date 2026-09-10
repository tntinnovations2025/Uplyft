<?php

namespace App\Http\Controllers\GlobalAdmin;

use App\Http\Controllers\Controller;
use App\Models\SystemClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemClassController extends Controller
{
    public function index(): View
    {
        $classes = SystemClass::withTrashed()->orderBy('sort_order')->orderBy('name')->get();
        $educationTypeLabels = SystemClass::$educationTypeLabels;
        return view('global-admin.system-classes.index', compact('classes', 'educationTypeLabels'));
    }

    public function create(): View
    {
        $educationTypeLabels = SystemClass::$educationTypeLabels;
        return view('global-admin.system-classes.create', compact('educationTypeLabels'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:100|unique:system_classes,name',
            'short_code'     => 'required|string|max:20|unique:system_classes,short_code',
            'education_type' => 'required|in:matric,higher_sec,o_a_level,professional,other',
            'sort_order'     => 'nullable|integer|min:0|max:255',
        ]);

        SystemClass::create($validated + ['is_active' => true]);

        return redirect()
            ->route('global-admin.system-classes.index')
            ->with('success', "Class \"{$validated['name']}\" added.");
    }

    public function edit(SystemClass $systemClass): View
    {
        $educationTypeLabels = SystemClass::$educationTypeLabels;
        return view('global-admin.system-classes.edit', compact('systemClass', 'educationTypeLabels'));
    }

    public function update(Request $request, SystemClass $systemClass): RedirectResponse
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:100|unique:system_classes,name,' . $systemClass->id,
            'short_code'     => 'required|string|max:20|unique:system_classes,short_code,' . $systemClass->id,
            'education_type' => 'required|in:matric,higher_sec,o_a_level,professional,other',
            'sort_order'     => 'nullable|integer|min:0|max:255',
            'is_active'      => 'boolean',
        ]);

        $systemClass->update($validated);

        return redirect()
            ->route('global-admin.system-classes.index')
            ->with('success', "Class \"{$systemClass->name}\" updated.");
    }

    public function destroy(SystemClass $systemClass): RedirectResponse
    {
        $systemClass->update(['is_active' => false]);
        $systemClass->delete();

        return redirect()
            ->route('global-admin.system-classes.index')
            ->with('success', "Class \"{$systemClass->name}\" deactivated.");
    }
}
