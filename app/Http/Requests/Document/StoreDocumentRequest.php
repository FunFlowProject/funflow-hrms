<?php

declare(strict_types=1);

namespace App\Http\Requests\Document;

use App\Enums\DocumentClassification;
use App\Enums\DocumentScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('documents.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'classification' => ['required', 'string', Rule::enum(DocumentClassification::class)],
            'scope_type' => ['required', 'string', Rule::enum(DocumentScope::class)],
            'scope_id' => [
                'nullable',
                Rule::requiredIf(fn () => in_array($this->scope_type, [DocumentScope::SubCompany->value, DocumentScope::Squad->value])),
                'integer'
            ],
            'upload_type' => ['required', 'string', Rule::in(['file', 'url'])],
            'file' => ['required_if:upload_type,file', 'nullable', 'file', 'max:10240'], // 10MB max
            'file_url' => ['required_if:upload_type,url', 'nullable', 'url', 'max:2048'],
            'requires_acknowledgment' => ['sometimes', 'boolean'],
        ];
    }
}
