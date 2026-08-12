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
            'matriculation_cert'  => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'intermediate_cert'   => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'bachelors_cert'      => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'masters_cert'        => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'phd_cert'            => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'years_of_experience' => ['nullable', 'integer', 'min:0'],
            'specialization_subjects' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'basic_salary_pkr'        => ['nullable', 'numeric', 'min:0'],
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
            'matriculation_cert.required' => 'Matriculation certificate is required.',
            'intermediate_cert.required'  => 'Intermediate certificate is required.',
            'bachelors_cert.required'     => 'Bachelors certificate is required.',
        ];
    }
}
