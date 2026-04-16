<?php

declare(strict_types=1);

namespace App\DTOs\Profit;

use App\Models\ProfitBalance;

class ProfitBalanceDto
{
    public function __construct(
        public int $user_id,
        public string $employee_name,
        public float $balance,
        public string $balance_formatted,
        public ?string $updated_at,
        public ?string $updated_at_formatted,
    ) {}

    public static function fromModel(ProfitBalance $profitBalance): self
    {
        $user = $profitBalance->relationLoaded('user') ? $profitBalance->user : $profitBalance->user()->first();
        $balance = (float) $profitBalance->balance;

        return new self(
            user_id: (int) $profitBalance->user_id,
            employee_name: $user?->full_name ?? '-',
            balance: $balance,
            balance_formatted: format_money($balance),
            updated_at: $profitBalance->updated_at?->format('Y-m-d H:i:s'),
            updated_at_formatted: $profitBalance->updated_at?->format('d M Y, h:i A'),
        );
    }
}
