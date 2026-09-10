<?php

namespace App\Http\Requests\Lms;

use App\Services\DocumentSecurityService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates subject material uploads with byte-level content sniffing.
 *
 * Browser-declared MIME types and client filenames are never trusted;
 * the real file header is inspected by DocumentSecurityService after the
 * standard file/extension rules pass.
 */
class StoreSubjectMaterialRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && ($user->isAdministration() || $user->isTeacher());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $maxSizeKb = (int) config('lms.max_upload_size_kb', 524288);

        return [
            'title'         => ['required', 'string', 'max:255'],
            'document'      => ['required', 'file', 'mimes:pdf,doc,docx,txt', "max:{$maxSizeKb}"],
            'document_type' => ['nullable', 'string', 'in:textbook,notes,syllabus,reference'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('title')) {
            $this->merge(['title' => trim((string) $this->input('title'))]);
        }
    }

    /**
     * Post-validation hooks: sniff the actual file bytes.
     *
     * @return array<int, \Closure>
     */
    public function after(): array
    {
        return [
            function (\Illuminate\Contracts\Validation\Validator $validator) {
                if (! $this->hasFile('document')) {
                    return;
                }

                if ($validator->errors()->has('document')) {
                    return;
                }

                $reason = app(DocumentSecurityService::class)->validateUpload($this->file('document'));

                if ($reason !== null && $reason !== '') {
                    $validator->errors()->add('document', $reason);
                }
            },
        ];
    }
}