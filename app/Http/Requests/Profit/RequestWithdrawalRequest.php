<?php

declare(strict_types=1);

namespace App\Http\Requests\Profit;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class RequestWithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('profit.my.withdraw');
    }

    public function rules(): array
    {
        return [
            'amount' => [
                'required',
                'numeric',
                'gt:0',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $user = $this->user();
                    $currentBalance = (float) ($user?->profitBalance?->balance ?? 0);

                    if ((float) $value > $currentBalance) {
                        $fail(__('Requested amount must be less than or equal to your current profit balance.'));
                    }
                },
            ],
        ];
    }
}
