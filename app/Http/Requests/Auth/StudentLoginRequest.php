<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\Validator;

/**
 * Dedicated Student portal login request.
 *
 * Accepts ONLY an RFC-valid email address as the credential, a string
 * password, and an optional remember flag. Any other submitted field is
 * rejected so that identifier/roll-number/auth-vector smuggling is
 * impossible on the student portal.
 */
class StudentLoginRequest extends LoginRequest
{
    private const ALLOWED_FIELDS = ['credential', 'password', 'remember', '_token'];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'credential' => ['required', 'string', 'max:255', 'email:rfc,filter'],
            'password'   => ['required', 'string'],
            'remember'   => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'credential' => trim((string) $this->input('credential')),
            'password'   => (string) $this->input('password'),
            'remember'   => $this->boolean('remember'),
        ]);
    }

    /**
     * Configure the validator instance.
     *
     * Rejects any unexpected field so a crafted payload cannot smuggle
     * alternative credential keys or role/auth tokens into the request.
     */
    public function withValidator(Validator $validator): void
    {
        $unexpected = array_values(array_diff_key($this->all(), array_flip(self::ALLOWED_FIELDS)));

        if ($unexpected !== []) {
            $validator->errors()->add(
                'credential',
                'Unexpected field(s) are not allowed on the student login form: ' . implode(', ', $unexpected) . '.'
            );
        }
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'credential.required' => 'Please enter your email address.',
            'credential.email'    => 'Student login requires a valid email address in the format name@example.com.',
            'password.required'   => 'Please enter your password.',
        ];
    }

    /**
     * This request is always bound to the Student portal login.
     */
    protected function isStudentLogin(): bool
    {
        return true;
    }
}