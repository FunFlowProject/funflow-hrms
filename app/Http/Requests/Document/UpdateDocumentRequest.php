<?php

declare(strict_types=1);

namespace App\Http\Requests\Document;

use App\Enums\DocumentClassification;
use App\Enums\DocumentScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('documents.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'classification' => ['sometimes', 'string', Rule::enum(DocumentClassification::class)],
            'scope_type' => ['sometimes', 'string', Rule::enum(DocumentScope::class)],
            'scope_id' => [
                'nullable',
                Rule::requiredIf(fn () => in_array($this->scope_type, [DocumentScope::SubCompany->value, DocumentScope::Squad->value])),
                'integer'
            ],
            'upload_type' => ['sometimes', 'string', Rule::in(['file', 'url', 'keep'])],
            'file' => ['required_if:upload_type,file', 'nullable', 'file', 'max:10240'],
            'file_url' => ['required_if:upload_type,url', 'nullable', 'url', 'max:2048'],
            'requires_acknowledgment' => ['sometimes', 'boolean'],
        ];
    }
}
