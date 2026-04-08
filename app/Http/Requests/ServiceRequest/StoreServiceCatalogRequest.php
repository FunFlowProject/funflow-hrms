<?php

declare(strict_types=1);

namespace App\Http\Requests\ServiceRequest;

use App\Enums\ActiveStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreServiceCatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('service-catalog.create');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'category' => ['required', 'string', 'min:2', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'requires_justification' => ['nullable', 'boolean'],
            'active' => ['nullable', 'integer', 'in:' . implode(',', ActiveStatus::values())],
        ];
    }
}
