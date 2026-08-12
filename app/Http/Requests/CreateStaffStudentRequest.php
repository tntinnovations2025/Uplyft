<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\StrongPassword;
use App\Rules\ValidIdentifier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates teacher/student account creation requests.
 * Principals and delegated teachers can use this.
 */
class CreateStaffStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('createStaffOrStudent', User::class);
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'identifier' => ['required', 'string', new ValidIdentifier, 'unique:users,identifier'],
            'password'   => ['required', 'string', 'confirmed', new StrongPassword],
            'role'       => ['required', 'string', Rule::in([User::ROLE_TEACHER, User::ROLE_STUDENT])],
        ];
    }

    public function messages(): array
    {
        return [
            'identifier.required' => 'A Roll Number or Employee ID is required.',
            'role.in'             => 'Role must be either teacher or student.',
        ];
    }
}
