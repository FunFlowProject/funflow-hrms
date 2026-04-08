<?php

declare(strict_types=1);

namespace App\Http\Requests\Organization;

use App\Enums\ActiveStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSquadRequest extends FormRequest
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
        $squadId = $this->route('squad');
        $subCompanyId = (int) $this->input('sub_company_id');

        return [
            'sub_company_id' => ['required', 'integer', 'exists:sub_companies,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('squads', 'name')
                    ->where(fn ($query) => $query->where('sub_company_id', $subCompanyId))
                    ->ignore($squadId),
            ],
            'active' => ['sometimes', 'integer', 'in:' . implode(',', ActiveStatus::values())],
        ];
    }
}
