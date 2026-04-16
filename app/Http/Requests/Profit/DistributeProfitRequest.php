<?php

declare(strict_types=1);

namespace App\Http\Requests\Profit;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class DistributeProfitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('profit.distribute');
    }

    public function rules(): array
    {
        return [
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'integer', 'distinct', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
        ];
    }
}
