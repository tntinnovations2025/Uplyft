<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherOnboardingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'institute_id'        => ['nullable', 'integer', 'exists:institutes,id'],
            'first_name'          => ['required', 'string', 'max:50'],
            'last_name'           => ['required', 'string', 'max:50'],
            'email'               => ['required', 'email', 'max:255'],
            'phone'               => ['nullable', 'string', 'max:20'],
            'qualification'       => ['required', 'string', 'max:100'],
            'qualifications_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'], // Max 5MB
        ];
    }

    /**
     * Custom validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'qualifications_file.required' => 'The academic qualification transcript file is required.',
            'qualifications_file.max'      => 'The transcript document size must not exceed 5MB.',
            'qualifications_file.mimes'    => 'The transcript document must be a PDF or valid image (JPG, JPEG, PNG).',
        ];
    }
}
