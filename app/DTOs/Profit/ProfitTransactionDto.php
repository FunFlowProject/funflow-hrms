<?php

declare(strict_types=1);

namespace App\DTOs\Profit;

use App\Enums\ProfitTransactionType;
use App\Enums\WithdrawalRequestStatus;
use App\Models\ProfitTransaction;
use App\Models\WithdrawalRequest;

class ProfitTransactionDto
{
    public function __construct(
        public int $id,
        public int $user_id,
        public string $employee_name,
        public string $type,
        public string $type_label,
        public float $amount,
        public string $amount_formatted,
        public float $balance_after,
        public string $balance_after_formatted,
        public ?string $description,
        public ?int $performed_by_id,
        public string $performed_by_name,
        public ?string $status,
        public ?string $status_label,
        public ?int $related_id,
        public ?string $related_type,
        public string $created_at,
        public string $created_at_formatted,
    ) {}

    public static function fromModel(ProfitTransaction $profitTransaction): self
    {
        $type = ProfitTransactionType::safeFrom($profitTransaction->type) ?? ProfitTransactionType::Credit;
        $employee = $profitTransaction->relationLoaded('user') ? $profitTransaction->user : $profitTransaction->user()->first();
        $performedBy = $profitTransaction->relationLoaded('performedBy') ? $profitTransaction->performedBy : $profitTransaction->performedBy()->first();

        $status = null;
        $statusLabel = null;
        $related = $profitTransaction->relationLoaded('related') ? $profitTransaction->related : null;

        if ($related instanceof WithdrawalRequest) {
            $withdrawalStatus = WithdrawalRequestStatus::safeFrom($related->status) ?? WithdrawalRequestStatus::Pending;
            $status = $withdrawalStatus->value;
            $statusLabel = $withdrawalStatus->label();
        }

        return new self(
            id: (int) $profitTransaction->id,
            user_id: (int) $profitTransaction->user_id,
            employee_name: $employee?->full_name ?? '-',
            type: $type->value,
            type_label: $type->label(),
            amount: (float) $profitTransaction->amount,
            amount_formatted: format_money((float) $profitTransaction->amount),
            balance_after: (float) $profitTransaction->balance_after,
            balance_after_formatted: format_money((float) $profitTransaction->balance_after),
            description: $profitTransaction->description,
            performed_by_id: $profitTransaction->performed_by !== null ? (int) $profitTransaction->performed_by : null,
            performed_by_name: $performedBy?->full_name ?? '-',
            status: $status,
            status_label: $statusLabel,
            related_id: $profitTransaction->related_id !== null ? (int) $profitTransaction->related_id : null,
            related_type: $profitTransaction->related_type,
            created_at: $profitTransaction->created_at->format('Y-m-d H:i:s'),
            created_at_formatted: $profitTransaction->created_at->format('d M Y, h:i A'),
        );
    }
}
