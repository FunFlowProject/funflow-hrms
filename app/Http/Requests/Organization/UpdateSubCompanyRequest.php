<?php

declare(strict_types=1);

namespace App\Http\Requests\Organization;

use App\Enums\ActiveStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubCompanyRequest extends FormRequest
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
        $subCompanyId = $this->route('subCompany');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('sub_companies', 'name')->ignore($subCompanyId)],
            'description' => ['nullable', 'string', 'max:255'],
            'active' => ['sometimes', 'integer', 'in:' . implode(',', ActiveStatus::values())],
        ];
    }
}
