<?php

declare(strict_types=1);

namespace App\Events\Profit;

use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WithdrawalRejected
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly WithdrawalRequest $withdrawalRequest,
        public readonly User $admin,
    ) {}
}
