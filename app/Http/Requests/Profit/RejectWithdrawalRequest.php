<?php

declare(strict_types=1);

namespace App\Http\Requests\Profit;

use Illuminate\Foundation\Http\FormRequest;

class RejectWithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('profit.manage-withdrawals');
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
