<?php

declare(strict_types=1);

namespace App\DTOs\Profit;

use App\Enums\WithdrawalRequestStatus;
use App\Models\WithdrawalRequest;

class WithdrawalRequestDto
{
    public function __construct(
        public int $id,
        public int $user_id,
        public string $employee_name,
        public float $amount,
        public string $amount_formatted,
        public string $status,
        public string $status_label,
        public ?int $acted_by,
        public ?string $acted_by_name,
        public ?string $acted_at,
        public ?string $acted_at_formatted,
        public ?string $rejection_reason,
        public string $created_at,
        public string $created_at_formatted,
        public ?string $updated_at,
        public ?string $updated_at_formatted,
    ) {}

    public static function fromModel(WithdrawalRequest $withdrawalRequest): self
    {
        $status = WithdrawalRequestStatus::safeFrom($withdrawalRequest->status) ?? WithdrawalRequestStatus::Pending;
        $user = $withdrawalRequest->relationLoaded('user') ? $withdrawalRequest->user : $withdrawalRequest->user()->first();
        $actor = $withdrawalRequest->relationLoaded('actor') ? $withdrawalRequest->actor : $withdrawalRequest->actor()->first();

        return new self(
            id: (int) $withdrawalRequest->id,
            user_id: (int) $withdrawalRequest->user_id,
            employee_name: $user?->full_name ?? '-',
            amount: (float) $withdrawalRequest->amount,
            amount_formatted: format_money((float) $withdrawalRequest->amount),
            status: $status->value,
            status_label: $status->label(),
            acted_by: $withdrawalRequest->acted_by !== null ? (int) $withdrawalRequest->acted_by : null,
            acted_by_name: $actor?->full_name,
            acted_at: $withdrawalRequest->acted_at?->format('Y-m-d H:i:s'),
            acted_at_formatted: $withdrawalRequest->acted_at?->format('d M Y, h:i A'),
            rejection_reason: $withdrawalRequest->rejection_reason,
            created_at: $withdrawalRequest->created_at->format('Y-m-d H:i:s'),
            created_at_formatted: $withdrawalRequest->created_at->format('d M Y, h:i A'),
            updated_at: $withdrawalRequest->updated_at->format('Y-m-d H:i:s'),
            updated_at_formatted: $withdrawalRequest->updated_at->format('d M Y, h:i A'),
        );
    }
}
