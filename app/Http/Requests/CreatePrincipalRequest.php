<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\StrongPassword;
use App\Rules\ValidIdentifier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates principal account creation requests.
 * Only Global Admin can use this.
 */
class CreatePrincipalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('createPrincipal', User::class);
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'identifier'   => ['nullable', 'string', new ValidIdentifier, 'unique:users,identifier'],
            'password'     => ['required', 'string', 'confirmed', new StrongPassword],
            'institute_id' => ['required', 'integer', 'exists:institutes,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'institute_id.required' => 'A principal must be assigned to an institute.',
            'institute_id.exists'   => 'The selected institute does not exist.',
        ];
    }
}
