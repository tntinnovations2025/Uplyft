<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\ScholarshipCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScholarshipPolicyController extends Controller
{
    /**
     * Display a listing of scholarship policies and custom verification questions.
     */
    public function index(): View
    {
        $scholarships = ScholarshipCategory::withCount('students')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('principal.scholarships.index', compact('scholarships'));
    }

    /**
     * Store a newly created scholarship policy category in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'discount_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'questions' => ['nullable', 'array'],
            'questions.*.label' => ['required', 'string', 'max:255'],
            'questions.*.type' => ['required', 'string', 'in:text,number,date,select'],
            'questions.*.options' => ['nullable', 'string'],
            'questions.*.required' => ['nullable'],
        ]);

        // Clean up questions array structure
        $cleanQuestions = [];
        if (! empty($validated['questions'])) {
            foreach ($validated['questions'] as $q) {
                if (! empty($q['label'])) {
                    $cleanQuestions[] = [
                        'id' => 'q_'.uniqid(),
                        'label' => trim($q['label']),
                        'type' => $q['type'] ?? 'text',
                        'options' => ! empty($q['options']) ? array_map('trim', explode(',', $q['options'])) : [],
                        'required' => ! empty($q['required']),
                    ];
                }
            }
        }

        $scholarship = ScholarshipCategory::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'discount_percentage' => $validated['discount_percentage'],
            'questions' => $cleanQuestions,
            'is_active' => true,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Scholarship Policy created successfully.',
                'scholarship' => $scholarship,
            ], 201);
        }

        return redirect()->back()->with('success', 'Scholarship Policy created successfully.');
    }

    /**
     * Update the specified scholarship policy.
     */
    public function update(Request $request, ScholarshipCategory $scholarship)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'discount_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'questions' => ['nullable', 'array'],
            'questions.*.label' => ['required', 'string', 'max:255'],
            'questions.*.type' => ['required', 'string', 'in:text,number,date,select'],
            'questions.*.options' => ['nullable', 'string'],
            'questions.*.required' => ['nullable'],
        ]);

        $cleanQuestions = [];
        if (! empty($validated['questions'])) {
            foreach ($validated['questions'] as $q) {
                if (! empty($q['label'])) {
                    $cleanQuestions[] = [
                        'id' => $q['id'] ?? ('q_'.uniqid()),
                        'label' => trim($q['label']),
                        'type' => $q['type'] ?? 'text',
                        'options' => ! empty($q['options']) ? (is_array($q['options']) ? $q['options'] : array_map('trim', explode(',', $q['options']))) : [],
                        'required' => ! empty($q['required']),
                    ];
                }
            }
        }

        $scholarship->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'discount_percentage' => $validated['discount_percentage'],
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $scholarship->is_active,
            'questions' => $cleanQuestions,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Scholarship Policy updated successfully.',
                'scholarship' => $scholarship,
            ]);
        }

        return redirect()->back()->with('success', 'Scholarship Policy updated successfully.');
    }

    /**
     * Remove the specified scholarship policy category.
     */
    public function destroy(ScholarshipCategory $scholarship): RedirectResponse
    {
        $scholarship->delete();

        return redirect()->back()->with('success', 'Scholarship Policy deleted successfully.');
    }
}
