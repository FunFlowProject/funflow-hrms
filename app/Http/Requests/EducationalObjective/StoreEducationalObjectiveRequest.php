<?php

declare(strict_types=1);

namespace App\Http\Requests\EducationalObjective;

use App\Enums\EducationalObjectivePriority;
use App\Enums\EducationalObjectiveScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEducationalObjectiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('educational-objectives.manage-all') || $this->user()->can('educational-objectives.manage-team');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'mandatory' => ['nullable', 'boolean'],
            'target_date' => ['nullable', 'date'],
            'priority' => ['required', Rule::enum(EducationalObjectivePriority::class)],
            'scope_type' => ['required', Rule::enum(EducationalObjectiveScope::class)],
            'scope_id' => [
                'nullable', 
                'integer',
                Rule::requiredIf(fn() => in_array($this->scope_type, ['sub_company', 'squad', 'individual']))
            ],
            'file' => ['nullable', 'file', 'max:10240'], // 10MB max
            'attachment_url' => ['nullable', 'url', 'max:2048'],
        ];
    }
}
