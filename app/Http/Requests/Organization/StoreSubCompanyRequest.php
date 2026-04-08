<?php

declare(strict_types=1);

namespace App\Http\Requests\Organization;

use App\Enums\ActiveStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:sub_companies,name'],
            'description' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'integer', 'in:' . implode(',', ActiveStatus::values())],
        ];
    }
}
