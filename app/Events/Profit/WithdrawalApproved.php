<?php

declare(strict_types=1);

namespace App\Events\Profit;

use App\Models\ProfitTransaction;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WithdrawalApproved
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly WithdrawalRequest $withdrawalRequest,
        public readonly User $admin,
        public readonly ProfitTransaction $transaction,
    ) {}
}
