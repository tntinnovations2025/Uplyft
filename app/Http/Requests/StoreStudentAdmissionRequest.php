<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentAdmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Under a multi-tenant model, verify user has permissions to write to this institute.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Resolve target institute ID for scoping checks (e.g., email uniqueness)
        $instituteId = auth()->user()->institute_id 
            ?? $this->input('institute_id') 
            ?? (app()->bound('current_institute_id') ? app('current_institute_id') : null);

        return [
            // If not authenticated or globally bound, require institute ID in request body
            'institute_id' => [
                Rule::requiredIf(!auth()->check() && !app()->bound('current_institute_id')),
                'integer',
                'exists:institutes,id'
            ],
            
            // Personal Details
            'first_name' => ['required', 'string', 'max:50'],
            'last_name'  => ['required', 'string', 'max:50'],
            'email'      => [
                'required',
                'email',
                'max:255',
                // Enforce email uniqueness ONLY within the active institute context
                Rule::unique('students', 'email')->where(function ($query) use ($instituteId) {
                    if ($instituteId) {
                        return $query->where('institute_id', $instituteId);
                    }
                    return $query;
                })
            ],
            'phone'         => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'blood_group'   => ['nullable', 'string', \Illuminate\Validation\Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],

            // Academic Profile
            'previous_marks' => ['required', 'numeric', 'min:0', 'max:100'],
            'enrolled_program' => ['nullable', 'string', 'max:50'],

            // Guardian Tax Filing Profile
            'guardian_tax_status' => ['required', 'string', Rule::in(['filer', 'non-filer'])],
            
            // New Advanced Enrollment Fields
            'passport_picture' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
            'student_bform_cnic' => ['nullable', 'string', 'max:20'],
            'father_guardian_cnic' => ['nullable', 'string', 'max:20'],
            'father_guardian_name' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'base_fee' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'previous_marks.max' => 'The previous class marks cannot exceed 100.',
            'previous_marks.min' => 'The previous class marks must be at least 0.',
            'guardian_tax_status.in' => 'The guardian tax status must be either Filer or Non-Filer.',
        ];
    }
}
