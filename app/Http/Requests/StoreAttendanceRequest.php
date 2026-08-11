<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceRequest extends FormRequest
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
            'academic_term_id'          => ['required', 'integer'],
            'date'                      => ['required', 'date'],
            'attendances'               => ['required', 'array', 'min:1'],
            'attendances.*.student_id'  => ['required', 'integer', 'exists:students,id'],
            'attendances.*.status'      => ['required', 'string', Rule::in(['present', 'absent', 'late', 'leave'])],
        ];
    }
}
