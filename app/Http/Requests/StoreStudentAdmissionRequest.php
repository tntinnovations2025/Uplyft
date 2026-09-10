<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
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
     * Prepare the data for validation by stitching country codes and digits into standard format.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('phone_number') && $this->input('phone_number')) {
            $code = $this->input('phone_country_code', '+92');
            $num = preg_replace('/\D/', '', $this->input('phone_number'));
            $this->merge([
                'phone' => $code . ' ' . $num,
            ]);
        }

        if ($this->has('guardian_phone_number') && $this->input('guardian_phone_number')) {
            $code = $this->input('guardian_country_code', '+92');
            $num = preg_replace('/\D/', '', $this->input('guardian_phone_number'));
            $this->merge([
                'guardian_phone' => $code . ' ' . $num,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
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
                Rule::requiredIf(! auth()->check() && ! app()->bound('current_institute_id')),
                'integer',
                'exists:institutes,id',
            ],

            // Personal Details — all optional, partial data accepted
            'first_name' => ['nullable', 'string', 'max:50'],
            'last_name' => ['nullable', 'string', 'max:50'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                // Enforce email uniqueness ONLY within the active institute context
                Rule::unique('students', 'email')->where(function ($query) use ($instituteId) {
                    if ($instituteId) {
                        return $query->where('institute_id', $instituteId);
                    }

                    return $query;
                }),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'guardian_phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'blood_group' => ['nullable', 'string', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],

            // Academic Profile
            'previous_marks' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'enrolled_program' => ['nullable', 'string', 'max:50'],
            'class_section_id' => ['nullable', 'integer', 'exists:class_sections,id'],

            // Guardian Tax Filing Profile
            'guardian_tax_status' => ['nullable', 'string', Rule::in(['filer', 'non-filer'])],

            // New Advanced Enrollment Fields
            'passport_picture' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
            'student_bform_cnic' => ['nullable', 'string', 'regex:/^[0-9]{5}-[0-9]{7}-[0-9]{1}$/'],
            'father_guardian_cnic' => ['nullable', 'string', 'regex:/^[0-9]{5}-[0-9]{7}-[0-9]{1}$/'],
            'father_guardian_name' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'base_fee' => ['nullable', 'numeric', 'min:0'],
            'admission_fee' => ['nullable', 'numeric', 'min:0'],
            'security_fee' => ['nullable', 'numeric', 'min:0'],
            'tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'scholarship_category_id' => ['nullable', 'integer', 'exists:scholarship_categories,id'],
            'scholarship_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'scholarship_reason' => ['nullable', 'string', 'max:500'],
            'scholarship_verification_answers' => ['nullable', 'array'],
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
            'passport_picture.mimes' => 'Student picture must be a JPG, JPEG, or PNG image file.',
            'previous_marks.max' => 'The previous class marks cannot exceed 100.',
            'previous_marks.min' => 'The previous class marks must be at least 0.',
            'guardian_tax_status.in' => 'The guardian tax status must be either Filer or Non-Filer.',
            'student_bform_cnic.regex' => 'Student B-Form / CNIC must match the exact fixed format 00000-0000000-0.',
            'father_guardian_cnic.regex' => 'Father / Guardian CNIC must match the exact fixed format 00000-0000000-0.',
        ];
    }
}
